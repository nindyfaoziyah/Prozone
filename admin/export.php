<?php
require_once '../config/config.php';
requireRole(['admin']);
require_once '../includes/icons.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$message_type = '';

// Handle export
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    $filename = '';
    $headers = [];
    $data = [];

    switch ($type) {
        case 'users':
            $filename = 'users-' . date('Y-m-d') . '.csv';
            $headers = ['ID', 'Username', 'Nama Lengkap', 'Email', 'Role', 'Level', 'Total XP', 'Terdaftar'];
            $stmt = $db->query("SELECT id, username, nama_lengkap, email, role, level, total_xp, created_at FROM users ORDER BY id");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
            break;

        case 'courses':
            $filename = 'courses-' . date('Y-m-d') . '.csv';
            $headers = ['ID', 'Kode', 'Judul', 'Level', 'Harga', 'Total Lessons', 'Total Students', 'Rating', 'Dibuat'];
            $stmt = $db->query("SELECT id, kode_course, judul_course, level, harga, total_lessons, total_students, rating, created_at FROM courses ORDER BY id");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
            break;

        case 'enrollments':
            $filename = 'enrollments-' . date('Y-m-d') . '.csv';
            $headers = ['ID', 'User ID', 'Nama User', 'Course ID', 'Judul Course', 'Progress', 'Status', 'Tanggal Daftar'];
            $stmt = $db->query("SELECT e.id, e.user_id, u.nama_lengkap, e.course_id, c.judul_course, e.progress_percent, e.status, e.enrolled_at FROM enrollments e JOIN users u ON e.user_id = u.id JOIN courses c ON e.course_id = c.id ORDER BY e.id");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
            break;

        case 'certificates':
            $filename = 'certificates-' . date('Y-m-d') . '.csv';
            $headers = ['ID', 'User ID', 'Nama User', 'Course ID', 'Judul Course', 'Kode Sertifikat', 'Diterbitkan'];
            $stmt = $db->query("SELECT cert.id, cert.user_id, u.nama_lengkap, cert.course_id, c.judul_course, cert.certificate_code, cert.issued_at FROM certificates cert JOIN users u ON cert.user_id = u.id JOIN courses c ON cert.course_id = c.id ORDER BY cert.id");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
            break;

        case 'achievements':
            $filename = 'user-achievements-' . date('Y-m-d') . '.csv';
            $headers = ['ID', 'User ID', 'Nama User', 'Achievement', 'Kode', 'XP Reward', 'Earned At'];
            $stmt = $db->query("SELECT ua.id, ua.user_id, u.nama_lengkap, a.nama_achievement, a.kode_achievement, a.xp_reward, ua.earned_at FROM user_achievements ua JOIN users u ON ua.user_id = u.id JOIN achievements a ON ua.achievement_id = a.id ORDER BY ua.earned_at DESC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
            break;

        case 'comments':
            $filename = 'comments-' . date('Y-m-d') . '.csv';
            $headers = ['ID', 'User', 'Lesson', 'Course', 'Content', 'Tanggal'];
            $stmt = $db->query("SELECT c.id, u.nama_lengkap, l.judul_lesson, co.judul_course, c.content, c.created_at FROM comments c JOIN users u ON c.user_id = u.id JOIN lessons l ON c.lesson_id = l.id JOIN courses co ON l.course_id = co.id ORDER BY c.id");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
            break;

        default:
            die('Invalid export type');
    }

    // Send CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for UTF-8
    fputcsv($output, $headers);
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// Stats for display
$stats = [
    'users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'courses' => $db->query("SELECT COUNT(*) FROM courses")->fetchColumn(),
    'enrollments' => $db->query("SELECT COUNT(*) FROM enrollments")->fetchColumn(),
    'certificates' => $db->query("SELECT COUNT(*) FROM certificates")->fetchColumn(),
    'achievements' => $db->query("SELECT COUNT(*) FROM user_achievements")->fetchColumn(),
    'comments' => $db->query("SELECT COUNT(*) FROM comments")->fetchColumn(),
];

$page_title = 'Export Data';
$page_css = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'admin.css', 'shared.css'];
$body_class = getThemeClass();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/head.php'; ?>
    <style>
        .export-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:1rem; }
        .export-card { background:var(--bg-surface); border:1px solid var(--border-default); border-radius:var(--radius-lg); padding:1.5rem; text-decoration:none; color:var(--text-primary); transition:all var(--transition-fast); display:flex; align-items:center; gap:1rem; }
        .export-card:hover { border-color:var(--brand); box-shadow:var(--shadow-md); transform:translateY(-1px); }
        .export-icon { width:48px; height:48px; border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .export-info h3 { margin:0 0 0.25rem 0; font-size:0.95rem; }
        .export-info p { margin:0; font-size:0.8125rem; color:var(--text-muted); }
        .export-count { font-size:0.75rem; color:var(--brand); font-weight:600; margin-top:0.15rem; }
    </style>
</head>
<body class="dashboard-layout <?php echo $body_class; ?>">
    <?php include_once 'navbar.php'; ?>
    <div class="dashboard-container">
        <div class="dashboard-content">
            <div class="admin-header">
                <div>
                    <h1><span class="header-icon green">??</span> Export Data</h1>
                    <p class="admin-subtitle">Download data platform dalam format CSV</p>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 style="margin:0;font-size:1.125rem;font-weight:700;">Pilih Data untuk Diexport</h2>
                    <span style="color:var(--text-muted);font-size:0.8125rem;">Format: CSV (dapat dibuka di Excel)</span>
                </div>
                <div class="export-grid">
                    <a href="?export=users" class="export-card">
                        <div class="export-icon" style="background:rgba(59,130,246,0.15);"><?php icon('users', 24, '#3B82F6'); ?></div>
                        <div class="export-info">
                            <h3>Users</h3>
                            <p>Data seluruh user</p>
                            <div class="export-count"><?php echo number_format($stats['users']); ?> records</div>
                        </div>
                    </a>
                    <a href="?export=courses" class="export-card">
                        <div class="export-icon" style="background:rgba(16,185,129,0.15);"><?php icon('book', 24, '#10b981'); ?></div>
                        <div class="export-info">
                            <h3>Courses</h3>
                            <p>Data seluruh kursus</p>
                            <div class="export-count"><?php echo number_format($stats['courses']); ?> records</div>
                        </div>
                    </a>
                    <a href="?export=enrollments" class="export-card">
                        <div class="export-icon" style="background:rgba(245,158,11,0.15);"><?php icon('clipboard', 24, '#F59E0B'); ?></div>
                        <div class="export-info">
                            <h3>Enrollments</h3>
                            <p>Data pendaftaran kursus</p>
                            <div class="export-count"><?php echo number_format($stats['enrollments']); ?> records</div>
                        </div>
                    </a>
                    <a href="?export=certificates" class="export-card">
                        <div class="export-icon" style="background:rgba(139,92,246,0.15);"><?php icon('certificate', 24, '#8B5CF6'); ?></div>
                        <div class="export-info">
                            <h3>Sertifikat</h3>
                            <p>Data sertifikat terbit</p>
                            <div class="export-count"><?php echo number_format($stats['certificates']); ?> records</div>
                        </div>
                    </a>
                    <a href="?export=achievements" class="export-card">
                        <div class="export-icon" style="background:rgba(236,72,153,0.15);"><?php icon('award', 24, '#EC4899'); ?></div>
                        <div class="export-info">
                            <h3>User Achievements</h3>
                            <p>Riwayat pencapaian user</p>
                            <div class="export-count"><?php echo number_format($stats['achievements']); ?> records</div>
                        </div>
                    </a>
                    <a href="?export=comments" class="export-card">
                        <div class="export-icon" style="background:rgba(6,182,212,0.15);"><?php icon('message-circle', 24, '#06B6D4'); ?></div>
                        <div class="export-info">
                            <h3>Komentar</h3>
                            <p>Data komentar lesson</p>
                            <div class="export-count"><?php echo number_format($stats['comments']); ?> records</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <?php include '../includes/loading.php'; ?>
    <?php include '../includes/toast.php'; ?>
    <script src="../assets/js/navbar.js"></script>
</body>
</html>
