<?php
/**
 * Switch / toggle field.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Fields;

defined( 'ABSPATH' ) || exit;

use Mino\Option\Validation\Sanitizer;

/**
 * Accessible switch control storing a boolean.
 */
final class SwitchField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'switch';
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
		$name    = (string) ( $context['name'] ?? $field['id'] );
		$id      = (string) ( $context['id'] ?? $field['id'] );
		$checked = Sanitizer::boolean( $value );
		?>
		<button
			type="button"
			class="mino-switch<?php echo $checked ? ' is-on' : ''; ?>"
			role="switch"
			aria-checked="<?php echo $checked ? 'true' : 'false'; ?>"
			data-mino-switch
			id="<?php echo esc_attr( $id ); ?>-btn"
			<?php disabled( ! empty( $field['disabled'] ) ); ?>
		>
			<span class="mino-switch-track" aria-hidden="true"><span class="mino-switch-thumb"></span></span>
			<span class="screen-reader-text"><?php echo esc_html( (string) $field['title'] ); ?></span>
		</button>
		<input type="hidden" class="mino-switch-input" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo $checked ? '1' : '0'; ?>" />
		<?php
	}
}
