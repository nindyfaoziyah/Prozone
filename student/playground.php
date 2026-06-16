<?php
require_once '../config/config.php';
requireLogin();
require_once '../includes/icons.php';

$page_title       = 'Coding Playground';
$page_description = 'Praktik coding interaktif';
$page_css         = ['sidebar-island.css', 'dashboard-override.css'];
$body_class       = trim(getThemeClass() . ' dashboard-layout');

$course_id = $_GET['course_id'] ?? 0;
$level     = $_GET['level'] ?? 'Praktik';
$quest     = $_GET['quest'] ?? 'Coding Challenge';
$skill     = $_GET['skill'] ?? '';
$xp        = (int)($_GET['xp'] ?? 50);
$level_id  = (int)($_GET['level_id'] ?? 0);
$quest_idx = (int)($_GET['quest_idx'] ?? -1);
$has_next  = ($level_id > 0 && $quest_idx >= 0);

$level_quests = [
    1 => 8, 2 => 7, 3 => 8, 4 => 7, 5 => 6, 6 => 7, 7 => 6, 8 => 7
];
$total_quests = $level_quests[$level_id] ?? 0;

// Check if there are more quests in this level
$next_quest_url = '';
if ($has_next) {
    if ($quest_idx + 1 < $total_quests) {
        $next_quest_url = 'course-viewer.php?level_id=' . $level_id . '&quest=' . ($quest_idx + 1);
    }
}
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
body.dashboard-layout .dashboard-content { max-width: 1600px; }

.pg-layout {
    display: grid; grid-template-columns: 320px 1fr; gap: 16px;
    min-height: calc(100vh - 160px);
}

/* INSTRUCTIONS PANEL */
.pg-side {
    background: var(--card); border: 1px solid var(--card-border);
    border-radius: var(--radius); padding: 18px 16px;
    display: flex; flex-direction: column; gap: 10px; overflow-y: auto;
}
.pg-side h2 { font-size: .85rem; font-weight: 800; color: var(--text-p); margin: 0 0 2px; }
.pg-side .pg-badge {
    font-size: .55rem; font-weight: 700; text-transform: uppercase;
    padding: 2px 10px; border-radius: 999px;
    background: var(--primary); color: #fff; display: inline-block;
    letter-spacing: .04em; margin-bottom: 4px;
}
.pg-side p { font-size: .72rem; color: var(--text-s); margin: 0 0 6px; line-height: 1.5; }

