<?php
require_once('/var/www/html/wp-load.php');

$user = get_user_by('email', 'newsitter@test.com');
echo "User Exists: " . ($user ? "YES (ID: " . $user->ID . ")" : "NO") . "\n";

if (function_exists('mps_services_map')) {
    echo "Services: " . print_r(mps_services_map(), true) . "\n";
} else {
    echo "mps_services_map function NOT found.\n";
}
