<?php
require_once 'config/config.php';
requireLogin();
require_once 'includes/icons.php';
require_once 'includes/language-icons.php';
require_once 'includes/rpg_system.php';

$page_title       = 'Dashboard';
$page_description = 'Dashboard pembelajaran coding Prozone.';
$page_css         = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'pages/admin.css', 'rpg-system.css'];
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

if ($role === 'admin') {
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
    
    // Get total clans
    $query_clans = "SELECT COUNT(*) as total FROM clans";
    $stmt_clans = $db->prepare($query_clans);
    $stmt_clans->execute();
    $clans_data = $stmt_clans->fetch(PDO::FETCH_ASSOC);
    $total_clans = $clans_data['total'] ?? 0;
    
    // Get total completions
    $query_completions = "SELECT COUNT(*) as total FROM enrollments WHERE status = 'completed'";
    $stmt_completions = $db->prepare($query_completions);
    $stmt_completions->execute();
    $completions_data = $stmt_completions->fetch(PDO::FETCH_ASSOC);
    $total_completions = $completions_data['total'] ?? 0;
    
    // Get total achievements
    $total_achievements = $db->query("SELECT COUNT(*) FROM user_achievements")->fetchColumn();
    
    // Get total certificates
    $total_certificates = $db->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
    
    // Get total comments
    $total_comments = $db->query("SELECT COUNT(*) FROM comments")->fetchColumn();
    
    // User growth last 30 days
    $user_growth = [];
    $stmt_ug = $db->query("SELECT DATE(created_at) as date, COUNT(*) as total FROM users WHERE role = 'student' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY date");
    $growth_data = [];
    while ($row = $stmt_ug->fetch(PDO::FETCH_ASSOC)) {
        $growth_data[$row['date']] = (int)$row['total'];
    }
    $start = new DateTime('-30 days');
    $end = new DateTime();
    $cumulative = 0;
    $initial_users = $db->query("SELECT COUNT(*) FROM users WHERE role = 'student' AND created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
    $cumulative = (int)$initial_users;
    for ($d = clone $start; $d <= $end; $d->modify('+1 day')) {
        $date = $d->format('Y-m-d');
        $cumulative += $growth_data[$date] ?? 0;
        $user_growth[] = ['date' => $date, 'total' => $cumulative];
    }
    
    // Popular courses (top 5)
    $popular_courses = [];
    $stmt_pc = $db->query("SELECT c.judul_course, COUNT(e.id) as enrollment_count FROM courses c LEFT JOIN enrollments e ON c.id = e.course_id GROUP BY c.id ORDER BY enrollment_count DESC LIMIT 5");
    while ($row = $stmt_pc->fetch(PDO::FETCH_ASSOC)) {
        $popular_courses[] = $row;
    }
    
    // Enrollment trends last 30 days
    $enrollment_trends = [];
    $stmt_et = $db->query("SELECT DATE(enrolled_at) as date, COUNT(*) as total FROM enrollments WHERE enrolled_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(enrolled_at) ORDER BY date");
    $et_data = [];
    while ($row = $stmt_et->fetch(PDO::FETCH_ASSOC)) {
        $et_data[$row['date']] = (int)$row['total'];
    }
    for ($d = clone $start; $d <= $end; $d->modify('+1 day')) {
        $date = $d->format('Y-m-d');
        $enrollment_trends[] = ['date' => $date, 'total' => $et_data[$date] ?? 0];
    }
    
    // Recent enrollments
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
    
    // Recent registrations
    $query_new_users = "SELECT id, nama_lengkap, username, email, created_at 
                        FROM users WHERE role = 'student' 
                        ORDER BY created_at DESC LIMIT 5";
    $stmt_new_users = $db->prepare($query_new_users);
    $stmt_new_users->execute();
    $recent_users = [];
    while ($row = $stmt_new_users->fetch(PDO::FETCH_ASSOC)) {
        $recent_users[] = $row;
    }
    
    // Recent activity log
    $recent_activities = [];
    $stmt_act = $db->query("SELECT al.*, u.nama_lengkap FROM activity_log al JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 5");
    while ($row = $stmt_act->fetch(PDO::FETCH_ASSOC)) {
        $recent_activities[] = $row;
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
    <style>
        .dashboard-grid-secondary.mb-8 { margin-bottom:2rem; }
        .mb-8 { margin-bottom:2rem; }
        .admin-stats-mini-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .mini-stat { background:var(--bg-subtle); border-radius:var(--radius-md); padding:1rem; text-align:center; }
        .mini-stat-value { font-size:1.5rem; font-weight:700; color:var(--brand); }
        .mini-stat-label { font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem; }
        canvas { max-height:200px; }
    </style>
</head>
<body class="<?php echo trim($body_class . ' dashboard-layout'); ?>">
    <?php require_once 'navbar.php'; ?>

    <div class="page-wrapper dashboard-main-container">
        <div class="dashboard-content">
            <?php if ($role === 'student'): ?>

            <!-- Premium Hero Section -->
            <div class="glass-header premium-hero">
                <div class="hero-text-content">
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:20px;">
                        <div class="hero-badge">
                            <span class="hero-badge-dot"></span>
                            Platform Coding #1 untuk Pemula
                        </div>
                        <?php if ($streakDays > 0): ?>
                        <div class="streak-badge">
                            <span class="streak-fire">🔥</span>
                            Streak <span class="streak-count"><?php echo $streakDays; ?> hari</span>
                        </div>
                        <?php endif; ?>
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

            <!-- Stats Row 2: Mini Stats -->
            <div class="stats-row-2">
                <div class="stat-mini-card">
                    <div class="stat-mini-icon purple">📚</div>
                    <div class="stat-mini-body">
                        <div class="stat-mini-label">Belajar</div>
                        <div class="stat-mini-value"><?php echo number_format($total_courses ?? 0); ?>+ Kursus</div>
                    </div>
                </div>
                <div class="stat-mini-card">
                    <div class="stat-mini-icon green">📊</div>
                    <div class="stat-mini-body">
                        <div class="stat-mini-label">Progress Rata-rata</div>
                        <div class="stat-mini-value"><?php echo number_format($avg_progress, 0); ?>%</div>
                    </div>
                </div>
                <div class="stat-mini-card">
                    <div class="stat-mini-icon amber">🔥</div>
                    <div class="stat-mini-body">
                        <div class="stat-mini-label">Streak</div>
                        <div class="stat-mini-value"><?php echo $streakDays; ?> Hari</div>
                    </div>
                </div>
                <div class="stat-mini-card">
                    <div class="stat-mini-icon blue">✅</div>
                    <div class="stat-mini-body">
                        <div class="stat-mini-label">Selesai</div>
                        <div class="stat-mini-value"><?php echo $completed_courses; ?> Kursus</div>
                    </div>
                </div>
            </div>

            <!-- Continue Learning -->
            <?php if (!empty($lastCourse)): ?>
            <div class="continue-card">
                <div class="continue-info">
                    <div class="continue-icon-box"><?php icon('play', 26); ?></div>
                    <div class="continue-text">
                        <h3><?php echo htmlspecialchars($lastCourse['judul_course']); ?></h3>
                        <p>Progress: <span class="progress-highlight"><?php echo number_format($lastCourse['progress_percent'], 0); ?>%</span></p>
                    </div>
                </div>
                <a href="course.php?id=<?php echo $lastCourse['id']; ?>" class="glass-btn glass-btn-primary glass-btn-lg">
                    <?php icon('play', 16); ?> Lanjutkan
                </a>
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
                                <span class="empty-icon">🎯</span>
                                <div class="empty-title">Belum ada kursus</div>
                                <div class="empty-sub">Mulai belajar dengan kursus pertama!</div>
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

                        <!-- Tantangan Harian -->
                        <div class="section-title-row" style="margin-top:20px;">
                            <h3>Tantangan Harian <span class="glass-badge glass-badge-primary ml-2" style="font-size:9px;">🔥 Baru</span></h3>
                        </div>
                        <div class="challenge-item-horizontal">
                            <div class="challenge-icon-box">💻</div>
                            <div class="challenge-mini-info">
                                <span class="challenge-name">Two Sum</span>
                                <span class="challenge-desc">Selesaikan algoritma Two Sum</span>
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
                    <div class="rpg-xp-ring-wrapper">
                        <svg class="rpg-xp-ring-svg" viewBox="0 0 44 44">
                            <defs>
                                <linearGradient id="epicGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#3B82F6"/>
                                    <stop offset="100%" stop-color="#EC4899"/>
                                </linearGradient>
                                <linearGradient id="legendaryGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#FF6B35"/>
                                    <stop offset="50%" stop-color="#14B8A6"/>
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

                    <div class="rpg-class-badge"><?php echo $char_data['badge']; ?></div>
                    <div class="rpg-class-name"><?php echo htmlspecialchars($char_data['name']); ?></div>
                    <div class="rpg-class-title"><?php echo htmlspecialchars($char_data['title']); ?></div>
                    <span class="rarity-badge rarity-<?php echo $char_data['rarity']; ?>">✦ <?php echo $char_data['rarity_label']; ?></span>

                    <div class="rpg-stats-row">
                        <div class="rpg-stat-pill lvl">⭐ Lv.<?php echo $level; ?></div>
                        <div class="rpg-stat-pill xp">⚡ <?php echo number_format($total_xp); ?> XP</div>
                    </div>

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

                    <a href="characters.php" class="glass-btn glass-btn-primary w-full" style="margin-top:16px;justify-content:center;">🏆 Lihat Semua Trophy</a>
                </div>
            </div>

            <?php elseif ($role === 'admin'): ?>
            
            <!-- Admin Welcome Banner -->
            <div class="admin-welcome-banner">
                <div class="admin-welcome-bg-shapes"></div>
                <div class="admin-welcome-text">
                    <div class="admin-welcome-badge">
                        <?php icon('shield', 14); ?> ADMIN
                    </div>
                    <h2>Selamat datang, <?php echo htmlspecialchars(explode(' ', $_SESSION['nama_lengkap'] ?? 'Admin')[0]); ?>!</h2>
                    <p>Kelola platform Prozone — kursus, user, clan, dan pantau aktivitas belajar.</p>
                </div>
                <div class="admin-welcome-stats">
                    <div class="welcome-stat">
                        <span class="welcome-stat-value"><?php echo $total_courses ?? 0; ?></span>
                        <span class="welcome-stat-label">Kursus</span>
                    </div>
                    <div class="welcome-stat-divider"></div>
                    <div class="welcome-stat">
                        <span class="welcome-stat-value"><?php echo $total_users ?? 0; ?></span>
                        <span class="welcome-stat-label">Siswa</span>
                    </div>
                    <div class="welcome-stat-divider"></div>
                    <div class="welcome-stat">
                        <span class="welcome-stat-value"><?php echo $total_lessons ?? 0; ?></span>
                        <span class="welcome-stat-label">Lessons</span>
                    </div>
                </div>
            </div>

            <!-- Admin Stats Grid -->
            <div class="admin-stats-grid">
                <div class="admin-stat-card purple">
                    <div class="admin-stat-icon"><?php icon('book', 22); ?></div>
                    <div class="admin-stat-body">
                        <div class="stat-label">Total Kursus</div>
                        <div class="stat-value"><?php echo $total_courses ?? 0; ?></div>
                        <div class="admin-stat-sublabel">Course published</div>
                    </div>
                </div>
                <div class="admin-stat-card blue">
                    <div class="admin-stat-icon"><?php icon('file-text', 22); ?></div>
                    <div class="admin-stat-body">
                        <div class="stat-label">Total Lesson</div>
                        <div class="stat-value"><?php echo $total_lessons ?? 0; ?></div>
                        <div class="admin-stat-sublabel">Materi pembelajaran</div>
                    </div>
                </div>
                <div class="admin-stat-card emerald">
                    <div class="admin-stat-icon"><?php icon('users', 22); ?></div>
                    <div class="admin-stat-body">
                        <div class="stat-label">Total Student</div>
                        <div class="stat-value"><?php echo $total_users ?? 0; ?></div>
                        <div class="admin-stat-sublabel">User terdaftar</div>
                    </div>
                </div>
                <div class="admin-stat-card amber">
                    <div class="admin-stat-icon"><?php icon('zap', 22); ?></div>
                    <div class="admin-stat-body">
                        <div class="stat-label">Total Clan</div>
                        <div class="stat-value"><?php echo $total_clans ?? 0; ?></div>
                        <div class="admin-stat-sublabel">Komunitas aktif</div>
                    </div>
                </div>
                <div class="admin-stat-card rose">
                    <div class="admin-stat-icon"><?php icon('award', 22); ?></div>
                    <div class="admin-stat-body">
                        <div class="stat-label">Kursus Selesai</div>
                        <div class="stat-value"><?php echo $total_completions ?? 0; ?></div>
                        <div class="admin-stat-sublabel">Siswa lulus</div>
                    </div>
                </div>
                <div class="admin-stat-card cyan">
                    <div class="admin-stat-icon"><?php icon('trending-up', 22); ?></div>
                    <div class="admin-stat-body">
                        <div class="stat-label">Siswa Aktif</div>
                        <div class="stat-value"><?php echo $total_students ?? 0; ?></div>
                        <div class="admin-stat-sublabel">Saat ini belajar</div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="dashboard-grid-secondary mb-8">
                <div class="learning-section-card">
                    <div class="section-title-row">
                        <h3>Pertumbuhan User (30 hari)</h3>
                    </div>
                    <div style="padding:1rem 0;">
                        <canvas id="userGrowthChart" height="200"></canvas>
                    </div>
                </div>
                <div class="learning-section-card">
                    <div class="section-title-row">
                        <h3>Kursus Terpopuler</h3>
                    </div>
                    <div style="padding:1rem 0;">
                        <canvas id="popularCoursesChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid-secondary mb-8">
                <div class="learning-section-card">
                    <div class="section-title-row">
                        <h3>Pendaftaran per Hari (30 hari)</h3>
                    </div>
                    <div style="padding:1rem 0;">
                        <canvas id="enrollmentTrendChart" height="180"></canvas>
                    </div>
                </div>
                <div class="learning-section-card">
                    <div class="admin-stats-mini-grid">
                        <div class="mini-stat">
                            <div class="mini-stat-value"><?php echo $total_achievements; ?></div>
                            <div class="mini-stat-label">Achievement Diraih</div>
                        </div>
                        <div class="mini-stat">
                            <div class="mini-stat-value"><?php echo $total_certificates; ?></div>
                            <div class="mini-stat-label">Sertifikat Diterbitkan</div>
                        </div>
                        <div class="mini-stat">
                            <div class="mini-stat-value"><?php echo $total_comments; ?></div>
                            <div class="mini-stat-label">Komentar</div>
                        </div>
                        <div class="mini-stat">
                            <div class="mini-stat-value"><?php echo $total_completions; ?></div>
                            <div class="mini-stat-label">Kursus Selesai</div>
                        </div>
                    </div>
                    <div style="margin-top:1rem;">
                        <a href="manage-enrollments.php" class="admin-action-btn" style="width:100%;justify-content:center;padding:0.6rem;font-size:0.85rem;">Kelola Enrollment</a>
                    </div>
                </div>
            </div>

            <!-- Admin Quick Actions -->
            <div class="admin-quick-actions">
                <h3 class="admin-section-title">Akses Cepat</h3>
                <div class="admin-nav-cards">
                    <a href="manage-courses.php" class="admin-nav-card">
                        <div class="admin-nav-icon"><?php icon('book', 20); ?></div>
                        <div class="admin-nav-info">
                            <div class="admin-nav-label">Kelola Kursus</div>
                            <div class="admin-nav-desc">Tambah, edit, hapus kursus</div>
                        </div>
                        <span class="admin-nav-arrow"><?php icon('arrow-right', 16); ?></span>
                    </a>
                    <a href="users.php" class="admin-nav-card">
                        <div class="admin-nav-icon"><?php icon('users', 20); ?></div>
                        <div class="admin-nav-info">
                            <div class="admin-nav-label">Kelola User</div>
                            <div class="admin-nav-desc">Manajemen student & admin</div>
                        </div>
                        <span class="admin-nav-arrow"><?php icon('arrow-right', 16); ?></span>
                    </a>
                    <a href="manage-enrollments.php" class="admin-nav-card">
                        <div class="admin-nav-icon"><?php icon('clipboard', 20); ?></div>
                        <div class="admin-nav-info">
                            <div class="admin-nav-label">Enrollments</div>
                            <div class="admin-nav-desc">Kelola pendaftaran kursus</div>
                        </div>
                        <span class="admin-nav-arrow"><?php icon('arrow-right', 16); ?></span>
                    </a>
                    <a href="manage-achievements.php" class="admin-nav-card">
                        <div class="admin-nav-icon"><?php icon('award', 20); ?></div>
                        <div class="admin-nav-info">
                            <div class="admin-nav-label">Achievements</div>
                            <div class="admin-nav-desc">Kelola achievement</div>
                        </div>
                        <span class="admin-nav-arrow"><?php icon('arrow-right', 16); ?></span>
                    </a>
                    <a href="manage-categories.php" class="admin-nav-card">
                        <div class="admin-nav-icon"><?php icon('tag', 20); ?></div>
                        <div class="admin-nav-info">
                            <div class="admin-nav-label">Kategori Kursus</div>
                            <div class="admin-nav-desc">Atur kategori</div>
                        </div>
                        <span class="admin-nav-arrow"><?php icon('arrow-right', 16); ?></span>
                    </a>
                    <a href="manage-comments.php" class="admin-nav-card">
                        <div class="admin-nav-icon"><?php icon('message-circle', 20); ?></div>
                        <div class="admin-nav-info">
                            <div class="admin-nav-label">Komentar</div>
                            <div class="admin-nav-desc">Moderasi komentar</div>
                        </div>
                        <span class="admin-nav-arrow"><?php icon('arrow-right', 16); ?></span>
                    </a>
                    <a href="manage-certificates.php" class="admin-nav-card">
                        <div class="admin-nav-icon"><?php icon('certificate', 20); ?></div>
                        <div class="admin-nav-info">
                            <div class="admin-nav-label">Sertifikat</div>
                            <div class="admin-nav-desc">Lihat & kelola sertifikat</div>
                        </div>
                        <span class="admin-nav-arrow"><?php icon('arrow-right', 16); ?></span>
                    </a>
                    <a href="manage-notifications.php" class="admin-nav-card">
                        <div class="admin-nav-icon"><?php icon('send', 20); ?></div>
                        <div class="admin-nav-info">
                            <div class="admin-nav-label">Broadcast</div>
                            <div class="admin-nav-desc">Kirim notifikasi</div>
                        </div>
                        <span class="admin-nav-arrow"><?php icon('arrow-right', 16); ?></span>
                    </a>
                    <a href="manage-logs.php" class="admin-nav-card">
                        <div class="admin-nav-icon"><?php icon('scroll', 20); ?></div>
                        <div class="admin-nav-info">
                            <div class="admin-nav-label">Log Aktivitas</div>
                            <div class="admin-nav-desc">Riwayat aktivitas admin</div>
                        </div>
                        <span class="admin-nav-arrow"><?php icon('arrow-right', 16); ?></span>
                    </a>
                    <a href="manage-backup.php" class="admin-nav-card">
                        <div class="admin-nav-icon"><?php icon('save', 20); ?></div>
                        <div class="admin-nav-info">
                            <div class="admin-nav-label">Backup DB</div>
                            <div class="admin-nav-desc">Backup & restore database</div>
                        </div>
                        <span class="admin-nav-arrow"><?php icon('arrow-right', 16); ?></span>
                    </a>
                    <a href="admin_analytics.php" class="admin-nav-card">
                        <div class="admin-nav-icon"><?php icon('bar-chart-2', 20); ?></div>
                        <div class="admin-nav-info">
                            <div class="admin-nav-label">Analytics</div>
                            <div class="admin-nav-desc">Statistik & laporan detail</div>
                        </div>
                        <span class="admin-nav-arrow"><?php icon('arrow-right', 16); ?></span>
                    </a>
                    <a href="export.php" class="admin-nav-card">
                        <div class="admin-nav-icon"><?php icon('download', 20); ?></div>
                        <div class="admin-nav-info">
                            <div class="admin-nav-label">Export Data</div>
                            <div class="admin-nav-desc">Export CSV/Excel</div>
                        </div>
                        <span class="admin-nav-arrow"><?php icon('arrow-right', 16); ?></span>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="dashboard-grid-secondary" style="grid-template-columns:1fr 1fr 1fr;">
                <!-- Recent Enrollments -->
                <div class="learning-section-card">
                    <div class="section-title-row">
                        <h3>Pendaftaran Terbaru</h3>
                        <a href="manage-enrollments.php" class="view-all-link">Lihat Semua</a>
                    </div>
                    <?php if (empty($recent_enrollments)): ?>
                        <div class="glass-empty-state"><p>Belum ada pendaftaran.</p></div>
                    <?php else: ?>
                        <?php foreach ($recent_enrollments as $re): ?>
                        <div class="admin-activity-item">
                            <div class="activity-avatar">
                                <?php echo strtoupper(substr($re['nama_lengkap'], 0, 1)); ?>
                            </div>
                            <div class="activity-info">
                                <div class="activity-name"><?php echo htmlspecialchars($re['nama_lengkap']); ?></div>
                                <div class="activity-detail">Mendaftar: <?php echo htmlspecialchars($re['judul_course']); ?></div>
                            </div>
                            <div class="activity-time"><?php echo date('d/m/Y', strtotime($re['enrolled_at'])); ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Recent Registrations -->
                <div class="learning-section-card">
                    <div class="section-title-row">
                        <h3>Student Baru</h3>
                        <a href="users.php" class="view-all-link">Lihat Semua</a>
                    </div>
                    <?php if (empty($recent_users)): ?>
                        <div class="glass-empty-state"><p>Belum ada student baru.</p></div>
                    <?php else: ?>
                        <?php foreach ($recent_users as $ru): ?>
                        <div class="admin-activity-item">
                            <div class="activity-avatar"><?php echo strtoupper(substr($ru['nama_lengkap'], 0, 1)); ?></div>
                            <div class="activity-info">
                                <div class="activity-name"><?php echo htmlspecialchars($ru['nama_lengkap']); ?></div>
                                <div class="activity-detail">@<?php echo htmlspecialchars($ru['username']); ?></div>
                            </div>
                            <div class="activity-time"><?php echo date('d/m/Y', strtotime($ru['created_at'])); ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Recent Activity Log -->
                <div class="learning-section-card">
                    <div class="section-title-row">
                        <h3>Aktivitas Terbaru</h3>
                        <a href="manage-logs.php" class="view-all-link">Lihat Semua</a>
                    </div>
                    <?php if (empty($recent_activities)): ?>
                        <div class="glass-empty-state"><p>Belum ada aktivitas.</p></div>
                    <?php else: ?>
                        <?php foreach ($recent_activities as $act): ?>
                        <div class="admin-activity-item">
                            <div class="activity-avatar"><?php echo strtoupper(substr($act['nama_lengkap'], 0, 1)); ?></div>
                            <div class="activity-info">
                                <div class="activity-name"><?php echo htmlspecialchars($act['nama_lengkap']); ?></div>
                                <div class="activity-detail" style="font-size:0.75rem;"><?php echo htmlspecialchars($act['description'] ?: $act['action']); ?></div>
                            </div>
                            <div class="activity-time"><?php echo date('d/m/Y', strtotime($act['created_at'])); ?></div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chart JS -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                const isDark = document.body.classList.contains('dark-mode');
                const textColor = isDark ? '#94a3b8' : '#64748b';
                const gridColor = isDark ? 'rgba(148,163,184,0.1)' : 'rgba(0,0,0,0.06)';

                new Chart(document.getElementById('userGrowthChart'), {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_column($user_growth, 'date')); ?>,
                        datasets: [{
                            label: 'Total User',
                            data: <?php echo json_encode(array_column($user_growth, 'total')); ?>,
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59,130,246,0.1)',
                            fill: true, tension: 0.3, pointRadius: 2
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { ticks: { color: textColor, font: { size: 10 }, maxTicksLimit: 10 }, grid: { color: gridColor } },
                            y: { ticks: { color: textColor, font: { size: 10 } }, grid: { color: gridColor }, beginAtZero: true }
                        }
                    }
                });

                new Chart(document.getElementById('popularCoursesChart'), {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode(array_column($popular_courses, 'judul_course')); ?>,
                        datasets: [{
                            data: <?php echo json_encode(array_column($popular_courses, 'enrollment_count')); ?>,
                            backgroundColor: ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6']
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { color: textColor, font: { size: 10 }, boxWidth: 12 } } }
                    }
                });

                new Chart(document.getElementById('enrollmentTrendChart'), {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_column($enrollment_trends, 'date')); ?>,
                        datasets: [{
                            label: 'Pendaftaran',
                            data: <?php echo json_encode(array_column($enrollment_trends, 'total')); ?>,
                            backgroundColor: 'rgba(16,185,129,0.5)',
                            borderColor: '#10B981', borderWidth: 1, borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { ticks: { color: textColor, font: { size: 10 }, maxTicksLimit: 10 }, grid: { color: gridColor } },
                            y: { ticks: { color: textColor, font: { size: 10 } }, grid: { color: gridColor }, beginAtZero: true }
                        }
                    }
                });
            </script>

            <?php endif; ?>

        </div>
    </div>

    <?php include 'includes/loading.php'; ?>
    <?php include 'includes/toast.php'; ?>

    <script src="assets/js/navbar.js"></script>
</body>
</html>
