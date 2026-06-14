<?php
require_once 'config/config.php';
requireLogin();
require_once 'includes/icons.php';
require_once 'includes/language-icons.php';

require_once 'models/Course.php';
require_once 'models/Lesson.php';
require_once 'models/Enrollment.php';
require_once 'models/UserProgress.php';

$database = new Database();
$db = $database->getConnection();

$course = new Course($db);
$lesson = new Lesson($db);
$enrollment = new Enrollment($db);
$user_progress = new UserProgress($db);

// Get course
$course_id = $_GET['id'] ?? 0;
$course->id = $course_id;
$course_data = $course->readOne();

if (!$course_data) {
    header('Location: courses.php');
    exit();
}

// Get lessons
$lessons_stmt = $lesson->readByCourse($course_id);
$lessons = [];
while ($row = $lessons_stmt->fetch(PDO::FETCH_ASSOC)) {
    $lessons[] = $row;
}

// Check enrollment
$is_enrolled = $enrollment->isEnrolled($_SESSION['user_id'], $course_id);
$enrollment_data = null;
if ($is_enrolled) {
    $enrollment_stmt = $enrollment->getUserEnrollments($_SESSION['user_id']);
    while ($row = $enrollment_stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['course_id'] == $course_id) {
            $enrollment_data = $row;
            break;
        }
    }
}

// Get user progress for each lesson
$progress_map = [];
if ($is_enrolled) {
    $progress_stmt = $user_progress->getCourseProgress($_SESSION['user_id'], $course_id);
    while ($row = $progress_stmt->fetch(PDO::FETCH_ASSOC)) {
        $progress_map[$row['lesson_id']] = $row;
    }
}

