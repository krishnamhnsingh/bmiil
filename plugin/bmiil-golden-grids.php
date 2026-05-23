<?php
/**
 * BMIIL — Golden Grid Lines on all pages
 * Add via Code Snippets → PHP → Run everywhere
 */
add_action('wp_head', function() { ?>
<style id="bmiil-golden-grids">
/* Make all Elementor section background grid lines gold (0.03 opacity) */
/* This targets the rgba(196,154,42,0.03) grid pattern used in templates */
/* Already golden - this ensures they're always visible */

/* Override any grey/white grid patterns with gold */
[style*="background-image:linear-gradient(rgba(196,154,42,0.03)"],
[style*="background-image: linear-gradient(rgba(196,154,42,0.03)"] {
  background-image:
    linear-gradient(rgba(196,154,42,0.08) 1px, transparent 1px),
    linear-gradient(90deg, rgba(196,154,42,0.08) 1px, transparent 1px) !important;
}

/* Brighter version for dark navy sections */
[style*="background:#08122A"] [style*="background-image:linear-gradient"],
[style*="background:#0F1F45"] [style*="background-image:linear-gradient"] {
  background-image:
    linear-gradient(rgba(196,154,42,0.10) 1px, transparent 1px),
    linear-gradient(90deg, rgba(196,154,42,0.10) 1px, transparent 1px) !important;
}
</style>
<?php }, 6);
