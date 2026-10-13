<?php
/**
 * Textarea field.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Multi-line text.
 */
final class TextareaField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'textarea';
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( $value, array $field ) {
		if ( is_array( $value ) ) {
			return '';
		}
		return sanitize_textarea_field( (string) $value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $field, $value, array $context ): void {
		$attrs = $this->control_attrs(
			$field,
			$context,
			array(
				'rows'  => (int) ( $field['rows'] ?? 4 ),
				'class' => trim( 'mino-textarea ' . (string) ( $field['class'] ?? '' ) ),
			)
		);
		unset( $attrs['value'] );
		echo '<textarea' . $this->attributes( $attrs ) . '>' . esc_textarea( (string) ( $value ?? '' ) ) . '</textarea>';
	}
}
