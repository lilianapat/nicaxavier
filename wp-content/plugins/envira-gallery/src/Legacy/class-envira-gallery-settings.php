<?php
/**
 * Importers class.
 *
 * @since 1.7.0
 *
 * @package Envira Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Envira Gallery Settings Legacy Class
 */
class Envira_Gallery_Settings {

	/**
	 * Helper Method for Singleton.
	 *
	 * @var object
	 */
	public static $_instance = null;

	/**
	 * Class Constructor
	 */
	public function __construct() {}

	/**
	 * __Depricated since 1.7.0 use envira_get_setting.
	 *
	 * @access public
	 * @param mixed $setting Value of settings.
	 * @return mixed
	 */
	public function get_setting( $setting ) {

		return envira_get_setting( $setting );
	}

	/**
	 *  Nothing to see here, just a fix for X theme
	 *
	 * @return void
	 */
	public function admin_menu() {}

	/**
	 * Update Settings.
	 *
	 * __Depricated since 1.7.0.
	 *
	 * @access public
	 * @param string $key Setting Key.
	 * @param mixed  $value Setting Value.
	 * @return mixed
	 */
	public function update_setting( $key, $value ) {

		return envira_update_setting( $key, $value );
	}

	/**
	 * Get Class instance.
	 *
	 * __Depricated since 1.7.0.
	 *
	 * @access public
	 * @static
	 * @return object
	 */
	public static function get_instance() {

		if ( ! isset( self::$_instance ) && ! ( self::$_instance instanceof Envira_Gallery_Settings ) ) {

			self::$_instance = new self();
		}

		return self::$_instance;
	}
}
