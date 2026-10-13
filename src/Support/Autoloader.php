<?php
/**
 * PSR-4 style autoloader for Arash\SettingsFramework.
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework\Support;

defined( 'ABSPATH' ) || exit;

/**
 * Maps Arash\SettingsFramework\* to src/*.
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
		$prefix = 'Arash\\SettingsFramework\\';

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
