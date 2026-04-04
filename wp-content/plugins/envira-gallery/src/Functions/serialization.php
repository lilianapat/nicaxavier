<?php
/**
 * Envira Serialization Functions.
 *
 * @since 1.7.0
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {

	exit;

}
/**
 * Fix a serialized string
 *
 * @since 1.3.1.6
 *
 * @param string $text Serialized string to fix.
 *
 * @return array Unserialized data.
 */
function envira_fix_serialized_string( $text ) {

	// Check string is serialised and if it already works return it.
	if ( ! preg_match( '/^[aOs]:/', $text ) ) {
		return $text;
	}
	if ( unserialize( $text ) !== false ) { // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
		return unserialize( $text ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
	}

	// String needs fixing - fix it.
	$text = preg_replace_callback( '/\bs:(\d+):"(.*?)"/', 'envira_fix_str_length', $text );

	return unserialize( $text ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
}

/**
 * Callback function for replacing the string's length paramter on a broken
 * serialized string
 *
 * @since 1.3.1.6
 *
 * @param array $matches preg_replace matches.
 * @return string Replacement string.
 */
function envira_fix_str_length( $matches ) {

	$string       = $matches[2];
	$right_length = strlen( $string );

	return 's:' . $right_length . ':"' . $string . '"';
}
