<?php

namespace WP_Forge\Command\Directives;

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
	 * Initialize properties for the directive.
	 *
	 * @param array $args Directive arguments.
	 */
	public function initialize( array $args ) {
		$this->command = data_get( $args, 'command' );
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

		\WP_CLI::RunCommand(
			Str::replaceFirst( 'wp ', '', $this->command ), // Remove 'wp' portion of command
			array(
				'launch' => false, // Use the existing process
				'force'  => $this->shouldOverwrite(),
			)
		);
	}

	/**
	 * Check if we should overwrite files.
	 *
	 * @return bool
	 */
	protected function shouldOverwrite() {
		return (bool) data_get( \WP_CLI::get_runner()->assoc_args, 'force', false );
	}

}
