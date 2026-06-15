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

// Check user role
$user_role = $_SESSION['user_role'] ?? 'student';

// Student menu items
if ($user_role === 'student') {
    $menu_items = [
        ['label' => 'Dashboard',         'icon' => 'grid',        'link' => 'student/dashboard.php'],
        ['label' => 'Learning Path',     'icon' => 'map',         'link' => 'student/learning-path.php', 'badge' => 'NEW'],
        ['label' => 'Courses',           'icon' => 'book',        'link' => 'student/courses.php'],
        ['label' => 'Achievement',       'icon' => 'trophy',      'link' => 'characters.php'],
        ['label' => 'Multiplayer Battle','icon' => 'zap',         'link' => 'multiplayer.php',   'badge' => 'LIVE'],
        ['label' => 'AI Mentor',         'icon' => 'cpu',         'link' => 'ai-mentor.php'],
        ['label' => 'Clan',             'icon' => 'users',       'link' => 'clan.php'],
        ['label' => 'Leaderboard',      'icon' => 'award',       'link' => 'leaderboard.php'],
        ['label' => 'Profile',          'icon' => 'user',        'link' => 'student/profile.php'],
    ];
}

// Admin menu items
if ($user_role === 'admin') {
    $menu_items = [
        ['label' => 'Dashboard',         'icon' => 'grid',        'link' => 'admin/dashboard.php', 'badge' => 'ADMIN'],
        ['label' => '',                  'icon' => '',            'link' => '', 'type' => 'divider'],
        ['label' => 'Konten',            'icon' => '',            'link' => '', 'type' => 'label'],
        ['label' => 'Kelola Kursus',     'icon' => 'book',        'link' => 'admin/manage-courses.php', 'badge' => 'ADMIN'],
        ['label' => 'Lessons',           'icon' => 'file-text',   'link' => 'admin/manage-lessons.php', 'badge' => 'ADMIN'],
        ['label' => 'Kategori Kursus',   'icon' => 'tag',         'link' => 'admin/manage-categories.php', 'badge' => 'ADMIN'],
        ['label' => 'Achievements',      'icon' => 'award',       'link' => 'admin/manage-achievements.php', 'badge' => 'ADMIN'],
        ['label' => 'Sertifikat',        'icon' => 'certificate', 'link' => 'admin/manage-certificates.php', 'badge' => 'ADMIN'],
        ['label' => 'Komentar',          'icon' => 'message-circle', 'link' => 'admin/manage-comments.php', 'badge' => 'ADMIN'],
        ['label' => '',                  'icon' => '',            'link' => '', 'type' => 'divider'],
        ['label' => 'Pengguna',          'icon' => '',            'link' => '', 'type' => 'label'],
        ['label' => 'Kelola User',       'icon' => 'users',       'link' => 'admin/users.php', 'badge' => 'ADMIN'],
        ['label' => 'Kelola Clan',       'icon' => 'zap',         'link' => 'admin/manage-clans.php', 'badge' => 'ADMIN'],
        ['label' => 'Enrollments',       'icon' => 'clipboard',   'link' => 'admin/manage-enrollments.php', 'badge' => 'ADMIN'],
        ['label' => '',                  'icon' => '',            'link' => '', 'type' => 'divider'],
        ['label' => 'Sistem',            'icon' => '',            'link' => '', 'type' => 'label'],
        ['label' => 'Broadcast',         'icon' => 'send',        'link' => 'admin/manage-notifications.php', 'badge' => 'ADMIN'],
        ['label' => 'Log Aktivitas',     'icon' => 'activity',    'link' => 'admin/manage-logs.php', 'badge' => 'ADMIN'],
        ['label' => 'Export Data',       'icon' => 'download',    'link' => 'admin/export.php', 'badge' => 'ADMIN'],
        ['label' => 'Backup DB',         'icon' => 'shield',      'link' => 'admin/manage-backup.php', 'badge' => 'ADMIN'],
        ['label' => 'Analytics',         'icon' => 'bar-chart',   'link' => 'admin/admin_analytics.php', 'badge' => 'ADMIN'],
        ['label' => 'Settings',          'icon' => 'settings',    'link' => 'admin/pengaturan.php'],
    ];
}
?>

<!-- Hidden SVG defs for XP ring gradient -->
<svg width="0" height="0" style="position:absolute">
    <defs>
        <linearGradient id="xpGradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%"   stop-color="#3B82F6"/>
            <stop offset="100%" stop-color="#20C7B7"/>
        </linearGradient>
    </defs>
</svg>

