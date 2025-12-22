<?php
require_once('/var/www/html/wp-load.php');

$user = get_user_by('email', 'newsitter@test.com');
echo "User Exists: " . ($user ? "YES (ID: " . $user->ID . ")" : "NO") . "\n";

if (function_exists('antigravity_v200_services_map')) {
    echo "Services: " . print_r(antigravity_v200_services_map(), true) . "\n";
} else {
    echo "antigravity_v200_services_map function NOT found.\n";
}


