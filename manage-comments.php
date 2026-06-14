<?php
require_once 'config/config.php';
requireRole(['admin']);
require_once 'includes/icons.php';
require_once 'includes/activity_log.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$message_type = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Sesi tidak valid (CSRF Token Error).';
        $message_type = 'error';
    } elseif ($_POST['action'] === 'delete') {
        $query = "DELETE FROM comments WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $_POST['id']);
        if ($stmt->execute()) {
            // Also delete replies
            $query_replies = "DELETE FROM comments WHERE parent_id = :id";
            $stmt_replies = $db->prepare($query_replies);
            $stmt_replies->bindParam(':id', $_POST['id']);
            $stmt_replies->execute();
            logActivity($db, $_SESSION['user_id'], 'comment_delete', 'Menghapus komentar ID: ' . $_POST['id']);
            $message = 'Komentar berhasil dihapus!';
            $message_type = 'success';
        } else {
            $message = 'Gagal menghapus komentar!';
            $message_type = 'error';
        }
    } elseif ($_POST['action'] === 'delete_all_user') {
        $query = "DELETE FROM comments WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $_POST['user_id']);
        $stmt->execute();
        logActivity($db, $_SESSION['user_id'], 'comment_delete_all', 'Menghapus semua komentar user ID: ' . $_POST['user_id']);
        $message = 'Semua komentar user berhasil dihapus!';
        $message_type = 'success';
    }
}

// Get filter
$filter_type = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Get all comments with user and lesson info
$query = "SELECT c.*, u.nama_lengkap, u.username, u.avatar as user_avatar, l.judul_lesson, co.judul_course
          FROM comments c
          JOIN users u ON c.user_id = u.id
          JOIN lessons l ON c.lesson_id = l.id
          JOIN courses co ON l.course_id = co.id
          WHERE 1=1";

if ($filter_type === 'replies') {
    $query .= " AND c.parent_id IS NOT NULL";
} elseif ($filter_type === 'top') {
    $query .= " AND c.parent_id IS NULL";
}

if (!empty($search)) {
    $query .= " AND (c.content LIKE :search OR u.nama_lengkap LIKE :search2)";
}

$query .= " ORDER BY c.created_at DESC LIMIT 100";

$stmt = $db->prepare($query);
if (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bindParam(':search', $search_param);
    $stmt->bindParam(':search2', $search_param);
}
$stmt->execute();

$comments = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $comments[] = $row;
}

// Stats
$total_comments = $db->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$total_users_commented = $db->query("SELECT COUNT(DISTINCT user_id) FROM comments")->fetchColumn();

