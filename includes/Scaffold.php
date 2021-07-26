<?php

namespace WP_Forge\Command;

use WP_Forge\Command\Concerns\CLIOutput;
use WP_Forge\Command\Concerns\DependencyInjection;
use WP_Forge\Command\Concerns\Filesystem;
use WP_Forge\Command\Concerns\Mustache;
use WP_Forge\Container\Container;

/**
 * Class Scaffold
 */
class Scaffold {

	use DependencyInjection, CLIOutput, Mustache, Filesystem;

	/**
	 * The source directory. Defaults to the templates directory.
	 *
	 * @var string
	 */
	protected $sourceDir;

	/**
	 * The target directory. Defaults to the current working directory.
	 *
	 * @var string
	 */
	protected $targetDir;

	/**
	 * Source Filesystem instance.
	 *
	 * @var \League\Flysystem\Filesystem
	 */
	protected $source;

	/**
	 * Target Filesystem instance.
	 *
	 * @var \League\Flysystem\Filesystem
	 */
	protected $target;

	/**
	 * Whether or not to force overwrite files.
	 *
	 * @var bool
	 */
	protected $overwrite = false;

	/**
	 * Scaffold constructor.
	 *
	 * @param Container $container Container instance
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
		$this->withSourceDir( $container->get( 'template_dir' ) );
		$this->withTargetDir( getcwd() );
	}

	/**
	 * Set the source directory.
	 *
	 * @param string $source Source directory
	 *
	 * @return $this
	 */
	public function withSourceDir( $source ) {
		$this->sourceDir = $source;
		$this->source    = $this->filesystem( $source );
		return $this;
	}

	/**
	 * Set the target directory.
	 *
	 * @param string $target Target directory
	 *
	 * @return $this
	 */
	public function withTargetDir( $target ) {
		$this->targetDir = $target;
		$this->target    = $this->filesystem( $target );
		return $this;
	}

	/**
	 * Set whether or not to force overwrite files.
	 *
	 * @param boolean $overwrite Whether or not to overwrite files.
	 *
	 * @return $this
	 */
	public function overwrite( $overwrite = true ) {
		$this->overwrite = $overwrite;
		return $this;
	}

	/**
	 * Copy a directory, replacing placeholders as needed.
	 *
	 * @param string $from Source path
	 * @param string $to Target path
	 * @param array  $data Data for replacements
	 *
	 * @return $this
	 */
	public function copyDir( $from, $to, array $data = array() ) {

		$files = $this->source->listContents( $from )->toArray();

		foreach ( $files as $item ) {
			$targetPath = $this->appendPath( $to, $item->path() );

			if ( $item->isFile() ) {
				$this->copyFile( $item->path(), $targetPath, $data );
			}
			if ( $item->isDir() ) {
				$base = strstr( $targetPath, $item->path(), true );
				$this->copyDir( $item->path(), rtrim( $base, DIRECTORY_SEPARATOR ), $data );
			}
		}
		return $this;
	}

	/**
	 * Copy a file, replacing placeholders as needed.
	 *
	 * @param string $from Source path
	 * @param string $to Target path
	 * @param array  $data Data for replacements
	 *
	 * @return $this
	 */
	public function copyFile( $from, $to, array $data = array() ) {
		$file = $this->appendPath( $this->targetDir, $to );
		if ( file_exists( $file ) && ! $this->overwrite ) {
			$this->cli()->lightGray( 'File exists, skipping: ' . $to );
			return $this;
		}
		if ( ! file_exists( $this->appendPath( $this->sourceDir, $from ) ) ) {
			$this->error( 'Unable to locate file: ' . $this->appendPath( $this->sourceDir, $from ) );
		}
		$raw     = $this->source->read( $from );
		$content = $this->replace( $raw, $data );
		$this->target->write( $to, $content );
		$this->out( "Created <green>{$to}</green>" );
		return $this;
	}

}
