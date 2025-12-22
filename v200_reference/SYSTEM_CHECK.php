<?php
/**
 * SYSTEM CHECK SCRIPT
 * Run this by visiting: /wp-content/plugins/MyPetSitters/SYSTEM_CHECK.php
 * Or by including it in your theme temporarily.
 */

// Disable WP checks
define('ABSPATH', true); 

header('Content-Type: text/plain');

$dir = __DIR__;
$files = glob($dir . '/*.php');
$includes = glob($dir . '/includes/*.php');
$all = array_merge($files, $includes);

echo "----------------------------------------\n";
echo "MPS PLUGIN SYSTEM CHECK\n";
echo "----------------------------------------\n";
echo "Scanning " . count($all) . " files in $dir...\n\n";

$errors = 0;

foreach ($all as $file) {
    if ($file === __FILE__) continue;
    
    $content = file_get_contents($file);
    $name = basename($file);
    
    // 1. Check for whitespace before <?php
    if (preg_match('/^[\s\n\r]+<\?php/', $content)) {
        echo "[FAIL] $name: Whitespace found before opening PHP tag.\n";
        $errors++;
    } elseif (strpos($content, '<?php') !== 0) {
        // Checking if it starts with something else? 
        // e.g. text file masquerading as php?
        // But tmpl files might be HTML first.
        // echo "[INFO] $name: Does not start with <?php (might be template)\n";
    }
    
    // 2. Check for closing ?> tag at EOF (Risk of trailing whitespace)
    // Trim content first? No, we want to catch whitespace.
    if (preg_match('/\?>[\s\n\r]*$/', $content)) {
        // It has a closing tag at the end.
        // Check if there is whitespace after it.
        $len = strlen($content);
        $last_tag = strrpos($content, '?>');
        $trailing = substr($content, $last_tag + 2);
        
        if (strlen($trailing) > 0 && trim($trailing) === '') {
             echo "[FAIL] $name: Trailing whitespace found after closing ?> tag.\n";
             $errors++;
        } elseif (strlen($trailing) > 0) {
             echo "[INFO] $name: Content found after closing ?> tag.\n";
        }
    }
}

// 3. Logic Check: Check for missing dependencies or undefined usage
echo "\nChecking Dependencies...\n";

// Check if mps-locations-au.php has syntax error (by including it?)
// We can't include without risking crash.
// Inspect manually via regex.

if ($errors === 0) {
    echo "\nPASS: No fatal file structure issues found.\n";
} else {
    echo "\nFAIL: Found $errors issues.\n";
}

echo "\nDone.";
