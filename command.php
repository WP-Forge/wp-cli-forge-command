<?php

use WP_Forge\Command\Package;

if ( ! class_exists( 'WP_CLI' ) || ! is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	return;
}

require __DIR__ . '/vendor/autoload.php';

new Package(
	array(
		'base_command'             => 'forge',
		'template_config_filename' => 'config.json',
		'project_config_filename'  => '.wp-forge.json',
		'global_config_filename'   => '.wp-forge.json',
	)
);
