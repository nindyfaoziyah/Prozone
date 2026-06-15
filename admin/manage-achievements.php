<?php
require_once '../config/config.php';
requireRole(['admin']);
require_once '../includes/icons.php';
require_once '../includes/activity_log.php';

require_once '../models/Achievement.php';

$database = new Database();
$db = $database->getConnection();

$achievement = new Achievement($db);

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Sesi tidak valid (CSRF Token Error). Silakan refresh halaman.';
        $message_type = 'error';
    } elseif (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $query = "INSERT INTO achievements SET kode_achievement=:kode, nama_achievement=:nama, deskripsi=:deskripsi, icon=:icon, xp_reward=:xp, tipe=:tipe, requirement_value=:req";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':kode', $_POST['kode_achievement']);
                $stmt->bindParam(':nama', $_POST['nama_achievement']);
                $stmt->bindParam(':deskripsi', $_POST['deskripsi']);
                $stmt->bindParam(':icon', $_POST['icon']);
                $stmt->bindParam(':xp', $_POST['xp_reward']);
                $stmt->bindParam(':tipe', $_POST['tipe']);
                $stmt->bindParam(':req', $_POST['requirement_value']);
                if ($stmt->execute()) {
                    logActivity($db, $_SESSION['user_id'], 'achievement_create', 'Membuat achievement: ' . $_POST['kode_achievement']);
                    $message = 'Achievement berhasil ditambahkan!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menambahkan achievement!';
                    $message_type = 'error';
                }
                break;

            case 'update':
                $query = "UPDATE achievements SET kode_achievement=:kode, nama_achievement=:nama, deskripsi=:deskripsi, icon=:icon, xp_reward=:xp, tipe=:tipe, requirement_value=:req WHERE id=:id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $_POST['id']);
                $stmt->bindParam(':kode', $_POST['kode_achievement']);
                $stmt->bindParam(':nama', $_POST['nama_achievement']);
                $stmt->bindParam(':deskripsi', $_POST['deskripsi']);
                $stmt->bindParam(':icon', $_POST['icon']);
                $stmt->bindParam(':xp', $_POST['xp_reward']);
                $stmt->bindParam(':tipe', $_POST['tipe']);
                $stmt->bindParam(':req', $_POST['requirement_value']);
                if ($stmt->execute()) {
                    logActivity($db, $_SESSION['user_id'], 'achievement_update', 'Memperbarui achievement ID: ' . $_POST['id']);
                    $message = 'Achievement berhasil diperbarui!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal memperbarui achievement!';
                    $message_type = 'error';
                }
                break;

            case 'delete':
                $query = "DELETE FROM achievements WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $_POST['id']);
                if ($stmt->execute()) {
                    logActivity($db, $_SESSION['user_id'], 'achievement_delete', 'Menghapus achievement ID: ' . $_POST['id']);
                    $message = 'Achievement berhasil dihapus!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menghapus achievement!';
                    $message_type = 'error';
                }
                break;

            case 'toggle_active':
                $query = "UPDATE achievements SET is_active = NOT is_active WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $_POST['id']);
                $stmt->execute();
                logActivity($db, $_SESSION['user_id'], 'achievement_toggle', 'Toggle status achievement ID: ' . $_POST['id']);
                $message = 'Status achievement diubah!';
                $message_type = 'success';
                break;
        }
    }
}

// Get all achievements
$achievements = [];
$stmt = $db->query("SELECT a.*, (SELECT COUNT(*) FROM user_achievements ua WHERE ua.achievement_id = a.id) as total_earned FROM achievements a ORDER BY a.xp_reward DESC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $achievements[] = $row;
}

// Get all user achievements for stats
$user_achievements = [];
$stmt_ua = $db->query("SELECT ua.*, a.nama_achievement, a.icon, u.nama_lengkap FROM user_achievements ua JOIN achievements a ON ua.achievement_id = a.id JOIN users u ON ua.user_id = u.id ORDER BY ua.earned_at DESC LIMIT 50");
while ($row = $stmt_ua->fetch(PDO::FETCH_ASSOC)) {
    $user_achievements[] = $row;
}

