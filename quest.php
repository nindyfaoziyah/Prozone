<?php
require_once 'config/config.php';
requireLogin();
require_once 'includes/icons.php';

require_once 'models/Course.php';
require_once 'models/Lesson.php';

$database = new Database();
$db = $database->getConnection();

$course = new Course($db);
$lesson = new Lesson($db);

$course_id = $_GET['course_id'] ?? 0;
if (!$course_id) {
    header('Location: learning-path.php');
    exit();
}

$course->id = $course_id;
$course_data = $course->readOne();
if (!$course_data) {
    header('Location: learning-path.php');
    exit();
}

$lessons_stmt = $lesson->readByCourse($course_id);
$all_lessons = [];
while ($row = $lessons_stmt->fetch(PDO::FETCH_ASSOC)) {
    $all_lessons[] = $row;
}

$theory_lessons = array_values(array_filter($all_lessons, fn($l) => $l['tipe'] === 'theory'));

$first_practice = null;
foreach ($all_lessons as $l) {
    if ($l['tipe'] === 'practice') {
        $first_practice = $l;
        break;
    }
}

$course_lower = strtolower($course_data['judul_course'] ?? '');
$file_ext = '.html';
$editor_mode = 'htmlmixed';
$default_code = '<!DOCTYPE html>
<html>
<head>
    <title>Hello World</title>
</head>
<body>
    <h1>Hello World!</h1>
    <p>Ini adalah paragraf pertama saya.</p>
</body>
</html>';

if (strpos($course_lower, 'python') !== false) {
    $file_ext = '.py';
    $editor_mode = 'python';
    $default_code = '# Python Code
print("Hello, World!")
';
} elseif (strpos($course_lower, 'php') !== false) {
    $file_ext = '.php';
    $editor_mode = 'php';
    $default_code = '<?php
echo "Hello, World!";
?>';
} elseif (strpos($course_lower, 'javascript') !== false || strpos($course_lower, 'js') !== false) {
    $file_ext = '.js';
    $editor_mode = 'javascript';
    $default_code = '// JavaScript Code
console.log("Hello, World!");
';
} elseif (strpos($course_lower, 'java') !== false && strpos($course_lower, 'javascript') === false) {
    $file_ext = '.java';
    $editor_mode = 'text/x-java';
    $default_code = 'public class Main {
    public static void main(String[] args) {
        System.out.println("Hello, World!");
    }
}';
} elseif (strpos($course_lower, 'c++') !== false || strpos($course_lower, 'cpp') !== false) {
    $file_ext = '.cpp';
    $editor_mode = 'text/x-c++src';
    $default_code = '#include <iostream>
using namespace std;

int main() {
    cout << "Hello, World!" << endl;
    return 0;
}';
} elseif (strpos($course_lower, 'c') !== false && strpos($course_lower, 'css') === false) {
    $file_ext = '.c';
    $editor_mode = 'text/x-csrc';
    $default_code = '#include <stdio.h>

int main() {
    printf("Hello, World!\\n");
    return 0;
}';
}

$practice_code = $default_code;
$practice_instructions = '';
if ($first_practice) {
    $practice_code = $first_practice['kode_contoh'] ?? $default_code;
    $instruksi = $first_practice['instruksi'] ?? '';
    if (!empty($instruksi)) {
        $parsed = json_decode($instruksi, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
            $texts = [];
            foreach ($parsed as $item) {
                if (is_array($item)) {
                    $texts[] = $item['text'] ?? '';
                } else {
                    $texts[] = $item;
                }
            }
            $practice_instructions = implode("\n\n", array_filter($texts));
        } else {
            $practice_instructions = $instruksi;
        }
    }
}

