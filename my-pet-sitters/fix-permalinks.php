<?php
require_once('/var/www/html/wp-load.php');

// Set permalink structure to post name
update_option('permalink_structure', '/%postname%/');
echo "Set permalink structure to /%postname%/\n";

// Flush rewrite rules
flush_rewrite_rules(true);
echo "Flushed rewrite rules\n";

// Verify
$struct = get_option('permalink_structure');
echo "Current permalink structure: {$struct}\n";

// Test Brisbane page
$page = get_page_by_path('cities/brisbane');
if ($page) {
    echo "Brisbane page found: ID={$page->ID}\n";
    echo "Permalink: " . get_permalink($page->ID) . "\n";
} else {
    echo "Brisbane page NOT FOUND by path\n";
    
    // Try finding by slug
    $cities_parent = get_page_by_path('cities');
    if ($cities_parent) {
        echo "Cities parent: ID={$cities_parent->ID}\n";
        $children = get_pages(['parent' => $cities_parent->ID]);
        echo "Children of Cities:\n";
        foreach ($children as $child) {
            echo "  - {$child->post_title} ({$child->post_name})\n";
        }
    }
}
