<?php
/**
 * Script to update all template files to use the unified header include
 * This script will replace common header patterns with the new shared header include
 */

// Define the templates directory
$templates_dir = __DIR__ . '/../templates';

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

// Function to update a template file
function updateTemplateFile($filepath) {
    $content = file_get_contents($filepath);
    $original_content = $content;
    $updated = false;
    
    // Calculate relative path to shared/header.ihtml.php
    $relative_path = dirname($filepath);
    $depth = substr_count(str_replace($GLOBALS['templates_dir'], '', $relative_path), '/');
    $include_path = str_repeat('../', $depth) . 'shared/header.ihtml.php';
    
    // Pattern 1: Basic HTML header with HEAD section
    $pattern1 = '/<!-- .*? - START -->\s*(?:<\?php[^>]*?\?>)?\s*<HTML[^>]*>\s*<HEAD>.*?<\/HEAD>\s*(?:<SCRIPT[^>]*>.*?<\/SCRIPT>\s*)*\s*<BODY[^>]*>/s';
    
    if (preg_match($pattern1, $content)) {
        $replacement = "<!-- " . basename($filepath, '.php') . " - START -->\n<?php\n// Include unified header\ninclude_once(__DIR__ . '/$include_path');\n?>";
        $content = preg_replace($pattern1, $replacement, $content);
        $updated = true;
    }
    
    // Pattern 2: PHP block followed by HTML
    $pattern2 = '/<!-- .*? - START -->\s*<\?php\s+.*?\?\>\s*<HTML[^>]*>\s*<HEAD>.*?<\/HEAD>\s*(?:<SCRIPT[^>]*>.*?<\/SCRIPT>\s*)*\s*<BODY[^>]*>/s';
    
    if (!$updated && preg_match($pattern2, $content)) {
        $replacement = "<!-- " . basename($filepath, '.php') . " - START -->\n<?php\n// Include unified header\ninclude_once(__DIR__ . '/$include_path');\n?>";
        $content = preg_replace($pattern2, $replacement, $content);
        $updated = true;
    }
    
    // Write back if updated
    if ($updated && $content !== $original_content) {
        file_put_contents($filepath, $content);
        return true;
    }
    
    return false;
}

// Main execution
echo "Updating template files to use unified header...\n";

$templates_dir = realpath($templates_dir);
$GLOBALS['templates_dir'] = $templates_dir;

$files = findTemplateFiles($templates_dir);
$updated_count = 0;
$skipped_files = [];

foreach ($files as $file) {
    // Skip files that are already using the unified header or are the header itself
    if (strpos($file, 'shared/header.ihtml.php') !== false ||
        strpos(file_get_contents($file), 'shared/header.ihtml.php') !== false) {
        continue;
    }
    
    echo "Processing: " . str_replace($templates_dir, '', $file) . "\n";
    
    if (updateTemplateFile($file)) {
        $updated_count++;
        echo "  ✓ Updated\n";
    } else {
        $skipped_files[] = $file;
        echo "  - Skipped (no matching pattern)\n";
    }
}

echo "\nSummary:\n";
echo "Updated: $updated_count files\n";
echo "Skipped: " . count($skipped_files) . " files\n";

if (!empty($skipped_files)) {
    echo "\nFiles that need manual review:\n";
    foreach ($skipped_files as $file) {
        echo "  - " . str_replace($templates_dir, '', $file) . "\n";
    }
}

echo "\nDone!\n";
