<?php
require_once 'config/config.php';
requireLogin();
require_once 'includes/icons.php';

$page_title       = 'Learning Path';
$page_description = 'Pilih role tujuanmu, lalu ikuti jalur belajar yang sesuai.';
$page_css         = ['sidebar-island.css', 'dashboard-override.css', 'pages/learning-path.css'];
$body_class       = trim(getThemeClass() . ' dashboard-layout');

$themeColors = [
    'html' => '#F97316',
    'css' => '#2563EB',
    'js' => '#F59E0B',
    'react' => '#22C55E',
    'backend' => '#14B8A6',
    'api' => '#EC4899',
    'database' => '#0EA5E9',
    'fullstack' => '#10B981',
];

$learningPath = [
    [
        'level' => 1,
        'name' => 'HTML Forest',
        'icon' => '&#127799;',
        'theme' => 'html',
        'course_id' => 1,
        'description' => 'Masuki dunia markup dengan keharmonisan struktur dan desain semantic.',
        'reward' => 100,
        'material_count' => 8,
        'duration' => '30 min',
        'skills' => ['Markup', 'Semantic HTML', 'Forms', 'Media', 'Accessibility'],
        'progress' => 100,
        'status' => 'completed',
        'boss' => 'Build your personal profile page',
        'nodes' => [
            ['label' => 'Hello World', 'status' => 'completed'],
            ['label' => 'Struktur HTML', 'status' => 'completed'],
            ['label' => 'Heading', 'status' => 'completed'],
            ['label' => 'Paragraph', 'status' => 'completed'],
            ['label' => 'Link', 'status' => 'completed'],
            ['label' => 'Image', 'status' => 'completed'],
            ['label' => 'Form', 'status' => 'completed'],
            ['label' => 'Semantic HTML', 'status' => 'completed'],
        ],
    ],
    [
        'level' => 2,
        'name' => 'CSS Desert',
        'icon' => '&#127793;',
        'theme' => 'css',
        'course_id' => 1,
        'description' => 'Ciptakan visual premium dengan gradient, efek neon, dan layout futuristik.',
        'reward' => 150,
        'material_count' => 7,
        'duration' => '35 min',
        'skills' => ['Selectors', 'Color Theory', 'Typography', 'Layout', 'Flexbox'],
        'progress' => 72,
        'status' => 'in-progress',
        'boss' => 'Craft a futuristic landing page',
        'nodes' => [
            ['label' => 'Selector', 'status' => 'completed'],
            ['label' => 'Color', 'status' => 'completed'],
            ['label' => 'Typography', 'status' => 'completed'],
            ['label' => 'Margin', 'status' => 'completed'],
            ['label' => 'Padding', 'status' => 'active'],
            ['label' => 'Flexbox', 'status' => 'locked'],
            ['label' => 'Grid', 'status' => 'locked'],
        ],
    ],
    [
        'level' => 3,
        'name' => 'JavaScript Kingdom',
        'icon' => '&#9889;&#65039;',
        'theme' => 'js',
        'course_id' => 4,
        'description' => 'Kuasakan logika interaktif dan bangun mekanisme cerdas di dunia digital.',
        'reward' => 200,
        'material_count' => 8,
        'duration' => '45 min',
        'skills' => ['Variables', 'Functions', 'Conditionals', 'Loops', 'Events'],
        'progress' => 0,
        'status' => 'in-progress',
        'boss' => 'Build a smart calculator app',
        'nodes' => [
            ['label' => 'Variable', 'status' => 'available'],
            ['label' => 'Data Type', 'status' => 'available'],
            ['label' => 'Operator', 'status' => 'available'],
            ['label' => 'Conditional', 'status' => 'available'],
            ['label' => 'Loop', 'status' => 'available'],
            ['label' => 'Function', 'status' => 'available'],
            ['label' => 'Array', 'status' => 'available'],
            ['label' => 'Object', 'status' => 'available'],
        ],
    ],
    [
        'level' => 4,
        'name' => 'React Citadel',
        'icon' => '&#127952;',
        'theme' => 'react',
        'course_id' => 9,
        'description' => 'Bangun antarmuka modern yang responsif dengan komponen cerdas dan state dinamis.',
        'reward' => 225,
        'material_count' => 7,
        'duration' => '55 min',
        'skills' => ['Components', 'State', 'Hooks', 'Routing', 'UX'],
        'progress' => 0,
        'status' => 'in-progress',
        'boss' => 'Create a reactive dashboard experience',
        'nodes' => [
            ['label' => 'Komponen', 'status' => 'available'],
            ['label' => 'Props', 'status' => 'available'],
            ['label' => 'State', 'status' => 'available'],
            ['label' => 'Event Handler', 'status' => 'available'],
            ['label' => 'Lifecycle', 'status' => 'available'],
            ['label' => 'Hooks', 'status' => 'available'],
            ['label' => 'Routing', 'status' => 'available'],
        ],
    ],
    [
        'level' => 5,
        'name' => 'Backend Volcano',
        'icon' => '&#127797;',
        'theme' => 'backend',
        'course_id' => 3,
        'description' => 'Masuki inti server, logika bisnis, dan infrastruktur aplikasi berskala.',
        'reward' => 250,
        'material_count' => 7,
        'duration' => '50 min',
        'skills' => ['Server', 'Routing', 'Auth', 'Database', 'CRUD'],
        'progress' => 0,
        'status' => 'in-progress',
        'boss' => 'Build a powerful REST API',
        'nodes' => [
            ['label' => 'Server', 'status' => 'available'],
            ['label' => 'Routing', 'status' => 'available'],
            ['label' => 'Database', 'status' => 'available'],
            ['label' => 'Authentication', 'status' => 'available'],
            ['label' => 'CRUD', 'status' => 'available'],
            ['label' => 'Validation', 'status' => 'available'],
            ['label' => 'Deployment', 'status' => 'available'],
        ],
    ],
    [
        'level' => 6,
        'name' => 'API Ocean',
        'icon' => '&#127753;',
        'theme' => 'api',
        'course_id' => 10,
        'description' => 'Terhubung dengan layanan global dan bangun ekosistem data realtime.',
        'reward' => 230,
        'material_count' => 6,
        'duration' => '50 min',
        'skills' => ['HTTP', 'Endpoints', 'Requests', 'Responses', 'Security'],
        'progress' => 0,
        'status' => 'in-progress',
        'boss' => 'Integrate a realtime API system',
        'nodes' => [
            ['label' => 'HTTP', 'status' => 'available'],
            ['label' => 'Endpoints', 'status' => 'available'],
            ['label' => 'Request', 'status' => 'available'],
            ['label' => 'Response', 'status' => 'available'],
            ['label' => 'Authentication', 'status' => 'available'],
            ['label' => 'Debugging', 'status' => 'available'],
        ],
    ],
    [
        'level' => 7,
        'name' => 'Database Sanctuary',
        'icon' => '&#128194;',
        'theme' => 'database',
        'course_id' => 11,
        'description' => 'Rancang ruang data yang aman, efisien, dan siap untuk aplikasi canggih.',
        'reward' => 220,
        'material_count' => 6,
        'duration' => '40 min',
        'skills' => ['Schema', 'Queries', 'Relations', 'Indexing', 'Backup'],
        'progress' => 0,
        'status' => 'in-progress',
        'boss' => 'Design a scalable database schema',
        'nodes' => [
            ['label' => 'Tabel', 'status' => 'available'],
            ['label' => 'Query', 'status' => 'available'],
            ['label' => 'Relasi', 'status' => 'available'],
            ['label' => 'Index', 'status' => 'available'],
            ['label' => 'Backup', 'status' => 'available'],
            ['label' => 'Optimasi', 'status' => 'available'],
        ],
    ],
    [
        'level' => 8,
        'name' => 'Full Stack Empire',
        'icon' => '&#128138;',
        'theme' => 'fullstack',
        'course_id' => 3,
        'description' => 'Jelajahi mahkota digital dengan kemampuan frontend, backend, dan deployment siap produksi.',
        'reward' => 300,
        'material_count' => 7,
        'duration' => '70 min',
        'skills' => ['Architecture', 'Optimization', 'Testing', 'Scaling', 'Launch'],
        'progress' => 0,
        'status' => 'in-progress',
        'boss' => 'Launch a full stack flagship app',
        'nodes' => [
            ['label' => 'Integrasi Frontend', 'status' => 'available'],
            ['label' => 'Integrasi Backend', 'status' => 'available'],
            ['label' => 'Database', 'status' => 'available'],
            ['label' => 'API', 'status' => 'available'],
            ['label' => 'Deployment', 'status' => 'available'],
            ['label' => 'Testing', 'status' => 'available'],
            ['label' => 'Optimization', 'status' => 'available'],
        ],
    ],
];

