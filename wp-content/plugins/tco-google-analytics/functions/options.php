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

GLOBAL $tco_google_analytics_options;

if ( isset( $_POST['tco_google_analytics_form_submitted'] ) ) {
  if ( strip_tags( $_POST['tco_google_analytics_form_submitted'] ) == 'submitted' && current_user_can( 'manage_options' ) && current_user_can( 'manage_options' ) && isset($_POST['tco_google_analytics_noncename']) && wp_verify_nonce( $_POST['tco_google_analytics_noncename'], 'tco_google_analytics' )) {

    $tco_google_analytics_options['tco_google_analytics_enable']   = ( isset( $_POST['tco_google_analytics_enable'] ) ) ? strip_tags( $_POST['tco_google_analytics_enable'] ) : '';
    $tco_google_analytics_options['tco_google_analytics_position'] = strip_tags( $_POST['tco_google_analytics_position'] );
    $tco_google_analytics_options['tco_google_analytics_implementation'] = strip_tags( $_POST['tco_google_analytics_implementation'] );
    $tco_google_analytics_options['tco_google_analytics_id'] = strip_tags( $_POST['tco_google_analytics_id'] );
    $tco_google_analytics_options['tco_google_analytics_track_all'] = ( isset( $_POST['tco_google_analytics_track_all'] ) ) ? strip_tags( $_POST['tco_google_analytics_track_all'] ) : '';
    $tco_google_analytics_options['tco_meta_tag']     = stripslashes( wp_kses( $_POST['tco_meta_tag'], array( 'meta' => array( 'content' => array(), 'name' => array() ) ) ) );


    update_option( 'tco_google_analytics', $tco_google_analytics_options );

  }
}



// Get Options
// =============================================================================

$tco_google_analytics_options = apply_filters( 'tco_google_analytics_options', get_option( 'tco_google_analytics' ) );

if ( $tco_google_analytics_options != '' ) {

  $tco_google_analytics_enable   = $tco_google_analytics_options['tco_google_analytics_enable'];
  $tco_google_analytics_position = $tco_google_analytics_options['tco_google_analytics_position'];
  $tco_google_analytics_implementation = $tco_google_analytics_options['tco_google_analytics_implementation'];
  $tco_google_analytics_id  	 = $tco_google_analytics_options['tco_google_analytics_id'];
  $tco_google_analytics_track_all     = $tco_google_analytics_options['tco_google_analytics_track_all'];
  $tco_meta_tag 				 = $tco_google_analytics_options['tco_meta_tag'];

}
