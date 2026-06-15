<?php
require_once '../config/config.php';
requireLogin();
require_once '../includes/icons.php';
require_once '../includes/language-icons.php';
require_once '../includes/rpg_system.php';

$page_title       = 'Dashboard Student';
$page_description = 'Dashboard pembelajaran coding Prozone.';
$page_css         = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'pages/admin.css', 'rpg-system.css'];
$body_class       = trim(getThemeClass() . ' dashboard-layout');
require_once '../models/User.php';
require_once '../models/Course.php';
require_once '../models/Enrollment.php';
require_once '../models/UserProgress.php';

$database = new Database();
$db = $database->getConnection();

$role = $_SESSION['user_role'];

if ($role !== 'student') {
    header('Location: ../dashboard.php');
    exit;
}

$course = new Course($db);
$enrollment = new Enrollment($db);

$hour = date('H');
if ($hour < 12) {
    $greeting = 'Selamat Pagi';
    $greeting_icon = 'sun';
} elseif ($hour < 17) {
    $greeting = 'Selamat Siang';
    $greeting_icon = 'sun';
} else {
    $greeting = 'Selamat Malam';
    $greeting_icon = 'moon';
}

$total_courses = $course->getTotalCourses();
$enrollment_stmt = $enrollment->getUserEnrollments($_SESSION['user_id']);
$enrolled_courses = [];
$total_progress = 0;
$completed_courses = 0;

while ($row = $enrollment_stmt->fetch(PDO::FETCH_ASSOC)) {
    $enrolled_courses[] = $row;
    $total_progress += $row['progress_percent'];
    if ($row['status'] == 'completed') {
        $completed_courses++;
    }
}

$avg_progress = count($enrolled_courses) > 0 ? $total_progress / count($enrolled_courses) : 0;
$total_enrolled = count($enrolled_courses);

$userModel = new User($db);
$userModel->id = $_SESSION['user_id'];
$userModel->readOne();
$total_xp = $userModel->xp ?? 0;
$level = $userModel->level ?? 1;
$avatar = $userModel->avatar ?? null;
$char_slug = $userModel->character_class ?? 'code-warrior';
$char_data = getClassData($char_slug);
$next_unlock = getNextUnlock($level, $total_xp);
$xp_percent = getLevelProgress($level, $total_xp);

$streakStmt = $db->prepare("SELECT streak_days FROM user_progress WHERE user_id = :uid ORDER BY updated_at DESC LIMIT 1");
$streakStmt->bindParam(':uid', $_SESSION['user_id']);
$streakStmt->execute();
$streakDays = (int) $streakStmt->fetchColumn();

$lastCourseStmt = $db->prepare("SELECT c.title, up.progress_percent, c.id FROM user_progress up JOIN enrollments e ON up.enrollment_id = e.id JOIN courses c ON e.course_id = c.id WHERE up.user_id = :uid ORDER BY up.updated_at DESC LIMIT 1");
$lastCourseStmt->bindParam(':uid', $_SESSION['user_id']);
$lastCourseStmt->execute();
$lastCourse = $lastCourseStmt->fetch(PDO::FETCH_ASSOC);

$totalAchievements = 0;
$achStmt = $db->prepare("SELECT COUNT(*) FROM user_achievements WHERE user_id = :uid");
$achStmt->bindParam(':uid', $_SESSION['user_id']);
$achStmt->execute();
$totalAchievements = (int) $achStmt->fetchColumn();

$top_percent = 50;
$rankStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE xp > (SELECT xp FROM users WHERE id = :uid)");
$rankStmt->bindParam(':uid', $_SESSION['user_id']);
$rankStmt->execute();
$better_than = (int) $rankStmt->fetchColumn();
$totalUsers = 0;
$totalUsersStmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
$totalUsers = (int) $totalUsersStmt->fetchColumn();
if ($totalUsers > 0) {
    $top_percent = round(($better_than / $totalUsers) * 100);
}
$userRank = $better_than + 1;

