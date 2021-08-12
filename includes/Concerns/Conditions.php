<?php

namespace WP_Forge\Command\Concerns;

use WP_Forge\Command\Conditions\ConditionHandler;

trait Conditions {

	/**
	 * Dependency injection container
	 *
	 * @var \WP_Forge\Container\Container
	 */
	protected $container;

	/**
	 * Gets the ConditionHandler class for bulk evaluation of conditions.
	 *
	 * @return ConditionHandler
	 */
	protected function conditions() {
		return new ConditionHandler( $this->container );
	}

}
