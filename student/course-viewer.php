<?php
require_once '../config/config.php';
requireLogin();
require_once '../includes/icons.php';

$page_title       = 'Course Viewer';
$page_description = 'Belajar langkah demi langkah';
$page_css         = ['sidebar-island.css', 'dashboard-override.css'];
$body_class       = trim(getThemeClass() . ' dashboard-layout');

$level_id = (int)($_GET['level_id'] ?? 0);
$quest_idx = (int)($_GET['quest'] ?? 0);

$levels = [
    ['id' => 1, 'name' => 'HTML Forest',          'emoji' => '🌳', 'reward' => 120, 'color' => '#F97316', 'course_id' => 1],
    ['id' => 2, 'name' => 'CSS Desert',           'emoji' => '🏜️', 'reward' => 150, 'color' => '#2563EB', 'course_id' => 1],
    ['id' => 3, 'name' => 'JavaScript City',      'emoji' => '🌆', 'reward' => 200, 'color' => '#F59E0B', 'course_id' => 4],
    ['id' => 4, 'name' => 'React Kingdom',        'emoji' => '👑', 'reward' => 225, 'color' => '#22C55E', 'course_id' => 9],
    ['id' => 5, 'name' => 'API Ocean',            'emoji' => '🌊', 'reward' => 230, 'color' => '#06B6D4', 'course_id' => 10],
    ['id' => 6, 'name' => 'NextJS Citadel',       'emoji' => '🏰', 'reward' => 250, 'color' => '#8B5CF6', 'course_id' => 3],
    ['id' => 7, 'name' => 'Performance Mountain', 'emoji' => '⛰️', 'reward' => 270, 'color' => '#EC4899', 'course_id' => 3],
    ['id' => 8, 'name' => 'Frontend Master Castle','emoji' => '🏆', 'reward' => 350, 'color' => '#6366F1', 'course_id' => 3],
];

$all_quests = [
    1 => [
        ['label' => 'Pengenalan HTML',       'skill' => 'Struktur'],
        ['label' => 'Struktur Dasar HTML',   'skill' => 'Struktur'],
        ['label' => 'Heading & Paragraph',   'skill' => 'Teks'],
        ['label' => 'Link & Image',          'skill' => 'Media'],
        ['label' => 'List & Table',          'skill' => 'Data'],
        ['label' => 'Form & Input',          'skill' => 'Interaksi'],
        ['label' => 'Semantic HTML',         'skill' => 'Semantik'],
        ['label' => 'Multimedia & Embed',    'skill' => 'Media'],
    ],
    2 => [
        ['label' => 'CSS Dasar & Selector',  'skill' => 'Dasar'],
        ['label' => 'Color & Typography',    'skill' => 'Desain'],
        ['label' => 'Box Model & Layout',    'skill' => 'Layout'],
        ['label' => 'Flexbox',               'skill' => 'Layout'],
        ['label' => 'Grid Layout',           'skill' => 'Layout'],
        ['label' => 'Responsive Design',     'skill' => 'Responsif'],
        ['label' => 'CSS Animation',         'skill' => 'Animasi'],
    ],
    3 => [
        ['label' => 'Variable & Tipe Data',  'skill' => 'Dasar'],
        ['label' => 'Operator & Ekspresi',   'skill' => 'Logika'],
        ['label' => 'Conditional & Loop',    'skill' => 'Logika'],
        ['label' => 'Function',              'skill' => 'Fungsi'],
        ['label' => 'Array & Object',        'skill' => 'Data'],
        ['label' => 'DOM Manipulation',      'skill' => 'DOM'],
        ['label' => 'Event Handling',        'skill' => 'Interaksi'],
        ['label' => 'Async & Fetch API',     'skill' => 'Async'],
    ],
    4 => [
        ['label' => 'JSX & Komponen',        'skill' => 'Dasar'],
        ['label' => 'Props & State',         'skill' => 'State'],
        ['label' => 'Event Handler',         'skill' => 'Event'],
        ['label' => 'Conditional Rendering', 'skill' => 'Logic'],
        ['label' => 'List & Keys',           'skill' => 'Data'],
        ['label' => 'Hooks (useState)',      'skill' => 'Hooks'],
        ['label' => 'useEffect & Lifecycle', 'skill' => 'Hooks'],
    ],
    5 => [
        ['label' => 'HTTP & RESTful API',    'skill' => 'Dasar'],
        ['label' => 'Endpoint & Routing',    'skill' => 'Route'],
        ['label' => 'Request & Response',    'skill' => 'Komunikasi'],
        ['label' => 'Authentication',        'skill' => 'Keamanan'],
        ['label' => 'CRUD Operations',       'skill' => 'Data'],
        ['label' => 'JSON & Data Handling',  'skill' => 'Data'],
    ],
];

