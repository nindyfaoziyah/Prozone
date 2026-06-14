<?php
require_once 'config/config.php';
requireRole(['admin']);
require_once 'includes/icons.php';
require_once 'includes/activity_log.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$message_type = '';

// Backup directory
$backup_dir = __DIR__ . '/backups';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

// Handle backup creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Sesi tidak valid (CSRF Token Error).';
        $message_type = 'error';
    } elseif ($_POST['action'] === 'backup') {
        try {
            $db_name = $db->query("SELECT DATABASE()")->fetchColumn();
            $filename = 'backup-' . $db_name . '-' . date('Y-m-d-His') . '.sql';
            $filepath = $backup_dir . '/' . $filename;

            // Get all tables
            $tables = [];
            $stmt_t = $db->query("SHOW TABLES");
            while ($row = $stmt_t->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            $output = "-- Prozone Database Backup\n";
            $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $output .= "-- Database: $db_name\n\n";
            $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $output .= "SET AUTOCOMMIT = 0;\n";
            $output .= "START TRANSACTION;\n\n";

            foreach ($tables as $table) {
                // Drop table if exists
                $output .= "DROP TABLE IF EXISTS `$table`;\n";

                // Create table
                $stmt_c = $db->query("SHOW CREATE TABLE `$table`");
                $create = $stmt_c->fetch(PDO::FETCH_ASSOC);
                $output .= $create['Create Table'] . ";\n\n";

                // Get data
                $stmt_d = $db->query("SELECT * FROM `$table`");
                $rows = $stmt_d->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($rows)) {
                    $columns = array_keys($rows[0]);
                    $col_list = '`' . implode('`, `', $columns) . '`';
                    
                    foreach ($rows as $row) {
                        $values = [];
                        foreach ($columns as $col) {
                            $val = $row[$col];
                            if ($val === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = $db->quote($val);
                            }
                        }
                        $output .= "INSERT INTO `$table` ($col_list) VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $output .= "\n";
                }
            }

            $output .= "COMMIT;\n";

            file_put_contents($filepath, $output);

            logActivity($db, $_SESSION['user_id'], 'backup_create', "Membuat backup database: $filename");
            $message = "Backup berhasil! File: $filename";
            $message_type = 'success';

        } catch (Exception $e) {
            $message = 'Gagal membuat backup: ' . $e->getMessage();
            $message_type = 'error';
        }

    } elseif ($_POST['action'] === 'restore') {
        $backup_file = $_POST['backup_file'] ?? '';
        $filepath = $backup_dir . '/' . basename($backup_file);

        if (!file_exists($filepath)) {
            $message = 'File backup tidak ditemukan!';
            $message_type = 'error';
        } else {
            try {
                $sql = file_get_contents($filepath);
                $db->exec($sql);
                logActivity($db, $_SESSION['user_id'], 'backup_restore', "Restore dari backup: $backup_file");
                $message = "Database berhasil direstore dari: $backup_file";
                $message_type = 'success';
            } catch (Exception $e) {
                $message = 'Gagal restore: ' . $e->getMessage();
                $message_type = 'error';
            }
        }

    } elseif ($_POST['action'] === 'delete_backup') {
        $backup_file = $_POST['backup_file'] ?? '';
        $filepath = $backup_dir . '/' . basename($backup_file);
        if (file_exists($filepath)) {
            unlink($filepath);
            logActivity($db, $_SESSION['user_id'], 'backup_delete', "Menghapus backup: $backup_file");
            $message = "Backup berhasil dihapus!";
            $message_type = 'success';
        }
    } elseif ($_POST['action'] === 'download_backup') {
        $backup_file = $_POST['backup_file'] ?? '';
        $filepath = $backup_dir . '/' . basename($backup_file);
        if (file_exists($filepath)) {
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . basename($backup_file) . '"');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit;
        }
    }
}

// Get existing backups
$backups = [];
$files = glob($backup_dir . '/*.sql');
if ($files) {
    foreach ($files as $file) {
        $backups[] = [
            'filename' => basename($file),
            'size' => filesize($file),
            'date' => date('Y-m-d H:i:s', filemtime($file)),
        ];
    }
}
rsort($backups); // newest first

// Get database size
$db_name = $db->query("SELECT DATABASE()")->fetchColumn();
$db_size = $db->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb FROM information_schema.tables WHERE table_schema = :db", [':db' => $db_name])->fetchColumn();

