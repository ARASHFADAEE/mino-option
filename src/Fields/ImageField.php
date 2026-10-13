<?php
/**
 * Image field.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Image-only media picker (stores attachment ID).
 */
final class ImageField extends MediaField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'image';
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $field, $value, array $context ): void {
		$field['library'] = 'image';
		parent::render( $field, $value, $context );
	}
}
