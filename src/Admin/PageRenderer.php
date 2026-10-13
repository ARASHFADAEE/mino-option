<?php
/**
 * Settings page UI.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Admin;

defined( 'ABSPATH' ) || exit;

use Mino\Option\Registry\PageRegistry;
use Mino\Option\Security\RequestGuard;
use Mino\Option\Storage\OptionStore;

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
			wp_die( esc_html__( 'You do not have permission to access this page.', 'mino-option' ) );
		}

		$tabs      = PageRegistry::instance()->get_tabs( $page_id );
		$requested = isset( $_GET['mino_tab'] ) ? sanitize_key( wp_unslash( (string) $_GET['mino_tab'] ) ) : '';
		$active    = RequestGuard::sanitize_tab( $page_id, $requested );
		$values    = OptionStore::instance()->all( $page_id );
		$status    = isset( $_GET['mino_status'] ) ? sanitize_key( wp_unslash( (string) $_GET['mino_status'] ) ) : '';

		$template = MINO_PATH . 'templates/page.php';
		if ( is_readable( $template ) ) {
			include $template;
			return;
		}

		echo '<div class="wrap"><p>' . esc_html__( 'Settings template missing.', 'mino-option' ) . '</p></div>';
	}
}
