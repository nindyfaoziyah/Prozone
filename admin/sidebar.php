<?php
// admin/sidebar.php - Admin Island Navigation (Beautified)
// This is an INDEPENDENT copy for admin role only.

$current_page = basename($_SERVER['PHP_SELF']);
$first_name   = explode(' ', $_SESSION['nama_lengkap'] ?? 'Admin')[0];
$user_level   = $level ?? 1;
$user_xp      = $total_xp ?? 0;

// XP needed for next level (simple formula: level * 500)
$xp_next      = $user_level * 500;
$xp_prev      = ($user_level - 1) * 500;
$xp_progress  = $xp_next - $xp_prev;
$xp_current   = $user_xp - $xp_prev;
$xp_pct       = $xp_progress > 0 ? min(100, round(($xp_current / $xp_progress) * 100)) : 0;
$dash_value   = $xp_pct;

// Fetch RPG class from DB
require_once '../includes/rpg_system.php';
$sidebar_cls = getClassData('code-warrior');
if (isset($db) && isset($_SESSION['user_id'])) {
    $stmt_sb = $db->prepare("SELECT character_class FROM users WHERE id = :uid");
    $stmt_sb->bindParam(':uid', $_SESSION['user_id']);
    $stmt_sb->execute();
    if ($sb_user = $stmt_sb->fetch(PDO::FETCH_ASSOC)) {
        $sidebar_cls = getClassData($sb_user['character_class'] ?? 'code-warrior');
    }
}
$avatar_emoji = $sidebar_cls['badge'];

// Admin menu items - Clean & organized
$menu_items = [
    ['label' => 'Dashboard',         'icon' => 'grid',           'link' => 'dashboard.php'],
    ['label' => '',                  'icon' => '',               'link' => '', 'type' => 'divider'],
    ['label' => 'Konten',            'icon' => '',               'link' => '', 'type' => 'label'],
    ['label' => 'Kelola Kursus',     'icon' => 'book',           'link' => 'manage-courses.php'],
    ['label' => 'Lessons',           'icon' => 'file-text',      'link' => 'manage-lessons.php'],
    ['label' => 'Kategori Kursus',   'icon' => 'tag',            'link' => 'manage-categories.php'],
    ['label' => 'Achievements',      'icon' => 'award',          'link' => 'manage-achievements.php'],
    ['label' => 'Sertifikat',        'icon' => 'certificate',    'link' => 'manage-certificates.php'],
    ['label' => 'Komentar',          'icon' => 'message-circle', 'link' => 'manage-comments.php'],
    ['label' => '',                  'icon' => '',               'link' => '', 'type' => 'divider'],
    ['label' => 'Pengguna',          'icon' => '',               'link' => '', 'type' => 'label'],
    ['label' => 'Kelola User',       'icon' => 'users',          'link' => 'users.php'],
    ['label' => 'Kelola Clan',       'icon' => 'zap',            'link' => 'manage-clans.php'],
    ['label' => 'Enrollments',       'icon' => 'clipboard',      'link' => 'manage-enrollments.php'],
    ['label' => '',                  'icon' => '',               'link' => '', 'type' => 'divider'],
    ['label' => 'Sistem',            'icon' => '',               'link' => '', 'type' => 'label'],
    ['label' => 'Broadcast',         'icon' => 'send',           'link' => 'manage-notifications.php'],
    ['label' => 'Log Aktivitas',     'icon' => 'activity',       'link' => 'manage-logs.php'],
    ['label' => 'Export Data',       'icon' => 'download',       'link' => 'export.php'],
    ['label' => 'Backup DB',         'icon' => 'shield',         'link' => 'manage-backup.php'],
    ['label' => 'Analytics',         'icon' => 'bar-chart',      'link' => 'admin_analytics.php'],
    ['label' => '',                  'icon' => '',               'link' => '', 'type' => 'divider'],
    ['label' => 'Settings',          'icon' => 'settings',       'link' => 'pengaturan.php'],
];
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
				<span class="sidebar-brand-text">Prozone</span>
			</a>
		</div>

		<!-- Admin Role Badge -->
        <div class="admin-role-badge">
            <span class="admin-role-icon">🛡️</span>
            <span class="admin-role-text">Admin Panel</span>
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
                    $is_active = ($item['link'] === basename($_SERVER['PHP_SELF'])) ? 'active' : '';
                    ?>
                    <a href="<?php echo $item['link']; ?>" class="menu-item <?php echo $is_active; ?>">
                        <span class="menu-icon"><?php icon($item['icon'], 18); ?></span>
                        <span class="menu-label"><?php echo $item['label']; ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- XP Card (admin version - simplified) -->
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
    if (stored === 'true') {
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