// Handle enrollment
if ($_POST) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        die('Sesi tidak valid (CSRF Token Error). Silakan refresh halaman.');
    }
    if (isset($_POST['enroll'])) {
        $enrollment->user_id = $_SESSION['user_id'];
        $enrollment->course_id = $course_id;
        if ($enrollment->enroll()) {
            header('Location: course.php?id=' . $course_id);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'includes/favicon.php'; ?>
    <?php include 'includes/seo.php'; echo seo_meta(htmlspecialchars($course_data['judul_course']) . ' - ' . APP_NAME, htmlspecialchars($course_data['deskripsi'] ?? 'Detail kursus'), 'course, learning, ' . htmlspecialchars($course_data['judul_course'])); ?>
    <title><?php echo htmlspecialchars($course_data['judul_course']); ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/pages/course-detail.css">
</head>
<body>
    <!-- Navbar -->
    <?php require_once 'navbar.php'; ?>

    <div class="dashboard-main-container">
        <div class="dashboard-content">
            <div class="course-header">
                <div class="course-header-content">
                    <div class="course-breadcrumb">
                        <a href="courses.php"><?php icon('arrow-left', 14); ?> Kembali ke Semua Kursus</a>
                    </div>
                    <div class="course-title-with-logo">
                        <?php 
                        $logo_url = getLanguageIcon($course_data['judul_course']);
                        if ($logo_url): 
                        ?>
                        <img src="<?php echo $logo_url; ?>" alt="Language Logo" class="course-language-logo">
                        <?php endif; ?>
                        <h1 class="course-title-large"><?php echo htmlspecialchars($course_data['judul_course']); ?></h1>
                    </div>
                    <div class="course-meta-info">
                        <div class="course-meta-item">
                            <span><?php icon('book', 16); ?></span>
                            <span><?php echo $course_data['total_lessons']; ?> Lessons</span>
                        </div>
                        <div class="course-meta-item">
                            <span><?php icon('clock', 16); ?></span>
                            <span><?php echo $course_data['durasi_jam']; ?> Jam</span>
                        </div>
                        <div class="course-meta-item">
                            <span><?php icon('user', 16); ?></span>
                            <span><?php echo htmlspecialchars($course_data['instructor_name']); ?></span>
                        </div>
                        <div class="course-meta-item">
                            <span><?php icon('chart', 16); ?></span>
                            <span><?php echo ucfirst($course_data['level']); ?> Level</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="course-description-full">
                <h2>Tentang Kursus</h2>
                <p><?php echo nl2br(htmlspecialchars($course_data['deskripsi'])); ?></p>
            </div>

            <?php if (!$is_enrolled): ?>
                <div class="enroll-section">
                    <h2>Mulai Belajar Sekarang!</h2>
                    <p><?php echo $course_data['is_free'] ? 'Kursus ini gratis!' : 'Harga: Rp ' . number_format($course_data['harga'], 0, ',', '.'); ?></p>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <button type="submit" name="enroll" class="btn-start">
                            <?php icon('user-plus', 16); ?>
                            Daftar Kursus
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <?php if ($enrollment_data): ?>
                    <?php
                    // Calculate statistics
                    $total_xp = 0;
                    $total_time = 0;
                    foreach ($lessons as $l) {
                        $lp = $progress_map[$l['id']] ?? null;
                        if ($lp && $lp['status'] == 'completed') {
                            $total_xp += $l['xp_reward'] ?? 10;
                        }
                        $total_time += $l['durasi_menit'] ?? 0;
                    }
                    $last_lesson = null;
                    foreach ($lessons as $l) {
                        $lp = $progress_map[$l['id']] ?? null;
                        if ($lp && ($lp['status'] == 'in_progress' || $lp['status'] == 'completed')) {
                            $last_lesson = $l;
                        }
                    }
                    // Find next incomplete lesson
                    $next_lesson = null;
                    foreach ($lessons as $l) {
                        $lp = $progress_map[$l['id']] ?? null;
                        if (!$lp || $lp['status'] != 'completed') {
                            $next_lesson = $l;
                            break;
                        }
                    }
                    ?>
                    <div class="enroll-section progress-section-enhanced">
                        <div class="progress-header">
                            <h2>📊 Progress Belajar Anda</h2>
                            <div class="progress-badge"><?php echo number_format($enrollment_data['progress_percent'], 0); ?>%</div>
                        </div>
                        
                        <div class="progress-bar-container">
                            <div class="progress-bar-track">
                                <div class="progress-bar-fill" style="width: <?php echo $enrollment_data['progress_percent']; ?>%;" data-progress="<?php echo $enrollment_data['progress_percent']; ?>">
                                    <div class="progress-bar-glow"></div>
                                </div>
                            </div>
                            <div class="progress-stats">
                                <span class="progress-text">
                                    <strong><?php echo $enrollment_data['completed_lessons']; ?></strong> dari <strong><?php echo $course_data['total_lessons']; ?></strong> lessons selesai
                                </span>
                            </div>
                        </div>

                        <div class="course-statistics">
                            <div class="stat-card">
                                <div class="stat-icon">⭐</div>
                                <div class="stat-content">
                                    <div class="stat-value"><?php echo number_format($total_xp); ?></div>
                                    <div class="stat-label">XP Diperoleh</div>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon">⏱️</div>
                                <div class="stat-content">
                                    <div class="stat-value"><?php echo number_format($total_time); ?></div>
                                    <div class="stat-label">Menit</div>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon">🎯</div>
                                <div class="stat-content">
                                    <div class="stat-value"><?php echo $enrollment_data['completed_lessons']; ?></div>
                                    <div class="stat-label">Lessons Selesai</div>
                                </div>
                            </div>
                        </div>

                        <?php if ($next_lesson): ?>
                        <div class="quick-action-section">
                            <a href="lesson.php?course_id=<?php echo $course_id; ?>&lesson_id=<?php echo $next_lesson['id']; ?>" class="quick-action-btn">
                                <span class="quick-action-icon"><?php icon('play', 20); ?></span>
                                <div class="quick-action-content">
                                    <div class="quick-action-title">Lanjutkan Belajar</div>
                                    <div class="quick-action-subtitle"><?php echo htmlspecialchars($next_lesson['judul_lesson']); ?></div>
                                </div>
                                <span class="quick-action-arrow"><?php icon('arrow-right', 16); ?></span>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="lessons-section">
                <h2>Daftar Lesson</h2>
                <div class="lessons-list">
                    <?php if (empty($lessons)): ?>
                    <div class="lessons-empty">
                        Belum ada lesson yang tersedia.
                    </div>
                    <?php else: ?>
                        <?php foreach ($lessons as $lesson_item): ?>
                            <?php 
                            $lesson_progress = $progress_map[$lesson_item['id']] ?? null;
                            $is_completed = $lesson_progress && $lesson_progress['status'] == 'completed';
                            $is_started = $lesson_progress && $lesson_progress['status'] == 'in_progress';
                            ?>
                            <div class="lesson-item <?php echo $is_completed ? 'completed' : ''; ?>" data-lesson-id="<?php echo $lesson_item['id']; ?>">
                                <div class="lesson-number <?php echo $is_completed ? 'completed' : ''; ?>">
                                    <?php if ($is_completed): ?>
                                        <span>✓</span>
                                    <?php else: ?>
                                        <?php echo $lesson_item['urutan']; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="lesson-content">
                                    <div class="lesson-title">
                                        <?php echo htmlspecialchars($lesson_item['judul_lesson']); ?>
                                        <?php if ($is_completed): ?>
                                            <span class="lesson-badge completed-badge">Selesai</span>
                                        <?php elseif ($is_started): ?>
                                            <span class="lesson-badge progress-badge">Sedang Dikerjakan</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="lesson-meta">
                                        <span class="lesson-type-badge"><?php echo ucfirst($lesson_item['tipe']); ?></span>
                                        <span class="lesson-duration">⏱️ <?php echo $lesson_item['durasi_menit']; ?> menit</span>
                                        <?php if ($lesson_item['xp_reward'] ?? 0): ?>
                                            <span class="lesson-xp">⭐ +<?php echo $lesson_item['xp_reward']; ?> XP</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="lesson-action">
                                    <?php if ($is_enrolled): ?>
                                        <a href="lesson.php?course_id=<?php echo $course_id; ?>&lesson_id=<?php echo $lesson_item['id']; ?>" 
                                           class="<?php echo $is_started || $is_completed ? 'btn-continue' : 'btn-start'; ?>">
                                            <?php if ($is_started): ?>
                                                <?php icon('play', 14); ?> Lanjutkan
                                            <?php elseif ($is_completed): ?>
                                                <?php icon('refresh', 14); ?> Review
                                            <?php else: ?>
                                                <?php icon('play', 14); ?> Mulai
                                            <?php endif; ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="lesson-locked">Daftar untuk mengakses</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/loading.php'; ?>
    <?php include 'includes/toast.php'; ?>

    <script src="assets/js/navbar.js"></script>
    <script>
        // Animate progress bar on load
        document.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.querySelector('.progress-bar-fill');
            if (progressBar) {
                const targetWidth = progressBar.dataset.progress || 0;
                progressBar.style.width = '0%';
                setTimeout(() => {
                    progressBar.style.width = targetWidth + '%';
                }, 300);
            }

            // Add click animation to lesson items
            const lessonItems = document.querySelectorAll('.lesson-item');
            lessonItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    // Only animate if clicking on the item itself, not on links
                    if (e.target.tagName !== 'A' && !e.target.closest('a')) {
                        const link = item.querySelector('a');
                        if (link) {
                            link.click();
                        }
                    }
                });
            });

            // Add hover effects to stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px) scale(1.02)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });

        // Realtime progress updates (optional - can be enabled if needed)
        <?php if ($is_enrolled && $enrollment_data): ?>
        (function() {
            const courseId = <?php echo $course_id; ?>;
            let lastProgress = <?php echo $enrollment_data['progress_percent']; ?>;
            
            async function updateProgress() {
                try {
                    const response = await fetch(`api/get-course-progress.php?course_id=${courseId}`);
                    const result = await response.json();
                    
                    if (result.success && result.data) {
                        const newProgress = result.data.progress_percent || 0;
                        if (Math.abs(newProgress - lastProgress) > 0.1) {
                            // Update progress bar
                            const progressBar = document.querySelector('.progress-bar-fill');
                            const progressText = document.querySelector('.progress-text');
                            const progressBadge = document.querySelector('.progress-badge');
                            
                            if (progressBar) {
                                progressBar.style.width = newProgress + '%';
                                progressBar.dataset.progress = newProgress;
                            }
                            
                            if (progressText) {
                                progressText.innerHTML = `<strong>${result.data.completed_lessons}</strong> dari <strong>${result.data.total_lessons}</strong> lessons selesai`;
                            }
                            
                            if (progressBadge) {
                                progressBadge.textContent = Math.round(newProgress) + '%';
                            }
                            
                            lastProgress = newProgress;
                        }
                    }
                } catch (error) {
                    console.error('Error updating progress:', error);
                }
            }
            
            // Update every 10 seconds if page is visible
            let updateInterval;
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    if (updateInterval) clearInterval(updateInterval);
                } else {
                    updateProgress();
                    updateInterval = setInterval(updateProgress, 10000);
                }
            });
            
            // Initial update after 5 seconds
            setTimeout(updateProgress, 5000);
        })();
        <?php endif; ?>
    </script>
</body>
</html>

