<?php
require_once 'config/config.php';
requireRole(['admin']);
require_once 'includes/icons.php';

require_once 'models/Clan.php';

$database = new Database();
$db = $database->getConnection();

$clan = new Clan($db);

$message = '';
$message_type = '';

// Handle form submission
if ($_POST) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Sesi tidak valid (CSRF Token Error). Silakan refresh halaman.';
        $message_type = 'error';
    } elseif (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update':
                $clan->id = sanitizeInput($_POST['id']);
                $clan->nama_clan = sanitizeInput($_POST['nama_clan']);
                $clan->deskripsi = $_POST['deskripsi'] ?? '';
                $clan->is_public = isset($_POST['is_public']) ? 1 : 0;
                $clan->max_members = sanitizeInput($_POST['max_members'] ?? 50);

                if ($clan->update()) {
                    $message = 'Clan berhasil diperbarui!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal memperbarui clan!';
                    $message_type = 'error';
                }
                break;

            case 'delete':
                $clan->id = sanitizeInput($_POST['id']);
                if ($clan->delete()) {
                    $message = 'Clan berhasil dihapus!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menghapus clan!';
                    $message_type = 'error';
                }
                break;

            case 'toggle_public':
                $clan->id = sanitizeInput($_POST['id']);
                $clan_data_stmt = $clan->readOne();
                if ($clan_data = $clan_data_stmt->fetch(PDO::FETCH_ASSOC)) {
                    $clan->nama_clan = $clan_data['nama_clan'];
                    $clan->deskripsi = $clan_data['deskripsi'];
                    $clan->is_public = $clan_data['is_public'] ? 0 : 1;
                    $clan->max_members = $clan_data['max_members'];
                    if ($clan->update()) {
                        $status = $clan->is_public ? 'Publik' : 'Privat';
                        $message = "Clan berhasil diubah menjadi {$status}!";
                        $message_type = 'success';
                    }
                }
                break;
        }
    }
}

// Search or read all clans
$search = sanitizeInput($_GET['search'] ?? '');
if (!empty($search)) {
    $stmt = $clan->search($search);
} else {
    $stmt = $clan->readAllAdmin();
}

