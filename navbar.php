<?php
// navbar.php - Floating Header, includes sidebar island
include_once 'sidebar.php';

// Get user data for header display
$first_name = explode(' ', $_SESSION['nama_lengkap'] ?? 'User')[0];
$user_level = $level ?? 1;
$user_xp    = $total_xp ?? 0;
$user_streak = $streakDays ?? 0;
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
        <!-- Toggle Sidebar -->
        <button id="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Navigation" aria-label="Toggle Navigation">
            <span id="toggle-icon">☰</span>
        </button>

        <!-- Floating Search Bar -->
        <div class="header-search">
            <div class="search-container">
                <span class="search-icon"><?php icon('search', 16); ?></span>
                <input type="text" id="global-search" placeholder="Cari kursus, materi, atau topik...">
                <kbd class="search-shortcut">Ctrl K</kbd>
            </div>
        </div>

        <!-- Status Indicators -->
        <div class="header-actions">
            <?php if ($user_role === 'admin'): ?>
            <?php
            // Online users count
            $online_count = 0;
            $online_stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE is_online = 1 AND last_seen >= NOW() - INTERVAL 5 MINUTE");
            $online_stmt->execute();
            $online_count = $online_stmt->fetchColumn();
            ?>
            <div class="header-stat-pill online-pill" title="Pengguna Online">
                <?php icon('users', 14); ?>
                <span class="pill-value"><?php echo $online_count; ?> Online</span>
            </div>
            <?php else: ?>
            <div class="header-stat-pill xp-pill" title="Total XP">
                <?php icon('zap', 14); ?>
                <span class="pill-value"><?php echo number_format($user_xp); ?> XP</span>
            </div>
            <div class="header-stat-pill streak-pill" title="Daily Streak">
                <?php icon('trending-up', 14); ?>
                <span class="pill-value"><?php echo $user_streak; ?> Day Streak</span>
            </div>
            <?php endif; ?>
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
                    <span class="user-level"><?php echo $user_role === 'admin' ? 'Admin' : 'Level ' . $user_level; ?></span>
                </div>
                <div class="user-avatar-wrapper">
                    <?php if (!empty($avatar) && file_exists('assets/uploads/avatars/' . $avatar)): ?>
                        <img src="assets/uploads/avatars/<?php echo $avatar; ?>" alt="Avatar" class="user-avatar">
                    <?php else: ?>
                        <div class="user-avatar" style="background:linear-gradient(135deg,#3B82F6,#20C7B7);display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:1rem;">
                            <?php echo strtoupper(substr($_SESSION['nama_lengkap'] ?? 'U', 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div class="online-indicator"></div>
                </div>
            </div>
            <a href="logout.php" class="header-btn" title="Logout" style="color:#EF4444;border-color:#FEE2E2;">
                <?php icon('log-out', 18); ?>
            </a>
        </div>
    </div>
<script>
function toggleNotifications(e) {
    e.stopPropagation();
    var dd = document.getElementById('notif-dropdown');
    var open = dd.classList.contains('open');
    document.querySelectorAll('.notification-dropdown.open').forEach(function(d) { d.classList.remove('open'); });
    if (!open) {
        dd.classList.add('open');
        loadNotifications();
    }
}

function loadNotifications() {
    var list = document.getElementById('notif-list');
    list.innerHTML = '<div class="notif-loading">Memuat...</div>';
    fetch('api/notifications.php')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.success || !res.data || res.data.length === 0) {
                list.innerHTML = '<div class="notif-empty">Belum ada notifikasi</div>';
                document.getElementById('notif-count').textContent = '';
                return;
            }
            var html = '';
            var unread = 0;
            res.data.forEach(function(n) {
                if (!n.is_read) unread++;
                var cls = n.is_read ? 'notif-item' : 'notif-item unread';
                var icon = n.type === 'achievement' ? '\u2B50' : n.type === 'course' ? '\uD83D\uDCD6' : n.type === 'clan' ? '\uD83C\uDF1F' : '\uD83D\uDD14';
                var link = n.link ? " onclick=\"location.href='" + n.link + "'\"" : '';
                html += '<div class="' + cls + '" data-id="' + n.id + '"' + link + ' onclick="markRead(' + n.id + ', this)">' +
                    '<div class="notif-icon ' + n.type + '">' + icon + '</div>' +
                    '<div class="notif-body"><p>' + n.message + '</p>' +
                    '<span class="notif-time">' + n.created_at_formatted + '</span></div></div>';
            });
            list.innerHTML = html;
            document.getElementById('notif-count').textContent = unread + ' belum dibaca';
            updateBadge(unread);
        })
        .catch(function() {
            list.innerHTML = '<div class="notif-loading">Gagal memuat notifikasi</div>';
        });
}

function markRead(id, el) {
    if (el.classList.contains('unread')) {
        el.classList.remove('unread');
        fetch('api/notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'mark_read', id: id })
        }).then(function(r) { return r.json(); }).then(function(res) {
            if (res.success) updateBadge();
        });
    }
}

function markAllRead() {
    fetch('api/notifications.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'mark_read' })
    }).then(function(r) { return r.json(); }).then(function(res) {
        if (res.success) {
            document.querySelectorAll('#notif-list .unread').forEach(function(el) { el.classList.remove('unread'); });
            document.getElementById('notif-count').textContent = '0 belum dibaca';
            updateBadge(0);
        }
    });
}

function updateBadge(count) {
    var badge = document.getElementById('notif-badge');
    if (count === undefined) {
        fetch('api/notifications.php?action=count')
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    var c = parseInt(res.count);
                    badge.textContent = c > 0 ? c : '';
                    badge.style.display = c > 0 ? 'flex' : 'none';
                }
            });
    } else {
        badge.textContent = count > 0 ? count : '';
        badge.style.display = count > 0 ? 'flex' : 'none';
    }
}

document.addEventListener('click', function(e) {
    var dd = document.getElementById('notif-dropdown');
    if (dd.classList.contains('open') && !dd.closest('.notification-wrapper').contains(e.target)) {
        dd.classList.remove('open');
    }
});
</script>
</header>
