<?php

namespace WP_Forge\Command\Directives;

use WP_CLI;
use WP_Forge\Helpers\Str;

/**
 * Class RunCommand
 */
class RunCommand extends AbstractDirective {

	/**
	 * Type of copy action. Can be copyDir or copyFile.
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * Command to be run.
	 *
	 * @var string
	 */
	protected $command;

	/**
	 * Directory from which to run command.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Initialize properties for the directive.
	 *
	 * @param array $args Directive arguments.
	 */
	public function initialize( array $args ) {
		$this->command = data_get( $args, 'command' );
		$relativeTo    = data_get( $args, 'relativeTo', 'workingDir' );
		$this->path    = ( 'projectRoot' === $relativeTo ) ? $this->container->get( 'project_config' )->path() : getcwd();
	}

	/**
	 * Validate the directive properties.
	 */
	public function validate() {
		if ( empty( $this->command ) ) {
			$this->error( 'Command is missing!' );
		}
	}

	/**
	 * Execute the directive.
	 */
	public function execute() {

		// Allow for dynamic replacements in commands
		if ( false !== strpos( $this->command, '{{' ) ) {
			$this->command = $this->container->get( 'mustache' )->render( $this->command, $this->container->get( 'registry' )->get( 'data' )->toArray() );
		}

		if ( Str::startsWith( $this->command, array( 'wp', $this->container( 'base_command' ) ) ) ) {

			// Run a WP-CLI command
			WP_CLI::RunCommand(
				Str::replaceFirst( 'wp ', '', $this->command ), // Remove 'wp' portion of command
				array(
					'launch' => false, // Use the existing process
					'force'  => $this->shouldOverwrite(),
				)
			);

		} else {

			passthru( $this->command, $code ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_passthru
			if ( 0 !== $code ) {
				$this->error( 'Command failed: ' . $this->command );
			}
		}

	}

	/**
	 * Check if we should overwrite files.
	 *
	 * @return bool
	 */
	protected function shouldOverwrite() {
		return (bool) data_get( WP_CLI::get_runner()->assoc_args, 'force', false );
	}

}
