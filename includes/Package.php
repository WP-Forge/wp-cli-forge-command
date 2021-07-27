<?php

namespace WP_Forge\Command;

use League\CLImate\CLImate;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Mustache_Engine;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use WP_CLI;
use WP_Forge\Command\Commands\RepoCommand;
use WP_Forge\Command\Concerns\CLIOutput;
use WP_Forge\Command\Directives\DirectiveFactory;
use WP_Forge\Command\Prompts\PromptFactory;
use WP_Forge\Command\Prompts\PromptHandler;
use WP_Forge\Container\Container;
use WP_Forge\DataStore\DataStore;

/**
 * Class Package
 */
class Package {

	use CLIOutput;

	/**
	 * Dependency injection container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Config constructor.
	 *
	 * @param array $args Arguments to be injected into the container.
	 */
	public function __construct( array $args = array() ) {

		// Setup dependency injection container
		$this->setup_container( $args );

		// Register available commands
		$this->registerCommands();

		// Run code on shutdown
		register_shutdown_function( array( $this, 'onShutdown' ) );

		/**
		 * Get the global config.
		 *
		 * @var GlobalConfig $globalConfig
		 */
		$globalConfig = $this->container->get( 'global_config' );

		// Get the URL for the default template repository
		$defaultTemplateRepo = $this->container->get( 'default_template_repo' );

		// If no default template repo exists in the global config, then set it (allows a user to set a new default).
		if ( ! $globalConfig->data()->has( 'default_template_repo' ) ) {
			$globalConfig->data()->set( 'default_template_repo', $defaultTemplateRepo );
			$globalConfig->save();
		}

		// If there are no default templates, clone the default repo
		if ( ! file_exists( $this->getTemplatesDir() . DIRECTORY_SEPARATOR . 'default' ) ) {
			( new RepoCommand( $this->container ) )->clone( array( $globalConfig->data()->get( 'default_template_repo' ) ), array() );
		}
	}

	/**
	 * Setup dependency injection container.
	 *
	 * @param array $args Arguments to be injected into the container.
	 */
	public function setup_container( array $args = array() ) {
		$container = new Container(
			array_merge(
				array(
					'home_dir'     => $this->getHomeDir(),
					'template_dir' => $this->getTemplatesDir(),
				),
				$args
			)
		);

		$container->set(
			'climate',
			$container->service(
				function () {
					return new CLImate();
				}
			)
		);

		$container->set(
			'mustache',
			$container->service(
				function () {

					// Get Mustache engine
					$mustache = new Mustache_Engine(
						array(
							'entity_flags' => ENT_QUOTES,
							'pragmas'      => array(
								Mustache_Engine::PRAGMA_FILTERS,
							),
						)
					);

					// Copy all transforms as helper methods
					$class   = new ReflectionClass( Transforms::class );
					$methods = $class->getMethods();
					foreach ( $methods as $method ) {
						$mustache->addHelper( $method->name, array( $method->class, $method->name ) );
					}

					return $mustache;
				}
			)
		);

		$container->set(
			'filesystem',
			$container->factory(
				function () {
					return function ( $path ) {
						return new Filesystem( new LocalFilesystemAdapter( $path ) );
					};
				}
			)
		);

		$container->set(
			'config',
			$container->factory(
				function ( Container $c ) {
					return new Config( $c );
				}
			)
		);

		$container->set(
			'project_config',
			$container->service(
				function ( Container $c ) {
					return new ProjectConfig( $c );
				}
			)
		);

		$container->set(
			'global_config',
			$container->service(
				function ( Container $c ) {
					return new GlobalConfig( $c );
				}
			)
		);

		$container->set(
			'registry',
			$container->service(
				function ( Container $c ) {

					// Create global registry
					$registry = new DataStore();

					// Create store for collected user data
					$data = new DataStore();

					/**
					 * Project configuration
					 *
					 * @var ProjectConfig $projectConfig
					 */
					$projectConfig = $c->get( 'project_config' );

					// Pre-populate user data with project settings
					$data->put( $projectConfig->data()->toArray() );

					// Also make important paths available
					$data->set( 'project_root', $projectConfig->path() );
					$data->set( 'working_dir', getcwd() );

					// Store user data store in registry
					$registry->set( 'data', $data );

					return $registry;
				}
			)
		);

		$container->set(
			'store',
			$container->factory(
				function () {
					return new DataStore();
				}
			)
		);

		$container->set(
			'scaffold',
			$container->factory(
				function ( Container $c ) {
					return new Scaffold( $c );
				}
			)
		);

		$container->set(
			'prompt',
			$container->factory(
				function ( Container $c ) {
					return function ( array $args ) use ( $c ) {
						return ( new PromptFactory( $c ) )->make( $args );
					};
				}
			)
		);

		$container->set(
			'prompt_handler',
			$container->factory(
				function ( Container $c ) {
					return new PromptHandler( $c );
				}
			)
		);

		$container->set(
			'directive',
			$container->factory(
				function ( Container $c ) {
					return function ( array $args ) use ( $c ) {
						return ( new DirectiveFactory( $c ) )->make( $args );
					};
				}
			)
		);

		$this->container = $container;
	}

	/**
	 * Register WP CLI commands.
	 */
	public function registerCommands() {
		$iterator = new RecursiveDirectoryIterator( __DIR__ . '/Commands' );
		/**
		 * File instance.
		 *
		 * @var \SplFileInfo $file
		 */
		foreach ( new RecursiveIteratorIterator( $iterator ) as $file ) {
			if ( $file->getExtension() === 'php' ) {
				$relativePath      = str_replace( __DIR__ . DIRECTORY_SEPARATOR, '', $file->getPath() );
				$relativeNamespace = str_replace( DIRECTORY_SEPARATOR, '\\', $relativePath );
				$class             = __NAMESPACE__ . "\\$relativeNamespace\\" . $file->getBasename( '.php' );
				$instance          = new $class( $this->container );
				WP_CLI::add_command(
					$this->container->get( 'base_command' ) . ' ' . $class::COMMAND,
					$instance
				);
			}
		}
	}

	/**
	 * Shutdown callback.
	 */
	public function onShutdown() {
		// Display any registered messages
		$this->displayMessages();
	}

	/**
	 * Display messages.
	 */
	public function displayMessages() {
		$messages = $this->container->get( 'registry' )->get( 'messages' );
		if ( $messages && is_array( $messages ) ) {
			foreach ( $messages as $message ) {
				$type = data_get( $message, 'type' );
				if ( empty( $type ) || property_exists( $this, $type ) ) {
					$type = 'out';
				}
				$this->{$type}( data_get( $message, 'message' ), false );
			}
		}
	}

	/**
	 * Get the home directory.
	 *
	 * @return string
	 */
	public function getHomeDir() {
		$home = getenv( 'HOME' );
		if ( ! $home ) {
			// In Windows $HOME may not be defined
			$home = getenv( 'HOMEDRIVE' ) . getenv( 'HOMEPATH' );
		}

		return rtrim( $home, '/\\' );
	}

	/**
	 * Get the templates directory.
	 *
	 * @return string
	 */
	public function getTemplatesDir() {
		return implode( DIRECTORY_SEPARATOR, array( $this->getHomeDir(), '.wp-cli', 'templates' ) );
	}

}
