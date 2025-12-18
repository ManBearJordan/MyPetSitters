<?php
/**
 * Flush Rewrite Rules Script
 * Run this once to fix 404 errors after adding new rewrite rules (like Dog Boarding/Service pages).
 */
define('WP_USE_THEMES', false);
require_once('/var/www/html/wp-load.php');

// Flush rules hard
flush_rewrite_rules(true);

echo "Rewrite rules flushed successfully!\n";
echo "Try accessing /services/dog-boarding/ now.\n";


