<?php
require_once('/var/www/html/wp-load.php');

echo "Testing form processing logic...\n";

// Simulate logged in user
$user = get_user_by('login', 'testsitter');
if (!$user) {
    echo "User 'testsitter' not found!\n";
    exit;
}
wp_set_current_user($user->ID);
echo "Logged in as: " . wp_get_current_user()->user_login . "\n";

// Test if template_redirect hook is registered
global $wp_filter;
if (isset($wp_filter['template_redirect'])) {
    echo "template_redirect hooks registered: " . count($wp_filter['template_redirect']->callbacks) . "\n";
}

// Check if mps_get_user_sitter_posts function exists
if (function_exists('mps_get_user_sitter_posts')) {
    echo "mps_get_user_sitter_posts exists\n";
    $posts = mps_get_user_sitter_posts($user->ID, $user->user_email);
    echo "User sitter posts: " . count($posts) . "\n";
} else {
    echo "mps_get_user_sitter_posts does NOT exist!\n";
}

// Check if mps_services_map function exists
if (function_exists('mps_services_map')) {
    echo "mps_services_map exists\n";
} else {
    echo "mps_services_map does NOT exist!\n";
}

echo "\nDone.\n";
