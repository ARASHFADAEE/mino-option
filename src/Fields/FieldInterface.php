<?php
/**
 * Field renderer contract.
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Every built-in and custom field type implements this interface.
 */
interface FieldInterface {

	/**
	 * Field type slug.
	 */
	public static function type(): string;

	/**
	 * Sanitize inbound value.
	 *
	 * @param mixed                $value Raw value.
	 * @param array<string, mixed> $field Field config.
	 * @return mixed
	 */
	public function sanitize( $value, array $field );

	/**
	 * Render the field control markup (not the wrapping row).
	 *
	 * @param array<string, mixed> $field   Field config.
	 * @param mixed                $value   Current value.
	 * @param array<string, mixed> $context Render context (page_id, name, id, index…).
	 */
	public function render( array $field, $value, array $context ): void;

	/**
	 * Assets this field may need: color, media, editor, sortable.
	 *
	 * @return array<int, string>
	 */
	public function required_assets(): array;
}
