<?php
/**
 * PSR-4 style autoloader for Mino\Option.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Maps Mino\Option\* to src/*.
 */
final class Autoloader {

	/**
	 * @var string
	 */
	private static $base_dir = '';

	/**
	 * Register the autoloader once.
	 *
	 * @param string $base_dir Absolute path to src/.
	 */
	public static function register( string $base_dir ): void {
		if ( '' !== self::$base_dir ) {
			return;
		}

		self::$base_dir = trailingslashit( $base_dir );

		spl_autoload_register( array( __CLASS__, 'load' ) );
	}

	/**
	 * @param string $class Fully-qualified class name.
	 */
	public static function load( string $class ): void {
		$prefix = 'Mino\\Option\\';

		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
		$relative = str_replace( '\\', '/', $relative );
		$file     = self::$base_dir . $relative . '.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
}
