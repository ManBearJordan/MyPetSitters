<?php
require '/var/www/html/wp-load.php';

$pages = [
    'lost-password' => [
        'title' => 'Lost Password',
        'content' => '[mps_lost_password]'
    ],
    'reset-password' => [
        'title' => 'Reset Password',
        'content' => '[mps_reset_password]'
    ]
];

foreach ($pages as $slug => $data) {
    $existing = get_page_by_path($slug);
    if ($existing) {
        wp_update_post([
            'ID' => $existing->ID,
            'post_content' => $data['content'],
            'post_title' => $data['title']
        ]);
        echo "Updated $slug\n";
    } else {
        $id = wp_insert_post([
            'post_title' => $data['title'],
            'post_name' => $slug,
            'post_content' => $data['content'],
            'post_status' => 'publish',
            'post_type' => 'page'
        ]);
        echo "Created $slug (ID: $id)\n";
    }
}
