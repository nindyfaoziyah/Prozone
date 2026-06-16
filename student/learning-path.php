<?php
require_once '../config/config.php';
requireLogin();
require_once '../includes/icons.php';

$page_title       = 'Learning Path — RPG World Map';
$page_description = 'Jelajahi dunia coding dalam petualangan RPG interaktif.';
$page_css         = ['sidebar-island.css', 'dashboard-override.css'];
$body_class       = trim(getThemeClass() . ' dashboard-layout');

$levels = [
    ['id' => 1, 'name' => 'HTML Forest',          'emoji' => '🌳', 'desc' => 'Kuasi markup language sebagai fondasi web.',
        'reward' => 120, 'materi' => 8, 'durasi' => '30 min', 'color' => '#F97316',
        'skills' => ['HTML Structure', 'Semantic', 'Forms', 'Media', 'Accessibility'],
        'boss' => 'Buat halaman profil pribadi', 'course_id' => 1,
        'quests' => [
            ['label' => 'Pengenalan HTML',       'skill' => 'Struktur'],
            ['label' => 'Struktur Dasar HTML',   'skill' => 'Struktur'],
            ['label' => 'Heading & Paragraph',   'skill' => 'Teks'],
            ['label' => 'Link & Image',          'skill' => 'Media'],
            ['label' => 'List & Table',          'skill' => 'Data'],
            ['label' => 'Form & Input',          'skill' => 'Interaksi'],
            ['label' => 'Semantic HTML',         'skill' => 'Semantik'],
            ['label' => 'Multimedia & Embed',    'skill' => 'Media'],
        ]],
    ['id' => 2, 'name' => 'CSS Desert',           'emoji' => '🏜️', 'desc' => 'Ciptakan visual memukau dengan style modern.',
        'reward' => 150, 'materi' => 7, 'durasi' => '35 min', 'color' => '#2563EB',
        'skills' => ['Selectors', 'Color', 'Typography', 'Flexbox', 'Grid'],
        'boss' => 'Buat landing page futuristik', 'course_id' => 1,
        'quests' => [
            ['label' => 'CSS Dasar & Selector',  'skill' => 'Dasar'],
            ['label' => 'Color & Typography',    'skill' => 'Desain'],
            ['label' => 'Box Model & Layout',    'skill' => 'Layout'],
            ['label' => 'Flexbox',               'skill' => 'Layout'],
            ['label' => 'Grid Layout',           'skill' => 'Layout'],
            ['label' => 'Responsive Design',     'skill' => 'Responsif'],
            ['label' => 'CSS Animation',         'skill' => 'Animasi'],
        ]],
    ['id' => 3, 'name' => 'JavaScript City',      'emoji' => '🌆', 'desc' => 'Hidupkan web dengan logika dan interaksi.',
        'reward' => 200, 'materi' => 8, 'durasi' => '45 min', 'color' => '#F59E0B',
        'skills' => ['Variables', 'Functions', 'DOM', 'Events', 'Async'],
        'boss' => 'Buat kalkulator pintar', 'course_id' => 4,
        'quests' => [
            ['label' => 'Variable & Tipe Data',  'skill' => 'Dasar'],
            ['label' => 'Operator & Ekspresi',   'skill' => 'Logika'],
            ['label' => 'Conditional & Loop',    'skill' => 'Logika'],
            ['label' => 'Function',              'skill' => 'Fungsi'],
            ['label' => 'Array & Object',        'skill' => 'Data'],
            ['label' => 'DOM Manipulation',      'skill' => 'DOM'],
            ['label' => 'Event Handling',        'skill' => 'Interaksi'],
            ['label' => 'Async & Fetch API',     'skill' => 'Async'],
        ]],
    ['id' => 4, 'name' => 'React Kingdom',        'emoji' => '👑', 'desc' => 'Bangun antarmuka modern dengan komponen.',
        'reward' => 225, 'materi' => 7, 'durasi' => '55 min', 'color' => '#22C55E',
        'skills' => ['JSX', 'Components', 'State', 'Props', 'Hooks'],
        'boss' => 'Buat dashboard interaktif', 'course_id' => 9,
        'quests' => [
            ['label' => 'JSX & Komponen',        'skill' => 'Dasar'],
            ['label' => 'Props & State',         'skill' => 'State'],
            ['label' => 'Event Handler',         'skill' => 'Event'],
            ['label' => 'Conditional Rendering', 'skill' => 'Logic'],
            ['label' => 'List & Keys',           'skill' => 'Data'],
            ['label' => 'Hooks (useState)',      'skill' => 'Hooks'],
            ['label' => 'useEffect & Lifecycle', 'skill' => 'Hooks'],
        ]],
    ['id' => 5, 'name' => 'API Ocean',            'emoji' => '🌊', 'desc' => 'Hubungkan aplikasi dengan data dan layanan.',
        'reward' => 230, 'materi' => 6, 'durasi' => '50 min', 'color' => '#06B6D4',
        'skills' => ['HTTP', 'Endpoints', 'CRUD', 'Auth', 'JSON'],
        'boss' => 'Integrasi sistem API realtime', 'course_id' => 10,
        'quests' => [
            ['label' => 'HTTP & RESTful API',    'skill' => 'Dasar'],
            ['label' => 'Endpoint & Routing',    'skill' => 'Route'],
            ['label' => 'Request & Response',    'skill' => 'Komunikasi'],
            ['label' => 'Authentication',        'skill' => 'Keamanan'],
            ['label' => 'CRUD Operations',       'skill' => 'Data'],
            ['label' => 'JSON & Data Handling',  'skill' => 'Data'],
        ]],
    ['id' => 6, 'name' => 'NextJS Citadel',       'emoji' => '🏰', 'desc' => 'Raih kekuatan full-stack dengan React.',
        'reward' => 250, 'materi' => 7, 'durasi' => '60 min', 'color' => '#8B5CF6',
        'skills' => ['SSR', 'Routing', 'API', 'Deploy', 'Optimization'],
        'boss' => 'Deploy aplikasi full-stack', 'course_id' => 3,
        'quests' => [
            ['label' => 'NextJS Setup',          'skill' => 'Dasar'],
            ['label' => 'Pages & Routing',       'skill' => 'Route'],
            ['label' => 'SSR & SSG',             'skill' => 'Rendering'],
            ['label' => 'API Routes',            'skill' => 'Backend'],
            ['label' => 'Database Integration',  'skill' => 'Data'],
            ['label' => 'Authentication',        'skill' => 'Keamanan'],
            ['label' => 'Deployment',            'skill' => 'Deploy'],
        ]],
    ['id' => 7, 'name' => 'Performance Mountain', 'emoji' => '⛰️', 'desc' => 'Optimalkan aplikasi hingga level profesional.',
        'reward' => 270, 'materi' => 6, 'durasi' => '50 min', 'color' => '#EC4899',
        'skills' => ['Lighthouse', 'Lazy Loading', 'Caching', 'Bundle', 'Monitoring'],
        'boss' => 'Audit & optimasi aplikasi riil', 'course_id' => 3,
        'quests' => [
            ['label' => 'Web Vitals',            'skill' => 'Metrics'],
            ['label' => 'Lazy Loading & Code Split','skill' => 'Optimasi'],
            ['label' => 'Image Optimization',    'skill' => 'Media'],
            ['label' => 'Caching Strategy',      'skill' => 'Cache'],
            ['label' => 'Bundle Analysis',       'skill' => 'Build'],
            ['label' => 'Monitoring & Debug',    'skill' => 'Tools'],
        ]],
    ['id' => 8, 'name' => 'Frontend Master Castle','emoji' => '🏆', 'desc' => 'Capai puncak karir frontend developer.',
        'reward' => 350, 'materi' => 7, 'durasi' => '70 min', 'color' => '#6366F1',
        'skills' => ['Architecture', 'Testing', 'CI/CD', 'System Design', 'Leadership'],
        'boss' => 'Bangun aplikasi flagship end-to-end', 'course_id' => 3,
        'quests' => [
            ['label' => 'Arsitektur Frontend',   'skill' => 'Arch'],
            ['label' => 'Testing (Jest/RTL)',    'skill' => 'QA'],
            ['label' => 'CI/CD Pipeline',        'skill' => 'DevOps'],
            ['label' => 'Monorepo & Workspace',  'skill' => 'Tooling'],
            ['label' => 'Storybook & Design Sys','skill' => 'DS'],
            ['label' => 'Performance Budget',    'skill' => 'Perf'],
            ['label' => 'Code Review & Best Practice','skill' => 'Lead'],
        ]],
];

