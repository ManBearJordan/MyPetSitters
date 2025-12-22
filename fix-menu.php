<?php
require_once('/var/www/html/wp-load.php');

echo "--- Checking Regions Page & Menu ---\n";

// 1. Ensure Page Exists
$page_path = 'regions';
$page_id = 0;
$page = get_page_by_path($page_path);

if ($page) {
    echo "Page 'regions' already exists (ID: {$page->ID}).\n";
    $page_id = $page->ID;
} else {
    echo "Page 'regions' NOT found. Creating it...\n";
    $page_id = wp_insert_post([
        'post_title'   => 'Regions',
        'post_name'    => 'regions',
        'post_content' => '[mps_regions]',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'comment_status' => 'closed'
    ]);
    if (is_wp_error($page_id)) {
        die("Error creating page: " . $page_id->get_error_message() . "\n");
    }
    echo "Page created successfully (ID: $page_id).\n";
}

// 2. Ensure Menu Exists
$menu_name = 'Primary'; 
$menu_exists = wp_get_nav_menu_object($menu_name);

if (!$menu_exists) {
    echo "Menu 'Primary' not found. Searching for any menu...\n";
    $menus = wp_get_nav_menus();
    if (!empty($menus)) {
        $menu_exists = $menus[0];
        echo "Using menu: " . $menu_exists->name . "\n";
    } else {
        echo "No menus found. Creating 'Primary'...\n";
        $menu_id = wp_create_nav_menu('Primary');
        $menu_exists = wp_get_nav_menu_object($menu_id);
    }
}

$menu_id = $menu_exists->term_id;
echo "Target Menu ID: $menu_id\n";

// 3. Check if Item is in Menu
$menu_items = wp_get_nav_menu_items($menu_id);
$found = false;

if ($menu_items) {
    foreach ($menu_items as $item) {
        if ($item->object_id == $page_id && $item->object == 'page') {
            echo "Regions is ALREADY in the menu (Item ID: {$item->ID}).\n";
            $found = true;
            break;
        }
    }
}

if (!$found) {
    echo "Adding 'Regions' to the menu...\n";
    $item_id = wp_update_nav_menu_item($menu_id, 0, [
        'menu-item-title'  => 'Regions',
        'menu-item-object' => 'page',
        'menu-item-object-id' => $page_id,
        'menu-item-type'   => 'post_type',
        'menu-item-status' => 'publish',
        'menu-item-position' => 3 // Try to put it after Home/About
    ]);
    
    if (is_wp_error($item_id)) {
        echo "Error adding to menu: " . $item_id->get_error_message() . "\n";
    } else {
        echo "SUCCESS: Added 'Regions' to menu (Item ID: $item_id).\n";
    }
} else {
    echo "No menu changes needed.\n";
}
