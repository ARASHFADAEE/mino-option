<?php
/**
 * Public static registration API.
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

namespace Arash\SettingsFramework;

defined( 'ABSPATH' ) || exit;

use Arash\SettingsFramework\Registry\PageRegistry;
use InvalidArgumentException;

/**
 * Facade for registering pages and tabs.
 *
 * Prefer this over touching registries directly. Throws InvalidArgumentException
 * for duplicate or invalid configuration (caught and reported as admin notices
 * in production-friendly mode when WP_DEBUG is off).
 */
final class Settings {

	/**
	 * Register a settings page.
	 *
	 * @param array<string, mixed> $config Page configuration.
	 * @return void
	 */
	public static function register_page( array $config ): void {
		try {
			PageRegistry::instance()->register_page( $config );
		} catch ( InvalidArgumentException $e ) {
			self::report_error( $e->getMessage() );
		}
	}

	/**
	 * Register a tab (and its fields) on an existing or soon-to-exist page.
	 *
	 * @param string               $page_id Page ID.
	 * @param array<string, mixed> $config  Tab configuration.
	 * @return void
	 */
	public static function register_tab( string $page_id, array $config ): void {
		try {
			PageRegistry::instance()->register_tab( $page_id, $config );
		} catch ( InvalidArgumentException $e ) {
			self::report_error( $e->getMessage() );
		}
	}

	/**
	 * Register a custom field type class.
	 *
	 * @param string $type       Field type slug.
	 * @param string $class_name Fully-qualified class implementing FieldInterface.
	 */
	public static function register_field_type( string $type, string $class_name ): void {
		Framework::instance()->field_types()->register( $type, $class_name );
	}

	/**
	 * @param string $message Error message.
	 */
	private static function report_error( string $message ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			trigger_error( '[ASF] ' . $message, E_USER_WARNING );
		}

		add_action(
			'admin_notices',
			static function () use ( $message ): void {
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}
				echo '<div class="notice notice-error"><p>' . esc_html( '[ASF] ' . $message ) . '</p></div>';
			}
		);
	}
}
