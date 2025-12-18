<?php
require '/var/www/html/wp-load.php';

$page_path = 'messages';
$page = get_page_by_path($page_path);

if ($page) {
    wp_update_post([
        'ID' => $page->ID,
        'post_content' => '[mps_inbox]',
        'post_title' => 'Messages',
        'post_status' => 'publish'
    ]);
    echo "Updated 'Messages' page.\n";
} else {
    $id = wp_insert_post([
        'post_title' => 'Messages',
        'post_name' => $page_path,
        'post_content' => '[mps_inbox]',
        'post_status' => 'publish',
        'post_type' => 'page'
    ]);
    echo "Created 'Messages' page (ID: $id).\n";
}

// Add to menu (optional, but good practice)
// This part is skipped for now to avoid messing with existing menu complex logic in a simple script, 
// user can add to menu manually or I can do it separately.
