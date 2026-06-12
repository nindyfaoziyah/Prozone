<?php
require_once 'config/config.php';
requireLogin();
require_once 'includes/icons.php';

$page_title       = 'Learning Path';
$page_description = 'Jalur RPG Adventure untuk perjalanan belajar coding Prozone.';
$page_css         = ['sidebar-island.css', 'dashboard-override.css', 'pages/learning-path.css'];
$body_class       = trim(getThemeClass() . ' dashboard-layout');

$learningPath = [
    [
        'level' => 1,
        'name' => 'HTML Forest',
        'icon' => '🌳',
        'theme' => 'html',
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
        'icon' => '🏜️',
        'theme' => 'css',
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
        'icon' => '⚡',
        'theme' => 'js',
        'description' => 'Kuasakan logika interaktif dan bangun mekanisme cerdas di dunia digital.',
        'reward' => 200,
        'material_count' => 8,
        'duration' => '45 min',
        'skills' => ['Variables', 'Functions', 'Conditionals', 'Loops', 'Events'],
        'progress' => 0,
        'status' => 'locked',
        'boss' => 'Build a smart calculator app',
        'nodes' => [
            ['label' => 'Variable', 'status' => 'locked'],
            ['label' => 'Data Type', 'status' => 'locked'],
            ['label' => 'Operator', 'status' => 'locked'],
            ['label' => 'Conditional', 'status' => 'locked'],
            ['label' => 'Loop', 'status' => 'locked'],
            ['label' => 'Function', 'status' => 'locked'],
            ['label' => 'Array', 'status' => 'locked'],
            ['label' => 'Object', 'status' => 'locked'],
        ],
    ],
    [
        'level' => 4,
        'name' => 'React Citadel',
        'icon' => '🏰',
        'theme' => 'react',
        'description' => 'Bangun antarmuka modern yang responsif dengan komponen cerdas dan state dinamis.',
        'reward' => 225,
        'material_count' => 7,
        'duration' => '55 min',
        'skills' => ['Components', 'State', 'Hooks', 'Routing', 'UX'],
        'progress' => 0,
        'status' => 'locked',
        'boss' => 'Create a reactive dashboard experience',
        'nodes' => [
            ['label' => 'Komponen', 'status' => 'locked'],
            ['label' => 'Props', 'status' => 'locked'],
            ['label' => 'State', 'status' => 'locked'],
            ['label' => 'Event Handler', 'status' => 'locked'],
            ['label' => 'Lifecycle', 'status' => 'locked'],
            ['label' => 'Hooks', 'status' => 'locked'],
            ['label' => 'Routing', 'status' => 'locked'],
        ],
    ],
    [
        'level' => 5,
        'name' => 'Backend Volcano',
        'icon' => '🌋',
        'theme' => 'backend',
        'description' => 'Masuki inti server, logika bisnis, dan infrastruktur aplikasi berskala.',
        'reward' => 250,
        'material_count' => 7,
        'duration' => '50 min',
        'skills' => ['Server', 'Routing', 'Auth', 'Database', 'CRUD'],
        'progress' => 0,
        'status' => 'locked',
        'boss' => 'Build a powerful REST API',
        'nodes' => [
            ['label' => 'Server', 'status' => 'locked'],
            ['label' => 'Routing', 'status' => 'locked'],
            ['label' => 'Database', 'status' => 'locked'],
            ['label' => 'Authentication', 'status' => 'locked'],
            ['label' => 'CRUD', 'status' => 'locked'],
            ['label' => 'Validation', 'status' => 'locked'],
            ['label' => 'Deployment', 'status' => 'locked'],
        ],
    ],
    [
        'level' => 6,
        'name' => 'API Ocean',
        'icon' => '🌊',
        'theme' => 'api',
        'description' => 'Terhubung dengan layanan global dan bangun ekosistem data realtime.',
        'reward' => 230,
        'material_count' => 6,
        'duration' => '50 min',
        'skills' => ['HTTP', 'Endpoints', 'Requests', 'Responses', 'Security'],
        'progress' => 0,
        'status' => 'locked',
        'boss' => 'Integrate a realtime API system',
        'nodes' => [
            ['label' => 'HTTP', 'status' => 'locked'],
            ['label' => 'Endpoints', 'status' => 'locked'],
            ['label' => 'Request', 'status' => 'locked'],
            ['label' => 'Response', 'status' => 'locked'],
            ['label' => 'Authentication', 'status' => 'locked'],
            ['label' => 'Debugging', 'status' => 'locked'],
        ],
    ],
    [
        'level' => 7,
        'name' => 'Database Sanctuary',
        'icon' => '💾',
        'theme' => 'database',
        'description' => 'Rancang ruang data yang aman, efisien, dan siap untuk aplikasi canggih.',
        'reward' => 220,
        'material_count' => 6,
        'duration' => '40 min',
        'skills' => ['Schema', 'Queries', 'Relations', 'Indexing', 'Backup'],
        'progress' => 0,
        'status' => 'locked',
        'boss' => 'Design a scalable database schema',
        'nodes' => [
            ['label' => 'Tabel', 'status' => 'locked'],
            ['label' => 'Query', 'status' => 'locked'],
            ['label' => 'Relasi', 'status' => 'locked'],
            ['label' => 'Index', 'status' => 'locked'],
            ['label' => 'Backup', 'status' => 'locked'],
            ['label' => 'Optimasi', 'status' => 'locked'],
        ],
    ],
    [
        'level' => 8,
        'name' => 'Full Stack Empire',
        'icon' => '👑',
        'theme' => 'fullstack',
        'description' => 'Jelajahi mahkota digital dengan kemampuan frontend, backend, dan deployment siap produksi.',
        'reward' => 300,
        'material_count' => 7,
        'duration' => '70 min',
        'skills' => ['Architecture', 'Optimization', 'Testing', 'Scaling', 'Launch'],
        'progress' => 0,
        'status' => 'locked',
        'boss' => 'Launch a full stack flagship app',
        'nodes' => [
            ['label' => 'Integrasi Frontend', 'status' => 'locked'],
            ['label' => 'Integrasi Backend', 'status' => 'locked'],
            ['label' => 'Database', 'status' => 'locked'],
            ['label' => 'API', 'status' => 'locked'],
            ['label' => 'Deployment', 'status' => 'locked'],
            ['label' => 'Testing', 'status' => 'locked'],
            ['label' => 'Optimization', 'status' => 'locked'],
        ],
    ],
];