$roles = [
    'frontend' => ['name' => 'Frontend Developer', 'icon' => '🎨', 'color' => '#6366F1',
        'desc' => 'Bangun antarmuka web yang indah, interaktif, dan responsif.',
        'levels' => [1,2,3,4,5,6,7,8]],
    'backend' => ['name' => 'Backend Developer', 'icon' => '⚙️', 'color' => '#06B6D4',
        'desc' => 'Kuasai server, database, API, dan arsitektur aplikasi.',
        'levels' => [5,6,7]],
    'fullstack' => ['name' => 'Full Stack Developer', 'icon' => '🚀', 'color' => '#F59E0B',
        'desc' => 'Frontend hingga backend — jadi developer serba bisa!',
        'levels' => [1,2,3,4,5,6,7,8]],
    'mobile' => ['name' => 'Mobile Developer', 'icon' => '📱', 'color' => '#22C55E',
        'desc' => 'Kembangkan aplikasi mobile modern dengan React Native.',
        'levels' => [3,4,6]],
    'uiux' => ['name' => 'UI/UX Designer', 'icon' => '🖌️', 'color' => '#EC4899',
        'desc' => 'Desain pengalaman digital yang indah dan manusiawi.',
        'levels' => [1,2,4]],
    'data' => ['name' => 'Data Science', 'icon' => '📊', 'color' => '#0EA5E9',
        'desc' => 'Olah data, bangun model, dan temukan insight berharga.',
        'levels' => [5,7]],
    'cyber' => ['name' => 'Cyber Security', 'icon' => '🛡️', 'color' => '#EF4444',
        'desc' => 'Lindungi sistem dan data dari ancaman digital.',
        'levels' => [5,6,7]],
    'ai' => ['name' => 'AI Engineer', 'icon' => '🤖', 'color' => '#8B5CF6',
        'desc' => 'Ciptakan sistem cerdas dengan machine learning & AI.',
        'levels' => [3,5,6,7]],
];

$totalLevels = count($levels);

// Get real quest progress from database
$user_id = $_SESSION['user_id'] ?? 0;
$quest_progress = [];
$quest_detail = [];
if ($user_id && isset($db)) {
    $stmt = $db->prepare("SELECT level_id, COUNT(*) as completed FROM user_quest_progress WHERE user_id = :uid AND status = 'completed' GROUP BY level_id");
    $stmt->execute([':uid' => $user_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $quest_progress[(int)$row['level_id']] = (int)$row['completed'];
    }

    $stmt2 = $db->prepare("SELECT level_id, quest_idx, status FROM user_quest_progress WHERE user_id = :uid");
    $stmt2->execute([':uid' => $user_id]);
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        $lid = (int)$row['level_id'];
        $qidx = (int)$row['quest_idx'];
        if (!isset($quest_detail[$lid])) $quest_detail[$lid] = [];
        $quest_detail[$lid][$qidx] = $row['status'];
    }
}

// Determine level status and progress
$current_found = false;
foreach ($levels as &$lvl) {
    $completed_q = $quest_progress[$lvl['id']] ?? 0;
    $total_q = $lvl['materi'];
    $lvl['completed_quests'] = $completed_q;
    $lvl['progress'] = $total_q > 0 ? round(($completed_q / $total_q) * 100) : 0;

    if (!$current_found) {
        if ($lvl['progress'] >= 100) {
            $lvl['status'] = 'completed';
        } else {
            $lvl['status'] = 'in-progress';
            $current_found = true;
        }
    } else {
        $lvl['status'] = 'locked';
    }
}
unset($lvl);
$totalXP = array_sum(array_column($levels, 'reward'));
$totalQuest = array_sum(array_column($levels, 'materi'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/head.php'; ?>
<style>
/* ===== LIGHT THEME — RPG WORLD MAP ===== */
:root {
    --primary: #6366F1;
    --primary-light: rgba(99,102,241,0.08);
    --primary-mid: rgba(99,102,241,0.12);
    --suc: #22C55E;
    --suc-light: rgba(34,197,94,0.08);
    --gold: #F59E0B;
    --bg: #F8FAFC;
    --card: #FFFFFF;
    --card-border: #E2E8F0;
    --border-light: #F1F5F9;
    --text-p: #0F172A;
    --text-s: #475569;
    --text-m: #94A3B8;
    --radius: 14px;
    --shadow-sm: 0 1px 2px rgba(0,0,0,0.04);
    --shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.06), 0 2px 4px -1px rgba(0,0,0,0.04);
    --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.06), 0 4px 6px -2px rgba(0,0,0,0.06);
}
* { box-sizing: border-box; }

body.dashboard-layout .page-wrapper.dashboard-main-container {
    background: var(--bg) !important;
}
body.dashboard-layout .dashboard-content {
    max-width: 1400px;
}

/* ===== REAL-TIME BUBBLE ANIMATION ===== */
.rpg-particles {
    position: absolute; inset: 0; overflow: hidden;
    pointer-events: none; z-index: 0;
}
.rpg-particles span {
    position: absolute; border-radius: 50%;
    background: radial-gradient(circle at 30% 30%, rgba(99,102,241,0.18), rgba(99,102,241,0.03));
    border: 1px solid rgba(99,102,241,0.15);
    opacity: 0; animation: floatBubble 14s infinite ease-in-out;
}
@keyframes floatBubble {
    0% { opacity: 0; transform: translateY(0) translateX(0) scale(0.6); }
    12% { opacity: 1; }
    80% { opacity: 0.5; }
    100% { opacity: 0; transform: translateY(-350px) translateX(50px) scale(1.15); }
}

/* ===== GRID OVERLAY (subtle) ===== */
.grid-overlay {
    position: absolute; inset: 0; overflow: hidden;
    pointer-events: none; z-index: 0;
    background-image:
        linear-gradient(rgba(148,163,184,0.06) 1px, transparent 1px),
        linear-gradient(90deg, rgba(148,163,184,0.06) 1px, transparent 1px);
    background-size: 60px 60px;
}

/* ===== WORLD MAP CONTAINER ===== */
.rpg-world {
    position: relative; display: flex; flex-direction: column;
    min-height: 0; width: 100%; padding: 0;
}
.step { display: flex; flex-direction: column; min-height: 0; position: relative; z-index: 1; }

