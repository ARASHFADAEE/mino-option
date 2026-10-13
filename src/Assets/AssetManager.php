<?php
/**
 * Conditional admin asset loading.
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework\Assets;

defined( 'ABSPATH' ) || exit;

use Arash\SettingsFramework\Fields\FieldTypeRegistry;
use Arash\SettingsFramework\Registry\PageRegistry;

/**
 * Enqueues CSS/JS only on registered ASF admin pages.
 */
final class AssetManager {

	/**
	 * @param string $hook_suffix Current admin page hook.
	 */
	public static function enqueue( string $hook_suffix ): void {
		$page_id = self::detect_page_id();
		if ( null === $page_id ) {
			return;
		}

		$fields  = PageRegistry::instance()->get_fields( $page_id );
		$needed  = FieldTypeRegistry::instance()->collect_assets( $fields );
		$version = defined( 'ASF_VERSION' ) ? ASF_VERSION : '1.0.0';

		wp_enqueue_style(
			'asf-admin',
			ASF_URL . 'assets/css/admin.css',
			array( 'dashicons' ),
			$version
		);

		$deps = array();
		if ( in_array( 'sortable', $needed, true ) || in_array( 'repeater', $needed, true ) ) {
			$deps[] = 'jquery-ui-sortable';
		}
		if ( in_array( 'color', $needed, true ) ) {
			wp_enqueue_style( 'wp-color-picker' );
			$deps[] = 'wp-color-picker';
		}
		if ( in_array( 'media', $needed, true ) ) {
			wp_enqueue_media();
		}
		if ( in_array( 'editor', $needed, true ) ) {
			wp_enqueue_editor();
		}

		wp_enqueue_script(
			'asf-admin',
			ASF_URL . 'assets/js/admin.js',
			$deps,
			$version,
			true
		);

		wp_localize_script(
			'asf-admin',
			'asfAdmin',
			array(
				'pageId'  => $page_id,
				'i18n'    => array(
					'confirmReset' => __( 'Reset all settings on this page to defaults? This cannot be undone.', 'arash-settings-framework' ),
					'selectMedia'  => __( 'Select media', 'arash-settings-framework' ),
					'useMedia'     => __( 'Use selection', 'arash-settings-framework' ),
					'noFile'       => __( 'No file selected', 'arash-settings-framework' ),
				),
				'rtl'     => is_rtl(),
			)
		);

		/**
		 * Fires after ASF assets are enqueued for a page.
		 *
		 * @param string $page_id     Page ID.
		 * @param array  $needed      Asset tags.
		 * @param string $hook_suffix Hook.
		 */
		do_action( 'asf_enqueue_assets', $page_id, $needed, $hook_suffix );
	}

	/**
	 * Detect which ASF page is being viewed.
	 */
	private static function detect_page_id(): ?string {
		if ( empty( $_GET['page'] ) ) {
			return null;
		}
		$slug = sanitize_key( wp_unslash( (string) $_GET['page'] ) );
		foreach ( PageRegistry::instance()->all_pages() as $page_id => $page ) {
			if ( $slug === (string) $page['menu_slug'] ) {
				return $page_id;
			}
		}
		return null;
	}
}
