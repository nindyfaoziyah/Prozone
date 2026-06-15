<?php
require_once '../config/config.php';
requireLogin();
requireRole(['admin']);
require_once '../includes/icons.php';

require_once '../models/User.php';
require_once '../models/Course.php';

$database = new Database();
$db = $database->getConnection();

// Key Metrics
$metrics = [];

$query = "SELECT COUNT(*) as total FROM users WHERE role = 'student'";
$stmt = $db->prepare($query);
$stmt->execute();
$metrics['total_students'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM courses";
$stmt = $db->prepare($query);
$stmt->execute();
$metrics['total_courses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM enrollments";
$stmt = $db->prepare($query);
$stmt->execute();
$metrics['total_enrollments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM lessons";
$stmt = $db->prepare($query);
$stmt->execute();
$metrics['total_lessons'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM clans";
$stmt = $db->prepare($query);
$stmt->execute();
$metrics['total_clans'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM enrollments WHERE status = 'completed'";
$stmt = $db->prepare($query);
$stmt->execute();
$metrics['total_completions'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// User Growth (Last 30 Days)
$user_growth = [];
$query = "SELECT DATE(created_at) as date, COUNT(*) as count 
          FROM users 
          WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
          GROUP BY DATE(created_at) 
          ORDER BY date ASC";
$stmt = $db->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $user_growth[] = $row;
}

// Popular Courses
$popular_courses = [];
$query = "SELECT c.judul_course, COUNT(e.id) as student_count 
          FROM courses c 
          LEFT JOIN enrollments e ON c.id = e.course_id 
          GROUP BY c.id 
          ORDER BY student_count DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $popular_courses[] = $row;
}

// Recent Enrollments
$query = "SELECT e.*, c.judul_course, u.nama_lengkap, u.email
          FROM enrollments e
          JOIN courses c ON e.course_id = c.id
          JOIN users u ON e.user_id = u.id
          ORDER BY e.enrolled_at DESC
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent Users
$query = "SELECT id, nama_lengkap, email, role, created_at 
          FROM users 
          ORDER BY created_at DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Admin Dashboard';
$page_css = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'admin.css', 'shared.css'];
$page_js = [];
$body_class = getThemeClass();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/head.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
      .admin-stat-card .admin-stat-icon svg { width: 22px; height: 22px; }
      .dark-mode .admin-chart-card { background: var(--bg-elevated); border-color: var(--border-color); }
      .dark-mode .admin-stat-card { background: var(--bg-elevated); border-color: var(--border-color); }
      .dark-mode .dark-mode .admin-action-btn { background: var(--bg-elevated); border-color: var(--border-color); }
      .dark-mode .dark-mode .admin-date-badge { background: var(--bg-elevated); border-color: var(--border-color); }
    </style>
</head>
<body class="dashboard-layout <?php echo $body_class; ?>">
    <?php include_once 'navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-content">
            <!-- Page Header -->
            <div class="admin-header">
                <div>
                    <h1>Admin Dashboard</h1>
                    <p class="admin-subtitle">Ringkasan aktivitas dan statistik platform</p>
                </div>
                <div class="admin-header-actions">
                    <span class="admin-date-badge">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <?php echo date('d F Y'); ?>
                    </span>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="admin-stats-grid">
                <div class="admin-stat-card purple">
                    <div class="admin-stat-icon"><?php echo getIcon('users', 22); ?></div>
                    <div class="admin-stat-body">
                        <div class="stat-label">Total Siswa</div>
                        <div class="stat-value"><?php echo number_format($metrics['total_students']); ?></div>
                    </div>
                </div>
                <div class="admin-stat-card blue">
                    <div class="admin-stat-icon"><?php echo getIcon('book', 22); ?></div>
                    <div class="admin-stat-body">
                        <div class="stat-label">Total Kursus</div>
                        <div class="stat-value"><?php echo number_format($metrics['total_courses']); ?></div>
                    </div>
                </div>
                <div class="admin-stat-card emerald">
                    <div class="admin-stat-icon"><?php echo getIcon('file', 22); ?></div>
                    <div class="admin-stat-body">
                        <div class="stat-label">Total Pelajaran</div>
                        <div class="stat-value"><?php echo number_format($metrics['total_lessons']); ?></div>
                    </div>
                </div>
                <div class="admin-stat-card amber">
                    <div class="admin-stat-icon"><?php echo getIcon('target', 22); ?></div>
                    <div class="admin-stat-body">
                        <div class="stat-label">Total Pendaftaran</div>
                        <div class="stat-value"><?php echo number_format($metrics['total_enrollments']); ?></div>
                    </div>
                </div>
                <div class="admin-stat-card rose">
                    <div class="admin-stat-icon"><?php echo getIcon('trophy', 22); ?></div>
                    <div class="admin-stat-body">
                        <div class="stat-label">Sertifikat Diterbitkan</div>
                        <div class="stat-value"><?php echo number_format($metrics['total_completions']); ?></div>
                    </div>
                </div>
                <div class="admin-stat-card cyan">
                    <div class="admin-stat-icon"><?php echo getIcon('clan', 22); ?></div>
                    <div class="admin-stat-body">
                        <div class="stat-label">Total Clan</div>
                        <div class="stat-value"><?php echo number_format($metrics['total_clans']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="admin-actions-grid">
                <a href="manage-courses.php" class="admin-action-btn">
                    <span class="action-icon" style="background:rgba(59,130,246,0.12);color:#3b82f6;"><?php echo getIcon('plus', 20); ?></span>
                    Kelola Kursus
                </a>
                <a href="users.php" class="admin-action-btn">
                    <span class="action-icon" style="background:rgba(59,130,246,0.12);color:#3B82F6;"><?php echo getIcon('user-plus', 20); ?></span>
                    Kelola User
                </a>
                <a href="manage-clans.php" class="admin-action-btn">
                    <span class="action-icon" style="background:rgba(16,185,129,0.12);color:#10b981;"><?php echo getIcon('clan', 20); ?></span>
                    Kelola Clan
                </a>
                <a href="manage-lessons.php" class="admin-action-btn">
                    <span class="action-icon" style="background:rgba(245,158,11,0.12);color:#f59e0b;"><?php echo getIcon('file', 20); ?></span>
                    Kelola Pelajaran
                </a>
            </div>

            <!-- Charts -->
            <div class="admin-charts-row">
                <div class="admin-chart-card">
                    <div class="admin-chart-header">
                        <h3>Pertumbuhan User</h3>
                        <span class="chart-badge">30 Hari Terakhir</span>
                    </div>
                    <div class="admin-chart-body">
                        <canvas id="userGrowthChart"></canvas>
                    </div>
                </div>
                <div class="admin-chart-card">
                    <div class="admin-chart-header">
                        <h3>Kursus Terpopuler</h3>
                        <span class="chart-badge">Top 5</span>
                    </div>
                    <div class="admin-chart-body">
                        <canvas id="popularCoursesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:2rem;">
                <!-- Recent Enrollments -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3>Pendaftaran Terbaru</h3>
                        <a href="manage-courses.php" class="card-link">Lihat Semua â†’</a>
                    </div>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Siswa</th>
                                    <th>Kursus</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recent_enrollments) > 0): ?>
                                    <?php foreach ($recent_enrollments as $enroll): ?>
                                        <tr>
                                            <td>
                                                <div class="user-cell">
                                                    <div class="user-avatar"><?php echo strtoupper(substr($enroll['nama_lengkap'] ?? 'U', 0, 1)); ?></div>
                                                    <div class="user-info">
                                                        <div class="name"><?php echo htmlspecialchars($enroll['nama_lengkap'] ?? 'Unknown'); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($enroll['judul_course']); ?></td>
                                            <td>
                                                <?php if ($enroll['status'] === 'completed'): ?>
                                                    <span class="admin-badge success">Selesai</span>
                                                <?php elseif ($enroll['status'] === 'in_progress'): ?>
                                                    <span class="admin-badge warning">Berjalan</span>
                                                <?php else: ?>
                                                    <span class="admin-badge neutral"><?php echo ucfirst($enroll['status']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="color:var(--text-muted);font-size:0.8125rem;"><?php echo date('d/m/Y', strtotime($enroll['enrolled_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4">
                                            <div class="admin-empty-state">
                                                <div class="empty-icon">ðŸ“‹</div>
                                                <div class="empty-text">Belum ada pendaftaran</div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3>User Terbaru</h3>
                        <a href="users.php" class="card-link">Lihat Semua â†’</a>
                    </div>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Role</th>
                                    <th>Tanggal Daftar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recent_users) > 0): ?>
                                    <?php foreach ($recent_users as $u): ?>
                                        <tr>
                                            <td>
                                                <div class="user-cell">
                                                    <div class="user-avatar"><?php echo strtoupper(substr($u['nama_lengkap'] ?? 'U', 0, 1)); ?></div>
                                                    <div class="user-info">
                                                        <div class="name"><?php echo htmlspecialchars($u['nama_lengkap'] ?? 'Unknown'); ?></div>
                                                        <div class="email"><?php echo htmlspecialchars($u['email']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($u['role'] === 'admin'): ?>
                                                    <span class="admin-badge danger">Admin</span>
                                                <?php else: ?>
                                                    <span class="admin-badge info">Siswa</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="color:var(--text-muted);font-size:0.8125rem;"><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3">
                                            <div class="admin-empty-state">
                                                <div class="empty-icon">ðŸ‘¤</div>
                                                <div class="empty-text">Belum ada user</div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const textColor = isDark ? '#94a3b8' : '#64748b';
        const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';

        // User Growth Chart
        const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        const userGrowthData = <?php echo json_encode($user_growth); ?>;

        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: userGrowthData.map(function(d) { return d.date; }),
                datasets: [{
                    label: 'User Baru',
                    data: userGrowthData.map(function(d) { return d.count; }),
                    borderColor: '#3B82F6',
                    backgroundColor: function(ctx) {
                        const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 280);
                        gradient.addColorStop(0, isDark ? 'rgba(59,130,246,0.25)' : 'rgba(59,130,246,0.15)');
                        gradient.addColorStop(1, isDark ? 'rgba(59,130,246,0.01)' : 'rgba(59,130,246,0.01)');
                        return gradient;
                    },
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#3B82F6',
                    pointBorderColor: isDark ? '#1e293b' : '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark ? '#1e293b' : '#fff',
                        titleColor: isDark ? '#f1f5f9' : '#0f172a',
                        bodyColor: isDark ? '#94a3b8' : '#64748b',
                        borderColor: isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)',
                        borderWidth: 1,
                        cornerRadius: 10,
                        padding: 12
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { color: textColor, font: { size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { size: 10 }, maxTicksLimit: 10 }
                    }
                }
            }
        });

        // Popular Courses Chart
        const popularCoursesCtx = document.getElementById('popularCoursesChart').getContext('2d');
        const popularCoursesData = <?php echo json_encode($popular_courses); ?>;

        new Chart(popularCoursesCtx, {
            type: 'doughnut',
            data: {
                labels: popularCoursesData.map(function(d) { return d.judul_course; }),
                datasets: [{
                    data: popularCoursesData.map(function(d) { return d.student_count; }),
                    backgroundColor: ['#3B82F6', '#60A5FA', '#10b981', '#f59e0b', '#f43f5e'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            font: { size: 11 },
                            padding: 12,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#1e293b' : '#fff',
                        titleColor: isDark ? '#f1f5f9' : '#0f172a',
                        bodyColor: isDark ? '#94a3b8' : '#64748b',
                        borderColor: isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)',
                        borderWidth: 1,
                        cornerRadius: 10,
                        padding: 12,
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                const pct = total > 0 ? Math.round(ctx.parsed / total * 100) : 0;
                                return ctx.label + ': ' + ctx.parsed + ' siswa (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    </script>

    <?php include 'footer.php'; ?>
    <?php include '../includes/loading.php'; ?>
    <?php include '../includes/toast.php'; ?>
    <script src="../assets/js/navbar.js"></script>
</body>
</html>
