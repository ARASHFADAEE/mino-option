<?php
/**
 * Gallery field.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Fields;

defined( 'ABSPATH' ) || exit;

use Mino\Option\Validation\Sanitizer;

/**
 * Multiple attachment IDs.
 */
final class GalleryField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'gallery';
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
		if ( is_string( $value ) ) {
			$value = array_filter( array_map( 'trim', explode( ',', $value ) ) );
		}
		if ( ! is_array( $value ) ) {
			return array();
		}
		$ids = array();
		foreach ( $value as $item ) {
			$id = Sanitizer::attachment_id( $item );
			if ( $id > 0 ) {
				$ids[] = $id;
			}
		}
		return array_values( array_unique( $ids ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $field, $value, array $context ): void {
		$id   = (string) ( $context['id'] ?? $field['id'] );
		$name = (string) ( $context['name'] ?? $field['id'] );
		$ids  = is_array( $value ) ? array_map( 'absint', $value ) : array();
		$ids  = array_filter( $ids );
		?>
		<div class="mino-gallery" data-mino-gallery>
			<input type="hidden" class="mino-gallery-ids" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>" />
			<ul class="mino-gallery-list">
				<?php foreach ( $ids as $att ) : ?>
					<?php $thumb = wp_get_attachment_image_url( $att, 'thumbnail' ); ?>
					<li data-id="<?php echo esc_attr( (string) $att ); ?>">
						<?php if ( $thumb ) : ?>
							<img src="<?php echo esc_url( $thumb ); ?>" alt="" />
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<div class="mino-media-actions">
				<button type="button" class="button mino-gallery-select"><?php esc_html_e( 'Select images', 'mino-option' ); ?></button>
				<button type="button" class="button mino-gallery-clear" <?php disabled( empty( $ids ) ); ?>><?php esc_html_e( 'Clear', 'mino-option' ); ?></button>
			</div>
		</div>
		<?php
	}
}
