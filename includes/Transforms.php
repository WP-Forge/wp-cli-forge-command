<?php

namespace WP_Forge\Command;

use WP_Forge\Helpers\Str;

/**
 * Class Transforms
 */
class Transforms {

	/**
	 * Abbreviate a string by getting the first letter of each word.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function abbreviate( $value ) {
		return Utilities::getInitials( $value );
	}

	/**
	 * Convert a string to camel case.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function camelCase( $value ) {
		return Str::camel( $value );
	}

	/**
	 * Convert a string to kebab case.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function kebabCase( $value ) {
		return Str::kebab( $value );
	}

	/**
	 * Convert a string to lowercase.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function lowercase( $value ) {
		return Str::lower( $value );
	}

	/**
	 * Convert a string to pascal case.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function pascalCase( $value ) {
		return Str::studly( $value );
	}

	/**
	 * Get the plural form of a word.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function plural( $value ) {
		return Str::plural( $value );
	}

	/**
	 * Get the singular form of a word.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function singular( $value ) {
		return Str::singular( $value );
	}

	/**
	 * Convert a string to snake case.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function snakeCase( $value ) {
		return Str::snake( $value );
	}

	/**
	 * Convert a string to title case.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function titleCase( $value ) {
		return Str::title( $value );
	}

	/**
	 * Convert a string to uppercase.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function uppercase( $value ) {
		return Str::upper( $value );
	}

	/**
	 * Convert string to proper words with spaces.
	 *
	 * @param string $value Value to be transformed
	 *
	 * @return string
	 */
	public static function words( $value ) {
		return implode( ' ', array_filter( preg_split( '/(?=[A-Z])/', Str::studly( $value ) ) ) );
	}

}