<?php

namespace WP_Forge\Command\Concerns;

use WP_Forge\Command\Templates\TemplateFinder;

/**
 * Trait Templates
 */
trait Templates {

	/**
	 * Dependency injection container
	 *
	 * @var \WP_Forge\Container\Container
	 */
	protected $container;

	/**
	 * Get a template finder instance.
	 *
	 * @return TemplateFinder
	 */
	protected function templates() {
		return new TemplateFinder( $this->container );
	}

}
