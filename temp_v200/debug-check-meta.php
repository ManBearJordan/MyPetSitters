<?php
require_once('wp-load.php');

if (!is_user_logged_in()) {
    die('Please log in first.');
}

$current_user = wp_get_current_user();
echo "<h1>Debug Meta for User: {$current_user->display_name} (ID: {$current_user->ID})</h1>";

// Find Sitter Post
$posts = get_posts([
    'post_type' => 'sitter',
    'author'    => $current_user->ID,
    'post_status' => 'any',
    'numberposts' => 1
]);

if (!$posts) {
    die("No sitter profile found for this user.");
}

$sitter = $posts[0];
echo "<h2>Sitter Profile ID: {$sitter->ID}</h2>";
echo "Title: {$sitter->post_title}<br>";

// Dump all meta
$all_meta = get_post_meta($sitter->ID);
echo "<pre style='background:#f0f0f0;padding:15px;border:1px solid #ccc;'>";
print_r($all_meta);
echo "</pre>";

echo "<h3>Specific Keys:</h3>";
echo "mps_phone: " . get_post_meta($sitter->ID, 'mps_phone', true) . "<br>";
echo "mps_show_phone: " . get_post_meta($sitter->ID, 'mps_show_phone', true) . "<br>";
echo "mps_email: " . get_post_meta($sitter->ID, 'mps_email', true) . "<br>";
echo "mps_show_email: " . get_post_meta($sitter->ID, 'mps_show_email', true) . "<br>";


