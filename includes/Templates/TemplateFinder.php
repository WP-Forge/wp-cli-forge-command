<?php

namespace WP_Forge\Command\Templates;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WP_Forge\Command\Concerns\CLIOutput;
use WP_Forge\Command\Concerns\DependencyInjection;
use WP_Forge\Command\Concerns\Filesystem;

/**
 * Class TemplateFinder
 */
class TemplateFinder {

	use CLIOutput, DependencyInjection, Filesystem;

	/**
	 * Find templates.
	 *
	 * @return array
	 */
	public function find() {
		$templates = array();

		$dirIterator    = new RecursiveDirectoryIterator( $this->container( 'template_dir' ), RecursiveDirectoryIterator::FOLLOW_SYMLINKS );
		$filterIterator = new TemplateFilterIterator( $dirIterator );
		$iterator       = new RecursiveIteratorIterator( $filterIterator, RecursiveIteratorIterator::SELF_FIRST );
		foreach ( $iterator as $item ) {

			$path   = $this->appendPath( $item->getPath(), $item->getFilename() );
			$config = $this->appendPath( $path, $this->container( 'template_config_filename' ) );

			if ( ! file_exists( $config ) ) {
				// No config file exists in this directory. Keep looking.
				continue;
			}

			$templates[] = $path;
		}

		return $templates;
	}

}
