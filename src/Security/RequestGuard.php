<?php
/**
 * Authorization and nonce helpers.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Security;

defined( 'ABSPATH' ) || exit;

use Mino\Option\Registry\PageRegistry;

/**
 * Capability + nonce checks for settings writes and privileged renders.
 */
final class RequestGuard {

	/**
	 * Nonce action prefix.
	 */
	public const NONCE_ACTION = 'mino_save_settings';

	/**
	 * @param string $page_id Page ID.
	 */
	public static function nonce_action( string $page_id ): string {
		return self::NONCE_ACTION . '_' . $page_id;
	}

	/**
	 * @param string $page_id Page ID.
	 */
	public static function nonce_field( string $page_id ): void {
		wp_nonce_field( self::nonce_action( $page_id ), 'mino_nonce' );
	}

	/**
	 * @param string $page_id Page ID.
	 */
	public static function verify_nonce( string $page_id ): bool {
		$nonce = isset( $_REQUEST['mino_nonce'] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST['mino_nonce'] ) ) : '';
		return (bool) wp_verify_nonce( $nonce, self::nonce_action( $page_id ) );
	}

	/**
	 * @param string $page_id Page ID.
	 */
	public static function user_can( string $page_id ): bool {
		$page = PageRegistry::instance()->get_page( $page_id );
		if ( ! $page ) {
			return false;
		}

		$cap = (string) $page['capability'];

		/**
		 * Filters capability check for a page.
		 *
		 * @param bool   $allowed Whether allowed.
		 * @param string $page_id Page ID.
		 * @param string $cap     Capability.
		 */
		return (bool) apply_filters( 'mino_user_can', current_user_can( $cap ), $page_id, $cap );
	}

	/**
	 * Full write authorization: capability + nonce + registered page.
	 *
	 * @param string $page_id Page ID.
	 */
	public static function authorize_write( string $page_id ): bool {
		if ( ! PageRegistry::instance()->has_page( $page_id ) ) {
			return false;
		}

		if ( ! self::user_can( $page_id ) ) {
			return false;
		}

		return self::verify_nonce( $page_id );
	}

	/**
	 * Sanitize active tab query param against registered tabs.
	 *
	 * @param string $page_id Page ID.
	 * @param string $requested Requested tab ID.
	 */
	public static function sanitize_tab( string $page_id, string $requested ): string {
		$tabs = PageRegistry::instance()->get_tabs( $page_id );
		if ( empty( $tabs ) ) {
			return '';
		}

		if ( isset( $tabs[ $requested ] ) ) {
			return $requested;
		}

		return (string) array_key_first( $tabs );
	}

	/**
	 * Resolve a custom tab template safely within an allowlisted base directory.
	 *
	 * @param string $template Relative or absolute path from config.
	 * @param string $base     Allowlisted base directory.
	 * @return string|null Real path or null.
	 */
	public static function resolve_template( string $template, string $base ): ?string {
		$base_real = realpath( $base );
		if ( false === $base_real || ! is_dir( $base_real ) ) {
			return null;
		}

		$candidate = $template;
		if ( 0 !== strpos( $template, '/' ) && ! preg_match( '#^[A-Za-z]:[\\\\/]#', $template ) ) {
			$candidate = trailingslashit( $base_real ) . ltrim( $template, '/\\' );
		}

		$real = realpath( $candidate );
		if ( false === $real || ! is_readable( $real ) ) {
			return null;
		}

		$base_prefix = trailingslashit( $base_real );
		if ( 0 !== strpos( $real, $base_prefix ) && $real !== $base_real ) {
			return null;
		}

		return $real;
	}
}
