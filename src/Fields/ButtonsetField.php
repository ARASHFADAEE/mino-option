<?php
/**
 * Buttonset field.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Fields;

defined( 'ABSPATH' ) || exit;

use Mino\Option\Validation\Sanitizer;

/**
 * Segmented button control (radio under the hood).
 */
final class ButtonsetField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'buttonset';
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( $value, array $field ) {
		$options = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
		return Sanitizer::allowlisted( $value, $options, false );
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $field, $value, array $context ): void {
		$name    = (string) ( $context['name'] ?? $field['id'] );
		$id_base = (string) ( $context['id'] ?? $field['id'] );
		$options = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
		echo '<div class="mino-buttonset" role="radiogroup">';
		foreach ( $options as $opt_value => $label ) {
			$oid     = $id_base . '-' . sanitize_html_class( (string) $opt_value );
			$is_active = (string) $value === (string) $opt_value;
			?>
			<label class="mino-buttonset-item<?php echo $is_active ? ' is-active' : ''; ?>" for="<?php echo esc_attr( $oid ); ?>">
				<input type="radio" class="mino-buttonset-input" id="<?php echo esc_attr( $oid ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $opt_value ); ?>" <?php checked( $is_active ); ?> <?php disabled( ! empty( $field['disabled'] ) ); ?> />
				<span><?php echo esc_html( (string) $label ); ?></span>
			</label>
			<?php
		}
		echo '</div>';
	}
}
