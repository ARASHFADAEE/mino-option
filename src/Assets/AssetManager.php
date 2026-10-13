<?php
/**
 * Conditional admin asset loading.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Assets;

defined( 'ABSPATH' ) || exit;

use Mino\Option\Admin\SaveHandler;
use Mino\Option\Fields\FieldTypeRegistry;
use Mino\Option\Registry\PageRegistry;
use Mino\Option\Security\RequestGuard;

/**
 * Enqueues CSS/JS only on registered Mino Option admin pages.
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
		$version = defined( 'MINO_VERSION' ) ? MINO_VERSION : '1.0.0';

		wp_enqueue_style(
			'mino-peyda',
			MINO_URL . 'assets/fonts/peyda/fontiran.css',
			array(),
			$version
		);

		wp_enqueue_style(
			'mino-admin',
			MINO_URL . 'assets/css/admin.css',
			array( 'dashicons', 'mino-peyda' ),
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
			'mino-admin',
			MINO_URL . 'assets/js/admin.js',
			$deps,
			$version,
			true
		);

		wp_localize_script(
			'mino-admin',
			'minoAdmin',
			array(
				'pageId'   => $page_id,
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'ajaxAction' => SaveHandler::AJAX_ACTION,
				'nonce'    => wp_create_nonce( RequestGuard::nonce_action( $page_id ) ),
				'i18n'     => array(
					'confirmReset' => __( 'Reset all settings on this page to defaults? This cannot be undone.', 'mino-option' ),
					'selectMedia'  => __( 'Select media', 'mino-option' ),
					'useMedia'     => __( 'Use selection', 'mino-option' ),
					'noFile'       => __( 'No file selected', 'mino-option' ),
					'saving'       => __( 'Saving settings…', 'mino-option' ),
					'resetting'    => __( 'Resetting settings…', 'mino-option' ),
					'saved'        => __( 'Settings saved.', 'mino-option' ),
					'resetDone'    => __( 'Settings reset to defaults.', 'mino-option' ),
					'saveFailed'   => __( 'Save failed. Please try again.', 'mino-option' ),
				),
				'rtl'      => is_rtl(),
			)
		);

		/**
		 * Fires after Mino Option assets are enqueued for a page.
		 *
		 * @param string $page_id     Page ID.
		 * @param array  $needed      Asset tags.
		 * @param string $hook_suffix Hook.
		 */
		do_action( 'mino_enqueue_assets', $page_id, $needed, $hook_suffix );
	}

	/**
	 * Detect which Mino Option page is being viewed.
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
