<?php
require_once '../config/config.php';
requireRole(['admin']);
require_once '../includes/icons.php';
require_once '../includes/language-icons.php';
require_once '../includes/rpg_system.php';

$page_title       = 'Dashboard';
$page_description = 'Dashboard pembelajaran coding Prozone.';
$page_css         = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'admin.css', 'shared.css', 'rpg-system.css'];
$body_class       = getThemeClass();
require_once '../models/User.php';
require_once '../models/Course.php';
require_once '../models/Enrollment.php';
require_once '../models/UserProgress.php';

$database = new Database();
$db = $database->getConnection();

// Get dashboard data based on user role
$role = $_SESSION['user_role'];

$course = new Course($db);
$enrollment = new Enrollment($db);

// Initialize default values to prevent undefined variable warnings
$total_courses = 0;
$enrolled_courses = [];
$total_progress = 0;
$completed_courses = 0;
$avg_progress = 0;
$total_enrolled = 0;
$total_xp = 0;
$level = 1;
$avatar = null;
$char_slug = 'code-warrior';
$char_data = getClassData('code-warrior');
$next_unlock = null;
$xp_percent = 0;
$streakDays = 0;
$lastCourse = null;
$total_students = 0;
$total_users = 0;
$total_lessons = 0;
$total_clans = 0;
$total_completions = 0;
$total_achievements = 0;
$total_certificates = 0;
$total_comments = 0;
$user_growth = [];
$popular_courses = [];
$enrollment_trends = [];
$recent_enrollments = [];
$recent_users = [];
$recent_activities = [];

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

// Admin dashboard data
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
    try {
        $stmt_act = $db->query("SELECT al.*, u.nama_lengkap FROM activity_log al JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 5");
        while ($row = $stmt_act->fetch(PDO::FETCH_ASSOC)) {
            $recent_activities[] = $row;
        }
    } catch (Exception $e) {
        // activity_log table may not exist yet
        $recent_activities = [];
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/head.php'; ?>
    <style>
        .mini-stat-icon { font-size: 1.5rem; margin-bottom: 0.25rem; }
    </style>
</head>
<body class="<?php echo trim($body_class . ' dashboard-layout'); ?>">
    <?php require_once 'navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-content">

            <!-- Admin Welcome Banner -->
            <div class="admin-welcome-banner">
                <div class="admin-welcome-bg-shapes"></div>
                <div class="admin-welcome-text">
                    <div class="admin-welcome-badge">
                        <?php icon('shield', 14); ?> ADMIN PANEL
                    </div>
                    <h2><?php echo $greeting; ?>, <?php echo htmlspecialchars(explode(' ', $_SESSION['nama_lengkap'] ?? 'Admin')[0]); ?>! 👋</h2>
                    <p>Kelola platform Prozone — kursus, user, clan, dan pantau aktivitas belajar siswa.</p>
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
                        <h3>📈 Pertumbuhan User (30 hari)</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="userGrowthChart" height="200"></canvas>
                    </div>
                </div>
                <div class="learning-section-card">
                    <div class="section-title-row">
                        <h3>🏆 Kursus Terpopuler</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="popularCoursesChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid-secondary mb-8">
                <div class="learning-section-card">
                    <div class="section-title-row">
                        <h3>📝 Pendaftaran per Hari (30 hari)</h3>
                    </div>
                    <div class="chart-container">
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
                    <div class="enrollment-action-wrap">
                        <a href="manage-enrollments.php" class="admin-action-btn enrollment-action-btn">Kelola Enrollment</a>
                    </div>
                </div>
            </div>

            <!-- Admin Quick Actions -->
            <div class="admin-quick-actions">
                <h3 class="admin-section-title"><?php icon('zap', 18); ?> Akses Cepat</h3>
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
            <div class="dashboard-grid-secondary dashboard-activity-grid">
                <!-- Recent Enrollments -->
                <div class="learning-section-card">
                    <div class="section-title-row">
                        <h3>🎓 Pendaftaran Terbaru</h3>
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
                        <h3>👤 Student Baru</h3>
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
                        <h3>📋 Aktivitas Terbaru</h3>
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
                                <div class="activity-detail"><?php echo htmlspecialchars($act['description'] ?: $act['action']); ?></div>
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

        </div>
    </div>

    <?php include '../includes/loading.php'; ?>
    <?php include '../includes/toast.php'; ?>

    <script src="../assets/js/navbar.js"></script>
</body>
</html>
