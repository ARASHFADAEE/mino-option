<?php
/**
 * Options API persistence layer.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Storage;

defined( 'ABSPATH' ) || exit;

use Mino\Option\Registry\PageRegistry;

/**
 * One option array per settings page. Defaults are applied at read time only.
 *
 * Removed / renamed fields: values remain in the option until an explicit reset
 * or migration clears them; they are ignored on subsequent saves (unregistered
 * keys are never written). Hidden conditional fields keep previously saved
 * values unless the save payload explicitly includes a new value.
 */
final class OptionStore {

	/**
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Clear singleton (tests only).
	 *
	 * @internal
	 */
	public static function reset_instance(): void {
		self::$instance = null;
	}

	/**
	 * Resolve option key for a page.
	 *
	 * @param string $page_id Page ID.
	 */
	public function option_key( string $page_id ): string {
		$page = PageRegistry::instance()->get_page( $page_id );
		if ( $page && ! empty( $page['option_key'] ) ) {
			return (string) $page['option_key'];
		}

		return $page_id;
	}

	/**
	 * Raw option array without defaults.
	 *
	 * @param string $page_id Page ID.
	 * @return array<string, mixed>
	 */
	public function raw( string $page_id ): array {
		$value = get_option( $this->option_key( $page_id ), array() );
		return is_array( $value ) ? $value : array();
	}

	/**
	 * All values merged with registered defaults.
	 *
	 * @param string $page_id Page ID.
	 * @return array<string, mixed>
	 */
	public function all( string $page_id ): array {
		$saved    = $this->raw( $page_id );
		$defaults = PageRegistry::instance()->get_defaults( $page_id );

		// array_key_exists preserves false / "0" / 0 / ''.
		$merged = $defaults;
		foreach ( $saved as $key => $value ) {
			$merged[ $key ] = $value;
		}

		/**
		 * Filters merged options for a page.
		 *
		 * @param array  $merged  Merged values.
		 * @param string $page_id Page ID.
		 */
		return apply_filters( 'mino_get_options', $merged, $page_id );
	}

	/**
	 * Get a single value.
	 *
	 * @param string     $page_id  Page ID.
	 * @param string     $field_id Field ID.
	 * @param mixed|null $default  Explicit default; null uses field default then null.
	 * @return mixed
	 */
	public function get( string $page_id, string $field_id, $default = null ) {
		$saved = $this->raw( $page_id );

		if ( array_key_exists( $field_id, $saved ) ) {
			$value = $saved[ $field_id ];
		} else {
			if ( null !== $default ) {
				$value = $default;
			} else {
				$defaults = PageRegistry::instance()->get_defaults( $page_id );
				$value    = array_key_exists( $field_id, $defaults ) ? $defaults[ $field_id ] : null;
			}
		}

		/**
		 * Filters a single option value.
		 *
		 * @param mixed  $value    Value.
		 * @param string $page_id  Page ID.
		 * @param string $field_id Field ID.
		 * @param mixed  $default  Requested default.
		 */
		return apply_filters( 'mino_get_option', $value, $page_id, $field_id, $default );
	}

	/**
	 * Persist sanitized values. Merges into existing option; only registered
	 * keys from $values are updated. Unregistered keys in $values are dropped.
	 *
	 * @param string               $page_id Page ID.
	 * @param array<string, mixed> $values  Sanitized field values.
	 * @return bool
	 */
	public function save( string $page_id, array $values ): bool {
		$registered = PageRegistry::instance()->get_fields( $page_id );
		$existing   = $this->raw( $page_id );
		$next       = $existing;

		foreach ( $values as $key => $value ) {
			if ( ! isset( $registered[ $key ] ) ) {
				continue;
			}
			$next[ $key ] = $value;
		}

		/**
		 * Filters values about to be saved.
		 *
		 * @param array  $next     Next option array.
		 * @param array  $values   Incoming sanitized values.
		 * @param string $page_id  Page ID.
		 */
		$next = apply_filters( 'mino_pre_save_options', $next, $values, $page_id );

		$key    = $this->option_key( $page_id );
		$result = update_option( $key, $next, false );

		// update_option returns false when value unchanged; treat as success.
		$success = false !== $result || $this->raw( $page_id ) === $next;

		if ( $success ) {
			/**
			 * Fires after a successful save.
			 *
			 * @param array  $next    Saved values.
			 * @param string $page_id Page ID.
			 */
			do_action( 'mino_saved', $next, $page_id );
		}

		return $success;
	}

	/**
	 * Reset page options to registered defaults.
	 *
	 * @param string $page_id Page ID.
	 * @return bool
	 */
	public function reset( string $page_id ): bool {
		$defaults = PageRegistry::instance()->get_defaults( $page_id );
		$key      = $this->option_key( $page_id );

		/**
		 * Filters defaults used for reset.
		 *
		 * @param array  $defaults Defaults.
		 * @param string $page_id  Page ID.
		 */
		$defaults = apply_filters( 'mino_reset_defaults', $defaults, $page_id );

		$result = update_option( $key, $defaults, false );

		/**
		 * Fires after reset.
		 *
		 * @param array  $defaults Reset values.
		 * @param string $page_id  Page ID.
		 */
		do_action( 'mino_reset', $defaults, $page_id );

		return false !== $result || $this->raw( $page_id ) === $defaults;
	}
}
