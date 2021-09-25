<?php

namespace WP_Forge\Command\Directives;

use WP_Forge\Command\Concerns\Filesystem;
use WP_Forge\Command\Concerns\Registry;

/**
 * Class Execute
 */
class Execute extends AbstractDirective {

	use Filesystem, Registry;

	/**
	 * Path to file containing script.
	 *
	 * @var string
	 */
	protected $file;

	/**
	 * Initialize properties for the directive.
	 *
	 * @param array $args Directive arguments.
	 */
	public function initialize( array $args ) {
		$this->file = data_get( $args, 'file' );
	}

	/**
	 * Validate the directive properties.
	 */
	public function validate() {
		if ( empty( $this->file ) ) {
			$this->error( 'File is missing!' );
		}
	}

	/**
	 * Execute the directive.
	 */
	public function execute() {

		$path = $this->appendPath(
			$this->container( 'template_dir' ),
			$this->registry()->get( 'template' ),
			$this->file
		);

		// Make the container available to scripts
		$container = $this->container;

		require $path;

	}

}
