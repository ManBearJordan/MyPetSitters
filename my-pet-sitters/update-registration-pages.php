<?php
/**
 * Update Join page to use User Registration plugin
 */
require '/var/www/html/wp-load.php';

// Find the Join page
$join_page = get_page_by_path('join');

if ($join_page) {
    // Update with User Registration shortcode
    wp_update_post([
        'ID' => $join_page->ID,
        'post_content' => '[user_registration_form id="81"]',
        'post_status' => 'publish'
    ]);
    echo "Updated Join page (ID: {$join_page->ID}) with User Registration form\n";
} else {
    // Create the page if it doesn't exist
    $id = wp_insert_post([
        'post_title' => 'Join',
        'post_name' => 'join',
        'post_content' => '[user_registration_form id="81"]',
        'post_status' => 'publish',
        'post_type' => 'page'
    ]);
    echo "Created Join page (ID: $id) with User Registration form\n";
}

// Also update or create Login page for User Registration
$login_page = get_page_by_path('login');
if ($login_page) {
    wp_update_post([
        'ID' => $login_page->ID,
        'post_content' => '[user_registration_my_account]',
        'post_status' => 'publish'
    ]);
    echo "Updated Login page (ID: {$login_page->ID}) with User Registration login\n";
} else {
    $id = wp_insert_post([
        'post_title' => 'Login',
        'post_name' => 'login', 
        'post_content' => '[user_registration_my_account]',
        'post_status' => 'publish',
        'post_type' => 'page'
    ]);
    echo "Created Login page (ID: $id)\n";
}

echo "Done!\n";
