<?php

namespace WP_Forge\Command;

use WP_Forge\Command\Concerns\Mustache;
use WP_Forge\Container\Container;
use WP_Forge\DataStore\DataStore;

/**
 * Class PromptHandler
 */
class PromptHandler {

	use Mustache;

	/**
	 * Dependency injection container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Data collected from the user.
	 *
	 * @var DataStore
	 */
	protected $data;

	/**
	 * Prompts
	 *
	 * @var array
	 */
	protected $prompts = array();

	/**
	 * PromptHandler constructor.
	 *
	 * @param Container $container Container instance
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
		$this->data      = $container->get( 'store' );
	}

	/**
	 * Add a prompt.
	 *
	 * @param array $args Prompt arguments
	 */
	public function add( array $args ) {
		$this->prompts[] = $args;
		return $this;
	}

	/**
	 * Set all prompts.
	 *
	 * @param array $prompts Collection of prompts
	 */
	public function populate( array $prompts ) {
		$this->prompts = $prompts;
		return $this;
	}

	/**
	 * Render prompts and return all data.
	 *
	 * @return $this
	 */
	public function render() {
		foreach ( $this->prompts as $index => $args ) {

			// Perform a replacement on the default value, if necessary
			if ( array_key_exists( 'default', $args ) && is_string( $args['default'] ) && false !== strpos( $args['default'], '{{' ) ) {
				$args['default'] = $this->replace( $args['default'], $this->data()->toArray() );
			}

			// Transform default value, if necessary
			if ( array_key_exists( 'transform_default', $args ) ) {
				$args['default'] = $this->transform( data_get( $args, 'default' ), $args, 'transform_default' );
			}

			// Get name
			$name = data_get( $args, 'name', $index );

			// Don't request data we already have!
			if ( $this->data()->has( $name ) ) {
				continue;
			}

			// Get value from user and transform, if necessary
			$value = $this->transform( $this->factory( $args ), $args );

			// Set value in data store
			$this->data->set( $name, $value );
		}
		return $this;
	}

	/**
	 * Transform a value.
	 *
	 * @param mixed  $value Value provided by the user
	 * @param array  $args Prompt arguments
	 * @param string $key Key used to find transform callback
	 *
	 * @return mixed
	 */
	public function transform( $value, array $args, $key = 'transform' ) {
		$transform = (array) data_get( $args, $key, array() );
		if ( ! empty( $transform ) ) {
			foreach ( $transform as $callback ) {
				$value = $callback( $value );
			}
		}
		return $value;
	}

	/**
	 * Factory method for returning a new prompt instance.
	 *
	 * @param array $args Prompt arguments
	 *
	 * @return array|bool|string
	 */
	public function factory( array $args ) {

		/**
		 * Prompts instance.
		 *
		 * @var Prompts $prompt
		 */
		$prompt = $this->container->get( 'prompts' );

		$type    = data_get( $args, 'type', 'input' );
		$message = data_get( $args, 'message' );
		$default = data_get( $args, 'default' );
		$options = data_get( $args, 'options', array() );

		switch ( $type ) {
			case 'checkboxes':
				return $prompt->checkboxes( $message, $options );
			case 'confirm':
				return $prompt->confirm( $message );
			case 'enum':
				return $prompt->enum( $message, $options );
			case 'multiline':
				return $prompt->multiline( $message );
			case 'password':
				return $prompt->password( $message );
			case 'radio':
				return $prompt->radio( $message, $options );
			default:
				return $prompt->input( $message, $default );
		}
	}

	/**
	 * Get all data.
	 *
	 * @return DataStore
	 */
	public function data() {
		return $this->data;
	}

}
