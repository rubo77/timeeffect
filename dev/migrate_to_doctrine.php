<?php
/**
 * Migration Script: PEAR DB to Doctrine DBAL
 * 
 * This script helps migrate from legacy PEAR DB code to modern Doctrine DBAL
 */

require_once dirname(__DIR__) . '/bootstrap.php';

class PearToDoctrineMigrator {
    private $patterns = [
        // DB connection patterns
        '/DB::connect\s*\(\s*([^)]+)\s*\)/' => 'ModernDB::connect($1)',
        '/DB::factory\s*\(\s*([^)]+)\s*\)/' => 'ModernDB::connect($1)',
        
        // Error checking patterns
        '/DB::isError\s*\(\s*([^)]+)\s*\)/' => 'ModernDB::isError($1)',
        '/PEAR::isError\s*\(\s*([^)]+)\s*\)/' => 'ModernDB::isError($1)',
        
        // Connection checking
        '/DB::isConnection\s*\(\s*([^)]+)\s*\)/' => 'ModernDB::isConnection($1)',
        
        // Query methods - these need manual review
        '/\$db->query\s*\(\s*([^)]+)\s*\)/' => '$db->query($1) /* Review: Consider using prepared statements */',
        '/\$db->getRow\s*\(\s*([^)]+)\s*\)/' => '$db->getRow($1) /* Review: Consider using prepared statements */',
        '/\$db->getAll\s*\(\s*([^)]+)\s*\)/' => '$db->getAll($1) /* Review: Consider using prepared statements */',
        '/\$db->getOne\s*\(\s*([^)]+)\s*\)/' => '$db->getOne($1) /* Review: Consider using prepared statements */',
    ];
    
    public function analyzeFile($filePath) {
        $content = file_get_contents($filePath);
        $issues = [];
        $suggestions = [];
        
        // Check for PEAR DB usage
        if (preg_match_all('/DB::(connect|factory|isError|isConnection)/', $content, $matches)) {
            $issues[] = "Uses PEAR DB static methods: " . implode(', ', array_unique($matches[1]));
            $suggestions[] = "Replace with ModernDB equivalents";
        }
        
        // Check for direct SQL queries (potential SQL injection)
        if (preg_match_all('/\$db->(query|getRow|getAll|getOne)\s*\(\s*["\']([^"\']*\$[^"\']*)["\']/', $content, $matches)) {
            $issues[] = "Potential SQL injection in queries with variables";
            $suggestions[] = "Use prepared statements with parameter binding";
        }
        
        // Check for error handling
        if (!preg_match('/try\s*\{/', $content) && preg_match('/DB::/', $content)) {
            $issues[] = "No try-catch blocks found for database operations";
            $suggestions[] = "Add proper exception handling";
        }
        
        return [
            'file' => $filePath,
            'issues' => $issues,
            'suggestions' => $suggestions,
            'complexity' => $this->calculateComplexity($content)
        ];
    }
    
    private function calculateComplexity($content) {
        $dbCalls = preg_match_all('/DB::|\$db->/', $content);
        $sqlQueries = preg_match_all('/SELECT|INSERT|UPDATE|DELETE/i', $content);
        
        if ($dbCalls + $sqlQueries < 5) return 'Low';
        if ($dbCalls + $sqlQueries < 15) return 'Medium';
        return 'High';
    }
    
    public function generateMigrationPlan($files) {
        $plan = [
            'phase1' => ['title' => 'Compatibility Layer', 'files' => []],
            'phase2' => ['title' => 'Direct Migration', 'files' => []],
            'phase3' => ['title' => 'Optimization', 'files' => []]
        ];
        
        foreach ($files as $file) {
            $analysis = $this->analyzeFile($file);
            
            switch ($analysis['complexity']) {
                case 'Low':
                    $plan['phase1']['files'][] = $analysis;
                    break;
                case 'Medium':
                    $plan['phase2']['files'][] = $analysis;
                    break;
                case 'High':
                    $plan['phase3']['files'][] = $analysis;
                    break;
            }
        }
        
        return $plan;
    }
    
    public function createModernExample($originalFile) {
        $content = file_get_contents($originalFile);
        $modernContent = $content;
        
        // Apply basic transformations
        foreach ($this->patterns as $pattern => $replacement) {
            $modernContent = preg_replace($pattern, $replacement, $modernContent);
        }
        
        // Add modern error handling template
        $errorHandlingTemplate = '
// Modern error handling example:
try {
    $result = $db->query($sql);
    // Process result
} catch (Exception $e) {
    $GLOBALS[\'logger\']->error(\'Database error\', [
        \'message\' => $e->getMessage(),
        \'file\' => __FILE__,
        \'line\' => __LINE__
    ]);
    // Handle error appropriately
}
';
        
        return $modernContent . "\n\n/* MIGRATION NOTES:\n" . 
               "1. Review all database queries for SQL injection vulnerabilities\n" .
               "2. Replace direct SQL with prepared statements where possible\n" .
               "3. Add proper error handling with try-catch blocks\n" .
               "4. Test thoroughly with the new infrastructure\n" .
               $errorHandlingTemplate . "*/";
    }
}

// Main execution
echo "=== PEAR DB to Doctrine DBAL Migration Analysis ===\n\n";

$migrator = new PearToDoctrineMigrator();

// Load analysis results
$analysisFile = __DIR__ . '/integration_analysis.json';
if (!file_exists($analysisFile)) {
    echo "Please run integrate_modern_db.php first\n";
    exit(1);
}

$analysis = json_decode(file_get_contents($analysisFile), true);
$appFiles = array_filter($analysis['files_list'], function($file) {
    return !strpos($file, '/include/pear/'); // Exclude PEAR library files
});

echo "Analyzing " . count($appFiles) . " application files...\n\n";

$migrationPlan = $migrator->generateMigrationPlan($appFiles);

foreach ($migrationPlan as $phase => $data) {
    echo "=== {$data['title']} ===\n";
    if (empty($data['files'])) {
        echo "No files in this phase.\n\n";
        continue;
    }
    
    foreach ($data['files'] as $fileAnalysis) {
        echo "File: " . basename($fileAnalysis['file']) . " (Complexity: {$fileAnalysis['complexity']})\n";
        if (!empty($fileAnalysis['issues'])) {
            echo "  Issues:\n";
            foreach ($fileAnalysis['issues'] as $issue) {
                echo "    - $issue\n";
            }
        }
        if (!empty($fileAnalysis['suggestions'])) {
            echo "  Suggestions:\n";
            foreach ($fileAnalysis['suggestions'] as $suggestion) {
                echo "    - $suggestion\n";
            }
        }
        echo "\n";
    }
}

// Save migration plan
$migrationData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'migration_plan' => $migrationPlan,
    'next_steps' => [
        'Phase 1: Enable compatibility layer for all files',
        'Phase 2: Migrate medium complexity files to direct Doctrine DBAL',
        'Phase 3: Refactor high complexity files with proper architecture',
        'Testing: Comprehensive testing after each phase'
    ]
];

file_put_contents(__DIR__ . '/migration_plan.json', json_encode($migrationData, JSON_PRETTY_PRINT));

echo "Migration plan saved to migration_plan.json\n";
echo "\nRecommended approach:\n";
echo "1. Start with Phase 1 files (low complexity)\n";
echo "2. Use the compatibility layer initially\n";
echo "3. Gradually migrate to direct Doctrine DBAL usage\n";
echo "4. Focus on security improvements (prepared statements)\n";
?>
