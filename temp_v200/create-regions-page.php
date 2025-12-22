<?php
/**
 * Create Regions Page (Helper)
 * Run this once to create the /regions/ page with [mps_regions]
 */

// Try to load WordPress
$path = dirname(__FILE__);
// Loop up to 4 levels to find wp-load.php
for ($i=0; $i<4; $i++) {
    if (file_exists($path . '/wp-load.php')) {
        require_once($path . '/wp-load.php');
        break;
    }
    $path = dirname($path);
}

if (!defined('ABSPATH')) {
    die("Error: Could not find wp-load.php. Please create the page manually: Title='Regions', Content='[mps_regions]'");
}

if (!function_exists('post_exists')) {
    require_once(ABSPATH . 'wp-admin/includes/post.php');
}

$title = 'Regions';
$slug  = 'regions';
$content = '[mps_regions]';

// Check if exists
$page = get_page_by_path($slug);

if ($page) {
    echo "Page 'Regions' already exists (ID: {$page->ID}).<br>";
    echo "View it at: " . get_permalink($page->ID) . "<br>";
    
    // Optional: Update content if empty or wrong?
    if (!has_shortcode($page->post_content, 'mps_regions')) {
        echo "Updating content to include [mps_regions]...<br>";
        wp_update_post([
            'ID' => $page->ID,
            'post_content' => $content . "\n\n" . $page->post_content
        ]);
        echo "Updated.<br>";
    }
} else {
    // Create
    $id = wp_insert_post([
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'comment_status' => 'closed'
    ]);
    
    if ($id && !is_wp_error($id)) {
        echo "SUCCESS: Created 'Regions' page (ID: $id).<br>";
        echo "Link: " . get_permalink($id);
    } else {
        echo "ERROR: Could not create page.";
    }
}
?>