$page_title = 'Moderasi Komentar';
$page_css = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'pages/admin.css'];
$body_class = getThemeClass();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once 'includes/head.php'; ?>
    <style>
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
        .stat-card .stat-value { font-size:1.5rem; font-weight:700; color:var(--brand); }
        .stat-card .stat-label { font-size:0.8125rem; color:var(--text-muted); margin-top:0.25rem; }
        .filter-bar { display:flex; gap:0.75rem; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; }
        .filter-bar a { padding:0.5rem 1rem; border-radius:var(--radius-md); font-size:0.8125rem; font-weight:600; text-decoration:none; color:var(--text-muted); background:var(--bg-subtle); border:1px solid var(--border-default); transition:all var(--transition-fast); }
        .filter-bar a.active { color:var(--brand); border-color:var(--brand); background:rgba(59,130,246,0.1); }
        .filter-bar a:hover { color:var(--text-primary); }
        .comment-content { max-width:400px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .search-box { display:flex; gap:0.5rem; margin-left:auto; }
        .search-box input { padding:0.5rem 0.75rem; border:1px solid var(--border-default); border-radius:var(--radius-md); background:var(--bg-subtle); color:var(--text-primary); font-size:0.8125rem; }
        .search-box input:focus { outline:none; border-color:var(--brand); }
        .search-box button { padding:0.5rem 1rem; background:var(--brand); color:#fff; border:none; border-radius:var(--radius-md); cursor:pointer; font-size:0.8125rem; }
        .reply-indicator { font-size:0.75rem; color:var(--text-muted); background:var(--bg-subtle); padding:0.15rem 0.5rem; border-radius:4px; }
    </style>
</head>
<body class="dashboard-layout <?php echo $body_class; ?>">
    <?php include_once 'navbar.php'; ?>
    <div class="dashboard-container">
        <div class="dashboard-content">
            <div class="admin-header">
                <div>
                    <h1>Moderasi Komentar</h1>
                    <p class="admin-subtitle">Kelola komentar dari seluruh lesson</p>
                </div>
            </div>

            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($total_comments); ?></div>
                    <div class="stat-label">Total Komentar</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($total_users_commented); ?></div>
                    <div class="stat-label">User Berkomentar</div>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="filter-bar">
                <a href="manage-comments.php?filter=all" class="<?php echo $filter_type === 'all' ? 'active' : ''; ?>">Semua</a>
                <a href="manage-comments.php?filter=top" class="<?php echo $filter_type === 'top' ? 'active' : ''; ?>">Komentar Utama</a>
                <a href="manage-comments.php?filter=replies" class="<?php echo $filter_type === 'replies' ? 'active' : ''; ?>">Balasan</a>
                <form method="GET" class="search-box">
                    <input type="hidden" name="filter" value="<?php echo $filter_type; ?>">
                    <input type="text" name="search" placeholder="Cari komentar atau user..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Cari</button>
                </form>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 style="margin:0;color:var(--text-primary);font-size:1.125rem;font-weight:700;">Daftar Komentar</h2>
                    <span style="color:var(--text-muted);font-size:0.875rem;"><?php echo count($comments); ?> komentar</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width:160px;">User</th>
                                <th>Komentar</th>
                                <th style="width:180px;">Lesson / Course</th>
                                <th style="width:140px;">Tanggal</th>
                                <th style="width:100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($comments)): ?>
                                <tr><td colspan="5" style="text-align:center;padding:3rem;color:var(--text-muted);">Tidak ada komentar ditemukan.</td></tr>
                            <?php else: ?>
                                <?php foreach ($comments as $c): ?>
                                    <tr>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                                <div style="width:32px;height:32px;border-radius:50%;background:var(--brand-gradient,linear-gradient(135deg,#3B82F6,#20C7B7));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.75rem;flex-shrink:0;">
                                                    <?php echo strtoupper(substr($c['nama_lengkap'],0,1)); ?>
                                                </div>
                                                <div>
                                                    <div style="font-weight:600;font-size:0.8125rem;"><?php echo htmlspecialchars($c['nama_lengkap']); ?></div>
                                                    <div style="font-size:0.75rem;color:var(--text-muted);">@<?php echo htmlspecialchars($c['username']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($c['parent_id']): ?>
                                                <span class="reply-indicator">Balasan</span>
                                            <?php endif; ?>
                                            <div class="comment-content" title="<?php echo htmlspecialchars($c['content']); ?>">
                                                <?php echo htmlspecialchars(substr($c['content'], 0, 120)) . (strlen($c['content']) > 120 ? '...' : ''); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-size:0.8125rem;font-weight:500;"><?php echo htmlspecialchars($c['judul_lesson']); ?></div>
                                            <div style="font-size:0.75rem;color:var(--text-muted);"><?php echo htmlspecialchars($c['judul_course']); ?></div>
                                        </td>
                                        <td style="font-size:0.8125rem;color:var(--text-muted);"><?php echo formatDateTime($c['created_at']); ?></td>
                                        <td>
                                            <div style="display:flex;gap:0.35rem;flex-wrap:wrap;">
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus komentar ini?');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                                    <button type="submit" class="admin-action-btn delete" style="font-size:0.75rem;"><?php icon('trash',14); ?></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/loading.php'; ?>
    <?php include 'includes/toast.php'; ?>
    <script src="assets/js/navbar.js"></script>
</body>
</html>
