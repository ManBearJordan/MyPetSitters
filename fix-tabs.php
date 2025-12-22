<?php
/**
 * Force Tabs to Show for Sitter Registration
 * Hooks into shortcode attributes to guarantee role='' when default_tab='sitter'
 */
add_filter('shortcode_atts_mps_register', function($out, $pairs, $atts, $shortcode) {
    // If the shortcode is calling for default_tab='sitter', it implies we want BOTH tabs.
    // Ensure role is empty to allow tabs to render.
    if (isset($atts['default_tab']) && $atts['default_tab'] === 'sitter') {
        $out['role'] = ''; 
    }
    return $out;
}, 20, 4);
