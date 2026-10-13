<?php
/**
 * Radio field.
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework\Fields;

defined( 'ABSPATH' ) || exit;

use Arash\SettingsFramework\Validation\Sanitizer;

/**
 * Radio group.
 */
final class RadioField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'radio';
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
		echo '<div class="asf-radio-group" role="radiogroup" aria-labelledby="' . esc_attr( $id_base ) . '-label">';
		foreach ( $options as $opt_value => $label ) {
			$oid = $id_base . '-' . sanitize_html_class( (string) $opt_value );
			?>
			<label class="asf-radio" for="<?php echo esc_attr( $oid ); ?>">
				<input type="radio" id="<?php echo esc_attr( $oid ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $opt_value ); ?>" <?php checked( (string) $value, (string) $opt_value ); ?> <?php disabled( ! empty( $field['disabled'] ) ); ?> />
				<span><?php echo esc_html( (string) $label ); ?></span>
			</label>
			<?php
		}
		echo '</div>';
	}
}
