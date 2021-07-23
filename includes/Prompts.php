<?php

namespace WP_Forge\Command;

use League\CLImate\CLImate;
use WP_Forge\Command\Concerns\DependencyInjection;

/**
 * Class Prompts
 */
class Prompts {

	use DependencyInjection;

	/**
	 * Get CLImate instance.
	 *
	 * @return CLImate
	 */
	protected function cli() {
		return $this->container->get( 'climate' );
	}

	/**
	 * Present user with interactive checkboxes.
	 *
	 * @param string $message Prompt message
	 * @param array  $options Options
	 *
	 * @return array Selected keys if options is an associative array, selected values otherwise.
	 */
	public function checkboxes( $message, array $options ) {
		return $this->cli()->checkboxes( $message, $options )->prompt();
	}

	/**
	 * Request a yes/no confirmation from the user.
	 *
	 * @param string $message Prompt message
	 *
	 * @return boolean
	 */
	public function confirm( $message ) {
		return $this->cli()->confirm( $message )->confirmed();
	}

	/**
	 * Request enum input from the user.
	 *
	 * @param string $message Prompt message
	 * @param array  $options Options
	 *
	 * return string
	 */
	public function enum( $message, array $options ) {
		return $this->cli()->input( $message )->accept( $options, true )->prompt();
	}

	/**
	 * Request text input from the user.
	 *
	 * @param string $message Prompt message
	 * @param string $default Default value (optional)
	 *
	 * @return string
	 */
	public function input( $message, $default = null ) {

		$message = rtrim( $message, ':?' );

		// Display default value, if provided
		if ( ! is_null( $default ) ) {
			$message = rtrim( $message, ':' ) . ' [<yellow>' . $default . '</yellow>]';
		}

		$input = $this->cli()->input( $message . ':' );

		// Set default value, if provided
		if ( ! is_null( $default ) ) {
			$input->defaultTo( $default );
		}

		$value = $input->prompt();

		// Continue to show prompt if there is no default value and entered value is empty
		while ( empty( $value ) && empty( $default ) ) {
			$value = $input->prompt();
		}

		return $value;
	}

	/**
	 * Request multi-line text input from the user.
	 *
	 * @param string $message Prompt message
	 *
	 * @return string
	 */
	public function multiline( $message ) {
		return $this->cli()->input( $message )->multiLine()->prompt();
	}

	/**
	 * Request a password from the user.
	 *
	 * @param string $message Prompt message
	 *
	 * @return string
	 */
	public function password( $message ) {
		return $this->cli()->password( $message )->prompt();
	}

	/**
	 * Present user with interactive radio buttons.
	 *
	 * @param string $message Prompt message
	 * @param array  $options Options
	 *
	 * @return string Selected key if options is an associative array, selected value otherwise.
	 */
	public function radio( $message, array $options ) {
		return $this->cli()->radio( $message, $options )->prompt();
	}

}
