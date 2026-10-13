<?php
/**
 * Media field.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Fields;

defined( 'ABSPATH' ) || exit;

use Mino\Option\Validation\Sanitizer;

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
		<div class="mino-media" data-mino-media data-library="<?php echo esc_attr( $lib ); ?>">
			<input type="hidden" class="mino-media-id" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $att ); ?>" />
			<div class="mino-media-preview">
				<?php if ( $thumb ) : ?>
					<img src="<?php echo esc_url( $thumb ); ?>" alt="" />
				<?php elseif ( $url ) : ?>
					<span class="mino-media-filename"><?php echo esc_html( basename( $url ) ); ?></span>
				<?php else : ?>
					<span class="mino-media-empty"><?php esc_html_e( 'No file selected', 'mino-option' ); ?></span>
				<?php endif; ?>
			</div>
			<div class="mino-media-actions">
				<button type="button" class="button mino-media-select"><?php esc_html_e( 'Select', 'mino-option' ); ?></button>
				<button type="button" class="button mino-media-remove" <?php disabled( ! $att ); ?>><?php esc_html_e( 'Remove', 'mino-option' ); ?></button>
			</div>
		</div>
		<?php
	}
}
