<?php

namespace WP_Forge\Command\Commands;

use WP_Forge\Command\AbstractCommand;
use WP_Forge\Command\Concerns\Config;
use WP_Forge\Command\Concerns\DependencyInjection;

/**
 * Class InitCommand
 */
class InitCommand extends AbstractCommand {

	use DependencyInjection, Config;

	/**
	 * Command name.
	 *
	 * @var string
	 */
	const COMMAND = 'init';

	/**
	 * Generates a project config file.
	 *
	 * ## OPTIONS
	 *
	 * [--force=<value>]
	 * : Whether or not to force overwrite files.
	 * ---
	 * default: false
	 * options:
	 *   - true
	 *   - false
	 * ---
	 *
	 * @when before_wp_load
	 *
	 * @param array $args Command arguments
	 * @param array $options Command options
	 */
	public function __invoke( $args, $options ) {

		$this->init( $args, $options );

		$force     = $this->option( 'force', false );
		$hasConfig = $this->projectConfig()->hasConfig( getcwd() );

		// Ensure that we aren't blindly overwriting an existing config
		if ( ! $force && $hasConfig ) {
			$this->error( 'A project config already exists!', false );
			$overwrite = $this->prompt()->confirm( 'Do you want to force overwrite?' );
			if ( ! $overwrite ) {
				exit( 1 );
			}
		}

		$prompts = $this->prompts();

		$prompts->populate(
			array(
				array(
					'message'   => 'Project root path',
					'name'      => 'project_root',
					'type'      => 'input',
					'default'   => '.',
					'transform' => 'realpath',
				),
				array(
					'message' => 'Project Name',
					'name'    => 'project_name',
					'type'    => 'input',
				),
				array(
					'message' => 'Vendor Name',
					'name'    => 'vendor_name',
					'type'    => 'input',
				),
				array(
					'message'           => 'Package Name',
					'name'              => 'package_name',
					'type'              => 'input',
					'default'           => '{{ project_name }}',
					'transform_default' => '\WP_Forge\Helpers\Str::kebab',
				),
				array(
					'message' => 'Project text domain',
					'name'    => 'text_domain',
					'type'    => 'input',
					'default' => basename( getcwd() ),
				),
				array(
					'message'           => 'Project namespace',
					'name'              => 'namespace',
					'type'              => 'input',
					'default'           => '{{ project_name }}',
					'transform_default' => '\WP_Forge\Helpers\Str::studly',
				),
				array(
					'message'           => 'Long prefix',
					'name'              => 'prefixes.long',
					'type'              => 'input',
					'default'           => '{{ project_name }}',
					'transform_default' => '\WP_Forge\Helpers\Str::studly',
				),
				array(
					'message'           => 'Short prefix',
					'name'              => 'prefixes.short',
					'type'              => 'input',
					'default'           => '{{ project_name }}',
					'transform_default' => '\WP_Forge\Command\Utilities::getInitials',
				),
				array(
					'message'           => 'Function prefix',
					'name'              => 'prefixes.function',
					'type'              => 'input',
					'default'           => '{{ prefixes.short }}_',
					'transform_default' => 'strtolower',
				),
				array(
					'message'           => 'Constant prefix',
					'name'              => 'prefixes.constant',
					'type'              => 'input',
					'default'           => '{{ prefixes.short }}_',
					'transform_default' => 'strtoupper',
				),
				array(
					'message'           => 'Meta prefix (used for post meta, options, etc.)',
					'name'              => 'prefixes.meta',
					'type'              => 'input',
					'default'           => '{{ prefixes.short }}_',
					'transform_default' => 'strtolower',
				),
				array(
					'message'           => 'Slug prefix (used for post types and taxonomy names)',
					'name'              => 'prefixes.slug',
					'type'              => 'input',
					'default'           => '{{ prefixes.short }}-',
					'transform_default' => 'strtolower',
				),
			)
		);

		$prompts->render();

		$data = $prompts->data();

		$this->projectConfig()->withPath( $data->get( 'project_root' ) );

		// Don't add the project root to the config
		$data->forget( 'project_root' );

		$this->projectConfig()->withData( $data )->save();

	}

}