/* ===== HERO / ROLE SELECTION ===== */
.hero { padding: 6px 0 0; }
.hero-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 16px; border-radius: 999px;
    background: var(--primary-light); border: 1px solid var(--primary-mid);
    color: var(--primary); font-size: .65rem; font-weight: 800;
    letter-spacing: .06em; margin-bottom: 8px;
}
.hero h1 { font-size: clamp(1.4rem,2.5vw,2rem); font-weight: 900; color: var(--text-p); margin: 0 0 4px; }
.hero h1 span { background: linear-gradient(135deg,var(--primary),#8B5CF6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.hero p { color: var(--text-s); font-size: .85rem; margin: 0 0 16px; max-width: 520px; line-height: 1.6; }
.hero-stats { display: flex; gap: 18px; margin-bottom: 22px; flex-wrap: wrap; }
.hero-stat { text-align: center; min-width: 70px; }
.hero-stat-val { font-size: 1.2rem; font-weight: 900; background: linear-gradient(135deg,var(--primary),#8B5CF6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; line-height: 1.2; }
.hero-stat-lbl { font-size: .62rem; font-weight: 600; color: var(--text-m); text-transform: uppercase; letter-spacing: .04em; margin-top: 1px; }

.roles-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(185px, 1fr));
    gap: 12px;
}
.role-card {
    position: relative; background: var(--card); border: 1px solid var(--card-border);
    border-radius: var(--radius); padding: 18px 16px 16px;
    cursor: pointer; transition: all .25s ease;
    overflow: hidden; box-shadow: var(--shadow-sm);
}
.role-card:hover { transform: translateY(-3px); border-color: var(--primary); box-shadow: var(--shadow-md); }
.role-card::before {
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(circle at 50% 0%, color-mix(in srgb, var(--rc) 12%, transparent), transparent 70%);
    opacity: 0; transition: opacity .4s; pointer-events: none;
}
.role-card:hover::before { opacity: 1; }
.role-icon {
    width: 42px; height: 42px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; margin-bottom: 10px; border: 1px solid var(--border-light);
    background: var(--bg);
}
.role-card h3 { font-size: .85rem; font-weight: 800; color: var(--text-p); margin: 0 0 3px; }
.role-card p { font-size: .68rem; color: var(--text-s); margin: 0 0 10px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.role-meta { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 8px; }
.role-meta span {
    font-size: .6rem; font-weight: 600; color: var(--text-s);
    padding: 2px 8px; border-radius: 8px;
    background: var(--bg); border: 1px solid var(--border-light);
}
.role-progress { height: 4px; background: var(--border-light); border-radius: 4px; overflow: hidden; }
.role-progress-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg,var(--rc),var(--primary)); transition: width .6s ease; }

/* ===== SUMMARY BAR ===== */
.summary-bar {
    background: rgba(255,255,255,0.85); backdrop-filter: blur(12px);
    border: 1px solid var(--card-border); border-radius: 16px;
    padding: 14px 20px; display: flex; align-items: center;
    gap: 16px; flex-wrap: wrap; margin-bottom: 16px;
    box-shadow: var(--shadow);
}
.btn-back {
    width: 36px; height: 36px; border-radius: 50%; border: 1px solid var(--card-border);
    background: var(--card); color: var(--text-s); font-size: 1rem; line-height: 1;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: all .2s; flex-shrink: 0; padding: 0;
}
.btn-back:hover { border-color: var(--primary); color: var(--primary); box-shadow: 0 0 0 2px rgba(99,102,241,0.1); }
.btn-back:active { transform: scale(0.92); }
.summary-role {
    display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0;
}
.summary-role-icon { font-size: 1.5rem; }
.summary-role h2 { font-size: .95rem; font-weight: 800; color: var(--text-p); margin: 0; }
.summary-role span { font-size: .6rem; font-weight: 600; color: var(--primary); text-transform: uppercase; letter-spacing: .06em; }
.summary-stats { display: flex; gap: 10px; flex-wrap: wrap; }
.summary-stat {
    text-align: center; min-width: 48px;
}
.summary-stat strong { display: block; font-size: .8rem; font-weight: 800; color: var(--text-p); }
.summary-stat span { font-size: .5rem; font-weight: 600; color: var(--text-m); text-transform: uppercase; letter-spacing: .04em; }
.summary-progress { display: flex; align-items: center; gap: 10px; min-width: 120px; }
.summary-track { flex: 1; height: 5px; background: var(--border-light); border-radius: 4px; overflow: hidden; }
.summary-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg,var(--primary),#8B5CF6); transition: width .8s; }

/* ===== MAP LAYOUT ===== */
.map-layout {
    display: grid; grid-template-columns: 1fr 320px; gap: 20px;
    flex: 1; min-height: 0;
}

/* ===== ZIG-ZAG WORLD MAP ===== */
.world-map {
    position: relative; overflow-y: auto; min-height: 0;
    padding: 24px 16px 40px;
}
.world-map::-webkit-scrollbar { width: 4px; }
.world-map::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }

.map-grid {
    display: flex; flex-direction: column; gap: 28px;
    position: relative;
}

.map-svg {
    position: absolute; top: 0; left: 0;
    pointer-events: none; z-index: 1;
}

/* ===== NODE CARD ===== */
.node-card {
    display: flex; align-items: center; gap: 16px;
    position: relative; z-index: 2; animation: fadeNode .4s ease forwards;
    opacity: 0; cursor: pointer;
}
@keyframes fadeNode { to { opacity: 1; } }
.node-card--left { flex-direction: row; padding-right: 42%; }
.node-card--right { flex-direction: row-reverse; padding-left: 42%; }
.node-card--left .node-body { text-align: left; }
.node-card--right .node-body { text-align: right; }
.node-card--left .node-marker { order: -1; }
.node-card--right .node-marker { order: 1; }

.node-marker {
    width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.05rem; font-weight: 800; transition: all .3s;
    position: relative; z-index: 3;
}
.node-marker--done {
    background: linear-gradient(135deg, var(--suc), #059669);
    box-shadow: 0 0 0 4px rgba(34,197,94,0.12), 0 0 16px rgba(34,197,94,0.15);
    color: #fff;
}
.node-marker--current {
    background: linear-gradient(135deg, var(--primary), #8B5CF6);
    box-shadow: 0 0 0 4px rgba(99,102,241,0.12), 0 0 20px rgba(99,102,241,0.15);
    color: #fff; animation: markerPulse 2s infinite;
}
.node-marker--locked {
    background: #F1F5F9; border: 2px solid #E2E8F0;
    color: #CBD5E1;
}
@keyframes markerPulse {
    0%,100% { box-shadow: 0 0 0 4px rgba(99,102,241,0.12), 0 0 20px rgba(99,102,241,0.15); }
    50% { box-shadow: 0 0 0 8px rgba(99,102,241,0.05), 0 0 30px rgba(99,102,241,0.08); }
}

.node-body {
    flex: 1; min-width: 0; padding: 14px 60px 14px 18px;
    border-radius: var(--radius); transition: all .3s;
    border: 1px solid var(--card-border); position: relative; overflow: hidden;
    background: linear-gradient(135deg,
        color-mix(in srgb, var(--lc) 5%, var(--card)) 0%,
        var(--card) 45%
    );
}
/* Right-aligned cards: badge on left, pad left side instead */
.node-card--right .node-body {
    padding: 14px 18px 14px 60px;
}
.node-card--right .node-badge {
    right: auto;
    left: 10px;
}
/* Colored accent bar per course */
.node-body::before {
    content: '';
    position: absolute; left: 0; top: 6px; bottom: 6px; width: 4px;
    border-radius: 0 3px 3px 0;
    background: color-mix(in srgb, var(--lc) 45%, transparent);
    transition: all .3s;
}
.node-body--done::before { background: color-mix(in srgb, var(--lc) 30%, rgba(34,197,94,0.35)); }
.node-body--current::before { background: color-mix(in srgb, var(--lc) 30%, rgba(99,102,241,0.35)); }

.node-body--done {
    border-color: color-mix(in srgb, var(--lc) 25%, rgba(34,197,94,0.25));
}
.node-body--done .node-level { color: var(--suc); }
.node-body--done:hover {
    border-color: color-mix(in srgb, var(--lc) 50%, transparent);
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--lc) 12%, transparent),
                0 0 24px color-mix(in srgb, var(--lc) 16%, transparent);
}

