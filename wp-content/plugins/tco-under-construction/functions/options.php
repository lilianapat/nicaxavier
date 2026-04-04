<?php

// =============================================================================
// FUNCTIONS/OPTIONS.PHP
// -----------------------------------------------------------------------------
// Plugin options.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Set Options
//   02. Get Options
// =============================================================================

// Set Options
// =============================================================================

//
// Set $_POST variables to options array and update option.
//

GLOBAL $tco_under_construction_options;

// Default options to not cause undefined index warnings
$UNDER_CONSTRUCTION_OPTIONS = [
  'tco_under_construction_enable' => false,
  'tco_under_construction_status_header' => false,
  'tco_under_construction_use_custom' => false,
  'tco_under_construction_custom' => false,
  'tco_under_construction_heading' => false,
  'tco_under_construction_subheading' => false,
  'tco_under_construction_extra_text' => false,
  'tco_under_construction_date' => false,
  'tco_under_construction_background_image' => false,
  'tco_under_construction_logo_image' => false,
  'tco_under_construction_background_color' => false,
  'tco_under_construction_heading_color' => false,
  'tco_under_construction_subheading_color' => false,
  'tco_under_construction_date_color' => false,
  'tco_under_construction_social_color' => false,
  'tco_under_construction_whitelist' => false,
  'tco_under_construction_bypass_password' => false,
  'tco_under_construction_bypass_expiration' => false,
  'tco_under_construction_bypass_token' => false,
];

$social_medias = array(
  'facebook'    => array( 'title' => 'Facebook',    'tco-icon' => '&#xf082;', 'type' => 'b' ),
  'twitter'     => array( 'title' => 'Twitter',     'tco-icon' => '&#xf081;', 'type' => 'b' ),
  'google_plus' => array( 'title' => 'Google Plus', 'tco-icon' => '&#xf0d4;', 'type' => 'b' ),
  'linkedin'    => array( 'title' => 'Likedin',     'tco-icon' => '&#xf08c;', 'type' => 'b' ),
  'xing'        => array( 'title' => 'XING',        'tco-icon' => '&#xf169;', 'type' => 'b' ),
  'foursquare'  => array( 'title' => 'Foursquare',  'tco-icon' => '&#xf180;', 'type' => 'b' ),
  'youtube'     => array( 'title' => 'Youtube',     'tco-icon' => '&#xf431;', 'type' => 'b' ),
  'vimeo'       => array( 'title' => 'Vimeo',       'tco-icon' => '&#xf194;', 'type' => 'b' ),
  'instagram'   => array( 'title' => 'Instagram',   'tco-icon' => '&#xf16d;', 'type' => 'b' ),
  'pinterest'   => array( 'title' => 'Pinterest',   'tco-icon' => '&#xf0d3;', 'type' => 'b' ),
  'dribbble'    => array( 'title' => 'Dribbble',    'tco-icon' => '&#xf17d;', 'type' => 'b' ),
  'flickr'      => array( 'title' => 'Flickr',      'tco-icon' => '&#xf16e;', 'type' => 'b' ),
  'github'      => array( 'title' => 'Github',      'tco-icon' => '&#xf092;', 'type' => 'b' ),
  'behance'     => array( 'title' => 'Behance',     'tco-icon' => '&#xf1b5;', 'type' => 'b' ),
  'tumblr'      => array( 'title' => 'Tumblr',      'tco-icon' => '&#xf174;', 'type' => 'b' ),
  'whatsapp'    => array( 'title' => 'Whatsapp',    'tco-icon' => '&#xf232;', 'type' => 'b' ),
  'soundcloud'  => array( 'title' => 'SoundCloud',  'tco-icon' => '&#xf1be;', 'type' => 'b' ),
  'rss'         => array( 'title' => 'RSS',         'tco-icon' => '&#xf143;', 'type' => 's' ),
);

