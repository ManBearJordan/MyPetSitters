<?php
// Fix Page Content for 'list-your-services'
// The previous content '[mps_edit_profile]' is forcing a login.
// We need '[mps_sitter_landing]' which is the public sales page.

require_once('/var/www/html/wp-load.php');

$page_path = 'list-your-services';
$target_shortcode = '[mps_sitter_submit]';

$page = get_page_by_path($page_path);

if ($page) {
    if (trim($page->post_content) !== $target_shortcode) {
        $page->post_content = $target_shortcode;
        wp_update_post($page);
        echo "FIXED: Updated '$page_path' to use '$target_shortcode'.\n";
    } else {
        echo "SKIPPED: Page content is already correct.\n";
    }
} else {
    echo "ERROR: Page '$page_path' not found. Creating it...\n";
    wp_insert_post([
        'post_title' => 'List Your Services',
        'post_name' => $page_path,
        'post_content' => $target_shortcode,
        'post_status' => 'publish',
        'post_type' => 'page'
    ]);
    echo "CREATED: New page '$page_path' created.\n";
}
