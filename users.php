<?php
require_once 'config/config.php';
requireRole(['admin']);
require_once 'includes/icons.php';

require_once 'models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$message = '';
$message_type = '';

// Handle form submission
if ($_POST) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Sesi tidak valid (CSRF Token Error). Silakan refresh halaman.';
        $message_type = 'error';
    } elseif (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $user->username = sanitizeInput($_POST['username'] ?? '');
                $user->password = $_POST['password'] ?? '';
                $user->nama_lengkap = sanitizeInput($_POST['nama_lengkap'] ?? '');
                $user->email = sanitizeInput($_POST['email'] ?? '');
                $user->role = sanitizeInput($_POST['role'] ?? 'student');

                if ($user->create()) {
                    $message = 'User berhasil ditambahkan!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menambahkan user!';
                    $message_type = 'error';
                }
                break;

            case 'update':
                $user->id = sanitizeInput($_POST['id'] ?? '');
                $user->username = sanitizeInput($_POST['username'] ?? '');
                $user->nama_lengkap = sanitizeInput($_POST['nama_lengkap'] ?? '');
                $user->email = sanitizeInput($_POST['email'] ?? '');
                $user->role = sanitizeInput($_POST['role'] ?? 'student');

                if ($user->update()) {
                    $message = 'User berhasil diperbarui!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal memperbarui user!';
                    $message_type = 'error';
                }
                break;

            case 'delete':
                $user->id = sanitizeInput($_POST['id'] ?? '');
                if ($user->id == $_SESSION['user_id']) {
                    $message = 'Tidak dapat menghapus akun sendiri!';
                    $message_type = 'error';
                } elseif ($user->delete()) {
                    $message = 'User berhasil dihapus!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menghapus user!';
                    $message_type = 'error';
                }
                break;

            case 'change_password':
                $user->id = sanitizeInput($_POST['id'] ?? '');
                $new_password = $_POST['new_password'] ?? '';
                
                if ($user->changePassword($new_password)) {
                    $message = 'Password berhasil diubah!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal mengubah password!';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get all users
$stmt = $user->readAll();

$page_title = 'Manajemen User';
$page_css = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'pages/admin.css'];
$body_class = getThemeClass();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once 'includes/head.php'; ?>
    <style>
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text-primary);
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            background: var(--bg-surface);
            color: var(--text-primary);
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px var(--brand-subtle);
        }
        .admin-modal {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 2000;
            overflow-y: auto;
            padding: 2rem;
            backdrop-filter: blur(4px);
        }
        .admin-modal-inner {
            max-width: 500px;
            margin: 3rem auto;
            background: var(--bg-surface);
            border-radius: var(--radius-lg);
            padding: 2rem;
            border: 1px solid var(--border-default);
            box-shadow: var(--shadow-xl);
        }
        .admin-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-default);
        }
        .admin-modal-header h2 {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
        }
        .admin-modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.25rem;
            line-height: 1;
            transition: color 0.2s;
        }
        .admin-modal-close:hover { color: var(--text-primary); }
        .dark-mode .admin-modal-inner { background: var(--bg-elevated); border-color: var(--border-color); }
        .dark-mode .form-group input, .dark-mode .form-group select { background: var(--bg-tertiary); }
    </style>
