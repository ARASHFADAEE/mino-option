<?php
/**
 * Plugin Name:       ASF Example Plugin
 * Description:       Demonstrates Arash Settings Framework (tabs, conditionals, repeater, media, color).
 * Version:           1.0.0
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            Arash Fadaee
 * Text Domain:       asf-example
 *
 * @package ASFExample
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load the framework from this repo (or a bundled copy).
 * Do not auto-activate demo settings — this example registers its own page.
 */
$asf_bootstrap = dirname( __DIR__, 2 ) . '/arash-settings-framework.php';
if ( is_readable( $asf_bootstrap ) ) {
	require_once $asf_bootstrap;
} else {
	$bundled = plugin_dir_path( __FILE__ ) . 'vendor/arash-settings-framework/arash-settings-framework.php';
	if ( is_readable( $bundled ) ) {
		require_once $bundled;
	}
}

if ( ! class_exists( \Arash\SettingsFramework\Settings::class ) ) {
	return;
}

use Arash\SettingsFramework\Settings;

add_action(
	'plugins_loaded',
	static function (): void {
		Settings::register_page(
			array(
				'id'          => 'asf_example',
				'title'       => __( 'ASF Example', 'asf-example' ),
				'menu_title'  => __( 'ASF Example', 'asf-example' ),
				'menu_type'   => 'menu',
				'capability'  => 'manage_options',
				'icon'        => 'dashicons-admin-settings',
				'position'    => 58,
				'description' => __( 'Demonstration settings page powered by Arash Settings Framework.', 'asf-example' ),
			)
		);

		Settings::register_tab(
			'asf_example',
			array(
				'id'          => 'general',
				'title'       => __( 'General', 'asf-example' ),
				'description' => __( 'Core options', 'asf-example' ),
				'icon'        => 'dashicons-admin-home',
				'fields'      => array(
					array(
						'id'      => 'enable_feature',
						'type'    => 'switch',
						'title'   => __( 'Enable Feature', 'asf-example' ),
						'default' => false,
					),
					array(
						'id'          => 'api_endpoint',
						'type'        => 'url',
						'title'       => __( 'API Endpoint', 'asf-example' ),
						'description' => __( 'Shown when the feature is enabled.', 'asf-example' ),
						'placeholder' => 'https://api.example.com',
						'default'     => '',
						'require'     => array(
							array( 'enable_feature', '==', true ),
						),
					),
					array(
						'id'      => 'contact_email',
						'type'    => 'email',
						'title'   => __( 'Contact Email', 'asf-example' ),
						'default' => '',
					),
					array(
						'id'      => 'mode',
						'type'    => 'buttonset',
						'title'   => __( 'Mode', 'asf-example' ),
						'options' => array(
							'live'    => __( 'Live', 'asf-example' ),
							'sandbox' => __( 'Sandbox', 'asf-example' ),
						),
						'default' => 'sandbox',
					),
				),
			)
		);

		Settings::register_tab(
			'asf_example',
			array(
				'id'          => 'appearance',
				'title'       => __( 'Appearance', 'asf-example' ),
				'description' => __( 'Colors & media', 'asf-example' ),
				'icon'        => 'dashicons-admin-appearance',
				'fields'      => array(
					array(
						'id'      => 'primary_color',
						'type'    => 'color',
						'title'   => __( 'Primary Color', 'asf-example' ),
						'default' => '#1a6f5b',
					),
					array(
						'id'    => 'logo',
						'type'  => 'image',
						'title' => __( 'Logo', 'asf-example' ),
					),
					array(
						'id'    => 'gallery',
						'type'  => 'gallery',
						'title' => __( 'Gallery', 'asf-example' ),
					),
					array(
						'id'    => 'about',
						'type'  => 'editor',
						'title' => __( 'About text', 'asf-example' ),
						'rows'  => 6,
					),
				),
			)
		);

		Settings::register_tab(
			'asf_example',
			array(
				'id'          => 'storage',
				'title'       => __( 'Storage', 'asf-example' ),
				'description' => __( 'Conditional remote storage', 'asf-example' ),
				'icon'        => 'dashicons-cloud',
				'fields'      => array(
					array(
						'id'      => 'enable_remote_storage',
						'type'    => 'switch',
						'title'   => __( 'Enable Remote Storage', 'asf-example' ),
						'default' => false,
					),
					array(
						'id'      => 'storage_provider',
						'type'    => 'select',
						'title'   => __( 'Storage Provider', 'asf-example' ),
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
						'title'   => __( 'Bucket', 'asf-example' ),
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
			'asf_example',
			array(
				'id'          => 'social',
				'title'       => __( 'Social', 'asf-example' ),
				'description' => __( 'Repeater demo', 'asf-example' ),
				'icon'        => 'dashicons-share',
				'fields'      => array(
					array(
						'id'       => 'social_networks',
						'type'     => 'repeater',
						'title'    => __( 'Social Networks', 'asf-example' ),
						'min_rows' => 0,
						'max_rows' => 10,
						'fields'   => array(
							array(
								'id'    => 'network_name',
								'type'  => 'text',
								'title' => __( 'Network Name', 'asf-example' ),
							),
							array(
								'id'    => 'network_url',
								'type'  => 'url',
								'title' => __( 'URL', 'asf-example' ),
							),
							array(
								'id'    => 'network_icon',
								'type'  => 'image',
								'title' => __( 'Icon', 'asf-example' ),
							),
						),
					),
				),
			)
		);

		Settings::register_tab(
			'asf_example',
			array(
				'id'       => 'dashboard',
				'title'    => __( 'Dashboard', 'asf-example' ),
				'icon'     => 'dashicons-dashboard',
				'callback' => static function (): void {
					$enabled = asf_get_option( 'asf_example', 'enable_feature', false );
					$color   = asf_get_option( 'asf_example', 'primary_color', '#1a6f5b' );
					$logo_id = (int) asf_get_option( 'asf_example', 'logo', 0 );
					echo '<div class="asf-custom-dashboard">';
					echo '<h2>' . esc_html__( 'Live values', 'asf-example' ) . '</h2>';
					echo '<p>' . esc_html__( 'Feature enabled:', 'asf-example' ) . ' <strong>' . ( $enabled ? 'yes' : 'no' ) . '</strong></p>';
					echo '<p>' . esc_html__( 'Primary color:', 'asf-example' ) . ' <code style="color:' . esc_attr( (string) $color ) . '">' . esc_html( (string) $color ) . '</code></p>';
					if ( $logo_id ) {
						$url = asf_attachment_url( $logo_id, 'medium' );
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
