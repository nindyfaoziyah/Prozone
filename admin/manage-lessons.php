<?php
require_once '../config/config.php';
requireRole(['admin']);
require_once '../includes/icons.php';
require_once '../includes/wysiwyg-editor.php';

require_once '../models/Course.php';
require_once '../models/Lesson.php';

$database = new Database();
$db = $database->getConnection();

$course = new Course($db);
$lesson = new Lesson($db);

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// If no course_id, show course selection
$show_course_selector = false;
if ($course_id > 0) {
    $course->id = $course_id;
    $course_data = $course->readOne();
    if (!$course_data) {
        $show_course_selector = true;
    }
} else {
    $show_course_selector = true;
}

// Get all courses for selector
$all_courses_stmt = $course->readAll();
$all_courses = [];
while ($row = $all_courses_stmt->fetch(PDO::FETCH_ASSOC)) {
    $all_courses[] = $row;
}

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
                $lesson->course_id = $course_id;
                $lesson->judul_lesson = sanitizeInput($_POST['judul_lesson']);
                $lesson->slug = ''; // Will be auto-generated in model
                $lesson->urutan = sanitizeInput($_POST['urutan']);
                $lesson->konten = $_POST['konten'] ?? '';
                $lesson->kode_contoh = $_POST['kode_contoh'] ?? '';
                $lesson->kode_solusi = $_POST['kode_solusi'] ?? '';
                $lesson->hints = $_POST['hints'] ?? '';
                $lesson->instruksi = $_POST['instruksi'] ?? '';
                $lesson->tipe = sanitizeInput($_POST['tipe']);
                $lesson->durasi_menit = sanitizeInput($_POST['durasi_menit'] ?? 0);
                $lesson->is_free = isset($_POST['is_free']) ? 1 : 0;
                $lesson->xp_reward = sanitizeInput($_POST['xp_reward'] ?? 10);

                if ($lesson->create()) {
                    // Update total_lessons in course
                    $query_update = "UPDATE courses SET total_lessons = (SELECT COUNT(*) FROM lessons WHERE course_id = :course_id) WHERE id = :course_id";
                    $stmt_update = $db->prepare($query_update);
                    $stmt_update->bindParam(':course_id', $course_id);
                    $stmt_update->execute();
                    
                    $message = 'Lesson berhasil ditambahkan!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menambahkan lesson!';
                    $message_type = 'error';
                }
                break;

            case 'update':
                $lesson->id = sanitizeInput($_POST['id']);
                $lesson->course_id = $course_id;
                $lesson->judul_lesson = sanitizeInput($_POST['judul_lesson']);
                $lesson->slug = ''; // Will be auto-generated in model
                $lesson->urutan = sanitizeInput($_POST['urutan']);
                $lesson->konten = $_POST['konten'] ?? '';
                $lesson->kode_contoh = $_POST['kode_contoh'] ?? '';
                $lesson->kode_solusi = $_POST['kode_solusi'] ?? '';
                $lesson->hints = $_POST['hints'] ?? '';
                $lesson->instruksi = $_POST['instruksi'] ?? '';
                $lesson->tipe = sanitizeInput($_POST['tipe']);
                $lesson->durasi_menit = sanitizeInput($_POST['durasi_menit'] ?? 0);
                $lesson->is_free = isset($_POST['is_free']) ? 1 : 0;
                $lesson->xp_reward = sanitizeInput($_POST['xp_reward'] ?? 10);

                if ($lesson->update()) {
                    $message = 'Lesson berhasil diperbarui!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal memperbarui lesson!';
                    $message_type = 'error';
                }
                break;

            case 'delete':
                $lesson->id = sanitizeInput($_POST['id']);
                if ($lesson->delete()) {
                    // Update total_lessons in course
                    $query_update = "UPDATE courses SET total_lessons = (SELECT COUNT(*) FROM lessons WHERE course_id = :course_id) WHERE id = :course_id";
                    $stmt_update = $db->prepare($query_update);
                    $stmt_update->bindParam(':course_id', $course_id);
                    $stmt_update->execute();
                    
                    $message = 'Lesson berhasil dihapus!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menghapus lesson!';
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Get all lessons for this course
$lessons_stmt = $lesson->readByCourse($course_id);
$lessons = [];
while ($row = $lessons_stmt->fetch(PDO::FETCH_ASSOC)) {
    $lessons[] = $row;
}
// Sort by urutan
usort($lessons, function($a, $b) {
    return $a['urutan'] - $b['urutan'];
});

$page_title = 'Kelola Lesson - ' . ($course_data['judul_course'] ?? 'Lesson');
$page_css = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'admin.css', 'shared.css'];
$body_class = getThemeClass();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/head.php'; ?>
    <style>
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-default);
            border-radius: var(--radius-md);
            background: var(--bg-subtle);
            color: var(--text-primary);
            font-size: 0.875rem;
            transition: all var(--transition-fast);
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: var(--shadow-primary);
            background: var(--bg-surface);
        }
        .form-group textarea {
            resize: vertical;
            font-family: 'Courier New', monospace;
        }
        .form-group input[type="checkbox"] {
            margin-right: 0.5rem;
            width: auto;
        }
        .lesson-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
        }
        .lesson-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-default);
        }
        .lesson-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        .lesson-table th {
            padding: 0.75rem;
            text-align: left;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--border-default);
        }
        .lesson-table td {
            padding: 0.75rem;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-default);
            vertical-align: middle;
        }
        .lesson-table tr:last-child td {
            border-bottom: none;
        }
        .lesson-table tr:hover td {
            background: var(--bg-hover);
        }
        .lesson-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 2000;
            overflow-y: auto;
            padding: 2rem;
        }
        .lesson-modal-content {
            max-width: 1000px;
            margin: 2rem auto;
            background: var(--bg-surface);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-lg);
        }
        .lesson-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-default);
        }
        .lesson-modal-header h2 {
            margin: 0;
            color: var(--text-primary);
        }
        .lesson-modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.5rem;
            cursor: pointer;
            transition: color var(--transition-fast);
            line-height: 1;
        }
        .lesson-modal-close:hover {
            color: var(--text-primary);
        }
        </style>
