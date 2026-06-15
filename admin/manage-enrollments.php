<?php
require_once '../config/config.php';
requireRole(['admin']);
require_once '../includes/icons.php';
require_once '../includes/activity_log.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$message_type = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Sesi tidak valid (CSRF Token Error).';
        $message_type = 'error';
    } elseif (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'enroll':
                $user_id = (int)$_POST['user_id'];
                $course_id = (int)$_POST['course_id'];
                // Check if already enrolled
                $check = $db->prepare("SELECT id FROM enrollments WHERE user_id = :uid AND course_id = :cid");
                $check->bindParam(':uid', $user_id);
                $check->bindParam(':cid', $course_id);
                $check->execute();
                if ($check->rowCount() > 0) {
                    $message = 'User sudah terdaftar di kursus ini!';
                    $message_type = 'error';
                } else {
                    $stmt = $db->prepare("INSERT INTO enrollments (user_id, course_id, status, enrolled_at) VALUES (:uid, :cid, 'enrolled', NOW())");
                    $stmt->bindParam(':uid', $user_id);
                    $stmt->bindParam(':cid', $course_id);
                    if ($stmt->execute()) {
                        logActivity($db, $_SESSION['user_id'], 'enrollment_create', "Manual enroll user #$user_id ke course #$course_id");
                        $message = 'User berhasil didaftarkan!';
                        $message_type = 'success';
                    } else {
                        $message = 'Gagal mendaftarkan user!';
                        $message_type = 'error';
                    }
                }
                break;

            case 'update_status':
                $id = (int)$_POST['id'];
                $status = $_POST['status'];
                $valid_statuses = ['enrolled', 'completed', 'dropped'];
                if (!in_array($status, $valid_statuses)) break;
                $stmt = $db->prepare("UPDATE enrollments SET status = :status WHERE id = :id");
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':id', $id);
                if ($stmt->execute()) {
                    logActivity($db, $_SESSION['user_id'], 'enrollment_update', "Update enrollment #$id ke status $status");
                    $message = 'Status enrollment berhasil diperbarui!';
                    $message_type = 'success';
                }
                break;

            case 'delete':
                $id = (int)$_POST['id'];
                $stmt = $db->prepare("DELETE FROM enrollments WHERE id = :id");
                $stmt->bindParam(':id', $id);
                if ($stmt->execute()) {
                    logActivity($db, $_SESSION['user_id'], 'enrollment_delete', "Hapus enrollment #$id");
                    $message = 'Enrollment berhasil dihapus!';
                    $message_type = 'success';
                }
                break;
        }
    }
}

// Filters
$filter_course = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$filter_status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 30;
$offset = ($page - 1) * $limit;

// Get courses for filter
$courses = [];
$stmt_c = $db->query("SELECT id, judul_course FROM courses ORDER BY judul_course");
while ($row = $stmt_c->fetch(PDO::FETCH_ASSOC)) {
    $courses[] = $row;
}

// Get users for enroll form
$users = [];
$stmt_u = $db->query("SELECT id, nama_lengkap, username FROM users WHERE role = 'student' ORDER BY nama_lengkap");
while ($row = $stmt_u->fetch(PDO::FETCH_ASSOC)) {
    $users[] = $row;
}

// Build query
$query = "SELECT e.*, c.judul_course, u.nama_lengkap, u.username, u.avatar FROM enrollments e JOIN courses c ON e.course_id = c.id JOIN users u ON e.user_id = u.id WHERE 1=1";
$count_query = "SELECT COUNT(*) FROM enrollments e WHERE 1=1";
$params = [];

if ($filter_course > 0) {
    $query .= " AND e.course_id = :cid";
    $count_query .= " AND e.course_id = :cid";
    $params[':cid'] = $filter_course;
}
if ($filter_status) {
    $query .= " AND e.status = :status";
    $count_query .= " AND e.status = :status";
    $params[':status'] = $filter_status;
}
if ($search) {
    $query .= " AND (u.nama_lengkap LIKE :search OR u.username LIKE :search2 OR c.judul_course LIKE :search3)";
    $count_query .= " AND (u.nama_lengkap LIKE :search OR u.username LIKE :search2 OR c.judul_course LIKE :search3)";
    $s = "%$search%";
    $params[':search'] = $s;
    $params[':search2'] = $s;
    $params[':search3'] = $s;
}

$query .= " ORDER BY e.enrolled_at DESC LIMIT $limit OFFSET $offset";

