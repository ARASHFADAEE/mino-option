<?php
/**
 * PasswordField.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * password field.
 */
final class PasswordField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'password';
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( $value, array $field ) {
		return parent::sanitize($value, $field);
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $field, $value, array $context ): void {
		$attrs = $this->control_attrs( $field, $context, array(
			'type'  => 'password',
			'value' => null === $value ? '' : (string) $value,
		) );
		echo '<input' . $this->attributes( $attrs ) . ' />';
	}
}
