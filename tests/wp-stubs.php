<?php
/**
 * Minimal WordPress function stubs for unit tests.
 *
 * @package Arash\SettingsFramework
 */

declare(strict_types=1);

$GLOBALS['asf_test_options'] = array();
$GLOBALS['asf_test_hooks']   = array();
$GLOBALS['asf_test_user_can'] = true;
$GLOBALS['asf_test_nonces']  = array();
$GLOBALS['asf_test_attachments'] = array();

if ( ! function_exists( 'trailingslashit' ) ) {
	function trailingslashit( $s ) {
		return rtrim( (string) $s, '/\\' ) . '/';
	}
}

if ( ! function_exists( 'plugins_url' ) ) {
	function plugins_url( $path = '', $plugin = '' ) {
		return 'http://example.test/wp-content/plugins/' . ltrim( (string) $path, '/' );
	}
}

if ( ! function_exists( 'plugin_basename' ) ) {
	function plugin_basename( $file ) {
		return basename( dirname( (string) $file ) ) . '/' . basename( (string) $file );
	}
}

if ( ! function_exists( 'plugin_dir_path' ) ) {
	function plugin_dir_path( $file ) {
		return trailingslashit( dirname( (string) $file ) );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) {
		$str = (string) $str;
		$str = strip_tags( $str );
		return trim( $str );
	}
}

if ( ! function_exists( 'sanitize_textarea_field' ) ) {
	function sanitize_textarea_field( $str ) {
		return sanitize_text_field( $str );
	}
}

if ( ! function_exists( 'sanitize_email' ) ) {
	function sanitize_email( $email ) {
		return filter_var( (string) $email, FILTER_SANITIZE_EMAIL ) ?: '';
	}
}

