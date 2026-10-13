<?php
/**
 * Plugin Name:       Mino Option
 * Plugin URI:        https://github.com/ARASHFADAEE/mino-option
 * Description:       Production-ready reusable WordPress settings framework for plugins and themes.
 * Version:           1.0.0
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            Arash Fadaee
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       mino-option
 * Domain Path:       /languages
 *
 * @package Mino\Option
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
$mino_bundle = array(
	'version'  => '1.0.0',
	'major'    => 1,
	'base_dir' => __DIR__,
	'file'     => __FILE__,
);

if ( ! isset( $GLOBALS['mino_loader_registry'] ) || ! is_array( $GLOBALS['mino_loader_registry'] ) ) {
	$GLOBALS['mino_loader_registry'] = array(
		'active'   => null,
		'skipped'  => array(),
		'conflicts' => array(),
	);
}

$mino_registry = &$GLOBALS['mino_loader_registry'];

if ( ! empty( $mino_registry['active'] ) ) {
	$mino_active = $mino_registry['active'];
	$mino_registry['skipped'][] = $mino_bundle;

	if ( (int) $mino_active['major'] !== (int) $mino_bundle['major'] ) {
		$mino_registry['conflicts'][] = array(
			'active'  => $mino_active,
			'attempt' => $mino_bundle,
		);
	}

	return;
}

$mino_registry['active'] = $mino_bundle;

if ( ! defined( 'MINO_VERSION' ) ) {
	define( 'MINO_VERSION', $mino_bundle['version'] );
}
if ( ! defined( 'MINO_FILE' ) ) {
	define( 'MINO_FILE', $mino_bundle['file'] );
}
if ( ! defined( 'MINO_PATH' ) ) {
	define( 'MINO_PATH', trailingslashit( $mino_bundle['base_dir'] ) );
}
if ( ! defined( 'MINO_URL' ) ) {
	define( 'MINO_URL', trailingslashit( plugins_url( '', MINO_FILE ) ) );
}
if ( ! defined( 'MINO_TEXTDOMAIN' ) ) {
	define( 'MINO_TEXTDOMAIN', 'mino-option' );
}

require_once MINO_PATH . 'src/Support/Autoloader.php';

\Mino\Option\Support\Autoloader::register( MINO_PATH . 'src/' );

require_once MINO_PATH . 'src/functions.php';

\Mino\Option\Framework::instance()->boot();