// Get total count
$stmt_count = $db->prepare($count_query);
foreach ($params as $k => $v) { $stmt_count->bindValue($k, $v); }
$stmt_count->execute();
$total_enrollments = $stmt_count->fetchColumn();
$total_pages = ceil($total_enrollments / $limit);

// Get enrollments
$stmt = $db->prepare($query);
foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
$stmt->execute();
$enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$stats = [];
$stats['total'] = $db->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
$stats['active'] = $db->query("SELECT COUNT(*) FROM enrollments WHERE status = 'enrolled'")->fetchColumn();
$stats['completed'] = $db->query("SELECT COUNT(*) FROM enrollments WHERE status = 'completed'")->fetchColumn();
$stats['dropped'] = $db->query("SELECT COUNT(*) FROM enrollments WHERE status = 'dropped'")->fetchColumn();

$page_title = 'Manage Enrollments';
$page_css = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'admin.css', 'shared.css'];
$body_class = getThemeClass();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/head.php'; ?>
    <style>
        .filter-bar select, .filter-bar input { padding:0.5rem 1rem; border:1px solid var(--border-default); border-radius:var(--radius-md); background:var(--bg-subtle); color:var(--text-primary); font-size:0.875rem; }
        .filter-bar select:focus, .filter-bar input:focus { outline:none; border-color:var(--brand); }
        .filter-bar a { padding:0.4rem 0.8rem; border-radius:var(--radius-md); font-size:0.8125rem; text-decoration:none; color:var(--text-muted); border:1px solid var(--border-default); }
        .filter-bar a.active { color:var(--brand); border-color:var(--brand); background:rgba(59,130,246,0.1); }
        .badge { display:inline-block; padding:0.2rem 0.6rem; border-radius:999px; font-size:0.75rem; font-weight:600; }
        .badge.enrolled { background:rgba(59,130,246,0.15); color:#3B82F6; }
        .badge.completed { background:rgba(16,185,129,0.15); color:#10b981; }
        .badge.dropped { background:rgba(239,68,68,0.15); color:#ef4444; }
        .modal-overlay { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.6); z-index:2000; overflow-y:auto; padding:2rem; }
        .modal-content { max-width:500px; margin:2rem auto; background:var(--bg-surface); border:1px solid var(--border-default); border-radius:var(--radius-lg); padding:2rem; box-shadow:var(--shadow-lg); }
        .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; padding-bottom:1rem; border-bottom:1px solid var(--border-default); }
        .modal-header h2 { margin:0; color:var(--text-primary); }
        .modal-close { background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer; }
        .form-group select { width:100%; padding:0.75rem 1rem; border:1px solid var(--border-default); border-radius:var(--radius-md); background:var(--bg-subtle); color:var(--text-primary); font-size:0.875rem; }
        </style>
</head>
<body class="dashboard-layout <?php echo $body_class; ?>">
    <?php include_once 'navbar.php'; ?>
    <div class="dashboard-container">
        <div class="dashboard-content">
            <div class="admin-header">
                <div>
                    <h1>Manage Enrollments</h1>
                    <p class="admin-subtitle">Kelola pendaftaran user ke kursus</p>
                </div>
                <div class="admin-header-actions">
                    <button onclick="openEnrollModal()" class="admin-action-btn primary" style="padding:0.6rem 1.25rem;font-size:0.85rem;">+ Tambah Enrollment</button>
                </div>
            </div>

            <div class="stat-grid">
                <div class="stat-card"><div class="stat-value" style="color:var(--brand);"><?php echo $stats['total']; ?></div><div class="stat-label">Total</div></div>
                <div class="stat-card"><div class="stat-value" style="color:#3B82F6;"><?php echo $stats['active']; ?></div><div class="stat-label">Aktif</div></div>
                <div class="stat-card"><div class="stat-value" style="color:#10b981;"><?php echo $stats['completed']; ?></div><div class="stat-label">Selesai</div></div>
                <div class="stat-card"><div class="stat-value" style="color:#ef4444;"><?php echo $stats['dropped']; ?></div><div class="stat-label">Dropped</div></div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="filter-bar">
                <a href="manage-enrollments.php" class="<?php echo !$filter_status ? 'active' : ''; ?>">Semua</a>
                <a href="manage-enrollments.php?status=enrolled" class="<?php echo $filter_status === 'enrolled' ? 'active' : ''; ?>">Aktif</a>
                <a href="manage-enrollments.php?status=completed" class="<?php echo $filter_status === 'completed' ? 'active' : ''; ?>">Selesai</a>
                <a href="manage-enrollments.php?status=dropped" class="<?php echo $filter_status === 'dropped' ? 'active' : ''; ?>">Dropped</a>
                <form method="GET" style="display:flex;gap:0.5rem;margin-left:auto;">
                    <select name="course_id" onchange="this.form.submit()">
                        <option value="0">Semua Kursus</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $filter_course == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['judul_course']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="search" placeholder="Cari user/kursus..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="admin-action-btn" style="padding:0.4rem 1rem;font-size:0.8125rem;">Cari</button>
                </form>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 style="margin:0;font-size:1.125rem;font-weight:700;">Daftar Enrollment</h2>
                    <span style="color:var(--text-muted);font-size:0.875rem;"><?php echo $total_enrollments; ?> total</span>
                </div>
                <div style="overflow-x:auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Kursus</th>
                                <th style="text-align:center;">Progress</th>
                                <th style="text-align:center;">Status</th>
                                <th>Tanggal Daftar</th>
                                <th style="width:160px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($enrollments)): ?>
                                <tr><td colspan="6" style="text-align:center;padding:3rem;color:var(--text-muted);">Tidak ada enrollment ditemukan.</td></tr>
                            <?php else: ?>
                                <?php foreach ($enrollments as $e): ?>
                                    <tr>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#3B82F6,#20C7B7);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:0.75rem;flex-shrink:0;">
                                                    <?php echo strtoupper(substr($e['nama_lengkap'],0,1)); ?>
                                                </div>
                                                <div>
                                                    <div style="font-weight:600;font-size:0.8125rem;"><?php echo htmlspecialchars($e['nama_lengkap']); ?></div>
                                                    <div style="font-size:0.75rem;color:var(--text-muted);">@<?php echo htmlspecialchars($e['username']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($e['judul_course']); ?></strong></td>
                                        <td style="text-align:center;font-weight:600;"><?php echo number_format($e['progress_percent'], 0); ?>%</td>
                                        <td style="text-align:center;"><span class="badge <?php echo $e['status']; ?>"><?php echo ucfirst($e['status']); ?></span></td>
                                        <td style="font-size:0.8125rem;color:var(--text-muted);"><?php echo formatDateTime($e['enrolled_at']); ?></td>
                                        <td>
                                            <div style="display:flex;gap:0.35rem;">
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
                                                    <select name="status" onchange="this.form.submit()" style="padding:0.3rem 0.5rem;font-size:0.75rem;border:1px solid var(--border-default);border-radius:var(--radius-md);background:var(--bg-subtle);color:var(--text-primary);">
                                                        <option value="enrolled" <?php echo $e['status']==='enrolled'?'selected':''; ?>>Aktif</option>
                                                        <option value="completed" <?php echo $e['status']==='completed'?'selected':''; ?>>Selesai</option>
                                                        <option value="dropped" <?php echo $e['status']==='dropped'?'selected':''; ?>>Dropped</option>
                                                    </select>
                                                </form>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus enrollment ini?');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
                                                    <button type="submit" class="admin-action-btn delete" style="font-size:0.75rem;padding:0.3rem 0.6rem;"><?php icon('trash',14); ?></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&course_id=<?php echo $filter_course; ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search); ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Enroll Modal -->
            <div id="enrollModal" class="modal-overlay" style="display:none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Tambah Enrollment</h2>
                        <button onclick="closeModal()" class="modal-close">&times;</button>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <input type="hidden" name="action" value="enroll">
                        <div class="form-group">
                            <label>Pilih User</label>
                            <select name="user_id" required>
                                <option value="">-- Pilih User --</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['nama_lengkap']); ?> (@<?php echo htmlspecialchars($u['username']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Pilih Kursus</label>
                            <select name="course_id" required>
                                <option value="">-- Pilih Kursus --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['judul_course']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="margin-top:1.5rem;display:flex;gap:1rem;justify-content:flex-end;">
                            <button type="button" onclick="closeModal()" style="padding:0.6rem 1.25rem;background:var(--bg-subtle);color:var(--text-secondary);border:1px solid var(--border-default);border-radius:var(--radius-md);cursor:pointer;">Batal</button>
                            <button type="submit" class="admin-action-btn primary" style="padding:0.6rem 1.5rem;">Daftarkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openEnrollModal() { document.getElementById('enrollModal').style.display = 'block'; }
        function closeModal() { document.getElementById('enrollModal').style.display = 'none'; }
        document.getElementById('enrollModal')?.addEventListener('click', function(e) { if (e.target === this) closeModal(); });
    </script>

    <?php include 'footer.php'; ?>
    <?php include '../includes/loading.php'; ?>
    <?php include '../includes/toast.php'; ?>
    <script src="../assets/js/navbar.js"></script>
</body>
</html>