// Role definitions
$roles = [
    'frontend' => [
        'id' => 'frontend',
        'name' => 'Frontend Developer',
        'icon' => '🎨',
        'description' => 'Bangun antarmuka web yang indah, interaktif, dan responsif. Mulai dari HTML, CSS, JavaScript, hingga framework modern.',
        'color' => '#22C55E',
        'gradient' => 'linear-gradient(135deg, #22C55E, #0EA5E9)',
        'levels' => [1, 2, 3, 4],
    ],
    'backend' => [
        'id' => 'backend',
        'name' => 'Backend Developer',
        'icon' => '&#128295;',
        'description' => 'Kuasai logika server, API, database, dan arsitektur aplikasi yang scalable dan aman.',
        'color' => '#14B8A6',
        'gradient' => 'linear-gradient(135deg, #14B8A6, #EC4899)',
        'levels' => [5, 6, 7],
    ],
    'fullstack' => [
        'id' => 'fullstack',
        'name' => 'Full Stack Developer',
        'icon' => '&#128640;',
        'description' => 'Kuasai frontend hingga backend. Dari HTML hingga deployment, jadi developer serba bisa!',
        'color' => '#F59E0B',
        'gradient' => 'linear-gradient(135deg, #F59E0B, #EC4899)',
        'levels' => [1, 2, 3, 4, 5, 6, 7, 8],
    ],
    'data' => [
        'id' => 'data',
        'name' => 'Data & AI Engineer',
        'icon' => '&#129302;',
        'description' => 'Olah data, bangun pipeline, dan ciptakan sistem cerdas dengan machine learning.',
        'color' => '#06B6D4',
        'gradient' => 'linear-gradient(135deg, #06B6D4, #3B82F6)',
        'levels' => [3, 5, 6, 7],
    ],
];

