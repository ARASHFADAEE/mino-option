<?php
/**
 * Section heading (non-persistent).
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Visual heading — value is never saved.
 */
final class HeadingField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'heading';
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( $value, array $field ) {
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $field, $value, array $context ): void {
		echo '<h3 class="asf-heading">' . esc_html( (string) $field['title'] ) . '</h3>';
		if ( ! empty( $field['description'] ) ) {
			echo '<p class="asf-heading-desc">' . esc_html( (string) $field['description'] ) . '</p>';
		}
	}
}
