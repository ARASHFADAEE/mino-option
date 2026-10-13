<?php
/**
 * UrlField.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * url field.
 */
final class UrlField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'url';
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( $value, array $field ) {
		if (!is_scalar($value)) return '';
		return esc_url_raw((string)$value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $field, $value, array $context ): void {
		$attrs = $this->control_attrs( $field, $context, array(
			'type'  => 'url',
			'value' => null === $value ? '' : (string) $value,
		) );
		echo '<input' . $this->attributes( $attrs ) . ' />';
	}
}
