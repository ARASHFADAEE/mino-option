<?php
/**
 * Plugin Name:       Arash Settings Framework
 * Plugin URI:        https://github.com/ARASHFADAEE/mino-option
 * Description:       Production-ready reusable WordPress settings framework for plugins and themes.
 * Version:           1.0.0
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            Arash Fadaee
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       arash-settings-framework
 * Domain Path:       /languages
 *
 * @package Arash\SettingsFramework
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bundle metadata for multi-instance safety.
 *
 * Strategy: first loaded copy wins. Later copies of the same major version are
 * skipped. Incompatible major versions surface a WP_DEBUG / admin notice and
 * do not replace an already-booted runtime (registrations stay bound to the
 * first loaded implementation).
 */
$asf_bundle = array(
	'version'  => '1.0.0',
	'major'    => 1,
	'base_dir' => __DIR__,
	'file'     => __FILE__,
);

if ( ! isset( $GLOBALS['asf_loader_registry'] ) || ! is_array( $GLOBALS['asf_loader_registry'] ) ) {
	$GLOBALS['asf_loader_registry'] = array(
		'active'   => null,
		'skipped'  => array(),
		'conflicts' => array(),
	);
}

$asf_registry = &$GLOBALS['asf_loader_registry'];

if ( ! empty( $asf_registry['active'] ) ) {
	$asf_active = $asf_registry['active'];
	$asf_registry['skipped'][] = $asf_bundle;

	if ( (int) $asf_active['major'] !== (int) $asf_bundle['major'] ) {
		$asf_registry['conflicts'][] = array(
			'active'  => $asf_active,
			'attempt' => $asf_bundle,
		);
	}

	return;
}

$asf_registry['active'] = $asf_bundle;

if ( ! defined( 'ASF_VERSION' ) ) {
	define( 'ASF_VERSION', $asf_bundle['version'] );
}
if ( ! defined( 'ASF_FILE' ) ) {
	define( 'ASF_FILE', $asf_bundle['file'] );
}
if ( ! defined( 'ASF_PATH' ) ) {
	define( 'ASF_PATH', trailingslashit( $asf_bundle['base_dir'] ) );
}
if ( ! defined( 'ASF_URL' ) ) {
	define( 'ASF_URL', trailingslashit( plugins_url( '', ASF_FILE ) ) );
}
if ( ! defined( 'ASF_TEXTDOMAIN' ) ) {
	define( 'ASF_TEXTDOMAIN', 'arash-settings-framework' );
}

require_once ASF_PATH . 'src/Support/Autoloader.php';

\Arash\SettingsFramework\Support\Autoloader::register( ASF_PATH . 'src/' );

require_once ASF_PATH . 'src/functions.php';

\Arash\SettingsFramework\Framework::instance()->boot();
