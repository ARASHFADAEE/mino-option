<?php
/**
 * Color picker field.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Fields;

defined( 'ABSPATH' ) || exit;

use Mino\Option\Validation\Sanitizer;

/**
 * WordPress iris color picker.
 */
final class ColorField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'color';
	}

	/**
	 * {@inheritdoc}
	 */
	public function required_assets(): array {
		return array( 'color' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( $value, array $field ) {
		return Sanitizer::hex_color( $value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $field, $value, array $context ): void {
		$attrs = $this->control_attrs(
			$field,
			$context,
			array(
				'type'  => 'text',
				'value' => (string) ( $value ?? '' ),
				'class' => 'mino-color-picker',
			)
		);
		echo '<input' . $this->attributes( $attrs ) . ' data-default-color="' . esc_attr( (string) ( $field['default'] ?? '' ) ) . '" />';
	}
}
