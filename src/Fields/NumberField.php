<?php
/**
 * NumberField.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * number field.
 */
final class NumberField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'number';
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( $value, array $field ) {
		if (is_array($value)) return null;
		if ('' === $value || null === $value) return null;
		if (isset($field['step']) && false !== strpos((string)$field['step'], '.')) {
			return (float) $value;
		}
		return (int) $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $field, $value, array $context ): void {
		$attrs = $this->control_attrs( $field, $context, array(
			'type'  => 'number',
			'value' => null === $value ? '' : (string) $value,
		) );
		if (isset($field['min']) && null !== $field['min']) { $attrs['min'] = $field['min']; }
		if (isset($field['max']) && null !== $field['max']) { $attrs['max'] = $field['max']; }
		if (isset($field['step']) && null !== $field['step']) { $attrs['step'] = $field['step']; }
		echo '<input' . $this->attributes( $attrs ) . ' />';
	}
}