// Get stats
$total_clans_stmt = $db->query("SELECT COUNT(*) as total FROM clans");
$total_clans = $total_clans_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$public_clans_stmt = $db->query("SELECT COUNT(*) as total FROM clans WHERE is_public = 1");
$public_clans = $public_clans_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$total_members_stmt = $db->query("SELECT COUNT(*) as total FROM clan_members");
$total_members = $total_members_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$page_title = 'Manajemen Clan';
$page_css = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css'];
$body_class = getThemeClass();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once 'includes/head.php'; ?>
    <style>
        .admin-manage-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .admin-manage-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }
        .admin-manage-header p {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin: 0.25rem 0 0 0;
        }
        .admin-stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .admin-stat-card {
            background: var(--bg-card, #fff);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 12px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .admin-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .admin-stat-icon.brand { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .admin-stat-icon.success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .admin-stat-icon.warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .admin-stat-info .stat-value { font-size: 1.5rem; font-weight: 700; }
        .admin-stat-info .stat-label { font-size: 0.75rem; color: var(--text-muted); }
        .search-form {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .search-form input {
            flex: 1;
            padding: 0.625rem 1rem;
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 8px;
            font-size: 0.875rem;
            background: var(--bg-card, #fff);
            color: var(--text-primary);
        }
        .search-form input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .search-form button {
            padding: 0.625rem 1.25rem;
            background: linear-gradient(135deg, #3b82f6, #0ea5e9);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
        }
        .search-form .btn-clear {
            background: var(--bg-elevated, #f8fafc);
            color: var(--text-primary);
            border: 1px solid var(--border-color, #e2e8f0);
        }
        .clan-table-card {
            background: var(--bg-card, #fff);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 12px;
            overflow: hidden;
        }
        .clan-table-card .table-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color, #e2e8f0);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .clan-table-card .table-header h2 {
            font-size: 1.125rem;
            font-weight: 700;
            margin: 0;
        }
        .clan-table {
            width: 100%;
            border-collapse: collapse;
        }
        .clan-table th {
            padding: 0.75rem 1rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            background: var(--bg-subtle, #f8fafc);
            border-bottom: 1px solid var(--border-color, #e2e8f0);
        }
        .clan-table td {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            border-bottom: 1px solid var(--border-color, #f1f5f9);
            vertical-align: middle;
        }
        .clan-table tr:hover td {
            background: var(--bg-hover, #f8fafc);
        }
        .clan-name-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .clan-avatar-sm {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: linear-gradient(135deg, #3b82f6, #0ea5e9);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.875rem;
            flex-shrink: 0;
        }
        .clan-name-text .name { font-weight: 600; }
        .clan-name-text .desc { font-size: 0.75rem; color: var(--text-muted); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .clan-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .clan-badge.public { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .clan-badge.private { background: rgba(107, 114, 128, 0.1); color: #6b7280; }
        .clan-actions {
            display: flex;
            gap: 0.375rem;
        }
        .clan-actions form { display: inline; }
        .clan-actions .btn-icon-sm {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 6px;
            background: var(--bg-card, #fff);
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.2s;
        }
        .clan-actions .btn-icon-sm:hover { background: var(--bg-hover); color: var(--text-primary); }
        .clan-actions .btn-icon-sm.danger:hover { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: #fca5a5; }
        .clan-actions .btn-icon-sm.success:hover { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: #6ee7b7; }
        .empty-clans {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-muted);
        }
        .empty-clans .empty-icon { font-size: 2rem; margin-bottom: 0.5rem; }
        @media (max-width: 768px) {
            .admin-stats-row { grid-template-columns: 1fr; }
            .clan-table { font-size: 0.8rem; }
            .clan-table th:nth-child(4), .clan-table td:nth-child(4),
            .clan-table th:nth-child(5), .clan-table td:nth-child(5) { display: none; }
        }
    </style>
</head>
<body class="<?php echo trim($body_class . ' dashboard-layout'); ?>">
    <?php require_once 'navbar.php'; ?>

    <div class="page-wrapper dashboard-main-container">
        <div class="dashboard-content">

            <!-- Header -->
            <div class="admin-manage-header">
                <div>
                    <h1>⚔️ Manajemen Clan</h1>
                    <p>Kelola semua clan di platform Prozone</p>
                </div>
                <a href="dashboard.php" class="glass-btn glass-btn-secondary">← Kembali ke Dashboard</a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>" style="margin-bottom: 1.5rem;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="admin-stats-row">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon brand">⚔️</div>
                    <div class="admin-stat-info">
                        <div class="stat-value"><?php echo $total_clans; ?></div>
                        <div class="stat-label">Total Clan</div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon success">🌐</div>
                    <div class="admin-stat-info">
                        <div class="stat-value"><?php echo $public_clans; ?></div>
                        <div class="stat-label">Clan Publik</div>
                    </div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-icon warning">👥</div>
                    <div class="admin-stat-info">
                        <div class="stat-value"><?php echo $total_members; ?></div>
                        <div class="stat-label">Total Member</div>
                    </div>
                </div>
            </div>

            <!-- Search -->
            <form class="search-form" method="GET">
                <input type="text" name="search" placeholder="Cari clan berdasarkan nama, deskripsi, atau leader..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">🔍 Cari</button>
                <?php if (!empty($search)): ?>
                    <a href="manage-clans.php" class="btn-clear" style="padding: 0.625rem 1rem; text-decoration: none; border-radius: 8px; border: 1px solid var(--border-color); color: var(--text-primary);">✕ Reset</a>
                <?php endif; ?>
            </form>

            <!-- Clan Table -->
            <div class="clan-table-card">
                <div class="table-header">
                    <h2>Daftar Clan (<?php echo $stmt->rowCount(); ?>)</h2>
                </div>

                <?php if ($stmt->rowCount() > 0): ?>
                <div style="overflow-x: auto;">
                <table class="clan-table">
                    <thead>
                        <tr>
                            <th>Clan</th>
                            <th>Leader</th>
                            <th>Members</th>
                            <th>Total XP</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td>
                                <div class="clan-name-cell">
                                    <div class="clan-avatar-sm">
                                        <?php echo strtoupper(substr($row['nama_clan'], 0, 2)); ?>
                                    </div>
                                    <div class="clan-name-text">
                                        <div class="name"><?php echo htmlspecialchars($row['nama_clan']); ?></div>
                                        <div class="desc"><?php echo htmlspecialchars($row['deskripsi'] ?? '-'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row['leader_name'] ?? '-'); ?></td>
                            <td>
                                <strong><?php echo $row['member_count'] ?? $row['total_members'] ?? 0; ?></strong>
                                / <?php echo $row['max_members'] ?? 50; ?>
                            </td>
                            <td><?php echo number_format($row['total_xp'] ?? 0); ?></td>
                            <td>
                                <span class="clan-badge <?php echo $row['is_public'] ? 'public' : 'private'; ?>">
                                    <?php echo $row['is_public'] ? '🌐 Publik' : '🔒 Privat'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="clan-actions">
                                    <!-- Toggle Public/Private -->
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Ubah status clan ini?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                        <input type="hidden" name="action" value="toggle_public">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn-icon-sm success" title="<?php echo $row['is_public'] ? 'Jadikan Privat' : 'Jadikan Publik'; ?>">
                                            <?php echo $row['is_public'] ? '🔒' : '🌐'; ?>
                                        </button>
                                    </form>
                                    <!-- View Members -->
                                    <a href="clan.php?slug=<?php echo htmlspecialchars($row['slug']); ?>" class="btn-icon-sm" title="Lihat Detail">
                                        👁️
                                    </a>
                                    <!-- Delete -->
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus clan ini? Semua member akan dikeluarkan.');">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn-icon-sm danger" title="Hapus Clan">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                </div>
                <?php else: ?>
                <div class="empty-clans">
                    <div class="empty-icon">⚔️</div>
                    <p><?php echo !empty($search) ? 'Tidak ada clan yang cocok dengan pencarian "' . htmlspecialchars($search) . '"' : 'Belum ada clan yang terdaftar.'; ?></p>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <?php include 'footer.php'; ?>
    <?php include 'includes/loading.php'; ?>
    <?php include 'includes/toast.php'; ?>
    <script src="assets/js/navbar.js"></script>
</body>
</html>
