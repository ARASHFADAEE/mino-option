<?php
/**
 * Settings page and tab registry.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Registry;

defined( 'ABSPATH' ) || exit;

use InvalidArgumentException;

/**
 * Holds page / tab / field configuration. Does not persist defaults to the DB.
 */
final class PageRegistry {

	/**
	 * Field ID pattern: lowercase alphanumeric with underscores, 1–64 chars.
	 */
	public const FIELD_ID_PATTERN = '/^[a-z][a-z0-9_]{0,63}$/';

	/**
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * @var array<string, array<string, mixed>>
	 */
	private $pages = array();

	/**
	 * @var array<string, array<string, array<string, mixed>>>
	 */
	private $tabs = array();

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
	 * Reset for tests.
	 *
	 * @internal
	 */
	public static function reset(): void {
		self::$instance = null;
	}

	/**
	 * @param array<string, mixed> $config Page config.
	 * @throws InvalidArgumentException When invalid or duplicate.
	 */
	public function register_page( array $config ): void {
		$config = $this->normalize_page( $config );
		$id     = $config['id'];

		if ( isset( $this->pages[ $id ] ) ) {
			throw new InvalidArgumentException(
				sprintf( 'Settings page "%s" is already registered.', $id )
			);
		}

		/**
		 * Filters page configuration before registration.
		 *
		 * @param array  $config Page config.
		 * @param string $id     Page ID.
		 */
		$config = apply_filters( 'mino_register_page', $config, $id );

		$this->pages[ $id ] = $config;

		/**
		 * Fires after a page is registered.
		 *
		 * @param array  $config Page config.
		 * @param string $id     Page ID.
		 */
		do_action( 'mino_page_registered', $config, $id );
	}

