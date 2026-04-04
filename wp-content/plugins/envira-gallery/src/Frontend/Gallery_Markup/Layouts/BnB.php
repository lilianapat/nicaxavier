<?php
/**
 * Mason layout class.
 *
 * @since ??
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

namespace Envira\Frontend\Gallery_Markup\Layouts;

use Envira\Frontend\Gallery_Markup\Base;
use Envira\Utils\Shortcode_Utils;

/**
 * Grid Overlay (BnB) layout class.
 */
class BnB extends Base {
	/**
	 * Unique ID for the overlay.
	 *
	 * @var string
	 */
	protected $overlay_unique_id;

	/**
	 * Overlay Object.
	 *
	 * @var array $config All needed data.
	 */
	private $overlay_object;

	/**
	 * Construct.
	 *
	 * @param array $config All needed data.
	 */
	public function __construct( $config ) {
		$this->overlay_object = new BnB_Overlay( $config );

		// Obtain an unaltered version of data.
		$config['data'] = Shortcode_Utils::get_data_and_id( $config['parsed_attrs'] )['data'];

		parent::__construct( $config );

		// Limit gallery display to only the first 5 images.
		$this->data['gallery'] = array_slice( $config['data']['gallery'], 0, 5, true );

		// Disable pagination.
		$this->data['config']['pagination'] = false;

		// Disable downloads.
		$this->data['config']['download'] = false;

		// Disable exif.
		$this->data['config']['exif'] = false;

		// Disable print.
		$this->data['config']['print'] = false;

		// Disable lightbox.
		$this->data['config']['lightbox_enabled'] = false;

		// Disable mobile lightbox.
		$this->data['config']['mobile_lightbox'] = false;

		// Disable social.
		$this->data['config']['social']        = false;
		$this->data['config']['mobile_social'] = false;

		// Disable tags.
		$this->data['config']['tags'] = false;

		// Disable woocommerce.
		$this->data['config']['woocommerce'] = false;

		// Disable proofing.
		$this->data['config']['proofing'] = false;

		$this->data['config']['mobile_gallery_link_enabled'] = true;

		$this->overlay_unique_id = $this->unique_id;

		$this->unique_id = "grid-{$config['unique_id']}";
	}

	/**
	 * Get loader HTML.
	 */
	protected function get_loader() {
		if ( ! envira_get_config( 'lazy_loading', $this->data ) ) {
			return '';
		}
		return parent::get_loader();
	}

	/**
	 * Generate wrapper classes
	 *
	 * @since 1.9.0
	 */
	protected function get_wrapper_classes() {
		$classes   = parent::get_wrapper_classes();
		$classes[] = 'envira-layout-bnb';

		return $classes;
	}

	/**
	 * Get container class. Method intended to be extended.
	 *
	 * @return array
	 */
	protected function get_container_classes() {
		return [
			'envira-layout-bnb--container',
		];
	}

	/**
	 * Get item config.
	 *
	 * @param array $config Item config.
	 *
	 * @return BnB_Item
	 */
	protected function get_item_object( $config ) {
		return new BnB_Item( $config );
	}

	/**
	 * Get Container Extra Attrs
	 *
	 * @return array
	 */
	protected function get_container_extra_attrs() {
		$gutter = envira_get_config( 'gutter', $this->data, envira_get_config_default( 'gutter' ) );
		if ( $this->is_mobile ) {
			$gutter = envira_get_config( 'gutter_mobile', $this->data, $gutter );
		}

		$style = "style=\"grid-gap: {$gutter}px\"";

		return [ $style ];
	}

	/**
	 * Get Container Extra Attrs
	 *
	 * @return array
	 */
	protected function get_container_data() {
		return [
			"data-unique-id='$this->overlay_unique_id'",
		];
	}

	/**
	 * HTML gallery output after container.
	 *
	 * @param string $gallery_markup Gallery Markup.
	 *
	 * @return string|null
	 */
	protected function envira_gallery_output_after_container( $gallery_markup ) {
		$classes = [
			'envira-layout-bnb--more-link',
		];

		$button_text = envira_get_config( 'show_more_text', $this->data );
		if ( empty( $button_text ) ) {
			$classes[] = 'envira-layout-bnb--more-link--empty';
		}

		$classes  = apply_filters( 'envira_bnb_more_link_classes', $classes, $this->data );
		$classes  = implode( ' ', $classes );
		$icon_url = ENVIRA_URL . 'assets/images/bnb-more-icon.svg';

		return "$gallery_markup<button class=\"$classes\"><img src=\"$icon_url\" alt=\"$button_text\"/>$button_text</button>";
	}

	/**
	 * Gallery Markup.
	 *
	 * @return string
	 */
	public function markup() {
		return parent::markup() . $this->overlay_object->markup();
	}
}
