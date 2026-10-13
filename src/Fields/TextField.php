<?php
/**
 * TextField.
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * text field.
 */
final class TextField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'text';
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
			'type'  => 'text',
			'value' => null === $value ? '' : (string) $value,
		) );
		echo '<input' . $this->attributes( $attrs ) . ' />';
	}
}