	/**
	 * @param string               $page_id Page ID.
	 * @param array<string, mixed> $config  Tab config.
	 * @throws InvalidArgumentException When invalid or duplicate.
	 */
	public function register_tab( string $page_id, array $config ): void {
		if ( '' === $page_id ) {
			throw new InvalidArgumentException( 'Tab registration requires a page ID.' );
		}

		$config = $this->normalize_tab( $config );
		$tab_id = $config['id'];

		if ( ! isset( $this->tabs[ $page_id ] ) ) {
			$this->tabs[ $page_id ] = array();
		}

		if ( isset( $this->tabs[ $page_id ][ $tab_id ] ) ) {
			throw new InvalidArgumentException(
				sprintf( 'Tab "%s" is already registered on page "%s".', $tab_id, $page_id )
			);
		}

		$this->assert_unique_field_ids( $page_id, $config['fields'] );

		/**
		 * Filters tab configuration before registration.
		 *
		 * @param array  $config  Tab config.
		 * @param string $page_id Page ID.
		 */
		$config = apply_filters( 'mino_register_tab', $config, $page_id );

		$this->tabs[ $page_id ][ $tab_id ] = $config;

		/**
		 * Fires after a tab is registered.
		 *
		 * @param array  $config  Tab config.
		 * @param string $page_id Page ID.
		 */
		do_action( 'mino_tab_registered', $config, $page_id );
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public function all_pages(): array {
		return $this->pages;
	}

	/**
	 * @param string $page_id Page ID.
	 * @return array<string, mixed>|null
	 */
	public function get_page( string $page_id ): ?array {
		return $this->pages[ $page_id ] ?? null;
	}

	/**
	 * @param string $page_id Page ID.
	 * @return array<string, array<string, mixed>>
	 */
	public function get_tabs( string $page_id ): array {
		return $this->tabs[ $page_id ] ?? array();
	}

	/**
	 * Flat map of field_id => field config for a page.
	 *
	 * @param string $page_id Page ID.
	 * @return array<string, array<string, mixed>>
	 */
	public function get_fields( string $page_id ): array {
		$fields = array();
		foreach ( $this->get_tabs( $page_id ) as $tab ) {
			foreach ( $tab['fields'] as $field ) {
				$fields[ $field['id'] ] = $field;
			}
		}

		/**
		 * Filters the registered field map for a page.
		 *
		 * @param array  $fields  Field configs keyed by ID.
		 * @param string $page_id Page ID.
		 */
		return apply_filters( 'mino_page_fields', $fields, $page_id );
	}

	/**
	 * Defaults map for a page (field_id => default).
	 *
	 * @param string $page_id Page ID.
	 * @return array<string, mixed>
	 */
	public function get_defaults( string $page_id ): array {
		$defaults = array();
		foreach ( $this->get_fields( $page_id ) as $id => $field ) {
			if ( array_key_exists( 'default', $field ) ) {
				$defaults[ $id ] = $field['default'];
			}
		}

		/**
		 * Filters default values for a page.
		 *
		 * @param array  $defaults Defaults.
		 * @param string $page_id  Page ID.
		 */
		return apply_filters( 'mino_page_defaults', $defaults, $page_id );
	}

	/**
	 * @param string $page_id Page ID.
	 */
	public function has_page( string $page_id ): bool {
		return isset( $this->pages[ $page_id ] );
	}

	/**
	 * @param array<string, mixed> $config Raw config.
	 * @return array<string, mixed>
	 * @throws InvalidArgumentException When invalid.
	 */
	private function normalize_page( array $config ): array {
		$id = isset( $config['id'] ) ? (string) $config['id'] : '';
		if ( ! preg_match( self::FIELD_ID_PATTERN, $id ) ) {
			throw new InvalidArgumentException( 'Page ID must match ' . self::FIELD_ID_PATTERN );
		}

		$menu_type = isset( $config['menu_type'] ) ? (string) $config['menu_type'] : 'menu';
		if ( ! in_array( $menu_type, array( 'menu', 'submenu', 'top', 'sub' ), true ) ) {
			throw new InvalidArgumentException( 'menu_type must be menu or submenu.' );
		}
		if ( 'top' === $menu_type ) {
			$menu_type = 'menu';
		}
		if ( 'sub' === $menu_type ) {
			$menu_type = 'submenu';
		}

		$parent = isset( $config['parent_slug'] ) ? (string) $config['parent_slug'] : ( $config['page_parent'] ?? '' );
		if ( 'submenu' === $menu_type && '' === $parent ) {
			throw new InvalidArgumentException( 'submenu pages require parent_slug.' );
		}

		$slug = isset( $config['menu_slug'] ) ? (string) $config['menu_slug'] : ( $config['page_slug'] ?? $id );
		$slug = sanitize_key( $slug );

		return array(
			'id'          => $id,
			'title'       => (string) ( $config['title'] ?? $config['page_title'] ?? $config['dev_title'] ?? $id ),
			'menu_title'  => (string) ( $config['menu_title'] ?? $config['title'] ?? $id ),
			'menu_type'   => $menu_type,
			'parent_slug' => (string) $parent,
			'capability'  => (string) ( $config['capability'] ?? $config['page_capability'] ?? 'manage_options' ),
			'menu_slug'   => $slug,
			'icon'        => (string) ( $config['icon'] ?? $config['icon_url'] ?? 'dashicons-admin-generic' ),
			'position'    => isset( $config['position'] ) ? (int) $config['position'] : ( isset( $config['menu_priority'] ) ? (int) $config['menu_priority'] : null ),
			'logo'        => (string) ( $config['logo'] ?? $config['logo_url'] ?? '' ),
			'description' => (string) ( $config['description'] ?? '' ),
			'option_key'  => (string) ( $config['option_key'] ?? $id ),
		);
	}

	/**
	 * @param array<string, mixed> $config Raw tab.
	 * @return array<string, mixed>
	 * @throws InvalidArgumentException When invalid.
	 */
	private function normalize_tab( array $config ): array {
		$id = isset( $config['id'] ) ? (string) $config['id'] : '';
		if ( ! preg_match( self::FIELD_ID_PATTERN, $id ) ) {
			throw new InvalidArgumentException( 'Tab ID must match ' . self::FIELD_ID_PATTERN );
		}

		$fields = array();
		if ( ! empty( $config['fields'] ) && is_array( $config['fields'] ) ) {
			foreach ( $config['fields'] as $field ) {
				if ( ! is_array( $field ) ) {
					continue;
				}
				$fields[] = $this->normalize_field( $field );
			}
		}

		$callback = null;
		if ( isset( $config['callback'] ) && is_callable( $config['callback'] ) ) {
			$callback = $config['callback'];
		}

		// Template paths are only accepted when paired with an explicit base allowlist.
		$template      = '';
		$template_base = '';
		if ( ! empty( $config['template'] ) && ! empty( $config['template_base'] ) ) {
			$template      = (string) $config['template'];
			$template_base = (string) $config['template_base'];
		}

		return array(
			'id'            => $id,
			'title'         => (string) ( $config['title'] ?? $id ),
			'description'   => (string) ( $config['description'] ?? $config['desc'] ?? '' ),
			'icon'          => (string) ( $config['icon'] ?? '' ),
			'fields'        => $fields,
			'callback'      => $callback,
			'template'      => $template,
			'template_base' => $template_base,
		);
	}

	/**
	 * @param array<string, mixed> $field Raw field.
	 * @return array<string, mixed>
	 * @throws InvalidArgumentException When invalid.
	 */
	private function normalize_field( array $field ): array {
		$id = isset( $field['id'] ) ? (string) $field['id'] : '';
		if ( ! preg_match( self::FIELD_ID_PATTERN, $id ) ) {
			throw new InvalidArgumentException(
				sprintf( 'Field ID "%s" is invalid. Use lowercase letters, numbers, underscores; start with a letter.', $id )
			);
		}

		$type = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'text';

		$normalized = array(
			'id'          => $id,
			'type'        => $type,
			'title'       => (string) ( $field['title'] ?? $id ),
			'description' => (string) ( $field['description'] ?? $field['desc'] ?? '' ),
			'default'     => $field['default'] ?? null,
			'required'    => ! empty( $field['required'] ),
			'disabled'    => ! empty( $field['disabled'] ),
			'readonly'    => ! empty( $field['readonly'] ),
			'placeholder' => (string) ( $field['placeholder'] ?? '' ),
			'options'     => isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array(),
			'attributes'  => isset( $field['attributes'] ) && is_array( $field['attributes'] ) ? $field['attributes'] : array(),
			'class'       => (string) ( $field['class'] ?? '' ),
			'require'     => isset( $field['require'] ) && is_array( $field['require'] ) ? $field['require'] : array(),
			'dependency'  => isset( $field['dependency'] ) && is_array( $field['dependency'] ) ? $field['dependency'] : array(),
			'fields'      => isset( $field['fields'] ) && is_array( $field['fields'] ) ? $field['fields'] : array(),
			'min'         => $field['min'] ?? null,
			'max'         => $field['max'] ?? null,
			'step'        => $field['step'] ?? null,
			'rows'        => isset( $field['rows'] ) ? (int) $field['rows'] : 4,
			'min_rows'    => isset( $field['min_rows'] ) ? (int) $field['min_rows'] : 0,
			'max_rows'    => isset( $field['max_rows'] ) ? (int) $field['max_rows'] : 50,
			'sanitize'    => $field['sanitize'] ?? null,
			'validate'    => $field['validate'] ?? null,
			'renderer'    => $field['renderer'] ?? null,
			'library'     => (string) ( $field['library'] ?? 'image' ),
			'multiple'    => ! empty( $field['multiple'] ),
		);

		if ( 'repeater' === $type && ! empty( $normalized['fields'] ) ) {
			$sub = array();
			foreach ( $normalized['fields'] as $subfield ) {
				if ( is_array( $subfield ) ) {
					$sub[] = $this->normalize_field( $subfield );
				}
			}
			$normalized['fields'] = $sub;
		}

		/**
		 * Filters a single field configuration.
		 *
		 * @param array $normalized Normalized field.
		 * @param array $field      Original field.
		 */
		return apply_filters( 'mino_normalize_field', $normalized, $field );
	}

	/**
	 * @param string               $page_id Page ID.
	 * @param array<int, array>    $fields  New fields.
	 * @throws InvalidArgumentException On duplicates.
	 */
	private function assert_unique_field_ids( string $page_id, array $fields ): void {
		$existing = array_keys( $this->get_fields( $page_id ) );
		$seen     = array();

		foreach ( $fields as $field ) {
			$id = $field['id'];
			if ( in_array( $id, $existing, true ) || isset( $seen[ $id ] ) ) {
				throw new InvalidArgumentException(
					sprintf( 'Field ID "%s" is already in use on page "%s".', $id, $page_id )
				);
			}
			$seen[ $id ] = true;
		}
	}
}
