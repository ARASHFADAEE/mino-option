<?php
/**
 * Plugin Name:       Mino Example Plugin
 * Description:       Demonstrates Mino Option (tabs, conditionals, repeater, media, color).
 * Version:           1.0.0
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            Arash Fadaee
 * Text Domain:       mino-example
 *
 * @package MinoExample
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load the framework from this repo (or a bundled copy).
 * Do not auto-activate demo settings — this example registers its own page.
 */
$mino_bootstrap = dirname( __DIR__, 2 ) . '/mino-option.php';
if ( is_readable( $mino_bootstrap ) ) {
	require_once $mino_bootstrap;
} else {
	$bundled = plugin_dir_path( __FILE__ ) . 'vendor/mino-option/mino-option.php';
	if ( is_readable( $bundled ) ) {
		require_once $bundled;
	}
}

if ( ! class_exists( \Mino\Option\Settings::class ) ) {
	return;
}

use Mino\Option\Settings;

add_action(
	'plugins_loaded',
	static function (): void {
		Settings::register_page(
			array(
				'id'          => 'mino_example',
				'title'       => __( 'Mino Example', 'mino-example' ),
				'menu_title'  => __( 'Mino Example', 'mino-example' ),
				'menu_type'   => 'menu',
				'capability'  => 'manage_options',
				'icon'        => 'dashicons-admin-settings',
				'position'    => 58,
				'description' => __( 'Demonstration settings page powered by Mino Option.', 'mino-example' ),
			)
		);

		Settings::register_tab(
			'mino_example',
			array(
				'id'          => 'general',
				'title'       => __( 'General', 'mino-example' ),
				'description' => __( 'Core options', 'mino-example' ),
				'icon'        => 'dashicons-admin-home',
				'fields'      => array(
					array(
						'id'      => 'enable_feature',
						'type'    => 'switch',
						'title'   => __( 'Enable Feature', 'mino-example' ),
						'default' => false,
					),
					array(
						'id'          => 'api_endpoint',
						'type'        => 'url',
						'title'       => __( 'API Endpoint', 'mino-example' ),
						'description' => __( 'Shown when the feature is enabled.', 'mino-example' ),
						'placeholder' => 'https://api.example.com',
						'default'     => '',
						'require'     => array(
							array( 'enable_feature', '==', true ),
						),
					),
					array(
						'id'      => 'contact_email',
						'type'    => 'email',
						'title'   => __( 'Contact Email', 'mino-example' ),
						'default' => '',
					),
					array(
						'id'      => 'mode',
						'type'    => 'buttonset',
						'title'   => __( 'Mode', 'mino-example' ),
						'options' => array(
							'live'    => __( 'Live', 'mino-example' ),
							'sandbox' => __( 'Sandbox', 'mino-example' ),
						),
						'default' => 'sandbox',
					),
				),
			)
		);

		Settings::register_tab(
			'mino_example',
			array(
				'id'          => 'appearance',
				'title'       => __( 'Appearance', 'mino-example' ),
				'description' => __( 'Colors & media', 'mino-example' ),
				'icon'        => 'dashicons-admin-appearance',
				'fields'      => array(
					array(
						'id'      => 'primary_color',
						'type'    => 'color',
						'title'   => __( 'Primary Color', 'mino-example' ),
						'default' => '#1a6f5b',
					),
					array(
						'id'    => 'logo',
						'type'  => 'image',
						'title' => __( 'Logo', 'mino-example' ),
					),
					array(
						'id'    => 'gallery',
						'type'  => 'gallery',
						'title' => __( 'Gallery', 'mino-example' ),
					),
					array(
						'id'    => 'about',
						'type'  => 'editor',
						'title' => __( 'About text', 'mino-example' ),
						'rows'  => 6,
					),
				),
			)
		);

		Settings::register_tab(
			'mino_example',
			array(
				'id'          => 'storage',
				'title'       => __( 'Storage', 'mino-example' ),
				'description' => __( 'Conditional remote storage', 'mino-example' ),
				'icon'        => 'dashicons-cloud',
				'fields'      => array(
					array(
						'id'      => 'enable_remote_storage',
						'type'    => 'switch',
						'title'   => __( 'Enable Remote Storage', 'mino-example' ),
						'default' => false,
					),
					array(
						'id'      => 'storage_provider',
						'type'    => 'select',
						'title'   => __( 'Storage Provider', 'mino-example' ),
						'options' => array(
							's3'    => 'Amazon S3 Compatible',
							'liara' => 'Liara',
							'minio' => 'MinIO',
						),
						'default' => 's3',
						'require' => array(
							array( 'enable_remote_storage', '==', true ),
						),
					),
					array(
						'id'      => 'bucket',
						'type'    => 'text',
						'title'   => __( 'Bucket', 'mino-example' ),
						'require' => array(
							'relation' => 'AND',
							array( 'enable_remote_storage', '==', true ),
							array( 'storage_provider', 'in', array( 's3', 'minio', 'liara' ) ),
						),
					),
				),
			)
		);

		Settings::register_tab(
			'mino_example',
			array(
				'id'          => 'social',
				'title'       => __( 'Social', 'mino-example' ),
				'description' => __( 'Repeater demo', 'mino-example' ),
				'icon'        => 'dashicons-share',
				'fields'      => array(
					array(
						'id'       => 'social_networks',
						'type'     => 'repeater',
						'title'    => __( 'Social Networks', 'mino-example' ),
						'min_rows' => 0,
						'max_rows' => 10,
						'fields'   => array(
							array(
								'id'    => 'network_name',
								'type'  => 'text',
								'title' => __( 'Network Name', 'mino-example' ),
							),
							array(
								'id'    => 'network_url',
								'type'  => 'url',
								'title' => __( 'URL', 'mino-example' ),
							),
							array(
								'id'    => 'network_icon',
								'type'  => 'image',
								'title' => __( 'Icon', 'mino-example' ),
							),
						),
					),
				),
			)
		);

		Settings::register_tab(
			'mino_example',
			array(
				'id'       => 'dashboard',
				'title'    => __( 'Dashboard', 'mino-example' ),
				'icon'     => 'dashicons-dashboard',
				'callback' => static function (): void {
					$enabled = mino_get_option( 'mino_example', 'enable_feature', false );
					$color   = mino_get_option( 'mino_example', 'primary_color', '#1a6f5b' );
					$logo_id = (int) mino_get_option( 'mino_example', 'logo', 0 );
					echo '<div class="mino-custom-dashboard">';
					echo '<h2>' . esc_html__( 'Live values', 'mino-example' ) . '</h2>';
					echo '<p>' . esc_html__( 'Feature enabled:', 'mino-example' ) . ' <strong>' . ( $enabled ? 'yes' : 'no' ) . '</strong></p>';
					echo '<p>' . esc_html__( 'Primary color:', 'mino-example' ) . ' <code style="color:' . esc_attr( (string) $color ) . '">' . esc_html( (string) $color ) . '</code></p>';
					if ( $logo_id ) {
						$url = mino_attachment_url( $logo_id, 'medium' );
						if ( $url ) {
							echo '<p><img src="' . esc_url( $url ) . '" alt="" style="max-width:160px;height:auto;" /></p>';
						}
					}
					echo '</div>';
				},
			)
		);
	},
	20
);
