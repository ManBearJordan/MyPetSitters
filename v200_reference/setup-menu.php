<?php
/**
 * Setup Menu Script with Dropdowns - Run once via CLI
 */
require_once('/var/www/html/wp-load.php');

// Menu configuration
$menu_name = 'Primary Menu';

// Delete existing menu if exists
$existing_menu = wp_get_nav_menu_object($menu_name);
if ($existing_menu) {
    wp_delete_nav_menu($existing_menu->term_id);
    echo "Deleted existing menu\n";
}

// Create menu
$menu_id = wp_create_nav_menu($menu_name);
if (is_wp_error($menu_id)) {
    die("Error creating menu: " . $menu_id->get_error_message() . "\n");
}
echo "Created menu: {$menu_name} (ID: {$menu_id})\n";

// Cities and Services for dropdowns
$cities = ['Brisbane', 'Sydney', 'Melbourne', 'Perth', 'Adelaide'];
$services = [
    'Dog Walking' => 'dog-walking',
    'Overnight Stays' => 'overnight-stays',
    'Daycare' => 'daycare',
    'Home Visits' => 'home-visits',
];

$position = 1;

// 1. Home
$home_id = wp_update_nav_menu_item($menu_id, 0, [
    'menu-item-title' => 'Home',
    'menu-item-url' => home_url('/'),
    'menu-item-status' => 'publish',
    'menu-item-position' => $position++,
    'menu-item-type' => 'custom',
]);
echo "Added: Home\n";

// 2. About
$about_id = wp_update_nav_menu_item($menu_id, 0, [
    'menu-item-title' => 'About',
    'menu-item-url' => home_url('/about/'),
    'menu-item-status' => 'publish',
    'menu-item-position' => $position++,
    'menu-item-type' => 'custom',
]);
echo "Added: About\n";

// 3. Cities (parent)
$cities_id = wp_update_nav_menu_item($menu_id, 0, [
    'menu-item-title' => 'Cities',
    'menu-item-url' => home_url('/cities/'),
    'menu-item-status' => 'publish',
    'menu-item-position' => $position++,
    'menu-item-type' => 'custom',
]);
echo "Added: Cities\n";

// Cities dropdown items
foreach ($cities as $city) {
    $slug = sanitize_title($city);
    wp_update_nav_menu_item($menu_id, 0, [
        'menu-item-title' => $city,
        'menu-item-url' => home_url("/cities/{$slug}/"),
        'menu-item-status' => 'publish',
        'menu-item-position' => $position++,
        'menu-item-parent-id' => $cities_id,
        'menu-item-type' => 'custom',
    ]);
    echo "  Added: {$city} (child of Cities)\n";
}

// 4. Services (parent)
$services_id = wp_update_nav_menu_item($menu_id, 0, [
    'menu-item-title' => 'Services',
    'menu-item-url' => home_url('/services/'),
    'menu-item-status' => 'publish',
    'menu-item-position' => $position++,
    'menu-item-type' => 'custom',
]);
echo "Added: Services\n";

// Services dropdown items
foreach ($services as $name => $slug) {
    wp_update_nav_menu_item($menu_id, 0, [
        'menu-item-title' => $name,
        'menu-item-url' => home_url("/services/{$slug}/"),
        'menu-item-status' => 'publish',
        'menu-item-position' => $position++,
        'menu-item-parent-id' => $services_id,
        'menu-item-type' => 'custom',
    ]);
    echo "  Added: {$name} (child of Services)\n";
}

// 5. Join
$join_id = wp_update_nav_menu_item($menu_id, 0, [
    'menu-item-title' => 'Join',
    'menu-item-url' => home_url('/join/'),
    'menu-item-status' => 'publish',
    'menu-item-position' => $position++,
    'menu-item-type' => 'custom',
]);
echo "Added: Join\n";

// 5a. Become a Sitter
$landing_id = wp_update_nav_menu_item($menu_id, 0, [
    'menu-item-title' => 'Become a Sitter',
    'menu-item-url' => home_url('/become-a-sitter/'),
    'menu-item-status' => 'publish',
    'menu-item-position' => $position++,
    'menu-item-type' => 'custom',
]);
echo "Added: Become a Sitter\n";

// 6. Login
$login_id = wp_update_nav_menu_item($menu_id, 0, [
    'menu-item-title' => 'Login',
    'menu-item-url' => home_url('/login/'),
    'menu-item-status' => 'publish',
    'menu-item-position' => $position++,
    'menu-item-type' => 'custom',
]);
echo "Added: Login\n";

// 7. List Your Services (button)
$cta_id = wp_update_nav_menu_item($menu_id, 0, [
    'menu-item-title' => 'List Your Services',
    'menu-item-url' => home_url('/list-your-services/'),
    'menu-item-status' => 'publish',
    'menu-item-position' => $position++,
    'menu-item-type' => 'custom',
    'menu-item-classes' => 'menu-cta',
]);
echo "Added: List Your Services (button)\n";

// Assign menu to all possible primary locations
$locations = get_theme_mod('nav_menu_locations', []);
$locations['primary_menu'] = $menu_id;
$locations['primary'] = $menu_id;
$locations['main-menu'] = $menu_id;
$locations['menu-1'] = $menu_id;
set_theme_mod('nav_menu_locations', $locations);
echo "Assigned menu to primary locations\n";

echo "\nMenu setup complete with dropdowns!\n";


