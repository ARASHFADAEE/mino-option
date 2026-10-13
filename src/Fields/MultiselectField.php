<?php
/**
 * Multiselect field.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Fields;

defined( 'ABSPATH' ) || exit;

use Mino\Option\Validation\Sanitizer;

/**
 * Multi-select dropdown.
 */
final class MultiselectField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'multiselect';
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( $value, array $field ) {
		$options = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
		return Sanitizer::allowlisted( $value, $options, true );
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $field, $value, array $context ): void {
		$name    = (string) ( $context['name'] ?? $field['id'] ) . '[]';
		$id      = (string) ( $context['id'] ?? $field['id'] );
		$current = is_array( $value ) ? array_map( 'strval', $value ) : array();
		$options = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
		?>
		<select class="mino-select mino-multiselect" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" multiple <?php disabled( ! empty( $field['disabled'] ) ); ?>>
			<?php foreach ( $options as $opt_value => $label ) : ?>
				<option value="<?php echo esc_attr( (string) $opt_value ); ?>" <?php echo in_array( (string) $opt_value, $current, true ) ? 'selected' : ''; ?>>
					<?php echo esc_html( (string) $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}
}
