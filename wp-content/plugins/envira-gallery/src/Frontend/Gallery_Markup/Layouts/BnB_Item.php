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
class BnB_Item extends Item {

	/**
	 * Construct.
	 *
	 * @param array $config All needed data.
	 */
	public function __construct( $config ) {
		parent::__construct( $config );
		$this->item_id = "grid-item-{$config['item_id']}";
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
	 * Generate link open tag.
	 *
	 * @param string $lightbox_caption Lightbox caption.
	 * @param string $lightbox_title Lightbox title.
	 *
	 * @return string
	 */
	protected function get_link_open_tag( $lightbox_caption, $lightbox_title ) {
		$link_class = Shortcode_Utils::classnames(
			[
				"envira-gallery-$this->unique_id",
				'envira-gallery-bnb-link',
				'envira-gallery-bnb-item',
			]
		);

		$link_href  = esc_url(
			apply_filters(
				'envira_gallery_link_href',
				$this->item['link'],
				$this->data,
				$this->item_id,
				$this->item,
				$this->count,
				$this->is_mobile
			)
		);
		$link_title = wp_strip_all_tags( htmlspecialchars( str_replace( '<', '&lt;', $this->item['title'] ) ) );

		return "<a class=\"$link_class\" href=\"$link_href\" title=\"$link_title\" itemprop=\"contentUrl\">";
	}
}
