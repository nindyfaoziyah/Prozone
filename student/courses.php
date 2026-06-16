<?php
if (!defined('PROZONE_ACCESS')) {
    define('PROZONE_ACCESS', true);
}

require_once __DIR__ . '/../config/config.php';
requireLogin();
require_once __DIR__ . '/../includes/icons.php';

require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Enrollment.php';

$database = new Database();
$db = $database->getConnection();

$course = new Course($db);
$enrollment = new Enrollment($db);

$user_id = $_SESSION['user_id'] ?? 0;

$total_courses = 0;
$total_students = 0;
$total_lessons = 0;
try {
    $r = $db->query("SELECT COUNT(*) FROM courses WHERE is_published = 1");
    $total_courses = (int)$r->fetchColumn();
    $r2 = $db->query("SELECT COUNT(DISTINCT user_id) FROM enrollments");
    $total_students = (int)$r2->fetchColumn();
    $r3 = $db->query("SELECT SUM(total_lessons) FROM courses WHERE is_published = 1");
    $total_lessons = (int)$r3->fetchColumn();
} catch (Exception $e) {}

$categories = [];
try {
    $catQ = $db->query("SELECT cc.id, cc.nama_kategori, cc.slug, COUNT(c.id) as course_count
                        FROM course_categories cc
                        LEFT JOIN courses c ON c.kategori_id = cc.id AND c.is_published = 1
                        GROUP BY cc.id, cc.nama_kategori, cc.slug
                        ORDER BY course_count DESC");
    while ($row = $catQ->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $row;
    }
} catch (Exception $e) {}

$search = sanitizeInput($_GET['search'] ?? '');
$category_filter = sanitizeInput($_GET['category'] ?? '');

$where_clause = "WHERE c.is_published = 1";
$query = "SELECT c.*, cc.nama_kategori, cc.slug as kategori_slug
          FROM courses c
          LEFT JOIN course_categories cc ON c.kategori_id = cc.id
          $where_clause";
$params = [];
if (!empty($search)) {
    $query .= " AND (c.judul_course LIKE :search OR c.deskripsi LIKE :search)";
    $params[':search'] = "%$search%";
}
if (!empty($category_filter)) {
    $query .= " AND c.kategori_id = :category";
    $params[':category'] = $category_filter;
}
$query .= " ORDER BY c.total_students DESC, c.rating DESC";
$stmt = $db->prepare($query);
foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$user_enrollments = [];
if ($user_id) {
    $enstmt = $enrollment->getUserEnrollments($user_id);
    while ($row = $enstmt->fetch(PDO::FETCH_ASSOC)) {
        $user_enrollments[$row['course_id']] = $row;
    }
}

$trending = [];
try {
    $tq = $db->prepare("SELECT c.*, cc.nama_kategori
                        FROM courses c
                        LEFT JOIN course_categories cc ON c.kategori_id = cc.id
                        WHERE c.is_published = 1
                        ORDER BY c.total_students DESC, c.rating DESC LIMIT 8");
    $tq->execute();
    $trending = $tq->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$featured = [];
try {
    $fq = $db->prepare("SELECT c.*, cc.nama_kategori
                        FROM courses c
                        LEFT JOIN course_categories cc ON c.kategori_id = cc.id
                        WHERE c.is_published = 1 AND c.total_students > 0
                        ORDER BY c.total_students DESC, c.rating DESC LIMIT 6");
    $fq->execute();
    $featured = $fq->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
if (empty($featured) && !empty($courses)) {
    $featured = array_slice($courses, 0, min(6, count($courses)));
}



$gradients = [
    'linear-gradient(135deg, #4F46E5, #7C3AED)',
    'linear-gradient(135deg, #06B6D4, #0891B2)',
    'linear-gradient(135deg, #8B5CF6, #6D28D9)',
    'linear-gradient(135deg, #10B981, #059669)',
    'linear-gradient(135deg, #F59E0B, #D97706)',
    'linear-gradient(135deg, #EF4444, #DC2626)',
    'linear-gradient(135deg, #EC4899, #DB2777)',
    'linear-gradient(135deg, #6366F1, #4F46E5)',
    'linear-gradient(135deg, #14B8A6, #0D9488)',
    'linear-gradient(135deg, #F97316, #EA580C)',
];

function getCourseLogo($title) {
    $t = strtolower($title);
    if (strpos($t, 'c++') !== false || strpos($t, 'cpp') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/cplusplus/cplusplus-original.svg';
    if (strpos($t, 'html') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/html5/html5-original.svg';
    if (strpos($t, 'css') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg';
    if (strpos($t, 'python') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/python/python-original.svg';
    if (strpos($t, 'php') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg';
    if (strpos($t, 'java') !== false && strpos($t, 'script') === false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/java/java-original.svg';
    if (strpos($t, 'javascript') !== false || strpos($t, 'js') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg';
    if (strpos($t, 'typescript') !== false || strpos($t, 'ts') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/typescript/typescript-original.svg';
    if (strpos($t, 'react') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/react/react-original.svg';
    if (strpos($t, 'vue') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/vuejs/vuejs-original.svg';
    if (strpos($t, 'node') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/nodejs/nodejs-original.svg';
    if (strpos($t, 'mysql') !== false || strpos($t, 'database') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg';
    if (strpos($t, 'git') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/git/git-original.svg';
    if (strpos($t, 'docker') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/docker/docker-original.svg';
    if (strpos($t, 'flutter') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/flutter/flutter-original.svg';
    if (strpos($t, 'kotlin') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/kotlin/kotlin-original.svg';
    if (strpos($t, 'go') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/go/go-original.svg';
    if (strpos($t, 'rust') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/rust/rust-plain.svg';
    if (strpos($t, 'ruby') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/ruby/ruby-original.svg';
    if (strpos($t, 'swift') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/swift/swift-original.svg';
    if (strpos($t, 'laravel') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/laravel/laravel-plain.svg';
    if (strpos($t, 'nextjs') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/nextjs/nextjs-original.svg';
    if (strpos($t, 'aws') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/amazonwebservices/amazonwebservices-original.svg';
    if (strpos($t, 'azure') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/azure/azure-original.svg';
    if (strpos($t, 'kubernetes') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/kubernetes/kubernetes-plain.svg';
    if (strpos($t, 'sass') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/sass/sass-original.svg';
    if (strpos($t, 'tailwind') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/tailwindcss/tailwindcss-original.svg';
    if (strpos($t, 'mongodb') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mongodb/mongodb-original.svg';
    if (strpos($t, 'redis') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/redis/redis-original.svg';
    if (strpos($t, 'nginx') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/nginx/nginx-original.svg';
    if (strpos($t, 'angular') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/angularjs/angularjs-original.svg';
    if (strpos($t, 'graphql') !== false) return 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/graphql/graphql-plain.svg';
    return null;
}

function getCategoryEmoji($cat) {
    $map = [
        'frontend' => '🎨', 'backend' => '⚙️', 'mobile' => '📱',
        'programming' => '🖥️', 'ai' => '🤖', 'artificial intelligence' => '🤖',
        'cyber' => '🛡️', 'security' => '🛡️', 'data science' => '📊',
        'data' => '📊', 'database' => '🗄️', 'cloud' => '☁️',
        'devops' => '🔄', 'ui/ux' => '🎨', 'design' => '🎨',
        'game' => '🎮', 'web' => '💻', 'networking' => '🌐',
        'linux' => '🐧', 'python' => '🐍', 'javascript' => '🟨',
        'typescript' => '🔵', 'react' => '⚛️', 'vue' => '💚',
        'angular' => '🔴', 'laravel' => '🔶', 'node' => '💚',
        'flutter' => '🔷', 'kotlin' => '🟣', 'swift' => '🟠',
        'go' => '🔵', 'rust' => '🦀', 'c++' => '🔷',
        'sql' => '🗄️', 'machine learning' => '🧠',
    ];
    $cl = strtolower($cat);
    foreach ($map as $k => $v) { if (strpos($cl, $k) !== false) return $v; }
    return '📚';
}

function getCategoryColor($cat) {
    $map = [
        'frontend' => '#6366F1', 'backend' => '#06B6D4', 'web' => '#4F46E5',
        'mobile' => '#10B981', 'programming' => '#F59E0B',
        'ai' => '#8B5CF6', 'artificial intelligence' => '#8B5CF6',
        'cyber' => '#EF4444', 'security' => '#EF4444',
        'data science' => '#0EA5E9', 'data' => '#0EA5E9',
        'database' => '#14B8A6', 'cloud' => '#06B6D4',
        'ui/ux' => '#EC4899', 'design' => '#EC4899',
        'game' => '#F97316', 'devops' => '#F59E0B',
        'networking' => '#2563EB', 'linux' => '#F97316',
        'machine learning' => '#8B5CF6',
    ];
    $cl = strtolower($cat);
    foreach ($map as $k => $v) { if (strpos($cl, $k) !== false) return $v; }
    return '#6366F1';
}

// Complete tech stack categories
$techCategories = [
    'frontend' => ['label' => 'Frontend', 'icon' => '🎨', 'color' => '#6366F1', 'skills' => ['HTML', 'CSS', 'JavaScript', 'TypeScript', 'React', 'Vue', 'Angular', 'NextJS']],
    'backend' => ['label' => 'Backend', 'icon' => '⚙️', 'color' => '#06B6D4', 'skills' => ['PHP', 'Laravel', 'NodeJS', 'ExpressJS', 'Java', 'Spring Boot', 'ASP.NET']],
    'mobile' => ['label' => 'Mobile', 'icon' => '📱', 'color' => '#10B981', 'skills' => ['Flutter', 'React Native', 'Kotlin', 'Swift']],
    'data' => ['label' => 'Data Science', 'icon' => '📊', 'color' => '#0EA5E9', 'skills' => ['Python', 'Pandas', 'NumPy', 'Matplotlib', 'Machine Learning']],
    'ai' => ['label' => 'AI', 'icon' => '🤖', 'color' => '#8B5CF6', 'skills' => ['AI Fundamentals', 'Prompt Engineering', 'TensorFlow', 'PyTorch', 'LLM']],
    'database' => ['label' => 'Database', 'icon' => '🗄️', 'color' => '#14B8A6', 'skills' => ['SQL', 'MySQL', 'PostgreSQL', 'MongoDB', 'Redis']],
    'cyber' => ['label' => 'Cyber Security', 'icon' => '🛡️', 'color' => '#EF4444', 'skills' => ['Networking', 'Linux', 'Web Security', 'Ethical Hacking', 'Pentest']],
    'languages' => ['label' => 'Programming', 'icon' => '🖥️', 'color' => '#F59E0B', 'skills' => ['C', 'C++', 'C#', 'Java', 'Python', 'Go', 'Rust', 'PHP', 'JavaScript']],
];

$page_title = 'Learning Hub — Eksplorasi Semua Materi Coding';
$page_description = 'Jelajahi seluruh materi pembelajaran coding dan teknologi yang tersedia di Prozone. Mulai belajar kapan pun kamu siap.';
$page_css = ['pages/dashboard.css', 'sidebar-island.css', 'dashboard-override.css'];
$body_class = getThemeClass();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once __DIR__ . '/../includes/head.php'; ?>
<style>
:root {
    --primary: #4F46E5; --primary-light: rgba(79,70,229,0.08);
    --primary-mid: rgba(79,70,229,0.15);
    --secondary: #06B6D4; --accent: #8B5CF6;
    --suc: #10B981; --gold: #F59E0B; --bg: #F8FAFC;
    --card: #FFFFFF; --card-border: #E2E8F0;
    --border-light: #F1F5F9;
    --text-p: #0F172A; --text-s: #475569; --text-m: #94A3B8;
    --radius: 16px; --radius-sm: 12px;
    --shadow-sm: 0 1px 2px rgba(0,0,0,0.04);
    --shadow: 0 1px 3px rgba(0,0,0,0.06);
    --shadow-md: 0 4px 12px rgba(0,0,0,0.05);
    --shadow-lg: 0 8px 30px rgba(0,0,0,0.06);
    --shadow-xl: 0 20px 60px -8px rgba(0,0,0,0.08);
    --glass: rgba(255,255,255,0.72);
    --glass-border: rgba(255,255,255,0.3);
}
* { box-sizing: border-box; }
body { background: var(--bg); color: var(--text-p); font-family: system-ui, -apple-system, sans-serif; }
body.dashboard-layout .page-wrapper.dashboard-main-container { background: var(--bg) !important; }
body.dashboard-layout .dashboard-content { max-width: 1400px; }

/* ─── FLOATING ORBS ─── */
.float-orbs { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
.float-orb {
    position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.06;
    animation: orbFloat 24s ease-in-out infinite;
}
.float-orb:nth-child(1) { width: 520px; height: 520px; background: #4F46E5; top: -12%; left: -8%; }
.float-orb:nth-child(2) { width: 420px; height: 420px; background: #06B6D4; bottom: -8%; right: -6%; animation-delay: -8s; }
.float-orb:nth-child(3) { width: 340px; height: 340px; background: #8B5CF6; top: 35%; right: 8%; animation-delay: -16s; }
@keyframes orbFloat {
    0%, 100% { transform: translate(0,0) scale(1); }
    33% { transform: translate(40px,-40px) scale(1.08); }
    66% { transform: translate(-30px,30px) scale(0.92); }
}

/* ─── SCROLL REVEAL ─── */
.reveal { opacity: 0; transform: translateY(28px); transition: opacity .6s ease, transform .6s ease; }
.reveal.is-revealed { opacity: 1; transform: translateY(0); }

/* ─── HERO ─── */
.lh-hero { position: relative; z-index: 1; padding: 28px 0 20px; }
.lh-badge {
    display: inline-flex; align-items: center; gap: 8px; padding: 6px 18px;
    border-radius: 999px; background: linear-gradient(135deg, var(--primary-light), rgba(139,92,246,0.06));
    border: 1px solid var(--primary-mid); color: var(--primary);
    font-size: .7rem; font-weight: 700; margin-bottom: 14px; letter-spacing: .02em;
}
.lh-badge span { animation: badgeLive 2s infinite; }
@keyframes badgeLive { 0%,100%{opacity:1} 50%{opacity:0.5} }
.lh-hero h1 {
    font-size: clamp(1.6rem, 3.8vw, 2.6rem); font-weight: 900; line-height: 1.08;
    margin: 0 0 10px;
    background: linear-gradient(135deg, #0F172A 0%, #4F46E5 40%, #8B5CF6 70%, #06B6D4 100%);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.lh-hero p { font-size: .92rem; color: var(--text-s); margin: 0 0 20px; max-width: 520px; line-height: 1.6; }

/* Stats */
.lh-stats { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
.lh-stat {
    background: var(--glass); backdrop-filter: blur(16px);
    border: 1px solid var(--glass-border); border-radius: var(--radius-sm);
    padding: 12px 20px; min-width: 110px; text-align: center;
    box-shadow: var(--shadow-sm);
}
.lh-stat-val { font-size: 1.3rem; font-weight: 900; background: linear-gradient(135deg, var(--primary), var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; line-height: 1.2; }
.lh-stat-lbl { font-size: .58rem; font-weight: 600; color: var(--text-m); text-transform: uppercase; letter-spacing: .04em; margin-top: 2px; }

/* Search */
.lh-search-wrap { position: relative; margin-bottom: 18px; max-width: 560px; }
.lh-search-wrap .si { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-m); pointer-events: none; z-index: 2; }
.lh-search-wrap input {
    width: 100%; padding: 13px 18px 13px 48px; border-radius: 999px;
    border: 1px solid var(--card-border); background: var(--card);
    font-size: .88rem; font-family: inherit; color: var(--text-p);
    transition: all .25s; box-shadow: var(--shadow-sm);
}
.lh-search-wrap input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
.lh-search-wrap input::placeholder { color: var(--text-m); }

/* Trending banner */
.trending-banner {
    display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
    padding: 10px 0 0; margin-bottom: 6px;
}
.trending-banner-lbl { font-size: .65rem; font-weight: 700; color: var(--text-m); text-transform: uppercase; letter-spacing: .05em; white-space: nowrap; display: flex; align-items: center; gap: 4px; }
.trending-tags { display: flex; gap: 6px; flex-wrap: wrap; }
.trending-tag {
    padding: 4px 14px; border-radius: 999px; font-size: .7rem; font-weight: 600;
    background: var(--card); border: 1px solid var(--card-border); color: var(--text-s);
    cursor: pointer; transition: all .2s; white-space: nowrap;
}
.trending-tag:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-light); transform: translateY(-1px); }
.trending-tag .hot { color: #EF4444; margin-right: 3px; font-size: .5rem; vertical-align: middle; }

/* ─── SECTION HEADERS ─── */
.lh-section {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px; gap: 12px; flex-wrap: wrap;
}
.lh-section h2 {
    font-size: 1.1rem; font-weight: 800; color: var(--text-p); margin: 0;
    display: flex; align-items: center; gap: 8px;
}
.lh-section h2 small {
    font-size: .6rem; font-weight: 600; color: var(--text-m);
    background: var(--border-light); padding: 2px 10px; border-radius: 999px;
}
.lh-section .see-all {
    font-size: .72rem; font-weight: 700; color: var(--primary);
    text-decoration: none; transition: all .2s; display: flex; align-items: center; gap: 4px;
}
.lh-section .see-all:hover { gap: 8px; }
.lh-section .see-all svg { transition: transform .2s; }
.lh-section .see-all:hover svg { transform: translateX(3px); }

/* ─── FEATURED SLIDER ─── */
.featured-section { margin-bottom: 28px; position: relative; z-index: 1; }
.featured-slider {
    position: relative; border-radius: 20px; overflow: hidden;
    height: 240px; background: var(--card);
}
.featured-slide {
    position: absolute; inset: 0; display: flex; align-items: center;
    opacity: 0; transition: opacity .6s ease, transform .6s ease;
    transform: scale(0.96); padding: 32px 40px;
}
.featured-slide.active { opacity: 1; transform: scale(1); z-index: 1; }
.featured-bg {
    position: absolute; inset: 0;
    background: linear-gradient(135deg, #4F46E5, #7C3AED);
    z-index: 0;
}
.featured-bg::after {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(circle at 70% 20%, rgba(255,255,255,0.15) 0%, transparent 50%),
                      radial-gradient(circle at 20% 80%, rgba(255,255,255,0.06) 0%, transparent 40%);
}
.featured-slide:nth-child(2) .featured-bg { background: linear-gradient(135deg, #06B6D4, #0891B2); }
.featured-slide:nth-child(3) .featured-bg { background: linear-gradient(135deg, #8B5CF6, #6D28D9); }
.featured-slide:nth-child(4) .featured-bg { background: linear-gradient(135deg, #10B981, #059669); }
.featured-slide:nth-child(5) .featured-bg { background: linear-gradient(135deg, #F59E0B, #D97706); }
.featured-slide:nth-child(6) .featured-bg { background: linear-gradient(135deg, #EF4444, #DC2626); }
.featured-content { position: relative; z-index: 1; display: flex; align-items: center; gap: 28px; width: 100%; }
.featured-icon {
    width: 72px; height: 72px; border-radius: 20px;
    background: rgba(255,255,255,0.12); backdrop-filter: blur(8px);
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; flex-shrink: 0; border: 1px solid rgba(255,255,255,0.15);
}
.featured-icon img { width: 36px; height: 36px; object-fit: contain; filter: brightness(10); }
.featured-text { flex: 1; min-width: 0; }
.featured-text .fbadge {
    display: inline-block; font-size: .5rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .08em; padding: 3px 10px; border-radius: 999px;
    background: rgba(255,255,255,0.12); color: rgba(255,255,255,0.85); margin-bottom: 6px; backdrop-filter: blur(4px);
}
.featured-text h3 { font-size: 1.2rem; font-weight: 800; color: #fff; margin: 0 0 4px; }
.featured-text p { font-size: .78rem; color: rgba(255,255,255,0.75); margin: 0 0 10px; line-height: 1.5; max-width: 480px; }
.featured-meta { display: flex; gap: 14px; flex-wrap: wrap; font-size: .68rem; color: rgba(255,255,255,0.7); }
.featured-meta span { display: flex; align-items: center; gap: 4px; }
.featured-btn {
    padding: 10px 24px; border-radius: 12px; border: none;
    background: rgba(255,255,255,0.18); backdrop-filter: blur(8px);
    color: #fff; font-weight: 700; font-size: .78rem; cursor: pointer;
    transition: all .25s; font-family: inherit; text-decoration: none; white-space: nowrap;
}
.featured-btn:hover { background: rgba(255,255,255,0.28); transform: translateY(-2px); }
.featured-dots {
    position: absolute; bottom: 14px; left: 50%; transform: translateX(-50%);
    display: flex; gap: 6px; z-index: 2;
}
.featured-dot {
    width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,0.3);
    cursor: pointer; transition: all .3s; border: none; padding: 0;
}
.featured-dot.active { background: #fff; width: 22px; border-radius: 3px; }

/* ─── CATEGORY CARDS ─── */
.cat-section { margin-bottom: 32px; position: relative; z-index: 1; }
.cat-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 12px;
}
.cat-card {
    position: relative; background: var(--card); border: 1px solid var(--card-border);
    border-radius: var(--radius); padding: 20px 18px; cursor: pointer;
    transition: all .35s cubic-bezier(0.175, 0.885, 0.32, 1.1);
    overflow: hidden; box-shadow: var(--shadow-sm);
}
.cat-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); border-color: transparent; }
.cat-card::before {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(135deg, color-mix(in srgb, var(--cc) 8%, transparent), color-mix(in srgb, var(--cc) 3%, transparent));
    opacity: 0; transition: opacity .35s; border-radius: inherit;
}
.cat-card:hover::before { opacity: 1; }
.cat-card::after {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: var(--cc); opacity: 0; transition: opacity .35s; border-radius: 3px 3px 0 0;
}
.cat-card:hover::after { opacity: 1; }
.cat-card-icon {
    width: 44px; height: 44px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; margin-bottom: 12px; position: relative; z-index: 1;
    background: color-mix(in srgb, var(--cc) 10%, transparent);
}
.cat-card h3 { font-size: .85rem; font-weight: 800; color: var(--text-p); margin: 0 0 4px; position: relative; z-index: 1; }
.cat-card p { font-size: .65rem; color: var(--text-m); margin: 0 0 8px; position: relative; z-index: 1; }
.cat-card .count {
    font-size: .6rem; font-weight: 700; color: var(--text-m);
    margin-bottom: 8px; display: block; position: relative; z-index: 1;
}
.cat-tags { display: flex; flex-wrap: wrap; gap: 3px; position: relative; z-index: 1; }
.cat-tag {
    font-size: .52rem; font-weight: 600; padding: 2px 7px;
    border-radius: 6px; background: color-mix(in srgb, var(--cc) 8%, transparent);
    border: 1px solid color-mix(in srgb, var(--cc) 12%, transparent);
    color: var(--cc);
}

/* ─── CONTINUE LEARNING ─── */
.continue-section { margin-bottom: 28px; position: relative; z-index: 1; }
.continue-scroll {
    display: flex; gap: 12px; overflow-x: auto; scroll-behavior: smooth;
    padding: 4px 2px 8px; -webkit-overflow-scrolling: touch;
}
.continue-scroll::-webkit-scrollbar { height: 0; }
.continue-card {
    flex: 0 0 300px; background: var(--card); border: 1px solid var(--card-border);
    border-radius: var(--radius); padding: 16px;
    box-shadow: var(--shadow-sm); transition: all .3s;
    cursor: pointer; text-decoration: none; display: block;
}
.continue-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); border-color: var(--primary-mid); }
.continue-card .cc-head { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
.continue-card .cc-icon {
    width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    background: var(--border-light); font-size: 1.1rem;
}
.continue-card .cc-icon img { width: 22px; height: 22px; object-fit: contain; }
.continue-card .cc-info { flex: 1; min-width: 0; }
.continue-card .cc-title { font-size: .78rem; font-weight: 700; color: var(--text-p); margin: 0 0 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.continue-card .cc-cat { font-size: .58rem; font-weight: 600; color: var(--text-m); text-transform: uppercase; letter-spacing: .04em; }
.continue-card .cc-bar { height: 4px; background: var(--border-light); border-radius: 4px; overflow: hidden; margin-bottom: 4px; }
.continue-card .cc-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--primary), var(--accent)); transition: width .6s; }
.continue-card .cc-meta { display: flex; justify-content: space-between; font-size: .6rem; color: var(--text-m); }

/* ─── ALL COURSES ─── */
.all-section { position: relative; z-index: 1; margin-bottom: 32px; }
.filter-chips {
    display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; overflow-x: auto; padding-bottom: 4px;
}
.filter-chip {
    padding: 6px 16px; border-radius: 999px; font-size: .72rem; font-weight: 700;
    border: 1px solid var(--card-border); background: var(--card); color: var(--text-s);
    cursor: pointer; transition: all .2s; font-family: inherit; white-space: nowrap;
    display: flex; align-items: center; gap: 5px;
}
.filter-chip:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
.filter-chip.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.filter-chip.active:hover { background: #4338CA; }

.courses-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}
.course-card {
    background: var(--card); border: 1px solid var(--card-border);
    border-radius: var(--radius); overflow: hidden;
    transition: all .4s cubic-bezier(0.175, 0.885, 0.32, 1.15);
    box-shadow: var(--shadow-sm); position: relative;
    display: flex; flex-direction: column; cursor: pointer; text-decoration: none;
}
.course-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: var(--shadow-xl); border-color: transparent; }
.course-card::before {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(135deg, color-mix(in srgb, var(--card-grad-start, var(--primary)) 4%, transparent), color-mix(in srgb, var(--card-grad-end, var(--accent)) 2%, transparent));
    opacity: 0; transition: opacity .4s; z-index: 0; pointer-events: none;
}
.course-card:hover::before { opacity: 1; }
.course-card::after {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: var(--card-grad, linear-gradient(90deg, var(--primary), var(--accent)));
    opacity: 0; transition: opacity .4s; z-index: 2;
}
.course-card:hover::after { opacity: 1; }

.course-thumb {
    position: relative; height: 160px; overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    background: var(--thumb-bg, linear-gradient(135deg, rgba(79,70,229,0.04), rgba(139,92,246,0.08)));
}
.course-thumb img {
    width: 56px; height: 56px; object-fit: contain; position: relative; z-index: 1;
    transition: transform .45s cubic-bezier(0.175, 0.885, 0.32, 1.2);
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.06));
}
.course-card:hover .course-thumb img { transform: scale(1.15) rotate(-6deg); }
.course-thumb .level-badge {
    position: absolute; top: 12px; right: 12px; z-index: 2;
    padding: 3px 10px; border-radius: 999px; font-size: .55rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .04em;
    backdrop-filter: blur(6px); border: 1px solid;
}
.level-badge.beginner { background: rgba(16,185,129,0.12); color: #059669; border-color: rgba(16,185,129,0.2); }
.level-badge.intermediate { background: rgba(245,158,11,0.12); color: #D97706; border-color: rgba(245,158,11,0.2); }
.level-badge.advanced { background: rgba(239,68,68,0.12); color: #DC2626; border-color: rgba(239,68,68,0.2); }

.course-body { padding: 14px 16px 16px; display: flex; flex-direction: column; flex: 1; position: relative; z-index: 1; }
.course-body .cat-lbl { font-size: .58rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--primary); margin-bottom: 4px; }
.course-body h3 { font-size: .92rem; font-weight: 800; color: var(--text-p); margin: 0 0 6px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.course-body .desc { font-size: .72rem; color: var(--text-s); line-height: 1.5; margin: 0 0 10px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

.course-meta { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; margin-bottom: 10px; }
.course-meta .cm-item { text-align: center; padding: 5px 2px; background: var(--bg); border-radius: 8px; border: 1px solid var(--border-light); }
.course-meta .cm-item .cm-val { font-size: .7rem; font-weight: 800; color: var(--text-p); line-height: 1.2; }
.course-meta .cm-item .cm-lbl { font-size: .48rem; font-weight: 600; color: var(--text-m); text-transform: uppercase; letter-spacing: .03em; }

.course-rating { display: flex; align-items: center; gap: 6px; margin-bottom: 8px; }
.course-rating .stars { color: var(--gold); font-size: .62rem; letter-spacing: 2px; }
.course-rating .rval { font-size: .68rem; font-weight: 700; color: var(--text-p); }
.course-rating .rcount { font-size: .58rem; color: var(--text-m); }

.course-progress { margin-bottom: 8px; }
.course-progress .cp-row { display: flex; justify-content: space-between; font-size: .62rem; font-weight: 600; color: var(--text-s); margin-bottom: 4px; }
.course-progress .cp-bar { height: 4px; background: var(--border-light); border-radius: 4px; overflow: hidden; }
.course-progress .cp-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--primary), var(--accent)); transition: width .6s cubic-bezier(0.4, 0, 0.2, 1); }

.course-btn {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    width: 100%; padding: 9px 16px; border-radius: 10px;
    font-size: .75rem; font-weight: 700; font-family: inherit;
    border: 1px solid var(--card-border); background: var(--card); color: var(--text-p);
    cursor: pointer; transition: all .25s; text-decoration: none; margin-top: auto;
}
.course-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
.course-btn.primary { background: var(--primary); color: #fff; border-color: var(--primary); }
.course-btn.primary:hover { background: #4338CA; box-shadow: 0 4px 14px rgba(79,70,229,0.25); transform: translateY(-1px); }
.course-btn.success { background: linear-gradient(135deg, var(--suc), #059669); color: #fff; border: none; }
.course-btn.success:hover { box-shadow: 0 4px 14px rgba(16,185,129,0.25); transform: translateY(-1px); }

.results-info { font-size: .78rem; color: var(--text-s); margin-bottom: 14px; }
.results-info strong { color: var(--text-p); }

.empty-state { text-align: center; padding: 60px 20px; grid-column: 1 / -1; }
.empty-state .empty-icon { font-size: 3rem; margin-bottom: 12px; opacity: .4; }
.empty-state h3 { font-size: 1.05rem; font-weight: 800; color: var(--text-p); margin: 0 0 6px; }
.empty-state p { font-size: .78rem; color: var(--text-m); margin: 0 0 16px; }

/* ─── RESPONSIVE ─── */
@media (max-width: 1024px) {
    .cat-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); }
    .courses-grid { grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); }
}
@media (max-width: 768px) {
    .featured-slider { height: 280px; }
    .featured-slide { padding: 24px 20px; align-items: flex-start; }
    .featured-content { flex-direction: column; text-align: center; gap: 16px; }
    .featured-text p { max-width: none; }
    .featured-meta { justify-content: center; }
    .cat-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 8px; }
    .cat-card { padding: 14px 12px; }
    .cat-card-icon { width: 36px; height: 36px; font-size: 1.1rem; }
    .courses-grid { grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; }
    .lh-stat { min-width: 80px; padding: 10px 14px; }
    .lh-stat-val { font-size: 1.1rem; }
    .continue-card { flex: 0 0 250px; }
}
@media (max-width: 480px) {
    .courses-grid { grid-template-columns: 1fr; }
    .cat-grid { grid-template-columns: repeat(2, 1fr); }
    .featured-slider { height: 300px; }
    .course-thumb { height: 140px; }
    .continue-card { flex: 0 0 220px; }
    .lh-stat { min-width: 70px; padding: 8px 10px; }
    .lh-stat-val { font-size: 1rem; }
}
</style>
</head>
<body class="<?php echo trim($body_class . ' dashboard-layout'); ?>">
<?php include 'navbar.php'; ?>

<div class="page-wrapper dashboard-main-container">
<div class="dashboard-content" style="position:relative;z-index:1;">

<div class="float-orbs"><div class="float-orb"></div><div class="float-orb"></div><div class="float-orb"></div></div>

<!-- ═══ HERO ═══ -->
<section class="lh-hero reveal">
    <div class="lh-badge"><span>●</span> Learning Hub — Pusat Eksplorasi Materi</div>
    <h1>Eksplorasi Dunia<br>Teknologi &amp; Coding</h1>
    <p>Jelajahi seluruh materi pemrograman tanpa batas. Pilih topik yang kamu minati, mulai belajar kapan pun.</p>

    <div class="lh-stats">
        <div class="lh-stat"><div class="lh-stat-val" data-target="<?php echo max($total_lessons, 12000); ?>">0</div><div class="lh-stat-lbl">Materi</div></div>
        <div class="lh-stat"><div class="lh-stat-val" data-target="<?php echo max($total_students, 4500); ?>">0</div><div class="lh-stat-lbl">Student</div></div>
        <div class="lh-stat"><div class="lh-stat-val" data-target="950">0</div><div class="lh-stat-lbl">Proyek</div></div>
        <div class="lh-stat"><div class="lh-stat-val" data-target="8">0</div><div class="lh-stat-lbl">Path</div></div>
    </div>

    <div class="lh-search-wrap">
        <span class="si">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        </span>
        <input type="text" id="searchInput" placeholder="Cari materi coding, framework, atau teknologi..." autocomplete="off">
    </div>

    <div class="trending-banner">
        <span class="trending-banner-lbl">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2.5" stroke-linecap="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            Trending
        </span>
        <div class="trending-tags">
            <span class="trending-tag"><span class="hot">●</span> AI Engineering</span>
            <span class="trending-tag"><span class="hot">●</span> Web Development</span>
            <span class="trending-tag"><span class="hot">●</span> Cyber Security</span>
            <span class="trending-tag"><span class="hot">●</span> Data Science</span>
            <span class="trending-tag">Laravel 12</span>
            <span class="trending-tag">React 19</span>
            <span class="trending-tag">Python 3.14</span>
        </div>
    </div>
</section>

<!-- ═══ FEATURED COURSES ═══ -->
<?php if (!empty($featured)): ?>
<section class="featured-section reveal">
    <div class="lh-section">
        <h2>🏆 Featured</h2>
    </div>
    <div class="featured-slider" id="featuredSlider">
        <?php foreach (array_slice($featured, 0, 6) as $fi => $fc):
            $furl = 'course-detail.php?id=' . $fc['id'];
        ?>
        <div class="featured-slide <?php echo $fi === 0 ? 'active' : ''; ?>">
            <div class="featured-bg"></div>
            <div class="featured-content">
                <div class="featured-icon">
                    <?php $flogo = getCourseLogo($fc['judul_course']); ?>
                    <?php if ($flogo): ?><img src="<?php echo $flogo; ?>" alt=""><?php else: ?>🏆<?php endif; ?>
                </div>
                <div class="featured-text">
                    <span class="fbadge">⭐ Featured</span>
                    <h3><?php echo htmlspecialchars($fc['judul_course']); ?></h3>
                    <p><?php echo htmlspecialchars(mb_substr($fc['deskripsi'] ?: 'Course unggulan dengan materi komprehensif.', 0, 120)); ?></p>
                    <div class="featured-meta">
                        <span>📚 <?php echo (int)$fc['total_lessons']; ?> Materi</span>
                        <span>⏱ <?php echo (int)$fc['durasi_jam']; ?> Jam</span>
                        <span>👥 <?php echo (int)$fc['total_students']; ?> Student</span>
                        <span><?php echo ucfirst($fc['level']); ?></span>
                    </div>
                </div>
                <a href="<?php echo $furl; ?>" class="featured-btn">🚀 Mulai Belajar</a>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="featured-dots" id="featuredDots">
            <?php foreach (array_slice($featured, 0, 6) as $fi => $fc): ?>
            <button class="featured-dot <?php echo $fi === 0 ? 'active' : ''; ?>" data-idx="<?php echo $fi; ?>"></button>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══ CATEGORIES ═══ -->
<section class="cat-section reveal">
    <div class="lh-section">
        <h2>📂 Jelajahi Kategori</h2>
    </div>
    <div class="cat-grid" id="categoryGrid">
        <?php foreach ($techCategories as $tkey => $tc):
            $matched = false;
            foreach ($categories as $cat) {
                $cl = strtolower($cat['nama_kategori']);
                if (strpos($cl, $tkey) !== false || strpos($tkey, $cl) !== false) {
                    $matched = true;
                    $cCount = (int)$cat['course_count'];
                    break;
                }
            }
            $ccolor = $tc['color'];
        ?>
        <div class="cat-card" style="--cc:<?php echo $ccolor; ?>" onclick="filterByCategory('<?php echo $tkey; ?>')">
            <div class="cat-card-icon" style="background:<?php echo $ccolor; ?>12"><?php echo $tc['icon']; ?></div>
            <h3><?php echo $tc['label']; ?></h3>
            <span class="count"><?php echo $matched ? "📚 $cCount Course" : '🚀 Segera Hadir'; ?></span>
            <div class="cat-tags">
                <?php foreach (array_slice($tc['skills'], 0, 4) as $sk): ?>
                <span class="cat-tag"><?php echo $sk; ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ═══ CONTINUE LEARNING ═══ -->
<?php if (!empty($user_enrollments)): ?>
<section class="continue-section reveal">
    <div class="lh-section">
        <h2>📖 Lanjutkan Belajar</h2>
        <a href="#" class="see-all">Lihat Semua
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
        </a>
    </div>
    <div class="continue-scroll" id="continueScroll">
        <?php foreach ($user_enrollments as $enr):
            $eprogress = (float)($enr['progress_percent'] ?? 0);
            $elogo = getCourseLogo($enr['judul_course'] ?? '');
        ?>
        <a href="course-detail.php?id=<?php echo $enr['course_id']; ?>" class="continue-card">
            <div class="cc-head">
                <div class="cc-icon"><?php if ($elogo): ?><img src="<?php echo $elogo; ?>" alt=""><?php else: ?>📘<?php endif; ?></div>
                <div class="cc-info">
                    <div class="cc-title"><?php echo htmlspecialchars($enr['judul_course'] ?? ''); ?></div>
                    <div class="cc-cat"><?php echo htmlspecialchars($enr['level'] ?? ''); ?></div>
                </div>
            </div>
            <div class="cc-bar"><div class="cc-fill" style="width:<?php echo $eprogress; ?>%"></div></div>
            <div class="cc-meta"><span>Progress</span><span><?php echo number_format($eprogress, 0); ?>%</span></div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>


<!-- ═══ SEMUA COURSE ═══ -->
<section class="all-section reveal">
    <div class="lh-section">
        <h2>📚 Semua Course <small id="resultCountLabel"><?php echo count($courses); ?> course</small></h2>
    </div>

    <div class="filter-chips" id="filterChips">
        <button class="filter-chip active" data-filter="all">🔥 Semua</button>
        <?php foreach ($techCategories as $tkey => $tc): ?>
        <button class="filter-chip" data-filter="<?php echo $tkey; ?>"><?php echo $tc['icon']; ?> <?php echo $tc['label']; ?></button>
        <?php endforeach; ?>
    </div>

    <div class="results-info" id="resultsInfo">
        Menampilkan <strong id="resultCount"><?php echo count($courses); ?></strong> course
    </div>

    <div class="courses-grid" id="coursesGrid">
        <?php if (empty($courses)): ?>
        <div class="empty-state">
            <div class="empty-icon">📚</div>
            <h3>Belum Ada Course</h3>
            <p>Course akan segera tersedia. Pantau terus perkembangan Prozone!</p>
        </div>
        <?php else: ?>
            <?php foreach ($courses as $course_item):
                $is_enrolled = isset($user_enrollments[$course_item['id']]);
                $enrollment_data = $is_enrolled ? $user_enrollments[$course_item['id']] : null;
                $logoUrl = getCourseLogo($course_item['judul_course']);
                $level = strtolower($course_item['level']);
                $progress = $is_enrolled ? (float)($enrollment_data['progress_percent'] ?? 0) : 0;
                $gradient_idx = abs(crc32($course_item['judul_course'] ?? '')) % count($gradients);
                $g = $gradients[$gradient_idx];
                $catColor = getCategoryColor($course_item['nama_kategori'] ?? '');
                $catEmoji = getCategoryEmoji($course_item['nama_kategori'] ?? '');
                $rating = (float)$course_item['rating'];
                $starDisplay = $rating > 0 ? str_repeat('★', floor($rating)) . (($rating - floor($rating)) >= 0.5 ? '½' : '') : '☆☆☆☆☆';
                $courseCat = $course_item['nama_kategori'] ?? 'Programming';
                $catSlug = strtolower(str_replace([' ', '/'], '-', trim($courseCat)));
            ?>
            <a href="course-detail.php?id=<?php echo $course_item['id']; ?>" class="course-card"
               data-category="<?php echo htmlspecialchars($catSlug); ?>"
               data-title="<?php echo htmlspecialchars(strtolower($course_item['judul_course'])); ?>"
               data-desc="<?php echo htmlspecialchars(strtolower($course_item['deskripsi'] ?? '')); ?>"
               style="--card-grad:<?php echo $g; ?>;--card-grad-start:<?php echo substr($g, strpos($g, '#'), 7); ?>;--card-grad-end:<?php echo substr($g, strrpos($g, '#'), 7); ?>;--thumb-bg:<?php echo $g; ?>10">
                <div class="course-thumb">
                    <?php if ($logoUrl): ?>
                        <img src="<?php echo $logoUrl; ?>" alt="">
                    <?php else: ?>
                        <span style="font-size:2rem;opacity:0.35"><?php echo $catEmoji; ?></span>
                    <?php endif; ?>
                    <div class="level-badge <?php echo $level; ?>"><?php echo ucfirst($course_item['level']); ?></div>
                </div>
                <div class="course-body">
                    <div class="cat-lbl"><?php echo $catEmoji; ?> <?php echo htmlspecialchars($courseCat); ?></div>
                    <h3><?php echo htmlspecialchars($course_item['judul_course']); ?></h3>
                    <div class="desc"><?php echo htmlspecialchars(mb_substr($course_item['deskripsi'] ?: '', 0, 120)); ?></div>
                    <div class="course-meta">
                        <div class="cm-item"><div class="cm-val">📚 <?php echo (int)$course_item['total_lessons']; ?></div><div class="cm-lbl">Materi</div></div>
                        <div class="cm-item"><div class="cm-val">⏱ <?php echo (int)$course_item['durasi_jam']; ?>j</div><div class="cm-lbl">Durasi</div></div>
                        <div class="cm-item"><div class="cm-val">👥 <?php echo (int)$course_item['total_students']; ?></div><div class="cm-lbl">Siswa</div></div>
                    </div>
                    <?php if ($rating > 0): ?>
                    <div class="course-rating">
                        <span class="stars"><?php echo $starDisplay; ?></span>
                        <span class="rval"><?php echo number_format($rating, 1); ?></span>
                        <span class="rcount">(<?php echo (int)$course_item['total_students']; ?>)</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($is_enrolled && $progress > 0): ?>
                    <div class="course-progress">
                        <div class="cp-row"><span>Progress</span><span><?php echo number_format($progress, 0); ?>%</span></div>
                        <div class="cp-bar"><div class="cp-fill" style="width:<?php echo $progress; ?>%"></div></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($progress >= 100): ?>
                    <div class="course-btn success">🎉 Selesai</div>
                    <?php elseif ($is_enrolled): ?>
                    <div class="course-btn primary">🚀 Lanjutkan</div>
                    <?php else: ?>
                    <div class="course-btn">📖 Mulai Belajar</div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

</div></div>

<?php include '../includes/toast.php'; ?>
<?php include '../includes/loading.php'; ?>
<script src="../assets/js/navbar.js"></script>
<script>
(function(){
var $ = function(id) { return document.getElementById(id); };

// ─── COUNTERS ───
function animateCounters() {
    document.querySelectorAll('.lh-stat-val[data-target]').forEach(function(el) {
        var target = parseInt(el.dataset.target);
        if (isNaN(target)) return;
        var current = 0, step = Math.max(1, Math.floor(target / 60));
        var dur = 1500, interval = Math.max(10, Math.floor(dur / (target / step)));
        var timer = setInterval(function() {
            current = Math.min(current + step, target);
            el.textContent = current >= 1000 ? (current / 1000).toFixed(1).replace(/\.0$/, '') + 'k+' : current + '+';
            if (current >= target) clearInterval(timer);
        }, interval);
    });
}

// ─── SCROLL REVEAL ───
function initReveal() {
    var obs = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                setTimeout(function() { entry.target.classList.add('is-revealed'); }, 50);
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.05, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.reveal').forEach(function(el) { obs.observe(el); });
}

// ─── FEATURED SLIDER ───
function initSlider() {
    var slides = document.querySelectorAll('.featured-slide');
    var dots = document.querySelectorAll('.featured-dot');
    if (!slides.length) return;
    var current = 0;
    function goTo(idx) {
        slides.forEach(function(s, i) { s.classList.toggle('active', i === idx); });
        dots.forEach(function(d, i) { d.classList.toggle('active', i === idx); });
        current = idx;
    }
    dots.forEach(function(d) {
        d.addEventListener('click', function() { goTo(parseInt(d.dataset.idx)); });
    });
    setInterval(function() { goTo((current + 1) % slides.length); }, 5000);
}

// ─── FILTER COURSES ───
var currentFilter = 'all';
var searchQuery = '';

function filterCourses() {
    var query = ($('searchInput').value || '').toLowerCase().trim();
    var cards = document.querySelectorAll('.course-card');
    var count = 0;
    cards.forEach(function(card) {
        var cat = (card.dataset.category || '').toLowerCase();
        var title = (card.dataset.title || '').toLowerCase();
        var desc = (card.dataset.desc || '').toLowerCase();
        var matchFilter = (currentFilter === 'all') || (cat.indexOf(currentFilter) !== -1);
        var matchSearch = !query || title.indexOf(query) !== -1 || desc.indexOf(query) !== -1;
        if (matchFilter && matchSearch) {
            card.style.display = '';
            count++;
            setTimeout(function() { card.style.opacity = '1'; card.style.transform = ''; }, 50);
        } else {
            card.style.display = 'none';
        }
    });
    $('resultCount').textContent = count;
    $('resultCountLabel').textContent = count + ' course';
}

$('searchInput').addEventListener('input', filterCourses);

document.querySelectorAll('.filter-chip').forEach(function(chip) {
    chip.addEventListener('click', function() {
        document.querySelectorAll('.filter-chip').forEach(function(c) { c.classList.remove('active'); });
        chip.classList.add('active');
        currentFilter = chip.dataset.filter;
        filterCourses();
    });
});

window.filterByCategory = function(slug) {
    document.querySelectorAll('.filter-chip').forEach(function(c) { c.classList.remove('active'); });
    var matchedChip = document.querySelector('.filter-chip[data-filter="' + slug + '"]');
    if (matchedChip) { matchedChip.classList.add('active'); currentFilter = slug; }
    else { currentFilter = slug; }
    filterCourses();
    var target = document.getElementById('filterChips');
    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

// ─── CARD ENTRANCE STAGGER ───
function staggerCards() {
    var cards = document.querySelectorAll('.course-card');
    cards.forEach(function(c, i) {
        c.style.opacity = '0';
        c.style.transform = 'translateY(20px)';
        setTimeout(function() {
            c.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            c.style.opacity = '1';
            c.style.transform = 'translateY(0)';
        }, 80 + i * 50);
    });
}

// ─── INIT ───
document.addEventListener('DOMContentLoaded', function() {
    animateCounters();
    initReveal();
    initSlider();
    filterCourses();
    staggerCards();
});
})();
</script>
</body>
</html>
