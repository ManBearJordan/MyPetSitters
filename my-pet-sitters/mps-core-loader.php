<?php
/**
 * Plugin Name: MPS Core Loader - FIXED
 * Description: Safely loads the MPS Core snippet from mps-snippets. (v2.45.0 - PROFILE SKILLS)
 * Version: 2.45.0
 */

if (!defined('ABSPATH')) exit;

// Force Menu Setup (Runs on init)
require_once plugin_dir_path(__FILE__) . 'force-menu-setup.php';

// Force Page Content Fix (Runs on init) - V14
require_once plugin_dir_path(__FILE__) . 'force-page-fix.php';

$core_files = [
    '1-mps-core.php',
    '2-mps-pages.php',
    '3-mps-sitter-profiles.php',
    '4-mps-auth.php',
    '5-mps-dashboard.php',
    '6-mps-admin.php',
    '7-mps-homepage.php',
    '8-mps-messaging.php',
    '9-mps-reviews.php',
    '10-mps-bookings.php',
    '11-mps-calendar.php',
    '12-mps-pets.php',
    '13-mps-favorites.php',
    '14-mps-landing.php',
    '15-mps-account.php',
    '16-mps-admin-dashboard.php',
    '17-mps-emails.php',
    '18-mps-security.php',
    '19-mps-edit-profile.php',
    'mps-installer.php'
];


if (!defined('ABSPATH')) exit;

foreach ($core_files as $file) {
    require_once plugin_dir_path(__FILE__) . $file;
}

// REGISTER ACTIVATION HOOK
register_activation_hook(__FILE__, 'mps_run_installer');

// AUTO-REPAIR: Check for missing pages on admin init (Fixes 404s if installer didn't run)
add_action('admin_init', 'mps_check_pages_exist');
function mps_check_pages_exist() {
    if (get_option('mps_pages_checked_v41')) return;
    
    $required = [
        'edit-profile' => '[mps_edit_profile]',
        'join' => '[mps_register]',
        'account' => '[mps_dashboard]',
        'list-your-services' => '[mps_sitter_landing]'
    ];
    
    foreach ($required as $slug => $content) {
        if (!get_page_by_path($slug)) {
            $title = ucwords(str_replace('-', ' ', $slug));
            wp_insert_post([
                'post_title' => $title,
                'post_name' => $slug,
                'post_content' => $content,
                'post_status' => 'publish',
                'post_type' => 'page'
            ]);
        }
    }
    update_option('mps_pages_checked_v41', time());
}

