<?php
/**
 * Plugin Name: MPS Snippet Loader
 * Description: Automatically loads all PHP files from wp-content/mps-snippets/
 */

$snippets_dir = plugin_dir_path(__FILE__); // Changed to INTERNAL plugin path (Rule 1)

if (is_dir($snippets_dir)) {
    foreach (glob($snippets_dir . '*.php') as $file) {
        $basename = basename($file);
        // Prevent loading loader files or index.php to avoid loops
        if ($basename === basename(__FILE__) || $basename === 'index.php' || $basename === 'mps-core-loader.php') continue; 
        require_once $file;
    }
}


