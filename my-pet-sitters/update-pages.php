<?php
require_once('/var/www/html/wp-load.php');

// Update Cities page to use [mps_cities] shortcode
$cities_page = get_page_by_path('cities');
if ($cities_page) {
    wp_update_post([
        'ID' => $cities_page->ID,
        'post_content' => '[mps_cities]',
    ]);
    echo "Updated Cities page (ID: {$cities_page->ID}) with [mps_cities] shortcode\n";
} else {
    echo "Cities page not found\n";
}

// Update Services page to use [mps_services] shortcode
$services_page = get_page_by_path('services');
if ($services_page) {
    wp_update_post([
        'ID' => $services_page->ID,
        'post_content' => '[mps_services]',
    ]);
    echo "Updated Services page (ID: {$services_page->ID}) with [mps_services] shortcode\n";
} else {
    echo "Services page not found\n";
}

echo "\nDone!\n";
