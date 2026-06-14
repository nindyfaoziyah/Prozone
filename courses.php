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
          LEFT JOIN users u ON c.instructor_id = u.id
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

$page_title = 'Kursus';
$page_description = 'Jelajahi berbagai kursus pemrograman untuk meningkatkan skill coding Anda';
$page_css = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css', 'pages/courses.css'];
$body_class = getThemeClass();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once __DIR__ . '/includes/head.php'; ?>
    
    <style>
        :root {
            --card-radius: 20px;
            --card-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.06);
            --card-shadow-hover: 0 20px 40px rgba(0,0,0,0.08), 0 6px 12px rgba(0,0,0,0.06);
            --card-border: rgba(99,102,241,0.06);
            --card-bg: #ffffff;
            --thumb-height: 170px;
            --filter-bg: rgba(99,102,241,0.03);
        }
        .dark-mode {
            --card-bg: rgba(255,255,255,0.03);
            --card-shadow: 0 1px 3px rgba(0,0,0,0.2), 0 1px 2px rgba(0,0,0,0.15);
            --card-shadow-hover: 0 20px 40px rgba(0,0,0,0.4), 0 6px 12px rgba(0,0,0,0.2);
            --card-border: rgba(255,255,255,0.05);
            --filter-bg: rgba(255,255,255,0.02);
        }

        /* === Reveal Animation === */
        .reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.7s ease, transform 0.7s ease;
        }
        .reveal.is-revealed {
            opacity: 1;
            transform: translateY(0);
        }

        .courses-hero {
            margin-bottom: 2.5rem;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.2rem;
            background: rgba(var(--accent-primary-rgb, 99,102,241), 0.1);
            border: 1px solid rgba(var(--accent-primary-rgb, 99,102,241), 0.15);
            border-radius: 100px;
            color: var(--accent-primary, #6366f1);
            font-size: 0.82rem;
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: 0.02em;
        }

        .hero-title {
            font-size: clamp(1.8rem, 3.5vw, 2.5rem);
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
            line-height: 1.15;
        }

        .hero-subtitle {
            color: var(--text-secondary);
            font-size: 1.05rem;
            max-width: 580px;
            line-height: 1.6;
        }

        .filter-section {
            margin-bottom: 2.5rem;
            background: var(--filter-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 1.5rem;
            transition: border-color 0.3s;
        }
        .filter-section:focus-within {
            border-color: rgba(99,102,241,0.2);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 1rem;
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
            gap: 0.4rem;
        }

        .filter-label {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--text-secondary);
            margin-left: 0.25rem;
            letter-spacing: 0.03em;
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
            display: flex;
        }

        .premium-input {
            width: 100%;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 0.7rem 1rem 0.7rem 2.75rem;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.9rem;
            transition: all 0.25s ease;
            box-sizing: border-box;
        }
        .premium-input::placeholder {
            color: var(--text-muted);
            opacity: 0.6;
        }
        .premium-input:focus {
            outline: none;
            border-color: var(--accent-primary, #6366f1);
            box-shadow: 0 0 0 3px rgba(var(--accent-primary-rgb, 99,102,241), 0.1);
        }

        .premium-select {
            width: 100%;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 0.7rem 2.5rem 0.7rem 1rem;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.25s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            box-sizing: border-box;
        }
        .premium-select:focus {
            outline: none;
            border-color: var(--accent-primary, #6366f1);
            box-shadow: 0 0 0 3px rgba(var(--accent-primary-rgb, 99,102,241), 0.1);
        }

        .btn-search {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
            border-radius: 12px;
            height: 44px;
            padding: 0 1.5rem;
            font-weight: 700;
            font-size: 0.9rem;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.25s ease;
            white-space: nowrap;
        }
        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(99,102,241,0.25);
        }
        .btn-search:active {
            transform: translateY(0);
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .course-card-premium {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--card-radius);
            overflow: hidden;
            transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.1);
            display: flex;
            flex-direction: column;
            position: relative;
            box-shadow: var(--card-shadow);
            height: 100%;
        }
        .course-card-premium:hover {
            transform: translateY(-6px);
            border-color: rgba(var(--accent-primary-rgb, 99,102,241), 0.2);
            box-shadow: var(--card-shadow-hover);
        }

        .course-thumbnail {
            position: relative;
            height: var(--thumb-height);
            background: linear-gradient(135deg, rgba(99,102,241,0.06), rgba(139,92,246,0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }
        .course-thumbnail::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 25% 40%, rgba(255,255,255,0.15) 0%, transparent 60%);
            pointer-events: none;
        }
        .dark-mode .course-thumbnail {
            background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(139,92,246,0.04));
        }
        .dark-mode .course-thumbnail::after {
            background-image: radial-gradient(circle at 25% 40%, rgba(255,255,255,0.04) 0%, transparent 60%);
        }

        .course-logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            position: relative;
            z-index: 1;
            transition: transform 0.4s ease;
            filter: drop-shadow(0 8px 16px rgba(0,0,0,0.08));
        }
        .course-card-premium:hover .course-logo {
            transform: scale(1.08) rotate(-3deg);
        }

        .course-badge-level {
            position: absolute;
            top: 0.9rem;
            right: 0.9rem;
            padding: 0.3rem 0.75rem;
            border-radius: 100px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            backdrop-filter: blur(6px);
            z-index: 2;
            border: 1px solid;
        }
        .badge-beginner {
            background: rgba(34,197,94,0.12);
            color: #16a34a;
            border-color: rgba(34,197,94,0.2);
        }
        .badge-intermediate {
            background: rgba(245,158,11,0.12);
            color: #d97706;
            border-color: rgba(245,158,11,0.2);
        }
        .badge-advanced {
            background: rgba(239,68,68,0.12);
            color: #dc2626;
            border-color: rgba(239,68,68,0.2);
        }
        .dark-mode .badge-beginner {
            background: rgba(34,197,94,0.15);
            color: #4ade80;
            border-color: rgba(34,197,94,0.25);
        }
        .dark-mode .badge-intermediate {
            background: rgba(245,158,11,0.15);
            color: #fbbf24;
            border-color: rgba(245,158,11,0.25);
        }
        .dark-mode .badge-advanced {
            background: rgba(239,68,68,0.15);
            color: #f87171;
            border-color: rgba(239,68,68,0.25);
        }

        .course-body-premium {
            padding: 1.25rem 1.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .course-cat {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--accent-primary, #6366f1);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.35rem;
        }

        .course-title-premium {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.6rem;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .course-desc-premium {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.55;
            flex-shrink: 0;
        }

        .course-meta-premium {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid var(--card-border);
            flex-shrink: 0;
        }

        .meta-item {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        .meta-item svg {
            flex-shrink: 0;
        }
        .meta-item--enrolled {
            margin-left: auto;
            color: #16a34a;
        }
        .dark-mode .meta-item--enrolled {
            color: #4ade80;
        }

        .course-action-premium {
            margin-top: 1.25rem;
            flex-shrink: 0;
        }

        .btn-course-premium {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 700;
            font-family: inherit;
            text-decoration: none;
            background: var(--card-bg);
            color: var(--text-primary);
            border: 1px solid var(--card-border);
            transition: all 0.25s ease;
            box-sizing: border-box;
            cursor: pointer;
        }
        .btn-course-premium:hover {
            border-color: rgba(var(--accent-primary-rgb, 99,102,241), 0.25);
            background: rgba(var(--accent-primary-rgb, 99,102,241), 0.03);
        }
        .btn-course-premium.enrolled {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            border: none;
        }
        .btn-course-premium.enrolled:hover {
            box-shadow: 0 8px 24px rgba(99,102,241,0.25);
            transform: translateY(-1px);
        }

        .progress-container {
            margin-top: 1rem;
            margin-bottom: 0.25rem;
            flex-shrink: 0;
        }
        .progress-label-premium {
            display: flex;
            justify-content: space-between;
            font-size: 0.72rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
            color: var(--text-secondary);
        }
        .progress-bar-premium {
            height: 5px;
            background: rgba(var(--accent-primary-rgb, 99,102,241), 0.08);
            border-radius: 100px;
            overflow: hidden;
        }
        .progress-fill-premium {
            height: 100%;
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
            border-radius: 100px;
            transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .empty-state-courses {
            text-align: center;
            padding: 4rem 2rem;
            border: 2px dashed var(--card-border);
            border-radius: 24px;
            grid-column: 1 / -1;
            background: var(--card-bg);
        }
        .empty-icon-wrapper {
            width: 72px;
            height: 72px;
            background: rgba(var(--accent-primary-rgb, 99,102,241), 0.08);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            color: var(--accent-primary, #6366f1);
        }
        .empty-title-courses {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        .empty-text-courses {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .results-info {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--text-secondary);
            font-size: 0.88rem;
        }
        .results-info strong {
            color: var(--text-primary);
        }
        .results-info a {
            color: var(--accent-primary, #6366f1);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.82rem;
            transition: opacity 0.2s;
        }
        .results-info a:hover {
            opacity: 0.8;
        }

        /* === Responsive Grid Improvements === */
        @media (max-width: 768px) {
            .courses-grid {
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
                gap: 1rem;
            }
            .course-body-premium {
                padding: 1rem 1.25rem 1.25rem;
            }
            .filter-section {
                padding: 1rem;
            }
            .courses-hero {
                margin-bottom: 1.5rem;
            }
            .course-title-premium {
                font-size: 1.05rem;
            }
        }
        @media (max-width: 480px) {
            .courses-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            :root {
                --thumb-height: 150px;
            }
            .course-logo {
                width: 56px;
                height: 56px;
            }
        }
    </style>
</head>
<body class="<?php echo trim($body_class . ' dashboard-layout'); ?>">
    <?php include 'navbar.php'; ?>

    <div class="page-wrapper dashboard-main-container">
        <div class="dashboard-content">
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
                <div class="filter-section reveal">
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
                    <div class="results-info reveal">
                        <span>Ditemukan <strong><?php echo count($courses); ?></strong> kursus yang sesuai.</span>
                        <a href="courses.php">Reset Filter</a>
                    </div>
                <?php endif; ?>

                <!-- Courses Grid -->
                <div class="courses-grid">
                    <?php if (empty($courses)): ?>
                        <div class="empty-state-courses reveal">
                            <div class="empty-icon-wrapper"><?php icon('book-open', 32); ?></div>
                            <h3 class="empty-title-courses">Kursus Tidak Ditemukan</h3>
                            <p class="empty-text-courses">Kami tidak dapat menemukan kursus yang sesuai dengan kriteria filter Anda.</p>
                            <a href="courses.php" class="btn-search" style="width: auto;">Reset Semua Filter</a>
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
                                            <div class="meta-item meta-item--enrolled">
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

