<?php
/**
 * Media field.
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework\Fields;

defined( 'ABSPATH' ) || exit;

use Arash\SettingsFramework\Validation\Sanitizer;

/**
 * Attachment ID picker via wp.media.
 */
class MediaField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'media';
	}

	/**
	 * {@inheritdoc}
	 */
	public function required_assets(): array {
		return array( 'media' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( $value, array $field ) {
		return Sanitizer::attachment_id( $value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $field, $value, array $context ): void {
		$id    = (string) ( $context['id'] ?? $field['id'] );
		$name  = (string) ( $context['name'] ?? $field['id'] );
		$att   = absint( $value );
		$url   = $att ? (string) wp_get_attachment_url( $att ) : '';
		$thumb = $att ? (string) wp_get_attachment_image_url( $att, 'thumbnail' ) : '';
		$lib   = (string) ( $field['library'] ?? 'all' );
		?>
		<div class="asf-media" data-asf-media data-library="<?php echo esc_attr( $lib ); ?>">
			<input type="hidden" class="asf-media-id" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $att ); ?>" />
			<div class="asf-media-preview">
				<?php if ( $thumb ) : ?>
					<img src="<?php echo esc_url( $thumb ); ?>" alt="" />
				<?php elseif ( $url ) : ?>
					<span class="asf-media-filename"><?php echo esc_html( basename( $url ) ); ?></span>
				<?php else : ?>
					<span class="asf-media-empty"><?php esc_html_e( 'No file selected', 'arash-settings-framework' ); ?></span>
				<?php endif; ?>
			</div>
			<div class="asf-media-actions">
				<button type="button" class="button asf-media-select"><?php esc_html_e( 'Select', 'arash-settings-framework' ); ?></button>
				<button type="button" class="button asf-media-remove" <?php disabled( ! $att ); ?>><?php esc_html_e( 'Remove', 'arash-settings-framework' ); ?></button>
			</div>
		</div>
		<?php
	}
}
