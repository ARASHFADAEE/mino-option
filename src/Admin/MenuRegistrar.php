<?php
/**
 * Admin menu registration.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Admin;

defined( 'ABSPATH' ) || exit;

use Mino\Option\Registry\PageRegistry;

/**
 * Registers top-level and submenu pages.
 */
final class MenuRegistrar {

	/**
	 * Hook callback for admin_menu.
	 */
	public static function register(): void {
		foreach ( PageRegistry::instance()->all_pages() as $page_id => $page ) {
			$callback = static function () use ( $page_id ): void {
				PageRenderer::render( $page_id );
			};

			if ( 'submenu' === $page['menu_type'] ) {
				add_submenu_page(
					(string) $page['parent_slug'],
					(string) $page['title'],
					(string) $page['menu_title'],
					(string) $page['capability'],
					(string) $page['menu_slug'],
					$callback,
					$page['position']
				);
			} else {
				add_menu_page(
					(string) $page['title'],
					(string) $page['menu_title'],
					(string) $page['capability'],
					(string) $page['menu_slug'],
					$callback,
					(string) $page['icon'],
					$page['position']
				);
			}
		}
	}
}