function statusBadge($status) {
    $map = [
        'completed' => '&#10004; Legendary',
        'in-progress' => '&#127764; Quest Active',
        'locked' => '&#128274; Sealed',
    ];
    return $map[$status] ?? ucfirst($status);
}

// Calculate stats per role
$totalLevels = count($learningPath);
$completedLevels = count(array_filter($learningPath, fn($l) => $l['status'] === 'completed'));
$inProgressLevels = count(array_filter($learningPath, fn($l) => $l['status'] === 'in-progress'));
$totalXP = array_sum(array_column($learningPath, 'reward'));
$overallProgress = round(array_sum(array_column($learningPath, 'progress')) / $totalLevels);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once 'includes/head.php'; ?>
</head>
<body class="<?php echo $body_class; ?>">
<?php require_once 'navbar.php'; ?>

<div class="page-wrapper dashboard-main-container">
    <div class="learning-path-page">

        <!-- ============================================= -->
        <!-- STEP 1: ROLE SELECTION -->
        <!-- ============================================= -->
        <div class="lp-step" id="lp-step-role">
            <section class="lp-hero">
                <div class="lp-hero-inner">
                    <div class="lp-hero-content">
                        <span class="lp-hero-chip">&#127775; Pilih Role-mu</span>
                        <h1>Who will you become?</h1>
                        <p>Pilih role developer yang ingin kamu capai. Kami akan menyusun jalur belajar yang tepat sesuai tujuanmu.</p>

                        <div class="lp-stats">
                            <div class="lp-stat-item">
                                <div class="lp-stat-icon lp-stat-icon--completed">&#10004;</div>
                                <div class="lp-stat-info">
                                    <span class="lp-stat-label">Completed</span>
                                    <strong class="lp-stat-value"><?php echo $completedLevels; ?></strong>
                                </div>
                            </div>
                            <div class="lp-stat-item">
                                <div class="lp-stat-icon lp-stat-icon--active">&#127764;</div>
                                <div class="lp-stat-info">
                                    <span class="lp-stat-label">In Progress</span>
                                    <strong class="lp-stat-value"><?php echo $inProgressLevels; ?></strong>
                                </div>
                            </div>
                            <div class="lp-stat-item">
                                <div class="lp-stat-icon lp-stat-icon--xp">&#9733;</div>
                                <div class="lp-stat-info">
                                    <span class="lp-stat-label">Total XP</span>
                                    <strong class="lp-stat-value">+<?php echo $totalXP; ?></strong>
                                </div>
                            </div>
                            <div class="lp-stat-item">
                                <div class="lp-stat-icon lp-stat-icon--progress">&#128200;</div>
                                <div class="lp-stat-info">
                                    <span class="lp-stat-label">Progress</span>
                                    <strong class="lp-stat-value"><?php echo $overallProgress; ?>%</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lp-hero-illustration">
                        <div class="lp-illustration-ring">
                            <div class="lp-illustration-core">
                                <span class="lp-illustration-icon">&#127891;</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="lp-section">
                <div class="lp-section-header">
                    <h2>Choose Your Path</h2>
                    <p>Setiap role memiliki jalur belajar yang berbeda. Pilih sesuai tujuan karirmu.</p>
                </div>

                <div class="lp-roles-grid">
                    <?php foreach ($roles as $role): ?>
                    <?php
                        $roleLevels = array_filter($learningPath, fn($l) => in_array($l['level'], $role['levels']));
                        $roleLevels = array_values($roleLevels);
                        $totalDuration = array_sum(array_map(function($d) {
                            return (int) filter_var($d, FILTER_SANITIZE_NUMBER_INT);
                        }, array_column($roleLevels, 'duration')));
                        $totalMateri = array_sum(array_column($roleLevels, 'material_count'));
                        $totalRoleXP = array_sum(array_column($roleLevels, 'reward'));
                        $roleProgress = round(array_sum(array_column($roleLevels, 'progress')) / count($roleLevels));
                    ?>
                    <div class="lp-role-card" data-role="<?php echo $role['id']; ?>" style="--role-color: <?php echo $role['color']; ?>; --role-gradient: <?php echo $role['gradient']; ?>;">
                        <div class="lp-role-glow"></div>
                        <div class="lp-role-visual">
                            <div class="lp-role-icon-wrap">
                                <span class="lp-role-icon"><?php echo $role['icon']; ?></span>
                            </div>
                        </div>
                        <div class="lp-role-body">
                            <h3 class="lp-role-name"><?php echo $role['name']; ?></h3>
                            <p class="lp-role-desc"><?php echo $role['description']; ?></p>
                            <div class="lp-role-meta">
                                <span class="lp-role-meta-item">
                                    <span>&#128218;</span> <?php echo count($role['levels']); ?> Levels
                                </span>
                                <span class="lp-role-meta-item">
                                    <span>&#9201;</span> ~<?php echo $totalDuration; ?> min
                                </span>
                                <span class="lp-role-meta-item">
                                    <span>&#9733;</span> +<?php echo $totalRoleXP; ?> XP
                                </span>
                            </div>
                            <div class="lp-role-progress">
                                <div class="lp-role-progress-bar">
                                    <div class="lp-role-progress-fill" style="width: <?php echo $roleProgress; ?>%"></div>
                                </div>
                                <span class="lp-role-progress-text"><?php echo $roleProgress; ?>%</span>
                            </div>
                        </div>
                        <div class="lp-role-footer">
                            <span class="lp-role-btn">Start This Path &#8594;</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <!-- ============================================= -->
        <!-- STEP 2: LEARNING PATH (hidden initially) -->
        <!-- ============================================= -->
        <div class="lp-step" id="lp-step-path" style="display: none;">
            <section class="lp-path-header">
                <div class="lp-path-header-left">
                    <button class="lp-back-btn" id="lp-back-btn">&#8592; Change Role</button>
                    <div class="lp-path-header-info">
                        <span class="lp-path-role-icon" id="lp-path-role-icon"></span>
                        <div>
                            <span class="lp-path-role-label">Learning Path</span>
                            <h2 id="lp-path-role-name"></h2>
                        </div>
                    </div>
                </div>
                <div class="lp-path-header-stats" id="lp-path-header-stats"></div>
            </section>

            <div class="lp-levels-grid" id="lp-levels-grid"></div>

            <!-- Detail Panel -->
            <section class="lp-section lp-detail-section">
                <div class="lp-detail-panel" id="lp-detail-panel">
                    <div class="lp-detail-placeholder" id="lp-detail-placeholder">
                        <span class="lp-detail-placeholder-icon">&#127916;</span>
                        <h3>Pilih Level untuk Mulai</h3>
                        <p>Klik salah satu level di atas untuk melihat detail quest, skill, dan node pembelajaran.</p>
                    </div>

                    <div class="lp-detail-content" id="lp-detail-content" style="display: none;">
                        <div class="lp-detail-top">
                            <div class="lp-detail-icon-wrap">
                                <span class="lp-detail-icon" id="lp-detail-icon"></span>
                            </div>
                            <div class="lp-detail-info">
                                <span class="lp-detail-level" id="lp-detail-level"></span>
                                <h2 id="lp-detail-name"></h2>
                                <p id="lp-detail-desc"></p>
                            </div>
                            <div class="lp-detail-ring-wrap">
                                <svg class="lp-detail-ring" viewBox="0 0 120 120" id="lp-detail-ring-svg">
                                    <circle cx="60" cy="60" r="52" fill="none" stroke="var(--lp-ring-bg)" stroke-width="8"/>
                                    <circle cx="60" cy="60" r="52" fill="none" stroke="var(--lp-ring-color)" stroke-width="8"
                                        stroke-dasharray="326.73" stroke-dashoffset="326.73" stroke-linecap="round"
                                        id="lp-detail-ring-circle"
                                        transform="rotate(-90 60 60)"/>
                                    <text x="60" y="56" text-anchor="middle" fill="#1e1b4b" font-size="18" font-weight="800" id="lp-detail-ring-text">0%</text>
                                    <text x="60" y="74" text-anchor="middle" fill="#64748b" font-size="10" font-weight="600">COMPLETE</text>
                                </svg>
                            </div>
                        </div>

                        <div class="lp-detail-stats">
                            <div class="lp-detail-stat">
                                <span class="lp-detail-stat-icon">&#9733;</span>
                                <span class="lp-detail-stat-label">XP Reward</span>
                                <strong id="lp-detail-xp">0 XP</strong>
                            </div>
                            <div class="lp-detail-stat">
                                <span class="lp-detail-stat-icon">&#9201;</span>
                                <span class="lp-detail-stat-label">Duration</span>
                                <strong id="lp-detail-duration">-</strong>
                            </div>
                            <div class="lp-detail-stat">
                                <span class="lp-detail-stat-icon">&#128218;</span>
                                <span class="lp-detail-stat-label">Materials</span>
                                <strong id="lp-detail-materials">0</strong>
                            </div>
                            <div class="lp-detail-stat">
                                <span class="lp-detail-stat-icon" id="lp-detail-status-icon"></span>
                                <span class="lp-detail-stat-label">Status</span>
                                <strong id="lp-detail-status">-</strong>
                            </div>
                        </div>

                        <div class="lp-detail-section-title">
                            <span>&#128161;</span> Boss Quest
                        </div>
                        <div class="lp-detail-boss" id="lp-detail-boss"></div>

                        <div class="lp-detail-section-title">
                            <span>&#9881;</span> Skills to Learn
                        </div>
                        <div class="lp-detail-skills" id="lp-detail-skills"></div>

                        <div class="lp-detail-section-title">
                            <span>&#127991;</span> Learning Nodes
                        </div>
                        <div class="lp-detail-nodes" id="lp-detail-nodes"></div>

                        <button class="lp-detail-btn" id="lp-detail-btn">Start Quest</button>
                    </div>
                </div>
            </section>
        </div>

    </div>
