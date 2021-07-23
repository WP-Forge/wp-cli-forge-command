<?php

namespace WP_Forge\Command\Concerns;

trait Prompts {

	/**
	 * Dependency injection container
	 *
	 * @var \WP_Forge\Container\Container
	 */
	protected $container;

	/**
	 * Get the Prompts class with available prompts.
	 *
	 * @return \WP_Forge\Command\Prompts
	 */
	protected function prompt() {
		return $this->container->get( 'prompts' );
	}

	/**
	 * Gets the PromptHandler class for configuring bulk prompts and managing the data.
	 *
	 * @return \WP_Forge\Command\PromptHandler
	 */
	protected function prompts() {
		return $this->container->get( 'prompt_handler' );
	}

}
