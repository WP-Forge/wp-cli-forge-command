<?php

namespace WP_Forge\Command\Conditions;

use WP_Forge\Command\Concerns\CLIOutput;
use WP_Forge\Command\Concerns\DependencyInjection;
use WP_Forge\Helpers\Str;

/**
 * Class ConditionFactory
 */
class ConditionFactory {

	use CLIOutput, DependencyInjection;

	/**
	 * Return a condition instance given the provided data.
	 *
	 * @param array $args Condition arguments.
	 *
	 * @return AbstractCondition
	 */
	public function make( array $args ) {
		$type = data_get( $args, 'condition' );

		if ( empty( $type ) ) {
			$this->error( 'Condition type not provided!' );
		}

		if ( ! is_string( $type ) ) {
			$this->error( 'Condition type invalid!' );
		}

		$class = __NAMESPACE__ . '\\' . Str::studly( $type );
		if ( ! class_exists( $class ) ) {
			$this->error( "Condition type '{$type}' not found." );
		}

		/**
		 * Get the prompt instance.
		 *
		 * @var AbstractCondition $instance
		 */
		$instance = new $class( $this->container );

		if ( ! is_a( $instance, AbstractCondition::class ) ) {
			$class_name = get_class( $instance );
			$this->error( "Invalid condition class: {$class_name}" );
		}

		$instance->withArgs( $args );
		$instance->withData( $this->container( 'data' ) );

		$instance->validate();

		return $instance;
	}

}