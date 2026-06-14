<?php
/**
 * Prozone - Courses Catalog
 * Halaman katalog kursus untuk user terdaftar
 */

if (!defined('PROZONE_ACCESS')) {
    define('PROZONE_ACCESS', true);
}

require_once __DIR__ . '/config/config.php';
requireLogin();
require_once __DIR__ . '/includes/icons.php';

require_once __DIR__ . '/models/Course.php';
require_once __DIR__ . '/models/Enrollment.php';

$database = new Database();
$db = $database->getConnection();

$course = new Course($db);
$enrollment = new Enrollment($db);

// Get filter parameters
$search = sanitizeInput($_GET['search'] ?? '');
$category_filter = sanitizeInput($_GET['category'] ?? '');
$level_filter = sanitizeInput($_GET['level'] ?? '');

// Get all courses with filters
$where_clause = ($_SESSION['user_role'] === 'student') ? "WHERE c.is_published = 1" : "WHERE 1=1";

$query = "SELECT c.*, cc.nama_kategori, u.nama_lengkap as instructor_name
          FROM courses c
          LEFT JOIN course_categories cc ON c.kategori_id = cc.id
          LEFT JOIN users u ON c.admin_id = u.id
          " . $where_clause;

$params = [];

if (!empty($search)) {
    $query .= " AND (c.judul_course LIKE :search OR c.deskripsi LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($category_filter)) {
    $query .= " AND c.kategori_id = :category";
    $params[':category'] = $category_filter;
}

if (!empty($level_filter)) {
    $query .= " AND c.level = :level";
    $params[':level'] = $level_filter;
}

$query .= " ORDER BY c.created_at DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();

$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all categories for filter
$categories_stmt = $db->prepare("SELECT id, nama_kategori FROM course_categories ORDER BY nama_kategori");
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user enrollments
$user_enrollments = [];
if (isset($_SESSION['user_id'])) {
    $enrollment_stmt = $enrollment->getUserEnrollments($_SESSION['user_id']);
    while ($row = $enrollment_stmt->fetch(PDO::FETCH_ASSOC)) {
        $user_enrollments[$row['course_id']] = $row;
    }
}