.node-body--current {
    border-color: color-mix(in srgb, var(--lc) 25%, rgba(99,102,241,0.2));
}
.node-body--current .node-level { color: var(--primary); }
.node-body--current:hover {
    border-color: color-mix(in srgb, var(--lc) 55%, transparent);
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--lc) 12%, transparent),
                0 0 24px color-mix(in srgb, var(--lc) 16%, transparent);
}

.node-body--locked {
    opacity: 0.4;
}
.node-body--locked:hover {
    opacity: 0.55;
    box-shadow: 0 0 0 1px color-mix(in srgb, var(--lc) 10%, transparent);
}

.node-body--selected {
    border-color: color-mix(in srgb, var(--lc) 70%, transparent) !important;
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--lc) 20%, transparent),
                0 0 32px color-mix(in srgb, var(--lc) 18%, transparent) !important;
}

.node-level { font-size: .52rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; margin-bottom: 2px; color: var(--text-m); }
.node-title { font-size: .88rem; font-weight: 800; color: var(--text-p); margin: 0 0 2px; }
.node-reward { font-size: .7rem; color: var(--text-s); font-weight: 600; }
.node-reward span { color: var(--gold); }

.node-badge {
    position: absolute; top: 8px; right: 10px;
    font-size: .55rem; font-weight: 700; padding: 2px 8px;
    border-radius: 999px;
}
.node-badge--done { background: var(--suc-light); color: var(--suc); border: 1px solid rgba(34,197,94,0.15); }
.node-badge--current { background: var(--primary-light); color: var(--primary); border: 1px solid var(--primary-mid); }
.node-badge--locked { background: #F1F5F9; color: #CBD5E1; border: 1px solid #E2E8F0; }

.node-progress { margin-top: 6px; height: 3px; background: var(--border-light); border-radius: 4px; overflow: hidden; }
.node-progress-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--primary), var(--suc)); transition: width .6s; }

/* ===== DETAIL PANEL (LIGHT) ===== */
.detail-panel {
    background: var(--card); border: 1px solid var(--card-border);
    border-radius: var(--radius); overflow-y: auto; max-height: 100%;
    box-shadow: var(--shadow-sm);
}
.detail-panel::-webkit-scrollbar { width: 4px; }
.detail-panel::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }

.detail-placeholder {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 50px 20px; text-align: center; min-height: 260px;
}
.detail-placeholder-icon { font-size: 2.6rem; margin-bottom: 10px; opacity: .35; }
.detail-placeholder h3 { font-size: .9rem; font-weight: 800; color: var(--text-p); margin: 0 0 4px; }
.detail-placeholder p { font-size: .72rem; color: var(--text-m); margin: 0; max-width: 200px; line-height: 1.5; }

.detail-body { padding: 18px 16px; }

