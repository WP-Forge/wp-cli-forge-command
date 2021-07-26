<?php

namespace WP_Forge\Command;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

/**
 * Class Utilities
 */
class Utilities {

	/**
	 * Flatten an array using dot notation.
	 *
	 * @param array $array Array data
	 *
	 * @return array
	 */
	public static function flattenArray( array $array ) {
		$iterator = new RecursiveIteratorIterator( new RecursiveArrayIterator( $array ) );
		$result   = array();
		foreach ( $iterator as $value ) {
			$keys = array();
			foreach ( range( 0, $iterator->getDepth() ) as $depth ) {
				$keys[] = $iterator->getSubIterator( $depth )->key();
			}
			$result[ implode( '.', $keys ) ] = $value;
		}
		return $result;
	}

	/**
	 * Transform a value via a callable.
	 *
	 * @param mixed    $value Value to be transformed
	 * @param callable $callable Callable to be called.
	 *
	 * @return mixed
	 */
	public static function transform( $value, $callable ) {
		// Check for registered transforms, otherwise look for a valid callback
		if ( method_exists( Transforms::class, $callable ) ) {
			return call_user_func( array( Transforms::class, $callable ), $value );
		}
		return $callable( $value );
	}

	/**
	 * Apply one or more transforms to a value, in order.
	 *
	 * @param mixed          $value Value to be transformed
	 * @param array|callable $transforms Transforms to be called.
	 *
	 * @return mixed
	 */
	public static function applyTransforms( $value, $transforms ) {
		if ( is_string( $transforms ) ) {
			$transforms = (array) $transforms;
		}
		if ( ! empty( $transforms ) && is_array( $transforms ) ) {
			foreach ( $transforms as $callable ) {
				$value = self::transform( $value, $callable );
			}
		}
		return $value;
	}

}
