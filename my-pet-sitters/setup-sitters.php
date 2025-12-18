<?php
/**
 * Setup script to create sample sitters and configure theme
 * Run via: docker exec mps-wordpress php /var/www/html/wp-content/setup-sitters.php
 */

require_once '/var/www/html/wp-load.php';

// Create sample sitters
$sitters = [
    [
        'name' => 'Sarah Johnson',
        'city' => 'Brisbane',
        'suburb' => 'South Brisbane',
        'services' => 'Dog Walking, Home Visits',
        'price' => '$25/walk',
        'bio' => 'Hi! I\'m Sarah, a passionate animal lover with 5+ years of experience caring for pets. I offer reliable dog walking and home visit services in South Brisbane and surrounding suburbs. I treat every pet like my own family member!',
        'email' => 'sarah@example.com',
        'phone' => '0412 345 678',
    ],
    [
        'name' => 'Mike Chen',
        'city' => 'Brisbane',
        'suburb' => 'Fortitude Valley',
        'services' => 'Overnight Stays, Daycare',
        'price' => '$45/night',
        'bio' => 'Professional pet sitter offering overnight stays and daycare in the Valley. My home is fully fenced and pet-safe. I have experience with dogs of all sizes and can accommodate special dietary needs.',
        'email' => 'mike@example.com',
        'phone' => '0423 456 789',
    ],
    [
        'name' => 'Emma Wilson',
        'city' => 'Sydney',
        'suburb' => 'Bondi',
        'services' => 'Dog Walking, Daycare',
        'price' => '$30/walk',
        'bio' => 'Active pet sitter in beautiful Bondi! I love taking dogs for beach walks and providing daytime care while you\'re at work. Certified in pet first aid.',
        'email' => 'emma@example.com',
        'phone' => '0434 567 890',
    ],
];

echo "Creating sample sitters...\n";

foreach ($sitters as $sitter) {
    // Check if already exists
    $existing = get_page_by_title($sitter['name'], OBJECT, 'sitter');
    if ($existing) {
        echo "Sitter '{$sitter['name']}' already exists\n";
        continue;
    }
    
    $post_id = wp_insert_post([
        'post_title' => $sitter['name'],
        'post_content' => $sitter['bio'],
        'post_status' => 'publish',
        'post_type' => 'sitter',
    ]);
    
    if (is_wp_error($post_id)) {
        echo "Error creating sitter: " . $post_id->get_error_message() . "\n";
        continue;
    }
    
    // Add meta
    update_post_meta($post_id, 'mps_city', $sitter['city']);
    update_post_meta($post_id, 'mps_suburb', $sitter['suburb']);
    update_post_meta($post_id, 'mps_services', $sitter['services']);
    update_post_meta($post_id, 'mps_price', $sitter['price']);
    update_post_meta($post_id, 'mps_email', $sitter['email']);
    update_post_meta($post_id, 'mps_phone', $sitter['phone']);
    
    // Sync to taxonomies
    $city_term = term_exists($sitter['city'], 'mps_city');
    if (!$city_term) {
        $city_term = wp_insert_term($sitter['city'], 'mps_city');
    }
    if (!is_wp_error($city_term)) {
        wp_set_object_terms($post_id, (int)($city_term['term_id'] ?? $city_term), 'mps_city', false);
    }
    
    // Service terms
    $service_labels = array_map('trim', explode(',', $sitter['services']));
    $service_ids = [];
    foreach ($service_labels as $label) {
        $term = term_exists($label, 'mps_service');
        if (!$term) {
            $term = wp_insert_term($label, 'mps_service');
        }
        if (!is_wp_error($term)) {
            $service_ids[] = (int)($term['term_id'] ?? $term);
        }
    }
    wp_set_object_terms($post_id, $service_ids, 'mps_service', false);
    
    echo "Created sitter '{$sitter['name']}' (ID: {$post_id})\n";
}

// Create navigation menu
echo "\nSetting up navigation menu...\n";

$menu_name = 'Primary Menu';
$menu_exists = wp_get_nav_menu_object($menu_name);

if (!$menu_exists) {
    $menu_id = wp_create_nav_menu($menu_name);
    echo "Created menu '{$menu_name}' (ID: {$menu_id})\n";
} else {
    $menu_id = $menu_exists->term_id;
    echo "Menu '{$menu_name}' already exists\n";
}

// Menu items
$menu_items = [
    'Home' => home_url('/'),
    'About' => home_url('/about/'),
    'Cities' => home_url('/cities/'),
    'Services' => home_url('/services/'),
    'Join' => home_url('/join/'),
    'Login' => home_url('/login/'),
];

foreach ($menu_items as $title => $url) {
    // Check if menu item exists
    $items = wp_get_nav_menu_items($menu_id);
    $exists = false;
    if ($items) {
        foreach ($items as $item) {
            if ($item->title === $title) {
                $exists = true;
                break;
            }
        }
    }
    
    if (!$exists) {
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => $title,
            'menu-item-url' => $url,
            'menu-item-status' => 'publish',
            'menu-item-type' => 'custom',
        ]);
        echo "Added menu item: {$title}\n";
    }
}

// Set menu location
$locations = get_theme_mod('nav_menu_locations');
$locations['primary'] = $menu_id;
$locations['primary-menu'] = $menu_id;
set_theme_mod('nav_menu_locations', $locations);
echo "Set menu location\n";

// Update site title
update_option('blogname', 'My Pet Sitters');
update_option('blogdescription', 'Find trusted pet sitters across Australia');
echo "Updated site title\n";

// Set permalink structure
update_option('permalink_structure', '/%postname%/');
flush_rewrite_rules();
echo "Set permalink structure\n";

echo "\nDone! Sample sitters and menu created.\n";