if ( isset( $_POST['tco_under_construction_form_submitted'] ) ) {
  if ( sanitize_text_field( $_POST['tco_under_construction_form_submitted'] ) == 'submitted' && current_user_can( 'manage_options' ) && current_user_can( 'manage_options' ) && isset($_POST['tco_under_construction_noncename']) && wp_verify_nonce( $_POST['tco_under_construction_noncename'], 'tco_under_construction' ) ) {

     //For bypass validation and session termination
    $tco_under_construction_options = get_option( 'tco_under_construction' );
    $new_password = sanitize_text_field( $_POST['tco_under_construction_bypass_password'] );
    $old_password = $tco_under_construction_options['tco_under_construction_bypass_password'];

    $tco_under_construction_options['tco_under_construction_enable']           = ( isset( $_POST['tco_under_construction_enable'] ) ) ? sanitize_text_field( $_POST['tco_under_construction_enable'] ) : '';
    $tco_under_construction_options['tco_under_construction_use_custom']       = ( isset( $_POST['tco_under_construction_use_custom'] ) ) ? sanitize_text_field( $_POST['tco_under_construction_use_custom'] ) : '';
    $tco_under_construction_options['tco_under_construction_status_header']       = ( isset( $_POST['tco_under_construction_status_header'] ) ) ? sanitize_text_field( $_POST['tco_under_construction_status_header'] ) : '';
    $tco_under_construction_options['tco_under_construction_custom']           = sanitize_text_field( $_POST['tco_under_construction_custom'] );
    $tco_under_construction_options['tco_under_construction_heading']          = sanitize_text_field( $_POST['tco_under_construction_heading'] );
    $tco_under_construction_options['tco_under_construction_subheading']       = sanitize_text_field( $_POST['tco_under_construction_subheading'] );
    $tco_under_construction_options['tco_under_construction_extra_text']       = strip_tags( $_POST['tco_under_construction_extra_text'] );
    $tco_under_construction_options['tco_under_construction_date']             = sanitize_text_field( $_POST['tco_under_construction_date'] );
    $tco_under_construction_options['tco_under_construction_background_image'] = sanitize_text_field( $_POST['tco_under_construction_background_image'] );
    $tco_under_construction_options['tco_under_construction_logo_image']       = sanitize_text_field( $_POST['tco_under_construction_logo_image'] );
    $tco_under_construction_options['tco_under_construction_background_color'] = sanitize_text_field( $_POST['tco_under_construction_background_color'] );
    $tco_under_construction_options['tco_under_construction_heading_color']    = sanitize_text_field( $_POST['tco_under_construction_heading_color'] );
    $tco_under_construction_options['tco_under_construction_subheading_color'] = sanitize_text_field( $_POST['tco_under_construction_subheading_color'] );
    $tco_under_construction_options['tco_under_construction_date_color']       = sanitize_text_field( $_POST['tco_under_construction_date_color'] );
    $tco_under_construction_options['tco_under_construction_social_color']     = sanitize_text_field( $_POST['tco_under_construction_social_color'] );
    $tco_under_construction_options['tco_under_construction_whitelist']        = sanitize_text_field( $_POST['tco_under_construction_whitelist'] );
    $tco_under_construction_options['tco_under_construction_bypass_password']  = $new_password;
    $tco_under_construction_options['tco_under_construction_bypass_expiration']  = sanitize_text_field( $_POST['tco_under_construction_bypass_expiration'] );


    foreach ( $social_medias as $key => $sc ) {
      $key = "tco_under_construction_{$key}";
      $tco_under_construction_options[ $key ] = sanitize_text_field( $_POST[ $key ] );
    }

    if ( $old_password !== $new_password  || empty( $new_password ) ) {
      $tco_under_construction_options['tco_under_construction_bypass_token'] = wp_generate_password( 15, false, false );
    }

    update_option( 'tco_under_construction', $tco_under_construction_options );

  }
}



// Get Options
// =============================================================================

$tco_under_construction_options = apply_filters( 'tco_under_construction_options', get_option( 'tco_under_construction' ) );

if ( $tco_under_construction_options != '' ) {

  // Set default options
  $tco_under_construction_options = array_merge($UNDER_CONSTRUCTION_OPTIONS, $tco_under_construction_options);

  $tco_under_construction_enable           = $tco_under_construction_options['tco_under_construction_enable'];
  $tco_under_construction_status_header    = $tco_under_construction_options['tco_under_construction_status_header'];
  $tco_under_construction_use_custom       = $tco_under_construction_options['tco_under_construction_use_custom'];
  $tco_under_construction_custom           = $tco_under_construction_options['tco_under_construction_custom'];
  $tco_under_construction_heading          = $tco_under_construction_options['tco_under_construction_heading'];
  $tco_under_construction_subheading       = $tco_under_construction_options['tco_under_construction_subheading'];
  $tco_under_construction_extra_text       = $tco_under_construction_options['tco_under_construction_extra_text'];
  $tco_under_construction_date             = $tco_under_construction_options['tco_under_construction_date'];
  $tco_under_construction_background_image = $tco_under_construction_options['tco_under_construction_background_image'];
  $tco_under_construction_logo_image       = $tco_under_construction_options['tco_under_construction_logo_image'];
  $tco_under_construction_background_color = $tco_under_construction_options['tco_under_construction_background_color'];
  $tco_under_construction_heading_color    = $tco_under_construction_options['tco_under_construction_heading_color'];
  $tco_under_construction_subheading_color = $tco_under_construction_options['tco_under_construction_subheading_color'];
  $tco_under_construction_date_color       = $tco_under_construction_options['tco_under_construction_date_color'];
  $tco_under_construction_social_color     = $tco_under_construction_options['tco_under_construction_social_color'];
  $tco_under_construction_whitelist        = $tco_under_construction_options['tco_under_construction_whitelist'];
  $tco_under_construction_bypass_password  = $tco_under_construction_options['tco_under_construction_bypass_password'];
  $tco_under_construction_bypass_expiration  = $tco_under_construction_options['tco_under_construction_bypass_expiration'];
  $tco_under_construction_bypass_token     = empty( $tco_under_construction_options['tco_under_construction_bypass_token'] ) ? '' : $tco_under_construction_options['tco_under_construction_bypass_token'] ; //Fallback condition for older versions

  foreach ( $social_medias as $key => $sc ) {
    $key = "tco_under_construction_{$key}";
    $$key = $tco_under_construction_options[ $key ];
  }

}
