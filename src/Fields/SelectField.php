<?php
/**
 * Select field.
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework\Fields;

defined( 'ABSPATH' ) || exit;

use Arash\SettingsFramework\Validation\Sanitizer;

/**
 * Single-select dropdown.
 */
final class SelectField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'select';
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( $value, array $field ) {
		$options = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
		return Sanitizer::allowlisted( $value, $options, false );
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $field, $value, array $context ): void {
		$attrs   = $this->control_attrs( $field, $context, array( 'class' => 'asf-select' ) );
		$options = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
		unset( $attrs['value'], $attrs['type'] );
		echo '<select' . $this->attributes( $attrs ) . '>';
		foreach ( $options as $opt_value => $label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( (string) $opt_value ),
				selected( (string) $value, (string) $opt_value, false ),
				esc_html( (string) $label )
			);
		}
		echo '</select>';
	}
}
