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

// Check if antigravity_v200_get_sitter_post function exists
if (function_exists('antigravity_v200_get_sitter_post')) {
    echo "antigravity_v200_get_sitter_post exists\n";
    $post = antigravity_v200_get_sitter_post($user->ID);
    echo "User sitter post found: " . ($post ? 'Yes (ID: ' . $post->ID . ')' : 'No') . "\n";
} else {
    echo "antigravity_v200_get_sitter_post does NOT exist!\n";
}

// Check if antigravity_v200_services_map function exists
if (function_exists('antigravity_v200_services_map')) {
    echo "antigravity_v200_services_map exists\n";
} else {
    echo "antigravity_v200_services_map does NOT exist!\n";
}

echo "\nDone.\n";