$page_title = 'Manage Achievements';
$page_css = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'admin.css', 'shared.css'];
$body_class = getThemeClass();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/head.php'; ?>
    <style>
        .modal-overlay { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.6); z-index:2000; overflow-y:auto; padding:2rem; }
        .modal-content { max-width:700px; margin:2rem auto; background:var(--bg-surface); border:1px solid var(--border-default); border-radius:var(--radius-lg); padding:2rem; box-shadow:var(--shadow-lg); }
        .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; padding-bottom:1rem; border-bottom:1px solid var(--border-default); }
        .modal-header h2 { margin:0; color:var(--text-primary); }
        .modal-close { background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer; }
        .modal-close:hover { color:var(--text-primary); }
        .tab-nav { display:flex; gap:0.5rem; margin-bottom:1.5rem; border-bottom:1px solid var(--border-default); padding-bottom:0; }
        .tab-nav button { padding:0.75rem 1.25rem; background:none; border:none; color:var(--text-muted); font-weight:600; font-size:0.875rem; cursor:pointer; border-bottom:2px solid transparent; transition:all var(--transition-fast); }
        .tab-nav button.active { color:var(--brand); border-bottom-color:var(--brand); }
        .tab-nav button:hover { color:var(--text-primary); }
    </style>
</head>
<body class="dashboard-layout <?php echo $body_class; ?>">
    <?php include_once 'navbar.php'; ?>
    <div class="dashboard-container">
        <div class="dashboard-content">
            <div class="admin-header">
                <div>
                    <h1><span class="header-icon amber">??</span> Manage Achievements</h1>
                    <p class="admin-subtitle">Kelola achievement dan lihat pencapaian user</p>
                </div>
                <div class="admin-header-actions">
                    <button onclick="openModal('create')" class="admin-action-btn primary" style="padding:0.6rem 1.25rem;font-size:0.85rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Tambah Achievement
                    </button>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="tab-nav">
                <button class="active" onclick="switchTab('achievements', this)">Daftar Achievement</button>
                <button onclick="switchTab('user-achievements', this)">Riwayat User (50 terakhir)</button>
            </div>

            <div id="tab-achievements">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 style="margin:0;color:var(--text-primary);font-size:1.125rem;font-weight:700;">Semua Achievement</h2>
                        <span style="color:var(--text-muted);font-size:0.875rem;"><?php echo count($achievements); ?> total</span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th style="width:40px;">Icon</th>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Tipe</th>
                                    <th>XP</th>
                                    <th style="text-align:center;">Earned</th>
                                    <th style="text-align:center;">Aktif</th>
                                    <th style="width:160px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($achievements)): ?>
                                    <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--text-muted);">Belum ada achievement.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($achievements as $a): ?>
                                        <tr>
                                            <td style="font-size:1.5rem;text-align:center;"><?php echo htmlspecialchars($a['icon'] ?? '🏆'); ?></td>
                                            <td><code style="background:var(--bg-subtle);padding:0.25rem 0.5rem;border-radius:4px;font-size:0.8125rem;"><?php echo htmlspecialchars($a['kode_achievement']); ?></code></td>
                                            <td><strong><?php echo htmlspecialchars($a['nama_achievement']); ?></strong></td>
                                            <td><span class="admin-badge info"><?php echo htmlspecialchars($a['tipe']); ?></span></td>
                                            <td style="font-weight:600;"><?php echo $a['xp_reward']; ?> XP</td>
                                            <td style="text-align:center;"><?php echo $a['total_earned']; ?>x</td>
                                            <td style="text-align:center;">
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                    <input type="hidden" name="action" value="toggle_active">
                                                    <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                                    <button type="submit" style="background:none;border:none;cursor:pointer;font-size:1.25rem;" title="<?php echo $a['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>">
                                                        <?php echo $a['is_active'] ? '✅' : '❌'; ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <div style="display:flex;gap:0.35rem;">
                                                    <button onclick='editAchievement(<?php echo json_encode($a); ?>)' class="admin-action-btn edit"><?php icon('edit',14); ?> Edit</button>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus achievement ini?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                                        <button type="submit" class="admin-action-btn delete"><?php icon('trash',14); ?> Hapus</button>
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

            <div id="tab-user-achievements" style="display:none;">
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h2 style="margin:0;color:var(--text-primary);font-size:1.125rem;font-weight:700;">Riwayat Pencapaian User</h2>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Achievement</th>
                                    <th>Icon</th>
                                    <th>Earned At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($user_achievements)): ?>
                                    <tr><td colspan="4" style="text-align:center;padding:3rem;color:var(--text-muted);">Belum ada pencapaian user.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($user_achievements as $ua): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($ua['nama_lengkap']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($ua['nama_achievement']); ?></td>
                                            <td style="font-size:1.25rem;text-align:center;"><?php echo htmlspecialchars($ua['icon'] ?? '🏆'); ?></td>
                                            <td style="color:var(--text-muted);font-size:0.8125rem;"><?php echo formatDateTime($ua['earned_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Create/Edit Modal -->
            <div id="achievementModal" class="modal-overlay" style="display:none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 id="modalTitle">Tambah Achievement</h2>
                        <button onclick="closeModal()" class="modal-close">&times;</button>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="achievementId">

                        <div class="form-row">
                            <div class="form-group">
                                <label>Kode Achievement</label>
                                <input type="text" name="kode_achievement" id="kode_achievement" required placeholder="first_lesson">
                            </div>
                            <div class="form-group">
                                <label>Nama Achievement</label>
                                <input type="text" name="nama_achievement" id="nama_achievement" required placeholder="First Lesson">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="2" placeholder="Selesaikan lesson pertama Anda"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Icon (emoji)</label>
                                <input type="text" name="icon" id="icon" placeholder="🏆" maxlength="10">
                            </div>
                            <div class="form-group">
                                <label>XP Reward</label>
                                <input type="number" name="xp_reward" id="xp_reward" min="0" value="50">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Tipe</label>
                                <select name="tipe" id="tipe" required>
                                    <option value="course_complete">Course Complete</option>
                                    <option value="lesson_complete">Lesson Complete</option>
                                    <option value="streak">Streak</option>
                                    <option value="clan">Clan</option>
                                    <option value="special">Special</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Requirement Value</label>
                                <input type="number" name="requirement_value" id="requirement_value" min="0" value="1">
                            </div>
                        </div>

                        <div style="margin-top:2rem;display:flex;gap:1rem;justify-content:flex-end;">
                            <button type="button" onclick="closeModal()" style="padding:0.6rem 1.25rem;background:var(--bg-subtle);color:var(--text-secondary);border:1px solid var(--border-default);border-radius:var(--radius-md);font-weight:600;cursor:pointer;">Batal</button>
                            <button type="submit" class="admin-action-btn primary" style="padding:0.6rem 1.5rem;">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(action, data) {
            document.getElementById('achievementModal').style.display = 'block';
            if (action === 'create') {
                document.getElementById('modalTitle').textContent = 'Tambah Achievement';
                document.getElementById('formAction').value = 'create';
                document.getElementById('achievementId').value = '';
                document.getElementById('kode_achievement').value = '';
                document.getElementById('nama_achievement').value = '';
                document.getElementById('deskripsi').value = '';
                document.getElementById('icon').value = '';
                document.getElementById('xp_reward').value = '50';
                document.getElementById('tipe').value = 'special';
                document.getElementById('requirement_value').value = '1';
            }
        }

        function editAchievement(a) {
            document.getElementById('modalTitle').textContent = 'Edit Achievement';
            document.getElementById('formAction').value = 'update';
            document.getElementById('achievementId').value = a.id;
            document.getElementById('kode_achievement').value = a.kode_achievement;
            document.getElementById('nama_achievement').value = a.nama_achievement;
            document.getElementById('deskripsi').value = a.deskripsi || '';
            document.getElementById('icon').value = a.icon || '';
            document.getElementById('xp_reward').value = a.xp_reward;
            document.getElementById('tipe').value = a.tipe;
            document.getElementById('requirement_value').value = a.requirement_value;
            document.getElementById('achievementModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('achievementModal').style.display = 'none';
        }

        document.getElementById('achievementModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        function switchTab(tab, btn) {
            document.querySelectorAll('.tab-nav button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('tab-achievements').style.display = tab === 'achievements' ? 'block' : 'none';
            document.getElementById('tab-user-achievements').style.display = tab === 'user-achievements' ? 'block' : 'none';
        }
    </script>

    <?php include 'footer.php'; ?>
    <?php include '../includes/loading.php'; ?>
    <?php include '../includes/toast.php'; ?>
    <script src="../assets/js/navbar.js"></script>
</body>
</html>
