<?php
require_once('/var/www/html/wp-load.php');

// Update List Your Services page to use [mps_edit_profile] shortcode
$page = get_page_by_path('list-your-services');
if ($page) {
    wp_update_post([
        'ID' => $page->ID,
        'post_content' => '[mps_edit_profile]',
    ]);
    echo "Updated List Your Services page (ID: {$page->ID}) with [mps_edit_profile] shortcode\n";
} else {
    echo "List Your Services page not found\n";
}
