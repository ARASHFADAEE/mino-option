<?php
/**
 * EmailField.
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * email field.
 */
final class EmailField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'email';
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( $value, array $field ) {
		if (!is_scalar($value)) return '';
		$email = sanitize_email((string)$value);
		return is_email($email) ? $email : '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $field, $value, array $context ): void {
		$attrs = $this->control_attrs( $field, $context, array(
			'type'  => 'email',
			'value' => null === $value ? '' : (string) $value,
		) );
		echo '<input' . $this->attributes( $attrs ) . ' />';
	}
}
