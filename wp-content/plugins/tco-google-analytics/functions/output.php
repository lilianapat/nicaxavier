<?php

// =============================================================================
// FUNCTIONS/OUTPUT.PHP
// -----------------------------------------------------------------------------
// Plugin output.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Google Analytics
//   02. Output
// =============================================================================

// Google Analytics
// =============================================================================

function tco_google_analytics_output() {

  require( TCO_GOOGLE_ANALYTICS_PATH . '/views/site/google-analytics.php' );

}


function tco_google_tag_manager_output() {

  require( TCO_GOOGLE_ANALYTICS_PATH . '/views/site/google-tag-manager.php' );

}


// Output
// =============================================================================

require( TCO_GOOGLE_ANALYTICS_PATH . '/functions/options.php' );

if ( isset( $tco_google_analytics_enable ) && $tco_google_analytics_enable == 1 ) {
  
  $callback = empty( $tco_google_analytics_implementation ) || $tco_google_analytics_implementation == 'ga' ? 'tco_google_analytics_output' : 'tco_google_tag_manager_output';

  add_action( 'wp_' . $tco_google_analytics_position, $callback, 9999 );

}
