<?php

namespace WP_Forge\Command\Prompts;

use WP_Forge\Command\Concerns\CLIOutput;
use WP_Forge\Command\Concerns\DependencyInjection;
use WP_Forge\Helpers\Str;

/**
 * Class PromptFactory
 */
class PromptFactory {

	use CLIOutput, DependencyInjection;

	/**
	 * Return a prompt instance given the provided data.
	 *
	 * @param array $args Prompt arguments.
	 *
	 * @return AbstractPrompt
	 */
	public function make( array $args ) {
		$type = data_get( $args, 'type' );

		if ( empty( $type ) ) {
			$this->error( 'Prompt type not provided!' );
		}

		if ( ! is_string( $type ) ) {
			$this->error( 'Prompt type invalid!' );
		}

		$class = __NAMESPACE__ . '\\' . Str::studly( $type );
		if ( ! class_exists( $class ) ) {
			$this->warning( "Prompt type '{$type}' not found for: " . data_get( $args, 'name' ) );
			$this->warning( "Defaulting to 'input' type!" );
			$class = __NAMESPACE__ . '\\Input';
		}

		/**
		 * Get the prompt instance.
		 *
		 * @var AbstractPrompt $instance
		 */
		$instance = new $class( $this->container );

		if ( ! is_a( $instance, AbstractPrompt::class ) ) {
			$class_name = get_class( $instance );
			$this->error( "Invalid prompt class: {$class_name}" );
		}

		$instance->withArgs( $args );

		$instance->validate();

		return $instance;
	}

}