<?php
/**
 * PHPUnit bootstrap with lightweight WordPress stubs.
 *
 * Full WordPress PHPUnit suite is optional; these stubs allow core logic tests
 * without a WP install. See docs/architecture.md for WP test suite setup.
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

$root = dirname( __DIR__ );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', $root . '/tests/wp-stub/' );
}

if ( ! defined( 'ASF_VERSION' ) ) {
	define( 'ASF_VERSION', '1.0.0' );
}
if ( ! defined( 'ASF_FILE' ) ) {
	define( 'ASF_FILE', $root . '/arash-settings-framework.php' );
}
if ( ! defined( 'ASF_PATH' ) ) {
	define( 'ASF_PATH', $root . '/' );
}
if ( ! defined( 'ASF_URL' ) ) {
	define( 'ASF_URL', 'http://example.test/wp-content/plugins/arash-settings-framework/' );
}
if ( ! defined( 'ASF_TEXTDOMAIN' ) ) {
	define( 'ASF_TEXTDOMAIN', 'arash-settings-framework' );
}

require_once $root . '/tests/wp-stubs.php';
require_once $root . '/src/Support/Autoloader.php';

\Arash\SettingsFramework\Support\Autoloader::register( $root . '/src/' );
require_once $root . '/src/functions.php';

\Arash\SettingsFramework\Framework::instance()->boot();
