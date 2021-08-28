<?php

namespace WP_Forge\Command\Concerns;

use WP_Forge\Container\Container;

/**
 * Trait Store
 */
trait Store {

	/**
	 * Dependency injection container
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Get the data store.
	 *
	 * @return \WP_Forge\DataStore\DataStore
	 */
	protected function store() {
		return $this->container->get( 'data' );
	}

}
