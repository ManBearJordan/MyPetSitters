<?php
/**
 * Force Menu Setup logic
 * Included by mps-core-loader.php
 */

function antigravity_v200_force_create_menu() {
    // Check if we've already run this version of the fix
    // UPDATED to v11
    if (get_option('mps_menu_fix_v11_run') === 'yes') {
        return;
    }

    $menu_name = 'MPS Primary Menu';
    
    // 1. Check if menu exists, if so, delete it to ensure fresh start
    $existing = wp_get_nav_menu_object($menu_name);
    if ($existing) {
        wp_delete_nav_menu($existing->term_id);
    }
    
    // 2. Create Menu
    $menu_id = wp_create_nav_menu($menu_name);
    if (is_wp_error($menu_id)) {
        return; // Fail silently or log
    }

    $position = 1;

    // 3. Add Items matching "Ideal" Screenshot
    
    antigravity_v200_add_menu_item_v11($menu_id, 'Home', '/', $position);
    antigravity_v200_add_menu_item_v11($menu_id, 'About', '/about/', $position);
    
    // Cities Dropdown (Parent is clickable)
    $cities_id = antigravity_v200_add_menu_item_v11($menu_id, 'Cities', '/cities/', $position);
    $cities = ['Brisbane', 'Sydney', 'Melbourne', 'Perth', 'Adelaide'];
    foreach ($cities as $city) {
        antigravity_v200_add_menu_item_v11($menu_id, $city, '/cities/' . sanitize_title($city) . '/', $position, $cities_id);
    }

    // Services Dropdown (Parent is clickable)
    $services_id = antigravity_v200_add_menu_item_v11($menu_id, 'Services', '/services/', $position);
    $services = [
        'Dog Walking' => 'dog-walking',
        'Overnight Stays' => 'overnight-stays',
        'Daycare' => 'daycare',
        'Home Visits' => 'home-visits',
    ];
    foreach ($services as $name => $slug) {
        antigravity_v200_add_menu_item_v11($menu_id, $name, '/services/' . $slug . '/', $position, $services_id);
    }

    antigravity_v200_add_menu_item_v11($menu_id, 'Join', '/join/', $position);
    antigravity_v200_add_menu_item_v11($menu_id, 'Become a Sitter', '/become-a-sitter/', $position);
    antigravity_v200_add_menu_item_v11($menu_id, 'Login', '/login/', $position);
    
    // The "List Your Services" Button
    antigravity_v200_add_menu_item_v11($menu_id, 'List Your Services', '/list-your-services/', $position, 0, ['menu-cta']);

    // 4. Assign to Primary Location
    $locations = get_theme_mod('nav_menu_locations', []);
    $locations['primary'] = $menu_id;
    $locations['primary_menu'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);

    // Mark as run
    update_option('mps_menu_fix_v11_run', 'yes');
}

/**
 * Helper to add item (Moved outside to prevent redeclaration fatal error)
 */
if (!function_exists('antigravity_v200_add_menu_item_v11')) {
    function antigravity_v200_add_menu_item_v11($menu_id, $title, $url, &$position, $parent_id = 0, $classes = []) {
        return wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title'   => $title,
            'menu-item-url'     => home_url($url),
            'menu-item-status'  => 'publish',
            'menu-item-position'=> $position++,
            'menu-item-parent-id' => $parent_id,
            'menu-item-classes' => implode(' ', $classes),
            'menu-item-type'    => 'custom',
        ]);
    }
}

// Hook to init so it runs on page load (temporary, but effective for immediate fix)
add_action('init', 'antigravity_v200_force_create_menu');


