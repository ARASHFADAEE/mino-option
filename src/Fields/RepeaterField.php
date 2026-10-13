<?php
/**
 * Repeater field.
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Sortable repeatable rows with nested subfields.
 *
 * Stored as a numerically indexed array of associative arrays keyed by subfield ID.
 */
final class RepeaterField extends AbstractField {

	/**
	 * {@inheritdoc}
	 */
	public static function type(): string {
		return 'repeater';
	}

	/**
	 * {@inheritdoc}
	 */
	public function required_assets(): array {
		return array( 'sortable', 'repeater' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function sanitize( $value, array $field ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$subfields = isset( $field['fields'] ) && is_array( $field['fields'] ) ? $field['fields'] : array();
		$by_id     = array();
		foreach ( $subfields as $sub ) {
			if ( isset( $sub['id'] ) ) {
				$by_id[ $sub['id'] ] = $sub;
			}
		}

		$min = isset( $field['min_rows'] ) ? (int) $field['min_rows'] : 0;
		$max = isset( $field['max_rows'] ) ? (int) $field['max_rows'] : 50;
		if ( $max < 1 ) {
			$max = 50;
		}

		$rows = array();
		foreach ( $value as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$clean = array();
			foreach ( $by_id as $sid => $sub ) {
				$raw = $row[ $sid ] ?? null;
				if ( in_array( (string) $sub['type'], array( 'checkbox', 'switch', 'switcher' ), true ) && null === $raw ) {
					$clean[ $sid ] = false;
					continue;
				}
				if ( null === $raw && ! array_key_exists( $sid, $row ) ) {
					continue;
				}
				$handler = FieldTypeRegistry::instance()->get( (string) $sub['type'] );
				$clean[ $sid ] = $handler
					? $handler->sanitize( $raw, $sub )
					: ( is_scalar( $raw ) ? sanitize_text_field( (string) $raw ) : '' );
			}
			if ( ! empty( $clean ) ) {
				$rows[] = $clean;
			}
			if ( count( $rows ) >= $max ) {
				break;
			}
		}

		while ( count( $rows ) < $min ) {
			$rows[] = $this->empty_row( $by_id );
		}

		return array_values( $rows );
	}

	/**
	 * @param array<string, array<string, mixed>> $by_id Subfields.
	 * @return array<string, mixed>
	 */
	private function empty_row( array $by_id ): array {
		$row = array();
		foreach ( $by_id as $sid => $sub ) {
			$row[ $sid ] = $sub['default'] ?? ( in_array( (string) $sub['type'], array( 'checkbox', 'switch' ), true ) ? false : '' );
		}
		return $row;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $field, $value, array $context ): void {
		$name      = (string) ( $context['name'] ?? $field['id'] );
		$id_base   = (string) ( $context['id'] ?? $field['id'] );
		$subfields = isset( $field['fields'] ) && is_array( $field['fields'] ) ? $field['fields'] : array();
		$rows      = is_array( $value ) ? $value : array();
		$min       = (int) ( $field['min_rows'] ?? 0 );
		$max       = (int) ( $field['max_rows'] ?? 50 );

		$registry = FieldTypeRegistry::instance();
		?>
		<div
			class="asf-repeater"
			data-asf-repeater
			data-name="<?php echo esc_attr( $name ); ?>"
			data-min="<?php echo esc_attr( (string) $min ); ?>"
			data-max="<?php echo esc_attr( (string) $max ); ?>"
		>
			<div class="asf-repeater-rows" data-asf-repeater-rows>
				<?php if ( empty( $rows ) ) : ?>
					<p class="asf-repeater-empty"><?php esc_html_e( 'No items yet. Add a row to get started.', 'arash-settings-framework' ); ?></p>
				<?php endif; ?>
				<?php foreach ( $rows as $index => $row ) : ?>
					<?php $this->render_row( $field, $subfields, $row, $name, $id_base, (int) $index, $registry ); ?>
				<?php endforeach; ?>
			</div>

			<template class="asf-repeater-template">
				<?php $this->render_row( $field, $subfields, array(), $name, $id_base, '{{INDEX}}', $registry, true ); ?>
			</template>

			<div class="asf-repeater-toolbar">
				<button type="button" class="button button-secondary asf-repeater-add">
					<?php esc_html_e( 'Add row', 'arash-settings-framework' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * @param array<string, mixed>        $field     Parent field.
	 * @param array<int, array>           $subfields Subfields.
	 * @param array<string, mixed>        $row       Row values.
	 * @param string                      $name      Base name.
	 * @param string                      $id_base   ID base.
	 * @param int|string                  $index     Row index.
	 * @param FieldTypeRegistry           $registry  Registry.
	 * @param bool                        $is_tpl    Template mode.
	 */
	private function render_row( array $field, array $subfields, array $row, string $name, string $id_base, $index, FieldTypeRegistry $registry, bool $is_tpl = false ): void {
		?>
		<div class="asf-repeater-row" data-asf-repeater-row>
			<div class="asf-repeater-row-header">
				<button type="button" class="asf-repeater-handle button-link" aria-label="<?php esc_attr_e( 'Drag to reorder', 'arash-settings-framework' ); ?>">
					<span class="dashicons dashicons-menu" aria-hidden="true"></span>
				</button>
				<span class="asf-repeater-row-label">
					<?php
					printf(
						/* translators: %s: row number */
						esc_html__( 'Item %s', 'arash-settings-framework' ),
						esc_html( (string) ( is_int( $index ) ? $index + 1 : $index ) )
					);
					?>
				</span>
				<div class="asf-repeater-row-actions">
					<button type="button" class="button-link asf-repeater-duplicate"><?php esc_html_e( 'Duplicate', 'arash-settings-framework' ); ?></button>
					<button type="button" class="button-link asf-repeater-remove"><?php esc_html_e( 'Remove', 'arash-settings-framework' ); ?></button>
				</div>
			</div>
			<div class="asf-repeater-row-body">
				<?php foreach ( $subfields as $sub ) : ?>
					<?php
					$sid   = (string) $sub['id'];
					$sname = $is_tpl ? $name . '[__i__][' . $sid . ']' : $name . '[' . $index . '][' . $sid . ']';
					$sid_h = $is_tpl ? $id_base . '___i___' . $sid : $id_base . '_' . $index . '_' . $sid;
					$val   = array_key_exists( $sid, $row ) ? $row[ $sid ] : ( $sub['default'] ?? null );
					$handler = $registry->get( (string) $sub['type'] );
					$dep     = ! empty( $sub['require'] ) ? $sub['require'] : ( $sub['dependency'] ?? array() );
					?>
					<div
						class="asf-field asf-field--<?php echo esc_attr( (string) $sub['type'] ); ?>"
						data-asf-field="<?php echo esc_attr( $sid ); ?>"
						<?php if ( ! empty( $dep ) ) : ?>
							data-asf-require="<?php echo esc_attr( wp_json_encode( $dep ) ); ?>"
						<?php endif; ?>
					>
						<?php if ( ! in_array( (string) $sub['type'], array( 'heading', 'notice', 'html', 'checkbox' ), true ) ) : ?>
							<label class="asf-field-label" for="<?php echo esc_attr( $sid_h ); ?>"><?php echo esc_html( (string) $sub['title'] ); ?></label>
						<?php endif; ?>
						<div class="asf-field-control">
							<?php
							if ( $handler ) {
								$handler->render(
									$sub,
									$val,
									array(
										'name' => $sname,
										'id'   => $sid_h,
									)
								);
							}
							?>
						</div>
						<?php if ( ! empty( $sub['description'] ) && 'notice' !== $sub['type'] ) : ?>
							<p class="asf-field-description"><?php echo esc_html( (string) $sub['description'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
