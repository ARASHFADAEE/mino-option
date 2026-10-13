<?php
/**
 * Type-aware sanitization pipeline.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Validation;

defined( 'ABSPATH' ) || exit;

use Mino\Option\Fields\FieldTypeRegistry;
use Mino\Option\Registry\PageRegistry;

/**
 * Sanitizes inbound settings against registered field definitions.
 */
final class Sanitizer {

	/**
	 * Sanitize a full submitted payload for a page.
	 *
	 * Only registered field IDs are kept. Missing checkbox/switch fields are
	 * coerced to false when present in the registered map and the submit
	 * includes the page marker.
	 *
	 * @param string               $page_id Page ID.
	 * @param array<string, mixed> $input   Raw input (already unslashed preferred).
	 * @return array<string, mixed>
	 */
	public static function sanitize_page( string $page_id, array $input ): array {
		$fields = PageRegistry::instance()->get_fields( $page_id );
		$out    = array();

		foreach ( $fields as $field_id => $field ) {
			$type = (string) $field['type'];

			// Checkboxes / switches absent from POST mean unchecked.
			if ( in_array( $type, array( 'checkbox', 'switch', 'switcher' ), true ) && ! array_key_exists( $field_id, $input ) ) {
				$out[ $field_id ] = false;
				continue;
			}

			if ( ! array_key_exists( $field_id, $input ) ) {
				continue;
			}

			$out[ $field_id ] = self::sanitize_field( $field, $input[ $field_id ] );
		}

		/**
		 * Filters sanitized page values.
		 *
		 * @param array  $out     Sanitized values.
		 * @param array  $input   Raw input.
		 * @param string $page_id Page ID.
		 */
		return apply_filters( 'mino_sanitize_page', $out, $input, $page_id );
	}

	/**
	 * @param array<string, mixed> $field Field config.
	 * @param mixed                $value Raw value.
	 * @return mixed
	 */
	public static function sanitize_field( array $field, $value ) {
		if ( isset( $field['sanitize'] ) && is_callable( $field['sanitize'] ) ) {
			$value = call_user_func( $field['sanitize'], $value, $field );
		} else {
			$handler = FieldTypeRegistry::instance()->get( (string) $field['type'] );
			if ( $handler ) {
				$value = $handler->sanitize( $value, $field );
			} else {
				$value = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
			}
		}

		/**
		 * Filters a sanitized field value.
		 *
		 * @param mixed $value Sanitized value.
		 * @param array $field Field config.
		 */
		return apply_filters( 'mino_sanitize_field', $value, $field );
	}

	/**
	 * Normalize hex color; empty string on invalid.
	 *
	 * @param mixed $raw Raw color.
	 */
	public static function hex_color( $raw ): string {
		if ( ! is_string( $raw ) ) {
			return '';
		}
		$raw = trim( $raw );
		if ( '' === $raw ) {
			return '';
		}
		if ( '#' !== $raw[0] ) {
			$raw = '#' . $raw;
		}
		$hex = sanitize_hex_color( $raw );
		return $hex ? $hex : '';
	}

	/**
	 * Validate attachment ID exists.
	 *
	 * @param mixed $raw Raw ID.
	 */
	public static function attachment_id( $raw ): int {
		$id = absint( $raw );
		if ( $id <= 0 ) {
			return 0;
		}
		$post = get_post( $id );
		if ( ! $post || 'attachment' !== $post->post_type ) {
			return 0;
		}
		return $id;
	}

	/**
	 * Allowlist select value(s).
	 *
	 * @param mixed                $value   Raw.
	 * @param array<string, mixed> $options Options map.
	 * @param bool                 $multi   Multiselect.
	 * @return string|array<int, string>
	 */
	public static function allowlisted( $value, array $options, bool $multi = false ) {
		$keys = array_map( 'strval', array_keys( $options ) );

		if ( $multi ) {
			if ( ! is_array( $value ) ) {
				return array();
			}
			$clean = array();
			foreach ( $value as $item ) {
				$item = is_scalar( $item ) ? (string) $item : '';
				if ( in_array( $item, $keys, true ) ) {
					$clean[] = $item;
				}
			}
			return array_values( array_unique( $clean ) );
		}

		$scalar = is_scalar( $value ) ? (string) $value : '';
		return in_array( $scalar, $keys, true ) ? $scalar : '';
	}

	/**
	 * Coerce to boolean without confusing "0".
	 *
	 * @param mixed $value Raw.
	 */
	public static function boolean( $value ): bool {
		if ( is_bool( $value ) ) {
			return $value;
		}
		if ( is_int( $value ) ) {
			return 1 === $value;
		}
		if ( is_string( $value ) ) {
			$v = strtolower( trim( $value ) );
			return in_array( $v, array( '1', 'true', 'yes', 'on' ), true );
		}
		return false;
	}
}
