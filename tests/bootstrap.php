<?php
/**
 * PHPUnit bootstrap with lightweight WordPress stubs.
 *
 * Full WordPress PHPUnit suite is optional; these stubs allow core logic tests
 * without a WP install. See docs/architecture.md for WP test suite setup.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

$root = dirname( __DIR__ );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', $root . '/tests/wp-stub/' );
}

if ( ! defined( 'MINO_VERSION' ) ) {
	define( 'MINO_VERSION', '1.0.0' );
}
if ( ! defined( 'MINO_FILE' ) ) {
	define( 'MINO_FILE', $root . '/mino-option.php' );
}
if ( ! defined( 'MINO_PATH' ) ) {
	define( 'MINO_PATH', $root . '/' );
}
if ( ! defined( 'MINO_URL' ) ) {
	define( 'MINO_URL', 'http://example.test/wp-content/plugins/mino-option/' );
}
if ( ! defined( 'MINO_TEXTDOMAIN' ) ) {
	define( 'MINO_TEXTDOMAIN', 'mino-option' );
}

require_once $root . '/tests/wp-stubs.php';
require_once $root . '/src/Support/Autoloader.php';

\Mino\Option\Support\Autoloader::register( $root . '/src/' );
require_once $root . '/src/functions.php';

\Mino\Option\Framework::instance()->boot();
