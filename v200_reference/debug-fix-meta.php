<?php
require_once('wp-load.php');

if (!is_user_logged_in()) {
    die('Please log in first.');
}

$current_user = wp_get_current_user();
echo "<style>body{font-family:sans-serif;padding:20px;max-width:800px;margin:0 auto;}</style>";
echo "<h1>Debug & Fix: {$current_user->display_name}</h1>";

// Find Sitter Post
$posts = get_posts([
    'post_type' => 'sitter',
    'author'    => $current_user->ID,
    'post_status' => 'any',
    'numberposts' => 1
]);

if (!$posts) {
    die("<p style='color:red'>No sitter profile found for this user.</p>");
}

$sitter = $posts[0];
$sitter_id = $sitter->ID;

// HANDLE FORCE UPDATE
if (isset($_POST['force_fix'])) {
    if (isset($_POST['set_phone'])) {
        update_post_meta($sitter_id, 'mps_phone', sanitize_text_field($_POST['set_phone']));
        echo "<div style='background:lightgreen;padding:10px;'>Updated Phone Number.</div>";
    }
    
    $show_p = isset($_POST['set_show_phone']) ? 1 : 0;
    update_post_meta($sitter_id, 'mps_show_phone', $show_p);
    echo "<div style='background:lightgreen;padding:10px;'>Updated Show Phone to: " . ($show_p ? 'YES' : 'NO') . "</div>";
}

echo "<h2>Sitter Profile ID: $sitter_id</h2>";

// Dump current values
$phone = get_post_meta($sitter_id, 'mps_phone', true);
$show_phone = get_post_meta($sitter_id, 'mps_show_phone', true);
$email = get_post_meta($sitter_id, 'mps_email', true);
$show_email = get_post_meta($sitter_id, 'mps_show_email', true);

echo "<table border='1' cellpadding='10' style='border-collapse:collapse;width:100%;'>";
echo "<tr><th>Key</th><th>Current Value (DB)</th><th>Status</th></tr>";
echo "<tr><td>mps_phone</td><td>" . esc_html($phone) . "</td><td>" . ($phone ? 'OK' : 'EMPTY') . "</td></tr>";
echo "<tr><td>mps_show_phone</td><td>" . esc_html($show_phone) . "</td><td>" . ($show_phone ? 'CHECKED' : 'UNCHECKED') . "</td></tr>";
echo "<tr><td>mps_email</td><td>" . esc_html($email) . "</td><td>" . ($email ? 'OK' : 'EMPTY') . "</td></tr>";
echo "<tr><td>mps_show_email</td><td>" . esc_html($show_email) . "</td><td>" . ($show_email ? 'CHECKED' : 'UNCHECKED') . "</td></tr>";
echo "</table>";

echo "<h3>Force Update</h3>";
echo "<form method='post'>";
echo "<p><strong>Phone Number:</strong> <input type='text' name='set_phone' value='" . esc_attr($phone) . "'></p>";
echo "<p><label><input type='checkbox' name='set_show_phone' value='1' " . checked($show_phone, 1, false) . "> Show Phone?</label></p>";
echo "<button type='submit' name='force_fix' style='padding:10px 20px;background:blue;color:white;border:none;cursor:pointer;'>Force Save These Values</button>";
echo "</form>";

if ($show_phone && $phone) {
    echo "<h3 style='color:green'>Diagnosis: It SHOULD be appearing.</h3>";
} else {
    echo "<h3 style='color:red'>Diagnosis: It will NOT appear because one of the above is missing/unchecked.</h3>";
}