$recentActivities = [];
$actStmt = $db->prepare("SELECT * FROM activity_log WHERE user_id = :uid ORDER BY created_at DESC LIMIT 5");
$actStmt->bindParam(':uid', $_SESSION['user_id']);
$actStmt->execute();
$recentActivities = $actStmt->fetchAll(PDO::FETCH_ASSOC);
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
            <div class="dash-greeting">
                <span class="greeting-icon"><?php icon($greeting_icon, 24); ?></span>
                <div>
                    <h1><?php echo $greeting; ?>, <?php echo htmlspecialchars(explode(' ', $_SESSION['nama_lengkap'] ?? 'Student')[0]); ?> 👋</h1>
                    <p class="greeting-sub">Lanjutkan petualangan coding-mu hari ini!</p>
                </div>
            </div>
            <div class="dash-header-actions">
                <a href="../courses.php" class="btn btn-primary">
                    <?php icon('book', 16); ?> Jelajahi Kursus
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-icon-enrolled">
                    <?php icon('book', 20); ?>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo $total_enrolled; ?></span>
                    <span class="stat-label">Kursus Diikuti</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-completed">
                    <?php icon('check-circle', 20); ?>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo $completed_courses; ?></span>
                    <span class="stat-label">Kursus Selesai</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-progress">
                    <?php icon('trending-up', 20); ?>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo round($avg_progress); ?>%</span>
                    <span class="stat-label">Rata-rata Progress</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-achievement">
                    <?php icon('award', 20); ?>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo $totalAchievements; ?></span>
                    <span class="stat-label">Achievement</span>
                </div>
            </div>
        </div>

        <div class="dashboard-grid-2col">
            <!-- Enrolled Courses -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><?php icon('book', 18); ?> Kursus Saya</h3>
                    <a href="../courses.php" class="dash-card-link">Lihat Semua</a>
                </div>
                <div class="dash-card-body">
                    <?php if (count($enrolled_courses) > 0): ?>
                        <?php foreach (array_slice($enrolled_courses, 0, 3) as $ec): ?>
                            <div class="course-progress-item">
                                <div class="cpi-info">
                                    <span class="cpi-title"><?php echo htmlspecialchars($ec['title'] ?? 'Course'); ?></span>
                                    <span class="cpi-status <?php echo $ec['status'] ?? ''; ?>"><?php echo ucfirst($ec['status'] ?? 'active'); ?></span>
                                </div>
                                <div class="cpi-bar">
                                    <div class="cpi-bar-track">
                                        <div class="cpi-bar-fill" style="width:<?php echo $ec['progress_percent'] ?? 0; ?>%"></div>
                                    </div>
                                    <span class="cpi-pct"><?php echo $ec['progress_percent'] ?? 0; ?>%</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="dash-empty">
                            <p>Belum ada kursus yang diikuti.</p>
                            <a href="../courses.php" class="btn btn-primary btn-sm">Mulai Belajar</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h3><?php icon('activity', 18); ?> Aktivitas Terbaru</h3>
                </div>
                <div class="dash-card-body">
                    <?php if (count($recentActivities) > 0): ?>
                        <?php foreach ($recentActivities as $act): ?>
                            <div class="activity-item">
                                <span class="activity-icon"><?php icon('circle', 10); ?></span>
                                <div class="activity-info">
                                    <span class="activity-text"><?php echo htmlspecialchars($act['activity'] ?? ''); ?></span>
                                    <span class="activity-time"><?php echo htmlspecialchars($act['created_at'] ?? ''); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="dash-empty">
                            <p>Belum ada aktivitas.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Continue Learning -->
        <?php if ($lastCourse): ?>
        <div class="dash-card continue-card">
            <div class="dash-card-header">
                <h3><?php icon('play', 18); ?> Lanjutkan Belajar</h3>
            </div>
            <div class="dash-card-body">
                <div class="continue-content">
                    <div class="continue-info">
                        <h4><?php echo htmlspecialchars($lastCourse['title']); ?></h4>
                        <div class="cpi-bar">
                            <div class="cpi-bar-track">
                                <div class="cpi-bar-fill" style="width:<?php echo $lastCourse['progress_percent']; ?>%"></div>
                            </div>
                            <span class="cpi-pct"><?php echo $lastCourse['progress_percent']; ?>%</span>
                        </div>
                    </div>
                    <a href="../course.php?id=<?php echo $lastCourse['id']; ?>" class="btn btn-primary">
                        <?php icon('arrow-right', 16); ?> Lanjutkan
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
