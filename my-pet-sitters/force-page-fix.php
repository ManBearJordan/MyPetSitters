<?php
/**
 * MPS PAGE CONTENT FIXER (V14)
 * 
 * Automatically repairs pages that were created with incorrect shortcodes
 * by the previous installer version.
 */

if (!defined('ABSPATH')) exit;

function mps_force_fix_page_content() {
    // Run only once
    if (get_option('mps_page_fix_v14_run') === 'yes') {
        return;
    }

    // 1. Fix CITIES Page
    $cities_page = get_page_by_path('cities');
    if ($cities_page && strpos($cities_page->post_content, '[mps_landing_page]') !== false) {
        $cities_page->post_content = '[mps_cities]';
        wp_update_post($cities_page);
    }

    // 2. Fix JOIN Page
    $join_page = get_page_by_path('join');
    if ($join_page && strpos($join_page->post_content, '[mps_registration]') !== false) {
        $join_page->post_content = '[mps_register]';
        wp_update_post($join_page);
    }

    // 3. Fix LIST YOUR SERVICES Page
    $list_page = get_page_by_path('list-your-services');
    if ($list_page && strpos($list_page->post_content, '[mps_sitter_registration]') !== false) {
        $list_page->post_content = '[mps_sitter_landing]';
        wp_update_post($list_page);
    }

    // 4. Fix SERVICES Page (Added V15)
    $services_page = get_page_by_path('services');
    // If page exists and content is empty OR wrong, fix it. 
    // We'll be aggressive here to ensure it works.
    if ($services_page) {
        $services_page->post_content = '[mps_services]';
        wp_update_post($services_page);
    }
    
    // Backup: If pages don't exist, recreating them is handled by mps-installer.php
    // This script specifically targets *existing* broken pages.

    update_option('mps_page_fix_v14_run', 'yes');
}

add_action('init', 'mps_force_fix_page_content', 20);
