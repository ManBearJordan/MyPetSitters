<?php
require_once('/var/www/html/wp-load.php');

echo "=== Form Handler Debug ===\n";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "nonce set: " . (isset($_POST['mps_edit_nonce']) ? 'YES' : 'NO') . "\n";
echo "logged in: " . (is_user_logged_in() ? 'YES' : 'NO') . "\n";

// Simulate a POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['mps_edit_nonce'] = wp_create_nonce('mps_edit_profile');
$_POST['name'] = 'Debug Test';
$_POST['email'] = 'debug@test.com';
$_POST['city'] = 'Brisbane';
$_POST['suburb'] = 'Test';
$_POST['bio'] = 'Debug bio test';
$_POST['services'] = ['Dog Walking'];
$_POST['price_dog-walking'] = '30';
$_POST['phone'] = '0400000000';

// Log in as testsitter
$user = get_user_by('login', 'testsitter');
if ($user) {
    wp_set_current_user($user->ID);
    echo "Set user to: " . wp_get_current_user()->user_login . "\n";
}

echo "nonce verify: " . (wp_verify_nonce($_POST['mps_edit_nonce'], 'mps_edit_profile') ? 'VALID' : 'INVALID') . "\n";

// Check if antigravity_v200_services_map exists
if (function_exists('antigravity_v200_services_map')) {
    echo "Services map: " . print_r(array_keys(antigravity_v200_services_map()), true);
}

// Check cities
if (function_exists('antigravity_v200_cities_list')) {
    echo "Cities: " . print_r(antigravity_v200_cities_list(), true);
}

// Check if Brisbane is in cities list
if (function_exists('antigravity_v200_cities_list')) {
    $cities = antigravity_v200_cities_list();
    echo "Brisbane in cities: " . (in_array('Brisbane', $cities) ? 'YES' : 'NO') . "\n";
}

// Manually run the form processing logic
echo "\n=== Testing form processing ===\n";
$name = sanitize_text_field($_POST['name']);
$email = sanitize_email($_POST['email']);
$city = sanitize_text_field($_POST['city']);
$bio = wp_kses_post($_POST['bio']);
$services = isset($_POST['services']) ? antigravity_v200_normalise_services_labels($_POST['services']) : [];

echo "Name: $name\n";
echo "Email: $email\n";
echo "City: $city\n";
echo "Bio: $bio\n";
echo "Services: " . print_r($services, true);

// Validation
$errors = [];
if (!$name) $errors[] = 'Name required.';
if (!$email || !is_email($email)) $errors[] = 'Valid email required.';
if (!in_array($city, antigravity_v200_cities_list())) $errors[] = 'Select a city.';
if (!$services) $errors[] = 'Select at least one service.';
if (!$bio) $errors[] = 'Bio is required.';

echo "Errors: " . (empty($errors) ? 'NONE' : print_r($errors, true));

if (empty($errors)) {
    echo "\n=== Validation passed, would create post ===\n";
    $title = $name . ' - ' . $city;
    echo "Title would be: $title\n";
    
    // Actually create the post
    $postarr = [
        'post_type' => 'sitter',
        'post_status' => 'pending',
        'post_title' => $title,
        'post_content' => $bio,
        'post_author' => $user->ID
    ];
    $post_id = wp_insert_post($postarr, true);
    
    if (is_wp_error($post_id)) {
        echo "ERROR creating post: " . $post_id->get_error_message() . "\n";
    } else {
        echo "Created post ID: $post_id\n";
        
        // Save meta
        update_post_meta($post_id, 'mps_city', $city);
        update_post_meta($post_id, 'mps_email', $email);
        update_post_meta($post_id, 'mps_services', implode(', ', $services));
        
        echo "Meta saved\n";
        echo "SUCCESS - would redirect to /account/?profile_saved=1\n";
    }
}


