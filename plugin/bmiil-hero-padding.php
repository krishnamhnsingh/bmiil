<?php
/**
 * BMIIL — Hero top padding fix for Elementor container pages
 * Targets apply-now (2018), latest-events (1919), startup-funds (2052)
 */
add_action('wp_head', function() { ?>
<style>
/* Remove extra top gap on Elementor container-based pages */
.elementor-page-2018 .e-con:first-child,
.elementor-page-1919 .e-con:first-child,
.elementor-page-2052 .e-con:first-child {
  padding-top: 2.5rem !important;
  min-height: 0 !important;
}
.elementor-page-2018 .e-con:first-child .elementor-spacer,
.elementor-page-1919 .e-con:first-child .elementor-spacer,
.elementor-page-2052 .e-con:first-child .elementor-spacer {
  display: none !important;
}
</style>
<?php }, 8);
