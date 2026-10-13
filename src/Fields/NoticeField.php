<?php
/**
 * Informational notice (non-persistent).
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Admin-style notice block.
 */
final class NoticeField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'notice';
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
		$type = isset( $field['notice_type'] ) ? sanitize_key( (string) $field['notice_type'] ) : 'info';
		if ( ! in_array( $type, array( 'info', 'success', 'warning', 'error' ), true ) ) {
			$type = 'info';
		}
		echo '<div class="asf-notice asf-notice--' . esc_attr( $type ) . '" role="status">';
		echo '<p>' . esc_html( (string) ( $field['description'] ?: $field['title'] ) ) . '</p>';
		echo '</div>';
	}
}
