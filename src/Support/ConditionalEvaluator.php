<?php
/**
 * Server-side conditional helpers (documentation / future use).
 *
 * @package Mino\Option
 */

declare(strict_types=1);

namespace Mino\Option\Support;

defined( 'ABSPATH' ) || exit;

use Mino\Option\Validation\Sanitizer;

/**
 * Evaluates require/dependency rules against a value map.
 *
 * Client-side JS mirrors this logic for visibility. The save pipeline remains
 * authoritative: visibility does not bypass field allowlisting.
 */
final class ConditionalEvaluator {

	/**
	 * @param array<int|string, mixed> $rules  Require rules.
	 * @param array<string, mixed>     $values Current values.
	 */
	public static function passes( array $rules, array $values ): bool {
		if ( empty( $rules ) ) {
			return true;
		}

		$relation = 'AND';
		$checks   = $rules;

		if ( isset( $rules['relation'] ) ) {
			$relation = strtoupper( (string) $rules['relation'] ) === 'OR' ? 'OR' : 'AND';
			unset( $checks['relation'] );
			$checks = array_values( $checks );
		}

		$results = array();
		foreach ( $checks as $rule ) {
			if ( ! is_array( $rule ) || count( $rule ) < 2 ) {
				continue;
			}
			$field = (string) $rule[0];
			$op    = isset( $rule[1] ) ? (string) $rule[1] : '==';
			$need  = $rule[2] ?? true;
			$cur   = $values[ $field ] ?? null;
			$results[] = self::compare( $cur, $op, $need );
		}

		if ( empty( $results ) ) {
			return true;
		}

		if ( 'OR' === $relation ) {
			return in_array( true, $results, true );
		}

		return ! in_array( false, $results, true );
	}

	/**
	 * @param mixed  $current Current value.
	 * @param string $op      Operator.
	 * @param mixed  $needed  Expected.
	 */
	public static function compare( $current, string $op, $needed ): bool {
		$op = strtolower( trim( $op ) );
		if ( '===' === $op ) {
			$op = '==';
		}
		if ( '=' === $op ) {
			$op = '==';
		}

		switch ( $op ) {
			case '!=':
			case '!==':
			case 'not':
				return ! self::loose_equal( $current, $needed );
			case 'empty':
				return self::is_emptyish( $current );
			case 'not_empty':
			case '!empty':
				return ! self::is_emptyish( $current );
			case 'in':
				$hay = is_array( $needed ) ? $needed : array( $needed );
				return in_array( self::stringify( $current ), array_map( array( __CLASS__, 'stringify' ), $hay ), true );
			case 'not_in':
				$hay = is_array( $needed ) ? $needed : array( $needed );
				return ! in_array( self::stringify( $current ), array_map( array( __CLASS__, 'stringify' ), $hay ), true );
			case '==':
			default:
				return self::loose_equal( $current, $needed );
		}
	}

	/**
	 * @param mixed $a A.
	 * @param mixed $b B.
	 */
	private static function loose_equal( $a, $b ): bool {
		if ( is_bool( $b ) ) {
			return Sanitizer::boolean( $a ) === $b;
		}
		return self::stringify( $a ) === self::stringify( $b );
	}

	/**
	 * @param mixed $value Value.
	 */
	private static function is_emptyish( $value ): bool {
		if ( null === $value || false === $value ) {
			return true;
		}
		if ( is_string( $value ) ) {
			return '' === $value;
		}
		if ( is_array( $value ) ) {
			return array() === $value;
		}
		return false;
	}

	/**
	 * @param mixed $value Value.
	 */
	private static function stringify( $value ): string {
		if ( is_bool( $value ) ) {
			return $value ? '1' : '0';
		}
		if ( is_array( $value ) ) {
			return wp_json_encode( $value ) ?: '';
		}
		return (string) $value;
	}
}
