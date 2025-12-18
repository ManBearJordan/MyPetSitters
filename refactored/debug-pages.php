<?php
require_once('/var/www/html/wp-load.php');

echo "Checking all pages...\n\n";

$pages = get_pages(['post_status' => 'publish,draft,pending,trash']);
foreach ($pages as $p) {
    echo "ID: {$p->ID} | Status: {$p->post_status} | Slug: {$p->post_name} | Title: {$p->post_title}\n";
}

echo "\n---\n";

// Check specifically for list-your-services
$page = get_page_by_path('list-your-services');
if ($page) {
    echo "Found list-your-services: ID={$page->ID}, Status={$page->post_status}\n";
    echo "Permalink: " . get_permalink($page->ID) . "\n";
} else {
    echo "list-your-services NOT FOUND by path\n";
    
    // Try by title
    $pages = get_posts(['post_type' => 'page', 'title' => 'List Your Services', 'post_status' => 'any']);
    if ($pages) {
        echo "Found by title: ID={$pages[0]->ID}, Status={$pages[0]->post_status}, Slug={$pages[0]->post_name}\n";
    }
}

echo "\nPermalink structure: " . get_option('permalink_structure') . "\n";


