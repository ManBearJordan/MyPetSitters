<?php
require '/var/www/html/wp-load.php';

$join_page = get_page_by_path('join');

if ($join_page) {
    wp_update_post([
        'ID' => $join_page->ID,
        'post_content' => '[mps_register]',
    ]);
    echo "Successfully updated 'Join' page to use [mps_register] shortcode.\n";
} else {
    echo "Error: 'Join' page not found.\n";
}


