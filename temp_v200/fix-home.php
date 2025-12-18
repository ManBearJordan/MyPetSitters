<?php
require '/var/www/html/wp-load.php';

$page_title = 'Home';
$page_content = '[mps_homepage]';

// Check if page exists
$page = get_page_by_title($page_title);

if (!$page) {
    // Create page
    $page_id = wp_insert_post([
        'post_title' => $page_title,
        'post_content' => $page_content,
        'post_status' => 'publish',
        'post_type' => 'page'
    ]);
    echo "Created Home page with ID: $page_id\n";
} else {
    $page_id = $page->ID;
    // Update content just in case
    wp_update_post([
        'ID' => $page_id,
        'post_content' => $page_content
    ]);
    echo "Found existing Home page ID: $page_id (updated content)\n";
}

// Set as front page
update_option('show_on_front', 'page');
update_option('page_on_front', $page_id);

echo "Success! Set ID $page_id as static front page.\n";