</head>
<body class="dashboard-layout <?php echo $body_class; ?>">
    <?php require_once 'navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-content">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Add User Form -->
                <div class="admin-card" style="margin-bottom:1.5rem;">
                    <div class="admin-card-header">
                        <h3>Tambah User Baru</h3>
                    </div>
                    <div style="padding:1.5rem;">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="hidden" name="action" value="create">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username" required>
                                </div>
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" id="password" name="password" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nama_lengkap">Nama Lengkap</label>
                                    <input type="text" id="nama_lengkap" name="nama_lengkap" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <select id="role" name="role" required>
                                        <option value="">Pilih Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="student">Student</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="admin-action-btn lessons" style="padding:0.6rem 1.25rem;font-size:0.85rem;">Tambah User</button>
                        </form>
                    </div>
                </div>

                <!-- Data User Table -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3>Daftar User</h3>
                    </div>
                    <div class="admin-table-wrap">
                        <table class="admin-table compact">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Nama Lengkap</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td style="font-weight:600;"><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar"><?php echo strtoupper(substr($row['nama_lengkap'] ?? 'U', 0, 1)); ?></div>
                                            <div class="user-info"><div class="name"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div></div>
                                        </div>
                                    </td>
                                    <td style="color:var(--text-muted);font-size:0.8125rem;"><?php echo htmlspecialchars($row['email'] ?: '-'); ?></td>
                                    <td>
                                        <?php if ($row['role'] === 'admin'): ?>
                                            <span class="admin-badge danger">Admin</span>
                                        <?php else: ?>
                                            <span class="admin-badge info">Siswa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color:var(--text-muted);font-size:0.8125rem;"><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <div style="display:flex;gap:0.35rem;flex-wrap:wrap;">
                                            <button onclick="editUser(<?php echo htmlspecialchars(json_encode($row)); ?>)" 
                                                    class="admin-action-btn edit">
                                                Edit
                                            </button>
                                            <button onclick="changePassword(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['username']); ?>')" 
                                                    class="admin-action-btn lessons">
                                                Password
                                            </button>
                                            <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                            <button onclick="deleteUser(<?php echo $row['id']; ?>)" 
                                                    class="admin-action-btn delete">
                                                Hapus
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="admin-modal">
        <div class="admin-modal-inner">
            <div class="admin-modal-header">
                <h2>Edit User</h2>
                <button onclick="closeEditModal()" class="admin-modal-close">&times;</button>
            </div>
            <form method="POST" id="editForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_username">Username</label>
                        <input type="text" id="edit_username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_nama_lengkap">Nama Lengkap</label>
                        <input type="text" id="edit_nama_lengkap" name="nama_lengkap" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email">
                    </div>
                    <div class="form-group">
                        <label for="edit_role">Role</label>
                        <select id="edit_role" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="student">Student</option>
                        </select>
                    </div>
                </div>

                    <div style="display: flex; gap: 10px; margin-top:1.5rem;">
                        <button type="submit" class="admin-action-btn lessons" style="padding:0.6rem 1.25rem;font-size:0.85rem;">Update</button>
                        <button type="button" onclick="closeEditModal()" style="padding:0.6rem 1.25rem;background:var(--bg-subtle);color:var(--text-secondary);border:1px solid var(--border-default);border-radius:var(--radius-md);font-weight:600;cursor:pointer;transition:all var(--transition-fast);font-size:0.85rem;">Batal</button>
                    </div>
            </form>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="passwordModal" class="admin-modal">
        <div class="admin-modal-inner" style="max-width:400px;">
            <div class="admin-modal-header">
                <h2>Ubah Password</h2>
                <button onclick="closePasswordModal()" class="admin-modal-close">&times;</button>
            </div>
            <form method="POST" id="passwordForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="id" id="password_user_id">
                
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="password_username" readonly style="background:var(--bg-subtle);">
                </div>
                
                <div class="form-group">
                    <label for="new_password">Password Baru</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>

                    <div style="display: flex; gap: 10px; margin-top:1.5rem;">
                        <button type="submit" class="admin-action-btn lessons" style="padding:0.6rem 1.25rem;font-size:0.85rem;">Ubah Password</button>
                        <button type="button" onclick="closePasswordModal()" style="padding:0.6rem 1.25rem;background:var(--bg-subtle);color:var(--text-secondary);border:1px solid var(--border-default);border-radius:var(--radius-md);font-weight:600;cursor:pointer;transition:all var(--transition-fast);font-size:0.85rem;">Batal</button>
                    </div>
            </form>
        </div>
    </div>

    <script>
        function editUser(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_username').value = data.username;
            document.getElementById('edit_nama_lengkap').value = data.nama_lengkap;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_role').value = data.role;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function changePassword(id, username) {
            document.getElementById('password_user_id').value = id;
            document.getElementById('password_username').value = username;
            document.getElementById('new_password').value = '';
            document.getElementById('passwordModal').style.display = 'block';
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }

        function deleteUser(id) {
            if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        document.getElementById('editModal').onclick = function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        }

        document.getElementById('passwordModal').onclick = function(e) {
            if (e.target === this) {
                closePasswordModal();
            }
        }
    </script>

    <?php include 'includes/loading.php'; ?>
    <?php include 'includes/toast.php'; ?>

    <script src="assets/js/navbar.js"></script>
</body>
</html>
