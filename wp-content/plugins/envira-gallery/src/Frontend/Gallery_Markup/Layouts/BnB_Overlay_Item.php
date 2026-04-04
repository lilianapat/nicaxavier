<?php
/**
 * Mason item layout class.
 *
 * @since ??
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

namespace Envira\Frontend\Gallery_Markup\Layouts;

use Envira\Frontend\Gallery_Markup\Item;
use Envira\Utils\Shortcode_Utils;

/**
 * Mason item class.
 */
class BnB_Overlay_Item extends Item {
	/**
	 * Automatic layout displays the caption and title on hover.
	 *
	 * @return string
	 */
	protected function gallery_image_caption_titles() {
		return '';
	}

	/**
	 * Helper method for getting item wrapper styles.
	 *
	 * @param numeric $gutter Gutter size.
	 * @param numeric $margin Margin.
	 *
	 * @return string
	 */
	protected function get_item_wrapper_styles( $gutter, $margin ) {
		return '';
	}

	/**
	 * Get the image title.
	 *
	 * @param string  $output_caption Output caption.
	 * @param numeric $item_id Item ID.
	 * @param array   $item Item.
	 * @param array   $data Data.
	 * @param int     $count Count.
	 *
	 * @return string
	 */
	protected function envira_gallery_output_after_link( $output_caption, $item_id, $item, $data, $count ) {
		$output = parent::envira_gallery_output_after_link( '', $item_id, $item, $data, $count );

		$caption_title_array = $this->get_title_caption();
		$caption_array       = [];

		if ( $caption_title_array['title'] ) {
			$caption_array[] = '<strong>' . wp_strip_all_tags( $caption_title_array['title'] ) . '</strong>';
		}

		if ( $caption_title_array['caption'] ) {
			$caption_array[] = wp_strip_all_tags( $caption_title_array['caption'] );
		}

		if ( empty( $caption_array ) ) {
			return $output;
		}

		$title_caption = implode( ' ', $caption_array );
		$classes_array = [ 'envira-gallery-item-bnb-title_caption' ];

		if ( false !== strpos( $output, 'envira-woocommerce' ) ) {
			$classes_array[] = 'envira-gallery-item-bnb-title_caption--with-woocommerce';
		}

		$classes    = Shortcode_Utils::classnames( $classes_array );
		$extra_html = "<div class=\"$classes\">$title_caption</div>";

		return $output . $extra_html;
	}
}
