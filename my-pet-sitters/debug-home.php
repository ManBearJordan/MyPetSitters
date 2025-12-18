<?php
require '/var/www/html/wp-load.php';

echo "=== Homepage Debug ===\n";
echo "show_on_front: " . get_option('show_on_front') . "\n";
echo "page_on_front: " . get_option('page_on_front') . "\n";

$front_id = get_option('page_on_front');
if ($front_id) {
    $p = get_post($front_id);
    echo "Front Page Title: " . $p->post_title . "\n";
    echo "Front Page Content: " . $p->post_content . "\n";
    
    // Force update
    wp_update_post([
        'ID' => $front_id,
        'post_content' => '[mps_homepage]'
    ]);
    echo "Refreshed content just in case.\n";
} else {
    echo "No front page set via ID. Searching for 'Home'...\n";
    $home = get_page_by_title('Home');
    if (!$home) $home = get_page_by_path('home');
    
    if ($home) {
        echo "Found Home page ID: " . $home->ID . "\n";
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home->ID);
        wp_update_post([
            'ID' => $home->ID,
            'post_content' => '[mps_homepage]'
        ]);
        echo "Set Home as front page and updated content.\n";
    } else {
        echo "Critial: No 'Home' page found at all.\n";
    }
}

// Check if shortcode is registered
global $shortcode_tags;
if (isset($shortcode_tags['mps_homepage'])) {
    echo "Shortcode [mps_homepage] IS registered.\n";
} else {
    echo "Shortcode [mps_homepage] is NOT registered via global check (might be late init).\n";
}
