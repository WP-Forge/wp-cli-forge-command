<?php

namespace WP_Forge\Command\Concerns;

use WP_Forge\Container\Container;

/**
 * Trait DependencyInjection
 */
trait DependencyInjection {

	/**
	 * Dependency injection container
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @param Container $container Container instance
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * Get an item from the container.
	 *
	 * @param string $id Name used to identify an item in the container
	 *
	 * @return mixed
	 */
	protected function container( $id ) {
		return $this->container->get( $id );
	}

}
