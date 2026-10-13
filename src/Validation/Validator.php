<?php
/**
 * Optional validation rules.
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework\Validation;

defined( 'ABSPATH' ) || exit;

use Arash\SettingsFramework\Registry\PageRegistry;

/**
 * Collects validation errors without throwing.
 */
final class Validator {

	/**
	 * @param string               $page_id Page ID.
	 * @param array<string, mixed> $values  Sanitized values.
	 * @return array<string, string> Field ID => error message.
	 */
	public static function validate_page( string $page_id, array $values ): array {
		$errors = array();
		$fields = PageRegistry::instance()->get_fields( $page_id );

		foreach ( $fields as $field_id => $field ) {
			$value = $values[ $field_id ] ?? null;

			if ( ! empty( $field['required'] ) && self::is_empty( $value ) ) {
				$errors[ $field_id ] = sprintf(
					/* translators: %s: field title */
					__( '%s is required.', 'arash-settings-framework' ),
					(string) $field['title']
				);
				continue;
			}

			$type = (string) $field['type'];
			if ( 'email' === $type && is_string( $value ) && '' !== $value && ! is_email( $value ) ) {
				$errors[ $field_id ] = __( 'Please enter a valid email address.', 'arash-settings-framework' );
			}
			if ( 'url' === $type && is_string( $value ) && '' !== $value && ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
				$errors[ $field_id ] = __( 'Please enter a valid URL.', 'arash-settings-framework' );
			}

			if ( isset( $field['validate'] ) && is_callable( $field['validate'] ) ) {
				$result = call_user_func( $field['validate'], $value, $field, $values );
				if ( is_string( $result ) && '' !== $result ) {
					$errors[ $field_id ] = $result;
				} elseif ( false === $result ) {
					$errors[ $field_id ] = __( 'Invalid value.', 'arash-settings-framework' );
				}
			}
		}

		/**
		 * Filters validation errors.
		 *
		 * @param array  $errors  Errors.
		 * @param array  $values  Values.
		 * @param string $page_id Page ID.
		 */
		return apply_filters( 'asf_validate_page', $errors, $values, $page_id );
	}

	/**
	 * @param mixed $value Value.
	 */
	public static function is_empty( $value ): bool {
		if ( null === $value ) {
			return true;
		}
		if ( is_string( $value ) ) {
			return '' === $value;
		}
		if ( is_array( $value ) ) {
			return array() === $value;
		}
		// false, 0, "0" are NOT empty for required checks of booleans/numbers.
		return false;
	}
}
