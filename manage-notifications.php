<?php
require_once 'config/config.php';
requireRole(['admin']);
require_once 'includes/icons.php';
require_once 'includes/activity_log.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$message_type = '';

// Handle broadcast
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Sesi tidak valid (CSRF Token Error).';
        $message_type = 'error';
    } elseif ($_POST['action'] === 'broadcast') {
        $type = sanitizeInput($_POST['type'] ?? 'system');
        $message_text = sanitizeInput($_POST['message']);
        $link = sanitizeInput($_POST['link'] ?? '');
        $target = $_POST['target'] ?? 'all'; // 'all', 'students', 'admins'

        // Get target users
        $query_users = "SELECT id FROM users WHERE 1=1";
        if ($target === 'students') {
            $query_users .= " AND role = 'student'";
        } elseif ($target === 'admins') {
            $query_users .= " AND role = 'admin'";
        }
        $stmt_users = $db->query($query_users);
        $user_ids = [];
        while ($row = $stmt_users->fetch(PDO::FETCH_ASSOC)) {
            $user_ids[] = $row['id'];
        }

        if (empty($user_ids)) {
            $message = 'Tidak ada user yang sesuai dengan target.';
            $message_type = 'error';
        } else {
            $insert_query = "INSERT INTO notifications (user_id, type, message, link) VALUES (:user_id, :type, :message, :link)";
            $stmt_insert = $db->prepare($insert_query);

            $count = 0;
            foreach ($user_ids as $uid) {
                $stmt_insert->bindParam(':user_id', $uid);
                $stmt_insert->bindParam(':type', $type);
                $stmt_insert->bindParam(':message', $message_text);
                $stmt_insert->bindParam(':link', $link);
                if ($stmt_insert->execute()) {
                    $count++;
                }
            }

            logActivity($db, $_SESSION['user_id'], 'notification_broadcast', "Broadcast notifikasi '$type' ke $count user (target: $target)");
            $message = "Notifikasi berhasil dikirim ke $count user!";
            $message_type = 'success';
        }
    } elseif ($_POST['action'] === 'broadcast_all') {
        $type = sanitizeInput($_POST['type'] ?? 'system');
        $message_text = sanitizeInput($_POST['message']);
        $link = sanitizeInput($_POST['link'] ?? '');

        // Use single-query approach for performance
        $insert_query = "INSERT INTO notifications (user_id, type, message, link) SELECT id, :type, :message, :link FROM users";
        $stmt = $db->prepare($insert_query);
        // We need to do it in batches or use the loop approach
        // For simplicity, use loop
        $stmt_users = $db->query("SELECT id FROM users");
        $user_ids = [];
        while ($row = $stmt_users->fetch(PDO::FETCH_ASSOC)) {
            $user_ids[] = $row['id'];
        }

        if (empty($user_ids)) {
            $message = 'Tidak ada user.';
            $message_type = 'error';
        } else {
            $insert_stmt = $db->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (:user_id, :type, :message, :link)");
            $count = 0;
            foreach ($user_ids as $uid) {
                $insert_stmt->bindParam(':user_id', $uid);
                $insert_stmt->bindParam(':type', $type);
                $insert_stmt->bindParam(':message', $message_text);
                $insert_stmt->bindParam(':link', $link);
                if ($insert_stmt->execute()) {
                    $count++;
                }
            }

            logActivity($db, $_SESSION['user_id'], 'notification_broadcast_all', "Broadcast notifikasi '$type' ke semua user ($count user)");
            $message = "Notifikasi broadcast berhasil dikirim ke $count user!";
            $message_type = 'success';
        }
    } elseif ($_POST['action'] === 'clear_all') {
        $target_clear = $_POST['clear_target'] ?? 'all';
        if ($target_clear === 'all') {
            $db->exec("DELETE FROM notifications");
            logActivity($db, $_SESSION['user_id'], 'notification_clear', 'Menghapus semua notifikasi');
            $message = 'Semua notifikasi berhasil dibersihkan!';
        } elseif ($target_clear === 'read') {
            $db->exec("DELETE FROM notifications WHERE is_read = 1");
            logActivity($db, $_SESSION['user_id'], 'notification_clear_read', 'Menghapus notifikasi yang sudah dibaca');
            $message = 'Notifikasi yang sudah dibaca berhasil dibersihkan!';
        } elseif ($target_clear === 'unread') {
            $db->exec("DELETE FROM notifications WHERE is_read = 0");
            logActivity($db, $_SESSION['user_id'], 'notification_clear_unread', 'Menghapus notifikasi yang belum dibaca');
            $message = 'Notifikasi yang belum dibaca berhasil dibersihkan!';
        }
        $message_type = 'success';
    }
}

// Get stats
$total_notifications = $db->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
$unread_notifications = $db->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0")->fetchColumn();
$unique_users_notified = $db->query("SELECT COUNT(DISTINCT user_id) FROM notifications")->fetchColumn();

