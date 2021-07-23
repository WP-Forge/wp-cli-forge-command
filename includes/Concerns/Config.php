<?php

namespace WP_Forge\Command\Concerns;

trait Config {

	/**
	 * Dependency injection container
	 *
	 * @var /WP_Forge\Container\Container
	 */
	protected $container;

	/**
	 * Get a new Config class instance.
	 *
	 * @return \WP_Forge\Command\Config
	 */
	protected function config() {
		return $this->container->get( 'config' );
	}

	/**
	 * Get ProjectConfig class instance.
	 *
	 * @return \WP_Forge\Command\ProjectConfig
	 */
	protected function projectConfig() {
		return $this->container->get( 'project_config' );
	}

	/**
	 * Get GlobalConfig class instance.
	 *
	 * @return \WP_Forge\Command\GlobalConfig
	 */
	protected function globalConfig() {
		return $this->container->get( 'global_config' );
	}

}
