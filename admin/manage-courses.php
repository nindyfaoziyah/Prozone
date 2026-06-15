<?php
require_once '../config/config.php';
requireRole(['admin']);
require_once '../includes/icons.php';
require_once '../includes/language-icons.php';
require_once '../includes/FileUpload.php';

require_once '../models/Course.php';
require_once '../models/CourseCategory.php';

$database = new Database();
$db = $database->getConnection();

$course = new Course($db);
$category = new CourseCategory($db);

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
                $course->kode_course = sanitizeInput($_POST['kode_course'] ?? '');
                $course->judul_course = sanitizeInput($_POST['judul_course'] ?? '');
                $course->slug = ''; // Will be auto-generated in model
                $course->kategori_id = sanitizeInput($_POST['kategori_id'] ?? '') ?: null;
                $course->admin_id = $_SESSION['user_id'];
                $course->deskripsi = $_POST['deskripsi'] ?? '';
                $course->level = sanitizeInput($_POST['level'] ?? '');
                $course->durasi_jam = sanitizeInput($_POST['durasi_jam'] ?? 0);
                $course->harga = sanitizeInput($_POST['harga'] ?? 0);
                $course->is_free = isset($_POST['is_free']) ? 1 : 0;
                $course->is_published = isset($_POST['is_published']) ? 1 : 0;
                $course->xp_reward = sanitizeInput($_POST['xp_reward'] ?? 100);

                // Handle Thumbnail Upload with secure validation
                if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                    $upload = FileUpload::uploadThumbnail($_FILES['thumbnail'], 'thumb');
                    if ($upload['success']) {
                        $course->thumbnail = $upload['filename'];
                    }
                }

                if ($course->create()) {
                    $message = 'Kursus berhasil ditambahkan!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menambahkan kursus!';
                    $message_type = 'error';
                }
                break;

            case 'update':
                $course->id = sanitizeInput($_POST['id'] ?? 0);
                $course->kode_course = sanitizeInput($_POST['kode_course'] ?? '');
                $course->judul_course = sanitizeInput($_POST['judul_course'] ?? '');
                $course->slug = ''; // Will be auto-generated in model
                $course->kategori_id = sanitizeInput($_POST['kategori_id'] ?? '') ?: null;
                $course->admin_id = $_SESSION['user_id'];
                $course->deskripsi = $_POST['deskripsi'] ?? '';
                $course->level = sanitizeInput($_POST['level'] ?? '');
                $course->durasi_jam = sanitizeInput($_POST['durasi_jam'] ?? 0);
                $course->harga = sanitizeInput($_POST['harga'] ?? 0);
                $course->is_free = isset($_POST['is_free']) ? 1 : 0;
                $course->is_published = isset($_POST['is_published']) ? 1 : 0;
                $course->xp_reward = sanitizeInput($_POST['xp_reward'] ?? 100);

                // Handle Thumbnail Upload with secure validation
                if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                    $upload = FileUpload::uploadThumbnail($_FILES['thumbnail'], 'thumb');
                    if ($upload['success']) {
                        $course->thumbnail = $upload['filename'];
                    }
                }

                if ($course->update()) {
                    $message = 'Kursus berhasil diperbarui!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal memperbarui kursus!';
                    $message_type = 'error';
                }
                break;

            case 'delete':
                $course->id = sanitizeInput($_POST['id'] ?? 0);
                if ($course->delete()) {
                    $message = 'Kursus berhasil dihapus!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menghapus kursus!';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get all courses
$stmt = $course->readAll();
$courses = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $courses[] = $row;
}

// Get categories
$categories_stmt = $category->readAll();
$categories = [];
while ($row = $categories_stmt->fetch(PDO::FETCH_ASSOC)) {
    $categories[$row['id']] = $row;
}

