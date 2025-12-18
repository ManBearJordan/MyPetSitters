<?php
/**
 * Plugin Name: MPS Snippet Loader (v200 Safe Mode)
 * Description: Safely loads v200 snippets only.
 */

// 1. CHANGE THE FOLDER NAME so it doesn't look at the old v110 files
$snippets_dir = WP_CONTENT_DIR . '/mps-snippets-v200/'; 

if (is_dir($snippets_dir)) {
    foreach (glob($snippets_dir . '*.php') as $file) {
        // 2. ADD SAFETY CHECK to prevent self-loading loops
        if (basename($file) === basename(__FILE__)) continue;
        require_once $file;
    }
}
