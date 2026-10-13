<?php
/**
 * Field row rendering.
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework\Admin;

defined( 'ABSPATH' ) || exit;

use Arash\SettingsFramework\Fields\FieldTypeRegistry;

/**
 * Renders a field wrapper + control.
 */
final class FieldRenderer {

	/**
	 * Non-persistent display types (no label duplication for heading etc.).
	 *
	 * @var array<int, string>
	 */
	private static $display_types = array( 'heading', 'notice', 'html', 'callback' );

	/**
	 * @param string               $page_id Page ID.
	 * @param array<string, mixed> $field   Field config.
	 * @param mixed                $value   Current value.
	 */
	public static function render( string $page_id, array $field, $value ): void {
		$type    = (string) $field['type'];
		$handler = FieldTypeRegistry::instance()->get( $type );

		if ( isset( $field['renderer'] ) && is_callable( $field['renderer'] ) && 'callback' !== $type ) {
			call_user_func(
				$field['renderer'],
				$field,
				$value,
				array(
					'page_id' => $page_id,
					'name'    => $field['id'],
					'id'      => $field['id'],
				)
			);
			return;
		}

		if ( ! $handler ) {
			echo '<p class="asf-error">' . esc_html(
				sprintf(
					/* translators: %s: field type */
					__( 'Unknown field type: %s', 'arash-settings-framework' ),
					$type
				)
			) . '</p>';
			return;
		}

		$dep = ! empty( $field['require'] ) ? $field['require'] : ( $field['dependency'] ?? array() );
		$classes = array(
			'asf-field',
			'asf-field--' . sanitize_html_class( $type ),
		);
		if ( ! empty( $field['class'] ) ) {
			$classes[] = sanitize_html_class( (string) $field['class'] );
		}

		ob_start();
		?>
		<div
			class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
			data-asf-field="<?php echo esc_attr( (string) $field['id'] ); ?>"
			<?php if ( ! empty( $dep ) ) : ?>
				data-asf-require="<?php echo esc_attr( wp_json_encode( $dep ) ); ?>"
			<?php endif; ?>
		>
			<?php if ( ! in_array( $type, self::$display_types, true ) && 'checkbox' !== $type ) : ?>
				<label class="asf-field-label" for="<?php echo esc_attr( (string) $field['id'] ); ?>" id="<?php echo esc_attr( (string) $field['id'] ); ?>-label">
					<?php echo esc_html( (string) $field['title'] ); ?>
					<?php if ( ! empty( $field['required'] ) ) : ?>
						<span class="asf-required" aria-hidden="true">*</span>
					<?php endif; ?>
				</label>
			<?php endif; ?>

			<div class="asf-field-control">
				<?php
				$handler->render(
					$field,
					$value,
					array(
						'page_id' => $page_id,
						'name'    => (string) $field['id'],
						'id'      => (string) $field['id'],
					)
				);
				?>
			</div>

			<?php if ( ! empty( $field['description'] ) && ! in_array( $type, array( 'notice', 'heading' ), true ) ) : ?>
				<p class="asf-field-description" id="<?php echo esc_attr( (string) $field['id'] ); ?>-desc">
					<?php echo esc_html( (string) $field['description'] ); ?>
				</p>
			<?php endif; ?>
		</div>
		<?php
		$html = ob_get_clean();

		/**
		 * Filters rendered field HTML.
		 *
		 * @param string $html    Markup.
		 * @param array  $field   Field.
		 * @param mixed  $value   Value.
		 * @param string $page_id Page ID.
		 */
		echo apply_filters( 'asf_render_field', $html, $field, $value, $page_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