$page_title = 'Kelola Kursus';
$page_css = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'admin.css', 'shared.css'];
$body_class = getThemeClass();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/head.php'; ?>
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
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            background: var(--bg-surface);
            color: var(--text-primary);
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px var(--brand-subtle);
        }
        .form-group textarea {
            resize: vertical;
            font-family: inherit;
            min-height: 100px;
        }
        .form-group input[type="checkbox"] {
            margin-right: 0.5rem;
            width: auto;
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
            max-width: 800px;
            margin: 2rem auto;
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
        .dark-mode .form-group input, .dark-mode .form-group select, .dark-mode .form-group textarea { background: var(--bg-tertiary); }
    </style>
</head>
<body class="dashboard-layout <?php echo $body_class; ?>">
    <?php require_once 'navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-content">

            <div class="content">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Add Course Button -->
                <div class="admin-header">
                    <div></div>
                    <button onclick="showCreateForm()" class="admin-action-btn lessons" style="padding:0.6rem 1.25rem;font-size:0.85rem;"><?php echo getIcon('plus', 16); ?> Tambah Kursus</button>
                </div>

                <!-- Courses Table -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3>Daftar Kursus</h3>
                        <span class="card-badge"><?php echo count($courses); ?> total</span>
                    </div>
                    <div class="admin-table-wrap">
                        <table class="admin-table compact">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Level</th>
                                    <th>Status</th>
                                    <th>Students</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($courses)): ?>
                                    <tr>
                                        <td colspan="8">
                                            <div class="admin-empty-state">
                                                <div class="empty-icon">📚</div>
                                                <div class="empty-text">Belum ada kursus. Klik "Tambah Kursus" untuk membuat kursus baru.</div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($courses as $course_item): ?>
                                        <tr>
                                            <td style="font-weight:600;"><?php echo htmlspecialchars($course_item['kode_course']); ?></td>
                                            <td>
                                                <div style="display:flex;align-items:center;gap:0.5rem;">
                                                    <?php $logo = getLanguageIcon($course_item['judul_course']); if ($logo): ?>
                                                    <img src="<?php echo $logo; ?>" alt="" style="width:24px;height:24px;object-fit:contain;flex-shrink:0;">
                                                    <?php endif; ?>
                                                    <strong><?php echo htmlspecialchars($course_item['judul_course']); ?></strong>
                                                </div>
                                            </td>
                                            <td style="color:var(--text-muted);font-size:0.8125rem;">
                                                <?php 
                                                if ($course_item['kategori_id'] && isset($categories[$course_item['kategori_id']])) {
                                                    echo htmlspecialchars($categories[$course_item['kategori_id']]['nama_kategori']);
                                                } else { echo '-'; }
                                                ?>
                                            </td>
                                            <td>
                                                <span class="admin-badge info"><?php echo htmlspecialchars($course_item['level']); ?></span>
                                            </td>
                                            <td>
                                                <?php if ($course_item['is_published']): ?>
                                                    <span class="admin-badge success">Published</span>
                                                <?php else: ?>
                                                    <span class="admin-badge warning">Draft</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="font-weight:600;"><?php echo $course_item['total_students']; ?></td>
                                             <td>
                                                <div style="display:flex;gap:0.35rem;flex-wrap:wrap;">
                                                    <button onclick="editCourse(<?php echo htmlspecialchars(json_encode($course_item)); ?>)" 
                                                            class="admin-action-btn edit">
                                                        <?php icon('edit', 14); ?> Edit
                                                    </button>
                                                    <a href="manage-lessons.php?course_id=<?php echo $course_item['id']; ?>" 
                                                       class="admin-action-btn lessons">
                                                        <?php icon('file', 14); ?> Lessons
                                                    </a>
                                                    <form method="POST" style="display:inline;" 
                                                          onsubmit="return confirm('Yakin ingin menghapus kursus ini?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $course_item['id']; ?>">
                                                        <button type="submit" class="admin-action-btn delete">
                                                            <?php icon('trash', 14); ?> Hapus
                                                        </button>
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

                <!-- Create/Edit Form Modal -->
                <div id="courseModal" class="admin-modal">
                    <div class="admin-modal-inner">
                        <div class="admin-modal-header">
                            <h2 id="modalTitle">Tambah Kursus</h2>
                            <button onclick="closeModal()" class="admin-modal-close">&times;</button>
                        </div>
                        <form method="POST" id="courseForm" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="hidden" name="action" id="formAction" value="create">
                            <input type="hidden" name="id" id="courseId">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Kode Kursus</label>
                                    <input type="text" name="kode_course" id="kode_course" required>
                                </div>
                                <div class="form-group">
                                    <label>Level</label>
                                    <select name="level" id="level" required>
                                        <option value="beginner">Beginner</option>
                                        <option value="intermediate">Intermediate</option>
                                        <option value="advanced">Advanced</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Judul Kursus</label>
                                <input type="text" name="judul_course" id="judul_course" required>
                            </div>

                            <div class="form-group">
                                <label>Thumbnail (Optional)</label>
                                <input type="file" name="thumbnail" id="thumbnail" accept="image/*">
                                <small style="color: #94a3b8;">Format: JPG, PNG, WEBP. Max 2MB.</small>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Kategori</label>
                                    <select name="kategori_id" id="kategori_id">
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>">
                                                <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                            </div>

                            <div class="form-group">
                                <label>Deskripsi</label>
                                <textarea name="deskripsi" id="deskripsi" rows="5"></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Durasi (Jam)</label>
                                    <input type="number" name="durasi_jam" id="durasi_jam" min="0" value="0">
                                </div>
                                <div class="form-group">
                                    <label>Harga</label>
                                    <input type="number" name="harga" id="harga" min="0" step="0.01" value="0">
                                </div>
                                <div class="form-group">
                                    <label>XP Reward</label>
                                    <input type="number" name="xp_reward" id="xp_reward" min="0" value="100">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="is_free" id="is_free" checked> 
                                        Kursus Gratis
                                    </label>
                                </div>
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="is_published" id="is_published"> 
                                        Publish Kursus
                                    </label>
                                </div>
                            </div>

                            <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end;">
                                <button type="button" onclick="closeModal()" style="padding:0.6rem 1.25rem;background:var(--bg-subtle);color:var(--text-secondary);border:1px solid var(--border-default);border-radius:var(--radius-md);font-weight:600;cursor:pointer;transition:all var(--transition-fast);font-size:0.85rem;">Batal</button>
                                <button type="submit" class="admin-action-btn lessons" style="padding:0.6rem 1.5rem;font-size:0.85rem;">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showCreateForm() {
            document.getElementById('modalTitle').textContent = 'Tambah Kursus';
            document.getElementById('formAction').value = 'create';
            document.getElementById('courseForm').reset();
            document.getElementById('courseId').value = '';
            document.getElementById('courseModal').style.display = 'block';
        }

        function editCourse(course) {
            document.getElementById('modalTitle').textContent = 'Edit Kursus';
            document.getElementById('formAction').value = 'update';
            document.getElementById('courseId').value = course.id;
            document.getElementById('kode_course').value = course.kode_course;
            document.getElementById('judul_course').value = course.judul_course;
            document.getElementById('kategori_id').value = course.kategori_id || '';
            document.getElementById('deskripsi').value = course.deskripsi || '';
            document.getElementById('level').value = course.level;
            document.getElementById('durasi_jam').value = course.durasi_jam;
            document.getElementById('harga').value = course.harga;
            document.getElementById('xp_reward').value = course.xp_reward || 100;
            document.getElementById('is_free').checked = course.is_free == 1;
            document.getElementById('is_published').checked = course.is_published == 1;
            document.getElementById('courseModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('courseModal').style.display = 'none';
        }

        // Close modal on outside click
        document.getElementById('courseModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>

    <?php include '../includes/loading.php'; ?>
    <?php include '../includes/toast.php'; ?>

    <script src="../assets/js/navbar.js"></script>
</body>
</html>


