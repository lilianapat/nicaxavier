<?php
/**
 * Frontend utilities.
 *
 * @since ??
 *
 * @package Envira_Gallery
 */

namespace Envira\Utils;

use Envira\Frontend\Gallery_Markup\Layouts\Automatic;
use Envira\Frontend\Gallery_Markup\Layouts\Blogroll;
use Envira\Frontend\Gallery_Markup\Layouts\BnB;
use Envira\Frontend\Gallery_Markup\Layouts\BnB_Overlay;
use Envira\Frontend\Gallery_Markup\Layouts\Grid;
use Envira\Frontend\Gallery_Markup\Layouts\Mason;
use Envira\Frontend\Gallery_Markup\Layouts\Square;
use Envira\Frontend\Gallery_Markup\Layouts\Polaroid;
use Envira\Frontend\Gallery_Markup\Layouts\Creative;

/**
 * Utilities shared among shortcode classes.
 */
class Shortcode_Utils {
	/**
	 * Shortcode default attributes. Used on gallery and link shortcode.
	 */
	const DEFAULT_ATTRS = [
		'id'                 => null,
		'limit'              => null,
		'settings'           => null,
		'type'               => null,
		'slug'               => null,
		'images'             => null,
		'dynamic'            => null,
		'cache'              => true,
		'gallery_images_raw' => null,
		'bnb_overlay'        => null,
	];

	/**
	 * Main script loaded.
	 *
	 * @var bool
	 */
	private static $main_script_loaded = false;

	/**
	 * Enqueue Main Script.
	 *
	 * @param array $data Gallery data.
	 *
	 * @return void
	 */
	public static function enqueue_main_script( $data ) {
		if ( self::$main_script_loaded ) {
			return;
		}

		// TODO pass lazy loading delay to general config because it will only read the config on one gallery if there are multiple on the same page.
		$lazy_loading_delay = isset( $data['config']['lazy_loading_delay'] ) ? intval( $data['config']['lazy_loading_delay'] ) : '500';

		wp_enqueue_script( ENVIRA_SLUG . '-script' );
		wp_localize_script(
			ENVIRA_SLUG . '-script',
			'envira_gallery',
			[
				'debug'      => is_envira_debug_on(),
				'll_delay'   => $lazy_loading_delay,
				'll_initial' => 'false',
				'll'         => self::is_lazy_loading( $data ),
				'mobile'     => envira_mobile_detect()->isMobile(),
			]
		);

		self::$main_script_loaded = true;
	}

	/**
	 * Is lazy loading option on.
	 *
	 * @param array $data Gallery data.
	 *
	 * @return bool
	 */
	public static function is_lazy_loading( $data ) {
		// If this is being viewed by AMP, javascript is being disabled and lazy loading shouldn't be on.
		$is_ll = function_exists( 'ampforwp_is_amp_endpoint' ) && ampforwp_is_amp_endpoint()
			? false
			: filter_var( envira_get_config( 'lazy_loading', $data ), FILTER_VALIDATE_BOOLEAN );

		return apply_filters( 'envira_is_lazy_loading', $is_ll, $data );
	}

	/**
	 * Obtain gallery data and ids from shortcode attrs.
	 *
	 * @param array $attrs Shortcode attributes.
	 *
	 * @return array|null
	 */
	public static function get_data_and_id( $attrs ) {
		global $post;

		$parsed_attrs = self::get_parsed_attrs( $attrs );

		// Initialize ids.
		$gallery_id      = null;
		$options_id      = null;
		$use_cached_data = ! ( is_preview() || is_envira_debug_on() );

		if ( self::DEFAULT_ATTRS === $parsed_attrs ) {
			// Pull gallery id from current post. For envira post_type.
			$gallery_id = $post->ID;
			$data       = $use_cached_data ? envira_get_gallery( $gallery_id ) : _envira_get_gallery( $gallery_id );
		} elseif ( $parsed_attrs['id'] ) {
			$gallery_id = (int) $parsed_attrs['id'];

			// Determine data by type. Probably is deprecated.
			if ( ! $parsed_attrs['type'] ) {
				$data = $use_cached_data ? _envira_get_gallery( $gallery_id ) : envira_get_gallery( $gallery_id );
			} elseif ( 'widget' === $parsed_attrs['type'] && $parsed_attrs['settings'] ) {
					// Only used on random gallery widget.
					$settings         = json_decode( urldecode( $parsed_attrs['settings'] ), ARRAY_A );
					$base_gallery_id  = (int) $settings['base_gallery_id'];
					$data             = $use_cached_data ? envira_get_gallery( $base_gallery_id ) : _envira_get_gallery( $base_gallery_id );
					$lightbox_enabled = isset( $settings['lightbox'] ) ? (int) $settings['lightbox'] : $data['config']['lightbox_enabled'];

					$data['config']['lightbox_enabled'] = $lightbox_enabled;
					$data['config']['sort_order']       = '1';
			} else {
				// New filter for being able to maniupulate data for custom scenarios (widgets, Gutenberg, alien attacks, etc).
				$data = apply_filters(
					'envira_gallery_custom_gallery_data_by_' . $parsed_attrs['type'],
					$use_cached_data ? envira_get_gallery( $gallery_id ) : _envira_get_gallery( $gallery_id ),
					$attrs,
					$post,
					$gallery_id
				);
			}
		} elseif ( $parsed_attrs['slug'] ) {
			$gallery_id = $parsed_attrs['slug'];
			$data       = $use_cached_data ? envira_get_gallery_by_slug( $gallery_id ) : _envira_get_gallery_by_slug( $gallery_id );
			// We have the gallery data, now just translate slug into the ID.
			if ( $data['id'] ) {
				$gallery_id = (int) $data['id'];
			}
		} else {
			// TODO seems to be only used when 'dynamic' and 'images' attrs are passed.
			// A custom attribute must have been passed. Allow it to be filtered to grab data from a custom source.
			$data       = apply_filters( 'envira_gallery_custom_gallery_data', false, $attrs, $post );
			$gallery_id = isset( $data['config']['id'] ) ? $data['config']['id'] : $gallery_id;
			$options_id = isset( $data['dynamic_id'] ) ? $data['dynamic_id'] : $options_id;
		}

		if ( ! $options_id ) {
			$options_id = $gallery_id;
		}

		if ( ! $gallery_id || ! $data ) {
			if ( is_envira_debug_on() ) {
				( 'Empty gallery or data. id: ' . print_r( $gallery_id, true ) . PHP_EOL . print_r( $data, true ) );
			}

			return null;
		}

		if ( ! isset( $data['config'] ) ) {
			// Legacy lite versions do not store defaults if no settings are modified.
			$data['config'] = envira_get_config_defaults( $gallery_id );
		}

		// Force shortcode to return bnb_overlay layout.
		if ( ! empty( $parsed_attrs['bnb_overlay'] ) ) {
			$data['config']['layout'] = 'bnb-overlay';
		}

		return [
			'id'           => "$gallery_id",
			'options_id'   => absint( $options_id ),
			'data'         => $data,
			'parsed_attrs' => $parsed_attrs,
		];
	}

