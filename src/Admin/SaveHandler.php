<?php
/**
 * Form save / reset handler.
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework\Admin;

defined( 'ABSPATH' ) || exit;

use Arash\SettingsFramework\Registry\PageRegistry;
use Arash\SettingsFramework\Security\RequestGuard;
use Arash\SettingsFramework\Storage\OptionStore;
use Arash\SettingsFramework\Validation\Sanitizer;
use Arash\SettingsFramework\Validation\Validator;

/**
 * Processes POST saves on admin_init.
 */
final class SaveHandler {

	/**
	 * Handle save or reset when posted.
	 */
	public static function maybe_handle(): void {
		if ( empty( $_POST['asf_action'] ) || empty( $_POST['asf_page_id'] ) ) {
			return;
		}

		$page_id = sanitize_key( wp_unslash( (string) $_POST['asf_page_id'] ) );
		$action  = sanitize_key( wp_unslash( (string) $_POST['asf_action'] ) );

		if ( ! PageRegistry::instance()->has_page( $page_id ) ) {
			return;
		}

		if ( ! RequestGuard::authorize_write( $page_id ) ) {
			wp_die(
				esc_html__( 'You are not allowed to save these settings.', 'arash-settings-framework' ),
				esc_html__( 'Authorization failed', 'arash-settings-framework' ),
				array( 'response' => 403 )
			);
		}

		$page = PageRegistry::instance()->get_page( $page_id );
		$slug = $page ? (string) $page['menu_slug'] : $page_id;

		if ( 'reset' === $action ) {
			OptionStore::instance()->reset( $page_id );
			self::redirect( $slug, 'reset' );
			return;
		}

		if ( 'save' !== $action ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per-field below.
		$raw = wp_unslash( $_POST );
		unset( $raw['asf_action'], $raw['asf_page_id'], $raw['asf_nonce'], $raw['_wp_http_referer'] );

		// Gallery fields may arrive as comma-separated strings.
		$fields = PageRegistry::instance()->get_fields( $page_id );
		foreach ( $fields as $fid => $field ) {
			if ( 'gallery' === $field['type'] && isset( $raw[ $fid ] ) && is_string( $raw[ $fid ] ) ) {
				$raw[ $fid ] = array_filter( array_map( 'absint', explode( ',', $raw[ $fid ] ) ) );
			}
		}

		$sanitized = Sanitizer::sanitize_page( $page_id, $raw );

		// Drop non-persistent display fields.
		foreach ( $fields as $fid => $field ) {
			if ( in_array( (string) $field['type'], array( 'heading', 'notice', 'html' ), true ) ) {
				unset( $sanitized[ $fid ] );
			}
			if ( null === ( $sanitized[ $fid ] ?? null ) && isset( $sanitized[ $fid ] ) ) {
				unset( $sanitized[ $fid ] );
			}
		}

		$errors = Validator::validate_page( $page_id, $sanitized );
		if ( ! empty( $errors ) ) {
			set_transient(
				self::notice_key( $page_id ),
				array(
					'type'   => 'error',
					'errors' => $errors,
				),
				45
			);
			self::redirect( $slug, 'error' );
			return;
		}

		OptionStore::instance()->save( $page_id, $sanitized );
		self::redirect( $slug, 'saved' );
	}

	/**
	 * @param string $page_id Page ID.
	 */
	public static function notice_key( string $page_id ): string {
		return 'asf_notice_' . $page_id . '_' . get_current_user_id();
	}

	/**
	 * @param string $menu_slug Menu slug.
	 * @param string $status    Status flag.
	 */
	private static function redirect( string $menu_slug, string $status ): void {
		$url = add_query_arg(
			array(
				'page'       => $menu_slug,
				'asf_status' => $status,
			),
			admin_url( 'admin.php' )
		);
		wp_safe_redirect( $url );
		exit;
	}
}
