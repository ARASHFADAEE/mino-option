<?php
/**
 * Trusted HTML field (non-persistent).
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Renders developer-provided HTML only when explicitly marked trusted.
 *
 * Set `'trusted' => true` on the field config. Without it, content is escaped.
 */
final class HtmlField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'html';
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
		$content = (string) ( $field['content'] ?? $field['description'] ?? '' );
		echo '<div class="asf-html">';
		if ( ! empty( $field['trusted'] ) ) {
			// Intentionally unescaped — developers must opt in with trusted => true.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $content;
		} else {
			echo esc_html( $content );
		}
		echo '</div>';
	}
}