</head>
<body class="dashboard-layout <?php echo $body_class; ?>">
    <?php include_once 'navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-content">
            <div class="admin-header">
                <div>
                    <h1>Kelola Lesson</h1>
                    <?php if (!$show_course_selector && isset($course_data)): ?>
                        <p style="color:var(--text-muted);margin-top:0.25rem;">Kelola lesson untuk kursus: <?php echo htmlspecialchars($course_data['judul_course']); ?></p>
                    <?php else: ?>
                        <p style="color:var(--text-muted);margin-top:0.25rem;">Pilih kursus untuk mengelola lesson</p>
                    <?php endif; ?>
                </div>
                <a href="manage-courses.php" style="color:var(--text-muted);text-decoration:none;font-size:0.875rem;">&larr; Kembali ke Daftar Kursus</a>
            </div>

            <div class="content">
                <?php if ($show_course_selector): ?>
                    <div class="lesson-card">
                        <div class="lesson-card-header">
                            <h2 style="margin:0;color:var(--text-primary);font-size:1.125rem;font-weight:700;">Pilih Kursus</h2>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;">
                            <?php foreach ($all_courses as $c): ?>
                                <a href="manage-lessons.php?course_id=<?php echo $c['id']; ?>" style="display:flex;align-items:center;gap:1rem;padding:1.25rem;background:var(--bg-surface);border:1px solid var(--border-default);border-radius:var(--radius-lg);text-decoration:none;transition:all var(--transition-fast);color:var(--text-primary);">
                                    <div style="width:48px;height:48px;border-radius:var(--radius-md);background:var(--brand-gradient,linear-gradient(135deg,#3B82F6,#20C7B7));display:flex;align-items:center;justify-content:center;font-size:1.25rem;font-weight:700;color:#fff;flex-shrink:0;"><?php echo strtoupper(substr($c['judul_course'],0,1)); ?></div>
                                    <div>
                                        <div style="font-weight:600;margin-bottom:0.25rem;"><?php echo htmlspecialchars($c['judul_course']); ?></div>
                                        <div style="font-size:0.8125rem;color:var(--text-muted);"><?php echo $c['total_lessons']; ?> lesson | <?php echo ucfirst($c['level']); ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                            <?php if (empty($all_courses)): ?>
                                <p style="color:var(--text-muted);text-align:center;grid-column:1/-1;padding:2rem;">Belum ada kursus. Buat kursus terlebih dahulu.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>

                <div style="margin-bottom: 1rem;">
                    <a href="manage-lessons.php" style="color: var(--brand); text-decoration: none;">← Pilih Kursus Lain</a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <!-- Add Lesson Button -->
                <div style="margin-bottom: 2rem; text-align: right;">
                    <button onclick="showCreateForm()" class="admin-action-btn lessons" style="padding:0.6rem 1.25rem;font-size:0.85rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Tambah Lesson
                    </button>
                </div>

                <!-- Lessons Table -->
                <div class="lesson-card">
                    <div class="lesson-card-header">
                        <h2 style="margin:0;color:var(--text-primary);font-size:1.125rem;font-weight:700;">Daftar Lesson</h2>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="lesson-table">
                            <thead>
                                <tr>
                                    <th style="text-align:center;width:60px;">Urutan</th>
                                    <th>Judul Lesson</th>
                                    <th style="width:100px;">Tipe</th>
                                    <th style="width:90px;">Durasi</th>
                                    <th style="width:70px;">XP</th>
                                    <th style="width:160px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($lessons)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center;padding:3rem 2rem;color:var(--text-muted);">
                                            <div style="margin-bottom:1rem;opacity:0.4;"><?php icon('file', 48); ?></div>
                                            <p style="font-size:1rem;margin-bottom:0.5rem;">Belum ada lesson.</p>
                                            <p style="font-size:0.875rem;">Klik "Tambah Lesson" untuk membuat lesson baru.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($lessons as $lesson_item): ?>
                                        <tr>
                                            <td style="text-align:center;font-weight:600;"><?php echo $lesson_item['urutan']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($lesson_item['judul_lesson']); ?></strong></td>
                                            <td>
                                                <span class="admin-badge info"><?php echo htmlspecialchars($lesson_item['tipe']); ?></span>
                                            </td>
                                            <td style="color:var(--text-muted);font-size:0.8125rem;"><?php echo $lesson_item['durasi_menit']; ?> menit</td>
                                            <td style="font-weight:600;"><?php echo $lesson_item['xp_reward']; ?> XP</td>
                                            <td>
                                                <div style="display:flex;gap:0.35rem;">
                                                    <button onclick="editLesson(<?php echo htmlspecialchars(json_encode($lesson_item)); ?>)" 
                                                            class="admin-action-btn edit">
                                                        <?php icon('edit', 14); ?> Edit
                                                    </button>
                                                    <form method="POST" style="display:inline;" 
                                                          onsubmit="return confirm('Yakin ingin menghapus lesson ini?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $lesson_item['id']; ?>">
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
                <div id="lessonModal" class="lesson-modal" style="display:none;">
                    <div class="lesson-modal-content">
                        <div class="lesson-modal-header">
                            <h2 id="modalTitle">Tambah Lesson</h2>
                            <button onclick="closeModal()" class="lesson-modal-close">&times;</button>
                        </div>
                        <form method="POST" id="lessonForm">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="hidden" name="action" id="formAction" value="create">
                            <input type="hidden" name="id" id="lessonId">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Judul Lesson</label>
                                    <input type="text" name="judul_lesson" id="judul_lesson" required>
                                </div>
                                <div class="form-group">
                                    <label>Urutan</label>
                                    <input type="number" name="urutan" id="urutan" min="1" required>
                                </div>
                                <div class="form-group">
                                    <label>Tipe</label>
                                    <select name="tipe" id="tipe" required>
                                        <option value="theory">Theory</option>
                                        <option value="practice">Practice</option>
                                        <option value="quiz">Quiz</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Konten / Materi</label>
                                
                                <!-- WYSIWYG Editor Container (for theory type) -->
                                <div id="wysiwygContainer" class="wysiwyg-editor" style="display: none;"></div>
                                
                                <!-- Plain textarea (for practice type) -->
                                <textarea name="konten" id="konten" rows="8" style="font-family: monospace;"></textarea>
                                
                                <!-- Quiz Builder UI -->
                                <div id="quizBuilder" style="display: none; margin-top: 1rem; border: 1px solid #4b5563; padding: 1rem; border-radius: 8px; background: rgba(0,0,0,0.2);">
                                    <h3 style="margin-top:0;margin-bottom:1rem;color:var(--text-primary);">Quiz Builder</h3>
                                    <div id="questionsContainer"></div>
                                    <button type="button" onclick="addQuestion()" class="admin-action-btn lessons" style="margin-top:0.75rem;">+ Tambah Pertanyaan</button>
                                </div>
                                
                                <p style="font-size:0.75rem;color:var(--text-muted);margin-top:0.5rem;">
                                    <span id="editorHint">Gunakan editor visual untuk membuat konten materi.</span>
                                </p>
                            </div>

                            <div class="form-group">
                                <label>Instruksi</label>
                                <textarea name="instruksi" id="instruksi" rows="3" 
                                          placeholder="Instruksi untuk student (akan muncul di panel instruksi)"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Kode Contoh</label>
                                <textarea name="kode_contoh" id="kode_contoh" rows="6" 
                                          style="font-family: 'Courier New', monospace;" 
                                          placeholder="Kode contoh yang akan muncul di editor"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Kode Solusi</label>
                                <textarea name="kode_solusi" id="kode_solusi" rows="6" 
                                          style="font-family: 'Courier New', monospace;" 
                                          placeholder="Kode solusi (untuk validasi)"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Hints</label>
                                <textarea name="hints" id="hints" rows="3" 
                                          placeholder="Hints untuk membantu student"></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Durasi (Menit)</label>
                                    <input type="number" name="durasi_menit" id="durasi_menit" min="0" value="0">
                                </div>
                                <div class="form-group">
                                    <label>XP Reward</label>
                                    <input type="number" name="xp_reward" id="xp_reward" min="0" value="10">
                                </div>
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="is_free" id="is_free" checked> 
                                        Lesson Gratis
                                    </label>
                                </div>
                            </div>

                            <div style="margin-top:2rem;display:flex;gap:1rem;justify-content:flex-end;">
                                <button type="button" onclick="closeModal()" style="padding:0.6rem 1.25rem;background:var(--bg-subtle);color:var(--text-secondary);border:1px solid var(--border-default);border-radius:var(--radius-md);font-weight:600;cursor:pointer;transition:all var(--transition-fast);font-size:0.85rem;">Batal</button>
                                <button type="submit" class="admin-action-btn lessons" style="padding:0.6rem 1.5rem;font-size:0.85rem;">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>

        function showCreateForm() {
            document.getElementById('modalTitle').textContent = 'Tambah Lesson';
            document.getElementById('formAction').value = 'create';
            document.getElementById('lessonForm').reset();
            document.getElementById('lessonId').value = '';
            document.getElementById('konten').value = ''; // Reset konten
            
            // Set default urutan
            const currentLessons = <?php echo count($lessons); ?>;
            document.getElementById('urutan').value = currentLessons + 1;
            
            // Reset WYSIWYG if exists
            if (wysiwygEditor && wysiwygEditor.content) {
                wysiwygEditor.content.innerHTML = '';
            }
            
            // Reset Quiz Builder
            questions = [];
            toggleQuizBuilder();
            
            document.getElementById('lessonModal').style.display = 'block';
        }

        function editLesson(lesson) {
            document.getElementById('modalTitle').textContent = 'Edit Lesson';
            document.getElementById('formAction').value = 'update';
            document.getElementById('lessonId').value = lesson.id;
            document.getElementById('judul_lesson').value = lesson.judul_lesson;
            document.getElementById('urutan').value = lesson.urutan;
            document.getElementById('konten').value = lesson.konten || '';
            document.getElementById('instruksi').value = lesson.instruksi || '';
            document.getElementById('kode_contoh').value = lesson.kode_contoh || '';
            document.getElementById('kode_solusi').value = lesson.kode_solusi || '';
            document.getElementById('hints').value = lesson.hints || '';
            document.getElementById('tipe').value = lesson.tipe;
            document.getElementById('durasi_menit').value = lesson.durasi_menit || 0;
            document.getElementById('xp_reward').value = lesson.xp_reward || 10;
            document.getElementById('is_free').checked = lesson.is_free == 1;
            
            // Load content to WYSIWYG if theory type
            if (lesson.tipe === 'theory' && wysiwygEditor && wysiwygEditor.content) {
                wysiwygEditor.content.innerHTML = lesson.konten || '';
            }
            
            // Handle Quiz Builder
            questions = [];
            if (lesson.tipe === 'quiz') {
                try {
                    const parsed = JSON.parse(lesson.konten || '[]');
                    if (Array.isArray(parsed)) {
                        questions = parsed;
                    }
                } catch (e) {}
            }
            toggleQuizBuilder();
            
            document.getElementById('lessonModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('lessonModal').style.display = 'none';
        }

        // Close modal on outside click
        document.getElementById('lessonModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // WYSIWYG Editor instance
        let wysiwygEditor = null;

        // Toggle between WYSIWYG, textarea, and Quiz builder based on type
        function toggleQuizBuilder() {
            const type = document.getElementById('tipe').value;
            const builder = document.getElementById('quizBuilder');
            const kontenField = document.getElementById('konten');
            const wysiwygContainer = document.getElementById('wysiwygContainer');
            const editorHint = document.getElementById('editorHint');
            
            // Reset all
            builder.style.display = 'none';
            kontenField.style.display = 'none';
            wysiwygContainer.style.display = 'none';
            
            if (type === 'quiz') {
                // Quiz Builder
                builder.style.display = 'block';
                editorHint.textContent = 'Gunakan Quiz Builder untuk membuat pertanyaan pilihan ganda.';
                renderQuestions();
            } else if (type === 'theory') {
                // WYSIWYG Editor for theory
                wysiwygContainer.style.display = 'block';
                editorHint.textContent = 'Gunakan editor visual untuk membuat konten materi dengan format rich text.';
                
                // Initialize WYSIWYG if not exists
                if (!wysiwygEditor && typeof initWysiwyg === 'function') {
                    wysiwygContainer.innerHTML = ''; // Clear
                    wysiwygEditor = initWysiwyg('wysiwygContainer', 'konten');
                }
                
                // Sync content to WYSIWYG
                if (wysiwygEditor && wysiwygEditor.content) {
                    wysiwygEditor.content.innerHTML = kontenField.value || '';
                }
            } else {
                // Plain textarea for practice
                kontenField.style.display = 'block';
                editorHint.textContent = 'Gunakan format Markdown atau HTML untuk konten praktik.';
            }
        }

        function addQuestion() {
            questions.push({
                question: '',
                options: ['', '', '', ''],
                correct: 0
            });
            renderQuestions();
        }

        function renderQuestions() {
            const container = document.getElementById('questionsContainer');
            container.innerHTML = '';
            
            questions.forEach((q, index) => {
                const qDiv = document.createElement('div');
                qDiv.className = 'question-item';
                qDiv.style.marginBottom = '1.5rem';
                qDiv.style.padding = '1rem';
                qDiv.style.background = 'var(--bg-subtle)';
                qDiv.style.borderRadius = '8px';
                qDiv.style.border = '1px solid var(--border-default)';
                
                let html = `
                    <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem;">
                        <strong style="color:var(--text-primary)">Pertanyaan ${index + 1}</strong>
                        <button type="button" onclick="removeQuestion(${index})" style="color:var(--color-error); background:none; border:none; cursor:pointer;">Hapus</button>
                    </div>
                    <input type="text" value="${q.question}" onchange="updateQuestion(${index}, 'question', this.value)" placeholder="Tulis pertanyaan..." style="width:100%; padding:0.5rem; margin-bottom:0.5rem; background:var(--bg-surface); border:1px solid var(--border-default); color:var(--text-primary); border-radius:4px;">
                    <div style="display:grid; gap:0.5rem;">
                `;
                
                q.options.forEach((opt, optIndex) => {
                    html += `
                        <div style="display:flex; gap:0.5rem; align-items:center;">
                            <input type="radio" name="correct_${index}" ${q.correct == optIndex ? 'checked' : ''} onchange="updateQuestion(${index}, 'correct', ${optIndex})">
                            <input type="text" value="${opt}" onchange="updateQuestion(${index}, 'option', this.value, ${optIndex})" placeholder="Pilihan ${optIndex + 1}" style="flex:1; padding:0.5rem; background:var(--bg-surface); border:1px solid var(--border-default); color:var(--text-primary); border-radius:4px;">
                        </div>
                    `;
                });
                
                html += `</div>`;
                qDiv.innerHTML = html;
                container.appendChild(qDiv);
            });
            
            updateHiddenInput();
        }

        function updateQuestion(index, field, value, optIndex = null) {
            if (field === 'question') {
                questions[index].question = value;
            } else if (field === 'correct') {
                questions[index].correct = parseInt(value);
            } else if (field === 'option') {
                questions[index].options[optIndex] = value;
            }
            updateHiddenInput();
        }

        function removeQuestion(index) {
            questions.splice(index, 1);
            renderQuestions();
        }

        function updateHiddenInput() {
            document.getElementById('konten').value = JSON.stringify(questions);
        }

        document.getElementById('tipe').addEventListener('change', toggleQuizBuilder);
    </script>

    <?php include 'footer.php'; ?>
    <?php include '../includes/loading.php'; ?>
    <?php include '../includes/toast.php'; ?>

    <script src="../assets/js/navbar.js"></script>
</body>
</html>


