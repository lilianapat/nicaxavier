<?php
/**
 * Legacy Updater Class.
 *
 * @since 1.0.0
 *
 * @package Envira Gallery
 */

use Envira\Utils\Updater;

/**
 * Legacy Updater Class.
 *
 * @since 1.0.0
 * @deprecated 1.9.15
 */
class Envira_Gallery_Updater {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.7.0
	 *
	 * @param array $config Array of updater config args.
	 */
	public function __construct( array $config ) {
		new Updater( $config );
	}
}
