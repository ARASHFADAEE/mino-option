<?php
/**
 * Public procedural helpers.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

defined( 'ABSPATH' ) || exit;

use Mino\Option\Framework;
use Mino\Option\Storage\OptionStore;

if ( ! function_exists( 'mino_get_option' ) ) {
	/**
	 * Retrieve a single setting value.
	 *
	 * Missing keys return $default (which falls back to the field default when
	 * null is passed and a field is registered). Boolean false and string "0"
	 * are valid stored values and are returned as-is.
	 *
	 * @param string     $page_id Settings page ID.
	 * @param string     $field_id Field ID.
	 * @param mixed|null $default  Explicit default; null uses registered field default.
	 * @return mixed
	 */
	function mino_get_option( string $page_id, string $field_id, $default = null ) {
		return OptionStore::instance()->get( $page_id, $field_id, $default );
	}
}

if ( ! function_exists( 'mino_get_options' ) ) {
	/**
	 * Retrieve all settings for a page (merged with registered defaults).
	 *
	 * @param string $page_id Settings page ID.
	 * @return array<string, mixed>
	 */
	function mino_get_options( string $page_id ): array {
		return OptionStore::instance()->all( $page_id );
	}
}

if ( ! function_exists( 'mino_attachment_url' ) ) {
	/**
	 * Resolve an attachment ID to a URL.
	 *
	 * @param int|string $attachment_id Attachment ID.
	 * @param string     $size          Image size.
	 * @return string
	 */
	function mino_attachment_url( $attachment_id, string $size = 'full' ): string {
		$id = absint( $attachment_id );
		if ( $id <= 0 ) {
			return '';
		}

		$url = wp_get_attachment_image_url( $id, $size );
		if ( ! $url ) {
			$url = wp_get_attachment_url( $id );
		}

		return $url ? (string) $url : '';
	}
}

if ( ! function_exists( 'mino_framework' ) ) {
	/**
	 * @return Framework
	 */
	function mino_framework(): Framework {
		return Framework::instance();
	}
}

if ( ! function_exists( 'mino_loader_info' ) ) {
	/**
	 * Active / skipped bundle metadata for diagnostics.
	 *
	 * @return array<string, mixed>
	 */
	function mino_loader_info(): array {
		return isset( $GLOBALS['mino_loader_registry'] ) && is_array( $GLOBALS['mino_loader_registry'] )
			? $GLOBALS['mino_loader_registry']
			: array();
	}
}
