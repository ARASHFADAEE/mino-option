<?php
/**
 * Checkbox field.
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework\Fields;

defined( 'ABSPATH' ) || exit;

use Arash\SettingsFramework\Validation\Sanitizer;

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
		<label class="asf-checkbox" for="<?php echo esc_attr( $id ); ?>">
			<input type="checkbox" class="asf-checkbox-input" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $checked ); ?> <?php disabled( ! empty( $field['disabled'] ) ); ?> />
			<span class="asf-checkbox-label"><?php echo esc_html( (string) $field['title'] ); ?></span>
		</label>
		<?php
	}
}
