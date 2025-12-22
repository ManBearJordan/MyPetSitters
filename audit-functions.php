<?php
/**
 * Audit Script: Compare Functions & Check for Junk
 */

$dir_current = __DIR__ . '/my-pet-sitters';
$dir_v200 = __DIR__ . '/v200_reference/my-pet-sitters';

function get_functions_in_dir($dir) {
    if (!is_dir($dir)) return [];
    $functions = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') continue;
        $content = file_get_contents($file->getPathname());
        if (preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\(/', $content, $matches)) {
            foreach ($matches[1] as $func) {
                // Ignore anonymous functions or closures context if possible, but regex above catches named ones
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
        // Skip this audit script itself if inside
        if (basename($file->getPathname()) === 'audit-functions.php') continue;
        
        $lines = file($file->getPathname());
        foreach ($lines as $i => $line) {
            foreach ($junk_terms as $term) {
                if (stripos($line, $term) !== false) {
                    // Filter out legit usages (e.g. error_log in catch blocks is fine, but maybe user wants review)
                    // We'll report everything to be safe.
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

echo "\n--- MISSING FUNCTIONS (Defined in V200 but NOT in Current) ---\n";
if (empty($missing)) {
    echo "NONE. All V200 functions are present.\n";
} else {
    foreach ($missing as $f) echo "- $f\n";
}

$extra = array_diff($funcs_current, $funcs_v200);
echo "\n--- EXTRA FUNCTIONS (New in Current) ---\n";
foreach ($extra as $f) echo "+ $f\n";

echo "\n--- JUNK CODE SCAN ---\n";
$junk = check_junk($dir_current);
foreach ($junk as $j) echo "$j\n";