</div>

<?php include 'includes/loading.php'; ?>
<?php include 'includes/toast.php'; ?>
<script src="assets/js/navbar.js"></script>
<script>
(function() {
    const allLevels = <?php echo json_encode($learningPath, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const roles = <?php echo json_encode($roles, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const themeColors = <?php echo json_encode($themeColors, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    const stepRole = document.getElementById('lp-step-role');
    const stepPath = document.getElementById('lp-step-path');
    const backBtn = document.getElementById('lp-back-btn');
    const roleCards = document.querySelectorAll('.lp-role-card');
    const levelsGrid = document.getElementById('lp-levels-grid');
    const pathRoleIcon = document.getElementById('lp-path-role-icon');
    const pathRoleName = document.getElementById('lp-path-role-name');
    const pathHeaderStats = document.getElementById('lp-path-header-stats');

    // Detail panel elements
    const placeholder = document.getElementById('lp-detail-placeholder');
    const content = document.getElementById('lp-detail-content');
    const detailIcon = document.getElementById('lp-detail-icon');
    const detailLevel = document.getElementById('lp-detail-level');
    const detailName = document.getElementById('lp-detail-name');
    const detailDesc = document.getElementById('lp-detail-desc');
    const detailRingCircle = document.getElementById('lp-detail-ring-circle');
    const detailRingText = document.getElementById('lp-detail-ring-text');
    const detailXp = document.getElementById('lp-detail-xp');
    const detailDuration = document.getElementById('lp-detail-duration');
    const detailMaterials = document.getElementById('lp-detail-materials');
    const detailStatusIcon = document.getElementById('lp-detail-status-icon');
    const detailStatus = document.getElementById('lp-detail-status');
    const detailBoss = document.getElementById('lp-detail-boss');
    const detailSkills = document.getElementById('lp-detail-skills');
    const detailNodes = document.getElementById('lp-detail-nodes');
    const detailBtn = document.getElementById('lp-detail-btn');
    const circumference = 2 * Math.PI * 52;
    let currentLevel = null;

    // Quest button click - open quest slides then coding practice
    detailBtn.addEventListener('click', function() {
        if (currentLevel && currentLevel.course_id) {
            window.location.href = 'quest.php?course_id=' + currentLevel.course_id;
        }
    });

    // ---- Role Selection ----
    roleCards.forEach(function(card) {
        card.addEventListener('click', function() {
            const roleId = this.dataset.role;
            selectRole(roleId);
        });
    });

    backBtn.addEventListener('click', function() {
        stepPath.style.display = 'none';
        stepRole.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    function selectRole(roleId) {
        const role = roles[roleId];
        if (!role) return;

        switchToPath(role);
    }

    function switchToPath(role) {
        // Update header
        pathRoleIcon.innerHTML = role.icon;
        pathRoleName.textContent = role.name;

        // Build stats
        const roleLevels = role.levels.map(function(id) {
            return allLevels.find(function(l) { return l.level === id; });
        }).filter(Boolean);

        const totalDuration = roleLevels.reduce(function(sum, l) {
            return sum + (parseInt(l.duration) || 0);
        }, 0);
        const totalXP = roleLevels.reduce(function(sum, l) { return sum + l.reward; }, 0);
        const totalMateri = roleLevels.reduce(function(sum, l) { return sum + l.material_count; }, 0);
        const earned = roleLevels.filter(function(l) { return l.status === 'completed'; }).length;

        pathHeaderStats.innerHTML =
            '<div class="lp-path-stat"><strong>' + earned + '/' + roleLevels.length + '</strong><span>Done</span></div>' +
            '<div class="lp-path-stat"><strong>' + totalMateri + '</strong><span>Materi</span></div>' +
            '<div class="lp-path-stat"><strong>' + totalDuration + 'm</strong><span>Duration</span></div>' +
            '<div class="lp-path-stat"><strong>+' + totalXP + '</strong><span>XP</span></div>';

        // Build level cards
        levelsGrid.innerHTML = '';
        roleLevels.forEach(function(level) {
            if (!level) return;
            const card = document.createElement('article');
            card.className = 'lp-card ' + (level.status || 'locked');
            card.dataset.level = level.level;

            const color = themeColors[level.theme] || '#3B82F6';

            card.innerHTML =
                '<div class="lp-card-header">' +
                    '<div class="lp-card-icon-wrap">' +
                        '<span class="lp-card-icon" style="background:' + color + ';">' + level.icon + '</span>' +
                        (level.status === 'completed' ? '<span class="lp-card-badge lp-card-badge--completed">&#10004;</span>' : '') +
                        (level.status === 'in-progress' ? '<span class="lp-card-badge lp-card-badge--active">&#8226;</span>' : '') +
                    '</div>' +
                    '<div class="lp-card-title-area">' +
                        '<span class="lp-card-level">Level ' + level.level + '</span>' +
                        '<h3>' + level.name + '</h3>' +
                    '</div>' +
                '</div>' +
                '<div class="lp-card-body">' +
                    '<div class="lp-card-meta">' +
                        '<span class="lp-card-meta-item"><span class="lp-card-meta-icon">&#9733;</span> ' + level.reward + ' XP</span>' +
                        '<span class="lp-card-meta-item"><span class="lp-card-meta-icon">&#128218;</span> ' + level.material_count + ' Materi</span>' +
                        '<span class="lp-card-meta-item"><span class="lp-card-meta-icon">&#9201;</span> ' + level.duration + '</span>' +
                    '</div>' +
                    '<div class="lp-card-progress">' +
                        '<div class="lp-progress-bar lp-progress-bar--sm">' +
                            '<div class="lp-progress-fill" style="--fill: ' + level.progress + '%"></div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="lp-card-footer">' +
                    '<span class="lp-card-status lp-card-status--' + level.status + '">' +
                        (level.status === 'completed' ? 'Legendary' : level.status === 'in-progress' ? 'Active Quest' : 'Locked') +
                    '</span>' +
                    '<span class="lp-card-arrow">&#8594;</span>' +
                '</div>';

            card.addEventListener('click', function() {
                const lvl = allLevels.find(function(l) { return l.level === Number(this.dataset.level); }.bind(this));
                if (lvl) renderDetail(lvl);
            });

            levelsGrid.appendChild(card);
        });

        // Show path, hide role selection
        stepRole.style.display = 'none';
        stepPath.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Reset detail panel
        placeholder.style.display = 'flex';
        content.style.display = 'none';

        // Auto-select first in-progress or completed
        const firstCard = levelsGrid.querySelector('.lp-card.in-progress') || levelsGrid.querySelector('.lp-card.completed') || levelsGrid.querySelector('.lp-card');
        if (firstCard) {
            const lvl = allLevels.find(function(l) { return l.level === Number(firstCard.dataset.level); });
            if (lvl) renderDetail(lvl);
        }
    }

    // ---- Detail Panel ----
    function formatStatus(status) {
        if (status === 'completed') return 'Legendary';
        if (status === 'in-progress') return 'Active Quest';
        return 'Locked';
    }

    function animateRing(circle, percent) {
        const offset = circumference - (percent / 100) * circumference;
        circle.style.transition = 'stroke-dashoffset 0.6s ease';
        circle.style.strokeDashoffset = offset;
    }

    function renderDetail(level) {
        if (!level) return;

        currentLevel = level;
        placeholder.style.display = 'none';
        content.style.display = 'block';

        const color = themeColors[level.theme] || '#3B82F6';
        detailIcon.innerHTML = level.icon;
        detailIcon.style.background = 'linear-gradient(135deg, ' + color + ', ' + color + 'dd)';
        detailLevel.textContent = 'Level ' + level.level;
        detailName.textContent = level.name;
        detailDesc.textContent = level.description;
        detailXp.textContent = level.reward + ' XP';
        detailDuration.textContent = level.duration || '-';
        detailMaterials.textContent = level.material_count;
        detailStatus.textContent = formatStatus(level.status);

        const statusIcons = { completed: '&#10004;', 'in-progress': '&#127764;', locked: '&#128274;' };
        detailStatusIcon.innerHTML = statusIcons[level.status] || '&#9899;';

        content.style.setProperty('--lp-ring-color', color);
        detailRingCircle.style.stroke = color;

        // Boss
        detailBoss.innerHTML = '';
        const bossDiv = document.createElement('div');
        bossDiv.className = 'lp-detail-boss-item';
        bossDiv.innerHTML = '<span class="lp-detail-boss-icon">&#128126;</span> ' + level.boss;
        detailBoss.appendChild(bossDiv);

        // Skills
        detailSkills.innerHTML = '';
        level.skills.forEach(function(skill) {
            const chip = document.createElement('span');
            chip.className = 'lp-detail-skill-chip';
            chip.textContent = skill;
            detailSkills.appendChild(chip);
        });

        // Nodes
        detailNodes.innerHTML = '';
        level.nodes.forEach(function(node) {
            const chip = document.createElement('span');
            chip.className = 'lp-detail-node-chip lp-detail-node-chip--' + (node.status || 'locked');
            const icons = { completed: '&#10004;', active: '&#8226;', locked: '&#128274;', available: '&#9654;' };
            chip.innerHTML = (icons[node.status] || '&#9899;') + ' ' + node.label;
            detailNodes.appendChild(chip);
        });

        // Button
        if (level.status === 'locked') {
            detailBtn.disabled = true;
            detailBtn.textContent = 'Locked';
            detailBtn.className = 'lp-detail-btn lp-detail-btn--disabled';
        } else {
            detailBtn.disabled = false;
            detailBtn.textContent = level.status === 'completed' ? 'Review Quest' : (level.progress > 0 ? 'Continue Quest' : 'Start Quest');
            detailBtn.className = 'lp-detail-btn';
        }

        detailRingText.textContent = level.progress + '%';
        animateRing(detailRingCircle, level.progress);

        // Highlight active card
        levelsGrid.querySelectorAll('.lp-card').forEach(function(card) {
            card.classList.toggle('lp-card--active', Number(card.dataset.level) === Number(level.level));
        });
    }
})();
</script>
</body>
</html>
