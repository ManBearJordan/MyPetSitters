<?php
require_once('/var/www/html/wp-load.php');

// Check list-your-services page
$page = get_page_by_path('list-your-services');
if ($page) {
    echo "Page ID: {$page->ID}\n";
    echo "Status: {$page->post_status}\n";
    echo "Content: {$page->post_content}\n";
    
    // Make sure it's published
    if ($page->post_status !== 'publish') {
        wp_update_post(['ID' => $page->ID, 'post_status' => 'publish']);
        echo "Updated status to publish\n";
    }
} else {
    echo "Page not found - recreating\n";
    $page_id = wp_insert_post([
        'post_title' => 'List Your Services',
        'post_name' => 'list-your-services',
        'post_content' => '[mps_edit_profile]',
        'post_status' => 'publish',
        'post_type' => 'page',
    ]);
    echo "Created page ID: {$page_id}\n";
}

// Flush permalinks
flush_rewrite_rules(true);
echo "Permalinks flushed\n";

// Test the permalink
$page = get_page_by_path('list-your-services');
if ($page) {
    echo "Permalink: " . get_permalink($page->ID) . "\n";
}


