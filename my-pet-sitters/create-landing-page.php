<?php
require '/var/www/html/wp-load.php';

$page_path = 'become-a-sitter';
$page = get_page_by_path($page_path);

if ($page) {
    wp_update_post([
        'ID' => $page->ID,
        'post_content' => '[mps_sitter_landing]',
        'post_title' => 'Become a Pet Sitter',
        'post_status' => 'publish'
    ]);
    echo "Updated 'Become a Sitter' page.\n";
} else {
    $id = wp_insert_post([
        'post_title' => 'Become a Pet Sitter',
        'post_name' => $page_path,
        'post_content' => '[mps_sitter_landing]',
        'post_status' => 'publish',
        'post_type' => 'page'
    ]);
    echo "Created 'Become a Sitter' page (ID: $id).\n";
}
