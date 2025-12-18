<?php
/**
 * Update Homepage to use new design
 */
require '/var/www/html/wp-load.php';

// Find the front page
$front_page_id = get_option('page_on_front');

if ($front_page_id) {
    // Update existing front page
    wp_update_post([
        'ID' => $front_page_id,
        'post_content' => '[mps_homepage]',
        'page_template' => 'default' // Ensure default template to let our styling take over
    ]);
    echo "Updated Homepage (ID: $front_page_id) with [mps_homepage]\n";
} else {
    // Basic fallback if no front page set (shouldn't happen on this setup)
    $home = get_page_by_path('home');
    if ($home) {
        wp_update_post([
            'ID' => $home->ID,
            'post_content' => '[mps_homepage]'
        ]);
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home->ID);
        echo "Updated Home page (ID: {$home->ID}) and set as front page\n";
    } else {
        echo "Could not find a designated front page to update.\n";
    }
}

echo "Done!\n";