$slides = [];
foreach ($theory_lessons as $tl) {
    $content = $tl['konten'] ?? $tl['instruksi'] ?? '';
    if (!empty($content)) {
        $parsed = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
            if (isset($parsed['teori'])) {
                $slides[] = [
                    'title' => $tl['judul_lesson'],
                    'content' => $parsed['teori'],
                    'xp' => $tl['xp_reward'] ?? 10,
                ];
            } elseif (isset($parsed[0]['title'])) {
                foreach ($parsed as $s) {
                    $slides[] = [
                        'title' => $s['title'] ?? $tl['judul_lesson'],
                        'content' => $s['content'] ?? '',
                        'xp' => $tl['xp_reward'] ?? 10,
                    ];
                }
            }
        } else {
            $slides[] = [
                'title' => $tl['judul_lesson'],
                'content' => $content,
                'xp' => $tl['xp_reward'] ?? 10,
            ];
        }
    }
}

if (empty($slides)) {
    $slides[] = [
        'title' => $course_data['judul_course'],
        'content' => '<h2>Selamat Datang!</h2><p>' . htmlspecialchars($course_data['deskripsi'] ?? '') . '</p><p>Siapkan dirimu untuk memulai petualangan belajar yang seru!</p>',
        'xp' => 10,
    ];
}

