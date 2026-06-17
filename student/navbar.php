<?php
// student/navbar.php - Student Floating Header
include_once 'sidebar.php';

$first_name   = explode(' ', $_SESSION['nama_lengkap'] ?? 'Student')[0];
$user_level   = $level ?? 1;
$user_xp      = $total_xp ?? 0;
$user_streak  = $streakDays ?? 0;
$notif_count = 0;
if (isset($db)) {
    $nc = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0");
    $nc->bindParam(':uid', $_SESSION['user_id']);
    $nc->execute();
    $notif_count = $nc->fetchColumn();
}
?>
<header class="header-floating">
    <div class="header-inner">
        <button id="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Navigation" aria-label="Toggle Navigation">
            <span id="toggle-icon">☰</span>
        </button>

        <div class="header-search">
            <div class="search-container">
                <span class="search-icon"><?php icon('search', 16); ?></span>
                <input type="text" id="global-search" placeholder="Cari kursus, materi, atau topik...">
                <kbd class="search-shortcut">Ctrl K</kbd>
            </div>
        </div>

        <div class="header-actions">
            <div class="header-stat-pill xp-pill" title="Total XP">
                <?php icon('zap', 14); ?>
                <span class="pill-value"><?php echo number_format($user_xp); ?> XP</span>
            </div>
            <div class="header-stat-pill streak-pill" title="Daily Streak">
                <?php icon('trending-up', 14); ?>
                <span class="pill-value"><?php echo $user_streak; ?> Day Streak</span>
            </div>
            <div class="notification-wrapper">
                <button class="header-btn notification-btn" title="Notifikasi" onclick="toggleNotifications(event)">
                    <?php icon('bell', 18); ?>
                    <span class="notification-badge" id="notif-badge" <?php echo $notif_count > 0 ? '' : 'style="display:none"'; ?>><?php echo $notif_count > 0 ? $notif_count : ''; ?></span>
                </button>
                <div class="notification-dropdown" id="notif-dropdown">
                    <div class="notif-header">
                        <h4>Notifikasi</h4>
                        <button onclick="markAllRead()" class="notif-mark-read">Tandai sudah dibaca</button>
                    </div>
                    <div class="notif-list" id="notif-list">
                        <div class="notif-loading">Memuat...</div>
                    </div>
                    <div class="notif-footer">
                        <span id="notif-count"></span>
                    </div>
                </div>
            </div>
            <div class="header-user-card">
                <div class="user-meta">
                    <span class="user-name"><?php echo htmlspecialchars($first_name); ?></span>
                    <span class="user-level">Level <?php echo $user_level; ?></span>
                </div>
                <div class="user-avatar-wrapper">
                    <?php if (!empty($avatar) && file_exists('../assets/uploads/avatars/' . $avatar)): ?>
                        <img src="../assets/uploads/avatars/<?php echo $avatar; ?>" alt="Avatar" class="user-avatar">
                    <?php else: ?>
                        <div class="user-avatar" style="background:linear-gradient(135deg,#3B82F6,#20C7B7);display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:1rem;">
                            <?php echo strtoupper(substr($_SESSION['nama_lengkap'] ?? 'U', 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div class="online-indicator"></div>
                </div>
            </div>
            <a href="../logout.php" class="header-btn logout-btn" title="Logout">
                <?php icon('log-out', 18); ?>
            </a>
        </div>
    </div>
<script>
function toggleNotifications(e) {
    e.stopPropagation();
    var dd = document.getElementById('notif-dropdown');
    var isOpen = dd.classList.contains('open');
    document.querySelectorAll('.notification-dropdown.open').forEach(function(d) { d.classList.remove('open'); });
    if (!isOpen) dd.classList.add('open');
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.notification-wrapper')) {
        document.querySelectorAll('.notification-dropdown.open').forEach(function(d) { d.classList.remove('open'); });
    }
});

function loadNotifications() {
    var list = document.getElementById('notif-list');
    list.innerHTML = '<div class="notif-loading">Memuat...</div>';
    fetch('../api/notifications.php')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.notifications.length > 0) {
                var html = '';
                data.notifications.forEach(function(n) {
                    var cls = n.is_read ? 'notif-item' : 'notif-item unread';
                    html += '<div class="' + cls + '" data-id="' + n.id + '">';
                    html += '<div class="notif-icon">' + (n.icon || '🔔') + '</div>';
                    html += '<div class="notif-body"><div class="notif-text">' + n.message + '</div>';
                    html += '<div class="notif-time">' + n.time_ago + '</div></div></div>';
                });
                list.innerHTML = html;
            } else {
                list.innerHTML = '<div class="notif-empty">Tidak ada notifikasi</div>';
            }
            var badge = document.getElementById('notif-badge');
            if (data.unread > 0) {
                badge.textContent = data.unread;
                badge.style.display = '';
            } else {
                badge.style.display = 'none';
            }
        })
        .catch(function() {
            list.innerHTML = '<div class="notif-error">Gagal memuat notifikasi</div>';
        });
}

function markAllRead() {
    fetch('../api/notifications.php?mark_read=all', { method: 'POST' })
        .then(function() {
            document.querySelectorAll('.notif-item.unread').forEach(function(item) { item.classList.remove('unread'); });
            document.getElementById('notif-badge').style.display = 'none';
        });
}

document.addEventListener('DOMContentLoaded', loadNotifications);
</script>
</header>