	/**
	 * Parse shortcode attributes.
	 *
	 * @param array $attrs Shortcode attributes.
	 *
	 * @return array|null
	 */
	private static function get_parsed_attrs( $attrs ) {
		// Set all values to string.
		$parsed_attrs = array_map( 'strval', wp_parse_args( $attrs, self::DEFAULT_ATTRS ) );

		// Sanitize specific attrs.
		$parsed_attrs['cache'] = $parsed_attrs['cache'] ? filter_var( $parsed_attrs['cache'], FILTER_VALIDATE_BOOLEAN ) : true;
		$parsed_attrs['limit'] = absint( $parsed_attrs['limit'] );

		return $parsed_attrs;
	}

	/**
	 * Sanitizes gallery/album description.
	 *
	 * @param array $data Gallery/Album data.
	 *
	 * @return string Gallery description
	 */
	public static function get_description( $data ) {
		$description = $data['config']['description'];

		// If the WP_Embed class is available, use that to parse the content using registered oEmbed providers.
		if ( isset( $GLOBALS['wp_embed'] ) ) {
			$description = $GLOBALS['wp_embed']->autoembed( $description );
		}

		// Get the description and apply most of the filters that apply_filters( 'the_content' ) would use
		// We don't use apply_filters( 'the_content' ) as this would result in a nested loop and a failure.
		$description = wptexturize( $description );
		$description = convert_smilies( $description );
		$description = wpautop( $description );
		$description = prepend_attachment( $description );

		return ( function_exists( 'wp_filter_content_tags' ) ) ? wp_filter_content_tags( $description ) : $description;
	}

	/**
	 * Generates html class string from array of classes.
	 *
	 * @param string[] $classes Array of classes.
	 *
	 * @return string
	 */
	public static function classnames( $classes ) {
		$sanitized = array_map( 'sanitize_html_class', $classes );

		// Avoid extra spaces in string by removing empty classes.
		$filtered = array_filter(
			$sanitized,
			function ( $class_name ) {
				return ! empty( $class_name );
			}
		);

		return implode( ' ', array_unique( $filtered ) );
	}

	/**
	 * Get the layout class.
	 *
	 * @param string $layout Layout name.
	 * @param array  $config Config array.
	 *
	 * @return object|null Layout class object.
	 */
	public static function get_layout_object( $layout, $config ) {
		$object = null;
		switch ( $layout ) {
			case 'automatic':
				$object = new Automatic( $config );
				break;
			case 'blogroll':
				$object = new Blogroll( $config );
				break;
			case 'grid':
				$object = new Grid( $config );
				break;
			case 'mason':
				$object = new Mason( $config );
				break;
			case 'square':
				$object = new Square( $config );
				break;
			case 'bnb':
				$object = ( new BnB( $config ) );
				break;
			case 'bnb-overlay':
				$object = ( new BnB_Overlay( $config ) );
				break;
			case 'polaroid':
				$object = ( new Polaroid( $config ) );
				break;
			case 'creative':
				$object = ( new Creative( $config ) );
				break;
			default:
				// no default.
		}

		return $object;
	}

	/**
	 * Outputs an individual gallery item in the grid
	 *
	 * @param string $gallery Gallery HTML.
	 * @param array  $data Gallery Config.
	 * @param array  $item Gallery Item (Image).
	 * @param int    $item_id Gallery Image ID.
	 * @param int    $count Index.
	 *
	 * @return string Gallery HTML
	 */
	public static function get_single_item_markup( $gallery, $data, $item, $item_id, $count ) {
		$layout = envira_get_config( 'layout', $data );
		// This config is not accurate as the real one can only be obtained from the Shortcode class.
		// It is compatible enough for single item render.
		$config = $data['config'];

		$layout_object = self::get_layout_object( $layout, $config );

		if ( 'bnb' === $layout ) {
			$layout_object = new BnB_Overlay( $config );
		}

		if ( null === $layout_object ) {
			return "ERROR: Layout not found: $layout";
		}

		return $layout_object->generate_gallery_item_markup( $gallery, $data, $item, $item_id, $count );
	}
}