$page_title = htmlspecialchars($course_data['judul_course']) . ' - Quest';
$page_css = ['sidebar-island.css', 'dashboard-override.css'];
$body_class = trim(getThemeClass() . ' dashboard-layout');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php require_once 'includes/head.php'; ?>
    <style>
        :root {
            --quest-primary: #0EA5E9;
            --quest-primary-dark: #0369A1;
            --quest-primary-light: #38BDF8;
            --quest-accent: #8B5CF6;
            --quest-bg: #F8FAFC;
            --quest-bg-secondary: #F1F5F9;
            --quest-bg-card: #FFFFFF;
            --quest-text: #0F172A;
            --quest-text-secondary: #475569;
            --quest-text-muted: #94A3B8;
            --quest-border: #E2E8F0;
            --quest-shadow: 0 4px 24px rgba(0,0,0,0.06);
        }

        .quest-hero {
            background: linear-gradient(135deg, #FFFFFF 0%, #F0F9FF 100%);
            border-bottom: 1px solid var(--quest-border);
            padding: 2rem 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .quest-hero::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--quest-primary), transparent);
        }

        .quest-hero-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
        }

        .quest-hero-info h1 {
            font-size: 1.75rem;
            font-weight: 800;
            margin: 0 0 0.25rem 0;
            color: #0F172A;
        }

        .quest-hero-info p {
            color: var(--quest-text-muted);
            margin: 0;
            font-size: 0.9rem;
        }

        .quest-hero-badge {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .quest-badge-item {
            padding: 0.5rem 1rem;
            background: #F8FAFC;
            border: 1px solid var(--quest-border);
            border-radius: 0.5rem;
            text-align: center;
        }

        .quest-badge-item strong {
            display: block;
            font-size: 1.1rem;
            color: var(--quest-primary);
        }

        .quest-badge-item span {
            font-size: 0.7rem;
            color: var(--quest-text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .quest-container {
            max-width: 960px;
            margin: 2rem auto;
            padding: 0 2rem 3rem;
        }

        .quest-slide-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1rem 1.5rem;
            background: var(--quest-bg-card);
            border: 1px solid var(--quest-border);
            border-radius: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .quest-slide-nav-btn {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--quest-primary) 0%, var(--quest-primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 0.625rem;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px rgba(14,165,233,0.3);
        }

        .quest-slide-nav-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(14,165,233,0.4);
        }

        .quest-slide-nav-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            box-shadow: none;
        }

        .quest-slide-nav-btn.practice-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 4px 15px rgba(16,185,129,0.3);
        }

        .quest-slide-nav-btn.practice-btn:hover {
            box-shadow: 0 8px 25px rgba(16,185,129,0.4);
        }

        .quest-slide-indicator {
            font-size: 1rem;
            font-weight: 700;
            color: var(--quest-primary);
        }

        .quest-progress {
            width: 100%;
            height: 6px;
            background: #E2E8F0;
            border-radius: 3px;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .quest-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--quest-primary), var(--quest-accent));
            border-radius: 3px;
            transition: width 0.5s cubic-bezier(0.4,0,0.2,1);
            width: 0%;
        }

        .quest-slide {
            display: none;
            background: var(--quest-bg-card);
            border: 1px solid var(--quest-border);
            border-radius: 1.25rem;
            padding: 2.5rem 3rem;
            min-height: 400px;
            animation: questSlideIn 0.5s cubic-bezier(0.4,0,0.2,1);
            box-shadow: var(--quest-shadow);
        }

        .quest-slide.active { display: block; }

        @keyframes questSlideIn {
            from { opacity: 0; transform: translateX(30px) scale(0.98); }
            to { opacity: 1; transform: translateX(0) scale(1); }
        }

        .quest-slide-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid;
            border-image: linear-gradient(90deg, var(--quest-primary), var(--quest-accent), transparent) 1;
        }

        .quest-slide-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0F172A;
            margin: 0;
        }

        .quest-slide-body {
            min-height: 300px;
            color: var(--quest-text-secondary);
            line-height: 1.8;
            font-size: 1rem;
        }

        .quest-slide-body h1 { font-size: 2rem; font-weight: 700; color: #0F172A; margin-bottom: 1rem; }
        .quest-slide-body h2 { font-size: 1.5rem; font-weight: 700; color: var(--quest-primary-dark); margin: 1.5rem 0 0.75rem; }
        .quest-slide-body h3 { font-size: 1.25rem; font-weight: 600; color: #1E293B; margin: 1.25rem 0 0.5rem; }
        .quest-slide-body p { margin-bottom: 1rem; line-height: 1.8; }
        .quest-slide-body ul, .quest-slide-body ol { padding-left: 1.5rem; margin-bottom: 1rem; }
        .quest-slide-body li { margin-bottom: 0.5rem; }
        .quest-slide-body strong { color: #0F172A; }
        .quest-slide-body code {
            background: #E0F2FE;
            color: #0369A1;
            padding: 0.2rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.9em;
        }
        .quest-slide-body pre {
            background: #0F172A;
            border: 1px solid #1E293B;
            border-radius: 0.75rem;
            padding: 1.25rem;
            overflow-x: auto;
            margin: 1rem 0;
        }
        .quest-slide-body pre code { background: none; padding: 0; color: #E2E8F0; }

        .quest-slide-body blockquote {
            border-left: 4px solid var(--quest-primary);
            padding: 0.75rem 1.25rem;
            margin: 1rem 0;
            background: #F0F9FF;
            border-radius: 0 0.5rem 0.5rem 0;
            color: var(--quest-text-secondary);
        }

        /* ===== WORKSPACE CODING ===== */
        .quest-workspace {
            display: none;
        }

        .quest-workspace.show {
            display: block;
            animation: questSlideIn 0.5s ease;
        }

        .quest-workspace-inner {
            display: flex;
            gap: 1.5rem;
            min-height: 500px;
        }

        .quest-workspace-instructions {
            flex: 0 0 340px;
            background: var(--quest-bg-card);
            border: 1px solid var(--quest-border);
            border-radius: 1rem;
            padding: 1.5rem;
            overflow-y: auto;
            max-height: 650px;
            position: sticky;
            top: 1rem;
        }

        .quest-workspace-instructions h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0F172A;
            margin: 0 0 1rem 0;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid;
            border-image: linear-gradient(90deg, var(--quest-primary), var(--quest-accent)) 1;
        }

        .quest-workspace-instructions p,
        .quest-workspace-instructions li {
            color: var(--quest-text-secondary);
            font-size: 0.875rem;
            line-height: 1.7;
        }

        .quest-workspace-instructions ol,
        .quest-workspace-instructions ul {
            padding-left: 1.25rem;
        }

        .quest-workspace-instructions li {
            margin-bottom: 0.5rem;
        }

        .quest-workspace-instructions code {
            background: #E0F2FE;
            color: #0369A1;
            padding: 0.15rem 0.4rem;
            border-radius: 0.25rem;
            font-size: 0.85em;
        }

        .quest-workspace-instructions pre {
            background: #0F172A;
            border-radius: 0.5rem;
            padding: 1rem;
            overflow-x: auto;
            margin: 0.75rem 0;
        }

        .quest-workspace-instructions pre code {
            background: none;
            padding: 0;
            color: #E2E8F0;
        }

        .quest-workspace-editor-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            min-width: 0;
        }

        .quest-workspace-editor {
            border: 1px solid var(--quest-border);
            border-radius: 0.75rem;
            overflow: hidden;
            flex: 1;
            min-height: 400px;
        }

        .quest-workspace-editor .CodeMirror {
            height: 100%;
            min-height: 400px;
            font-size: 14px;
        }

        .quest-workspace-editor .CodeMirror-gutters {
            background: #F8FAFC;
            border-right: 1px solid #E2E8F0;
        }

        .quest-workspace-editor .CodeMirror-linenumber {
            color: #94A3B8;
        }

        .quest-workspace-editor .CodeMirror-cursor {
            border-left: 2px solid #0EA5E9;
        }

        .quest-workspace-output {
            border: 1px solid var(--quest-border);
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .quest-workspace-output-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background: var(--quest-bg-secondary);
            border-bottom: 1px solid var(--quest-border);
        }

        .quest-workspace-output-header span {
            font-weight: 600;
            font-size: 0.875rem;
            color: #0F172A;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quest-workspace-run-btn {
            padding: 0.5rem 1.25rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quest-workspace-run-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(16,185,129,0.3);
        }

        .quest-workspace-run-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .quest-workspace-output-body {
            padding: 1rem;
            min-height: 120px;
            max-height: 450px;
            overflow-y: auto;
            background: #0F172A;
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            font-size: 0.8125rem;
            line-height: 1.6;
            color: #E2E8F0;
            white-space: pre-wrap;
            transition: max-height 0.3s ease;
        }

        .quest-workspace-output-body iframe {
            border-radius: 0;
        }

        .quest-workspace-output-body.empty {
            color: #64748B;
            font-style: italic;
        }

        .quest-workspace-output-body.error {
            color: #F87171;
        }

        .quest-workspace-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: questSpin 0.6s linear infinite;
        }

        @keyframes questSpin {
            to { transform: rotate(360deg); }
        }

        .quest-workspace-status-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem 1rem;
            background: var(--quest-bg-card);
            border: 1px solid var(--quest-border);
            border-radius: 0.5rem;
            font-size: 0.8rem;
            color: var(--quest-text-muted);
        }

        .quest-workspace-status-bar strong {
            color: var(--quest-text-secondary);
        }

        @media (max-width: 768px) {
            .quest-hero-inner { flex-direction: column; text-align: center; }
            .quest-container { padding: 0 1rem 2rem; }
            .quest-slide { padding: 1.5rem; }
            .quest-slide-nav { flex-wrap: wrap; gap: 0.75rem; justify-content: center; }
            .quest-workspace-inner { flex-direction: column; }
            .quest-workspace-instructions { flex: none; max-height: none; position: static; }
        }
    </style>
    <!-- CodeMirror -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/php/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/python/python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/closetag.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/closebrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/matchbrackets.min.js"></script>
