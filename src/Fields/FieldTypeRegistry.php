<?php
/**
 * Field type registry.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Maps type slugs to FieldInterface implementations.
 */
final class FieldTypeRegistry {

	/**
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * @var array<string, string>
	 */
	private $types = array();

	/**
	 * @var array<string, FieldInterface>
	 */
	private $instances = array();

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
	 * @internal
	 */
	public static function reset(): void {
		self::$instance = null;
	}

	/**
	 * Register built-in field types.
	 */
	public function register_builtins(): void {
		$map = array(
			'text'        => TextField::class,
			'email'       => EmailField::class,
			'url'         => UrlField::class,
			'password'    => PasswordField::class,
			'number'      => NumberField::class,
			'textarea'    => TextareaField::class,
			'hidden'      => HiddenField::class,
			'checkbox'    => CheckboxField::class,
			'switch'      => SwitchField::class,
			'switcher'    => SwitchField::class,
			'select'      => SelectField::class,
			'multiselect' => MultiselectField::class,
			'radio'       => RadioField::class,
			'buttonset'   => ButtonsetField::class,
			'color'       => ColorField::class,
			'image'       => ImageField::class,
			'media'       => MediaField::class,
			'gallery'     => GalleryField::class,
			'editor'      => EditorField::class,
			'tinymce'     => EditorField::class,
			'richtext'    => EditorField::class,
			'repeater'    => RepeaterField::class,
			'heading'     => HeadingField::class,
			'notice'      => NoticeField::class,
			'html'        => HtmlField::class,
			'callback'    => CallbackField::class,
		);

		foreach ( $map as $type => $class ) {
			$this->register( $type, $class );
		}
	}

	/**
	 * @param string $type       Type slug.
	 * @param string $class_name Class name.
	 */
	public function register( string $type, string $class_name ): void {
		$type = sanitize_key( $type );
		if ( '' === $type || ! class_exists( $class_name ) ) {
			return;
		}
		if ( ! is_subclass_of( $class_name, FieldInterface::class ) ) {
			return;
		}
		$this->types[ $type ] = $class_name;
		unset( $this->instances[ $type ] );

		/**
		 * Fires when a field type is registered.
		 *
		 * @param string $type       Type.
		 * @param string $class_name Class.
		 */
		do_action( 'mino_field_type_registered', $type, $class_name );
	}

	/**
	 * @param string $type Type slug.
	 */
	public function get( string $type ): ?FieldInterface {
		$type = sanitize_key( $type );
		if ( ! isset( $this->types[ $type ] ) ) {
			return null;
		}
		if ( ! isset( $this->instances[ $type ] ) ) {
			$class = $this->types[ $type ];
			$this->instances[ $type ] = new $class();
		}
		return $this->instances[ $type ];
	}

	/**
	 * @return array<string, string>
	 */
	public function all(): array {
		return $this->types;
	}

	/**
	 * Collect asset requirements for a set of fields.
	 *
	 * @param array<string, array<string, mixed>> $fields Fields.
	 * @return array<int, string>
	 */
	public function collect_assets( array $fields ): array {
		$needed = array();
		foreach ( $fields as $field ) {
			$handler = $this->get( (string) ( $field['type'] ?? '' ) );
			if ( $handler ) {
				$needed = array_merge( $needed, $handler->required_assets() );
			}
			if ( 'repeater' === ( $field['type'] ?? '' ) && ! empty( $field['fields'] ) && is_array( $field['fields'] ) ) {
				foreach ( $field['fields'] as $sub ) {
					$sub_handler = $this->get( (string) ( $sub['type'] ?? '' ) );
					if ( $sub_handler ) {
						$needed = array_merge( $needed, $sub_handler->required_assets() );
					}
				}
			}
		}
		return array_values( array_unique( $needed ) );
	}
}
