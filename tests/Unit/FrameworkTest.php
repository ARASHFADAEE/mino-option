<?php
/**
 * Core framework tests.
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Tests\Unit;

use Mino\Option\Framework;
use Mino\Option\Registry\PageRegistry;
use Mino\Option\Security\RequestGuard;
use Mino\Option\Settings;
use Mino\Option\Storage\OptionStore;
use Mino\Option\Support\ConditionalEvaluator;
use Mino\Option\Validation\Sanitizer;
use PHPUnit\Framework\TestCase;

final class FrameworkTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		$GLOBALS['mino_test_options'] = array();
		$GLOBALS['mino_test_user_can'] = true;
		$GLOBALS['mino_test_attachments'] = array( 10 => array( 'url' => 'http://example.test/a.jpg', 'thumb' => 'http://example.test/a-thumb.jpg' ) );
		PageRegistry::reset();
		OptionStore::reset_instance();
		// Re-register builtins after registry reset of field types is not needed;
		// FieldTypeRegistry persists. Reset pages only.
		Framework::instance()->field_types()->register_builtins();
	}

	public function test_bootstrap(): void {
		$this->assertTrue( Framework::instance()->is_booted() );
		$this->assertSame( '1.0.0', MINO_VERSION );
	}

	public function test_register_page_and_tab(): void {
		Settings::register_page(
			array(
				'id'         => 'demo_page',
				'title'      => 'Demo',
				'menu_title' => 'Demo',
				'menu_type'  => 'menu',
			)
		);
		Settings::register_tab(
			'demo_page',
			array(
				'id'     => 'general',
				'title'  => 'General',
				'fields' => array(
					array(
						'id'      => 'enable_feature',
						'type'    => 'switch',
						'title'   => 'Enable',
						'default' => false,
					),
					array(
						'id'      => 'api_endpoint',
						'type'    => 'url',
						'title'   => 'API',
						'default' => '',
					),
				),
			)
		);

		$this->assertTrue( PageRegistry::instance()->has_page( 'demo_page' ) );
		$this->assertCount( 1, PageRegistry::instance()->get_tabs( 'demo_page' ) );
		$this->assertArrayHasKey( 'enable_feature', PageRegistry::instance()->get_fields( 'demo_page' ) );
	}

	public function test_duplicate_page_is_rejected(): void {
		Settings::register_page(
			array(
				'id'         => 'dup_page',
				'title'      => 'A',
				'menu_title' => 'A',
				'menu_type'  => 'menu',
			)
		);
		// Second call should not overwrite.
		Settings::register_page(
			array(
				'id'         => 'dup_page',
				'title'      => 'B',
				'menu_title' => 'B',
				'menu_type'  => 'menu',
			)
		);
		$page = PageRegistry::instance()->get_page( 'dup_page' );
		$this->assertSame( 'A', $page['title'] );
	}

	public function test_defaults_and_retrieval(): void {
		$this->register_demo();
		$this->assertFalse( mino_get_option( 'demo_page', 'enable_feature' ) );
		$this->assertSame( '', mino_get_option( 'demo_page', 'api_endpoint' ) );
		$this->assertNull( mino_get_option( 'demo_page', 'missing_key' ) );
		$this->assertSame( 'fallback', mino_get_option( 'demo_page', 'missing_key', 'fallback' ) );
	}

	public function test_boolean_false_and_string_zero_preserved(): void {
		$this->register_demo();
		OptionStore::instance()->save(
			'demo_page',
			array(
				'enable_feature' => false,
				'count_value'    => '0',
			)
		);

		// Register count field for allowlist on next save tests — store already wrote via save allowlist.
		// Direct option write to simulate stored "0".
		$GLOBALS['mino_test_options']['demo_page'] = array(
			'enable_feature' => false,
			'api_endpoint'   => '0',
		);

		$this->assertFalse( mino_get_option( 'demo_page', 'enable_feature', true ) );
		$this->assertSame( '0', mino_get_option( 'demo_page', 'api_endpoint', 'x' ) );
		$this->assertNotSame( null, mino_get_option( 'demo_page', 'enable_feature' ) );
	}

	public function test_defaults_do_not_overwrite_saved(): void {
		$this->register_demo();
		OptionStore::instance()->save( 'demo_page', array( 'enable_feature' => true ) );
		$this->assertTrue( mino_get_option( 'demo_page', 'enable_feature' ) );
		// Re-reading defaults map must not mutate option.
		PageRegistry::instance()->get_defaults( 'demo_page' );
		$this->assertTrue( mino_get_option( 'demo_page', 'enable_feature' ) );
		$raw = OptionStore::instance()->raw( 'demo_page' );
		$this->assertArrayNotHasKey( 'api_endpoint', $raw );
	}

	public function test_text_url_email_sanitization(): void {
		$field_text = array( 'id' => 't', 'type' => 'text' );
		$field_url  = array( 'id' => 'u', 'type' => 'url' );
		$field_mail = array( 'id' => 'e', 'type' => 'email' );

		$this->assertSame( 'hello', Sanitizer::sanitize_field( $field_text, '<b>hello</b>' ) );
		$this->assertSame( 'https://example.com', Sanitizer::sanitize_field( $field_url, 'https://example.com' ) );
		$this->assertSame( '', Sanitizer::sanitize_field( $field_url, 'not-a-url' ) );
		$this->assertSame( 'a@b.com', Sanitizer::sanitize_field( $field_mail, 'a@b.com' ) );
		$this->assertSame( '', Sanitizer::sanitize_field( $field_mail, 'nope' ) );
	}

	public function test_select_allowlisting(): void {
		$field = array(
			'id'      => 'mode',
			'type'    => 'select',
			'options' => array(
				'a' => 'A',
				'b' => 'B',
			),
		);
		$this->assertSame( 'a', Sanitizer::sanitize_field( $field, 'a' ) );
		$this->assertSame( '', Sanitizer::sanitize_field( $field, 'hack' ) );
	}

	public function test_repeater_sanitization(): void {
		$this->register_demo_with_repeater();
		$field = PageRegistry::instance()->get_fields( 'demo_page' )['items'];
		$clean = Sanitizer::sanitize_field(
			$field,
			array(
				array(
					'label' => '<b>One</b>',
					'url'   => 'https://ok.example',
				),
				array(
					'label' => 'Two',
					'url'   => 'bad',
				),
				'not-an-array',
			)
		);
		$this->assertCount( 2, $clean );
		$this->assertSame( 'One', $clean[0]['label'] );
		$this->assertSame( 'https://ok.example', $clean[0]['url'] );
		$this->assertSame( '', $clean[1]['url'] );
	}

	public function test_unregistered_fields_rejected_on_save(): void {
		$this->register_demo();
		OptionStore::instance()->save(
			'demo_page',
			array(
				'enable_feature' => true,
				'evil_field'     => 'x',
			)
		);
		$raw = OptionStore::instance()->raw( 'demo_page' );
		$this->assertArrayNotHasKey( 'evil_field', $raw );
		$this->assertTrue( $raw['enable_feature'] );
	}

	public function test_unauthorized_write_and_invalid_nonce(): void {
		$this->register_demo();
		$GLOBALS['mino_test_user_can'] = false;
		$this->assertFalse( RequestGuard::authorize_write( 'demo_page' ) );

		$GLOBALS['mino_test_user_can'] = true;
		$_REQUEST['mino_nonce']        = 'invalid';
		$this->assertFalse( RequestGuard::authorize_write( 'demo_page' ) );

		$_REQUEST['mino_nonce'] = wp_create_nonce( RequestGuard::nonce_action( 'demo_page' ) );
		$this->assertTrue( RequestGuard::authorize_write( 'demo_page' ) );
	}

	public function test_conditional_configuration(): void {
		$values = array( 'enable_remote_storage' => true, 'storage_provider' => 's3' );
		$rules  = array(
			'relation' => 'AND',
			array( 'enable_remote_storage', '==', true ),
			array( 'storage_provider', 'in', array( 's3', 'minio' ) ),
		);
		$this->assertTrue( ConditionalEvaluator::passes( $rules, $values ) );
		$values['enable_remote_storage'] = false;
		$this->assertFalse( ConditionalEvaluator::passes( $rules, $values ) );
	}

	public function test_media_attachment_validation(): void {
		$this->assertSame( 10, Sanitizer::attachment_id( 10 ) );
		$this->assertSame( 0, Sanitizer::attachment_id( 999 ) );
		$this->assertSame( 0, Sanitizer::attachment_id( 'abc' ) );
	}

	public function test_reset_behavior(): void {
		$this->register_demo();
		OptionStore::instance()->save(
			'demo_page',
			array(
				'enable_feature' => true,
				'api_endpoint'   => 'https://x.test',
			)
		);
		OptionStore::instance()->reset( 'demo_page' );
		$this->assertFalse( mino_get_option( 'demo_page', 'enable_feature' ) );
		$this->assertSame( '', mino_get_option( 'demo_page', 'api_endpoint' ) );
	}

	public function test_sanitize_tab(): void {
		$this->register_demo();
		$this->assertSame( 'general', RequestGuard::sanitize_tab( 'demo_page', 'general' ) );
		$this->assertSame( 'general', RequestGuard::sanitize_tab( 'demo_page', 'missing' ) );
	}

	public function test_template_path_traversal_blocked(): void {
		$base = sys_get_temp_dir();
		$this->assertNull( RequestGuard::resolve_template( '../etc/passwd', $base ) );
	}

	public function test_multi_instance_loader_info(): void {
		$info = mino_loader_info();
		$this->assertIsArray( $info );
	}

	public function test_boolean_sanitizer(): void {
		$this->assertTrue( Sanitizer::boolean( '1' ) );
		$this->assertTrue( Sanitizer::boolean( 'true' ) );
		$this->assertTrue( Sanitizer::boolean( true ) );
		$this->assertFalse( Sanitizer::boolean( '0' ) );
		$this->assertFalse( Sanitizer::boolean( false ) );
		$this->assertFalse( Sanitizer::boolean( '' ) );
	}

	private function register_demo(): void {
		Settings::register_page(
			array(
				'id'         => 'demo_page',
				'title'      => 'Demo',
				'menu_title' => 'Demo',
				'menu_type'  => 'menu',
			)
		);
		Settings::register_tab(
			'demo_page',
			array(
				'id'     => 'general',
				'title'  => 'General',
				'fields' => array(
					array(
						'id'      => 'enable_feature',
						'type'    => 'switch',
						'title'   => 'Enable',
						'default' => false,
					),
					array(
						'id'      => 'api_endpoint',
						'type'    => 'url',
						'title'   => 'API',
						'default' => '',
					),
				),
			)
		);
	}

	private function register_demo_with_repeater(): void {
		$this->register_demo();
		Settings::register_tab(
			'demo_page',
			array(
				'id'     => 'list',
				'title'  => 'List',
				'fields' => array(
					array(
						'id'     => 'items',
						'type'   => 'repeater',
						'title'  => 'Items',
						'fields' => array(
							array(
								'id'    => 'label',
								'type'  => 'text',
								'title' => 'Label',
							),
							array(
								'id'    => 'url',
								'type'  => 'url',
								'title' => 'URL',
							),
						),
					),
				),
			)
		);
	}
}
