<?php

namespace WP_Forge\Command;

use WP_CLI;
use WP_Forge\Command\Concerns\DependencyInjection;
use WP_Forge\Command\Concerns\CLIOutput;
use WP_Forge\Command\Concerns\Prompts;
use WP_Forge\Command\Concerns\Registry;

/**
 * Class AbstractCommand
 */
abstract class AbstractCommand {

	use DependencyInjection, CLIOutput, Prompts, Registry;

	/**
	 * Command name.
	 *
	 * @var string
	 */
	const COMMAND = '';

	/**
	 * CLI arguments.
	 *
	 * @var array
	 */
	protected $args = array();

	/**
	 * CLI options.
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Initialize command info.
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	protected function init( $args, $options ) {
		$this->args    = $args;
		$this->options = $options;
	}

	/**
	 * Get command.
	 *
	 * @return string
	 */
	protected function getCommand() {
		return $this->get( 'base_command' ) . ' ' . static::COMMAND;
	}

	/**
	 * Get an argument by index.
	 *
	 * @param int   $index Argument index
	 * @param mixed $default Default value
	 *
	 * @return mixed
	 */
	protected function argument( $index = 0, $default = null ) {
		return data_get( $this->args, $index, $default );
	}

	/**
	 * Get an option by name, optionally set a default value.
	 *
	 * @param string $name Option name
	 * @param mixed  $default Default value
	 *
	 * @return mixed
	 */
	protected function option( $name, $default = null ) {
		return data_get( $this->options, $name, $default );
	}

}
