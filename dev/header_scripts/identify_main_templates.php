<?php
/**
 * Script to identify main page templates that need unified header migration
 * vs component templates that are included within other pages
 */

// Define the templates directory
$templates_dir = __DIR__ . '/../../templates';

// Find all .ihtml.php files
function findTemplateFiles($dir) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php' && 
            strpos($file->getFilename(), '.ihtml.') !== false) {
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

// Function to analyze template file type
function analyzeTemplate($filepath) {
    $content = file_get_contents($filepath);
    $filename = basename($filepath);
    
    // Skip if already using unified header
    if (strpos($content, 'shared/header.ihtml.php') !== false) {
        return 'already_migrated';
    }
    
    // Check if it's a main page template (has complete HTML structure)
    if (preg_match('/<HTML[^>]*>/i', $content) && 
        preg_match('/<HEAD>/i', $content) && 
        preg_match('/<BODY[^>]*>/i', $content)) {
        return 'main_page';
    }
    
    // Check if it's a component template (partial HTML, no complete structure)
    if (strpos($filename, 'row.ihtml.php') !== false ||
        strpos($filename, 'form.ihtml.php') !== false ||
        strpos($filename, 'left.ihtml.php') !== false ||
        strpos($filename, 'path.ihtml.php') !== false ||
        strpos($filename, 'topnav.ihtml.php') !== false ||
        strpos($filename, 'main-options.ihtml.php') !== false ||
        strpos($filename, 'login-form.ihtml.php') !== false) {
        return 'component';
    }
    
    // Check for partial HTML content (starts with table rows, divs, etc.)
    if (preg_match('/^\s*(?:<!--.*?-->\s*)?(?:<\?php.*?\?>\s*)*<(?:TR|TD|DIV|FORM|TABLE|P|H[1-6])/i', $content)) {
        return 'component';
    }
    
    // If it has some HTML but not complete structure, likely a component
    if (preg_match('/<(?:TR|TD|DIV|FORM|TABLE|INPUT|SELECT|OPTION)/i', $content) &&
        !preg_match('/<HTML[^>]*>/i', $content)) {
        return 'component';
    }
    
    return 'unknown';
}

// Main execution
echo "Analyzing template files to identify migration candidates...\n\n";

$templates_dir = realpath($templates_dir);
$files = findTemplateFiles($templates_dir);

$main_pages = [];
$components = [];
$already_migrated = [];
$unknown = [];

foreach ($files as $file) {
    $relative_path = str_replace($templates_dir, '', $file);
    $type = analyzeTemplate($file);
    
    switch ($type) {
        case 'main_page':
            $main_pages[] = $relative_path;
            break;
        case 'component':
            $components[] = $relative_path;
            break;
        case 'already_migrated':
            $already_migrated[] = $relative_path;
            break;
        case 'unknown':
            $unknown[] = $relative_path;
            break;
    }
}

echo "=== ANALYSIS RESULTS ===\n\n";

echo "MAIN PAGE TEMPLATES (need unified header migration):\n";
foreach ($main_pages as $file) {
    echo "  - $file\n";
}
echo "Total: " . count($main_pages) . "\n\n";

echo "ALREADY MIGRATED:\n";
foreach ($already_migrated as $file) {
    echo "  - $file\n";
}
echo "Total: " . count($already_migrated) . "\n\n";

echo "COMPONENT TEMPLATES (no migration needed):\n";
foreach ($components as $file) {
    echo "  - $file\n";
}
echo "Total: " . count($components) . "\n\n";

echo "UNKNOWN/NEEDS MANUAL REVIEW:\n";
foreach ($unknown as $file) {
    echo "  - $file\n";
}
echo "Total: " . count($unknown) . "\n\n";

echo "=== MIGRATION RECOMMENDATIONS ===\n";
echo "1. Focus migration efforts on " . count($main_pages) . " main page templates\n";
echo "2. Component templates (" . count($components) . ") should remain as-is\n";
echo "3. Review " . count($unknown) . " unknown templates manually\n";
echo "4. " . count($already_migrated) . " templates already use unified header\n\n";

echo "Done!\n";