// Get recent notifications
$recent_notifications = [];
$stmt_recent = $db->query("SELECT n.*, u.nama_lengkap FROM notifications n JOIN users u ON n.user_id = u.id ORDER BY n.created_at DESC LIMIT 50");
while ($row = $stmt_recent->fetch(PDO::FETCH_ASSOC)) {
    $recent_notifications[] = $row;
}

// Get notification types
$type_counts = [];
$stmt_types = $db->query("SELECT type, COUNT(*) as total FROM notifications GROUP BY type ORDER BY total DESC");
while ($row = $stmt_types->fetch(PDO::FETCH_ASSOC)) {
    $type_counts[] = $row;
}

$page_title = 'Broadcast Notification';
$page_css = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'pages/admin.css'];
$body_class = getThemeClass();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once 'includes/head.php'; ?>
    <style>
        .form-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1rem; margin-bottom:1rem; }
        .form-group { margin-bottom:1rem; }
        .form-group label { display:block; margin-bottom:0.5rem; color:var(--text-secondary); font-weight:600; font-size:0.875rem; }
        .form-group input, .form-group select, .form-group textarea { width:100%; padding:0.75rem 1rem; border:1px solid var(--border-default); border-radius:var(--radius-md); background:var(--bg-subtle); color:var(--text-primary); font-size:0.875rem; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline:none; border-color:var(--brand); box-shadow:var(--shadow-primary); background:var(--bg-surface); }
        .admin-card { background:var(--bg-surface); border:1px solid var(--border-default); border-radius:var(--radius-lg); padding:1.5rem; box-shadow:var(--shadow-md); }
        .admin-card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid var(--border-default); }
        .admin-table { width:100%; border-collapse:collapse; font-size:0.875rem; }
        .admin-table th { padding:0.75rem; text-align:left; color:var(--text-muted); font-weight:600; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; border-bottom:1px solid var(--border-default); }
        .admin-table td { padding:0.75rem; color:var(--text-primary); border-bottom:1px solid var(--border-default); vertical-align:middle; }
        .admin-table tr:last-child td { border-bottom:none; }
        .admin-table tr:hover td { background:var(--bg-hover); }
        .alert { padding:1rem 1.5rem; border-radius:var(--radius-md); margin-bottom:1.5rem; font-size:0.875rem; font-weight:500; }
        .alert-success { background:rgba(16,185,129,0.12); border-left:4px solid #10b981; color:#10b981; }
        .alert-error { background:rgba(239,68,68,0.12); border-left:4px solid #ef4444; color:#ef4444; }
        .stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:1rem; margin-bottom:1.5rem; }
        .stat-card { background:var(--bg-surface); border:1px solid var(--border-default); border-radius:var(--radius-lg); padding:1.25rem; text-align:center; }
        .stat-card .stat-value { font-size:1.5rem; font-weight:700; }
        .stat-card .stat-label { font-size:0.8125rem; color:var(--text-muted); margin-top:0.25rem; }
        .stat-card .stat-value.unread { color:#ef4444; }
        .stat-card .stat-value.total { color:var(--brand); }
        .broadcast-form { max-width:700px; }
        .message-preview { background:var(--bg-subtle); border:1px solid var(--border-default); border-radius:var(--radius-md); padding:1rem; margin-top:0.5rem; font-size:0.875rem; }
        .type-badge { display:inline-block; padding:0.2rem 0.6rem; border-radius:4px; font-size:0.75rem; font-weight:600; }
        .type-badge.system { background:rgba(59,130,246,0.15); color:#3B82F6; }
        .type-badge.achievement { background:rgba(245,158,11,0.15); color:#F59E0B; }
        .type-badge.course { background:rgba(16,185,129,0.15); color:#10b981; }
        .type-badge.clan { background:rgba(139,92,246,0.15); color:#8B5CF6; }
        .clear-actions { display:flex; gap:0.5rem; flex-wrap:wrap; }
        .tab-nav { display:flex; gap:0.5rem; margin-bottom:1.5rem; border-bottom:1px solid var(--border-default); padding-bottom:0; }
        .tab-nav button { padding:0.75rem 1.25rem; background:none; border:none; color:var(--text-muted); font-weight:600; font-size:0.875rem; cursor:pointer; border-bottom:2px solid transparent; transition:all var(--transition-fast); }
        .tab-nav button.active { color:var(--brand); border-bottom-color:var(--brand); }
    </style>
</head>
<body class="dashboard-layout <?php echo $body_class; ?>">
    <?php include_once 'navbar.php'; ?>
    <div class="dashboard-container">
        <div class="dashboard-content">
            <div class="admin-header">
                <div>
                    <h1>Broadcast Notifikasi</h1>
                    <p class="admin-subtitle">Kirim notifikasi ke seluruh user atau kelompok tertentu</p>
                </div>
            </div>

            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-value total"><?php echo number_format($total_notifications); ?></div>
                    <div class="stat-label">Total Notifikasi</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value unread"><?php echo number_format($unread_notifications); ?></div>
                    <div class="stat-label">Belum Dibaca</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value total"><?php echo number_format($unique_users_notified); ?></div>
                    <div class="stat-label">User Pernah Diberi Notif</div>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="tab-nav">
                <button class="active" onclick="switchTab('broadcast', this)">Kirim Broadcast</button>
                <button onclick="switchTab('history', this)">Riwayat</button>
                <button onclick="switchTab('manage', this)">Kelola</button>
            </div>

            <div id="tab-broadcast">
                <div class="admin-card broadcast-form">
                    <div class="admin-card-header">
                        <h2 style="margin:0;color:var(--text-primary);font-size:1.125rem;font-weight:700;">Kirim Notifikasi Broadcast</h2>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <input type="hidden" name="action" value="broadcast">

                        <div class="form-group">
                            <label>Target User</label>
                            <select name="target" required>
                                <option value="all">Semua User</option>
                                <option value="students">Student Saja</option>
                                <option value="admins">Admin Saja</option>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Tipe Notifikasi</label>
                                <select name="type" required>
                                    <option value="system">System</option>
                                    <option value="achievement">Achievement</option>
                                    <option value="course">Course</option>
                                    <option value="clan">Clan</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Link (opsional)</label>
                                <input type="text" name="link" placeholder="Contoh: courses.php">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Pesan Notifikasi</label>
                            <textarea name="message" id="broadcastMessage" rows="3" required placeholder="Tulis pesan notifikasi..." oninput="updatePreview(this.value)"></textarea>
                            <div class="message-preview" id="messagePreview">
                                <strong>Pratinjau:</strong>
                                <div id="previewText" style="margin-top:0.25rem;color:var(--text-primary);"></div>
                            </div>
                        </div>

                        <div style="margin-top:1.5rem;">
                            <button type="submit" class="admin-action-btn" style="padding:0.75rem 2rem;font-size:0.9rem;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:0.5rem;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg> Kirim Broadcast
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="tab-history" style="display:none;">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 style="margin:0;color:var(--text-primary);font-size:1.125rem;font-weight:700;">Riwayat Notifikasi</h2>
                        <span style="color:var(--text-muted);font-size:0.875rem;">50 notifikasi terbaru</span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Tipe</th>
                                    <th>Pesan</th>
                                    <th>Link</th>
                                    <th style="text-align:center;">Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_notifications)): ?>
                                    <tr><td colspan="6" style="text-align:center;padding:3rem;color:var(--text-muted);">Belum ada notifikasi terkirim.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recent_notifications as $n): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($n['nama_lengkap']); ?></strong></td>
                                            <td><span class="type-badge <?php echo $n['type']; ?>"><?php echo ucfirst($n['type']); ?></span></td>
                                            <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($n['message']); ?></td>
                                            <td style="font-size:0.8125rem;color:var(--text-muted);"><?php echo $n['link'] ? htmlspecialchars($n['link']) : '-'; ?></td>
                                            <td style="text-align:center;"><?php echo $n['is_read'] ? '✅' : '🔴'; ?></td>
                                            <td style="font-size:0.8125rem;color:var(--text-muted);"><?php echo formatDateTime($n['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="tab-manage" style="display:none;">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 style="margin:0;color:var(--text-primary);font-size:1.125rem;font-weight:700;">Kelola Notifikasi</h2>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
                        <form method="POST" onsubmit="return confirm('Hapus SEMUA notifikasi?');">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="hidden" name="action" value="clear_all">
                            <input type="hidden" name="clear_target" value="all">
                            <button type="submit" class="admin-action-btn delete" style="width:100%;padding:1rem;font-size:0.875rem;">
                                <?php icon('trash',16); ?> Hapus Semua Notifikasi
                            </button>
                        </form>
                        <form method="POST" onsubmit="return confirm('Hapus notifikasi yang sudah dibaca?');">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="hidden" name="action" value="clear_all">
                            <input type="hidden" name="clear_target" value="read">
                            <button type="submit" class="admin-action-btn edit" style="width:100%;padding:1rem;font-size:0.875rem;">
                                <?php icon('check',16); ?> Hapus yang sudah Dibaca
                            </button>
                        </form>
                        <form method="POST" onsubmit="return confirm('Hapus notifikasi yang BELUM dibaca?');">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="hidden" name="action" value="clear_all">
                            <input type="hidden" name="clear_target" value="unread">
                            <button type="submit" class="admin-action-btn" style="width:100%;padding:1rem;font-size:0.875rem;background:rgba(245,158,11,0.15);color:#F59E0B;border-color:rgba(245,158,11,0.3);">
                                <?php icon('alert-circle',16); ?> Hapus yang Belum Dibaca
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab, btn) {
            document.querySelectorAll('.tab-nav button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('tab-broadcast').style.display = tab === 'broadcast' ? 'block' : 'none';
            document.getElementById('tab-history').style.display = tab === 'history' ? 'block' : 'none';
            document.getElementById('tab-manage').style.display = tab === 'manage' ? 'block' : 'none';
        }

        function updatePreview(val) {
            document.getElementById('previewText').textContent = val || '(pesan kosong)';
        }
    </script>

    <?php include 'includes/loading.php'; ?>
    <?php include 'includes/toast.php'; ?>
    <script src="assets/js/navbar.js"></script>
</body>
</html>
