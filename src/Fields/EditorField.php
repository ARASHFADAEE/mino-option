<?php
/**
 * Rich text editor field.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * wp_editor integration. Type aliases: editor, tinymce, richtext.
 */
final class EditorField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'editor';
	}

	/**
	 * {@inheritdoc}
	 */
	public function required_assets(): array {
		return array( 'editor' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( $value, array $field ) {
		if ( is_array( $value ) ) {
			return '';
		}
		return wp_kses_post( (string) $value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $field, $value, array $context ): void {
		$id   = (string) ( $context['id'] ?? $field['id'] );
		$name = (string) ( $context['name'] ?? $field['id'] );
		// Editor IDs must be unique and alphanumeric.
		$editor_id = preg_replace( '/[^a-zA-Z0-9_]/', '_', $id );

		$settings = array(
			'textarea_name' => $name,
			'textarea_rows' => (int) ( $field['rows'] ?? 8 ),
			'media_buttons' => ! empty( $field['media_buttons'] ),
			'teeny'         => ! empty( $field['teeny'] ),
			'quicktags'     => true,
		);

		/**
		 * Filters wp_editor settings for an Mino Option editor field.
		 *
		 * @param array $settings Settings.
		 * @param array $field    Field config.
		 * @param array $context  Context.
		 */
		$settings = apply_filters( 'mino_editor_settings', $settings, $field, $context );

		echo '<div class="mino-editor">';
		wp_editor( (string) ( $value ?? '' ), $editor_id, $settings );
		echo '</div>';
	}
}
