<?php
require '/var/www/html/wp-load.php';

// Fix testsitter role - should be 'pro' only, not admin
$user = get_user_by('login', 'testsitter');
if ($user) {
    $user->set_role('pro');
    echo "Fixed: testsitter now has 'pro' role only (no admin access)\n";
} else {
    echo "User 'testsitter' not found\n";
}

// Also check for Test Sitter by email
$user2 = get_user_by('email', 'testsitter@example.com');
if ($user2 && $user2->ID !== ($user ? $user->ID : 0)) {
    $user2->set_role('pro');
    echo "Fixed: secondary user also set to 'pro' role\n";
}

echo "Done!\n";
