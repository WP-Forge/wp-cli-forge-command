<?php

namespace WP_Forge\Command\Concerns;

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
	 * @return \WP_Forge\Command\Templates\TemplateFinder
	 */
	protected function templates() {
		return $this->container->get( 'template_finder' );
	}

}
