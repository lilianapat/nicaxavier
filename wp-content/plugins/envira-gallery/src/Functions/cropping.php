<?php
/**
 * Envira Cropping Functions.
 *
 * @since 1.7.0
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

use Envira\Utils\Cropping;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * Crop all images in a background procces.
 *
 * @since 1.7.0
 *
 * @access public
 * @param int $gallery_id Gallery ID.
 * @return bool
 */
function envira_crop_images( $gallery_id ) {
	// Bail if no gallery ID.
	if ( ! isset( $gallery_id ) ) {
		return false;
	}

	$background = new Envira\Frontend\Background();

	$crop_data = [
		'id' => $gallery_id,
	];

	$background->background_request( $crop_data, 'crop-images' );

	return true;
}

/**
 * Helper Method to resize Images.
 *
 * @since 1.7.0
 *
 * @access public
 * @param string $url URL of image to resize.
 * @param mixed  $width (default: null).
 * @param mixed  $height (default: null).
 * @param bool   $crop (default: true).
 * @param string $align (default: 'c').
 * @param int    $quality (default: 100).
 * @param bool   $retina (default: false).
 * @param array  $data (default: array()).
 * @param bool   $force_overwrite (default: false).
 * @return string|WP_Error
 */
function envira_resize_image( $url, $width = null, $height = null, $crop = true, $align = 'c', $quality = 100, $retina = false, $data = [], $force_overwrite = false ) {

	// Get common vars.
	$args = [ $url, $width, $height, $crop, $align, $quality, $retina, $data ];

	// Filter args.
	$args = apply_filters( 'envira_gallery_resize_image_args', $args );

	// Get image info.
	$common = envira_get_image_info( $args );

	// Unpack variables if an array, otherwise return WP_Error.
	if ( is_wp_error( $common ) ) {
		return $common;
	}

	$orig_width     = $common['orig_width'];
	$orig_height    = $common['orig_height'];
	$dest_width     = $common['dest_width'];
	$dest_height    = $common['dest_height'];
	$dest_file_name = $common['dest_file_name'];

	// If the destination width/height values are the same as the original, don't do anything.
	if ( ! $force_overwrite && $orig_width === $dest_width && $orig_height === $dest_height ) {
		return $url;
	}

	// If the file doesn't exist yet, we need to create it.
	if ( ! file_exists( $dest_file_name ) || ( file_exists( $dest_file_name ) && $force_overwrite ) ) {
		( new Cropping() )->resize_image(
			$url,
			$width,
			$height,
			$crop,
			$align,
			$quality,
			$retina,
			$data,
			$force_overwrite
		);
	}

	// Set the resized image URL.
	$resized_url = str_replace( basename( $url ), basename( $dest_file_name ), $url );

	return apply_filters( 'envira_gallery_resize_image_resized_url', $resized_url );
}
