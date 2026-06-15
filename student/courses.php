<?php
require_once '../config/config.php';
requireLogin();
require_once '../includes/icons.php';

$page_title       = 'Courses';
$page_description = 'Jelajahi kursus coding interaktif Prozone.';
$page_css         = ['pages/courses.css', 'sidebar-island.css', 'dashboard-override.css', 'shared.css'];
$body_class       = trim(getThemeClass() . ' dashboard-layout');
require_once '../models/Course.php';
require_once '../models/CourseCategory.php';
require_once '../models/Enrollment.php';

$database = new Database();
$db = $database->getConnection();

$course = new Course($db);
$category = new CourseCategory($db);
$enrollment = new Enrollment($db);

$stmt_categories = $category->getAll();
$categories = [];
$all_count = $course->getTotalCourses();
while ($row = $stmt_categories->fetch(PDO::FETCH_ASSOC)) {
    $categories[] = $row;
}

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

$stmt = $course->getAll($category_id, $search);
$courses = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $courses[] = $row;
}

$enrolledIds = [];
$enrollStmt = $enrollment->getUserEnrollments($_SESSION['user_id']);
while ($erow = $enrollStmt->fetch(PDO::FETCH_ASSOC)) {
    $enrolledIds[$erow['course_id']] = $erow;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/head.php'; ?>
</head>
<body class="<?php echo $body_class; ?>">
<?php require_once 'navbar.php'; ?>

<div class="page-wrapper">
    <div class="dashboard-content">
        <div class="dashboard-header">
            <div>
                <h1><?php icon('book', 24); ?> Jelajahi Kursus</h1>
                <p class="greeting-sub">Temukan kursus coding yang sesuai dengan minatmu</p>
            </div>
            <div class="dash-header-actions">
                <a href="../learning-path.php" class="btn btn-secondary">
                    <?php icon('map', 16); ?> Learning Path
                </a>
            </div>
        </div>

        <!-- Categories -->
        <div class="categories-filter">
            <a href="courses.php" class="category-chip <?php echo !$category_id ? 'active' : ''; ?>">
                Semua <span class="chip-count"><?php echo $all_count; ?></span>
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="courses.php?category_id=<?php echo $cat['id']; ?>" class="category-chip <?php echo $category_id == $cat['id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                    <span class="chip-count"><?php echo $cat['course_count'] ?? 0; ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Course Grid -->
        <div class="courses-grid">
            <?php if (count($courses) > 0): ?>
                <?php foreach ($courses as $courseItem): 
                    $isEnrolled = isset($enrolledIds[$courseItem['id']]);
                    $progress = $isEnrolled ? ($enrolledIds[$courseItem['id']]['progress_percent'] ?? 0) : 0;
                    $status = $isEnrolled ? ($enrolledIds[$courseItem['id']]['status'] ?? '') : '';
                    $lessonCount = $courseItem['lesson_count'] ?? 0;
                ?>
                    <div class="course-card">
                        <div class="course-card-body">
                            <div class="course-icon-wrapper">
                                <?php 
                                $iconMap = ['html' => '&#128187;', 'css' => '&#127912;', 'js' => '&#9889;', 'php' => '&#128220;', 'database' => '&#128451;', 'default' => '&#128218;'];
                                $iconChar = $iconMap[$courseItem['slug'] ?? 'default'] ?? $iconMap['default'];
                                ?>
                                <span class="course-icon"><?php echo $iconChar; ?></span>
                            </div>
                            <h3 class="course-title"><?php echo htmlspecialchars($courseItem['title']); ?></h3>
                            <p class="course-desc"><?php echo htmlspecialchars(substr($courseItem['description'] ?? '', 0, 100)); ?>...</p>
                            <div class="course-meta">
                                <span><?php icon('file-text', 14); ?> <?php echo $lessonCount; ?> Pelajaran</span>
                                <span><?php icon('users', 14); ?> <?php echo $courseItem['total_students'] ?? 0; ?></span>
                            </div>
                            <?php if ($isEnrolled): ?>
                                <div class="course-progress-mini">
                                    <div class="cpi-bar">
                                        <div class="cpi-bar-track">
                                            <div class="cpi-bar-fill" style="width:<?php echo $progress; ?>%"></div>
                                        </div>
                                        <span class="cpi-pct"><?php echo $progress; ?>%</span>
                                    </div>
                                    <?php if ($status === 'completed'): ?>
                                        <span class="completion-badge">Selesai</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="course-card-footer">
                            <?php if ($isEnrolled): ?>
                                <a href="../course.php?id=<?php echo $courseItem['id']; ?>" class="btn btn-primary btn-sm">
                                    <?php icon('arrow-right', 14); ?> Lanjutkan
                                </a>
                            <?php else: ?>
                                <a href="../course.php?id=<?php echo $courseItem['id']; ?>" class="btn btn-outline btn-sm">
                                    <?php icon('info', 14); ?> Detail
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <?php icon('book-open', 48); ?>
                    <h3>Tidak ada kursus ditemukan</h3>
                    <p>Coba ubah filter atau cari kata kunci lain.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
