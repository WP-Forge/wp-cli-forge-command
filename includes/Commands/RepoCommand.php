<?php

namespace WP_Forge\Command\Commands;

use WP_Forge\Command\AbstractCommand;
use WP_Forge\Command\Concerns\Config;
use WP_Forge\Command\Concerns\DependencyInjection;
use WP_Forge\Command\Concerns\Filesystem;

/**
 * Manage Git repositories containing templates for scaffolding.
 */
class RepoCommand extends AbstractCommand {

	use DependencyInjection, Config, Filesystem;

	/**
	 * Command name.
	 *
	 * @var string
	 */
	const COMMAND = 'repo';

	/**
	 * Clone a template repository.
	 *
	 * ## OPTIONS
	 *
	 * <repository_url>
	 * : The URL for the Git repository.
	 *
	 * [--as=<name>]
	 * : Optionally assign a name to this repository.
	 * ---
	 * default: default
	 * ---
	 *
	 * [--force]
	 * : Whether or not to force override an existing repository.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function clone( $args, $options ) { // phpcs:ignore PHPCompatibility.Keywords.ForbiddenNames.cloneFound

		$this->init( $args, $options );

		// Ensure that git is available
		$this->gitCheck();

		$url    = $this->argument();
		$name   = $this->option( 'as', 'default' );
		$folder = $this->appendPath( 'templates', $name );

		$path = $this->appendPath( $this->get( 'home_dir' ), '.wp-cli', $folder );

		if ( file_exists( $path ) && ! $this->option( 'force', false ) ) {

			$this->error( 'Repository has already been cloned!', false );
			$this->error( "Run 'wp {$this->getCommand()} update' to update.", false );

		} else {

			if ( file_exists( $path ) ) {
				// Clean up directory
				$this->cli()->blue( 'Deleting existing files located at: ' . $path );
				$this->filesystem( $this->appendPath( $this->get( 'home_dir' ), '.wp-cli' ) )->deleteDirectory( $folder );
			}

			$branch = $this->option( 'branch', 'master' );
			$remote = $this->option( 'remote', 'origin' );

			// Clone the repository
			$this->gitClone( $url, $path );
			$this->gitUpdate( $path, $branch, $remote );
		}

		$this->globalConfig()->data()->set( "templates.{$name}.url", $url );
		$this->globalConfig()->data()->set( "templates.{$name}.path", $folder );
		$this->globalConfig()->save();

	}

	/**
	 * Update a template repository.
	 *
	 * ## OPTIONS
	 *
	 * [--as=<name>]
	 * : The name assigned to the repo.
	 * ---
	 * default: default
	 * ---
	 *
	 * [--branch=<name>]
	 * : The branch to pull.
	 * ---
	 * default: master
	 * ---
	 *
	 * [--remote=<name>]
	 * : The branch to pull.
	 * ---
	 * default: origin
	 * ---
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function update( $args, $options ) {

		$this->init( $args, $options );

		// Ensure that git is available
		$this->gitCheck();

		$name   = $this->option( 'as', 'default' );
		$folder = $this->globalConfig()->data()->get( "templates.{$name}.path", $this->appendPath( 'templates', $name ) );

		$path = $this->appendPath( $this->get( 'home_dir' ), '.wp-cli', $folder );

		if ( empty( $folder ) || empty( $path ) || ! file_exists( $path ) ) {
			$this->error( "No repository found under the name '{$name}'!", false );
			$this->error( "Run 'wp {$this->getCommand()} clone' to clone a new repository." );
		}

		$branch = $this->option( 'branch', 'master' );
		$remote = $this->option( 'remote', 'origin' );

		// Pull the repository
		$this->gitUpdate( $path, $branch, $remote );

	}

	/**
	 * Delete a template repository.
	 *
	 * ## OPTIONS
	 *
	 * <name>
	 * : The name of the repo.
	 * ---
	 * default: default
	 * ---
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function delete( $args, $options ) {

		$this->init( $args, $options );

		$shouldDelete = $this->prompt()->confirm( 'Are you sure you want to delete this repository?' );

		if ( $shouldDelete ) {

			$name   = $this->argument( 0, 'default' );
			$folder = $this->globalConfig()->data()->get( "templates.{$name}.path", $this->appendPath( 'templates', $name ) );

			$path = $this->appendPath( $this->get( 'home_dir' ), '.wp-cli', $folder );

			// Clean up directory
			$this->cli()->blue( 'Deleting existing files located at: ' . $path );
			$this->filesystem( $this->appendPath( $this->get( 'home_dir' ), '.wp-cli' ) )->deleteDirectory( $folder );

			$this->globalConfig()->data()->forget( "templates.{$name}" );
			$this->globalConfig()->save();

			$this->success( 'Repository deleted successfully!' );

		}

	}

	/**
	 * List the registered template repositories.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function list( $args, $options ) { // phpcs:ignore PHPCompatibility.Keywords.ForbiddenNames.listFound
		$this->init( $args, $options );
		$templates = $this->globalConfig()->data()->get( 'templates' );
		if ( $templates && is_array( $templates ) ) {
			foreach ( $templates as $name => $data ) {
				$url = data_get( $data, 'url' );
				$this->out( "<yellow>{$name}</yellow>: {$url}" );
			}
		}
	}

	/**
	 * Check if Git is available.
	 */
	protected function gitCheck() {
		// Ensure that git is available
		exec( 'command -v git', $output, $result ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
		if ( 0 !== $result ) {
			$this->error( 'Git is not installed!' );
		}
	}

	/**
	 * Clone a Git repository.
	 *
	 * @param string $url Git URL
	 * @param string $path Destination path
	 */
	protected function gitClone( $url, $path ) {
		shell_exec( "git clone {$url} {$path}" ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec
	}

	/**
	 * Update a Git repository.
	 *
	 * @param string $path Path to Git repository
	 * @param string $branch Git branch
	 * @param string $remote Git remote
	 */
	protected function gitUpdate( $path, $branch = 'master', $remote = 'origin' ) {
		shell_exec( "git -C {$path} pull {$remote} {$branch}" ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec
	}

}