</head>
<body class="<?php echo $body_class; ?>">
<?php require_once 'navbar.php'; ?>

<div class="page-wrapper dashboard-main-container">
    <div class="quest-hero">
        <div class="quest-hero-inner">
            <div class="quest-hero-info">
                <h1><?php echo htmlspecialchars($course_data['judul_course']); ?></h1>
                <p><?php echo htmlspecialchars($course_data['deskripsi'] ?? ''); ?></p>
            </div>
            <div class="quest-hero-badge">
                <div class="quest-badge-item">
                    <strong><?php echo count($slides); ?></strong>
                    <span>Materi</span>
                </div>
                <div class="quest-badge-item">
                    <strong><?php echo count($all_lessons); ?></strong>
                    <span>Total Lesson</span>
                </div>
                <div class="quest-badge-item">
                    <strong>+<?php echo $course_data['total_xp'] ?? array_sum(array_column($all_lessons, 'xp_reward')); ?></strong>
                    <span>XP</span>
                </div>
            </div>
        </div>
    </div>

    <div class="quest-container">
        <div class="quest-slide-nav">
            <button class="quest-slide-nav-btn" onclick="prevSlide()" id="prevBtn" disabled>&#8592; Sebelumnya</button>
            <div class="quest-slide-indicator" id="slideIndicator">1 / <?php echo count($slides); ?></div>
            <button class="quest-slide-nav-btn" onclick="nextSlide()" id="nextBtn">Selanjutnya &#8594;</button>
            <button class="quest-slide-nav-btn practice-btn" onclick="goToPractice()" id="practiceBtn" style="display:none;">&#128187; Mulai Praktik Coding</button>
        </div>

        <div class="quest-progress">
            <div class="quest-progress-bar" id="progressBar"></div>
        </div>

        <div id="slidesContainer">
            <?php foreach ($slides as $idx => $slide): ?>
            <div class="quest-slide <?php echo $idx === 0 ? 'active' : ''; ?>" data-index="<?php echo $idx; ?>">
                <div class="quest-slide-header">
                    <h2 class="quest-slide-title"><?php echo htmlspecialchars($slide['title']); ?></h2>
                </div>
                <div class="quest-slide-body">
                    <?php echo $slide['content']; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="quest-workspace" id="questWorkspace">
            <div class="quest-workspace-inner">
                <div class="quest-workspace-instructions">
                    <h3>&#128221; Instruksi Praktik</h3>
                    <?php if ($first_practice && !empty($practice_instructions)): ?>
                    <div class="quest-workspace-instructions-body">
                        <?php echo nl2br(htmlspecialchars($practice_instructions)); ?>
                    </div>
                    <div class="quest-workspace-status-bar" style="margin-top:1rem;">
                        <span>Lesson: <strong><?php echo htmlspecialchars($first_practice['judul_lesson']); ?></strong></span>
                        <span>File: <strong><?php echo $file_ext; ?></strong></span>
                    </div>
                    <?php else: ?>
                    <div class="quest-workspace-instructions-body">
                        <p>Tulis kode sesuai dengan materi yang telah kamu pelajari sebelumnya. Gunakan editor di sebelah kanan untuk menulis kode, lalu klik <strong>Run</strong> untuk melihat hasilnya.</p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="quest-workspace-editor-panel">
                    <div class="quest-workspace-editor" id="codeEditor"></div>
                    <div class="quest-workspace-output">
                        <div class="quest-workspace-output-header">
                            <span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>
                                Output
                            </span>
                            <button class="quest-workspace-run-btn" onclick="runCode()" id="runBtn">
                                &#9654; Run
                            </button>
                        </div>
                        <div class="quest-workspace-output-body empty" id="outputBody">
                            Klik "Run" untuk menjalankan kode...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/loading.php'; ?>