.detail-head { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
.detail-head-icon {
    width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; box-shadow: var(--shadow-sm);
}
.detail-head-info { flex: 1; min-width: 0; }
.detail-head-level { font-size: .55rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
.detail-head-level--done { color: var(--suc); }
.detail-head-level--current { color: var(--primary); }
.detail-head-level--locked { color: var(--text-m); }
.detail-head-title { font-size: .9rem; font-weight: 800; color: var(--text-p); margin: 1px 0 0; }

.detail-ring-wrap { flex-shrink: 0; }
.detail-ring { width: 52px; height: 52px; display: block; }
.detail-ring circle.bg { stroke: var(--border-light); }

.detail-stats {
    display: grid; grid-template-columns: repeat(3,1fr); gap: 5px; margin-bottom: 12px;
}
.detail-stat {
    padding: 7px 5px; background: var(--bg);
    border: 1px solid var(--border-light); border-radius: 10px; text-align: center;
}
.detail-stat-icon { font-size: .8rem; display: block; margin-bottom: 1px; }
.detail-stat-label { font-size: .52rem; color: var(--text-m); font-weight: 500; display: block; margin-bottom: 1px; }
.detail-stat strong { font-size: .72rem; color: var(--text-p); font-weight: 800; display: block; }

.detail-section { margin-bottom: 12px; }
.detail-section-title {
    font-size: .65rem; font-weight: 700; color: var(--text-m);
    margin-bottom: 6px; display: flex; align-items: center; gap: 4px;
    text-transform: uppercase; letter-spacing: .04em;
}

.detail-boss {
    display: flex; align-items: center; gap: 6px;
    padding: 7px 10px; background: rgba(245,158,11,.05);
    border: 1px solid rgba(245,158,11,.1); border-radius: 10px;
    font-size: .7rem; font-weight: 600; color: var(--gold);
}

.detail-quests { display: flex; flex-direction: column; gap: 3px; }
.detail-quest {
    display: flex; align-items: center; gap: 6px;
    padding: 5px 8px; border-radius: 8px;
    background: var(--bg); border: 1px solid var(--border-light);
    font-size: .7rem; color: var(--text-s); font-weight: 500;
}
.detail-quest-check {
    width: 18px; height: 18px; border-radius: 6px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: .5rem; font-weight: 800;
}
.detail-quest-check--done { background: var(--suc-light); color: var(--suc); }
.detail-quest-check--active { background: var(--primary-light); color: var(--primary); }
.detail-quest-check--locked { background: #F1F5F9; color: #CBD5E1; }

.detail-btn {
    display: block; width: 100%; padding: 10px 0;
    border: none; border-radius: 12px;
    font-size: .8rem; font-weight: 700;
    cursor: pointer; transition: all .2s; font-family: inherit;
    margin-top: 4px;
}
.detail-btn--active {
    background: linear-gradient(135deg, var(--primary), #8B5CF6);
    color: #fff; box-shadow: var(--shadow-sm);
}
.detail-btn--active:hover { transform: translateY(-1px); box-shadow: var(--shadow-md); }
.detail-btn--done {
    background: linear-gradient(135deg, var(--suc), #059669);
    color: #fff; box-shadow: var(--shadow-sm);
}
.detail-btn--done:hover { transform: translateY(-1px); box-shadow: var(--shadow-md); }
.detail-btn--locked { background: #F1F5F9; color: #CBD5E1; cursor: not-allowed; }

.detail-skills { display: flex; flex-wrap: wrap; gap: 4px; }
.detail-skill {
    font-size: .6rem; font-weight: 600; padding: 3px 8px;
    border-radius: 8px; background: var(--bg);
    border: 1px solid var(--border-light); color: var(--text-s);
}

/* ===== PROGRESS CIRCLE COLOR STATES ===== */
.detail-ring circle.pg-0 { stroke: #CBD5E1 !important; }
.detail-ring circle.pg-25 { stroke: #93C5FD !important; }
.detail-ring circle.pg-50 { stroke: #6366F1 !important; }
.detail-ring circle.pg-75 { stroke: #22D3EE !important; }
.detail-ring circle.pg-100 { stroke: #22C55E !important; filter: drop-shadow(0 0 6px rgba(34,197,94,0.5)); }
.detail-ring text.pg-glow { fill: #22C55E !important; font-weight: 900; }

/* Node progress bar colors */
.node-progress-fill.pg-0 { background: #E2E8F0; }
.node-progress-fill.pg-25 { background: linear-gradient(90deg, #93C5FD, #6366F1); }
.node-progress-fill.pg-50 { background: linear-gradient(90deg, #6366F1, #6366F1); }
.node-progress-fill.pg-75 { background: linear-gradient(90deg, #6366F1, #22D3EE); }
.node-progress-fill.pg-100 { background: linear-gradient(90deg, var(--suc), #059669); }

/* ===== LEGEND ===== */
.map-legend {
    display: flex; gap: 16px; padding: 4px 0 10px; flex-wrap: wrap;
}
.map-legend-item {
    display: flex; align-items: center; gap: 6px;
    font-size: .6rem; font-weight: 600; color: var(--text-s);
}
.map-legend-dot {
    width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
}
.map-legend-dot--done { background: var(--suc); box-shadow: 0 0 0 2px rgba(34,197,94,0.15); }
.map-legend-dot--current { background: var(--primary); box-shadow: 0 0 0 2px rgba(99,102,241,0.15); }
.map-legend-dot--locked { background: #E2E8F0; }

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
    .map-layout { grid-template-columns: 1fr; gap: 14px; }
    .detail-panel { max-height: none; }
    .node-card--left { padding-right: 20%; }
    .node-card--right { padding-left: 20%; }
}
@media (max-width: 768px) {
    .node-card--left, .node-card--right { padding: 0; flex-direction: row !important; }
    .node-card--right .node-marker { order: -1 !important; }
    .node-card--right .node-body { text-align: left !important; padding: 12px 56px 12px 14px !important; }
    .node-card--right .node-badge { right: 10px !important; left: auto !important; }
    .roles-grid { grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 8px; }
    .role-card { padding: 14px 12px; }
    .summary-bar { flex-direction: row; align-items: center; gap: 10px; }
    .summary-stats { justify-content: space-around; }
    .summary-progress { min-width: 0; }
    .world-map { padding: 12px 8px 24px; }
    .map-grid { gap: 18px; }
    .node-body { padding: 12px 56px 12px 14px; }
    .detail-body { padding: 14px 12px; }
}
/* ===== MATERIAL VIEWER MODAL ===== */
.modal-overlay {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(15,23,42,0.45); backdrop-filter: blur(6px);
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
}
.modal-box {
    background: var(--card); border-radius: 18px;
    width: 100%; max-width: 620px; max-height: 90vh;
    padding: 28px 32px 22px; position: relative;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);
    display: flex; flex-direction: column;
}
.modal-x {
    position: absolute; top: 12px; right: 14px;
    width: 32px; height: 32px; border-radius: 50%; border: none;
    background: var(--bg); color: var(--text-m); font-size: .85rem;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: all .2s;
}
.modal-x:hover { background: #FEE2E2; color: #EF4444; }
.modal-head { margin-bottom: 20px; padding-right: 36px; }
.modal-badge {
    display: inline-block; font-size: .55rem; font-weight: 700;
    padding: 3px 10px; border-radius: 999px; text-transform: uppercase;
    letter-spacing: .05em; margin-bottom: 6px;
}
.modal-head h3 { font-size: 1.05rem; font-weight: 800; color: var(--text-p); margin: 0; }

.modal-slide {
    flex: 1; min-height: 200px; display: flex; flex-direction: column;
    align-items: center; justify-content: center; text-align: center;
    padding: 20px 10px;
    background: linear-gradient(135deg, var(--bg) 0%, var(--card) 60%);
    border-radius: 14px; border: 1px solid var(--border-light);
    margin-bottom: 16px;
}
.slide-num {
    font-size: .6rem; font-weight: 700; color: var(--text-m);
    text-transform: uppercase; letter-spacing: .08em; margin-bottom: 10px;
}
.slide-title {
    font-size: 1.25rem; font-weight: 800; color: var(--text-p);
    margin: 0 0 12px; line-height: 1.4; max-width: 460px;
}
.slide-skill {
    display: inline-block; font-size: .65rem; font-weight: 600;
    padding: 4px 14px; border-radius: 999px;
    background: var(--primary-light); color: var(--primary);
    border: 1px solid var(--primary-mid);
}

.modal-foot { flex-shrink: 0; }
.modal-track { height: 4px; background: var(--border-light); border-radius: 4px; overflow: hidden; margin-bottom: 14px; }
.modal-track-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--primary), #8B5CF6); transition: width .4s; }
.modal-nav { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; }
.mbtn {
    padding: 9px 22px; border-radius: 12px; border: 1px solid var(--card-border);
    background: var(--card); color: var(--text-s); font-size: .78rem;
    font-weight: 700; cursor: pointer; transition: all .2s; font-family: inherit;
}
.mbtn:hover { border-color: var(--primary); color: var(--primary); }
.mbtn--next { background: var(--primary); color: #fff; border-color: var(--primary); }
.mbtn--next:hover { background: #4f46e5; }
.mbtn--go { background: linear-gradient(135deg, var(--suc), #059669); color: #fff; border: none; padding: 9px 26px; }
.mbtn--go:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(34,197,94,0.25); }
.mbtn--prev { background: var(--bg); }

@media (max-width: 640px) {
    .modal-box { padding: 20px 18px 18px; }
    .slide-title { font-size: 1.05rem; }
    .modal-slide { min-height: 150px; padding: 14px 8px; }
    .mbtn { padding: 8px 16px; font-size: .72rem; }
}
</style>
</head>
<body class="<?php echo $body_class; ?>">
<?php require_once 'navbar.php'; ?>
<div class="page-wrapper dashboard-main-container">
<div class="dashboard-content">
<div class="rpg-world">

<!-- floating particles -->
<div class="rpg-particles" id="particles"></div>

<!-- grid overlay -->
<div class="grid-overlay"></div>

<!-- ===== STEP 1: ROLE SELECTION ===== -->
<div class="step" id="step-roles">
    <section class="hero">
        <div class="hero-badge">★ PILIH ROLE-MU</div>
        <h1>Choose Your <span>Destiny</span></h1>
        <p>Setiap role memiliki jalur petualangan berbeda. Pilih karir impianmu dan mulai perjalanan codingmu.</p>
        <div class="hero-stats">
            <div class="hero-stat">
                <span class="hero-stat-val"><?php echo $totalLevels; ?></span>
                <div class="hero-stat-lbl">Level</div>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-val">+<?php echo $totalXP; ?></span>
                <div class="hero-stat-lbl">Total XP</div>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-val"><?php echo $totalQuest; ?></span>
                <div class="hero-stat-lbl">Quest</div>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-val" id="hero-progress">0%</span>
                <div class="hero-stat-lbl">Progress</div>
            </div>
        </div>
    </section>
    <div class="roles-grid">
        <?php foreach ($roles as $rid => $role):
            $rl = array_filter($levels, fn($l) => in_array($l['id'], $role['levels']));
            $rl = array_values($rl);
            $rc = $role['color'];
        ?>
        <div class="role-card" style="--rc: <?php echo $rc; ?>" data-role="<?php echo $rid; ?>">
            <div class="role-icon"><?php echo $role['icon']; ?></div>
            <h3><?php echo $role['name']; ?></h3>
            <p><?php echo $role['desc']; ?></p>
            <div class="role-meta">
                <span>📚 <?php echo count($rl); ?> Level</span>
                <span>⭐ +<?php echo array_sum(array_column($rl, 'reward')); ?> XP</span>
            </div>
            <div class="role-progress">
                <div class="role-progress-fill" style="width:<?php echo round(array_sum(array_column($rl, 'reward')) / max($totalXP, 1) * 100); ?>%"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ===== STEP 2: WORLD MAP ===== -->
<div class="step" id="step-journey" style="display:none">
    <!-- TOP SUMMARY BAR -->
    <div class="summary-bar" id="summary-bar">
        <button class="btn-back" id="btn-back" title="Kembali ke pemilihan role">←</button>
        <div class="summary-role">
            <span class="summary-role-icon" id="s-role-icon"></span>
            <div>
                <span>Learning Path</span>
                <h2 id="s-role-name"></h2>
            </div>
        </div>
        <div class="summary-stats" id="s-stats"></div>
        <div class="summary-progress">
            <div class="summary-track"><div class="summary-fill" id="s-prog-fill" style="width:0%"></div></div>
            <span style="color:var(--primary);font-weight:800;font-size:.8rem;min-width:36px;text-align:right" id="s-prog-pct">0%</span>
        </div>
    </div>

    <div class="map-legend">
        <div class="map-legend-item"><span class="map-legend-dot map-legend-dot--done"></span> Selesai</div>
        <div class="map-legend-item"><span class="map-legend-dot map-legend-dot--current"></span> Aktif</div>
        <div class="map-legend-item"><span class="map-legend-dot map-legend-dot--locked"></span> Terkunci</div>
    </div>

    <div class="map-layout">
        <div class="world-map" id="world-map">
            <div class="map-grid" id="map-grid">
                <svg class="map-svg" id="map-svg"></svg>
            </div>
        </div>

        <!-- SIDE PANEL -->
        <aside class="detail-panel" id="panel">
            <div class="detail-placeholder" id="panel-placeholder">
                <div class="detail-placeholder-icon">🗺️</div>
                <h3>Pilih Level</h3>
                <p>Klik salah satu node di peta untuk melihat detail.</p>
            </div>
            <div class="detail-body" id="panel-body" style="display:none">
                <div class="detail-head">
                    <div class="detail-head-icon" id="d-icon"></div>
                    <div class="detail-head-info">
                        <span class="detail-head-level" id="d-level"></span>
                        <h3 class="detail-head-title" id="d-title"></h3>
                    </div>
                    <div class="detail-ring-wrap">
                        <svg class="detail-ring" viewBox="0 0 60 60">
                            <circle cx="30" cy="30" r="24" fill="none" class="bg" stroke-width="4"/>
                            <circle cx="30" cy="30" r="24" fill="none" stroke="var(--primary)" stroke-width="4"
                                stroke-dasharray="150.8" stroke-dashoffset="150.8" stroke-linecap="round"
                                id="d-ring" transform="rotate(-90 30 30)"/>
                            <text x="30" y="32" text-anchor="middle" fill="#0F172A" font-size="10" font-weight="800" id="d-ring-text">0%</text>
                        </svg>
                    </div>
                </div>
                <div class="detail-stats">
                    <div class="detail-stat">
                        <span class="detail-stat-icon">⭐</span>
                        <span class="detail-stat-label">XP Reward</span>
                        <strong id="d-xp">0</strong>
                    </div>
                    <div class="detail-stat">
                        <span class="detail-stat-icon">⏱</span>
                        <span class="detail-stat-label">Durasi</span>
                        <strong id="d-dur">-</strong>
                    </div>
                    <div class="detail-stat">
                        <span class="detail-stat-icon">📚</span>
                        <span class="detail-stat-label">Materi</span>
                        <strong id="d-mat">0</strong>
                    </div>
                </div>
                <div class="detail-section">
                    <div class="detail-section-title">🎯 Boss Quest</div>
                    <div class="detail-boss">
                        <span class="detail-boss-icon">🏆</span>
                        <span id="d-boss"></span>
                    </div>
                </div>
                <div class="detail-section">
                    <div class="detail-section-title">⚡ Skills</div>
                    <div class="detail-skills" id="d-skills"></div>
                </div>
                <div class="detail-section">
                    <div class="detail-section-title">📋 Quest List</div>
                    <div class="detail-quests" id="d-quests"></div>
                </div>
                <button class="detail-btn" id="d-btn">📖 Buka Materi</button>
            </div>
        </aside>
    </div>
</div>

</div></div></div>

<!-- Material Viewer Modal -->
<div class="modal-overlay" id="modal-materi" style="display:none">
  <div class="modal-box">
    <button class="modal-x" id="m-close">✕</button>
    <div class="modal-head">
      <span class="modal-badge" id="m-badge"></span>
      <h3 id="m-level-title"></h3>
    </div>
    <div class="modal-slide" id="m-slide">
      <div class="slide-num" id="m-num"></div>
      <h2 class="slide-title" id="m-slide-title"></h2>
      <span class="slide-skill" id="m-slide-skill"></span>
    </div>
    <div class="modal-foot">
      <div class="modal-track"><div class="modal-track-fill" id="m-track"></div></div>
      <div class="modal-nav">
        <button class="mbtn mbtn--prev" id="m-prev" style="display:none">← Sebelumnya</button>
        <button class="mbtn mbtn--next" id="m-next">Selanjutnya →</button>
        <button class="mbtn mbtn--go" id="m-go" style="display:none">🚀 Mulai Praktek Coding</button>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/loading.php'; ?>
<?php include '../includes/toast.php'; ?>
<script src="../assets/js/navbar.js"></script>
<script>
(function(){
var allLevels = <?php echo json_encode($levels, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
var roles = <?php echo json_encode($roles, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
var questDetail = <?php echo json_encode($quest_detail, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
var currentLevel = null;
var selectedRole = null;

var $ = function(id) { return document.getElementById(id); };
var stepRoles = $('step-roles');
var stepJourney = $('step-journey');
var mapGrid = $('map-grid');
var mapSvg = $('map-svg');
var panelPlaceholder = $('panel-placeholder');
var panelBody = $('panel-body');

// ===== PROGRESS COLOR HELPER =====
function getPgClass(pct) {
    if (pct >= 100) return 'pg-100';
    if (pct >= 75) return 'pg-75';
    if (pct >= 50) return 'pg-50';
    if (pct >= 25) return 'pg-25';
    return 'pg-0';
}

// ===== BUBBLES =====
(function(){
    var c = $('particles');
    for (var i = 0; i < 50; i++) {
        var s = document.createElement('span');
        s.style.left = (Math.random() * 100) + '%';
        s.style.top = (5 + Math.random() * 85) + '%';
        s.style.animationDelay = (Math.random() * 14) + 's';
        s.style.animationDuration = (10 + Math.random() * 12) + 's';
        var size = 4 + Math.random() * 14;
        s.style.width = size + 'px';
        s.style.height = size + 'px';
        c.appendChild(s);
    }
})();

// ===== ROLE CLICKS =====
document.querySelectorAll('.role-card').forEach(function(c) {
    c.addEventListener('click', function() { selectRole(c.dataset.role); });
});

// ===== SELECT ROLE =====
function selectRole(rid) {
    var role = roles[rid];
    if (!role) return;
    selectedRole = role;
    var roleLevels = role.levels.map(function(id) { return allLevels.find(function(l) { return l.id === id; }); }).filter(Boolean);

    // Summary bar
    $('s-role-icon').textContent = role.icon;
    $('s-role-name').textContent = role.name;

    var totalMat = roleLevels.reduce(function(s,l) { return s + l.materi; }, 0);
    var totalXp = roleLevels.reduce(function(s,l) { return s + l.reward; }, 0);
    var overall = roleLevels.length ? Math.round(roleLevels.reduce(function(s,l) { return s + (l.progress||0); }, 0) / roleLevels.length) : 0;

    $('s-stats').innerHTML =
        '<div class="summary-stat"><strong>' + roleLevels.length + '</strong><span>Level</span></div>' +
        '<div class="summary-stat"><strong>' + totalMat + '</strong><span>Materi</span></div>' +
        '<div class="summary-stat"><strong>+' + totalXp + '</strong><span>XP</span></div>';

    $('s-prog-fill').style.width = overall + '%';
    $('s-prog-pct').textContent = overall + '%';

    buildMap(roleLevels);

    stepRoles.style.display = 'none';
    stepJourney.style.display = 'flex';
    $('world-map').scrollTop = 0;

    panelPlaceholder.style.display = 'flex';
    panelBody.style.display = 'none';

    if (roleLevels[0]) selectLevel(roleLevels[0]);
}

// ===== BUILD MAP =====
var nodeEls = [];

function buildMap(levels) {
    mapGrid.innerHTML = '';
    nodeEls = [];
    var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('class', 'map-svg');
    svg.setAttribute('id', 'map-svg');
    mapGrid.appendChild(svg);

    levels.forEach(function(lvl, i) {
        var color = lvl.color || '#6366F1';
        var status = lvl.status || 'locked';
        var progress = lvl.progress ?? 0;

        var isDone = status === 'completed';
        var isCurrent = status === 'in-progress';
        var isLocked = status === 'locked';
        var side = i % 2 === 0 ? 'left' : 'right';

        var markerCls = isDone ? 'done' : isCurrent ? 'current' : 'locked';
        var markerIcon = isDone ? '✓' : (isCurrent ? String(lvl.id) : '🔒');
        var bodyCls = isDone ? 'done' : isCurrent ? 'current' : 'locked';
        var badgeText = isDone ? '✓ Selesai' : (isCurrent ? '● Aktif' : '🔒 Terkunci');
        var badgeCls = isDone ? 'done' : isCurrent ? 'current' : 'locked';

        var node = document.createElement('div');
        node.className = 'node-card node-card--' + side;
        node.style.animationDelay = (i * 0.08) + 's';
        node.dataset.id = lvl.id;
        node.dataset.status = status;
        node.dataset.side = side;

        node.innerHTML =
            '<div class="node-marker node-marker--' + markerCls + '">' + markerIcon + '</div>' +
            '<div class="node-body node-body--' + bodyCls + '" style="--lc:' + color + '">' +
                '<div class="node-badge node-badge--' + badgeCls + '">' + badgeText + '</div>' +
                '<div class="node-level">Level ' + lvl.id + '</div>' +
                '<div class="node-title">' + lvl.name + '</div>' +
                '<div class="node-reward"><span>⭐</span> ' + lvl.reward + ' XP</div>' +
                '<div class="node-progress"><div class="node-progress-fill ' + getPgClass(progress) + '" style="width:' + progress + '%"></div></div>' +
            '</div>';

        node.addEventListener('click', function() { selectLevel(lvl); });
        mapGrid.appendChild(node);
        nodeEls.push({ el: node, lvl: lvl, status: status, isDone: isDone, isCurrent: isCurrent, side: side });
    });

    requestAnimationFrame(function() { drawPath(svg, levels); });
}

// ===== DRAW SVG PATH (light theme, pixel-perfect alignment) =====
function drawPath(svg, levels) {
    var container = svg.parentElement;
    var nodes = container.querySelectorAll('.node-marker');
    if (nodes.length < 2) return;

    var contRect = container.getBoundingClientRect();
    var fullW = Math.max(container.scrollWidth, contRect.width);
    var fullH = Math.max(container.scrollHeight, contRect.height);
    svg.style.width = fullW + 'px';
    svg.style.height = fullH + 'px';
    svg.setAttribute('viewBox', '0 0 ' + fullW + ' ' + fullH);
    svg.removeAttribute('preserveAspectRatio');

    var pts = [];
    nodes.forEach(function(n) {
        var r = n.getBoundingClientRect();
        pts.push({
            x: r.left - contRect.left + r.width / 2,
            y: r.top - contRect.top + r.height / 2
        });
    });

    svg.innerHTML = '';

    function buildSegmentD(i) {
        var prev = pts[i - 1], p = pts[i];
        var dx = p.x - prev.x;
        var c1x = prev.x + dx * 0.4;
        var c2x = prev.x + dx * 0.6;
        return 'M ' + prev.x + ' ' + prev.y +
            ' C ' + c1x + ' ' + prev.y + ', ' + c2x + ' ' + p.y + ', ' + p.x + ' ' + p.y;
    }

    // Build segment data with status
    var segments = [];
    for (var i = 1; i < pts.length; i++) {
        segments.push({
            d: buildSegmentD(i),
            endIdx: i
        });
    }

    // 1. Background path — all segments in light gray
    var allD = segments.map(function(s) { return s.d; }).join(' ');
    var bgPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    bgPath.setAttribute('d', allD);
    bgPath.setAttribute('stroke', '#E2E8F0');
    bgPath.setAttribute('stroke-width', '2.5');
    bgPath.setAttribute('fill', 'none');
    bgPath.setAttribute('stroke-linecap', 'round');
    bgPath.setAttribute('stroke-linejoin', 'round');
    svg.appendChild(bgPath);

    // Find current node index
    var currIdx = -1;
    for (var j = 0; j < nodeEls.length; j++) {
        if (nodeEls[j].isCurrent) { currIdx = j; break; }
    }

    // 2. Completed segments — green, from start through consecutive done nodes
    var doneParts = [];
    for (var k = 0; k < segments.length; k++) {
        var endNode = nodeEls[segments[k].endIdx];
        if (endNode && endNode.isDone) {
            doneParts.push(segments[k].d);
        } else {
            break;
        }
    }
    if (doneParts.length) {
        var donePath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        donePath.setAttribute('d', doneParts.join(' '));
        donePath.setAttribute('stroke', '#22C55E');
        donePath.setAttribute('stroke-width', '3');
        donePath.setAttribute('fill', 'none');
        donePath.setAttribute('stroke-linecap', 'round');
        donePath.setAttribute('stroke-linejoin', 'round');
        svg.appendChild(donePath);
    }

    // 3. Current segment — purple dashed, segment leading TO the current node
    if (currIdx > 0 && currIdx < pts.length) {
        var seg = segments[currIdx - 1];
        if (seg) {
            var currPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            currPath.setAttribute('d', seg.d);
            currPath.setAttribute('stroke', '#6366F1');
            currPath.setAttribute('stroke-width', '2.5');
            currPath.setAttribute('fill', 'none');
            currPath.setAttribute('stroke-linecap', 'round');
            currPath.setAttribute('stroke-linejoin', 'round');
            currPath.setAttribute('stroke-dasharray', '8 6');
            currPath.setAttribute('opacity', '0.65');
            svg.appendChild(currPath);
        }
    }

    // 4. First node marker dot (to show start point when only green from node 0)
    if (pts.length > 0) {
        var dot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        var p0 = pts[0];
        var isFirstDone = nodeEls[0] && nodeEls[0].isDone;
        dot.setAttribute('cx', p0.x);
        dot.setAttribute('cy', p0.y);
        dot.setAttribute('r', isFirstDone ? 4 : 3);
        dot.setAttribute('fill', isFirstDone ? '#22C55E' : '#CBD5E1');
        svg.appendChild(dot);
    }
}

// ===== SELECT LEVEL =====
function selectLevel(lvl) {
    if (!lvl) return;
    currentLevel = lvl;
    var color = lvl.color || '#6366F1';
    var status = lvl.status || 'locked';
    var isDone = status === 'completed';
    var isCurrent = status === 'in-progress';
    var isLocked = status === 'locked';

    panelPlaceholder.style.display = 'none';
    panelBody.style.display = 'block';

    $('d-icon').textContent = lvl.emoji || lvl.icon || '📘';
    $('d-icon').style.background = 'linear-gradient(135deg,' + color + ',' + color + '88)';
    $('d-level').textContent = isDone ? '✓ Level ' + lvl.id + ' — SELESAI' : (isLocked ? '🔒 Level ' + lvl.id + ' — Terkunci' : '● Level ' + lvl.id);
    $('d-level').className = 'detail-head-level detail-head-level--' + (isDone ? 'done' : isCurrent ? 'current' : 'locked');
    $('d-title').textContent = lvl.name;

    var progress = lvl.progress ?? 0;
    var circ = 2 * Math.PI * 24;
    var offset = circ - (progress / 100) * circ;
    var ring = $('d-ring');
    ring.style.stroke = '';
    ring.style.transition = 'stroke-dashoffset .6s ease';
    ring.style.strokeDashoffset = offset;
    ring.setAttribute('class', 'bg');
    var pgClass = 'pg-0';
    if (progress >= 100) pgClass = 'pg-100';
    else if (progress >= 75) pgClass = 'pg-75';
    else if (progress >= 50) pgClass = 'pg-50';
    else if (progress >= 25) pgClass = 'pg-25';
    ring.classList.add(pgClass);
    $('d-ring-text').textContent = progress + '%';
    $('d-ring-text').setAttribute('fill', isLocked ? '#CBD5E1' : (progress >= 100 ? '#22C55E' : '#0F172A'));
    if (progress >= 100) $('d-ring-text').classList.add('pg-glow');
    else $('d-ring-text').classList.remove('pg-glow');

    $('d-xp').textContent = lvl.reward + ' XP';
    $('d-dur').textContent = lvl.durasi || '-';
    var completedQ = lvl.completed_quests || 0;
    var totalQ = lvl.materi || 0;
    $('d-mat').textContent = completedQ + '/' + totalQ;
    $('d-boss').textContent = lvl.boss || '-';

    var btn = $('d-btn');
    btn.className = 'detail-btn';
    if (isDone || progress >= 100) {
        btn.classList.add('detail-btn--done');
        btn.disabled = false;
        btn.textContent = '✓ SELESAI';
    } else if (isLocked) {
        btn.classList.add('detail-btn--locked');
        btn.disabled = true;
        btn.textContent = '🔒 Terkunci';
    } else {
        btn.classList.add('detail-btn--active');
        btn.disabled = false;
        btn.textContent = (progress > 0) ? '🚀 Lanjutkan Belajar' : '📖 Mulai Belajar';
    }

    $('d-skills').innerHTML = '';
    (lvl.skills||[]).forEach(function(s) {
        var el = document.createElement('span');
        el.className = 'detail-skill';
        el.textContent = s;
        $('d-skills').appendChild(el);
    });

    $('d-quests').innerHTML = '';
    var qd = questDetail[lvl.id] || {};
    (lvl.quests||[]).forEach(function(q, qi) {
        var qStatus = qd[qi] || 'not_started';
        var isQDone = qStatus === 'completed';
        var isQActive = qStatus === 'in_progress';
        var checkCls = isQDone ? 'done' : (isQActive ? 'active' : 'locked');
        var checkIcon = isQDone ? '✓' : (isQActive ? '📖' : '🔒');
        var el = document.createElement('div');
        el.className = 'detail-quest';
        el.style.cursor = isQDone ? 'default' : 'pointer';
        el.innerHTML = '<span class="detail-quest-check detail-quest-check--' + checkCls + '">' + checkIcon + '</span> ' + q.label;
        if (!isQDone) {
            el.addEventListener('click', function(e) {
                e.stopPropagation();
                window.location.href = 'course-viewer.php?level_id=' + lvl.id + '&quest=' + qi;
            });
        }
        $('d-quests').appendChild(el);
    });

    // Highlight node
    mapGrid.querySelectorAll('.node-card').forEach(function(el) {
        var body = el.querySelector('.node-body');
        if (body) body.classList.toggle('node-body--selected', Number(el.dataset.id) === Number(lvl.id));
    });
}

$('d-btn').addEventListener('click', function() {
    if (!currentLevel) return;
    var isComplete = (currentLevel.status === 'completed' || (currentLevel.progress || 0) >= 100);
    if (isComplete) {
        return Toast.success('Selesai!', 'Level ini sudah 100% selesai 🎉');
    }
    // Redirect to course-viewer (Progate-like flow)
    if (currentLevel.quests && currentLevel.quests.length) {
        var firstQuest = 0;
        window.location.href = 'course-viewer.php?level_id=' + currentLevel.id + '&quest=' + firstQuest;
    }
});

$('btn-back').addEventListener('click', function() {
    stepRoles.style.display = 'flex';
    stepJourney.style.display = 'none';
});

// ===== MATERIAL VIEWER =====
var modal = $('modal-materi');
var mSlideIdx = 0;
var mSlides = [];

function openMateri(lvl, startIdx) {
    startIdx = startIdx || 0;
    if (!lvl || !lvl.quests || !lvl.quests.length) {
        if (lvl && lvl.course_id) window.location.href = '../quest.php?course_id=' + lvl.course_id;
        return;
    }
    mSlides = lvl.quests;
    mSlideIdx = Math.min(startIdx, mSlides.length - 1);
    $('m-badge').textContent = 'Level ' + lvl.id + ' — ' + lvl.name;
    $('m-badge').style.background = 'var(--primary-light)';
    $('m-badge').style.color = 'var(--primary)';
    $('m-level-title').textContent = lvl.emoji + ' ' + lvl.name;
    modal.style.display = 'flex';
    renderSlide();
}

function renderSlide() {
    var total = mSlides.length;
    var s = mSlides[mSlideIdx];
    $('m-num').textContent = 'Materi ' + (mSlideIdx + 1) + ' dari ' + total;
    $('m-slide-title').textContent = s.label;
    $('m-slide-skill').textContent = '⚡ ' + (s.skill || '');
    $('m-track').style.width = ((mSlideIdx + 1) / total * 100) + '%';

    $('m-prev').style.display = mSlideIdx === 0 ? 'none' : '';
    var isLast = mSlideIdx === total - 1;
    $('m-next').style.display = isLast ? 'none' : '';
    $('m-go').style.display = isLast ? '' : 'none';
}

$('m-next').addEventListener('click', function() {
    if (mSlideIdx < mSlides.length - 1) { mSlideIdx++; renderSlide(); }
});
$('m-prev').addEventListener('click', function() {
    if (mSlideIdx > 0) { mSlideIdx--; renderSlide(); }
});
$('m-close').addEventListener('click', function() { modal.style.display = 'none'; });
modal.addEventListener('click', function(e) { if (e.target === modal) modal.style.display = 'none'; });
$('m-go').addEventListener('click', function() {
    modal.style.display = 'none';
    if (currentLevel && mSlides.length) {
        var q = mSlides[mSlideIdx];
        var url = 'playground.php?course_id=' + (currentLevel.course_id || '') +
            '&level=' + encodeURIComponent(currentLevel.name) +
            '&quest=' + encodeURIComponent(q.label) +
            '&skill=' + encodeURIComponent(q.skill || '') +
            '&xp=' + (currentLevel.reward || 0) +
            '&level_id=' + (currentLevel.id || 0) +
            '&quest_idx=' + mSlideIdx;
        window.location.href = url;
    }
});

})();
</script>
</body>
</html>
