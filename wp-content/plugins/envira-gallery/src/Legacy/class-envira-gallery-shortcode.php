<?php
/**
 * Legacy Envira_Gallery_Shortcode class
 *
 * @package Envira_Gallery
 */

use Envira\Utils\Shortcode_Utils;

/**
 * Legacy Envira_Gallery_Shortcode class
 */
class Envira_Gallery_Shortcode {

	/**
	 * _instance
	 *
	 * (default value: null)
	 *
	 * @var mixed
	 * @access public
	 * @static
	 */
	public static $_instance = null;

	/**
	 * Is mobile.
	 *
	 * @var boolean
	 */
	public $is_mobile = false;

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->is_mobile = envira_mobile_detect()->isMobile();
	}

	/**
	 * Get config.
	 *
	 * @deprecated  since 1.7.0 use envira_get_config.
	 *
	 * @param string $key config key.
	 * @param array  $data gallery data.
	 *
	 * @return mixed
	 */
	public function get_config( $key, $data ) {

		return envira_get_config( $key, $data );
	}

	/**
	 * Minify function.
	 *
	 * @param mixed $text String to minify.
	 * @param bool  $strip_double_forwardslashes (default: true).
	 *
	 * @return string
	 * @deprecated since 1.7.0.
	 *
	 * @access public
	 */
	public function minify( $text, $strip_double_forwardslashes = true ) {

		return envira_minify( $text, $strip_double_forwardslashes );
	}

	/**
	 * Is image.
	 *
	 * @deprecated since 1.7.0.
	 *
	 * @param string $url URL.
	 */
	public function is_image( $url ) {

		return envira_is_image( $url );
	}

	/**
	 * Is mobile check.
	 *
	 * @deprecated since 1.7.0. Use envira_mobile_detect()->isMobile().
	 */
	public function is_mobile() {

		return envira_mobile_detect()->isMobile();
	}


	/**
	 * Shortcode function.
	 *
	 * @deprecated since 1.7.0. Use Envira\Frontend\Shortcode::shortcode().
	 *
	 * @param array $atts Attrs.
	 */
	public function shortcode( $atts ) {

		$shortcode = new Envira\Frontend\Shortcode();
		return $shortcode->shortcode( $atts );
	}

	/**
	 * Maybe sort the gallery images, if specified in the config
	 *
	 * Note: To ensure backward compat with the previous 'random' config
	 * key, the sorting parameter is still stored in the 'random' config
	 * key.
	 *
	 * @deprecated since 1.7.0.
	 *
	 * @param array       $data       Gallery Config.
	 * @param int         $gallery_id Gallery ID.
	 * @param false|array $exclusions Gallery IDs.
	 */
	public function maybe_sort_gallery( $data, $gallery_id, $exclusions = false ) {

		if ( ! empty( $this->gallery_sort[ $gallery_id ] ) && ! empty( $data['gallery'] ) ) {
			// sort using the gallery_sort order.
			$data['gallery'] = array_replace( array_flip( $this->gallery_sort[ $gallery_id ] ), $data['gallery'] );
			return $data;
		}

		// Return if gallery is empty.
		if ( empty( $data['gallery'] ) ) {
			return $data;
		}

		// Get sorting method.
		$sorting_method    = (string) $this->get_config( 'random', $data );
		$sorting_direction = $this->get_config( 'sorting_direction', $data );

		// Sort images based on method.
		switch ( $sorting_method ) {

			/**
			* Random
			* - Again, by design, to ensure backward compat when upgrading from 1.3.7.x or older
			* where we had a 'random' key = 0 or 1. Sorting was introduced in 1.3.8
			*/
			case '1':
				// Shuffle keys.
				$keys = array_keys( $data['gallery'] );
				shuffle( $keys );

				// Rebuild array in new order.
				$new = [];
				foreach ( $keys as $key ) {
					// if one of these images is an exclusion, don't add it.
					if ( ! $exclusions || ( ! in_array( $key, $exclusions, true ) && ! array_key_exists( $key, $new ) ) ) {
						$new[ $key ] = $data['gallery'][ $key ];
					}
				}

				// Assign back to gallery.
				$data['gallery'] = $new;

				break;

			/**
			* Image Meta
			*/
			case 'src':
			case 'title':
			case 'caption':
			case 'alt':
			case 'link':
				// Get metadata.
				$keys = [];
				foreach ( $data['gallery'] as $id => $item ) {
					$keys[ $id ] = wp_strip_all_tags( $item[ $sorting_method ] );
				}

				// Sort titles / captions natcasesort is case-insensitive, unlike asort.
				natcasesort( $keys );

				// allow override of the type of sort.
				$keys = apply_filters( 'envira_gallery_sort_image_meta', $keys, $data, $sorting_method, $gallery_id );

				if ( 'DESC' === $sorting_direction ) {
					$keys = array_reverse( $keys, true );
				}

				// Iterate through sorted items, rebuilding gallery.
				$new = [];
				foreach ( $keys as $key => $title ) {
					$new[ $key ] = $data['gallery'][ $key ];
				}

				// Assign back to gallery.
				$data['gallery'] = $new;
				break;

			/**
			* Published Date
			*/
			case 'date':
				// Get published date for each.
				$keys = [];
				foreach ( $data['gallery'] as $id => $item ) {
					$attachment = get_post( $id );
					// If the attachment isn't in the Media Library, we can't get a post date - assume now.
					if ( ! is_numeric( $id ) || ( false === $attachment ) ) {
						$keys[ $id ] = wp_date( 'Y-m-d H:i:s' );
					} else {
						$keys[ $id ] = $attachment->post_date;
					}
				}

				// Sort titles / captions.
				if ( 'ASC' === $sorting_direction ) {
					asort( $keys );
				} else {
					arsort( $keys );
				}

				// Iterate through sorted items, rebuilding gallery.
				$new = [];
				foreach ( $keys as $key => $title ) {
					$new[ $key ] = $data['gallery'][ $key ];
				}

				// Assign back to gallery.
				$data['gallery'] = $new;
				break;

			/**
			* None
			* - Do nothing
			*/
			case '0':
			case '':
				break;

			/**
			* If developers have added their own sort options, let them run them here
			*/
			default:
				$data = apply_filters( 'envira_gallery_sort_gallery', $data, $sorting_method, $gallery_id );
				break;

		}

		// Set the sort order.
		if ( ! empty( $data['gallery'] ) ) {
			foreach ( $data['gallery'] as $id => $d ) {
				$this->gallery_sort[ $gallery_id ][] = $id;
			}
		}

		return $data;
	}

	/**
	 * Outputs an individual gallery item in the grid
	 *
	 * @deprecated since 1.7.0. Use Envira\Utils\Shortcode_Utils::get_single_item_markup().
	 *
	 * @param string $gallery Gallery HTML.
	 * @param array  $data Gallery Config.
	 * @param array  $item Gallery Item (Image).
	 * @param int    $item_id Gallery Image ID.
	 * @param int    $count Index.
	 *
	 * @return string Gallery HTML
	 */
	public function generate_gallery_item_markup( $gallery, $data, $item, $item_id, $count ) {

		return Shortcode_Utils::get_single_item_markup( $gallery, $data, $item, $item_id, $count );
	}

	/**
	 * Get instance function.
	 *
	 * @deprecated since 1.7.0.
	 *
	 * @access public
	 * @static
	 */
	public static function get_instance() {

		if ( ! isset( self::$_instance ) && ! ( self::$_instance instanceof Envira_Gallery_Shortcode ) ) {

			self::$_instance = new self();
		}

		return self::$_instance;
	}
}
