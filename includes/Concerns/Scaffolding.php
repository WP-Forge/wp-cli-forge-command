<?php

namespace WP_Forge\Command\Concerns;

use WP_Forge\Command\Scaffold;

trait Scaffolding {

	/**
	 * Dependency injection container
	 *
	 * @var \WP_Forge\Container\Container
	 */
	protected $container;

	/**
	 * Get a new Scaffold class instance.
	 *
	 * @return \WP_Forge\Command\Scaffold
	 */
	protected function scaffold() {
		return new Scaffold( $this->container );
	}

}
