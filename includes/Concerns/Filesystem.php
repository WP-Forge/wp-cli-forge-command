<?php

namespace WP_Forge\Command\Concerns;

/**
 * Trait Filesystem
 */
trait Filesystem {

	/**
	 * Dependency injection container
	 *
	 * @var \WP_Forge\Container\Container
	 */
	protected $container;

	/**
	 * Get the Filesystem instance for a given path.
	 *
	 * @param string $path Base file path
	 *
	 * @return \League\Flysystem\Filesystem
	 */
	protected function filesystem( $path ) {
		return $this->container->get( 'filesystem' )( $path );
	}

	/**
	 * Safely append to a path.
	 *
	 * @param string $path Path
	 * @param string $append Path to be appended
	 *
	 * @return string
	 */
	protected function appendPath( $path, $append ) {
		$args = func_get_args();
		array_shift( $args );
		$append = implode( DIRECTORY_SEPARATOR, $args );
		return rtrim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . $append;
	}

}
