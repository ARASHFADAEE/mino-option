<?php
/**
 * Custom callback field.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Delegates rendering to a callable. Sanitization uses optional sanitize callback
 * or falls back to text sanitization for scalar values.
 */
final class CallbackField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'callback';
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( $value, array $field ) {
		if ( isset( $field['sanitize'] ) && is_callable( $field['sanitize'] ) ) {
			return call_user_func( $field['sanitize'], $value, $field );
		}
		if ( is_array( $value ) ) {
			return array_map(
				static function ( $item ) {
					return is_scalar( $item ) ? sanitize_text_field( (string) $item ) : '';
				},
				$value
			);
		}
		return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $field, $value, array $context ): void {
		$cb = $field['renderer'] ?? $field['callback'] ?? null;
		if ( ! is_callable( $cb ) ) {
			echo '<p class="mino-error">' . esc_html__( 'Callback field is missing a callable.', 'mino-option' ) . '</p>';
			return;
		}
		call_user_func( $cb, $field, $value, $context );
	}
}
