<?php
/**
 * Settings page UI.
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework\Admin;

defined( 'ABSPATH' ) || exit;

use Arash\SettingsFramework\Registry\PageRegistry;
use Arash\SettingsFramework\Security\RequestGuard;
use Arash\SettingsFramework\Storage\OptionStore;

/**
 * Renders the branded settings shell.
 */
final class PageRenderer {

	/**
	 * @param string $page_id Page ID.
	 */
	public static function render( string $page_id ): void {
		$page = PageRegistry::instance()->get_page( $page_id );
		if ( ! $page ) {
			return;
		}

		if ( ! RequestGuard::user_can( $page_id ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'arash-settings-framework' ) );
		}

		$tabs      = PageRegistry::instance()->get_tabs( $page_id );
		$requested = isset( $_GET['asf_tab'] ) ? sanitize_key( wp_unslash( (string) $_GET['asf_tab'] ) ) : '';
		$active    = RequestGuard::sanitize_tab( $page_id, $requested );
		$values    = OptionStore::instance()->all( $page_id );
		$status    = isset( $_GET['asf_status'] ) ? sanitize_key( wp_unslash( (string) $_GET['asf_status'] ) ) : '';

		$template = ASF_PATH . 'templates/page.php';
		if ( is_readable( $template ) ) {
			include $template;
			return;
		}

		echo '<div class="wrap"><p>' . esc_html__( 'Settings template missing.', 'arash-settings-framework' ) . '</p></div>';
	}
}
