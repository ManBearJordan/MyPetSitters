<?php
// Turn on all error reporting for this test
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>server File Diagnostic</h2>";
echo "<p><strong>Script Location:</strong> " . __DIR__ . "</p>";

// Check if 'includes' folder exists
$includes_path = __DIR__ . '/includes';
$includes_path_cap = __DIR__ . '/Includes'; // Check for capital I

echo "<hr>";

// 1. Check for lowercase 'includes'
if (is_dir($includes_path)) {
    echo "<p style='color:green; font-weight:bold;'>✅ Found folder: 'includes'</p>";
    
    // Scan contents
    echo "<h3>Contents of 'includes' folder:</h3><ul>";
    $files = scandir($includes_path);
    $found_target = false;
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $style = "color:black;";
        if ($file === 'mps-locations-au.php') {
            $style = "color:green; font-weight:bold;";
            $found_target = true;
        }
        
        echo "<li style='$style'>" . $file . "</li>";
    }
    echo "</ul>";
    
    if (!$found_target) {
        echo "<p style='color:red; font-weight:bold;'>❌ 'mps-locations-au.php' is NOT in this folder.</p>";
    } else {
        echo "<p style='color:green; font-weight:bold;'>✅ Target file exists.</p>";
        // Try to read it
        if (is_readable($includes_path . '/mps-locations-au.php')) {
            echo "<p>File is readable.</p>";
        } else {
            echo "<p style='color:red;'>❌ File exists but permissions deny reading.</p>";
        }
    }

} else {
    echo "<p style='color:red; font-weight:bold;'>❌ Folder 'includes' (lowercase) NOT FOUND.</p>";
}

// 2. Check for Capitalized 'Includes'
if (is_dir($includes_path_cap)) {
    echo "<p style='color:orange; font-weight:bold;'>⚠️ Found folder: 'Includes' (Capitalized). This is the problem.</p>";
}

// 3. Check ROOT contents (in case of extraction errors)
echo "<hr><h3>Files in Root Plugin Folder:</h3><ul>";
$root_files = scandir(__DIR__);
foreach ($root_files as $file) {
    if ($file === '.' || $file === '..') continue;
    
    // Check for "Backslash in filename" error (Windows Zip issue)
    if (strpos($file, 'includes\\') !== false) {
        echo "<li style='color:red; font-weight:bold;'>❌ MALFORMED FILENAME: " . $file . " (This is the issue!)</li>";
    } else {
        echo "<li>" . $file . "</li>";
    }
}
echo "</ul>";
?>