<?php
require_once 'config/config.php';
requireRole(['admin']);
require_once 'includes/icons.php';
require_once 'includes/activity_log.php';

require_once 'models/CourseCategory.php';

$database = new Database();
$db = $database->getConnection();

$category = new CourseCategory($db);

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
                $category->nama_kategori = sanitizeInput($_POST['nama_kategori']);
                $category->deskripsi = sanitizeInput($_POST['deskripsi']);
                $category->icon = sanitizeInput($_POST['icon'] ?? '💻');
                if ($category->create()) {
                    logActivity($db, $_SESSION['user_id'], 'category_create', 'Membuat kategori: ' . $_POST['nama_kategori']);
                    $message = 'Kategori berhasil ditambahkan!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menambahkan kategori!';
                    $message_type = 'error';
                }
                break;

            case 'update':
                $category->id = sanitizeInput($_POST['id']);
                $category->nama_kategori = sanitizeInput($_POST['nama_kategori']);
                $category->deskripsi = sanitizeInput($_POST['deskripsi']);
                $category->icon = sanitizeInput($_POST['icon'] ?? '💻');
                if ($category->update()) {
                    logActivity($db, $_SESSION['user_id'], 'category_update', 'Memperbarui kategori ID: ' . $_POST['id']);
                    $message = 'Kategori berhasil diperbarui!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal memperbarui kategori!';
                    $message_type = 'error';
                }
                break;

            case 'delete':
                $category->id = sanitizeInput($_POST['id']);
                if ($category->delete()) {
                    logActivity($db, $_SESSION['user_id'], 'category_delete', 'Menghapus kategori ID: ' . $_POST['id']);
                    $message = 'Kategori berhasil dihapus!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menghapus kategori!';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get all categories
$stmt = $category->readAll();
$categories = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Count courses in this category
    $count_stmt = $db->prepare("SELECT COUNT(*) as total FROM courses WHERE kategori_id = :id");
    $count_stmt->bindParam(':id', $row['id']);
    $count_stmt->execute();
    $row['total_courses'] = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $categories[] = $row;
}

$page_title = 'Manage Kategori Kursus';
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
        .modal-overlay { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.6); z-index:2000; overflow-y:auto; padding:2rem; }
        .modal-content { max-width:600px; margin:2rem auto; background:var(--bg-surface); border:1px solid var(--border-default); border-radius:var(--radius-lg); padding:2rem; box-shadow:var(--shadow-lg); }
        .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; padding-bottom:1rem; border-bottom:1px solid var(--border-default); }
        .modal-header h2 { margin:0; color:var(--text-primary); }
        .modal-close { background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer; }
        .modal-close:hover { color:var(--text-primary); }
        .alert { padding:1rem 1.5rem; border-radius:var(--radius-md); margin-bottom:1.5rem; font-size:0.875rem; font-weight:500; }
        .alert-success { background:rgba(16,185,129,0.12); border-left:4px solid #10b981; color:#10b981; }
        .alert-error { background:rgba(239,68,68,0.12); border-left:4px solid #ef4444; color:#ef4444; }
        .category-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:1rem; }
        .category-card { background:var(--bg-surface); border:1px solid var(--border-default); border-radius:var(--radius-lg); padding:1.5rem; transition:all var(--transition-fast); }
        .category-card:hover { border-color:var(--brand); box-shadow:var(--shadow-md); }
    </style>
</head>
<body class="dashboard-layout <?php echo $body_class; ?>">
    <?php include_once 'navbar.php'; ?>
    <div class="dashboard-container">
        <div class="dashboard-content">
            <div class="admin-header">
                <div>
                    <h1>Kategori Kursus</h1>
                    <p class="admin-subtitle">Kelola kategori untuk kursus</p>
                </div>
                <div class="admin-header-actions">
                    <button onclick="openModal('create')" class="admin-action-btn primary" style="padding:0.6rem 1.25rem;font-size:0.85rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Tambah Kategori
                    </button>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="category-grid">
                <?php if (empty($categories)): ?>
                    <div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--text-muted);background:var(--bg-surface);border-radius:var(--radius-lg);border:1px solid var(--border-default);">
                        <p style="font-size:1rem;margin-bottom:0.5rem;">Belum ada kategori.</p>
                        <p style="font-size:0.875rem;">Klik "Tambah Kategori" untuk membuat kategori baru.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <div class="category-card">
                            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;">
                                <span style="font-size:2rem;"><?php echo htmlspecialchars($cat['icon'] ?? '💻'); ?></span>
                                <div>
                                    <h3 style="margin:0;font-size:1rem;font-weight:700;color:var(--text-primary);"><?php echo htmlspecialchars($cat['nama_kategori']); ?></h3>
                                    <span style="font-size:0.8125rem;color:var(--text-muted);"><?php echo $cat['total_courses']; ?> kursus</span>
                                </div>
                            </div>
                            <p style="font-size:0.875rem;color:var(--text-secondary);margin:0 0 1rem 0;"><?php echo htmlspecialchars($cat['deskripsi'] ?? '-'); ?></p>
                            <div style="display:flex;gap:0.5rem;">
                                <button onclick='editCategory(<?php echo json_encode($cat); ?>)' class="admin-action-btn edit" style="font-size:0.8125rem;"><?php icon('edit',14); ?> Edit</button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus kategori ini? Kursus dengan kategori ini akan di-set NULL.');">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                    <button type="submit" class="admin-action-btn delete" style="font-size:0.8125rem;"><?php icon('trash',14); ?> Hapus</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Create/Edit Modal -->
            <div id="categoryModal" class="modal-overlay" style="display:none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 id="modalTitle">Tambah Kategori</h2>
                        <button onclick="closeModal()" class="modal-close">&times;</button>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="categoryId">

                        <div class="form-group">
                            <label>Nama Kategori</label>
                            <input type="text" name="nama_kategori" id="nama_kategori" required placeholder="Web Development">
                        </div>

                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="2" placeholder="Kursus tentang pengembangan web"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Icon (emoji)</label>
                            <input type="text" name="icon" id="icon" placeholder="💻" maxlength="10">
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
        function openModal(action) {
            document.getElementById('categoryModal').style.display = 'block';
            if (action === 'create') {
                document.getElementById('modalTitle').textContent = 'Tambah Kategori';
                document.getElementById('formAction').value = 'create';
                document.getElementById('categoryId').value = '';
                document.getElementById('nama_kategori').value = '';
                document.getElementById('deskripsi').value = '';
                document.getElementById('icon').value = '💻';
            }
        }

        function editCategory(cat) {
            document.getElementById('modalTitle').textContent = 'Edit Kategori';
            document.getElementById('formAction').value = 'update';
            document.getElementById('categoryId').value = cat.id;
            document.getElementById('nama_kategori').value = cat.nama_kategori;
            document.getElementById('deskripsi').value = cat.deskripsi || '';
            document.getElementById('icon').value = cat.icon || '💻';
            document.getElementById('categoryModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('categoryModal').style.display = 'none';
        }

        document.getElementById('categoryModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>

    <?php include 'includes/loading.php'; ?>
    <?php include 'includes/toast.php'; ?>
    <script src="assets/js/navbar.js"></script>
</body>
</html>