<nav class="sidebar-island-container" id="sidebar-island">
    <div class="sidebar-island-panel">
        <!-- Logo -->
        <div class="sidebar-logo">
            <a href="dashboard.php" class="logo-box">
                <img src="assets/img/Prozone Logo.png" alt="Prozone">
            </a>
        </div>

        <!-- Menu -->
        <div class="sidebar-menu" id="sidebar-menu">
            <?php foreach ($menu_items as $item): ?>
                <?php if (isset($item['type']) && $item['type'] === 'label'): ?>
                    <div class="sidebar-section-label">
                        <span><?php echo $item['label']; ?></span>
                        <span class="label-line"></span>
                    </div>
                <?php elseif (isset($item['type']) && $item['type'] === 'divider'): ?>
                    <div class="sidebar-divider"></div>
                <?php else: ?>
                    <?php 
                    $is_active = (basename($item['link']) === basename($_SERVER['PHP_SELF'])) ? 'active' : '';
                    $extra_class = '';
                    $is_admin = false;
                    if (!empty($item['badge']) && strtolower($item['badge']) === 'admin') {
                        $extra_class = ' admin-item';
                        $is_admin = true;
                    }
                    ?>
                    <a href="<?php echo $item['link']; ?>" class="menu-item <?php echo $is_active . $extra_class; ?>">
                        <span class="menu-icon"><?php icon($item['icon'], 18); ?></span>
                        <span class="menu-label"><?php echo $item['label']; ?></span>
                        <?php if ($is_admin): ?>
                            <span class="menu-badge admin-dot"></span>
                        <?php elseif (!empty($item['badge'])): ?>
                            <span class="menu-badge <?php echo strtolower($item['badge']); ?>"><?php echo $item['badge']; ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- XP Card with Avatar (non-admin) -->
        <?php if ($user_role !== 'admin'): ?>
        <div class="sidebar-footer">
            <div class="sidebar-xp-card">
                <div class="xp-avatar-ring" style="width: 48px; height: 48px; position: relative; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                    <svg class="xp-ring-svg" viewBox="0 0 36 36" style="position: absolute; top:0; left:0; width:100%; height:100%;">
                        <path class="xp-ring-bg"  d="M18 3 a15 15 0 0 1 0 30 a15 15 0 0 1 0 -30" style="fill: none; stroke: #E2E8F0; stroke-width: 2.5;"/>
                        <path class="xp-ring-fill" stroke-dasharray="<?php echo $dash_value; ?>,100"
                              d="M18 3 a15 15 0 0 1 0 30 a15 15 0 0 1 0 -30" style="fill: none; stroke: url(#xpGradient); stroke-width: 2.5; stroke-linecap: round;"/>
                    </svg>
                    <div class="xp-avatar-emoji" style="position: absolute; width: 34px; height: 34px; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#F5F3FF; border-radius:50%;">
                        <img src="<?php echo htmlspecialchars($sidebar_cls['image']); ?>" alt="<?php echo htmlspecialchars($sidebar_cls['name']); ?>" style="width:100%; height:100%; object-fit:cover; border-radius:50%;" title="<?php echo $avatar_emoji . ' ' . htmlspecialchars($sidebar_cls['name']); ?>">
                    </div>
                </div>

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
        <?php endif; ?>
    </div>
</nav>

<!-- Floating BG Orbs -->
<div class="floating-bg-elements">
    <div class="bg-orb orb-1"></div>
    <div class="bg-orb orb-2"></div>
</div>

<script>
(function() {
    var sidebar = document.getElementById('sidebar-island');

    function applyState(hidden) {
        if (hidden) {
            sidebar.classList.add('sidebar-hidden');
            document.body.classList.add('sidebar-hidden');
        } else {
            sidebar.classList.remove('sidebar-hidden');
            document.body.classList.remove('sidebar-hidden');
        }
    }

    var stored = localStorage.getItem('sidebar-collapsed');
    if (stored === null) {
        applyState(true);
        localStorage.setItem('sidebar-collapsed', 'true');
    } else if (stored === 'true') {
        applyState(true);
    }

    window.toggleSidebar = function() {
        var btn  = document.getElementById('sidebar-toggle');
        var icon = document.getElementById('toggle-icon');

        sidebar.classList.toggle('sidebar-hidden');
        document.body.classList.toggle('sidebar-hidden');
        if (btn) btn.classList.toggle('sidebar-hidden');

        var hidden = sidebar.classList.contains('sidebar-hidden');
        if (icon) icon.textContent = hidden ? '\u2630' : '\u2715';
        localStorage.setItem('sidebar-collapsed', hidden);
    };
})();
</script>