function getCourseLogo($title) {
    $title = strtolower($title);
    if (strpos($title, 'c++') !== false || strpos($title, 'cpp') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/cplusplus/cplusplus-original.svg';
    if (strpos($title, 'html') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/html5/html5-original.svg';
    if (strpos($title, 'css') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg';
    if (strpos($title, 'python') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/python/python-original.svg';
    if (strpos($title, 'php') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg';
    if (strpos($title, 'java') !== false && strpos($title, 'script') === false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/java/java-original.svg';
    if (strpos($title, 'javascript') !== false || strpos($title, 'js') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg';
    if (strpos($title, 'typescript') !== false || strpos($title, 'ts') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/typescript/typescript-original.svg';
    if (strpos($title, 'react') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/react/react-original.svg';
    if (strpos($title, 'vue') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/vuejs/vuejs-original.svg';
    if (strpos($title, 'node') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/nodejs/nodejs-original.svg';
    if (strpos($title, 'mysql') !== false || strpos($title, 'database') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg';
    if (strpos($title, 'git') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/git/git-original.svg';
    return null;
}

$page_title = 'Kursus - ' . APP_NAME;
$page_description = 'Jelajahi berbagai kursus pemrograman untuk meningkatkan skill coding Anda';
$page_css = ['assets/css/pages/courses.css'];
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'id'; ?>" data-theme="<?php echo $_SESSION['theme'] ?? 'dark'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/glassmorphism.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/ui-enhancements.css">
    <link rel="stylesheet" href="assets/css/pages/courses.css">
    
    <style>
        .page-courses {
            background: var(--bg-main);
            min-height: 100vh;
        }
        
        .courses-hero {
            margin-bottom: 2.5rem;
            text-align: left;
        }
        
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(var(--accent-primary-rgb), 0.1);
            border: 1px solid rgba(var(--accent-primary-rgb), 0.2);
            border-radius: 100px;
            color: var(--accent-primary);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .hero-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
        }
        
        .hero-subtitle {
            color: var(--text-secondary);
            font-size: 1.1rem;
            max-width: 600px;
        }

        .filter-section {
            margin-bottom: 2.5rem;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 1.25rem;
            align-items: flex-end;
        }

        @media (max-width: 992px) {
            .filter-grid {
                grid-template-columns: 1fr 1fr;
            }
            .filter-grid > :first-child {
                grid-column: span 2;
            }
        }

        @media (max-width: 576px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
            .filter-grid > :first-child {
                grid-column: span 1;
            }
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-left: 0.25rem;
        }

        .search-input-wrapper {
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        .premium-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            color: var(--text-primary);
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .premium-input:focus {
            outline: none;
            border-color: var(--accent-primary);
            background: rgba(255, 255, 255, 0.06);
            box-shadow: 0 0 0 4px rgba(var(--accent-primary-rgb), 0.1);
        }

        .premium-select {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            color: var(--text-primary);
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.5)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
        }

        .premium-select:focus {
            outline: none;
            border-color: var(--accent-primary);
            background-color: rgba(255, 255, 255, 0.06);
        }

        .btn-search {
            background: var(--grad-primary);
            color: white;
            border: none;
            border-radius: 12px;
            height: 46px;
            padding: 0 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(var(--accent-primary-rgb), 0.3);
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }

        .course-card-premium {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .course-card-premium:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(var(--accent-primary-rgb), 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .course-thumbnail {
            position: relative;
            height: 180px;
            background: linear-gradient(45deg, rgba(var(--accent-primary-rgb), 0.1), rgba(var(--accent-secondary-rgb), 0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .course-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.2));
            transition: transform 0.5s ease;
        }

        .course-card-premium:hover .course-logo {
            transform: scale(1.1) rotate(5deg);
        }

        .course-badge-level {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.4rem 0.8rem;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            backdrop-filter: blur(10px);
        }

        .badge-beginner { background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
        .badge-intermediate { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .badge-advanced { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }

        .course-body-premium {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .course-cat {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--accent-primary);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 0.5rem;
        }

        .course-title-premium {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            line-height: 1.3;
        }

        .course-desc-premium {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.6;
        }

        .course-meta-premium {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-top: auto;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .course-action-premium {
            margin-top: 1.5rem;
        }

        .btn-course-premium {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-course-premium.enrolled {
            background: var(--grad-primary);
            border: none;
            color: white;
        }

        .btn-course-premium:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            transform: scale(1.02);
        }

        .btn-course-premium.enrolled:hover {
            box-shadow: 0 8px 20px rgba(var(--accent-primary-rgb), 0.3);
        }

        .progress-container {
            margin-top: 1.25rem;
        }

        .progress-label-premium {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .progress-bar-premium {
            height: 6px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 100px;
            overflow: hidden;
        }

        .progress-fill-premium {
            height: 100%;
            background: var(--grad-primary);
            border-radius: 100px;
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .empty-state-courses {
            text-align: center;
            padding: 4rem 2rem;
            background: rgba(255, 255, 255, 0.02);
            border: 2px dashed rgba(255, 255, 255, 0.05);
            border-radius: 32px;
            grid-column: 1 / -1;
        }

        .empty-icon-wrapper {
            width: 80px;
            height: 80px;
            background: rgba(var(--accent-primary-rgb), 0.1);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: var(--accent-primary);
        }

        .empty-title-courses {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .empty-text-courses {
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }
    </style>
</head>
<body class="page-courses">
    <?php include 'navbar.php'; ?>

    <div class="dashboard-main-container">
        <div class="dashboard-content">
            <div class="page-wrapper">
                <!-- Hero Section -->
                <div class="courses-hero reveal">
                    <div class="hero-badge">
                        <span>📚</span>
                        <span>Katalog Kursus</span>
                    </div>
                    <h1 class="hero-title">Jelajahi Skill Baru</h1>
                    <p class="hero-subtitle">Mulai perjalanan belajarmu hari ini dengan koleksi kursus pemrograman terbaik kami.</p>
                </div>

                <!-- Filters -->
                <div class="glass-card filter-section reveal" style="padding: 1.5rem;">
                    <form method="GET" class="filter-grid">
                        <div class="filter-group">
                            <label class="filter-label">Cari Kursus</label>
                            <div class="search-input-wrapper">
                                <span class="search-icon"><?php icon('search', 18); ?></span>
                                <input type="text" name="search" placeholder="Contoh: Python, Web React..." value="<?php echo htmlspecialchars($search); ?>" class="premium-input">
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <label class="filter-label">Kategori</label>
                            <select name="category" class="premium-select">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Level</label>
                            <select name="level" class="premium-select">
                                <option value="">Semua Level</option>
                                <option value="beginner" <?php echo $level_filter == 'beginner' ? 'selected' : ''; ?>>Pemula</option>
                                <option value="intermediate" <?php echo $level_filter == 'intermediate' ? 'selected' : ''; ?>>Menengah</option>
                                <option value="advanced" <?php echo $level_filter == 'advanced' ? 'selected' : ''; ?>>Lanjutan</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-search">
                            <?php icon('filter', 18); ?>
                            Filter
                        </button>
                    </form>
                </div>

                <!-- Results Info -->
                <?php if ($search || $category_filter || $level_filter): ?>
                    <div class="reveal" style="margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem; color: var(--text-secondary); font-size: 0.9rem;">
                        <span>Ditemukan <strong><?php echo count($courses); ?></strong> kursus yang sesuai.</span>
                        <a href="courses.php" style="color: var(--accent-primary); text-decoration: none; font-weight: 600;">Reset Filter</a>
                    </div>
                <?php endif; ?>

                <!-- Courses Grid -->
                <div class="courses-grid">
                    <?php if (empty($courses)): ?>
                        <div class="empty-state-courses reveal">
                            <div class="empty-icon-wrapper"><?php icon('book-open', 32); ?></div>
                            <h3 class="empty-title-courses">Kursus Tidak Ditemukan</h3>
                            <p class="empty-text-courses">Kami tidak dapat menemukan kursus yang sesuai dengan kriteria filter Anda.</p>
                            <a href="courses.php" class="btn-search" style="display: inline-flex; width: auto;">Reset Semua Filter</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($courses as $course_item): 
                            $is_enrolled = isset($user_enrollments[$course_item['id']]);
                            $enrollment_data = $is_enrolled ? $user_enrollments[$course_item['id']] : null;
                            $logoUrl = getCourseLogo($course_item['judul_course']);
                            $level = strtolower($course_item['level']);
                            $progress = $is_enrolled ? (float)$enrollment_data['progress_percent'] : 0;
                        ?>
                            <div class="course-card-premium reveal">
                                <div class="course-thumbnail">
                                    <?php if ($logoUrl): ?>
                                        <img src="<?php echo $logoUrl; ?>" alt="" class="course-logo">
                                    <?php else: ?>
                                        <?php icon('code', 40, 'var(--accent-primary)'); ?>
                                    <?php endif; ?>
                                    <div class="course-badge-level badge-<?php echo $level; ?>">
                                        <?php echo ucfirst($course_item['level']); ?>
                                    </div>
                                </div>
                                
                                <div class="course-body-premium">
                                    <div class="course-cat"><?php echo htmlspecialchars($course_item['nama_kategori'] ?? 'Programming'); ?></div>
                                    <h3 class="course-title-premium"><?php echo htmlspecialchars($course_item['judul_course']); ?></h3>
                                    <p class="course-desc-premium"><?php echo htmlspecialchars($course_item['deskripsi']); ?></p>
                                    
                                    <?php if ($is_enrolled && $progress > 0): ?>
                                        <div class="progress-container">
                                            <div class="progress-label-premium">
                                                <span>Progress Selesai</span>
                                                <span style="color: var(--accent-primary);"><?php echo number_format($progress, 0); ?>%</span>
                                            </div>
                                            <div class="progress-bar-premium">
                                                <div class="progress-fill-premium" style="width: <?php echo $progress; ?>%"></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="course-meta-premium">
                                        <div class="meta-item">
                                            <?php icon('book-open', 14); ?>
                                            <span><?php echo (int)$course_item['total_lessons']; ?> Modul</span>
                                        </div>
                                        <div class="meta-item">
                                            <?php icon('clock', 14); ?>
                                            <span><?php echo (int)$course_item['durasi_jam']; ?> Jam</span>
                                        </div>
                                        <?php if ($is_enrolled): ?>
                                            <div class="meta-item" style="color: #4ade80; margin-left: auto;">
                                                <?php icon('check-circle', 14); ?>
                                                <span>Terdaftar</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="course-action-premium">
                                        <a href="course.php?id=<?php echo $course_item['id']; ?>" class="btn-course-premium <?php echo $is_enrolled ? 'enrolled' : ''; ?>">
                                            <?php if ($is_enrolled): ?>
                                                <?php icon('play-circle', 18); ?>
                                                <span>Lanjutkan Kursus</span>
                                            <?php else: ?>
                                                <?php icon('plus-circle', 18); ?>
                                                <span>Daftar Sekarang</span>
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <?php include 'includes/toast.php'; ?>
    <?php include 'includes/loading.php'; ?>

    <script src="assets/js/navbar.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.classList.add('is-revealed');
                        }, index * 100);
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>

