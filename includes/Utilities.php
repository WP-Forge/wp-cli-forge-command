<?php

namespace WP_Forge\Command;

use WP_Forge\Helpers\Str;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

/**
 * Class Utilities
 */
class Utilities {

	/**
	 * Get initials from text.
	 *
	 * @param string $value Text to be abbreviated
	 *
	 * @return string
	 */
	public static function getInitials( $value ) {
		$initials = array();
		$words    = explode( '-', Str::kebab( $value ) );
		foreach ( $words as $word ) {
			$initials[] = substr( $word, 0, 1 );
		}
		return implode( $initials );
	}

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

}
