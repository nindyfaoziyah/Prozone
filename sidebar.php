<?php
// sidebar.php - Prozone Island Navigation (Refined)

$current_page = basename($_SERVER['PHP_SELF']);
$first_name   = explode(' ', $_SESSION['nama_lengkap'] ?? 'User')[0];
$user_level   = $level ?? 1;
$user_xp      = $total_xp ?? 0;

// XP needed for next level (simple formula: level * 500)
$xp_next      = $user_level * 500;
$xp_prev      = ($user_level - 1) * 500;
$xp_progress  = $xp_next - $xp_prev;
$xp_current   = $user_xp - $xp_prev;
$xp_pct       = $xp_progress > 0 ? min(100, round(($xp_current / $xp_progress) * 100)) : 0;
// SVG circle: circumference = 2 * pi * 15.9155 ≈ 100
$dash_value   = $xp_pct; // out of 100

// Fetch RPG class from DB
require_once 'includes/rpg_system.php';
$sidebar_cls = getClassData('code-warrior');
if (isset($db) && isset($_SESSION['user_id'])) {
    $stmt_sb = $db->prepare("SELECT character_class FROM users WHERE id = :uid");
    $stmt_sb->bindParam(':uid', $_SESSION['user_id']);
    $stmt_sb->execute();
    if ($sb_user = $stmt_sb->fetch(PDO::FETCH_ASSOC)) {
        $sidebar_cls = getClassData($sb_user['character_class'] ?? 'code-warrior');
    }
}
$avatar_emoji = $sidebar_cls['badge']; // Fallback label / title attribute

$menu_items = [
    ['label' => 'Dashboard',         'icon' => 'grid',        'link' => 'dashboard.php'],
    ['label' => 'Learning Path',     'icon' => 'map',         'link' => 'learning-path.php', 'badge' => 'NEW'],
    ['label' => 'Courses',           'icon' => 'book',        'link' => 'courses.php'],
    ['label' => 'Characters',        'icon' => 'user',        'link' => 'characters.php', 'badge' => 'RPG'],
    ['label' => 'Coding Arena',      'icon' => 'code',        'link' => 'coding-arena.php'],
    ['label' => 'Multiplayer Battle','icon' => 'zap',         'link' => 'multiplayer.php',   'badge' => 'LIVE'],
    ['label' => 'AI Mentor',         'icon' => 'cpu',         'link' => 'ai-mentor.php'],
    ['label' => 'Digital Twin',      'icon' => 'user-check',  'link' => 'digital-twin.php'],
    ['label' => 'Community',         'icon' => 'users',       'link' => 'community.php'],
    ['label' => 'Leaderboard & Achievement', 'icon' => 'award', 'link' => 'leaderboard.php'],
    ['label' => 'Settings',          'icon' => 'settings',    'link' => 'settings.php'],
];
?>

<!-- Hidden SVG defs for XP ring gradient -->
<svg width="0" height="0" style="position:absolute">
    <defs>
        <linearGradient id="xpGradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%"   stop-color="#6C4CFD"/>
            <stop offset="100%" stop-color="#20C7B7"/>
        </linearGradient>
    </defs>
</svg>

<!-- Sidebar Toggle Button -->
<button id="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Navigation">
    <span id="toggle-icon">☰</span>
</button>

<nav class="sidebar-island-container" id="sidebar-island">
    <div class="sidebar-island-panel">
        <!-- Logo -->
        <div class="sidebar-logo">
            <div class="logo-box">
                <?php icon('code', 18); ?>
            </div>
            <span class="logo-text">Prozone</span>
        </div>

        <!-- Menu -->
        <div class="sidebar-menu" id="sidebar-menu">
            <?php foreach ($menu_items as $item): ?>
                <?php $is_active = ($item['link'] === basename($_SERVER['PHP_SELF'])) ? 'active' : ''; ?>
                <a href="<?php echo $item['link']; ?>" class="menu-item <?php echo $is_active; ?>">
                    <span class="menu-icon"><?php icon($item['icon'], 18); ?></span>
                    <span class="menu-label"><?php echo $item['label']; ?></span>
                    <?php if (!empty($item['badge'])): ?>
                        <span class="menu-badge <?php echo strtolower($item['badge']); ?>"><?php echo $item['badge']; ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- XP Card with Avatar -->
        <div class="sidebar-footer">
            <div class="sidebar-xp-card">
                <!-- Avatar Character + Ring -->
                <div class="xp-avatar-ring" style="width: 48px; height: 48px; position: relative; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                    <svg class="xp-ring-svg" viewBox="0 0 36 36" style="position: absolute; top:0; left:0; width:100%; height:100%;">
                        <path class="xp-ring-bg"  d="M18 3 a15 15 0 0 1 0 30 a15 15 0 0 1 0 -30" style="fill: none; stroke: #E2E8F0; stroke-width: 2.5;"/>
                        <path class="xp-ring-fill" stroke-dasharray="<?php echo $dash_value; ?>,100"
                              d="M18 3 a15 15 0 0 1 0 30 a15 15 0 0 1 0 -30" style="fill: none; stroke: #6C4CFD; stroke-width: 2.5; stroke-linecap: round;"/>
                    </svg>
                    <div class="xp-avatar-emoji" style="position: absolute; width: 34px; height: 34px; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#F5F3FF; border-radius:50%;">
                        <img src="<?php echo htmlspecialchars($sidebar_cls['image']); ?>" alt="<?php echo htmlspecialchars($sidebar_cls['name']); ?>" style="width:100%; height:100%; object-fit:cover; border-radius:50%;" title="<?php echo $avatar_emoji . ' ' . htmlspecialchars($sidebar_cls['name']); ?>">
                    </div>
                </div>

                <!-- Stats -->
                <div class="xp-info">
                    <div class="xp-name"><?php echo htmlspecialchars($first_name); ?></div>
                    <div class="xp-level-badge">Level <?php echo $user_level; ?></div>
                    <div class="xp-bar-row">
                        <div class="xp-bar-track">
                            <div class="xp-bar-fill" style="width:<?php echo $xp_pct; ?>%"></div>
                        </div>
                        <span class="xp-count"><?php echo number_format($user_xp); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Floating BG Orbs -->
<div class="floating-bg-elements">
    <div class="bg-orb orb-1"></div>
    <div class="bg-orb orb-2"></div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar-island');
    const btn     = document.getElementById('sidebar-toggle');
    const icon    = document.getElementById('toggle-icon');
    const wrapper = document.querySelector('.page-wrapper') || document.querySelector('.dashboard-main-container');
    const header  = document.querySelector('.header-floating');

    sidebar.classList.toggle('sidebar-hidden');
    const hidden = sidebar.classList.contains('sidebar-hidden');

    icon.textContent = hidden ? '☰' : '✕';

    // Shift content left when sidebar hidden
    const contentLeft = hidden ? '20px' : 'calc(240px + 40px)';
    const headerLeft  = hidden ? '20px' : 'calc(240px + 40px + 20px)';
    if (wrapper) wrapper.style.marginLeft = hidden ? '0' : '';
    if (header)  header.style.left = headerLeft;
    btn.style.left = hidden ? '20px' : 'calc(240px + 40px + 20px - 46px)';
}
</script>