if ( ! function_exists( 'is_email' ) ) {
	function is_email( $email ) {
		return (bool) filter_var( (string) $email, FILTER_VALIDATE_EMAIL );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	function esc_url_raw( $url ) {
		$url = (string) $url;
		return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : '';
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) {
		return htmlspecialchars( (string) $url, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_textarea' ) ) {
	function esc_textarea( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_js' ) ) {
	function esc_js( $text ) {
		return addslashes( (string) $text );
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $data ) {
		return strip_tags( (string) $data, '<p><a><strong><em><ul><ol><li><br><h1><h2><h3><h4><img>' );
	}
}

if ( ! function_exists( 'sanitize_hex_color' ) ) {
	function sanitize_hex_color( $color ) {
		if ( preg_match( '/^#([A-Fa-f0-9]{3}){1,2}$/', (string) $color ) ) {
			return (string) $color;
		}
		return null;
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( $maybeint ) {
		return abs( (int) $maybeint );
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'wp_unslash', $value );
		}
		return is_string( $value ) ? stripslashes( $value ) : $value;
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data, $options = 0, $depth = 512 ) {
		return json_encode( $data, $options, $depth );
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = 'default' ) {
		return esc_html( $text );
	}
}

if ( ! function_exists( 'esc_attr__' ) ) {
	function esc_attr__( $text, $domain = 'default' ) {
		return esc_attr( $text );
	}
}

if ( ! function_exists( 'esc_html_e' ) ) {
	function esc_html_e( $text, $domain = 'default' ) {
		echo esc_html( $text );
	}
}

if ( ! function_exists( 'esc_attr_e' ) ) {
	function esc_attr_e( $text, $domain = 'default' ) {
		echo esc_attr( $text );
	}
}

if ( ! function_exists( 'selected' ) ) {
	function selected( $selected, $current = true, $echo = true ) {
		$result = ( (string) $selected === (string) $current ) ? ' selected="selected"' : '';
		if ( $echo ) {
			echo $result;
		}
		return $result;
	}
}

if ( ! function_exists( 'checked' ) ) {
	function checked( $checked, $current = true, $echo = true ) {
		$result = ( (string) $checked === (string) $current || $checked === $current ) ? ' checked="checked"' : '';
		if ( $echo ) {
			echo $result;
		}
		return $result;
	}
}

if ( ! function_exists( 'disabled' ) ) {
	function disabled( $disabled, $current = true, $echo = true ) {
		$result = ( $disabled == $current ) ? ' disabled="disabled"' : '';
		if ( $echo ) {
			echo $result;
		}
		return $result;
	}
}

if ( ! function_exists( 'sanitize_html_class' ) ) {
	function sanitize_html_class( $class ) {
		return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $key, $default = false ) {
		return array_key_exists( $key, $GLOBALS['asf_test_options'] )
			? $GLOBALS['asf_test_options'][ $key ]
			: $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( $key, $value, $autoload = null ) {
		$prev = $GLOBALS['asf_test_options'][ $key ] ?? null;
		$GLOBALS['asf_test_options'][ $key ] = $value;
		return $prev !== $value;
	}
}

if ( ! function_exists( 'add_option' ) ) {
	function add_option( $key, $value ) {
		if ( array_key_exists( $key, $GLOBALS['asf_test_options'] ) ) {
			return false;
		}
		$GLOBALS['asf_test_options'][ $key ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( $key ) {
		unset( $GLOBALS['asf_test_options'][ $key ] );
		return true;
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		$GLOBALS['asf_test_hooks'][ $hook ][] = compact( 'callback', 'priority' );
		return true;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		return add_action( $hook, $callback, $priority, $accepted_args );
	}
}

if ( ! function_exists( 'do_action' ) ) {
	function do_action( $hook, ...$args ) {
		if ( empty( $GLOBALS['asf_test_hooks'][ $hook ] ) ) {
			return;
		}
		foreach ( $GLOBALS['asf_test_hooks'][ $hook ] as $item ) {
			call_user_func_array( $item['callback'], $args );
		}
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook, $value, ...$args ) {
		if ( empty( $GLOBALS['asf_test_hooks'][ $hook ] ) ) {
			return $value;
		}
		foreach ( $GLOBALS['asf_test_hooks'][ $hook ] as $item ) {
			$value = call_user_func( $item['callback'], $value, ...$args );
		}
		return $value;
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( $cap ) {
		return (bool) $GLOBALS['asf_test_user_can'];
	}
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
	function wp_verify_nonce( $nonce, $action = -1 ) {
		return isset( $GLOBALS['asf_test_nonces'][ $action ] ) && hash_equals( (string) $GLOBALS['asf_test_nonces'][ $action ], (string) $nonce );
	}
}

if ( ! function_exists( 'wp_create_nonce' ) ) {
	function wp_create_nonce( $action = -1 ) {
		$token = 'nonce_' . md5( (string) $action );
		$GLOBALS['asf_test_nonces'][ $action ] = $token;
		return $token;
	}
}

if ( ! function_exists( 'wp_nonce_field' ) ) {
	function wp_nonce_field( $action = -1, $name = '_wpnonce', $referer = true, $echo = true ) {
		$field = '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( wp_create_nonce( $action ) ) . '" />';
		if ( $echo ) {
			echo $field;
		}
		return $field;
	}
}

if ( ! function_exists( 'get_post' ) ) {
	function get_post( $id ) {
		$id = (int) $id;
		if ( isset( $GLOBALS['asf_test_attachments'][ $id ] ) ) {
			return (object) array(
				'ID'        => $id,
				'post_type' => 'attachment',
			);
		}
		return null;
	}
}

if ( ! function_exists( 'wp_get_attachment_url' ) ) {
	function wp_get_attachment_url( $id ) {
		return $GLOBALS['asf_test_attachments'][ (int) $id ]['url'] ?? false;
	}
}

if ( ! function_exists( 'wp_get_attachment_image_url' ) ) {
	function wp_get_attachment_image_url( $id, $size = 'thumbnail' ) {
		return $GLOBALS['asf_test_attachments'][ (int) $id ]['thumb'] ?? wp_get_attachment_url( $id );
	}
}

if ( ! function_exists( 'get_current_user_id' ) ) {
	function get_current_user_id() {
		return 1;
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	function set_transient( $key, $value, $expiration = 0 ) {
		$GLOBALS['asf_test_options'][ '_transient_' . $key ] = $value;
		return true;
	}
}

if ( ! function_exists( 'get_transient' ) ) {
	function get_transient( $key ) {
		return $GLOBALS['asf_test_options'][ '_transient_' . $key ] ?? false;
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	function delete_transient( $key ) {
		unset( $GLOBALS['asf_test_options'][ '_transient_' . $key ] );
		return true;
	}
}

if ( ! function_exists( 'is_rtl' ) ) {
	function is_rtl() {
		return false;
	}
}

if ( ! function_exists( 'load_plugin_textdomain' ) ) {
	function load_plugin_textdomain( $domain, $deprecated = false, $plugin_rel_path = false ) {
		return true;
	}
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
	function wp_enqueue_style( ...$args ) {}
}
if ( ! function_exists( 'wp_enqueue_script' ) ) {
	function wp_enqueue_script( ...$args ) {}
}
if ( ! function_exists( 'wp_enqueue_media' ) ) {
	function wp_enqueue_media( ...$args ) {}
}
if ( ! function_exists( 'wp_enqueue_editor' ) ) {
	function wp_enqueue_editor() {}
}
if ( ! function_exists( 'wp_localize_script' ) ) {
	function wp_localize_script( ...$args ) {}
}
if ( ! function_exists( 'add_menu_page' ) ) {
	function add_menu_page( ...$args ) {}
}
if ( ! function_exists( 'add_submenu_page' ) ) {
	function add_submenu_page( ...$args ) {}
}
if ( ! function_exists( 'wp_die' ) ) {
	function wp_die( $message = '', $title = '', $args = array() ) {
		throw new RuntimeException( is_string( $message ) ? $message : 'wp_die' );
	}
}
if ( ! function_exists( 'wp_safe_redirect' ) ) {
	function wp_safe_redirect( $location, $status = 302 ) {
		throw new RuntimeException( 'redirect:' . $location );
	}
}
if ( ! function_exists( 'admin_url' ) ) {
	function admin_url( $path = '' ) {
		return 'http://example.test/wp-admin/' . ltrim( (string) $path, '/' );
	}
}
if ( ! function_exists( 'add_query_arg' ) ) {
	function add_query_arg( ...$args ) {
		if ( is_array( $args[0] ) ) {
			$url = $args[1] ?? '';
			$query = http_build_query( $args[0] );
			return $url . ( strpos( $url, '?' ) === false ? '?' : '&' ) . $query;
		}
		return (string) ( $args[2] ?? '' );
	}
}
if ( ! function_exists( 'wp_editor' ) ) {
	function wp_editor( $content, $id, $settings = array() ) {
		echo '<textarea id="' . esc_attr( $id ) . '">' . esc_textarea( $content ) . '</textarea>';
	}
}
