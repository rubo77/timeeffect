<?php
/**
 * Integration Script for Modern DB Infrastructure
 * 
 * This script helps integrate the new Composer-based infrastructure
 * into existing PHP files that use PEAR DB
 */

// Find all PHP files that include PEAR DB
function findPearDbFiles($directory) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory)
    );
    
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            
            // Check for PEAR DB usage
            if (preg_match('/require.*pear.*DB\.php|include.*pear.*DB\.php/i', $content) ||
                preg_match('/DB::(connect|factory|isError)/i', $content)) {
                $files[] = $file->getPathname();
            }
        }
    }
    
    return $files;
}

// Add bootstrap include to files
function addBootstrapInclude($filePath) {
    $content = file_get_contents($filePath);
    
    // Check if bootstrap is already included
    if (strpos($content, 'bootstrap.php') !== false) {
        echo "Bootstrap already included in: $filePath\n";
        return false;
    }
    
    // Find the first PHP opening tag
    if (preg_match('/^(<\?php)/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
        $insertPos = $matches[0][1] + strlen($matches[0][0]);
        
        $bootstrapInclude = "\n\n// Load modern infrastructure\nrequire_once __DIR__ . '/../bootstrap.php';\n";
        
        $newContent = substr_replace($content, $bootstrapInclude, $insertPos, 0);
        
        // Backup original file
        copy($filePath, $filePath . '.backup');
        
        // Write modified content
        file_put_contents($filePath, $newContent);
        
        echo "Added bootstrap include to: $filePath\n";
        return true;
    }
    
    echo "Could not find PHP opening tag in: $filePath\n";
    return false;
}

// Main execution
echo "=== TimeEffect Modern DB Integration ===\n\n";

$projectRoot = dirname(__DIR__); // Parent directory since we're in dev/
$pearDbFiles = findPearDbFiles($projectRoot);

echo "Found " . count($pearDbFiles) . " files using PEAR DB:\n";
foreach ($pearDbFiles as $file) {
    echo "- " . str_replace($projectRoot, '', $file) . "\n";
}

echo "\nIntegration options:\n";
echo "1. Add bootstrap.php include to all files (recommended)\n";
echo "2. Manual integration (you handle includes yourself)\n";
echo "3. Show detailed analysis\n";
echo "\nChoose option (1-3): ";

// For automated execution, we'll create a summary file instead
$summary = [
    'timestamp' => date('Y-m-d H:i:s'),
    'files_found' => count($pearDbFiles),
    'files_list' => $pearDbFiles,
    'recommendations' => [
        'Add bootstrap.php include to main entry points',
        'Test PEAR DB compatibility layer',
        'Monitor logs for any compatibility issues',
        'Consider gradual migration to pure Doctrine DBAL'
    ]
];

file_put_contents($projectRoot . '/integration_analysis.json', json_encode($summary, JSON_PRETTY_PRINT));

echo "\nIntegration analysis saved to integration_analysis.json\n";
echo "To complete integration:\n";
echo "1. Copy .env.example to .env and configure your database\n";
echo "2. Add 'require_once __DIR__ . \"/bootstrap.php\";' to your main entry points\n";
echo "3. Test the application with the new infrastructure\n";
echo "4. Monitor logs/app.log for any compatibility issues\n";
?>
