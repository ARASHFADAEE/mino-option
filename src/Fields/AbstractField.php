<?php
/**
 * Shared field helpers.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Base field with attribute helpers.
 */
abstract class AbstractField implements FieldInterface {

	/**
	 * {@inheritdoc}
	 */
	public function required_assets(): array {
		return array();
	}

	/**
	 * Build HTML attributes string.
	 *
	 * @param array<string, mixed> $attrs Attributes.
	 */
	protected function attributes( array $attrs ): string {
		$html = '';
		foreach ( $attrs as $key => $val ) {
			if ( null === $val || false === $val ) {
				continue;
			}
			if ( true === $val ) {
				$html .= ' ' . esc_attr( (string) $key );
				continue;
			}
			$html .= sprintf( ' %s="%s"', esc_attr( (string) $key ), esc_attr( (string) $val ) );
		}
		return $html;
	}

	/**
	 * Merge field attributes with defaults.
	 *
	 * @param array<string, mixed> $field    Field.
	 * @param array<string, mixed> $context  Context.
	 * @param array<string, mixed> $defaults Defaults.
	 * @return array<string, mixed>
	 */
	protected function control_attrs( array $field, array $context, array $defaults = array() ): array {
		$attrs = array_merge(
			$defaults,
			array(
				'id'    => (string) ( $context['id'] ?? $field['id'] ),
				'name'  => (string) ( $context['name'] ?? $field['id'] ),
				'class' => trim( 'mino-input ' . (string) ( $field['class'] ?? '' ) ),
			),
			isset( $field['attributes'] ) && is_array( $field['attributes'] ) ? $field['attributes'] : array()
		);

		if ( ! empty( $field['placeholder'] ) ) {
			$attrs['placeholder'] = (string) $field['placeholder'];
		}
		if ( ! empty( $field['disabled'] ) ) {
			$attrs['disabled'] = true;
		}
		if ( ! empty( $field['readonly'] ) ) {
			$attrs['readonly'] = true;
		}
		if ( ! empty( $field['required'] ) ) {
			$attrs['required'] = true;
		}

		/**
		 * Filters control HTML attributes.
		 *
		 * @param array $attrs   Attributes.
		 * @param array $field   Field.
		 * @param array $context Context.
		 */
		return apply_filters( 'mino_field_attributes', $attrs, $field, $context );
	}

	/**
	 * Default sanitize: text field.
	 *
	 * @param mixed                $value Value.
	 * @param array<string, mixed> $field Field.
	 * @return string
	 */
	public function sanitize( $value, array $field ) {
		if ( is_array( $value ) ) {
			return '';
		}
		return sanitize_text_field( (string) $value );
	}
}
