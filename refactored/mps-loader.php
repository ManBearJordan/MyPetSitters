<?php
/**
 * Plugin Name: MPS Snippet Loader
 * Description: Automatically loads all PHP files from wp-content/mps-snippets/
 */

$snippets_dir = WP_CONTENT_DIR . '/mps-snippets/';

if (is_dir($snippets_dir)) {
    foreach (glob($snippets_dir . '*.php') as $file) {
        require_once $file;
    }
}


