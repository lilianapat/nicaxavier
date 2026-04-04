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

/**
 * Creative item class.
 */
class Creative_Item extends Item {
	/**
	 * Displays the caption and title on hover.
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
}