.pg-req { margin: 6px 0 0; }
.pg-req-title { font-size: .65rem; font-weight: 700; color: var(--text-m); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
.pg-req-item {
    display: flex; align-items: center; gap: 6px;
    font-size: .7rem; color: var(--text-s); padding: 4px 0;
}
.pg-req-item .pg-req-icon {
    width: 16px; height: 16px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: .45rem; font-weight: 800;
    background: var(--border-light); color: var(--text-m);
    transition: all .3s;
}
.pg-req-item.done .pg-req-icon { background: rgba(34,197,94,0.12); color: var(--suc); }
.pg-req-item.done { color: var(--suc); text-decoration: line-through; }

/* MAIN AREA */
.pg-main { display: flex; flex-direction: column; gap: 12px; min-height: 0; }

.pg-tabs {
    display: flex; gap: 2px; background: var(--card); border: 1px solid var(--card-border);
    border-radius: var(--radius) var(--radius) 0 0; padding: 6px 6px 0; overflow-x: auto;
}
.pg-tab {
    padding: 6px 16px; font-size: .65rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .04em; border: none; background: transparent; color: var(--text-m);
    cursor: pointer; border-radius: 8px 8px 0 0; transition: all .2s; font-family: inherit;
}
.pg-tab:hover { color: var(--text-s); background: var(--bg); }
.pg-tab.active { color: var(--primary); background: var(--bg); }

.pg-editors {
    flex: 1; display: flex; flex-direction: column; min-height: 0;
    background: var(--card); border: 1px solid var(--card-border); border-top: none;
    border-radius: 0 0 var(--radius) var(--radius); overflow: hidden;
}
.pg-editor-wrap { flex: 1; display: none; min-height: 0; }
.pg-editor-wrap.active { display: flex; }
.pg-editor-wrap textarea {
    width: 100%; height: 100%; min-height: 220px;
    border: none; outline: none; resize: none; padding: 14px;
    font-family: 'Cascadia Code','Fira Code','SF Mono','Consolas',monospace;
    font-size: .78rem; line-height: 1.6; color: #0F172A;
    background: #F8FAFC; tab-size: 2;
}
.pg-editor-wrap textarea::placeholder { color: #94A3B8; }

/* PREVIEW */
.pg-preview {
    background: var(--card); border: 1px solid var(--card-border);
    border-radius: var(--radius); overflow: hidden;
    display: flex; flex-direction: column; min-height: 260px;
}
.pg-preview-label {
    font-size: .55rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: var(--text-m); padding: 6px 14px;
    background: var(--bg); border-bottom: 1px solid var(--border-light);
}
.pg-preview iframe { width: 100%; flex: 1; border: none; background: #fff; }

/* SUBMIT */
.pg-actions {
    display: flex; gap: 8px; align-items: center;
    padding: 8px 0 0; flex-wrap: wrap;
}
.pg-btn {
    padding: 9px 24px; border-radius: 12px; border: none;
    font-size: .78rem; font-weight: 700; cursor: pointer;
    transition: all .2s; font-family: inherit;
}
.pg-btn--run {
    background: var(--primary); color: #fff;
}
.pg-btn--run:hover { background: #4f46e5; }
.pg-btn--submit {
    background: linear-gradient(135deg, var(--suc), #059669); color: #fff;
}
.pg-btn--submit:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(34,197,94,0.2); }
.pg-btn--submit:disabled { opacity: .4; cursor: not-allowed; transform: none; box-shadow: none; }
.pg-btn--suc {
    background: linear-gradient(135deg, #F59E0B, #D97706); color: #fff;
}
.pg-btn--suc:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(245,158,11,0.25); }
.pg-btn--back {
    background: var(--bg); color: var(--text-s); border: 1px solid var(--card-border);
}
.pg-btn--back:hover { border-color: var(--primary); color: var(--primary); }
.pg-status {
    font-size: .7rem; color: var(--text-m); font-weight: 600; margin-left: auto;
}

/* RESULT OVERLAY */
.pg-result {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(15,23,42,0.6); backdrop-filter: blur(8px);
    display: none; align-items: center; justify-content: center; padding: 20px;
    animation: pgFadeIn .3s ease;
}
@keyframes pgFadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes pgBounce {
    0% { transform: scale(0.7); opacity: 0; }
    60% { transform: scale(1.04); }
    100% { transform: scale(1); opacity: 1; }
}
@keyframes pgSlideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
@keyframes pgPulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0.4); }
    50% { box-shadow: 0 0 0 16px rgba(34,197,94,0); }
}
@keyframes pgConfetti {
    0% { transform: translateY(0) rotate(0deg); opacity: 1; }
    100% { transform: translateY(-60px) rotate(720deg); opacity: 0; }
}

.pg-result-box {
    background: linear-gradient(145deg, #FFFFFF, #F8FAFC);
    border-radius: 24px; width: 100%; max-width: 440px;
    padding: 0; text-align: center;
    box-shadow: 0 25px 60px -12px rgba(0,0,0,0.25);
    animation: pgBounce .45s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    overflow: hidden;
}
.pg-result-head {
    background: linear-gradient(135deg, #059669, #10B981, #34D399);
    padding: 32px 28px 28px; position: relative; overflow: hidden;
}
.pg-result-head::before {
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(circle at 30% 20%, rgba(255,255,255,0.2) 0%, transparent 60%);
}
.pg-result-icon {
    font-size: 3.2rem; margin-bottom: 8px; display: block;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
    animation: pgSlideUp .4s .15s both;
}
.pg-result-head h2 {
    font-size: 1.15rem; font-weight: 800; color: #fff; margin: 0 0 3px;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    animation: pgSlideUp .4s .2s both;
}
.pg-result-head p {
    font-size: .75rem; color: rgba(255,255,255,0.85); margin: 0;
    line-height: 1.4;
    animation: pgSlideUp .4s .25s both;
}
.pg-result-body { padding: 20px 28px 24px; }
.pg-xp {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 28px; border-radius: 999px;
    background: linear-gradient(135deg, rgba(34,197,94,0.1), rgba(16,185,129,0.06));
    border: 1.5px solid rgba(34,197,94,0.15);
    color: #059669; font-size: 1.3rem; font-weight: 900; margin-bottom: 14px;
    animation: pgSlideUp .4s .3s both, pgPulse 2s ease-in-out .5s infinite;
}
.pg-xp span { font-size: .75rem; font-weight: 600; color: #6B7280; }
.pg-result-detail {
    font-size: .7rem; color: var(--text-m); margin: 0 0 14px; line-height: 1.5;
    animation: pgSlideUp .4s .35s both;
}
.pg-result-actions { display: flex; flex-direction: column; gap: 8px; animation: pgSlideUp .4s .4s both; }
.pg-result .pg-btn {
    margin: 0; padding: 11px 24px; border-radius: 14px;
    font-size: .8rem; font-weight: 700; cursor: pointer; transition: all .2s;
    font-family: inherit; border: none; width: 100%;
}
.pg-result .pg-btn--submit {
    background: linear-gradient(135deg, #6366F1, #8B5CF6); color: #fff;
}
.pg-result .pg-btn--submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99,102,241,0.3); }
.pg-result .pg-btn--suc {
    background: linear-gradient(135deg, #F59E0B, #D97706); color: #fff;
}
.pg-result .pg-btn--suc:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(245,158,11,0.3); }
.pg-result .pg-btn--back {
    background: transparent; color: var(--text-m); border: none;
}
.pg-result .pg-btn--back:hover { color: var(--text-s); }

@media (max-width: 900px) {
    .pg-layout { grid-template-columns: 1fr; }
    .pg-side { max-height: none; }
    .pg-editor-wrap textarea { min-height: 150px; }
}
</style>
</head>
<body class="<?php echo $body_class; ?>">
<?php require_once 'navbar.php'; ?>
<div class="page-wrapper dashboard-main-container">
<div class="dashboard-content">

<div class="pg-layout">
    <!-- SIDE: Instructions -->
    <aside class="pg-side">
        <span class="pg-badge"><?php echo htmlspecialchars($skill ?: 'Praktik'); ?></span>
        <h2><?php echo htmlspecialchars($quest); ?></h2>
        <p><?php echo htmlspecialchars($level); ?> — Kerjakan challenge di bawah sesuai ketentuan.</p>

        <div class="pg-req">
            <div class="pg-req-title">Syarat Lulus</div>
            <div id="req-list"></div>
        </div>

        <div style="margin-top:auto;padding-top:10px;border-top:1px solid var(--border-light);font-size:.6rem;color:var(--text-m)">
            <strong>💡 Tip:</strong> Tulis kode di editor, lihat hasil live di preview, lalu klik Submit jika sudah sesuai.
        </div>
    </aside>

    <!-- MAIN -->
    <div class="pg-main">
        <!-- Tabs -->
        <div class="pg-tabs">
            <button class="pg-tab active" data-editor="html">HTML</button>
            <button class="pg-tab" data-editor="css">CSS</button>
            <button class="pg-tab" data-editor="js">JavaScript</button>
        </div>

        <!-- Editors -->
        <div class="pg-editors">
            <div class="pg-editor-wrap active" data-editor="html">
                <textarea id="code-html" placeholder="&lt;!-- Tulis HTML di sini --&gt;" spellcheck="false"></textarea>
            </div>
            <div class="pg-editor-wrap" data-editor="css">
                <textarea id="code-css" placeholder="/* Tulis CSS di sini */" spellcheck="false"></textarea>
            </div>
            <div class="pg-editor-wrap" data-editor="js">
                <textarea id="code-js" placeholder="// Tulis JavaScript di sini" spellcheck="false"></textarea>
            </div>
        </div>

        <!-- Preview -->
        <div class="pg-preview">
            <div class="pg-preview-label">🔍 Live Preview</div>
            <iframe id="preview" sandbox="allow-scripts"></iframe>
        </div>

        <!-- Actions -->
        <div class="pg-actions">
            <button class="pg-btn pg-btn--back" onclick="history.back()">← Kembali</button>
            <button class="pg-btn pg-btn--run" id="btn-run">▶ Jalankan</button>
            <button class="pg-btn pg-btn--submit" id="btn-submit">✅ Submit</button>
            <span class="pg-status" id="pg-status"></span>
        </div>
    </div>
</div>

</div></div>

<?php include '../includes/loading.php'; ?>
<?php include '../includes/toast.php'; ?>
<script src="../assets/js/navbar.js"></script>
<script>
(function(){
var $ = function(id) { return document.getElementById(id); };
var questLabel = <?php echo json_encode($quest); ?>;
var skillName = <?php echo json_encode($skill); ?>;
var xpReward = <?php echo $xp; ?>;
var nextQuestUrl = <?php echo json_encode($next_quest_url); ?>;
var fromLearningPath = <?php echo $has_next ? 'true' : 'false'; ?>;
var backUrl = fromLearningPath ? 'learning-path.php' : 'courses.php';
var totalQuests = <?php echo $total_quests; ?>;

// ===== DEFAULT CODE TEMPLATES =====
var defaults = {
    html: '<div class="card">\n  <h1>Halo, Dunia!</h1>\n  <p>Selamat datang di coding playground.</p>\n</div>',
    css: '/* Mulai coding CSS di sini */\n.card {\n  padding: 20px;\n  border-radius: 10px;\n  background: #f0f0f0;\n}\n',
    js: '// JavaScript di sini\nconsole.log("Ready!");\n'
};

var codeHtml = $('code-html');
var codeCss = $('code-css');
var codeJs = $('code-js');
var preview = $('preview');
var btnRun = $('btn-run');
var btnSubmit = $('btn-submit');
var statusEl = $('pg-status');
var reqList = $('req-list');

// Load saved code or defaults
var saved = JSON.parse(localStorage.getItem('pg_code_' + questLabel) || 'null');
codeHtml.value = (saved && saved.html) ? saved.html : defaults.html;
codeCss.value = (saved && saved.css) ? saved.css : defaults.css;
codeJs.value = (saved && saved.js) ? saved.js : defaults.js;

// ===== TABS =====
document.querySelectorAll('.pg-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.pg-tab').forEach(function(t) { t.classList.remove('active'); });
        document.querySelectorAll('.pg-editor-wrap').forEach(function(e) { e.classList.remove('active'); });
        tab.classList.add('active');
        var ed = document.querySelector('.pg-editor-wrap[data-editor="' + tab.dataset.editor + '"]');
        if (ed) ed.classList.add('active');
    });
});

// ===== RUN (update preview) =====
function runCode() {
    var html = codeHtml.value;
    var css = '<style>' + codeCss.value + '</style>';
    var js = '<script>' + codeJs.value + '<\/script>';
    var doc = '<!DOCTYPE html><html><head>' + css + '</head><body>' + html + js + '</body></html>';
    preview.srcdoc = doc;
    saveCode();
}
function saveCode() {
    localStorage.setItem('pg_code_' + questLabel, JSON.stringify({
        html: codeHtml.value, css: codeCss.value, js: codeJs.value
    }));
}

btnRun.addEventListener('click', runCode);
codeHtml.addEventListener('input', saveCode);
codeCss.addEventListener('input', saveCode);
codeJs.addEventListener('input', saveCode);

// ===== REQUIREMENTS =====
function getRequirements(label) {
    var map = {
        'Pengenalan HTML': [
            { check: function(c) { return /<h[1-6]>/.test(c) || /<p>/.test(c); }, label: 'Membuat heading atau paragraf' },
            { check: function(c) { return /<[a-z]+(\s|>)/.test(c); }, label: 'Menggunakan tag HTML' },
            { check: function(c) { return c.length > 30; }, label: 'Menulis kode HTML minimal' },
        ],
        'Struktur Dasar HTML': [
            { check: function(c) { return /<!DOCTYPE html>/i.test(c); }, label: 'Menyertakan DOCTYPE' },
            { check: function(c) { return /<html/i.test(c); }, label: 'Membuat tag html' },
            { check: function(c) { return /<head>/i.test(c); }, label: 'Membuat tag head' },
            { check: function(c) { return /<body>/i.test(c); }, label: 'Membuat tag body' },
        ],
        'Heading & Paragraph': [
            { check: function(c) { return /<h[1-6]>/.test(c); }, label: 'Menggunakan heading (h1-h6)' },
            { check: function(c) { return /<p>/.test(c); }, label: 'Membuat paragraf' },
            { check: function(c) { return /<(b|strong|i|em|u)>/.test(c); }, label: 'Menggunakan format teks (bold/italic)' },
        ],
        'Link & Image': [
            { check: function(c) { return /<a\s/.test(c); }, label: 'Membuat link dengan tag a' },
            { check: function(c) { return /href\s*=/.test(c); }, label: 'Menyertakan atribut href' },
            { check: function(c) { return /<img\s/.test(c); }, label: 'Menyertakan tag img' },
            { check: function(c) { return /src\s*=/.test(c); }, label: 'Menyertakan atribut src' },
        ],
        'List & Table': [
            { check: function(c) { return /<table>/i.test(c) || /<(ul|ol)>/.test(c); }, label: 'Membuat table atau list' },
            { check: function(c) { return /<tr>|<li>/i.test(c); }, label: 'Membuat baris/item' },
            { check: function(c) { return /<td>|<th>/i.test(c) || /<li>/.test(c); }, label: 'Membuat data/sel/item' },
        ],
        'Form & Input': [
            { check: function(c) { return /<form/i.test(c); }, label: 'Membuat form' },
            { check: function(c) { return /<input/.test(c); }, label: 'Menyertakan input' },
            { check: function(c) { return /type\s*=/.test(c); }, label: 'Menentukan tipe input' },
        ],
        'Semantic HTML': [
            { check: function(c) { return /<(header|nav|main|section|article|aside|footer)>/i.test(c); }, label: 'Menggunakan elemen semantic' },
            { check: function(c) { return /<(header|nav|main)>/i.test(c); }, label: 'Menyertakan header/nav/main' },
            { check: function(c) { return /<(section|article)>/i.test(c) || /<(aside|footer)>/i.test(c); }, label: 'Menyertakan section/article/footer' },
        ],
        'Multimedia & Embed': [
            { check: function(c) { return /<img\s/.test(c) || /<video/.test(c) || /<audio/.test(c); }, label: 'Menyertakan elemen multimedia' },
            { check: function(c) { return /src\s*=/.test(c); }, label: 'Menyertakan sumber media (src)' },
            { check: function(c) { return /alt\s*=/.test(c); }, label: 'Menyertakan teks alternatif' },
        ],
        'CSS Dasar & Selector': [
            { check: function(c) { return /\.\w+/.test(c); }, label: 'Menggunakan selector class (.nama)' },
            { check: function(c) { return /color\s*:/.test(c); }, label: 'Mengubah warna teks (color)' },
            { check: function(c) { return /font-size\s*:/.test(c); }, label: 'Mengubah ukuran font (font-size)' },
        ],
        'Color & Typography': [
            { check: function(c) { return /color\s*:/.test(c); }, label: 'Mengubah warna teks' },
            { check: function(c) { return /font-family\s*:/.test(c); }, label: 'Mengatur jenis font' },
            { check: function(c) { return /text-align\s*:/.test(c); }, label: 'Mengatur perataan teks' },
        ],
        'Box Model & Layout': [
            { check: function(c) { return /margin\s*:/.test(c); }, label: 'Menggunakan margin' },
            { check: function(c) { return /padding\s*:/.test(c); }, label: 'Menggunakan padding' },
            { check: function(c) { return /border\s*:/.test(c); }, label: 'Menggunakan border' },
        ],
        'Flexbox': [
            { check: function(c) { return /display\s*:\s*flex/.test(c); }, label: 'Menggunakan display: flex' },
            { check: function(c) { return /justify-content\s*:/.test(c); }, label: 'Mengatur justify-content' },
            { check: function(c) { return /align-items\s*:/.test(c); }, label: 'Mengatur align-items' },
        ],
        'Grid Layout': [
            { check: function(c) { return /display\s*:\s*grid/.test(c); }, label: 'Menggunakan display: grid' },
            { check: function(c) { return /grid-template-columns\s*:/.test(c); }, label: 'Mengatur grid-template-columns' },
            { check: function(c) { return /gap\s*:/.test(c) || /grid-gap\s*:/.test(c); }, label: 'Mengatur gap' },
        ],
        'Responsive Design': [
            { check: function(c) { return /@media/.test(c); }, label: 'Menggunakan media query (@media)' },
            { check: function(c) { return /max-width\s*:|min-width\s*:/.test(c); }, label: 'Mengatur breakpoint (max/min-width)' },
            { check: function(c) { return /width\s*:\s*.*%/.test(c); }, label: 'Menggunakan ukuran persen (%)' },
        ],
        'CSS Animation': [
            { check: function(c) { return /@keyframes/.test(c); }, label: 'Membuat keyframes animation' },
            { check: function(c) { return /animation\s*:/.test(c); }, label: 'Menerapkan animation' },
            { check: function(c) { return /transition\s*:/.test(c); }, label: 'Menggunakan transisi' },
        ],
        // JavaScript quests
        'Variable & Tipe Data': [
            { check: function(c) { return /(var|let|const)\s+\w+/.test(c); }, label: 'Mendeklarasikan variabel (var/let/const)' },
            { check: function(c) { return /\w+\s*=\s*["']/.test(c); }, label: 'Menggunakan tipe data string' },
            { check: function(c) { return /\w+\s*=\s*\d+/.test(c); }, label: 'Menggunakan tipe data number' },
        ],
        'Function': [
            { check: function(c) { return /function\s+\w+\s*\(/.test(c) || /\w+\s*=\s*\(?\s*\w*\s*\)?\s*=>/.test(c); }, label: 'Membuat function' },
            { check: function(c) { return /return\s/.test(c); }, label: 'Menggunakan return' },
            { check: function(c) { return /\w+\s*\(/.test(c); }, label: 'Memanggil function' },
        ],
        'DOM Manipulation': [
            { check: function(c) { return /getElementById|querySelector/.test(c); }, label: 'Mengakses elemen DOM' },
            { check: function(c) { return /\.innerHTML|\.textContent|\.innerText/.test(c); }, label: 'Mengubah konten elemen' },
            { check: function(c) { return /\.addEventListener|\.onclick/.test(c); }, label: 'Menambahkan event listener' },
        ],
    };

    if (map[label]) return map[label];

    // Auto-detect type from label
    var isCSS = /css|color|typography|box model|flexbox|grid|layout|responsive|animation|selector/i.test(label);
    var isJS = /variable|tipe data|function|dom|event|array|object|operator|conditional|loop|async|fetch/i.test(label);

    if (isCSS) return [
        { check: function(c) { return /\w+\s*\{/.test(c); }, label: 'Menggunakan selector CSS' },
        { check: function(c) { return /\w+\s*:\s*\w+/.test(c); }, label: 'Mendeklarasikan properti CSS' },
        { check: function(c) { return c.split('{').length > 2; }, label: 'Memiliki minimal 2 aturan CSS' },
    ];
    if (isJS) return [
        { check: function(c) { return /\w+\s*\(/.test(c); }, label: 'Memanggil function' },
        { check: function(c) { return /\w+\s*=\s*/.test(c); }, label: 'Menggunakan operator assignment' },
        { check: function(c) { return /console\.log/.test(c); }, label: 'Mencetak output (console.log)' },
    ];
    return [
        { check: function(c) { return /<[a-z]+(\s|>)/.test(c) || /\w+\s*\{/.test(c) || /\w+\s*\(/.test(c); }, label: 'Menulis kode yang valid' },
        { check: function(c) { return c.length > 20; }, label: 'Menulis minimal kode' },
        { check: function(c) { return true; }, label: 'Mencoba dan berusaha 💪' },
    ];
}

var requirements = getRequirements(questLabel);
var reqChecks = [];

function renderRequirements() {
    reqList.innerHTML = '';
    reqChecks = [];
    requirements.forEach(function(r) {
        var el = document.createElement('div');
        el.className = 'pg-req-item';
        el.innerHTML = '<span class="pg-req-icon">✕</span> ' + r.label;
        reqList.appendChild(el);
        reqChecks.push({ el: el, check: r.check, done: false });
    });
}
renderRequirements();

function validateAll(showStatus) {
    var allDone = true;
    var combined = codeHtml.value + '\n' + codeCss.value + '\n' + codeJs.value;
    reqChecks.forEach(function(r) {
        r.done = r.check(combined);
        r.el.className = 'pg-req-item' + (r.done ? ' done' : '');
        r.el.querySelector('.pg-req-icon').textContent = r.done ? '✓' : '✕';
        if (!r.done) allDone = false;
    });
    if (showStatus && !allDone) {
        var failCount = reqChecks.filter(function(r) { return !r.done; }).length;
        statusEl.textContent = '❌ ' + failCount + ' syarat belum terpenuhi';
        statusEl.style.color = '#EF4444';
    }
    return allDone;
}

// Real-time validation on input
codeHtml.addEventListener('input', function() { validateAll(false); });
codeCss.addEventListener('input', function() { validateAll(false); });
codeJs.addEventListener('input', function() { validateAll(false); });

// ===== SUBMIT =====
function saveQuestProgress(cb) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../api/complete-quest.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (cb) cb(xhr.status === 200 ? JSON.parse(xhr.responseText) : null);
        }
    };
    xhr.send(JSON.stringify({
        level_id: <?php echo $level_id; ?>,
        quest_idx: <?php echo $quest_idx; ?>,
        course_id: <?php echo $course_id ?: 0; ?>,
        xp: xpReward,
        total_quests: totalQuests
    }));
}

btnSubmit.addEventListener('click', function() {
    if (!validateAll(true)) {
        return;
    }
    statusEl.textContent = '✅ Semua syarat terpenuhi!';
    statusEl.style.color = 'var(--suc)';

    // Save to database
    saveQuestProgress(function(result) {
        // Show success result
        showResult(true, xpReward);
        // Auto-save completion
        localStorage.setItem('pg_done_' + questLabel, '1');
        localStorage.removeItem('pg_code_' + questLabel);
    });
});

function showResult(success, xp) {
    var overlay = document.createElement('div');
    overlay.className = 'pg-result';
    overlay.style.display = 'flex';

    var nextBtn = nextQuestUrl
        ? '<button class="pg-btn pg-btn--suc" onclick="window.location.href=\'' + nextQuestUrl + '\'">📖 Lanjut ke Materi Berikutnya</button>'
        : '';

    overlay.innerHTML =
        '<div class="pg-result-box">' +
            '<div class="pg-result-head">' +
                '<span class="pg-result-icon">' + (success ? '🏆' : '😅') + '</span>' +
                '<h2>' + (success ? 'Quest Completed!' : 'Belum Lulus') + '</h2>' +
                '<p>' + (success ? 'Selamat! Kamu berhasil menyelesaikan quest ini.' : 'Perbaiki kode kamu dan coba lagi.') + '</p>' +
            '</div>' +
            '<div class="pg-result-body">' +
                (success ? '<div class="pg-xp"><span>+</span>' + xp + ' <span>XP</span></div>' : '') +
                '<div class="pg-result-detail">' +
                    (success
                        ? '✅ ' + reqChecks.filter(function(r){return r.done}).length + ' dari ' + reqChecks.length + ' syarat terpenuhi'
                        : '✕ Masih ada syarat yang belum terpenuhi') +
                '</div>' +
                '<div class="pg-result-actions">' +
                    (success
                        ? '<button class="pg-btn pg-btn--submit" onclick="window.location.href=\'' + backUrl + '\'">🚀 Lanjut Belajar</button>' + nextBtn
                        : '<button class="pg-btn pg-btn--run" onclick="this.closest(\'.pg-result\').remove()">🔄 Coba Lagi</button>') +
                    '<button class="pg-btn pg-btn--back" onclick="window.location.href=\'' + backUrl + '\'">← Kembali</button>' +
                '</div>' +
            '</div>' +
        '</div>';
    document.body.appendChild(overlay);
    overlay.addEventListener('click', function(e) { if (e.target === overlay) overlay.remove(); });
}

// ===== AUTO SAVE ON LOAD =====
// Preview starts blank — click "Jalankan" to run code
})();
</script>
</body>
</html>
