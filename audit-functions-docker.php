<?php
/**
 * Audit Script: Compare Functions & Check for Junk (Docker Path Corrected)
 */

$dir_current = '/var/www/html/wp-content/plugins/my-pet-sitters';
$dir_v200    = '/var/www/html/v200_ref/my-pet-sitters';

function get_functions_in_dir($dir) {
    if (!is_dir($dir)) return [];
    $functions = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') continue;
        $content = file_get_contents($file->getPathname());
        // Simple regex to catch function defs
        if (preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\(/', $content, $matches)) {
            foreach ($matches[1] as $func) {
                $functions[] = $func;
            }
        }
    }
    return array_unique($functions);
}

function check_junk($dir) {
    $junk_terms = ['var_dump', 'print_r', 'error_log', 'TODO', 'FIXME', 'die(', 'exit('];
    $findings = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') continue;
        if (basename($file->getPathname()) === 'audit-functions.php') continue;
        
        $lines = file($file->getPathname());
        foreach ($lines as $i => $line) {
            foreach ($junk_terms as $term) {
                if (stripos($line, $term) !== false) {
                    // Ignore common safe usages or filtered lines if needed
                    // But reporting raw is safer for audit.
                    $findings[] = "JUNK? [$term] in " . basename($file->getPathname()) . ":" . ($i+1);
                }
            }
        }
    }
    return $findings;
}

echo "Scanning Current: $dir_current\n";
echo "Scanning V200:    $dir_v200\n";

$funcs_current = get_functions_in_dir($dir_current);
$funcs_v200 = get_functions_in_dir($dir_v200);

echo "Found " . count($funcs_current) . " functions in Current.\n";
echo "Found " . count($funcs_v200) . " functions in V200.\n";

$missing = array_diff($funcs_v200, $funcs_current);

echo "\n--- MISSING FUNCTIONS (Need Restoration) ---\n";
if (empty($missing)) {
    echo "NONE. All V200 functions are present.\n";
} else {
    foreach ($missing as $f) echo "- $f\n";
}

$extra = array_diff($funcs_current, $funcs_v200);
echo "\n--- EXTRA FUNCTIONS (Extensions) ---\n";
foreach ($extra as $f) echo "+ $f\n";

echo "\n--- JUNK CODE SCAN ---\n";
$junk = check_junk($dir_current);
foreach ($junk as $j) echo "$j\n";
