<?php
require '/var/www/html/wp-load.php';

echo "=== Sitter Listings ===\n\n";

$posts = get_posts([
    'post_type' => 'sitter',
    'post_status' => ['publish','pending','draft'],
    'numberposts' => 10
]);

foreach ($posts as $p) {
    $city = get_post_meta($p->ID, 'mps_city', true);
    $services = get_post_meta($p->ID, 'mps_services', true);
    echo "ID: {$p->ID}\n";
    echo "Title: {$p->post_title}\n";
    echo "Status: {$p->post_status}\n";
    echo "City: $city\n";
    echo "Services: $services\n";
    echo "---\n";
}

if (empty($posts)) {
    echo "No sitter listings found.\n";
}