function statusBadge($status) {
    $map = [
        'completed' => '✅ Legendary',
        'in-progress' => '🟣 Quest Active',
        'locked' => '🔒 Sealed',
    ];
    return $map[$status] ?? ucfirst($status);
}

function nodeStatusLabel($status) {
    $map = [
        'completed' => '✅',
        'active' => '🔵',
        'available' => '🟣',
        'locked' => '🔒',
    ];
    return $map[$status] ?? '●';
}

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
        <section class="learning-path-hero">
            <div class="hero-copy">
                <div class="hero-chip">Journey From Beginner To Digital Legend</div>
                <h1>Prozone World Map: RPG Quest of Code</h1>
                <p>Masuki pengalaman premium yang menggabungkan fantasy, cyberpunk, dan petualangan belajar coding kelas AAA.</p>

                <div class="hero-meta-grid">
                    <div class="hero-value">
                        <span>Total Island</span>
                        <strong><?php echo count($learningPath); ?></strong>
                    </div>
                    <div class="hero-value">
                        <span>Total XP Reward</span>
                        <strong>+<?php echo array_sum(array_column($learningPath, 'reward')); ?> XP</strong>
                    </div>
                    <div class="hero-value">
                        <span>Skill Track</span>
                        <strong><?php echo array_sum(array_map(function ($level) { return count($level['skills']); }, $learningPath)); ?></strong>
                    </div>
                </div>

                <div class="hero-overview">
                    <div class="hero-overview-card">
                        <span>Current Quest</span>
                        <strong>CSS Desert</strong>
                    </div>
                    <div class="hero-overview-card">
                        <span>Next Unlock</span>
                        <strong>JavaScript Kingdom</strong>
                    </div>
                </div>
            </div>

            <div class="hero-visual hero-map-v2">
                <div class="world-sky">
                    <div class="aurora aurora-1"></div>
                    <div class="aurora aurora-2"></div>
                    <div class="stars stars-1"></div>
                    <div class="stars stars-2"></div>
                    <div class="floating-cloud cloud-1"></div>
                    <div class="floating-cloud cloud-2"></div>
                    <div class="map-background">
                        <div class="map-edge-glow"></div>
                        <div class="map-stage world-map-container">
                            <div class="path-layer" aria-hidden="true">
                                <div class="map-path map-path-12"></div>
                                <div class="map-path map-path-23"></div>
                                <div class="map-path map-path-34"></div>
                                <div class="map-path map-path-45"></div>
                                <div class="map-path map-path-56"></div>
                                <div class="map-path map-path-67"></div>
                                <div class="map-path map-path-78"></div>
                            </div>
                            <div class="island-layer">
                                <div class="map-avatar" id="map-avatar">🧑‍💻</div>

                                <?php foreach ($learningPath as $level): ?>
                                    <article class="map-island island-<?php echo $level['level']; ?> <?php echo $level['status']; ?>" data-level="<?php echo $level['level']; ?>">
                                        <div class="island-aura"></div>
                                        <div class="island-top">
                                            <span class="island-icon island-icon-<?php echo htmlspecialchars($level['theme']); ?>"><?php echo htmlspecialchars($level['icon']); ?></span>
                                            <div>
                                                <div class="island-level">Level <?php echo $level['level']; ?></div>
                                                <h3><?php echo htmlspecialchars($level['name']); ?></h3>
                                            </div>
                                        </div>
                                        <div class="island-body">
                                            <p><?php echo htmlspecialchars($level['description']); ?></p>
                                            <div class="island-meta">
                                                <span><?php echo $level['reward']; ?> XP</span>
                                                <span><?php echo $level['material_count']; ?> Materi</span>
                                            </div>
                                        </div>
                                        <div class="island-footer">
                                            <span class="status-pill <?php echo $level['status']; ?>"><?php echo statusBadge($level['status']); ?></span>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="learning-path-details">
            <div class="selected-island-panel" id="selected-island-panel">
                <div class="panel-header">
                    <span class="panel-badge">Floating Glass Panel</span>
                    <h2 id="panel-title">Tap island untuk membuka misi</h2>
                    <p id="panel-copy">Setiap pulau adalah quest unik yang terbuka dalam dunia RPG futuristic ini.</p>
                </div>
                <div class="panel-body">
                    <div class="panel-summary">
                        <div class="panel-stat">
                            <span>Progress</span>
                            <strong id="panel-progress">0%</strong>
                        </div>
                        <div class="panel-stat">
                            <span>Duration</span>
                            <strong id="panel-duration">-</strong>
                        </div>
                        <div class="panel-stat">
                            <span>XP Reward</span>
                            <strong id="panel-xp">0 XP</strong>
                        </div>
                    </div>

                    <div class="panel-glow-bar">
                        <div class="progress-ring" id="panel-ring">
                            <div class="ring-value" id="panel-ring-value">0%</div>
                        </div>
                        <div class="panel-metrics">
                            <div>
                                <span>Status</span>
                                <strong class="status-pill active" id="panel-status">Ready</strong>
                            </div>
                            <div>
                                <span>Boss</span>
                                <strong id="panel-boss">-</strong>
                            </div>
                        </div>
                    </div>

                    <div class="skill-grid" id="panel-skills"></div>
                    <div class="node-chips" id="panel-nodes"></div>
                    <button class="start-learning-btn" id="panel-start-btn" disabled>Start Quest</button>
                </div>
            </div>
        </section>
    </div>
