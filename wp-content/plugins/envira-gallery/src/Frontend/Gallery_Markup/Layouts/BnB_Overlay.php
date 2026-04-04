<?php
/**
 * Grid layout class.
 *
 * @since ??
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

namespace Envira\Frontend\Gallery_Markup\Layouts;

use Envira\Frontend\Gallery_Markup\Base;

/**
 * Grid layout class.
 */
class BnB_Overlay extends Base {

	/**
	 * Get item config.
	 *
	 * @param array $config Item config.
	 *
	 * @return BnB_Overlay_Item
	 */
	protected function get_item_object( $config ) {
		return new BnB_Overlay_Item( $config );
	}

	/**
	 * Generate wrapper classes
	 *
	 * @since 1.9.0
	 */
	protected function get_wrapper_classes() {
		$classes   = parent::get_wrapper_classes();
		$classes[] = 'envira-layout-bnb--overlay';

		return $classes;
	}

	/**
	 * Get container class. Method intended to be extended.
	 *
	 * @return array
	 */
	protected function get_container_classes() {

		return [
			'envira-gallery-public',
			'envira-layout-bnb--overlay--container',
			'envira-clear',
		];
	}

	/**
	 * Gallery Markup.
	 *
	 * @return string
	 */
	public function markup() {
		$markup = parent::markup();
		return "<div id=\"envira-gallery-bnb-overlay-$this->unique_id\" class='envira-gallery-bnb-overlay-content'>$markup</div>";
	}
}