$page_title = 'Backup Database';
$page_css = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'pages/admin.css'];
$body_class = getThemeClass();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once 'includes/head.php'; ?>
    <style>
        .admin-card { background:var(--bg-surface); border:1px solid var(--border-default); border-radius:var(--radius-lg); padding:1.5rem; box-shadow:var(--shadow-md); margin-bottom:1.5rem; }
        .admin-card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid var(--border-default); }
        .admin-table { width:100%; border-collapse:collapse; font-size:0.875rem; }
        .admin-table th { padding:0.75rem; text-align:left; color:var(--text-muted); font-weight:600; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; border-bottom:1px solid var(--border-default); }
        .admin-table td { padding:0.75rem; color:var(--text-primary); border-bottom:1px solid var(--border-default); vertical-align:middle; }
        .admin-table tr:last-child td { border-bottom:none; }
        .admin-table tr:hover td { background:var(--bg-hover); }
        .alert { padding:1rem 1.5rem; border-radius:var(--radius-md); margin-bottom:1.5rem; font-size:0.875rem; font-weight:500; }
        .alert-success { background:rgba(16,185,129,0.12); border-left:4px solid #10b981; color:#10b981; }
        .alert-error { background:rgba(239,68,68,0.12); border-left:4px solid #ef4444; color:#ef4444; }
        .alert-warning { background:rgba(245,158,11,0.12); border-left:4px solid #f59e0b; color:#f59e0b; }
        .stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:1rem; margin-bottom:1.5rem; }
        .stat-card { background:var(--bg-surface); border:1px solid var(--border-default); border-radius:var(--radius-lg); padding:1.25rem; text-align:center; }
        .stat-card .stat-value { font-size:1.5rem; font-weight:700; }
        .stat-card .stat-label { font-size:0.8125rem; color:var(--text-muted); margin-top:0.25rem; }
        .backup-actions { display:flex; gap:1rem; flex-wrap:wrap; }
        .btn-primary { padding:0.75rem 2rem; background:var(--brand); color:#fff; border:none; border-radius:var(--radius-md); font-weight:600; cursor:pointer; font-size:0.9rem; display:inline-flex; align-items:center; gap:0.5rem; }
        .btn-primary:hover { opacity:0.9; }
        .btn-danger { padding:0.4rem 0.8rem; background:rgba(239,68,68,0.15); color:#ef4444; border:1px solid rgba(239,68,68,0.3); border-radius:var(--radius-md); font-size:0.8125rem; cursor:pointer; }
        .btn-danger:hover { background:rgba(239,68,68,0.25); }
        .btn-download { padding:0.4rem 0.8rem; background:rgba(59,130,246,0.15); color:#3B82F6; border:1px solid rgba(59,130,246,0.3); border-radius:var(--radius-md); font-size:0.8125rem; cursor:pointer; text-decoration:none; display:inline-block; }
        .btn-download:hover { background:rgba(59,130,246,0.25); }
        .warning-box { background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.2); border-radius:var(--radius-md); padding:1rem; margin-bottom:1.5rem; font-size:0.875rem; color:var(--text-secondary); }
    </style>
</head>
<body class="dashboard-layout <?php echo $body_class; ?>">
    <?php include_once 'navbar.php'; ?>
    <div class="dashboard-container">
        <div class="dashboard-content">
            <div class="admin-header">
                <div>
                    <h1>Backup Database</h1>
                    <p class="admin-subtitle">Backup dan restore database Prozone</p>
                </div>
            </div>

            <div class="stat-grid">
                <div class="stat-card"><div class="stat-value" style="color:var(--brand);"><?php echo htmlspecialchars($db_name); ?></div><div class="stat-label">Nama Database</div></div>
                <div class="stat-card"><div class="stat-value" style="color:#10b981;"><?php echo $db_size; ?> MB</div><div class="stat-label">Ukuran Database</div></div>
                <div class="stat-card"><div class="stat-value"><?php echo count($backups); ?></div><div class="stat-label">Total Backup</div></div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="warning-box">
                <strong>Peringatan:</strong> Proses restore akan MENIMPA semua data yang ada. Pastikan Anda telah membuat backup terbaru sebelum melakukan restore.
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 style="margin:0;font-size:1.125rem;font-weight:700;">Buat Backup Baru</h2>
                </div>
                <form method="POST" class="backup-actions">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="action" value="backup">
                    <button type="submit" class="btn-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg> Backup Sekarang
                    </button>
                </form>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 style="margin:0;font-size:1.125rem;font-weight:700;">Daftar Backup</h2>
                    <span style="color:var(--text-muted);font-size:0.875rem;"><?php echo count($backups); ?> file</span>
                </div>
                <?php if (empty($backups)): ?>
                    <div style="text-align:center;padding:3rem;color:var(--text-muted);">
                        <p>Belum ada file backup. Buat backup terlebih dahulu.</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Nama File</th>
                                    <th>Ukuran</th>
                                    <th>Tanggal</th>
                                    <th style="width:200px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($backups as $b): ?>
                                    <tr>
                                        <td><code style="background:var(--bg-subtle);padding:0.25rem 0.5rem;border-radius:4px;font-size:0.8125rem;"><?php echo htmlspecialchars($b['filename']); ?></code></td>
                                        <td><?php echo number_format($b['size'] / 1024, 1); ?> KB</td>
                                        <td style="font-size:0.8125rem;color:var(--text-muted);"><?php echo $b['date']; ?></td>
                                        <td>
                                            <div style="display:flex;gap:0.35rem;">
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                    <input type="hidden" name="action" value="download_backup">
                                                    <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($b['filename']); ?>">
                                                    <button type="submit" class="btn-download"><?php icon('download',14); ?> Download</button>
                                                </form>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin akan me-restore backup ini? Semua data akan tertimpa!');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                    <input type="hidden" name="action" value="restore">
                                                    <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($b['filename']); ?>">
                                                    <button type="submit" class="admin-action-btn edit" style="font-size:0.75rem;">Restore</button>
                                                </form>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus file backup ini?');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                    <input type="hidden" name="action" value="delete_backup">
                                                    <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($b['filename']); ?>">
                                                    <button type="submit" class="btn-danger"><?php icon('trash',14); ?></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
