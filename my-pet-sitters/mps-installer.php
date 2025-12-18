<?php
/**
 * MPS INSTALLER
 * 
 * Runs on Plugin Activation.
 * - Creates necessary Pages (Login, Dashboard, Landing, etc.)
 * - Sets up Primary Menu
 * - Configures Roles/Capabilities
 */

if (!defined('ABSPATH')) exit;


// register_activation_hook handled in mps-core-loader.php


function mps_run_installer() {
    // 1. CREATE PAGES
    $pages = [
        'login' => [
            'title' => 'Login',
            'content' => '[mps_login]'
        ],
        'join' => [
            'title' => 'Join',
            'content' => '[mps_register]' // Corrected from mps_registration
        ],
        'account' => [
            'title' => 'My Account',
            'content' => '[mps_dashboard]'
        ],
        'list-your-services' => [
            'title' => 'List Your Services',
            'content' => '[mps_sitter_landing]' // Corrected from mps_sitter_registration
        ],
        'list-your-services' => [
            'title' => 'List Your Services',
            'content' => '[mps_sitter_landing]'
        ],
        'edit-profile' => [
            'title' => 'Edit My Profile',
            'content' => '[mps_edit_profile]'
        ],
        'cities' => [
            'title' => 'Find a Pet Sitter',
            'content' => '[mps_cities]' // Corrected from mps_landing_page
        ],
        'services' => [
            'title' => 'Our Services',
            'content' => '[mps_services]' // Added V15
        ],
        'messages' => [
            'title' => 'Messages',
            'content' => '[mps_inbox]'
        ],
        'become-a-sitter' => [
            'title' => 'Become a Pet Sitter',
            'content' => '[mps_sitter_landing]'
        ],
        'lost-password' => [
            'title' => 'Lost Password',
            'content' => '[mps_lost_password]'
        ],
        'reset-password' => [
            'title' => 'Reset Password',
            'content' => '[mps_reset_password]'
        ]
    ];
    
    foreach ($pages as $slug => $data) {
        if (!get_page_by_path($slug)) {
            wp_insert_post([
                'post_title' => $data['title'],
                'post_name' => $slug,
                'post_content' => $data['content'],
                'post_status' => 'publish',
                'post_type' => 'page'
            ]);
        }
    }
    
    // 2. SETUP MENU (Only if 'Primary Menu' lacks items)
    $menu_name = 'Primary Menu';
    $menu = wp_get_nav_menu_object($menu_name);
    
    if (!$menu) {
        $menu_id = wp_create_nav_menu($menu_name);
        if (!is_wp_error($menu_id)) {
            // Add Items
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => 'Home',
                'menu-item-url' => home_url('/'),
                'menu-item-status' => 'publish'
            ]);
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => 'Find a Sitter',
                'menu-item-url' => home_url('/cities/'),
                'menu-item-status' => 'publish'
            ]);
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => 'Become a Sitter',
                'menu-item-url' => home_url('/become-a-sitter/'),
                'menu-item-status' => 'publish'
            ]);
             wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => 'Join',
                'menu-item-url' => home_url('/join/'),
                'menu-item-status' => 'publish'
            ]);
             wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => 'Login',
                'menu-item-url' => home_url('/login/'),
                'menu-item-status' => 'publish'
            ]);
             
             // List Your Services Button
             wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => 'List Your Services',
                'menu-item-url' => home_url('/list-your-services/'),
                'menu-item-status' => 'publish',
                'menu-item-classes' => 'menu-cta' // Triggers CSS style
            ]);
            
            // Assign to locations
            $locations = get_theme_mod('nav_menu_locations');
            $locations['primary'] = $menu_id;
            $locations['primary_menu'] = $menu_id;
            set_theme_mod('nav_menu_locations', $locations);
        }
    }
    
    // 3. FLUSH PERMALINKS
    flush_rewrite_rules();
}
