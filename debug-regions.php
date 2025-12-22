<?php
require_once('/var/www/html/wp-load.php');

echo "Checking Regions Page...\n";

// Check Shortcode
if (shortcode_exists('mps_regions')) {
    echo "Shortcode [mps_regions] is REGISTERED.\n";
} else {
    echo "Shortcode [mps_regions] is MISSING.\n";
}

// Check Page
$page = get_page_by_path('regions');
if ($page) {
    echo "Page 'regions' FOUND. ID: " . $page->ID . "\n";
    echo "Status: " . $page->post_status . "\n";
    echo "Content: " . $page->post_content . "\n";
} else {
    echo "Page 'regions' NOT FOUND.\n";
    // Try 'locations' ?
    $page2 = get_page_by_path('locations');
    if ($page) {
        echo "Found 'locations' page instead.\n";
    }
}

// Test Shortcode Output
echo "\n--- Shortcode Render Test ---\n";
$output = do_shortcode('[mps_regions]');
if (strpos($output, 'Hunter Region') !== false) {
    echo "SUCCESS: Found 'Hunter Region' in output.\n";
} else {
    echo "FAILURE: 'Hunter Region' NOT found in output.\n";
}
if (strpos($output, 'Gold Coast') !== false) {
    echo "SUCCESS: Found 'Gold Coast' in output.\n";
} else {
    echo "FAILURE: 'Gold Coast' NOT found in output.\n";
}
echo "Output length: " . strlen($output) . "\n";
