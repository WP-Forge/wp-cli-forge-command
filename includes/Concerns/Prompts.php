<?php

namespace WP_Forge\Command\Concerns;

use WP_Forge\Command\Prompts\PromptHandler;

trait Prompts {

	/**
	 * Dependency injection container
	 *
	 * @var \WP_Forge\Container\Container
	 */
	protected $container;

	/**
	 * Gets the PromptHandler class for configuring bulk prompts and managing the data.
	 *
	 * @return PromptHandler
	 */
	protected function prompts() {
		return new PromptHandler( $this->container );
	}

}
