<?php
require_once('wp-load.php');

// Output as text
header('Content-Type: text/plain');

echo "Debug Rewrite Rules:\n";
echo "====================\n";

$rules = get_option('rewrite_rules');
$found_sitter = false;

if (!$rules) {
    echo "NO REWRITE RULES FOUND!\n";
} else {
    foreach ($rules as $pattern => $dest) {
        if (strpos($pattern, 'sitter') !== false || strpos($dest, 'sitter') !== false) {
            echo "MATCH: $pattern -> $dest\n";
            $found_sitter = true;
        }
    }
}

if (!$found_sitter) {
    echo "\nWARNING: No 'sitter' rules found.\n";
} else {
    echo "\nSUCCESS: Sitter rules exist.\n";
}

echo "\nChecking Post Type 'sitter':\n";
$pt = get_post_type_object('sitter');
if ($pt) {
    echo "Public: " . ($pt->public ? 'Yes' : 'No') . "\n";
    echo "Queryable: " . ($pt->publicly_queryable ? 'Yes' : 'No') . "\n";
    echo "Rewrite: " . print_r($pt->rewrite, true) . "\n";
} else {
    echo "ERROR: Post Type 'sitter' not registered.\n";
}