$level_data = null;
foreach ($levels as $l) { if ($l['id'] === $level_id) { $level_data = $l; break; } }
$quests = $all_quests[$level_id] ?? [];
$quest = $quests[$quest_idx] ?? null;

if (!$level_data || !$quest) {
    header('Location: learning-path.php');
    exit;
}

$is_last_quest = $quest_idx >= count($quests) - 1;
$next_quest_idx = $is_last_quest ? -1 : $quest_idx + 1;

$ppt_path = sprintf('../assets/materials/%s/%s.pptx',
    strtolower(str_replace(' ', '-', $level_data['name'])),
    strtolower(str_replace(' ', '-', preg_replace('/&/', 'and', $quest['label']))));
$ppt_exists = file_exists($ppt_path);

// ===== SLIDE CONTENT =====
function getSlideContent($level_name, $quest_label, $skill, $slide_num, $total_slides) {
    $content_map = [
        'Pengenalan HTML' => [
            ['title' => 'Apa itu HTML?',
             'body' => '<p>HTML (HyperText Markup Language) adalah bahasa markup standar untuk membuat halaman web.</p>
<p>HTML bukan bahasa pemrograman — ia adalah bahasa <strong>markup</strong> yang memberi struktur pada konten web.</p>
<p>Setiap halaman web yang kamu buka di browser menggunakan HTML sebagai fondasinya.</p>
<div class="slide-fact">💡 HTML pertama kali dirilis pada 1993 dan sekarang sudah mencapai versi HTML5.</div>'],
            ['title' => 'Cara Kerja HTML',
             'body' => '<p>Browser mengunduh file HTML dari server, lalu <strong>menerjemahkan</strong> tag-tag HTML menjadi tampilan visual.</p>
<div class="slide-code-block">
<span class="slide-cmt">&lt;!-- Ini adalah elemen HTML sederhana --&gt;</span>
<span class="slide-tag">&lt;h1&gt;</span>Halo Dunia!<span class="slide-tag">&lt;/h1&gt;</span>
<span class="slide-tag">&lt;p&gt;</span>Ini adalah paragraf.<span class="slide-tag">&lt;/p&gt;</span>
</div>
<p>Hasilnya: Judul "Halo Dunia!" dicetak tebal dan besar, paragraf muncul di bawahnya.</p>'],
            ['title' => 'Struktur Tag HTML',
             'body' => '<p>HTML terdiri dari <strong>tag-tag</strong> yang membungkus konten.</p>
<div class="slide-code-block">
<span class="slide-tag">&lt;tagname&gt;</span>konten<span class="slide-tag">&lt;/tagname&gt;</span>
</div>
<ul>
<li>Tag pembuka: <code>&lt;tagname&gt;</code></li>
<li>Tag penutup: <code>&lt;/tagname&gt;</code></li>
<li>Konten di antara keduanya</li>
<li>Beberapa tag tidak perlu penutup (self-closing): <code>&lt;br&gt;</code>, <code>&lt;img&gt;</code></li>
</ul>'],
            ['title' => 'Contoh HTML Sederhana',
             'body' => '<p>Ini adalah contoh halaman HTML minimal:</p>
<div class="slide-code-block">
<span class="slide-tag">&lt;!DOCTYPE html&gt;</span>
<span class="slide-tag">&lt;html&gt;</span>
<span class="slide-tag">&lt;head&gt;</span>
  <span class="slide-tag">&lt;title&gt;</span>Judul Halaman<span class="slide-tag">&lt;/title&gt;</span>
<span class="slide-tag">&lt;/head&gt;</span>
<span class="slide-tag">&lt;body&gt;</span>
  <span class="slide-tag">&lt;h1&gt;</span>Selamat Datang!<span class="slide-tag">&lt;/h1&gt;</span>
  <span class="slide-tag">&lt;p&gt;</span>Ini halaman pertamaku.<span class="slide-tag">&lt;/p&gt;</span>
<span class="slide-tag">&lt;/body&gt;</span>
<span class="slide-tag">&lt;/html&gt;</span>
</div>'],
            ['title' => 'Siap Praktik?',
             'body' => '<p>Sekarang kamu sudah paham dasar HTML.</p>
<p>Yang perlu diingat:</p>
<ul>
<li>HTML = struktur halaman web</li>
<li>Tag membungkus konten</li>
<li>Browser menerjemahkan HTML jadi tampilan</li>
</ul>
<p>👍 Saatnya praktik coding!</p>'],
        ],
        'Struktur Dasar HTML' => [
            ['title' => 'Struktur Dokumen HTML',
             'body' => '<p>Setiap dokumen HTML memiliki struktur dasar yang wajib diikuti.</p>
<div class="slide-code-block">
<span class="slide-tag">&lt;!DOCTYPE html&gt;</span>
<span class="slide-tag">&lt;html lang="id"&gt;</span>
  <span class="slide-tag">&lt;head&gt;</span>
    <span class="slide-cmt">&lt;!-- Meta info --&gt;</span>
  <span class="slide-tag">&lt;/head&gt;</span>
  <span class="slide-tag">&lt;body&gt;</span>
    <span class="slide-cmt">&lt;!-- Konten --&gt;</span>
  <span class="slide-tag">&lt;/body&gt;</span>
<span class="slide-tag">&lt;/html&gt;</span>
</div>'],
            ['title' => 'Elemen &lt;head&gt;',
             'body' => '<p>Bagian <code>&lt;head&gt;</code> berisi informasi tentang halaman (metadata):</p>
<ul>
<li><code>&lt;title&gt;</code> — judul yang muncul di tab browser</li>
<li><code>&lt;meta&gt;</code> — encoding, deskripsi, viewport</li>
<li><code>&lt;link&gt;</code> — memuat file CSS eksternal</li>
<li><code>&lt;style&gt;</code> — CSS internal</li>
</ul>
<p>Konten di dalam head <strong>tidak tampil</strong> di halaman.</p>'],
            ['title' => 'Elemen &lt;body&gt;',
             'body' => '<p>Bagian <code>&lt;body&gt;</code> berisi semua konten yang tampil di browser.</p>
<p>Semua elemen seperti teks, gambar, video, form, dan lainnya ditempatkan di sini.</p>
<div class="slide-code-block">
<span class="slide-tag">&lt;body&gt;</span>
  <span class="slide-tag">&lt;h1&gt;</span>Judul<span class="slide-tag">&lt;/h1&gt;</span>
  <span class="slide-tag">&lt;p&gt;</span>Teks paragraf.<span class="slide-tag">&lt;/p&gt;</span>
  <span class="slide-tag">&lt;img src="foto.jpg" alt="foto"&gt;</span>
<span class="slide-tag">&lt;/body&gt;</span>
</div>'],
        ],
        'Heading & Paragraph' => [
            ['title' => 'Heading di HTML',
             'body' => '<p>Heading digunakan untuk membuat judul dan sub-judul. Ada 6 level:</p>
<div class="slide-code-block">
<span class="slide-tag">&lt;h1&gt;</span>Heading 1 — Paling penting<span class="slide-tag">&lt;/h1&gt;</span>
<span class="slide-tag">&lt;h2&gt;</span>Heading 2<span class="slide-tag">&lt;/h2&gt;</span>
<span class="slide-tag">&lt;h3&gt;</span>Heading 3<span class="slide-tag">&lt;/h3&gt;</span>
<span class="slide-tag">&lt;h4&gt;</span>Heading 4<span class="slide-tag">&lt;/h4&gt;</span>
<span class="slide-tag">&lt;h5&gt;</span>Heading 5<span class="slide-tag">&lt;/h5&gt;</span>
<span class="slide-tag">&lt;h6&gt;</span>Heading 6 — Paling kecil<span class="slide-tag">&lt;/h6&gt;</span>
</div>'],
            ['title' => 'Paragraf & Format Teks',
             'body' => '<p>Gunakan <code>&lt;p&gt;</code> untuk paragraf.</p>
<div class="slide-code-block">
<span class="slide-tag">&lt;p&gt;</span>Ini adalah paragraf teks biasa.<span class="slide-tag">&lt;/p&gt;</span>
</div>
<p>Format teks lainnya:</p>
<ul>
<li><code>&lt;b&gt;</code> atau <code>&lt;strong&gt;</code> — <strong>tebal</strong></li>
<li><code>&lt;i&gt;</code> atau <code>&lt;em&gt;</code> — <em>miring</em></li>
<li><code>&lt;u&gt;</code> — <u>garis bawah</u></li>
<li><code>&lt;br&gt;</code> — baris baru (enter)</li>
</ul>'],
        ],
        'Link & Image' => [
            ['title' => 'Membuat Link',
             'body' => '<p>Tag <code>&lt;a&gt;</code> (anchor) membuat hyperlink.</p>
<div class="slide-code-block">
<span class="slide-tag">&lt;a href="https://google.com"&gt;</span>Kunjungi Google<span class="slide-tag">&lt;/a&gt;</span>
</div>
<p>Atribut <code>href</code> menentukan tujuan link.</p>
<p>Tips: gunakan <code>target="_blank"</code> untuk membuka tab baru.</p>'],
            ['title' => 'Menampilkan Gambar',
             'body' => '<p>Tag <code>&lt;img&gt;</code> menampilkan gambar (self-closing).</p>
<div class="slide-code-block">
<span class="slide-tag">&lt;img src="gambar.jpg" alt="Deskripsi gambar" width="300"&gt;</span>
</div>
<ul>
<li><code>src</code> — path/URL gambar</li>
<li><code>alt</code> — teks alternatif (penting untuk aksesibilitas)</li>
<li><code>width</code> / <code>height</code> — ukuran (opsional)</li>
</ul>'],
        ],
    ];

    // Find specific content or generate generic
    if (isset($content_map[$quest_label])) {
        $slides = $content_map[$quest_label];
        if ($slide_num < count($slides)) {
            return $slides[$slide_num];
        }
    }

    // Generic fallback based on skill
    $generic = [
        ['title' => "Pengenalan $quest_label",
         'body' => "<p>Selamat datang di materi <strong>$quest_label</strong>!</p>
<p>Di sesi ini, kamu akan mempelajari konsep dan praktik <strong>$skill</strong> dalam $level_name.</p>
<p>Ikuti langkah demi langkah dan jangan ragu untuk mencoba sendiri.</p>"],
        ['title' => "Konsep Dasar $quest_label",
         'body' => "<p>$quest_label adalah bagian penting dari $level_name.</p>
<p>Dalam pengembangan web modern, pemahaman yang baik tentang <strong>$skill</strong> akan membantu kamu membangun aplikasi yang lebih baik.</p>
<div class=\"slide-fact\">💡 Fokus pada pemahaman konsep, bukan hanya menghafal sintaks.</div>"],
        ['title' => "Contoh Penerapan $skill",
         'body' => '<p>Berikut adalah contoh penerapan dalam kode:</p>
<div class="slide-code-block">
<span class="slide-cmt">// Contoh kode untuk ' . $quest_label . '</span>
<span class="slide-cmt">/* Tulis kode kamu di editor coding */</span>
</div>
<p>Coba pahami pola di atas, lalu praktikkan sendiri.</p>'],
        ['title' => "Tips & Best Practice",
         'body' => '<ul>
<li>Mulailah dari yang sederhana, lalu tingkatkan kompleksitas</li>
<li>Gunakan komentar untuk menjelaskan kode kamu</li>
<li>Biasakan menulis kode yang rapi dan konsisten</li>
<li>Jangan takut bereksperimen!</li>
</ul>'],
        ['title' => 'Siap Praktik?',
         'body' => '<p>Kamu sudah siap untuk mencoba sendiri!</p>
<p>Klik <strong>"Mulai Praktik Coding"</strong> untuk mengerjakan challenge.</p>
<p>💡 Ingat: praktik adalah kunci utama belajar coding.</p>'],
    ];

    if ($slide_num < count($generic)) {
        return $generic[$slide_num];
    }

    return ['title' => $quest_label, 'body' => "<p>Materi $quest_label — lanjutkan ke praktik coding.</p>"];
}

$level_name = $level_data['name'];
$quest_label = $quest['label'];
$skill = $quest['skill'];
$total_slides = 5;
$slide_content = getSlideContent($level_name, $quest_label, $skill, 0, $total_slides);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once '../includes/head.php'; ?>
<style>
:root {
    --primary: #6366F1; --suc: #22C55E; --bg: #F8FAFC;
    --card: #FFFFFF; --card-border: #E2E8F0; --border-light: #F1F5F9;
    --text-p: #0F172A; --text-s: #475569; --text-m: #94A3B8;
    --radius: 12px; --shadow: 0 1px 3px rgba(0,0,0,0.06);
}
* { box-sizing: border-box; }
body.dashboard-layout .page-wrapper.dashboard-main-container { background: var(--bg) !important; }
body.dashboard-layout .dashboard-content { max-width: 1100px; }

.cv-breadcrumb {
    display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
    font-size: .72rem; color: var(--text-m); margin-bottom: 12px;
}
.cv-breadcrumb a { color: var(--primary); text-decoration: none; }
.cv-breadcrumb a:hover { text-decoration: underline; }
.cv-breadcrumb .sep { color: var(--text-m); font-size: .6rem; }

.cv-header {
    background: var(--card); border: 1px solid var(--card-border);
    border-radius: var(--radius); padding: 20px 24px; margin-bottom: 16px;
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
}
.cv-header-left { flex: 1; min-width: 0; }
.cv-header-left .cv-badge {
    display: inline-block; font-size: .55rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .04em; padding: 2px 10px; border-radius: 999px;
    background: rgba(99,102,241,0.08); color: var(--primary); margin-bottom: 4px;
}
.cv-header-left h1 { font-size: 1.15rem; font-weight: 800; color: var(--text-p); margin: 0 0 2px; }
.cv-header-left p { font-size: .72rem; color: var(--text-s); margin: 0; }
.cv-header-right { text-align: right; flex-shrink: 0; }

/* SLIDE VIEWER */
.cv-slide-area {
    background: var(--card); border: 1px solid var(--card-border);
    border-radius: var(--radius); overflow: hidden;
    display: flex; flex-direction: column; min-height: 380px;
}
.cv-slide-progress {
    height: 4px; background: var(--border-light);
}
.cv-slide-progress-fill {
    height: 100%; background: linear-gradient(90deg, var(--primary), #8B5CF6);
    transition: width .5s ease; width: 20%;
}
.cv-slide-body {
    flex: 1; padding: 28px 32px; overflow-y: auto;
}
.cv-slide-body .slide-number {
    font-size: .6rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: var(--text-m); margin-bottom: 14px;
}
.cv-slide-body h2 {
    font-size: 1.1rem; font-weight: 800; color: var(--text-p);
    margin: 0 0 14px; line-height: 1.35;
}
.cv-slide-body p { font-size: .82rem; color: var(--text-s); line-height: 1.7; margin: 0 0 10px; }
.cv-slide-body ul { padding-left: 18px; margin: 8px 0; }
.cv-slide-body ul li { font-size: .8rem; color: var(--text-s); line-height: 1.7; margin-bottom: 3px; }
.cv-slide-body code {
    background: rgba(99,102,241,0.06); color: var(--primary); padding: 1px 5px;
    border-radius: 4px; font-size: .78rem; font-family: 'Cascadia Code','Fira Code',monospace;
}
.slide-code-block {
    background: #F1F5F9; border-radius: 8px; padding: 14px 16px; margin: 12px 0;
    font-family: 'Cascadia Code','Fira Code','Consolas',monospace;
    font-size: .78rem; line-height: 1.7; color: #0F172A; white-space: pre-wrap; overflow-x: auto;
}
.slide-code-block .slide-tag { color: #6366F1; }
.slide-code-block .slide-cmt { color: #94A3B8; font-style: italic; }
.slide-fact {
    background: rgba(99,102,241,0.06); border-left: 3px solid var(--primary);
    padding: 10px 14px; border-radius: 0 8px 8px 0; margin: 12px 0;
    font-size: .78rem; color: var(--text-s); line-height: 1.5;
}

/* NAVIGATION */
.cv-nav {
    display: flex; align-items: center; justify-content: space-between;
    gap: 8px; padding: 14px 24px; border-top: 1px solid var(--border-light);
    background: var(--bg); flex-wrap: wrap;
}
.cv-nav-left { font-size: .68rem; color: var(--text-m); font-weight: 600; }
.cv-nav-right { display: flex; gap: 8px; }
.cv-btn {
    padding: 8px 20px; border-radius: 10px; border: 1px solid var(--card-border);
    font-size: .75rem; font-weight: 700; cursor: pointer; transition: all .2s;
    font-family: inherit; background: var(--card); color: var(--text-s);
}
.cv-btn:hover { border-color: var(--primary); color: var(--primary); }
.cv-btn--primary {
    background: var(--primary); color: #fff; border-color: var(--primary);
}
.cv-btn--primary:hover { background: #4f46e5; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99,102,241,0.2); }
.cv-btn--suc {
    background: linear-gradient(135deg, var(--suc), #059669); color: #fff; border: none;
}
.cv-btn--suc:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(34,197,94,0.25); }
.cv-btn:disabled { opacity: .3; cursor: not-allowed; }

.cv-next-info {
    text-align: center; margin-top: 16px; padding: 16px;
    background: var(--card); border: 1px solid var(--card-border);
    border-radius: var(--radius); display: none;
}
.cv-next-info .cv-btn { margin-top: 8px; }

/* RESPONSIVE MOBILE */
@media (max-width: 700px) {
    .cv-header { flex-direction: column; text-align: center; }
    .cv-header-right { text-align: center; }
    .cv-slide-body { padding: 20px 16px; }
    .cv-nav { flex-direction: column; }
}
</style>
</head>
<body class="<?php echo $body_class; ?>">
<?php require_once 'navbar.php'; ?>
<div class="page-wrapper dashboard-main-container">
<div class="dashboard-content">

<!-- BREADCRUMB -->
<div class="cv-breadcrumb">
    <a href="learning-path.php">🗺️ Learning Path</a>
    <span class="sep">›</span>
    <a href="learning-path.php#level-<?php echo $level_id; ?>"><?php echo htmlspecialchars($level_data['emoji'] . ' ' . $level_data['name']); ?></a>
    <span class="sep">›</span>
    <span><?php echo htmlspecialchars($quest_label); ?></span>
</div>

<!-- HEADER -->
<div class="cv-header">
    <div class="cv-header-left">
        <div class="cv-badge">⚡ <?php echo htmlspecialchars($skill); ?></div>
        <h1><?php echo htmlspecialchars($quest_label); ?></h1>
        <p><?php echo htmlspecialchars($level_data['name']); ?> — Pelajari langkah demi langkah</p>
    </div>
    <div class="cv-header-right">
        <div style="font-size:.6rem;color:var(--text-m);font-weight:600">XP REWARD</div>
        <div style="font-size:1rem;font-weight:900;color:var(--suc)">+<?php echo $level_data['reward']; ?></div>
    </div>
</div>

<?php if ($ppt_exists): ?>
<div style="margin-bottom:16px;background:var(--card);border:1px solid var(--card-border);border-radius:var(--radius);overflow:hidden;">
    <iframe src="https://view.officeapps.live.com/op/view.aspx?src=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . '/' . ltrim($ppt_path, '../')); ?>" 
        style="width:100%;height:480px;border:none;" frameborder="0"></iframe>
</div>
<?php endif; ?>

<!-- SLIDE VIEWER -->
<div class="cv-slide-area">
    <div class="cv-slide-progress">
        <div class="cv-slide-progress-fill" id="progress-fill"></div>
    </div>
    <div class="cv-slide-body" id="slide-body">
        <div class="slide-number" id="slide-number">Slide 1 dari <?php echo $total_slides; ?></div>
        <h2 id="slide-title"><?php echo htmlspecialchars($slide_content['title']); ?></h2>
        <div id="slide-content"><?php echo $slide_content['body']; ?></div>
    </div>
    <div class="cv-nav">
        <div class="cv-nav-left" id="nav-info">Materi <?php echo $quest_label; ?></div>
        <div class="cv-nav-right">
            <button class="cv-btn" id="btn-prev" disabled>← Sebelumnya</button>
            <button class="cv-btn cv-btn--primary" id="btn-next">Selanjutnya →</button>
            <button class="cv-btn cv-btn--suc" id="btn-praktik" style="display:none">🚀 Mulai Praktik Coding</button>
        </div>
    </div>
</div>

</div></div>

<?php include '../includes/loading.php'; ?>
<?php include '../includes/toast.php'; ?>
<script src="../assets/js/navbar.js"></script>
<script>
(function(){
var totalSlides = <?php echo $total_slides; ?>;
var currentSlide = 0;
var slides = [];

<?php for ($i = 0; $i < $total_slides; $i++):
    $sc = getSlideContent($level_name, $quest_label, $skill, $i, $total_slides); ?>
slides.push(<?php echo json_encode($sc); ?>);
<?php endfor; ?>

function renderSlide(idx) {
    var s = slides[idx];
    document.getElementById('slide-number').textContent = 'Slide ' + (idx + 1) + ' dari ' + totalSlides;
    document.getElementById('slide-title').textContent = s.title;
    document.getElementById('slide-content').innerHTML = s.body;
    document.getElementById('progress-fill').style.width = ((idx + 1) / totalSlides * 100) + '%';

    document.getElementById('btn-prev').disabled = idx === 0;
    var isLast = idx === totalSlides - 1;
    document.getElementById('btn-next').style.display = isLast ? 'none' : '';
    document.getElementById('btn-praktik').style.display = isLast ? '' : 'none';
    if (isLast) {
        document.getElementById('nav-info').textContent = '🎉 Selesai! Saatnya coding!';
    } else {
        document.getElementById('nav-info').textContent = 'Materi <?php echo htmlspecialchars($quest_label); ?>';
    }
    document.getElementById('slide-body').scrollTop = 0;
}

document.getElementById('btn-next').addEventListener('click', function() {
    if (currentSlide < totalSlides - 1) { currentSlide++; renderSlide(currentSlide); }
});
document.getElementById('btn-prev').addEventListener('click', function() {
    if (currentSlide > 0) { currentSlide--; renderSlide(currentSlide); }
});
document.getElementById('btn-praktik').addEventListener('click', function() {
    var url = 'playground.php?course_id=<?php echo $level_data['course_id']; ?>' +
        '&level=' + encodeURIComponent('<?php echo htmlspecialchars($level_name); ?>') +
        '&quest=' + encodeURIComponent('<?php echo htmlspecialchars($quest_label); ?>') +
        '&skill=' + encodeURIComponent('<?php echo htmlspecialchars($skill); ?>') +
        '&xp=' + <?php echo $level_data['reward']; ?> +
        '&level_id=' + <?php echo $level_id; ?> +
        '&quest_idx=' + <?php echo $quest_idx; ?>;
    window.location.href = url;
});

renderSlide(0);

// Keyboard nav
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowRight' || e.key === ' ') {
        e.preventDefault();
        if (currentSlide < totalSlides - 1) { currentSlide++; renderSlide(currentSlide); }
    } else if (e.key === 'ArrowLeft') {
        e.preventDefault();
        if (currentSlide > 0) { currentSlide--; renderSlide(currentSlide); }
    }
});
})();
</script>
</body>
</html>
