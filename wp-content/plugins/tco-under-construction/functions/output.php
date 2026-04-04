<?php

// =============================================================================
// FUNCTIONS/OUTPUT.PHP
// -----------------------------------------------------------------------------
// Plugin output.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Under Construction
//   02. Output
// =============================================================================

// Under Construction
// =============================================================================

function get_user_IP() { //changed to eliminate warnings

    $client  = in_array( 'HTTP_CLIENT_IP', $_SERVER ) ? $_SERVER['HTTP_CLIENT_IP'] : '';
    $forward = in_array( 'HTTP_X_FORWARDED_FOR', $_SERVER ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';
    $remote  = $_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP)){
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP)){
        $ip = $forward;
    }
    else{
        $ip = $remote;
    }

    return $ip == '::1' ? '127.0.0.1' : $ip ; //Allow it for localhost

}

function is_allowed_ip ( $allowed_ips ) {

    if ( isset( $allowed_ips ) && !empty( $allowed_ips ) ) {

      $allowed_ips = explode(' ', $allowed_ips);
      $user_ip = get_user_IP();

      return in_array( $user_ip, $allowed_ips);

    }

    return false;

}

function tco_under_construction_output( $original_template ) {

  require( TCO_UNDER_CONSTRUCTION_PATH . '/functions/options.php' );

  // Handled by template_redirect
  if ( isset( $tco_under_construction_use_custom ) && $tco_under_construction_use_custom == 1 ) {
    return $original_template;
  }

  if ( isset( $tco_under_construction_enable ) && $tco_under_construction_enable == 1 && ! is_user_logged_in() ) {

    if( isset( $_COOKIE['tco_under_construction_bypass'] ) && $_COOKIE['tco_under_construction_bypass'] == $tco_under_construction_bypass_token ) {
      return $original_template;
    }

    if ( !empty($tco_under_construction_whitelist) && is_allowed_ip( $tco_under_construction_whitelist ) ) {

      return $original_template;

    }

    //Just remove Opengraph regardless of response code
    remove_action( 'wp_head', 'x_social_meta', 2 );

    return TCO_UNDER_CONSTRUCTION_PATH . '/views/site/under-construction.php';

  } else {

    return $original_template;

  }

}

function tco_under_construction_bypass_output () {

    require TCO_UNDER_CONSTRUCTION_PATH . '/views/site/bypass.php';

}


// Under Construction Custom Page
// =============================================================================

function tco_under_construction_custom_output( $original_template ) {

  require( TCO_UNDER_CONSTRUCTION_PATH . '/functions/options.php' );

  $custom_post = get_post( (int) $tco_under_construction_custom );

  if ( ! is_a( $custom_post, 'WP_Post' ) ) {
    return $original_template;
  }

  GLOBAL $wp_query;
  GLOBAL $post;

  $post = $custom_post;
  $wp_query = new WP_Query();
  $wp_query->query("page_id=" . $post->ID);

  if ( isset( $tco_under_construction_bypass_password ) && !empty ( $tco_under_construction_bypass_password ) ) {
    add_action( 'wp_footer', 'tco_under_construction_bypass_output' );
  }

  return get_page_template();
}



// Output
// =============================================================================

add_filter( 'template_include', 'tco_under_construction_output', 99);


/**
 * Custom page redirects the entire page before Services\FrontEnd
 */
add_filter("template_redirect", function($template) {

  require( TCO_UNDER_CONSTRUCTION_PATH . '/functions/options.php' );

  if (empty($tco_under_construction_use_custom ) || is_user_logged_in()) {
    return $template;
  }

  // Not enabled
  if ( empty( $tco_under_construction_enable ) ) {
    return $template;
  }

  // Already good to go
  if ( isset( $tco_under_construction_enable ) && $tco_under_construction_enable == 1 ) {
    if( isset( $_COOKIE['tco_under_construction_bypass'] ) && $_COOKIE['tco_under_construction_bypass'] == $tco_under_construction_bypass_token ) {
      return $template;
    }
  }

  // IP bypass
  if ( !empty($tco_under_construction_whitelist) && is_allowed_ip( $tco_under_construction_whitelist ) ) {
    return $template;
  }

  // Change current page
  return tco_under_construction_custom_output( $tco_under_construction_use_custom );
}, -99);
