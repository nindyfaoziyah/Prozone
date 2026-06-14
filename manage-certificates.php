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
        $query = "DELETE FROM certificates WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $_POST['id']);
        if ($stmt->execute()) {
            logActivity($db, $_SESSION['user_id'], 'certificate_delete', 'Menghapus sertifikat ID: ' . $_POST['id']);
            $message = 'Sertifikat berhasil dihapus!';
            $message_type = 'success';
        } else {
            $message = 'Gagal menghapus sertifikat!';
            $message_type = 'error';
        }
    }
}

// Get filter
$filter_course = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Get all courses for filter
$courses = [];
$stmt_c = $db->query("SELECT id, judul_course FROM courses ORDER BY judul_course");
while ($row = $stmt_c->fetch(PDO::FETCH_ASSOC)) {
    $courses[] = $row;
}

// Get certificates with user and course info
$query = "SELECT cert.*, u.nama_lengkap, u.username, c.judul_course
          FROM certificates cert
          JOIN users u ON cert.user_id = u.id
          JOIN courses c ON cert.course_id = c.id
          WHERE 1=1";

$params = [];
if ($filter_course > 0) {
    $query .= " AND cert.course_id = :course_id";
    $params[':course_id'] = $filter_course;
}
$query .= " ORDER BY cert.issued_at DESC LIMIT 200";

$stmt = $db->prepare($query);
foreach ($params as $key => $val) {
    $stmt->bindParam($key, $val);
}
$stmt->execute();

$certificates = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $certificates[] = $row;
}

// Stats
$total_certificates = $db->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
$total_users_certified = $db->query("SELECT COUNT(DISTINCT user_id) FROM certificates")->fetchColumn();
$total_courses_with_cert = $db->query("SELECT COUNT(DISTINCT course_id) FROM certificates")->fetchColumn();

$page_title = 'Manage Sertifikat';
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
        .filter-bar select { padding:0.5rem 1rem; border:1px solid var(--border-default); border-radius:var(--radius-md); background:var(--bg-subtle); color:var(--text-primary); font-size:0.875rem; }
        .filter-bar select:focus { outline:none; border-color:var(--brand); }
        .cert-code { font-family:monospace; font-size:0.8125rem; background:var(--bg-subtle); padding:0.25rem 0.5rem; border-radius:4px; }
    </style>
</head>
<body class="dashboard-layout <?php echo $body_class; ?>">
    <?php include_once 'navbar.php'; ?>
    <div class="dashboard-container">
        <div class="dashboard-content">
            <div class="admin-header">
                <div>
                    <h1>Manage Sertifikat</h1>
                    <p class="admin-subtitle">Lihat dan kelola sertifikat yang telah diterbitkan</p>
                </div>
            </div>

            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($total_certificates); ?></div>
                    <div class="stat-label">Total Sertifikat</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($total_users_certified); ?></div>
                    <div class="stat-label">User Bersertifikat</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($total_courses_with_cert); ?></div>
                    <div class="stat-label">Kursus dengan Sertifikat</div>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="filter-bar">
                <form method="GET" style="display:flex;gap:0.5rem;align-items:center;">
                    <select name="course_id" onchange="this.form.submit()">
                        <option value="0">Semua Kursus</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $filter_course == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['judul_course']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($filter_course > 0): ?>
                        <a href="manage-certificates.php" style="font-size:0.8125rem;color:var(--text-muted);text-decoration:none;">&times; Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 style="margin:0;color:var(--text-primary);font-size:1.125rem;font-weight:700;">Daftar Sertifikat</h2>
                    <span style="color:var(--text-muted);font-size:0.875rem;"><?php echo count($certificates); ?> sertifikat</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Kursus</th>
                                <th>Kode Sertifikat</th>
                                <th>Diterbitkan</th>
                                <th style="width:100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($certificates)): ?>
                                <tr><td colspan="5" style="text-align:center;padding:3rem;color:var(--text-muted);">Belum ada sertifikat diterbitkan.</td></tr>
                            <?php else: ?>
                                <?php foreach ($certificates as $cert): ?>
                                    <tr>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                                <div style="width:32px;height:32px;border-radius:50%;background:var(--brand-gradient,linear-gradient(135deg,#3B82F6,#20C7B7));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.75rem;">
                                                    <?php echo strtoupper(substr($cert['nama_lengkap'],0,1)); ?>
                                                </div>
                                                <div>
                                                    <div style="font-weight:600;font-size:0.8125rem;"><?php echo htmlspecialchars($cert['nama_lengkap']); ?></div>
                                                    <div style="font-size:0.75rem;color:var(--text-muted);">@<?php echo htmlspecialchars($cert['username']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($cert['judul_course']); ?></strong></td>
                                        <td><span class="cert-code"><?php echo htmlspecialchars($cert['certificate_code']); ?></span></td>
                                        <td style="font-size:0.8125rem;color:var(--text-muted);"><?php echo formatDateTime($cert['issued_at']); ?></td>
                                        <td>
                                            <div style="display:flex;gap:0.35rem;">
                                                <a href="certificate-print.php?code=<?php echo urlencode($cert['certificate_code']); ?>" target="_blank" class="admin-action-btn edit" style="font-size:0.75rem;text-decoration:none;">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:0.25rem;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg> Lihat
                                                </a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus sertifikat ini?');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $cert['id']; ?>">
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
