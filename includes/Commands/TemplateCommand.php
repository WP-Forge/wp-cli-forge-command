<?php

namespace WP_Forge\Command\Commands;

use WP_Forge\Command\AbstractCommand;
use WP_Forge\Command\Concerns\Config;
use WP_Forge\Command\Concerns\DependencyInjection;
use WP_Forge\Command\Concerns\Filesystem;
use WP_Forge\DataStore\DataStore;

/**
 * Create a templates within a scaffolding repo.
 */
class TemplateCommand extends AbstractCommand {

	use DependencyInjection, Config, Filesystem;

	/**
	 * Command name.
	 *
	 * @var string
	 */
	const COMMAND = 'template';

	/**
	 * Create a new template.
	 *
	 * ## OPTIONS
	 *
	 * [--force]
	 * : Whether or not to force override an existing repository.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function create( $args, $options ) {

		$this->init( $args, $options );

		$config = $this
			->config()
			->withFileName( 'config.json' )
			->withPath( getcwd() );

		if ( file_exists( $config->filePath() ) && ! $this->option( 'force', false ) ) {
			$this->error( 'A configuration file already exists for this template!', false );
			$this->error( 'Add the --force flag to overwrite' );
		}

		$this->configurePrompts( $config->data() );
		$this->configureDirectives( $config->data() );

		$config->save();

	}

	/**
	 * Configure prompts.
	 *
	 * @param DataStore $data Configuration data
	 */
	protected function configurePrompts( DataStore $data ) {
		if ( $this->prompt()->confirm( 'Would you like to request data from the user?' ) ) {
			$prompts = $this->get( 'store' );
			$this->addPrompt( $prompts );
			while ( $this->prompt()->confirm( 'Add another prompt?' ) ) {
				$this->addPrompt( $prompts );
			}
			$data->set( 'prompts', $prompts->toArray() );
		}
	}

	/**
	 * Configure directives.
	 *
	 * @param DataStore $data Configuration data
	 */
	protected function configureDirectives( DataStore $data ) {
		// TODO: Configure directives (have default like "copy all except config.json") - standardize on more obscure name?
	}

	/**
	 * Walk user through adding a prompt.
	 *
	 * @param DataStore $prompts Collection of prompts
	 */
	protected function addPrompt( DataStore $prompts ) {
		$field = $this
			->prompts()
			->populate( // Request details on required properties
				array(
					array(
						'message' => 'Message to show the user',
						'name'    => 'message',
						'type'    => 'input',
					),
					array(
						'message' => 'Internal field name (used for replacements)',
						'name'    => 'name',
						'type'    => 'input',
					),
					array(
						'message' => 'Select a field type',
						'name'    => 'type',
						'type'    => 'radio',
						'options' => array( // TODO: Support confirm field with optional directive
							'input'      => 'Text input (single line)',
							'multiline'  => 'Text input (multi-line)',
							'password'   => 'Password',
							'enum'       => 'Pick One (text)',
							'radio'      => 'Pick One (radio)',
							'checkboxes' => 'Pick Many (checkboxes)',
						),
					),
				)
			)
			->render()
			->data();

		$this->promptForDefault( $field );
		$this->promptForOptions( $field );

		$index = count( $prompts->toArray() );

		$prompts->set( $index, $field->toArray() );
	}

	/**
	 * Walk a user through setting a default value.
	 *
	 * @param DataStore $field Field data
	 */
	protected function promptForDefault( DataStore $field ) {

		$supportsDefault = array( 'input' );

		// Optionally set default value, if the field type supports it
		if ( in_array( $field->get( 'type' ), $supportsDefault, true ) ) {
			if ( $this->prompt()->confirm( 'Set a default value?' ) ) {
				$field->set( 'default', $this->prompt()->input( 'Default value' ) );
			}
		}
	}

	/**
	 * Walk a user through setting field options.
	 *
	 * @param DataStore $field Field data
	 */
	protected function promptForOptions( DataStore $field ) {

		$supportsOptions = array( 'checkboxes', 'enum', 'radio' );

		// Check if a field requires options
		if ( in_array( $field->get( 'type' ), $supportsOptions, true ) ) {
			$options     = array();
			$prompt      = 'Provide a comma separates list of options (use "key > value" syntax to set keys)';
			$optionsList = $this->prompt()->input( $prompt );
			$items       = explode( ',', $optionsList );
			foreach ( $items as $index => $item ) {
				$parts = explode( '>', $item );
				$value = trim( array_pop( $parts ) );
				$key   = trim( array_shift( $parts ) );
				if ( is_null( $key ) ) {
					$key = $index;
				}
				$options[ $key ] = $value;
			}
			$field->set( 'options', $options );
		}
	}

}
