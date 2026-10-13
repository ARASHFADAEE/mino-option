<?php
/**
 * Framework bootstrap singleton.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option;

defined( 'ABSPATH' ) || exit;

use Mino\Option\Admin\MenuRegistrar;
use Mino\Option\Admin\SaveHandler;
use Mino\Option\Assets\AssetManager;
use Mino\Option\Fields\FieldTypeRegistry;
use Mino\Option\Registry\PageRegistry;
use Mino\Option\Storage\OptionStore;

/**
 * Boots hooks once and exposes core services.
 */
final class Framework {

	/**
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * @var bool
	 */
	private $booted = false;

	/**
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Register WordPress hooks exactly once.
	 */
	public function boot(): void {
		if ( $this->booted ) {
			return;
		}

		$this->booted = true;

		$this->field_types()->register_builtins();

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ), 5 );
		add_action( 'admin_menu', array( MenuRegistrar::class, 'register' ), 20 );
		add_action( 'admin_enqueue_scripts', array( AssetManager::class, 'enqueue' ), 20 );
		add_action( 'admin_init', array( SaveHandler::class, 'maybe_handle' ), 5 );
		add_action( 'wp_ajax_' . SaveHandler::AJAX_ACTION, array( SaveHandler::class, 'ajax_handle' ) );
		add_action( 'admin_notices', array( $this, 'maybe_show_version_conflict' ) );

		/**
		 * Fires after the framework has booted.
		 *
		 * @param Framework $framework Framework instance.
		 */
		do_action( 'mino_booted', $this );
	}

	/**
	 * @return PageRegistry
	 */
	public function pages(): PageRegistry {
		return PageRegistry::instance();
	}

	/**
	 * @return FieldTypeRegistry
	 */
	public function field_types(): FieldTypeRegistry {
		return FieldTypeRegistry::instance();
	}

	/**
	 * @return OptionStore
	 */
	public function store(): OptionStore {
		return OptionStore::instance();
	}

	/**
	 * Whether the framework has finished booting.
	 */
	public function is_booted(): bool {
		return $this->booted;
	}

	/**
	 * Load translations.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			MINO_TEXTDOMAIN,
			false,
			dirname( plugin_basename( MINO_FILE ) ) . '/languages'
		);
	}

	/**
	 * Surface major-version conflicts when WP_DEBUG is on.
	 */
	public function maybe_show_version_conflict(): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$info = mino_loader_info();
		if ( empty( $info['conflicts'] ) || ! is_array( $info['conflicts'] ) ) {
			return;
		}

		foreach ( $info['conflicts'] as $conflict ) {
			$active  = isset( $conflict['active']['version'] ) ? (string) $conflict['active']['version'] : '?';
			$attempt = isset( $conflict['attempt']['version'] ) ? (string) $conflict['attempt']['version'] : '?';
			echo '<div class="notice notice-warning"><p>';
			echo esc_html(
				sprintf(
					/* translators: 1: active version, 2: skipped version */
					__( 'Mino Option: incompatible major version detected. Active %1$s; skipped %2$s. First loaded copy remains active.', 'mino-option' ),
					$active,
					$attempt
				)
			);
			echo '</p></div>';
		}
	}
}
