<?php
/**
 * Envira License Class Legacy
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
class Envira_Gallery_License {

	public static $_instance = null;
	public function __construct() {
	}

	public function notices() {
		// nothing to see here, just a fix for X theme
	}

	/**
	 * get_instance function.
	 *
	 * __Depricated since 1.7.0.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	public static function get_instance() {

		if ( ! isset( self::$_instance ) && ! ( self::$_instance instanceof Envira_Gallery_Settings ) ) {

			self::$_instance = new self();
		}

		return self::$_instance;
	}
}
