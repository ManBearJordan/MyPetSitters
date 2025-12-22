<?php
/**
 * Update Account page to include smart listing button
 */
require '/var/www/html/wp-load.php';

// Find or create Account page
$account_page = get_page_by_path('account');

$content = '[user_registration_my_account]

[mps_listing_button]';

if ($account_page) {
    wp_update_post([
        'ID' => $account_page->ID,
        'post_content' => $content,
        'post_status' => 'publish'
    ]);
    echo "Updated Account page (ID: {$account_page->ID})\n";
} else {
    $id = wp_insert_post([
        'post_title' => 'Account',
        'post_name' => 'account',
        'post_content' => $content,
        'post_status' => 'publish',
        'post_type' => 'page'
    ]);
    echo "Created Account page (ID: $id)\n";
}

// Also update Login page to use this combined approach
$login_page = get_page_by_path('login');
if ($login_page) {
    wp_update_post([
        'ID' => $login_page->ID,
        'post_content' => '[user_registration_my_account]

[mps_listing_button]',
        'post_status' => 'publish'
    ]);
    echo "Updated Login page (ID: {$login_page->ID})\n";
}

echo "Done!\n";


