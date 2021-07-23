<?php

namespace WP_Forge\Command\Concerns;

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
		return $this->container->get( 'scaffold' );
	}

}
