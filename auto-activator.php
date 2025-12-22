<?php
/**
 * Auto-activator for MPS Plugin
 * Dropped in by Agent to fix local environment state.
 */
add_action('init', function() {
    if (!function_exists('activate_plugin')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    $plugin_path = 'my-pet-sitters/mps-core-loader.php';
    
    if (!is_plugin_active($plugin_path)) {
        $result = activate_plugin($plugin_path);
        if (is_wp_error($result)) {
            error_log('MPS Auto-Activation Failed: ' . $result->get_error_message());
        } else {
            error_log('MPS Auto-Activation Success.');
        }
    }
    
    // Attempt self-destruct to clean up
    @unlink(__FILE__);
});
