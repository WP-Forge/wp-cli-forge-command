<?php

namespace WP_Forge\Command;

use League\CLImate\CLImate;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use WP_Forge\Command\Concerns\CLIOutput;
use WP_Forge\Command\Directives\DirectiveFactory;
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
		$this->setup_container( $args );
		$this->registerCommands();
		register_shutdown_function( array( $this, 'onShutdown' ) );
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
					'home_dir'      => $this->getHomeDir(),
					'templates_dir' => $this->getTemplatesDir(),
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
					$mustache = new \Mustache_Engine(
						array(
							'entity_flags' => ENT_QUOTES,
							'pragmas'      => array(
								\Mustache_Engine::PRAGMA_FILTERS,
							),
						)
					);

					// Copy all transforms as helper methods
					$class   = new \ReflectionClass( Transforms::class );
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
			'prompts',
			$container->service(
				function ( Container $c ) {
					return new Prompts( $c );
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
		$iterator = new \RecursiveDirectoryIterator( __DIR__ . '/Commands' );
		/**
		 * File instance.
		 *
		 * @var \SplFileInfo $file
		 */
		foreach ( new \RecursiveIteratorIterator( $iterator ) as $file ) {
			if ( $file->getExtension() === 'php' ) {
				$relativePath      = str_replace( __DIR__ . DIRECTORY_SEPARATOR, '', $file->getPath() );
				$relativeNamespace = str_replace( DIRECTORY_SEPARATOR, '\\', $relativePath );
				$class             = __NAMESPACE__ . "\\$relativeNamespace\\" . $file->getBasename( '.php' );
				$instance          = new $class( $this->container );
				/**
				 * Instance of command class.
				 *
				 * @var AbstractCommand $instance
				 */
				\WP_CLI::add_command(
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
