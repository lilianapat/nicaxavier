<?php
/**
 * Legacy Commons Class.
 *
 * @since 1.0.0
 *
 * @package Envira Gallery
 */

// phpcs:disable Squiz.Commenting.FunctionComment.InvalidReturnVoid
// phpcs:disable Generic.Commenting.DocComment.ShortNotCapital
// phpcs:disable Squiz.Commenting.FileComment.WrongStyle
// phpcs:disable Squiz.Commenting.InlineComment.InvalidEndChar
// phpcs:disable Squiz.Commenting.ClassComment.Missing
// phpcs:disable Squiz.Commenting.FunctionComment.MissingParamComment
// phpcs:disable Squiz.Commenting.VariableComment.Missing
// phpcs:disable Squiz.Commenting.FunctionComment.Missing

use Envira\Utils\Cropping;

class Envira_Gallery_Common {

	public static $_instance = null;
	public function __construct() {}

	/**
	 * Get config defaults.
	 *
	 * @deprecated since 1.7.0
	 *
	 * @access public
	 *
	 * @param mixed $post_id
	 *
	 * @return array
	 */
	public function get_config_defaults( $post_id ) {
		return envira_get_config_defaults( $post_id );
	}

	/**
	 * get_config_default function.
	 *
	 * @deprecated since 1.7.0
	 *
	 * @access public
	 *
	 * @param mixed $key
	 *
	 * @return false|string
	 */
	public function get_config_default( $key ) {
		return envira_get_config_default( $key );
	}

	/**
	 * standalone_get_slug function.
	 *
	 * @deprecated since 1.7.0
	 *
	 * @access public
	 *
	 * @param mixed $type
	 *
	 * @return string
	 */
	public function standalone_get_slug( $type ) {
		return envira_standalone_get_the_slug( $type );
	}

	/**
	 * get_transient_expiration_time function.
	 *
	 * @access public
	 *
	 * @param string $plugin (default: 'envira-gallery').
	 *
	 * @return int
	 */
	public function get_transient_expiration_time( $plugin = 'envira-gallery' ) {
		return envira_get_transient_expiration_time( $plugin );
	}

	/**
	 * get_columns function.
	 *
	 * @deprecated since 1.7.0
	 *
	 * @access public
	 * @return array
	 */
	public function get_columns() {
		return envira_get_columns();
	}

	/**
	 * get_justified_last_row function.
	 *
	 * @deprecated since 1.7.0
	 *
	 * @access public
	 * @return array
	 */
	public function get_justified_last_row() {
		return envira_get_justified_last_row();
	}

	/**
	 * get_justified_gallery_themes function.
	 *
	 * @deprecated since 1.7.0
	 *
	 * @access public
	 * @return array
	 */
	public function get_justified_gallery_themes() {
		return envira_get_justified_gallery_themes();
	}

	/**
	 * get_gallery_themes function.
	 *
	 * @deprecated since 1.7.0
	 *
	 * @access public
	 * @return array
	 */
	public function get_gallery_themes() {
		return envira_get_gallery_themes();
	}

	/**
	 * get_lightbox_themes function.
	 *
	 * @deprecated since 1.7.0
	 *
	 * @access public
	 * @return array
	 */
	public function get_lightbox_themes() {
		return envira_get_lightbox_themes();
	}

	/**
	 * get_title_displays function.
	 *
	 * @deprecated since 1.7.0
	 *
	 * @access public
	 * @return array
	 */
	public function get_title_displays() {
		return envira_get_title_displays();
	}

	/**
	 * get_arrows_positions function.
	 *
	 * @deprecated since 1.7.0
	 *
	 * @access public
	 * @return array
	 */
	public function get_arrows_positions() {
		return envira_get_arrows_positions();
	}

	/**
	 * get_toolbar_positions function.
	 *
	 * @deprecated since 1.7.0
	 *
	 * @access public
	 * @return array
	 */
	public function get_toolbar_positions() {
		return envira_get_toolbar_positions();
	}

	/**
	 * get_transition_effects function.
	 *
	 * @deprecated since 1.7.0
	 *
	 * @access public
	 * @return array
	 */
	public function get_transition_effects() {
		return envira_get_transition_effects();
	}

	/**
	 * Get Thumbnails Positions
	 *
	 * @deprecated since 1.7.0
	 *
	 * @access public
	 * @return array
	 */
	public function get_thumbnail_positions() {
		return envira_get_thumbnail_positions();
	}

	/**
	 * Flush Gallery Caches
	 *
	 * @deprecated since 1.7.0
	 *
	 * @access public
	 * @param mixed  $post_id Post ID.
	 * @param string $slug Post Slug.
	 */
	public function flush_gallery_caches( $post_id, $slug = '' ) {
		return envira_flush_gallery_caches( $post_id, $slug );
	}

	/**
	 * Supported File Types.
	 *
	 * @deprecated since 1.7.0
	 *
	 * @access public
	 * @return array
	 */
	public function get_supported_filetypes() {
		return envira_get_supported_filetypes();
	}

	/**
	 * API method for cropping images.
	 *
	 * @deprecated since 1.7.0
	 *
	 * @global object $wpdb The $wpdb database object.
	 *
	 * @param string $url     The URL of the image to resize.
	 * @param int    $width   The width for cropping the image.
	 * @param int    $height  The height for cropping the image.
	 * @param bool   $crop    Whether or not to crop the image (default yes).
	 * @param string $align   The crop position alignment.
	 * @param int    $quality Image Quality.
	 * @param bool   $retina  Is Retina.
	 * @param array  $data    Array of gallery data (optional).
	 * @param bool   $force_overwrite      Forces an overwrite even if the thumbnail already exists (useful for applying watermarks).
	 * @return WP_Error|string Return WP_Error on error, URL of resized image on success.
	 */
	public function resize_image( $url, $width = null, $height = null, $crop = true, $align = 'c', $quality = 100, $retina = false, $data = [], $force_overwrite = false ) {
		return envira_resize_image( $url, $width, $height, $crop, $align, $quality, $retina, $data, $force_overwrite );
	}

	/**
	 * Helper method to return common information about an image.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args      List of resizing args to expand for gathering info.
	 * @return WP_Error|string Return WP_Error on error, array of data on success.
	 */
	public function get_image_info( $args ) {
		return envira_get_image_info( $args );
	}

	/**
	 * Helper method for retrieving image sizes.
	 *
	 * @deprecated since 1.7.0
	 *
	 * @param   bool $core_only     WordPress Only (excludes the default and envira_gallery_random options).
	 *
	 * @global array $_wp_additional_image_sizes Array of registered image sizes.
	 *
	 * @return  array                       Array of image size data.
	 */
	public function get_image_sizes( $core_only = false ) {
		return envira_get_image_sizes( $core_only );
	}

	/**
	 * Get Singleton.
	 *
	 * @deprecated since 1.7.0
	 *
	 * @access public
	 * @static
	 * @return object
	 */
	public static function get_instance() {

		if ( ! isset( self::$_instance ) && ! ( self::$_instance instanceof Envira_Gallery_Common ) ) {

			self::$_instance = new self();
		}

		return self::$_instance;
	}
}
