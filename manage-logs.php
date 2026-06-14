<?php
require_once 'config/config.php';
requireRole(['admin']);
require_once 'includes/icons.php';
require_once 'includes/activity_log.php';

$database = new Database();
$db = $database->getConnection();

// Filters
$action_filter = $_GET['action'] ?? '';
$user_filter = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

$logs = getActivityLog($db, $limit, $offset, $action_filter, $user_filter);
$stats = getActivityStats($db);

// Get total for pagination
$count_query = "SELECT COUNT(*) FROM activity_log al WHERE 1=1";
$count_params = [];
if ($action_filter) {
    $count_query .= " AND al.action = :action";
    $count_params[':action'] = $action_filter;
}
if ($user_filter > 0) {
    $count_query .= " AND al.user_id = :uid";
    $count_params[':uid'] = $user_filter;
}
$stmt_count = $db->prepare($count_query);
foreach ($count_params as $k => $v) { $stmt_count->bindValue($k, $v); }
$stmt_count->execute();
$total_logs = $stmt_count->fetchColumn();
$total_pages = ceil($total_logs / $limit);

// Get all admins for filter
$admins = $db->query("SELECT id, nama_lengkap FROM users WHERE role = 'admin' ORDER BY nama_lengkap")->fetchAll(PDO::FETCH_ASSOC);

// Get distinct actions for filter
$actions = $db->query("SELECT DISTINCT action FROM activity_log ORDER BY action")->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Log Aktivitas';
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
        .stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:1rem; margin-bottom:1.5rem; }
        .stat-card { background:var(--bg-surface); border:1px solid var(--border-default); border-radius:var(--radius-lg); padding:1.25rem; text-align:center; }
        .stat-card .stat-value { font-size:1.5rem; font-weight:700; color:var(--brand); }
        .stat-card .stat-label { font-size:0.8125rem; color:var(--text-muted); margin-top:0.25rem; }
        .filter-bar { display:flex; gap:0.75rem; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; }
        .filter-bar select { padding:0.5rem 1rem; border:1px solid var(--border-default); border-radius:var(--radius-md); background:var(--bg-subtle); color:var(--text-primary); font-size:0.875rem; }
        .filter-bar select:focus { outline:none; border-color:var(--brand); }
        .action-badge { display:inline-block; padding:0.2rem 0.6rem; border-radius:4px; font-size:0.75rem; font-weight:600; background:var(--bg-subtle); color:var(--text-secondary); }
        .pagination { display:flex; gap:0.5rem; justify-content:center; margin-top:1.5rem; }
        .pagination a, .pagination span { padding:0.5rem 0.9rem; border-radius:var(--radius-md); font-size:0.8125rem; text-decoration:none; color:var(--text-muted); border:1px solid var(--border-default); }
        .pagination a.active { color:var(--brand); border-color:var(--brand); background:rgba(59,130,246,0.1); }
        .pagination a:hover { border-color:var(--brand); }
        .ip-address { font-size:0.75rem; color:var(--text-muted); font-family:monospace; }
    </style>
</head>
<body class="dashboard-layout <?php echo $body_class; ?>">
    <?php include_once 'navbar.php'; ?>
    <div class="dashboard-container">
        <div class="dashboard-content">
            <div class="admin-header">
                <div>
                    <h1>Log Aktivitas</h1>
                    <p class="admin-subtitle">Riwayat aktivitas admin di platform</p>
                </div>
                <a href="manage-logs.php" style="color:var(--text-muted);font-size:0.875rem;text-decoration:none;">&larr; Reset Filter</a>
            </div>

            <div class="stat-grid">
                <div class="stat-card"><div class="stat-value"><?php echo number_format($stats['total']); ?></div><div class="stat-label">Total Aktivitas</div></div>
                <div class="stat-card"><div class="stat-value" style="color:#F59E0B;"><?php echo $stats['today']; ?></div><div class="stat-label">Hari Ini</div></div>
                <div class="stat-card"><div class="stat-value"><?php echo $stats['unique_actions']; ?></div><div class="stat-label">Tipe Aksi</div></div>
            </div>

            <div class="filter-bar">
                <form method="GET" style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                    <select name="action" onchange="this.form.submit()">
                        <option value="">Semua Aksi</option>
                        <?php foreach ($actions as $a): ?>
                            <option value="<?php echo htmlspecialchars($a['action']); ?>" <?php echo $action_filter === $a['action'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($a['action']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="user_id" onchange="this.form.submit()">
                        <option value="0">Semua Admin</option>
                        <?php foreach ($admins as $a): ?>
                            <option value="<?php echo $a['id']; ?>" <?php echo $user_filter == $a['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($a['nama_lengkap']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <div style="margin-left:auto;font-size:0.8125rem;color:var(--text-muted);">
                    Top aksi:
                    <?php foreach (array_slice($stats['top_actions'], 0, 5) as $ta): ?>
                        <span class="action-badge"><?php echo htmlspecialchars($ta['action']); ?> (<?php echo $ta['total']; ?>)</span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 style="margin:0;font-size:1.125rem;font-weight:700;">Riwayat Aktivitas</h2>
                    <span style="color:var(--text-muted);font-size:0.875rem;"><?php echo number_format($total_logs); ?> total</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th style="width:140px;">Admin</th>
                                <th style="width:130px;">Aksi</th>
                                <th>Deskripsi</th>
                                <th style="width:140px;">IP Address</th>
                                <th style="width:140px;">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr><td colspan="5" style="text-align:center;padding:3rem;color:var(--text-muted);">Belum ada aktivitas tercatat.</td></tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                                <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#3B82F6,#20C7B7);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.7rem;flex-shrink:0;">
                                                    <?php echo strtoupper(substr($log['nama_lengkap'],0,1)); ?>
                                                </div>
                                                <span style="font-size:0.8125rem;"><?php echo htmlspecialchars($log['nama_lengkap']); ?></span>
                                            </div>
                                        </td>
                                        <td><span class="action-badge"><?php echo htmlspecialchars($log['action']); ?></span></td>
                                        <td style="max-width:300px;"><?php echo htmlspecialchars($log['description']); ?></td>
                                        <td><span class="ip-address"><?php echo htmlspecialchars($log['ip_address'] ?: '-'); ?></span></td>
                                        <td style="font-size:0.8125rem;color:var(--text-muted);"><?php echo formatDateTime($log['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&action=<?php echo urlencode($action_filter); ?>&user_id=<?php echo $user_filter; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/loading.php'; ?>
    <?php include 'includes/toast.php'; ?>
    <script src="assets/js/navbar.js"></script>
</body>
</html>
