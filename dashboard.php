<?php
require_once 'config/config.php';
requireLogin();
require_once 'includes/icons.php';
require_once 'includes/language-icons.php';
require_once 'includes/rpg_system.php';

$page_title       = 'Dashboard';
$page_description = 'Dashboard pembelajaran coding Prozone.';
$page_css         = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'rpg-system.css'];
$body_class       = getThemeClass();
require_once 'models/User.php';
require_once 'models/Course.php';
require_once 'models/Enrollment.php';
require_once 'models/UserProgress.php';

$database = new Database();
$db = $database->getConnection();

// Get dashboard data based on user role
$role = $_SESSION['user_role'];

$course = new Course($db);
$enrollment = new Enrollment($db);

// Time-based greeting
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

if ($role === 'student') {
    // Get student stats
    $total_courses = $course->getTotalCourses();
    
    // Get enrolled courses
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

    // Get user XP, Level, Character
    $query_user = "SELECT total_xp, level, avatar, character_class FROM users WHERE id = :user_id";
    $stmt_user = $db->prepare($query_user);
    $stmt_user->bindParam(':user_id', $_SESSION['user_id']);
    $stmt_user->execute();
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
    $total_xp = $user_data['total_xp'] ?? 0;
    $level = $user_data['level'] ?? 1;
    $avatar = $user_data['avatar'] ?? null;
    $char_slug = $user_data['character_class'] ?? 'code-warrior';
    $char_data = getClassData($char_slug);
    $next_unlock = getNextUnlock($level, $total_xp);
    
    // Calculate XP progress
    $xp_for_current_level = ($level - 1) * 100;
    $xp_for_next_level = $level * 100;
    $xp_progress = $xp_for_next_level - $xp_for_current_level;
    $xp_current = $total_xp - $xp_for_current_level;
    $xp_percent = $xp_progress > 0 ? ($xp_current / $xp_progress) * 100 : 0;
    
    // Get streak data
    $streakQuery = "SELECT COUNT(DISTINCT DATE(completed_at)) as streak_days
        FROM user_progress 
        WHERE user_id = :user_id 
        AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        AND status = 'completed'";
    $stmt = $db->prepare($streakQuery);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $streakData = $stmt->fetch(PDO::FETCH_ASSOC);
    $streakDays = $streakData['streak_days'] ?? 0;
    
    // Get last active course
    $lastCourseQuery = "SELECT c.*, e.progress_percent, l.judul_lesson as next_lesson, l.id as next_lesson_id
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        LEFT JOIN lessons l ON l.course_id = c.id 
            AND l.id NOT IN (SELECT lesson_id FROM user_progress WHERE user_id = :user_id AND status = 'completed')
        WHERE e.user_id = :user_id AND e.status = 'in_progress'
        ORDER BY e.enrolled_at DESC
        LIMIT 1";
    $stmt = $db->prepare($lastCourseQuery);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $lastCourse = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($role === 'admin' || $role === 'instructor') {
    // Get course stats
    $total_courses = $course->getTotalCourses();
    
    // Get total students
    $query_students = "SELECT COUNT(DISTINCT user_id) as total FROM enrollments";
    $stmt_students = $db->prepare($query_students);
    $stmt_students->execute();
    $students_data = $stmt_students->fetch(PDO::FETCH_ASSOC);
    $total_students = $students_data['total'] ?? 0;
    
    // Get total users
    $query_all_users = "SELECT COUNT(*) as total FROM users WHERE role = 'student'";
    $stmt_all_users = $db->prepare($query_all_users);
    $stmt_all_users->execute();
    $all_users_data = $stmt_all_users->fetch(PDO::FETCH_ASSOC);
    $total_users = $all_users_data['total'] ?? 0;
    
    // Get total lessons
    $query_lessons = "SELECT COUNT(*) as total FROM lessons";
    $stmt_lessons = $db->prepare($query_lessons);
    $stmt_lessons->execute();
    $lessons_data = $stmt_lessons->fetch(PDO::FETCH_ASSOC);
    $total_lessons = $lessons_data['total'] ?? 0;
    
    // Get recent enrollments
    $query_recent = "SELECT e.*, c.judul_course, u.nama_lengkap, u.avatar
                     FROM enrollments e
                     JOIN courses c ON e.course_id = c.id
                     JOIN users u ON e.user_id = u.id
                     ORDER BY e.enrolled_at DESC LIMIT 5";
    $stmt_recent = $db->prepare($query_recent);
    $stmt_recent->execute();
    $recent_enrollments = [];
    while ($row = $stmt_recent->fetch(PDO::FETCH_ASSOC)) {
        $recent_enrollments[] = $row;
    }
    
    // Get admin user data
    $query_user = "SELECT total_xp, level, avatar, character_class FROM users WHERE id = :user_id";
    $stmt_user = $db->prepare($query_user);
    $stmt_user->bindParam(':user_id', $_SESSION['user_id']);
    $stmt_user->execute();
    $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
    $level = $user_data['level'] ?? 1;
    $avatar = $user_data['avatar'] ?? null;
    $char_slug = $user_data['character_class'] ?? 'code-warrior';
    $char_data = getClassData($char_slug);
    $total_xp = $user_data['total_xp'] ?? 0;
    $next_unlock = getNextUnlock($level, $total_xp);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once 'includes/head.php'; ?>
</head>
<body class="<?php echo $body_class; ?>">
    <?php require_once 'navbar.php'; ?>

    <div class="page-wrapper dashboard-main-container">
        <div class="dashboard-content">
            <!-- Premium Hero Section -->
            <div class="glass-header premium-hero">
                <div class="hero-text-content">
                    <div class="hero-badge">
                        <span class="hero-badge-dot"></span>
                        Platform Coding #1 untuk Pemula
                    </div>
                    <h1>Belajar Coding dengan Cara <br><span class="text-accent-gradient">Menyenangkan</span></h1>
                    <p class="hero-subtitle">
                        Platform pembelajaran coding interaktif dengan clan, leaderboard, achievement, dan code editor langsung di browser. Tingkatkan skill programming Anda sambil bersenang-senang!
                    </p>
                    <div class="hero-actions">
                        <a href="courses.php" class="glass-btn glass-btn-primary glass-btn-lg">Mulai Belajar Gratis →</a>
                        <a href="courses.php" class="glass-btn glass-btn-secondary glass-btn-lg">
                            Lihat Kursus <?php icon('play', 14); ?>
                        </a>
                    </div>
                </div>
                <div class="hero-illustration hide-mobile">
                    <!-- Placeholder for illustration - in a real app this would be an SVG or 3D asset -->
                    <div class="code-terminal-mockup illustration-mockup">
                        <div class="terminal-header">
                            <span class="dot red"></span>
                            <span class="dot yellow"></span>
                            <span class="dot green"></span>
                        </div>
                        <div class="terminal-body">
                            <div class="line"><span class="cmd">></span> npm start</div>
                            <div class="line success">✓ Server running...</div>
                            <div class="line">✓ Ready to code!</div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($role === 'student'): ?>
            

            <!-- Stats Grid -->
            <div class="glass-stats-grid mb-10">
                <div class="glass-stat-card">
                    <div class="stat-icon-wrapper bg-accent-subtle">
                        <?php icon('book', 24, 'text-accent'); ?>
                    </div>
                    <div class="stat-info">
                        <div class="glass-stat-label">KURSUS</div>
                        <div class="glass-stat-value"><?php echo number_format($total_courses ?? 0); ?>+</div>
                        <div class="stat-desc">Kursus Interaktif</div>
                    </div>
                </div>
                <div class="glass-stat-card">
                    <div class="stat-icon-wrapper bg-primary-subtle">
                        <?php icon('users', 24, 'text-primary'); ?>
                    </div>
                    <div class="stat-info">
                        <div class="glass-stat-label">STUDENTS</div>
                        <div class="glass-stat-value"><?php echo number_format($total_enrolled ?? 0); ?>+</div>
                        <div class="stat-desc">Pelajar Aktif</div>
                    </div>
                </div>
                <div class="glass-stat-card">
                    <div class="stat-icon-wrapper bg-warning-subtle">
                        <?php icon('award', 24, 'text-warning'); ?>
                    </div>
                    <div class="stat-info">
                        <div class="glass-stat-label">INSTRUCTORS</div>
                        <div class="glass-stat-value">50+</div>
                        <div class="stat-desc">Instruktur Ahli</div>
                    </div>
                </div>
            </div>

            <!-- Continue Learning -->
            <?php if (!empty($lastCourse)): ?>
            <div class="glass-card mb-8">
                <div class="glass-card-content flex items-center justify-between gap-4 flex-wrap">
                    <div class="continue-info flex items-center gap-4">
                        <div class="continue-icon w-12 h-12 rounded-lg bg-brand flex items-center justify-center text-white"><?php icon('play', 22); ?></div>
                        <div class="continue-text">
                            <h3 class="text-lg font-bold text-white mb-1"><?php echo htmlspecialchars($lastCourse['judul_course']); ?></h3>
                            <p class="text-sm text-gray-300">Progress: <span class="text-brand font-bold"><?php echo number_format($lastCourse['progress_percent'], 0); ?>%</span></p>
                        </div>
                    </div>
                    <a href="course.php?id=<?php echo $lastCourse['id']; ?>" class="glass-btn glass-btn-primary glass-btn-lg">
                        <?php icon('play', 16); ?> Lanjutkan
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php endif; ?>

            <?php if ($role === 'admin' || $role === 'instructor'): ?>
            
            <!-- Admin Stats -->
            <div class="dash-stats-grid">
                <div class="dash-stat-card">
                    <div class="dash-stat-header">
                        <div class="dash-stat-icon icon-brand"><?php icon('book', 18); ?></div>
                    </div>
                    <div class="dash-stat-value"><?php echo $total_courses ?? 0; ?></div>
                    <div class="dash-stat-label">Total Kursus</div>
                </div>
                <div class="dash-stat-card">
                    <div class="dash-stat-header">
                        <div class="dash-stat-icon icon-info"><?php icon('users', 18); ?></div>
                    </div>
                    <div class="dash-stat-value"><?php echo $total_students ?? 0; ?></div>
                    <div class="dash-stat-label">Siswa Aktif</div>
                </div>
                <div class="dash-stat-card">
                    <div class="dash-stat-header">
                        <div class="dash-stat-icon icon-accent"><?php icon('user', 18); ?></div>
                    </div>
                    <div class="dash-stat-value"><?php echo $total_users ?? 0; ?></div>
                    <div class="dash-stat-label">Total User</div>
                </div>
                <div class="dash-stat-card">
                    <div class="dash-stat-header">
                        <div class="dash-stat-icon icon-warning"><?php icon('book-open', 18); ?></div>
                    </div>
                    <div class="dash-stat-value"><?php echo $total_lessons ?? 0; ?></div>
                    <div class="dash-stat-label">Total Lesson</div>
                </div>
            </div>

            <?php endif; ?>

            <!-- Secondary Section: Learning & Challenges -->
            <div class="dashboard-grid-secondary">
                <!-- Lanjutkan Belajar -->
                <div class="learning-section-card">
                    <div class="section-title-row">
                        <h3>Lanjutkan Belajar</h3>
                        <a href="courses.php" class="view-all-link">Lihat Semua</a>
                    </div>
                    
                    <div class="learning-tracks-list">
                        <?php if (empty($enrolled_courses)): ?>
                            <div class="glass-empty-state">
                                <p>Cari kursus pertamamu!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($enrolled_courses, 0, 2) as $course_item): ?>
                            <div class="course-item-horizontal">
                                <div class="course-logo-circle">
                                    <?php $logo = getLanguageIcon($course_item['judul_course']); if ($logo): ?>
                                    <img src="<?php echo $logo; ?>" alt="" style="width: 32px;">
                                    <?php else: ?>
                                    <?php icon('code', 24, 'text-primary'); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="course-mini-info">
                                    <a href="course.php?id=<?php echo $course_item['course_id']; ?>" class="track-name">
                                        <?php echo htmlspecialchars($course_item['judul_course']); ?>
                                    </a>
                                    <div class="progress-container-mini">
                                        <div class="progress-bar-bg">
                                            <div class="progress-bar-fill" style="width: <?php echo $course_item['progress_percent']; ?>%"></div>
                                        </div>
                                        <span class="progress-label"><?php echo number_format($course_item['progress_percent'], 0); ?>%</span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Tantangan Harian Placeholder -->
                        <div class="section-title-row mt-8">
                            <h3>Tantangan Harian <span class="glass-badge glass-badge-primary ml-2" style="font-size: 8px;">New</span></h3>
                        </div>
                        <div class="course-item-horizontal">
                            <div class="course-logo-circle">
                                <?php icon('code', 24, 'text-primary'); ?>
                            </div>
                            <div class="course-mini-info">
                                <span class="track-name">Two Sum</span>
                                <div class="text-xs text-muted">Selesaikan algoritma Two Sum</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RPG Character Profile Card -->
                <?php
                $xp_for_next_lvl = ($level) * 100;
                $xp_prev_lvl = ($level - 1) * 100;
                $xp_in_lvl = $total_xp - $xp_prev_lvl;
                $xp_needed = $xp_for_next_lvl - $xp_prev_lvl;
                $lvl_pct = $xp_needed > 0 ? min(100, ($xp_in_lvl / $xp_needed) * 100) : 100;
                $ring_offset = 314 - ($lvl_pct / 100) * 314;
                $next_xp_pct = 0;
                if ($next_unlock) {
                    $next_xp_pct = $next_unlock['xp_required'] > 0
                        ? min(100, ($total_xp / $next_unlock['xp_required']) * 100)
                        : 100;
                }
                ?>
                <div class="rpg-profile-card" style="--card-gradient:<?php echo $char_data['gradient']; ?>">
                    <!-- XP Ring + Avatar -->
                    <div class="rpg-xp-ring-wrapper">
                        <svg class="rpg-xp-ring-svg" viewBox="0 0 44 44">
                            <defs>
                                <linearGradient id="epicGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#6C4CFD"/>
                                    <stop offset="100%" stop-color="#EC4899"/>
                                </linearGradient>
                                <linearGradient id="legendaryGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#FF6B35"/>
                                    <stop offset="50%" stop-color="#8B5CF6"/>
                                    <stop offset="100%" stop-color="#06B6D4"/>
                                </linearGradient>
                            </defs>
                            <circle class="rpg-ring-bg" cx="22" cy="22" r="18"/>
                            <circle class="rpg-ring-fill rarity-fill-<?php echo $char_data['rarity']; ?>" cx="22" cy="22" r="18"
                                style="stroke-dashoffset:<?php echo $ring_offset; ?>"/>
                        </svg>
                        <div class="rpg-avatar-circle">
                            <img src="<?php echo htmlspecialchars($char_data['image']); ?>" alt="<?php echo htmlspecialchars($char_data['name']); ?>">
                        </div>
                    </div>

                    <!-- Class Info -->
                    <div class="rpg-class-badge"><?php echo $char_data['badge']; ?></div>
                    <div class="rpg-class-name"><?php echo htmlspecialchars($char_data['name']); ?></div>
                    <div class="rpg-class-title"><?php echo htmlspecialchars($char_data['title']); ?></div>
                    <span class="rarity-badge rarity-<?php echo $char_data['rarity']; ?>">✦ <?php echo $char_data['rarity_label']; ?></span>

                    <!-- Stats -->
                    <div class="rpg-stats-row">
                        <div class="rpg-stat-pill lvl">⭐ Lv.<?php echo $level; ?></div>
                        <div class="rpg-stat-pill xp">⚡ <?php echo number_format($total_xp); ?> XP</div>
                    </div>

                    <!-- Next unlock preview -->
                    <?php if ($next_unlock): ?>
                    <div class="rpg-next-unlock">
                        <div class="rpg-next-label"><?php echo $next_unlock['badge']; ?> Next: <?php echo htmlspecialchars($next_unlock['name']); ?></div>
                        <div class="rpg-next-bar-bg">
                            <div class="rpg-next-bar-fill" style="width:<?php echo $next_xp_pct; ?>%"></div>
                        </div>
                        <div class="rpg-next-hint"><?php echo number_format($total_xp); ?> / <?php echo number_format($next_unlock['xp_required']); ?> XP · Lv.<?php echo $next_unlock['level_required']; ?> required</div>
                    </div>
                    <?php else: ?>
                    <div class="rpg-next-unlock" style="text-align:center">
                        <div class="rpg-next-label" style="justify-content:center">🏆 Max Class Reached!</div>
                    </div>
                    <?php endif; ?>

                    <a href="characters.php" class="glass-btn glass-btn-primary w-full" style="margin-top:16px;justify-content:center;">⚔️ Lihat Koleksi Karakter</a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <?php include 'includes/loading.php'; ?>
    <?php include 'includes/toast.php'; ?>

    <script src="assets/js/navbar.js"></script>
</body>
</html>
