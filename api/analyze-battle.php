<?php
/**
 * API untuk menganalisis battle result dan merekomendasikan role
 * File: api/analyze-battle.php
 */

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Required fields from validation
$testCases = $input['test_cases'] ?? 0;              // Passed test cases (0-5)
$totalTestCases = $input['total_test_cases'] ?? 5;  // Total test cases
$executionTime = $input['execution_time'] ?? 0;     // Time in milliseconds
$codeQuality = $input['code_quality'] ?? 0;         // Score 0-100
$timeComplexity = $input['time_complexity'] ?? '';  // e.g., 'O(n)', 'O(n²)'
$language = $input['language'] ?? '';               // Programming language
$score = $input['score'] ?? 0;                      // Overall score 0-100

// ============================================
// ROLE RECOMMENDATION ENGINE
// ============================================
$roleRecommendation = analyzeAndRecommendRole(
    $testCases,
    $totalTestCases,
    $executionTime,
    $codeQuality,
    $timeComplexity,
    $language,
    $score
);

echo json_encode([
    'success' => true,
    'analysis' => $roleRecommendation
]);

/**
 * Analyze battle metrics and recommend role
 * Returns array with role, description, skills, and feedback
 */
function analyzeAndRecommendRole($testCases, $totalTestCases, $executionTime, $codeQuality, $timeComplexity, $language, $score) {
    
    // Calculate metrics
    $testCasesPercentage = $totalTestCases > 0 ? ($testCases / $totalTestCases) * 100 : 0;
    
    // Scoring system for different aspects
    $frontendScore = 0;
    $backendScore = 0;
    $fullstackScore = 0;
    $devopsScore = 0;
    
    // === ANALYSIS LOGIC ===
    
    // 1. Test Cases Passed (Critical for Backend/Quality)
    if ($testCasesPercentage >= 100) {
        $backendScore += 30;
        $fullstackScore += 20;
    } elseif ($testCasesPercentage >= 80) {
        $backendScore += 25;
        $fullstackScore += 15;
    } elseif ($testCasesPercentage >= 50) {
        $backendScore += 15;
        $fullstackScore += 10;
    }
    
    // 2. Code Quality (Indicates clean code practices)
    if ($codeQuality >= 90) {
        $backendScore += 20;
        $fullstackScore += 15;
        $frontendScore += 10;
    } elseif ($codeQuality >= 75) {
        $backendScore += 15;
        $fullstackScore += 12;
        $frontendScore += 8;
    } elseif ($codeQuality >= 60) {
        $fullstackScore += 8;
        $frontendScore += 5;
    }
    
    // 3. Time Complexity (Efficiency = Backend skill)
    if (stripos($timeComplexity, 'O(1)') !== false || stripos($timeComplexity, 'O(log n)') !== false) {
        $backendScore += 25;
        $fullstackScore += 15;
    } elseif (stripos($timeComplexity, 'O(n)') !== false) {
        $backendScore += 18;
        $fullstackScore += 12;
    } elseif (stripos($timeComplexity, 'O(n²)') !== false || stripos($timeComplexity, 'O(n log n)') !== false) {
        $backendScore += 10;
        $fullstackScore += 8;
    }
    
    // 4. Execution Speed (Optimal = DevOps/Backend)
    if ($executionTime > 0) {
        if ($executionTime < 100) { // < 100ms
            $backendScore += 20;
            $devopsScore += 15;
            $fullstackScore += 10;
        } elseif ($executionTime < 500) { // < 500ms
            $backendScore += 12;
            $devopsScore += 10;
            $fullstackScore += 8;
        } elseif ($executionTime < 1000) { // < 1s
            $fullstackScore += 5;
        }
    }
    
    // 5. Language Type
    if (in_array($language, ['htmlmixed', 'html', 'css', 'javascript', 'react', 'vue'])) {
        $frontendScore += 30;
        $fullstackScore += 15;
    } elseif (in_array($language, ['python', 'php', 'java', 'nodejs', 'golang'])) {
        $backendScore += 25;
        $fullstackScore += 20;
        $devopsScore += 10;
    }
    
    // 6. Overall Score (Problem solving ability)
    if ($score >= 90) {
        $backendScore += 15;
        $fullstackScore += 15;
        $frontendScore += 10;
        $devopsScore += 8;
    } elseif ($score >= 75) {
        $backendScore += 10;
        $fullstackScore += 10;
        $frontendScore += 8;
    } elseif ($score >= 60) {
        $fullstackScore += 8;
        $frontendScore += 5;
    }
    
    // ============================================
    // DETERMINE PRIMARY ROLE
    // ============================================
    $roles = [
        'frontend' => [
            'score' => $frontendScore,
            'name' => 'Frontend Developer',
            'icon' => '🎨',
            'description' => 'Kreativitas dan perhatian detail adalah kekuatanmu. Kamu akan berhasil membangun UI yang indah dan responsif.',
            'skills' => ['HTML/CSS', 'JavaScript', 'React', 'UI/UX', 'Figma'],
            'feedback' => 'Terus berkembang. Skor ' . $score . '/100 menunjukkan potensi besar di frontend — terus problem solving-mu!'
        ],
        'backend' => [
            'score' => $backendScore,
            'name' => 'Backend Developer',
            'icon' => '⚙️',
            'description' => 'Logika kuat dan optimasi adalah keahlianmu. Kamu cocok membangun sistem yang scalable dan efisien.',
            'skills' => ['Python', 'PHP', 'Node.js', 'SQL', 'APIs'],
            'feedback' => 'Kemampuan algoritmamu luar biasa! Dengan ' . round($testCasesPercentage) . '% test cases passed, kamu siap untuk backend yang kompleks.'
        ],
        'fullstack' => [
            'score' => $fullstackScore,
            'name' => 'Full Stack Developer',
            'icon' => '🌐',
            'description' => 'Kamu punya keseimbangan dalam frontend dan backend. Kamu cocok untuk proyek end-to-end.',
            'skills' => ['JavaScript', 'React/Vue', 'Node.js', 'SQL', 'DevOps basics'],
            'feedback' => 'Skill kamu merata di berbagai aspek. Full Stack adalah pilihan tepat untuk karir yang lebih fleksibel.'
        ],
        'devops' => [
            'score' => $devopsScore,
            'name' => 'DevOps Engineer',
            'icon' => '🚀',
            'description' => 'Fokusmu pada optimasi dan efisiensi sempurna untuk infrastruktur. Kamu akan excel di DevOps.',
            'skills' => ['Docker', 'Kubernetes', 'CI/CD', 'Performance Tuning', 'Cloud'],
            'feedback' => 'Performa ' . $executionTime . 'ms dan kompleksitas ' . $timeComplexity . ' menunjukkan potensi DevOps yang besar!'
        ]
    ];
    
    // Get top role
    $topRole = 'fullstack';
    $maxScore = 0;
    foreach ($roles as $key => $role) {
        if ($role['score'] > $maxScore) {
            $maxScore = $role['score'];
            $topRole = $key;
        }
    }
    
    // If all scores are 0, assign based on defaults
    if ($maxScore === 0) {
        if (in_array($language, ['htmlmixed', 'html', 'css', 'javascript'])) {
            $topRole = 'frontend';
        } else {
            $topRole = 'backend';
        }
    }
    
    $recommendedRole = $roles[$topRole];
    
    // ============================================
    // FORMAT RESPONSE
    // ============================================
    return [
        'score' => round($score),
        'test_cases' => $testCases . '/' . $totalTestCases,
        'test_cases_percentage' => round($testCasesPercentage),
        'code_quality' => round($codeQuality),
        'execution_time' => $executionTime > 0 ? $executionTime . 'ms' : 'N/A',
        'time_complexity' => $timeComplexity ?: 'N/A',
        'recommended_role' => $topRole,
        'role' => $recommendedRole['name'],
        'icon' => $recommendedRole['icon'],
        'description' => $recommendedRole['description'],
        'skills' => $recommendedRole['skills'],
        'feedback' => $recommendedRole['feedback'],
        'all_roles' => $roles // For ranking display
    ];
}
?>
