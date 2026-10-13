<?php
/**
 * Checkbox field.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Fields;

defined( 'ABSPATH' ) || exit;

use Mino\Option\Validation\Sanitizer;

/**
 * Single checkbox (boolean).
 */
final class CheckboxField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'checkbox';
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( $value, array $field ) {
		return Sanitizer::boolean( $value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $field, $value, array $context ): void {
		$name  = (string) ( $context['name'] ?? $field['id'] );
		$id    = (string) ( $context['id'] ?? $field['id'] );
		$checked = Sanitizer::boolean( $value );
		?>
		<label class="mino-checkbox" for="<?php echo esc_attr( $id ); ?>">
			<input type="checkbox" class="mino-checkbox-input" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $checked ); ?> <?php disabled( ! empty( $field['disabled'] ) ); ?> />
			<span class="mino-checkbox-label"><?php echo esc_html( (string) $field['title'] ); ?></span>
		</label>
		<?php
	}
}