<?php include 'includes/toast.php'; ?>
<script src="assets/js/navbar.js"></script>
<script>
(function() {
    const slides = document.querySelectorAll('.quest-slide');
    const totalSlides = slides.length;
    let currentSlide = 0;

    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const practiceBtn = document.getElementById('practiceBtn');
    const indicator = document.getElementById('slideIndicator');
    const progressBar = document.getElementById('progressBar');
    const workspace = document.getElementById('questWorkspace');
    const slidesContainer = document.getElementById('slidesContainer');

    function updateSlide(index) {
        slides.forEach((s, i) => {
            s.classList.toggle('active', i === index);
        });

        prevBtn.disabled = index === 0;
        const isLast = index === totalSlides - 1;
        nextBtn.style.display = isLast ? 'none' : '';
        practiceBtn.style.display = isLast ? '' : 'none';
        indicator.textContent = (index + 1) + ' / ' + totalSlides;
        progressBar.style.width = ((index + 1) / totalSlides * 100) + '%';
        currentSlide = index;
    }

    window.prevSlide = function() {
        if (currentSlide > 0) updateSlide(currentSlide - 1);
    };

    window.nextSlide = function() {
        if (currentSlide < totalSlides - 1) updateSlide(currentSlide + 1);
    };

    /** Init CodeMirror */
    let editor = null;
    function initEditor() {
        const editorEl = document.getElementById('codeEditor');
        if (!editorEl) {
            document.getElementById('outputBody').textContent = 'Error: Editor element not found';
            return;
        }
        if (typeof CodeMirror === 'undefined') {
            document.getElementById('outputBody').textContent = 'Error: CodeMirror library not loaded';
            return;
        }
        try {
            editor = CodeMirror(editorEl, {
                value: <?php echo json_encode($practice_code); ?>,
                mode: '<?php echo $editor_mode; ?>',
                theme: 'default',
                lineNumbers: true,
                indentUnit: 4,
                tabSize: 4,
                indentWithTabs: false,
                lineWrapping: true,
                autoCloseTags: true,
                autoCloseBrackets: true,
                matchBrackets: true
            });
        } catch(e) {
            document.getElementById('outputBody').textContent = 'Editor init error: ' + e.message;
        }
    }

    /** Show coding workspace */
    window.goToPractice = function() {
        slidesContainer.style.display = 'none';
        document.querySelector('.quest-slide-nav').style.display = 'none';
        document.querySelector('.quest-progress').style.display = 'none';
        workspace.classList.add('show');
        setTimeout(function() {
            if (!editor) initEditor();
            if (editor) editor.refresh();
        }, 300);
    };

    /** Run code via API */
    window.runCode = function() {
        const runBtn = document.getElementById('runBtn');
        const outputBody = document.getElementById('outputBody');
        const isHTML = <?php echo json_encode($editor_mode === 'htmlmixed'); ?>;

        runBtn.disabled = true;

        if (!editor) {
            initEditor();
        }
        if (!editor) {
            outputBody.className = 'quest-workspace-output-body error';
            outputBody.textContent = 'Error: Editor belum siap. Coba refresh halaman.';
            runBtn.disabled = false;
            runBtn.innerHTML = '\u25B6 Run';
            return;
        }

        const code = editor.getValue();

        if (isHTML) {
            runBtn.innerHTML = '<span class="quest-workspace-spinner"></span> Rendering...';
            outputBody.className = 'quest-workspace-output-body';
            outputBody.style.padding = '0';
            outputBody.style.overflow = 'hidden';
            outputBody.innerHTML = '';
            var iframe = document.createElement('iframe');
            iframe.style.cssText = 'width:100%;height:400px;border:none;background:#fff;display:block';
            outputBody.appendChild(iframe);
            setTimeout(function() {
                var doc = iframe.contentDocument || iframe.contentWindow.document;
                if (doc) {
                    doc.open();
                    doc.write(code);
                    doc.close();
                }
                runBtn.disabled = false;
                runBtn.innerHTML = '\u25B6 Run';
            }, 50);
            return;
        }

        runBtn.innerHTML = '<span class="quest-workspace-spinner"></span> Running...';
        outputBody.style.padding = '1rem';
        outputBody.style.overflow = '';
        outputBody.className = 'quest-workspace-output-body';
        outputBody.textContent = 'Menjalankan kode...';

        fetch('api/run-code.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                language: '<?php echo $editor_mode; ?>',
                code: code
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            runBtn.disabled = false;
            runBtn.innerHTML = '\u25B6 Run';
            outputBody.className = 'quest-workspace-output-body';
            if (data.success === false) {
                outputBody.className = 'quest-workspace-output-body error';
                outputBody.textContent = data.output || 'Error running code';
            } else {
                outputBody.textContent = data.output || '(No output)';
            }
        })
        .catch(function(err) {
            runBtn.disabled = false;
            runBtn.innerHTML = '\u25B6 Run';
            outputBody.className = 'quest-workspace-output-body error';
            outputBody.textContent = 'Error: ' + err.message;
        });
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowRight' || e.key === ' ') {
            e.preventDefault();
            if (currentSlide < totalSlides - 1) nextSlide();
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            if (currentSlide > 0) prevSlide();
        }
    });
})();
</script>
</body>
</html>
