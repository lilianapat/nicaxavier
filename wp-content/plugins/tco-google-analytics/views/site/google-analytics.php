<?php

// =============================================================================
// VIEWS/SITE/GOOGLE-ANALYTICS.PHP
// -----------------------------------------------------------------------------
// Plugin site output.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Require Options
//   02. Output
// =============================================================================

// Require Options
// =============================================================================

require( TCO_GOOGLE_ANALYTICS_PATH . '/functions/options.php' );



// Output
// =============================================================================

// Check if has admin capabilities
if ( (! ( is_user_logged_in() && current_user_can( 'update_core' ) ) ) || $tco_google_analytics_track_all == true ) {
?>

<!-- Google Analytics -->
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', '<?php echo $tco_google_analytics_id; ?>', 'auto');
    ga('send', 'pageview');
</script>
<!-- End Google Analytics -->

<?php
	echo $tco_meta_tag;
}