</div>

<?php include 'footer.php'; ?>
<?php include 'includes/loading.php'; ?>
<?php include 'includes/toast.php'; ?>
<script src="assets/js/navbar.js"></script>
<script>
  (function() {
    const levels = <?php echo json_encode($learningPath, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const islands = document.querySelectorAll('.map-island');
    const avatar = document.getElementById('map-avatar');
    const panelTitle = document.getElementById('panel-title');
    const panelCopy = document.getElementById('panel-copy');
    const panelProgress = document.getElementById('panel-progress');
    const panelDuration = document.getElementById('panel-duration');
    const panelXp = document.getElementById('panel-xp');
    const panelStatus = document.getElementById('panel-status');
    const panelBoss = document.getElementById('panel-boss');
    const panelNodes = document.getElementById('panel-nodes');
    const panelSkills = document.getElementById('panel-skills');
    const panelRingValue = document.getElementById('panel-ring-value');
    const panelButton = document.getElementById('panel-start-btn');
    const mapStage = document.querySelector('.map-stage');

    function getLevel(levelId) {
      return levels.find((level) => Number(level.level) === Number(levelId));
    }

    function formatStatus(status) {
      if (status === 'completed') return 'Legendary';
      if (status === 'in-progress') return 'Active Quest';
      return 'Sealed';
    }

    function moveAvatar(targetIsland) {
      if (!targetIsland || !mapStage) return;
      const islandRect = targetIsland.getBoundingClientRect();
      const stageRect = mapStage.getBoundingClientRect();
      const x = islandRect.left - stageRect.left + islandRect.width * 0.5;
      const y = islandRect.top - stageRect.top + islandRect.height * 0.4;
      avatar.style.left = `${x}px`;
      avatar.style.top = `${y}px`;
      avatar.classList.add('avatar-active');
      window.requestAnimationFrame(() => {
        avatar.classList.remove('avatar-active');
      });
    }

    function renderPanel(level) {
      if (!level) return;
      islands.forEach((island) => island.classList.toggle('active-island', Number(island.dataset.level) === Number(level.level)));
      panelTitle.textContent = level.name;
      panelCopy.textContent = level.description;
      panelProgress.textContent = level.progress + '%';
      panelDuration.textContent = level.duration || '-';
      panelXp.textContent = level.reward + ' XP';
      panelStatus.textContent = formatStatus(level.status);
      panelStatus.className = 'status-pill ' + (level.status === 'in-progress' ? 'active' : level.status === 'completed' ? 'completed' : 'locked');
      panelBoss.textContent = level.boss;
      panelRingValue.textContent = level.progress + '%';

      panelNodes.innerHTML = '';
      level.nodes.forEach((node) => {
        const chip = document.createElement('div');
        chip.className = 'node-chip ' + (node.status || 'locked');
        chip.textContent = node.label;
        panelNodes.appendChild(chip);
      });

      panelSkills.innerHTML = '';
      level.skills.forEach((skill) => {
        const chip = document.createElement('span');
        chip.className = 'skill-chip';
        chip.textContent = skill;
        panelSkills.appendChild(chip);
      });

      if (level.status === 'locked') {
        panelButton.disabled = true;
        panelButton.textContent = 'Sealed';
      } else {
        panelButton.disabled = false;
        panelButton.textContent = level.status === 'completed' ? 'Quest Review' : 'Continue Quest';
      }

      const targetIsland = document.querySelector(`.map-island[data-level="${level.level}"]`);
      moveAvatar(targetIsland);
    }

    islands.forEach((island) => {
      island.addEventListener('click', function() {
        const level = getLevel(this.dataset.level);
        renderPanel(level);
      });

      island.addEventListener('mouseenter', function() {
        this.classList.add('island-hover');
      });
      island.addEventListener('mouseleave', function() {
        this.classList.remove('island-hover');
      });
    });

    const initialIsland = document.querySelector('.map-island.in-progress') || document.querySelector('.map-island.completed') || islands[0];
    renderPanel(getLevel(initialIsland?.dataset.level || 1));
  })();
</script>
</body>
</html>
