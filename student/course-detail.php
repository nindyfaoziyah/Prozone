<?php
require_once '../config/config.php';
requireLogin();
require_once '../models/Course.php';
require_once '../models/Enrollment.php';
require_once '../models/Lesson.php';

$database = new Database();
$db = $database->getConnection();
$course = new Course($db);
$enrollment = new Enrollment($db);
$lesson = new Lesson($db);

$user_id = (int)($_SESSION['user_id'] ?? 0);
$course_id = (int)($_GET['id'] ?? 0);

$course->id = $course_id;
$course_data = $course->readOne();
if (!$course_data) { header('Location: courses.php'); exit; }

$lessons_stmt = $lesson->readByCourse($course_id);
$all_lessons = [];
while ($row = $lessons_stmt->fetch(PDO::FETCH_ASSOC)) { $all_lessons[] = $row; }

$user_lesson_progress = [];
if ($user_id) {
    $stmt = $db->prepare("SELECT lesson_id, status, skor FROM user_progress WHERE user_id = :uid AND course_id = :cid");
    $stmt->execute([':uid' => $user_id, ':cid' => $course_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $user_lesson_progress[(int)$row['lesson_id']] = $row; }
}

$is_enrolled = $enrollment->isEnrolled($user_id, $course_id);
$enroll_data = null;
if ($is_enrolled && $user_id) {
    $enstmt = $enrollment->getUserEnrollments($user_id);
    while ($row = $enstmt->fetch(PDO::FETCH_ASSOC)) {
        if ((int)$row['course_id'] === $course_id) { $enroll_data = $row; break; }
    }
}

$progress = $enroll_data ? (float)($enroll_data['progress_percent'] ?? 0) : 0;
$total_lessons = count($all_lessons);

$theory_lessons = array_values(array_filter($all_lessons, fn($l) => $l['tipe'] === 'theory'));
$quiz_lesson = null; $practice_lesson = null;
foreach ($all_lessons as $l) {
    if ($l['tipe'] === 'quiz') $quiz_lesson = $l;
    if ($l['tipe'] === 'practice') $practice_lesson = $l;
}

$quiz_questions = $quiz_lesson ? json_decode($quiz_lesson['konten'] ?? '[]', true) : [];
if (!is_array($quiz_questions)) $quiz_questions = [];
$quiz_xp = $quiz_lesson ? (int)$quiz_lesson['xp_reward'] : 25;
$practice_xp = $practice_lesson ? (int)$practice_lesson['xp_reward'] : 30;

$practice_starter = ($practice_lesson && !empty($practice_lesson['kode_contoh'])) ? $practice_lesson['kode_contoh'] : '';
$practice_instructions = $practice_lesson ? ($practice_lesson['konten'] ?? '') : '';

$mission_requirements = [];
if ($practice_lesson && !empty($practice_lesson['instruksi'])) {
    $ch = json_decode($practice_lesson['instruksi'], true);
    if ($ch && isset($ch['kriteria'])) $mission_requirements = $ch['kriteria'];
}

$page_title = $course_data['judul_course'] . ' — Belajar';
$page_css = ['pages/course-detail.css', 'sidebar-island.css', 'dashboard-override.css'];
$body_class = trim(getThemeClass() . ' dashboard-layout');

$logoUrl = '';
$t = strtolower($course_data['judul_course']);
if (strpos($t, 'html') !== false) $logoUrl = 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/html5/html5-original.svg';
if (strpos($t, 'css') !== false) $logoUrl = 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg';
if (strpos($t, 'python') !== false) $logoUrl = 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/python/python-original.svg';
if (strpos($t, 'php') !== false) $logoUrl = 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg';
if (strpos($t, 'javascript') !== false || strpos($t, 'js') !== false) $logoUrl = 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg';
if (strpos($t, 'react') !== false) $logoUrl = 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/react/react-original.svg';
if (strpos($t, 'laravel') !== false) $logoUrl = 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/laravel/laravel-plain.svg';
if (strpos($t, 'java') !== false) $logoUrl = 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/java/java-original.svg';
if (strpos($t, 'mysql') !== false || strpos($t, 'database') !== false) $logoUrl = 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<?php require_once '../includes/head.php'; ?>
<style>
@keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-20px)} }
@keyframes glow { 0%,100%{box-shadow:0 0 5px rgba(79,70,229,.3)} 50%{box-shadow:0 0 20px rgba(79,70,229,.6)} }
@keyframes pulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.05)} }
@keyframes slideUp { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
@keyframes slideIn { from{opacity:0;transform:translateX(40px)} to{opacity:1;transform:translateX(0)} }
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
@keyframes xpFloat { 0%{opacity:1;transform:translateY(0) scale(1)} 100%{opacity:0;transform:translateY(-100px) scale(1.5)} }
@keyframes confetti { 0%{transform:translateY(0) rotate(0deg);opacity:1} 100%{transform:translateY(400px) rotate(720deg);opacity:0} }
@keyframes shake { 0%,100%{transform:translateX(0)} 20%,60%{transform:translateX(-8px)} 40%,80%{transform:translateX(8px)} }
@keyframes bgShift { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
@keyframes sparkle { 0%,100%{opacity:0;transform:scale(0)} 50%{opacity:1;transform:scale(1)} }

:root{--primary:#4F46E5;--suc:#10B981;--gold:#F59E0B;--danger:#EF4444;--bg:#F8FAFC;--card:#FFFFFF;--border:#E2E8F0;--text:#0F172A;--text-m:#64748B;--radius:16px}
*{box-sizing:border-box}
body.dashboard-layout .page-wrapper.dashboard-main-container{background:#F8FAFC!important}
body.dashboard-layout .dashboard-content{max-width:1400px;padding:0}
.game-wrapper *{scrollbar-width:thin}.game-wrapper *::-webkit-scrollbar{width:6px;height:6px;display:block}.game-wrapper *::-webkit-scrollbar-track{background:transparent}.game-wrapper *::-webkit-scrollbar-thumb{background:#C7D2FE;border-radius:3px}.game-wrapper *::-webkit-scrollbar-thumb:hover{background:#A5B4FC}

.game-wrapper{height:100vh;position:relative;background:linear-gradient(-45deg,#EEF2FF,#E0E7FF,#F8FAFC,#E0E7FF);background-size:400% 400%;animation:bgShift 15s ease infinite;display:flex;flex-direction:column}
.game-particles{position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:0}
.game-particle{position:absolute;width:4px;height:4px;background:rgba(99,102,241,.15);border-radius:50%;animation:float 6s ease-in-out infinite}
.game-particle:nth-child(2n){width:6px;height:6px;background:rgba(6,182,212,.1);animation-delay:-2s;animation-duration:8s}
.game-particle:nth-child(3n){width:3px;height:3px;background:rgba(139,92,246,.15);animation-delay:-4s;animation-duration:5s}
.game-particle:nth-child(5n){width:8px;height:8px;background:rgba(16,185,129,.08);animation-delay:-1s;animation-duration:10s}

.game-header{position:relative;z-index:2;padding:20px 32px;display:flex;align-items:center;justify-content:space-between;gap:16px;background:rgba(255,255,255,.75);backdrop-filter:blur(12px);border-bottom:1px solid rgba(226,232,240,.8)}
.game-header-left{display:flex;align-items:center;gap:14px}
.game-logo{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(79,70,229,.1)}
.game-logo img{width:24px;height:24px;object-fit:contain}
.game-title{font-size:.85rem;font-weight:700;color:#0F172A}
.game-title small{display:block;font-size:.6rem;font-weight:500;color:var(--text-m);margin-top:1px}
.game-xp{display:flex;align-items:center;gap:6px;font-size:.72rem;font-weight:700;color:var(--gold);background:rgba(245,158,11,.08);padding:6px 14px;border-radius:999px}
.game-header-progress{flex:1;max-width:300px;margin:0 16px}
.game-header-progress .hp-bar{height:4px;background:rgba(226,232,240,.8);border-radius:4px;overflow:hidden}
.game-header-progress .hp-fill{height:100%;background:linear-gradient(90deg,var(--primary),#8B5CF6);border-radius:4px;transition:width .8s cubic-bezier(.4,0,.2,1);box-shadow:0 0 12px rgba(79,70,229,.3)}
.game-header-progress .hp-label{font-size:.55rem;color:var(--text-m);margin-top:4px;display:flex;justify-content:space-between}

.game-main{position:relative;z-index:2;padding:24px 32px;max-width:1200px;margin:0 auto;flex:1;min-height:0;display:flex;flex-direction:column}
.game-phase{display:none;animation:slideUp .6s cubic-bezier(.16,1,.3,1)}
.game-phase.active{display:flex;flex-direction:column;flex:1;min-height:0}

/* ── PHASE INDICATOR ── */
.game-steps{display:flex;gap:0;margin-bottom:24px;background:rgba(226,232,240,.5);border-radius:var(--radius);padding:4px;backdrop-filter:blur(8px)}
.game-step{flex:1;padding:10px 8px;text-align:center;border-radius:12px;font-size:.65rem;font-weight:700;color:var(--text-m);transition:all .3s;position:relative}
.game-step.active{background:rgba(79,70,229,.12);color:#4F46E5}
.game-step.done{background:rgba(16,185,129,.1);color:var(--suc)}
.game-step .step-icon{display:block;font-size:1rem;margin-bottom:2px}

/* ── MATERI PHASE ── */
.materi-card{background:#FFFFFF;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;backdrop-filter:blur(8px);animation:slideUp .5s cubic-bezier(.16,1,.3,1);display:flex;flex-direction:column;flex:1;min-height:0}
.materi-progress{height:4px;background:rgba(226,232,240,.8)}
.materi-progress-fill{height:100%;background:linear-gradient(90deg,var(--primary),#8B5CF6,#06B6D4);transition:width .6s;box-shadow:0 0 16px rgba(79,70,229,.25)}
.materi-body{padding:32px 36px;overflow-y:auto;flex:1;min-height:0}
.materi-meta{display:flex;gap:16px;flex-wrap:wrap;margin-bottom:16px;font-size:.68rem;color:var(--text-m)}
.materi-meta span{display:flex;align-items:center;gap:4px}
.materi-meta .badge{padding:2px 10px;border-radius:999px;font-size:.55rem;font-weight:700}
.badge-theory{background:rgba(79,70,229,.1);color:#4F46E5}
.materi-title{font-size:1.2rem;font-weight:800;color:#0F172A;margin:0 0 16px;line-height:1.35}
.materi-content{font-size:.88rem;color:#334155;line-height:1.7}
.materi-content p{margin:0 0 12px}
.materi-content ul,.materi-content ol{padding-left:18px;margin:8px 0}
.materi-content li{margin-bottom:4px}
.materi-content code{background:rgba(79,70,229,.08);color:#4F46E5;padding:1px 5px;border-radius:4px;font-size:.82rem}
.materi-content pre{background:#F1F5F9;border-radius:10px;padding:16px;margin:12px 0;font-size:.78rem;color:#1E293B;overflow-x:auto}
.materi-nav{display:flex;align-items:center;justify-content:space-between;padding:14px 24px;border-top:1px solid var(--border);background:#F8FAFC;flex-wrap:wrap;gap:8px;flex-shrink:0}
.materi-nav .m-info{font-size:.68rem;color:var(--text-m)}
.m-btn{padding:10px 24px;border-radius:12px;border:1px solid var(--border);font-size:.75rem;font-weight:700;cursor:pointer;transition:all .25s;font-family:inherit;background:#FFFFFF;color:#334155;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.m-btn:hover{border-color:var(--primary);color:#0F172A;transform:translateY(-1px)}
.m-btn:disabled{opacity:.3;cursor:not-allowed;transform:none}
.m-btn--primary{background:linear-gradient(135deg,var(--primary),#7C3AED);color:#fff;border:none;box-shadow:0 4px 16px rgba(79,70,229,.2)}
.m-btn--primary:hover{transform:translateY(-2px);box-shadow:0 6px 24px rgba(79,70,229,.3)}
.m-btn--suc{background:linear-gradient(135deg,var(--suc),#059669);color:#fff;border:none}
.m-btn--suc:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(16,185,129,.2)}
.m-btn--gold{background:linear-gradient(135deg,var(--gold),#D97706);color:#fff;border:none}
.m-btn--gold:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(245,158,11,.2)}
.m-btn--ghost{background:transparent;border:1.5px solid rgba(99,102,241,.4);color:#4F46E5}
.m-btn--ghost:hover{background:rgba(79,70,229,.06);border-color:var(--primary)}

/* ── QUIZ PHASE ── */
.quiz-card{background:#FFFFFF;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;backdrop-filter:blur(8px);display:flex;flex-direction:column;flex:1;min-height:0}
.quiz-header{padding:20px 28px 12px;border-bottom:1px solid var(--border);flex-shrink:0}
.quiz-header h2{font-size:1rem;font-weight:800;color:#0F172A;margin:0;display:flex;align-items:center;gap:8px}
.quiz-header .q-progress{font-size:.65rem;color:var(--text-m);margin-top:4px}
.quiz-body{padding:20px 28px;min-height:300px;animation:slideUp .4s ease;flex:1;overflow-y:auto}
.quiz-footer{padding:14px 24px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;background:#F8FAFC;flex-shrink:0}

/* ── DRAG & DROP ── */
.drag-zone{display:flex;flex-direction:column;gap:8px;padding:16px;background:#F1F5F9;border-radius:12px;border:2px dashed rgba(99,102,241,.25);min-height:200px}
.drag-item{padding:12px 16px;background:#FFFFFF;border:1px solid var(--border);border-radius:10px;cursor:grab;font-size:.82rem;color:#1E293B;font-family:monospace;transition:all .2s;user-select:none;display:flex;align-items:center;gap:10px}
.drag-item:hover{border-color:var(--primary);background:rgba(79,70,229,.04)}
.drag-item:active{cursor:grabbing;transform:scale(1.02)}
.drag-item .drag-handle{color:#94A3B8;font-size:.6rem}
.drag-item.dragging{opacity:.5;transform:scale(.95)}
.drag-item.dropped{border-color:rgba(16,185,129,.3);background:rgba(16,185,129,.04)}

/* ── FILL BLANK ── */
.fill-zone{background:#F1F5F9;border-radius:12px;padding:20px;font-family:monospace;font-size:.85rem;color:#1E293B;line-height:1.8}
.fill-zone .fill-input{background:rgba(79,70,229,.06);border:2px solid rgba(99,102,241,.35);border-radius:6px;padding:4px 10px;color:#4F46E5;font-family:monospace;font-size:.85rem;outline:none;width:160px;transition:all .2s}
.fill-zone .fill-input:focus{border-color:var(--primary);box-shadow:0 0 12px rgba(79,70,229,.2)}
.fill-zone .fill-input.correct{border-color:var(--suc);background:rgba(16,185,129,.06);color:var(--suc)}
.fill-zone .fill-input.wrong{border-color:var(--danger);background:rgba(239,68,68,.06);color:var(--danger);animation:shake .3s}

/* ── CODE ARRANGE ── */
.arrange-zone{display:flex;flex-direction:column;gap:6px;padding:16px;background:#F1F5F9;border-radius:12px;min-height:150px;border:2px dashed rgba(99,102,241,.25)}
.arrange-item{padding:10px 16px;background:#FFFFFF;border:1px solid var(--border);border-radius:8px;cursor:grab;font-size:.78rem;color:#1E293B;font-family:monospace;transition:all .2s;user-select:none}
.arrange-item:hover{border-color:var(--primary);background:rgba(79,70,229,.04)}
.arrange-item:active{cursor:grabbing;transform:scale(1.02)}

/* ── ERROR DETECT ── */
.error-code{background:#F1F5F9;border-radius:12px;padding:20px;font-family:monospace;font-size:.82rem;color:#1E293B;line-height:1.7;white-space:pre-wrap;position:relative}
.error-code .err-line{padding:2px 8px;border-radius:4px;cursor:pointer;transition:all .15s;display:inline}
.error-code .err-line:hover{background:rgba(239,68,68,.08)}
.error-code .err-line.found{background:rgba(239,68,68,.12);text-decoration:line-through;color:#DC2626}
.error-code .err-line.correct-bug{background:rgba(16,185,129,.12);text-decoration:line-through;color:#16A34A}
.error-code .err-line.false-alarm{background:rgba(239,68,68,.18)}
.error-hint{background:rgba(239,68,68,.06);border-left:3px solid var(--danger);padding:10px 14px;border-radius:0 8px 8px 0;margin-top:12px;font-size:.75rem;color:#DC2626}

/* ── PREDICT OUTPUT ── */
.predict-code{background:#F1F5F9;border-radius:12px;padding:16px 20px;font-family:monospace;font-size:.82rem;color:#1E293B;margin-bottom:12px;border-left:3px solid var(--primary)}
.predict-options{display:flex;flex-direction:column;gap:8px}
.predict-opt{padding:12px 16px;background:#FFFFFF;border:1px solid var(--border);border-radius:10px;cursor:pointer;font-size:.8rem;color:#334155;transition:all .2s;display:flex;align-items:center;gap:10px}
.predict-opt:hover{border-color:var(--primary);background:rgba(79,70,229,.04);color:#0F172A}
.predict-opt input[type="radio"]{accent-color:var(--primary)}
.predict-opt.selected{border-color:var(--primary);background:rgba(79,70,229,.08);color:#4F46E5}
.predict-opt.correct{border-color:var(--suc);background:rgba(16,185,129,.06);color:var(--suc)}
.predict-opt.wrong{border-color:var(--danger);background:rgba(239,68,68,.06);color:#DC2626}

/* ── MATCH PAIR ── */
.match-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.match-col{display:flex;flex-direction:column;gap:8px}
.match-item{padding:12px 14px;background:#FFFFFF;border:1px solid var(--border);border-radius:10px;cursor:pointer;font-size:.78rem;color:#334155;transition:all .2s;text-align:center}
.match-item:hover{border-color:var(--primary);transform:translateY(-1px)}
.match-item.selected{background:rgba(79,70,229,.1);border-color:var(--primary);color:#4F46E5;box-shadow:0 0 12px rgba(79,70,229,.15)}
.match-item.matched{background:rgba(16,185,129,.06);border-color:var(--suc);color:var(--suc);opacity:.7;pointer-events:none}
.match-item.wrong-match{background:rgba(239,68,68,.06);border-color:var(--danger);color:#DC2626;animation:shake .3s}

/* ── SCENARIO ── */
.scenario-box{padding:16px;background:#FFFBEB;border-radius:12px;border-left:3px solid var(--gold);margin-bottom:16px;font-size:.85rem;color:#92400E;line-height:1.6}
.scenario-options{display:flex;flex-direction:column;gap:8px}
.scenario-opt{padding:14px 16px;background:#FFFFFF;border:1px solid var(--border);border-radius:10px;cursor:pointer;font-size:.8rem;color:#334155;transition:all .2s}
.scenario-opt:hover{border-color:var(--gold);background:rgba(245,158,11,.04)}
.scenario-opt input[type="radio"]{accent-color:var(--gold)}
.scenario-opt.selected{border-color:var(--gold);background:rgba(245,158,11,.08);color:#B45309}
.scenario-opt.correct{border-color:var(--suc);background:rgba(16,185,129,.06);color:var(--suc)}
.scenario-opt.wrong{border-color:var(--danger);background:rgba(239,68,68,.06);color:#DC2626}
.scenario-explanation{padding:12px 16px;background:rgba(79,70,229,.05);border-radius:10px;font-size:.75rem;color:#334155;margin-top:12px;border-left:3px solid var(--primary);animation:fadeIn .3s}

/* ── MISSION PHASE ── */
.mission-layout{display:grid;grid-template-columns:320px 1fr;gap:16px}
.mission-sidebar{background:#FFFFFF;border:1px solid var(--border);border-radius:var(--radius);padding:20px;backdrop-filter:blur(8px)}
.mission-sidebar h3{font-size:.85rem;font-weight:800;color:#0F172A;margin:0 0 4px}
.mission-sidebar .ms-desc{font-size:.72rem;color:var(--text-m);margin-bottom:12px}
.mission-checklist{display:flex;flex-direction:column;gap:6px}
.mission-task{display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;background:#F1F5F9;font-size:.72rem;color:#64748B;transition:all .3s}
.mission-task .mt-check{width:18px;height:18px;border-radius:50%;border:2px solid #CBD5E1;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.55rem;transition:all .3s}
.mission-task.done{background:rgba(16,185,129,.05)}
.mission-task.done .mt-check{background:var(--suc);border-color:var(--suc);color:#fff;box-shadow:0 0 10px rgba(16,185,129,.2)}
.mission-task .mt-label{flex:1}

.mission-main{display:flex;flex-direction:column;gap:12px}
.mission-editor{background:#FFFFFF;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;backdrop-filter:blur(8px)}
.mission-editor .me-header{display:flex;align-items:center;justify-content:space-between;padding:8px 16px;background:#F1F5F9;color:var(--text-m);font-size:.65rem;font-weight:600}
.me-header .me-actions{display:flex;gap:6px}
.me-header .me-actions button{padding:4px 12px;border-radius:6px;border:none;font-size:.65rem;font-weight:700;cursor:pointer;font-family:inherit;transition:all .15s}
.btn-run{background:var(--suc);color:#fff}
.btn-run:hover{background:#059669}
.btn-submit{background:linear-gradient(135deg,var(--primary),#7C3AED);color:#fff;box-shadow:0 4px 12px rgba(79,70,229,.15)}
.btn-submit:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(79,70,229,.25)}
.btn-submit:disabled{opacity:.5;cursor:not-allowed;transform:none}
.mission-editor textarea{width:100%;height:300px;border:none;outline:none;resize:none;padding:16px;font-family:'Cascadia Code','Fira Code','Consolas',monospace;font-size:.78rem;line-height:1.6;color:#E2E8F0;background:#0B1120;tab-size:2}
.mission-preview{background:#FFFFFF;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;backdrop-filter:blur(8px)}
.mission-preview .mp-header{display:flex;align-items:center;padding:6px 14px;background:#F1F5F9;color:var(--text-m);font-size:.6rem;font-weight:600;gap:8px}
.mission-preview iframe{width:100%;height:200px;border:none;background:#fff}
.mission-console{background:#0B1120;padding:10px 14px;font-family:monospace;font-size:.72rem;color:#CBD5E1;max-height:80px;overflow-y:auto;border-top:1px solid var(--border)}
.mission-console .console-line{margin:2px 0}
.mission-console .console-line.info{color:#60A5FA}
.mission-console .console-line.warn{color:var(--gold)}
.mission-console .console-line.error{color:#F87171}
.mission-console .console-line.success{color:var(--suc)}

/* ── COMPLETE PHASE ── */
.victory-screen{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 20px;text-align:center;animation:slideUp .6s cubic-bezier(.16,1,.3,1)}
.victory-icon{font-size:4rem;margin-bottom:12px;animation:pulse 1s ease-in-out infinite}
.victory-screen h2{font-size:1.5rem;font-weight:900;color:#0F172A;margin:0 0 4px;background:linear-gradient(135deg,#4F46E5,#06B6D4,#4F46E5);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.victory-screen .vs-sub{font-size:.85rem;color:var(--text-m);margin:0 0 24px}
.vs-rewards{display:flex;gap:20px;justify-content:center;flex-wrap:wrap;margin-bottom:24px}
.vs-reward{background:#FFFFFF;border:1px solid var(--border);border-radius:14px;padding:18px 24px;text-align:center;min-width:110px;animation:slideUp .5s cubic-bezier(.16,1,.3,1) backwards}
.vs-reward:nth-child(1){animation-delay:.1s}
.vs-reward:nth-child(2){animation-delay:.2s}
.vs-reward:nth-child(3){animation-delay:.3s}
.vs-reward .vr-icon{font-size:1.6rem;margin-bottom:4px}
.vs-reward .vr-value{font-size:1.3rem;font-weight:900;color:#0F172A}
.vs-reward .vr-label{font-size:.6rem;color:var(--text-m);text-transform:uppercase;letter-spacing:.04em;font-weight:700}
.vs-achievement{background:linear-gradient(135deg,rgba(245,158,11,.08),rgba(251,191,36,.04));border:1px solid rgba(245,158,11,.2);border-radius:12px;padding:14px 24px;margin-bottom:20px;font-size:.82rem;font-weight:700;color:#B45309;display:inline-flex;align-items:center;gap:8px;animation:glow 1.5s ease-in-out infinite}
.vs-redirect{font-size:.68rem;color:var(--text-m);margin-top:12px}

/* XP float effect */
.xp-particle{position:fixed;pointer-events:none;z-index:9999;font-weight:900;font-size:1.1rem;animation:xpFloat 1.2s cubic-bezier(.4,0,.2,1) forwards}
.xp-particle.xp-gold{color:var(--gold);text-shadow:0 0 10px rgba(245,158,11,.4)}
.xp-particle.xp-green{color:var(--suc);text-shadow:0 0 10px rgba(16,185,129,.4)}
.xp-particle.xp-purple{color:#4F46E5;text-shadow:0 0 10px rgba(79,70,229,.4)}

/* Confetti */
.confetti-piece{position:fixed;pointer-events:none;z-index:9998;width:10px;height:10px;animation:confetti 2s linear forwards}

/* Responsive */
@media(max-width:860px){.mission-layout{grid-template-columns:1fr}.game-header{padding:14px 16px;flex-wrap:wrap}.game-main{padding:16px}.materi-body{padding:20px}.quiz-body{padding:16px 20px}}
@media(max-width:640px){.game-steps{flex-wrap:wrap}.game-step{flex:1 1 50%;font-size:.6rem;padding:8px 4px}}
</style>
</head>
<body class="<?php echo $body_class; ?>">
<div class="game-wrapper">
<div class="game-particles" id="particles"></div>

<div class="game-header">
    <div class="game-header-left">
        <div class="game-logo"><?php if ($logoUrl): ?><img src="<?php echo $logoUrl; ?>" alt=""><?php else: ?>📘<?php endif; ?></div>
        <div class="game-title"><?php echo htmlspecialchars($course_data['judul_course']); ?><small><?php echo htmlspecialchars($course_data['nama_kategori'] ?? 'Course'); ?></small></div>
    </div>
    <div class="game-header-progress">
        <div class="hp-bar"><div class="hp-fill" id="hpFill" style="width:0%"></div></div>
        <div class="hp-label"><span id="hpPhase">Mulai</span><span id="hpPct">0%</span></div>
    </div>
    <div class="game-xp" id="gameXpBadge">⚡ <span id="xpDisplay">0</span> XP</div>
</div>

<div class="game-main">
    <!-- Steps -->
    <div class="game-steps" id="gameSteps">
        <div class="game-step active" data-step="1"><span class="step-icon">📖</span> Materi</div>
        <div class="game-step" data-step="2"><span class="step-icon">🧩</span> Quiz</div>
        <div class="game-step" data-step="3"><span class="step-icon">💻</span> Mission</div>
        <div class="game-step" data-step="4"><span class="step-icon">🏆</span> Selesai</div>
    </div>

    <!-- ── PHASE 1: MATERI ── -->
    <div class="game-phase active" id="phaseMateri">
        <div class="materi-card">
            <div class="materi-progress"><div class="materi-progress-fill" id="materiFill" style="width:0%"></div></div>
            <div class="materi-body" id="materiBody">
                <div class="materi-meta">
                    <span>📚 <span id="materiNum">1/<?php echo count($theory_lessons); ?></span></span>
                    <span>⚡ +10 XP</span>
                    <span class="badge badge-theory">📖 Teori</span>
                </div>
                <h2 class="materi-title" id="materiTitle"></h2>
                <div class="materi-content" id="materiContent"></div>
            </div>
            <div class="materi-nav">
                <span class="m-info" id="materiInfo"></span>
                <div class="m-nav-btns">
                    <button class="m-btn" id="materiPrev" disabled>← Sebelumnya</button>
                    <button class="m-btn m-btn--primary" id="materiNext">Selanjutnya →</button>
                    <button class="m-btn m-btn--gold" id="materiDone" style="display:none">🔥 Lanjut ke Quiz</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── PHASE 2: QUIZ ── -->
    <div class="game-phase" id="phaseQuiz">
        <div class="quiz-card">
            <div class="quiz-header">
                <h2>🧩 Challenge Quiz</h2>
                <div class="q-progress">Soal <span id="quizProgress">1/<?php echo count($quiz_questions); ?></span></div>
            </div>
            <div class="quiz-body" id="quizBody"></div>
            <div class="quiz-footer">
                <span style="font-size:.72rem;color:var(--text-m)" id="quizStatus">Siap menjawab?</span>
                <div>
                    <button class="m-btn m-btn--ghost" id="quizPrev" style="display:none">← Sebelumnya</button>
                    <button class="m-btn m-btn--primary" id="quizNext">Selanjutnya →</button>
                    <button class="m-btn m-btn--gold" id="quizSubmit" style="display:none">📨 Submit Quiz</button>
                    <button class="m-btn m-btn--suc" id="quizToMission" style="display:none">🚀 Lanjut ke Mission</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── PHASE 3: MISSION ── -->
    <div class="game-phase" id="phaseMission">
        <div class="mission-layout">
            <div class="mission-sidebar">
                <h3>🎯 Mission Objective</h3>
                <p class="ms-desc"><?php echo htmlspecialchars(mb_substr(strip_tags($practice_instructions), 0, 200)); ?></p>
                <div class="mission-checklist" id="checklist"></div>
            </div>
            <div class="mission-main">
                <div class="mission-editor">
                    <div class="me-header">
                        <span>📄 index.html</span>
                        <span class="me-actions">
                            <button class="btn-run" id="missionRun">▶ Run</button>
                            <button class="btn-submit" id="missionSubmit">📨 Submit Mission</button>
                        </span>
                    </div>
                    <textarea id="missionEditor" spellcheck="false"><?php echo htmlspecialchars($practice_starter); ?></textarea>
                </div>
                <div class="mission-preview">
                    <div class="mp-header">🔍 Preview</div>
                    <iframe id="missionPreview" sandbox="allow-scripts allow-modals"></iframe>
                    <div class="mission-console" id="missionConsole"><div class="console-line info">● Menunggu eksekusi... klik "Run" untuk menjalankan kode.</div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── PHASE 4: COMPLETE ── -->
    <div class="game-phase" id="phaseComplete">
        <div class="victory-screen">
            <div class="victory-icon">🏆</div>
            <h2>Course Completed!</h2>
            <p class="vs-sub">Kamu telah menyelesaikan <?php echo htmlspecialchars($course_data['judul_course']); ?> dengan sempurna!</p>
            <div class="vs-rewards" id="victoryRewards">
                <div class="vs-reward"><div class="vr-icon">⚡</div><div class="vr-value" id="vXp">+0</div><div class="vr-label">XP</div></div>
                <div class="vs-reward"><div class="vr-icon">🪙</div><div class="vr-value" id="vCoins">+0</div><div class="vr-label">Koin</div></div>
                <div class="vs-reward"><div class="vr-icon">📜</div><div class="vr-value" id="vLessons">0</div><div class="vr-label">Materi</div></div>
            </div>
            <div class="vs-achievement" id="vAchievement" style="display:none">🏅 <span id="vAchName"></span></div>
            <a href="courses.php" class="m-btn m-btn--suc" style="font-size:.9rem;padding:12px 32px">📚 Kembali ke Courses</a>
            <div class="vs-redirect" id="vRedirect">Mengalihkan ke Courses dalam <strong id="countdown">5</strong> detik...</div>
        </div>
    </div>
</div>
</div>

<?php include '../includes/toast.php'; ?>
<script id="game-script">
(function(){
function es(s){var d=document.createElement('div');d.appendChild(document.createTextNode(s));return d.innerHTML;}

/* ── DATA ── */
var theoryData = <?php echo json_encode(array_map(function($l){return['id'=>(int)$l['id'],'judul'=>$l['judul_lesson'],'konten'=>$l['konten']??''];}, $theory_lessons)); ?>;
var quizData = <?php echo json_encode($quiz_questions); ?>;
var missionReqs = <?php echo json_encode($mission_requirements); ?>;
var qLessonId = <?php echo $quiz_lesson ? (int)$quiz_lesson['id'] : 0; ?>;
var pLessonId = <?php echo $practice_lesson ? (int)$practice_lesson['id'] : 0; ?>;
var cId = <?php echo $course_id; ?>;
var uId = <?php echo $user_id; ?>;
var totalMateri = theoryData.length;
var totalQuiz = quizData.length;
var totalLessons = <?php echo $total_lessons; ?>;

/* ── STATE ── */
var S = { phase:0, materiIdx:0, quizIdx:0, quizDone:false, quizPassed:false, missionDone:false, missionChecks:{}, totalXp:0 };

/* ── DOM ── */
var $ = function(id){return document.getElementById(id);};
var steps = document.querySelectorAll('.game-step');

/* ── PARTICLES ── */
(function(){
    var c = $('particles');
    for(var i=0;i<30;i++){
        var p = document.createElement('div');
        p.className = 'game-particle';
        p.style.left = Math.random()*100+'%';
        p.style.top = Math.random()*100+'%';
        p.style.animationDelay = (Math.random()*6)+'s';
        p.style.animationDuration = (4+Math.random()*6)+'s';
        c.appendChild(p);
    }
})();

/* ── XP FLOAT EFFECT ── */
function showXp(amount, cls){
    var el = document.createElement('div');
    el.className = 'xp-particle xp-'+cls;
    el.textContent = '+'+amount+' XP';
    el.style.left = (40+Math.random()*20)+'%';
    el.style.top = '50%';
    document.body.appendChild(el);
    setTimeout(function(){el.remove();}, 1200);
}

/* ── CONFETTI ── */
function burstConfetti(){
    var colors = ['#4F46E5','#10B981','#F59E0B','#EF4444','#06B6D4','#8B5CF6'];
    for(var i=0;i<30;i++){
        var c = document.createElement('div');
        c.className = 'confetti-piece';
        c.style.left = Math.random()*100+'%';
        c.style.top = '-10px';
        c.style.background = colors[Math.floor(Math.random()*colors.length)];
        c.style.borderRadius = Math.random()>.5 ? '50%' : '2px';
        c.style.animationDelay = (Math.random()*0.5)+'s';
        c.style.animationDuration = (1.5+Math.random()*1)+'s';
        document.body.appendChild(c);
        setTimeout(function(){c.remove();}, 3000);
    }
}

/* ── PHASE SWITCH ── */
function goPhase(n){
    S.phase = n;
    document.querySelectorAll('.game-phase').forEach(function(el,i){
        el.classList.toggle('active', i===n);
    });
    steps.forEach(function(el,i){
        el.classList.remove('active','done');
        if(i<n) el.classList.add('done');
        else if(i===n) el.classList.add('active');
    });
    updateProgress();
    if(n===0) renderMateri(S.materiIdx);
    if(n===1) renderQuiz();
    if(n===2) { renderMission(); updatePreview(); }
}

function updateProgress(){
    var pct = 0;
    var lbl = 'Mulai';
    if(S.phase===0){ pct = Math.round((S.materiIdx+1)/totalMateri*25); lbl = 'Materi '+(S.materiIdx+1); }
    if(S.phase===1){ pct = 25; lbl = 'Quiz'; }
    if(S.quizPassed){ pct = 50; lbl = 'Quiz Lulus'; }
    if(S.phase===2){ pct = 50; lbl = 'Mission'; }
    var done = Object.keys(S.missionChecks).length;
    var total = missionReqs.length;
    if(total>0 && S.phase===2) { pct = 50 + Math.round(done/total*25); lbl = done+'/'+total+' task'; }
    if(S.missionDone){ pct = 75; lbl = 'Mission Selesai'; }
    if(S.phase===3){ pct = 100; lbl = 'Selesai!'; }
    $('hpFill').style.width = pct+'%';
    $('hpPct').textContent = pct+'%';
    $('hpPhase').textContent = lbl;
}

/* ── MATERI PHASE ── */
function renderMateri(idx){
    var l = theoryData[idx];
    if(!l) return;
    var body = $('materiBody');
    body.style.animation = 'none';
    void body.offsetWidth;
    body.style.animation = 'slideUp .4s cubic-bezier(.16,1,.3,1)';
    $('materiNum').textContent = (idx+1)+'/'+totalMateri;
    $('materiTitle').textContent = l.judul;
    $('materiContent').innerHTML = l.konten || '<p style="color:var(--text-m)">Belum ada konten.</p>';
    $('materiFill').style.width = ((idx+1)/totalMateri*100)+'%';
    $('materiInfo').textContent = (idx+1)+'/'+totalMateri+' • '+l.judul;
    $('materiPrev').disabled = idx===0;
    var last = idx===totalMateri-1;
    $('materiNext').style.display = last?'none':'';
    $('materiDone').style.display = last?'':'none';
    updateProgress();
    if(uId){
        var x=new XMLHttpRequest();
        x.open('POST','../api/track-lesson-progress.php',true);
        x.setRequestHeader('Content-Type','application/json');
        x.send(JSON.stringify({course_id:cId,lesson_id:l.id,status:'in_progress'}));
    }
}
$('materiPrev').addEventListener('click',function(){if(S.materiIdx>0){S.materiIdx--;renderMateri(S.materiIdx);}});
$('materiNext').addEventListener('click',function(){if(S.materiIdx<totalMateri-1){S.materiIdx++;renderMateri(S.materiIdx);}});
$('materiDone').addEventListener('click',function(){goPhase(1);});

/* ── QUIZ ── */
var quizState = {answers:{}, submitted:false, passed:false, score:0, total:0};

function renderQuiz(){
    var idx = S.quizIdx;
    var q = quizData[idx];
    if(!q) return;
    $('quizBody').style.animation='none';void $('quizBody').offsetWidth;$('quizBody').style.animation='slideIn .3s ease';
    $('quizProgress').textContent = (idx+1)+'/'+totalQuiz;
    $('quizPrev').style.display = idx>0?'':'none';
    $('quizNext').style.display = 'none';
    $('quizSubmit').style.display = 'none';
    $('quizToMission').style.display = 'none';

    if(idx===totalQuiz-1){
        // Last question or submitted state
        if(quizState.submitted){
            if(quizState.passed){
                $('quizStatus').textContent = '🎉 Lulus! Skor: '+quizState.score+'/'+quizState.total;
                $('quizToMission').style.display = '';
            } else {
                $('quizStatus').textContent = '😅 Skor: '+quizState.score+'/'+quizState.total+'. Coba lagi!';
                $('quizSubmit').style.display = '';
                $('quizSubmit').textContent = '🔄 Coba Lagi';
            }
            renderQuestion(q, idx, true);
            return;
        }
        $('quizSubmit').style.display = '';
        $('quizSubmit').textContent = '📨 Submit Quiz';
    } else {
        $('quizNext').style.display = '';
    }
    renderQuestion(q, idx, false);
    $('quizStatus').textContent = 'Jawab soal ini dengan tepat!';
}

function renderQuestion(q, idx, showResult){
    var html = '';
    var a = quizState.answers[idx];

    // ── DRAG & DROP ──
    if(q.type==='drag-drop'){
        var order = a || Array.from({length:q.items.length},(_,i)=>i);
        if(showResult) order = q.correctOrder;
        html += '<div class="fade-zone"><p style="margin-bottom:12px;color:#CBD5E1;font-size:.85rem">'+es(q.question)+'</p>';
        html += '<div class="drag-zone" data-q="'+idx+'">';
        order.forEach(function(oi,i){
            var draggable = showResult ? 'false' : 'true';
            html += '<div class="drag-item" draggable="'+draggable+'" data-oi="'+oi+'" data-pos="'+i+'"><span class="drag-handle">⠿</span> '+es(q.items[oi])+'</div>';
        });
        html += '</div></div>';
        $('quizBody').innerHTML = html;
        if(!showResult) setupDragSort(idx);
        return;
    }

    // ── FILL BLANK ──
    if(q.type==='fill-blank'){
        var val = a || '';
        var code = q.code.replace(q.blank, '<input class="fill-input" id="fillInput" value="'+es(val)+'" placeholder="isi jawaban..." autocomplete="off">');
        var cls = '';
        if(showResult && q.answer){
            var isCorrect = q.answer.some(function(ans){return ans.toLowerCase()===val.toLowerCase();});
            cls = isCorrect ? 'correct' : 'wrong';
        }
        if(showResult && cls) code = code.replace('fill-input"', 'fill-input '+cls+'"');
        html += '<p style="margin-bottom:12px;color:#CBD5E1;font-size:.85rem">'+es(q.question)+'</p>';
        html += '<div class="fill-zone">'+code+'</div>';
        $('quizBody').innerHTML = html;
        var inp = $('fillInput');
        if(inp){
            inp.focus();
            inp.addEventListener('input',function(){
                quizState.answers[idx] = this.value;
            });
        }
        return;
    }

    // ── CODE ARRANGE ──
    if(q.type==='code-arrange'){
        var order2 = a || Array.from({length:q.blocks.length},(_,i)=>i);
        if(showResult) order2 = q.correctOrder;
        html += '<p style="margin-bottom:12px;color:#CBD5E1;font-size:.85rem">'+es(q.question)+'</p>';
        html += '<div class="arrange-zone" data-q="'+idx+'">';
        order2.forEach(function(oi,i){
            var draggable = showResult ? 'false' : 'true';
            html += '<div class="arrange-item" draggable="'+draggable+'" data-oi="'+oi+'" data-pos="'+i+'">'+(i+1)+'. '+es(q.blocks[oi])+'</div>';
        });
        html += '</div></div>';
        $('quizBody').innerHTML = html;
        if(!showResult) setupArrangeSort(idx);
        return;
    }

    // ── ERROR DETECT ──
    if(q.type==='error-detect'){
        var found = a || [];
        var lines = q.code.split('\n');
        var bugLines = (q.bugs||[]).map(function(b){return b.line;});
        html += '<p style="margin-bottom:12px;color:#CBD5E1;font-size:.85rem">'+es(q.question)+'</p>';
        html += '<div class="error-code">';
        lines.forEach(function(line, li){
            var isFound = found.indexOf(li) !== -1;
            var isBug = bugLines.indexOf(li) !== -1;
            var cls = 'err-line';
            if(showResult && isBug) cls += ' found correct-bug';
            else if(showResult && isFound && !isBug) cls += ' found false-alarm';
            else if(isFound) cls += ' found';
            html += '<div class="'+cls+'" data-line="'+li+'">'+(li+1)+'  '+es(line)+'</div>';
        });
        html += '</div>';
        if(showResult){
            var foundCount = bugLines.filter(function(bl){return found.indexOf(bl)!==-1;}).length;
            html += '<div class="error-hint">';
            if(foundCount >= bugLines.length) html += '✅ Semua '+bugLines.length+' bug ditemukan!';
            else html += '😅 '+foundCount+'/'+bugLines.length+' bug ditemukan.';
            html += '</div>';
            // Show fix hints for actual bugs
            (q.bugs||[]).forEach(function(b){
                var userFound = found.indexOf(b.line) !== -1;
                html += '<div class="error-hint" style="'+(userFound?'border-left-color:var(--suc);background:rgba(16,185,129,.06)':'border-left-color:var(--danger)')+'">';
                html += (userFound?'✅ ':'🔍 ')+'Baris '+(b.line+1)+': '+es(b.fixHint)+'</div>';
            });
        } else {
            html += '<div class="error-hint" id="errHint">🔍 Klik pada baris yang memiliki bug ('+found.length+' ditemukan)</div>';
        }
        $('quizBody').innerHTML = html;
        if(!showResult){
            $('quizBody').querySelectorAll('.err-line').forEach(function(el){
                el.addEventListener('click',function(){
                    var line = parseInt(this.dataset.line);
                    var arr = quizState.answers[idx] || [];
                    var pos = arr.indexOf(line);
                    if(pos === -1) { arr.push(line); this.classList.add('found'); }
                    else { arr.splice(pos,1); this.classList.remove('found'); }
                    quizState.answers[idx] = arr;
                    var h = document.getElementById('errHint');
                    if(h) h.textContent = '🔍 Klik pada baris yang memiliki bug ('+arr.length+' ditemukan)';
                });
            });
        }
        return;
    }

    // ── PREDICT OUTPUT ──
    if(q.type==='predict-output'){
        html += '<p style="margin-bottom:12px;color:#CBD5E1;font-size:.85rem">'+es(q.question)+'</p>';
        html += '<div class="predict-code">'+es(q.code)+'</div>';
        html += '<div class="predict-options">';
        q.options.forEach(function(opt, oi){
            var sel = a===oi ? ' selected' : '';
            var extra = '';
            if(showResult && oi===q.correct) extra = ' correct';
            else if(showResult && a===oi && oi!==q.correct) extra = ' wrong';
            html += '<label class="predict-opt'+sel+extra+'">';
            html += '<input type="radio" name="qpred" value="'+oi+'" '+(a===oi?'checked':'')+' '+(showResult?'disabled':'')+'>';
            html += es(opt);
            html += '</label>';
        });
        html += '</div>';
        $('quizBody').innerHTML = html;
        if(!showResult){
            $('quizBody').querySelectorAll('input[name="qpred"]').forEach(function(inp){
                inp.addEventListener('change',function(){
                    quizState.answers[idx] = parseInt(this.value);
                    $('quizBody').querySelectorAll('.predict-opt').forEach(function(el){el.classList.remove('selected');});
                    this.closest('.predict-opt').classList.add('selected');
                });
            });
        }
        return;
    }

    // ── MATCH PAIR ──
    if(q.type==='match-pair'){
        var pairs = a || [];
        var leftSelected = null;
        if(showResult) pairs = q.pairs.map(function(p){return p;});
        html += '<p style="margin-bottom:12px;color:#CBD5E1;font-size:.85rem">'+es(q.question)+'</p>';
        html += '<div class="match-grid">';
        html += '<div class="match-col" id="matchLeft">';
        q.left.forEach(function(item, li){
            var paired = pairs.some(function(p){return p[0]===li;});
            var cls = paired ? 'match-item matched' : (leftSelected===li ? 'match-item selected' : 'match-item');
            html += '<div class="'+cls+'" data-side="left" data-idx="'+li+'">'+es(item)+'</div>';
        });
        html += '</div><div class="match-col" id="matchRight">';
        q.right.forEach(function(item, ri){
            var paired = pairs.some(function(p){return p[1]===ri;});
            var cls = paired ? 'match-item matched' : 'match-item';
            html += '<div class="'+cls+'" data-side="right" data-idx="'+ri+'">'+es(item)+'</div>';
        });
        html += '</div></div>';
        $('quizBody').innerHTML = html;
        if(!showResult) setupMatchPairs(idx, q);
        return;
    }

    // ── SCENARIO ──
    if(q.type==='scenario'){
        html += '<div class="scenario-box">💡 '+es(q.question)+'<br><br><strong>Kasus:</strong> '+es(q.scenario)+'</div>';
        html += '<div class="scenario-options">';
        q.options.forEach(function(opt, oi){
            var sel = a===oi ? ' selected' : '';
            var extra = '';
            if(showResult && oi===q.correct) extra = ' correct';
            else if(showResult && a===oi && oi!==q.correct) extra = ' wrong';
            html += '<label class="scenario-opt'+sel+extra+'">';
            html += '<input type="radio" name="qscenario" value="'+oi+'" '+(a===oi?'checked':'')+' '+(showResult?'disabled':'')+'>';
            html += es(opt);
            html += '</label>';
        });
        html += '</div>';
        if(showResult && q.explanation){
            html += '<div class="scenario-explanation">💡 '+es(q.explanation)+'</div>';
        }
        $('quizBody').innerHTML = html;
        if(!showResult){
            $('quizBody').querySelectorAll('input[name="qscenario"]').forEach(function(inp){
                inp.addEventListener('change',function(){
                    quizState.answers[idx] = parseInt(this.value);
                    $('quizBody').querySelectorAll('.scenario-opt').forEach(function(el){el.classList.remove('selected');});
                    this.closest('.scenario-opt').classList.add('selected');
                });
            });
        }
        return;
    }

    // Fallback
    html = '<p>Unknown question type: '+q.type+'</p>';
    $('quizBody').innerHTML = html;
}

// ── DRAG SORT ──
function setupDragSort(idx){
    var zone = document.querySelector('.drag-zone');
    if(!zone) return;
    var dragged = null;
    zone.addEventListener('dragstart',function(e){
        var item = e.target.closest('.drag-item');
        if(!item) return;
        dragged = item;
        item.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    });
    zone.addEventListener('dragend',function(e){
        var item = e.target.closest('.drag-item');
        if(item) item.classList.remove('dragging');
    });
    zone.addEventListener('dragover',function(e){
        e.preventDefault();
        var target = e.target.closest('.drag-item');
        if(!target || target===dragged) return;
        var rect = target.getBoundingClientRect();
        var mid = rect.top + rect.height/2;
        if(e.clientY < mid) zone.insertBefore(dragged, target);
        else zone.insertBefore(dragged, target.nextSibling);
    });
    zone.addEventListener('drop',function(e){
        e.preventDefault();
        var items = zone.querySelectorAll('.drag-item');
        var order = Array.from(items).map(function(item){return parseInt(item.dataset.oi);});
        quizState.answers[idx] = order;
    });
}

function setupArrangeSort(idx){
    var zone = document.querySelector('.arrange-zone');
    if(!zone) return;
    var dragged = null;
    zone.addEventListener('dragstart',function(e){
        var item = e.target.closest('.arrange-item');
        if(!item) return;
        dragged = item;
        item.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    });
    zone.addEventListener('dragend',function(e){
        var item = e.target.closest('.arrange-item');
        if(item) item.classList.remove('dragging');
    });
    zone.addEventListener('dragover',function(e){
        e.preventDefault();
        var target = e.target.closest('.arrange-item');
        if(!target || target===dragged) return;
        var rect = target.getBoundingClientRect();
        var mid = rect.top + rect.height/2;
        if(e.clientY < mid) zone.insertBefore(dragged, target);
        else zone.insertBefore(dragged, target.nextSibling);
    });
    zone.addEventListener('drop',function(e){
        e.preventDefault();
        var items = zone.querySelectorAll('.arrange-item');
        var order = Array.from(items).map(function(item){return parseInt(item.dataset.oi);});
        quizState.answers[idx] = order;
    });
}

function setupMatchPairs(idx, q){
    var leftSel = null, rightSel = null;
    var leftCol = document.getElementById('matchLeft');
    var rightCol = document.getElementById('matchRight');
    if(!leftCol||!rightCol) return;
    var pairs = quizState.answers[idx] || [];

    function updateMatchUI(){
        leftCol.querySelectorAll('.match-item').forEach(function(el){
            var li = parseInt(el.dataset.idx);
            var isPaired = pairs.some(function(p){return p[0]===li;});
            el.className = isPaired ? 'match-item matched' : (leftSel===li ? 'match-item selected' : 'match-item');
        });
        rightCol.querySelectorAll('.match-item').forEach(function(el){
            var ri = parseInt(el.dataset.idx);
            var isPaired = pairs.some(function(p){return p[1]===ri;});
            el.className = isPaired ? 'match-item matched' : 'match-item';
        });
    }

    leftCol.addEventListener('click',function(e){
        var item = e.target.closest('.match-item');
        if(!item || item.classList.contains('matched')) return;
        var li = parseInt(item.dataset.idx);
        var isPaired = pairs.some(function(p){return p[0]===li;});
        if(isPaired) return;
        leftSel = li;
        leftCol.querySelectorAll('.match-item').forEach(function(el){el.classList.remove('selected');});
        item.classList.add('selected');
    });

    rightCol.addEventListener('click',function(e){
        var item = e.target.closest('.match-item');
        if(!item || item.classList.contains('matched')) return;
        var ri = parseInt(item.dataset.idx);
        var isPaired = pairs.some(function(p){return p[1]===ri;});
        if(isPaired || leftSel===null) return;
        pairs.push([leftSel, ri]);
        quizState.answers[idx] = pairs;
        leftSel = null;
        updateMatchUI();
    });
}

// ── QUIZ NAV ──
$('quizPrev').addEventListener('click',function(){
    if(S.quizIdx>0){S.quizIdx--;renderQuiz();}
});
$('quizNext').addEventListener('click',function(){
    if(S.quizIdx<totalQuiz-1){S.quizIdx++;renderQuiz();}
});
$('quizSubmit').addEventListener('click',function(){
    if(quizState.submitted && !quizState.passed){
        // Retry
        quizState = {answers:{}, submitted:false, passed:false, score:0, total:0};
        S.quizIdx = 0;
        renderQuiz();
        return;
    }
    // Submit all
    var answered = Object.keys(quizState.answers).length;
    if(answered < totalQuiz){
        $('quizStatus').textContent = '⚠️ Jawab semua soal dulu! ('+answered+'/'+totalQuiz+')';
        return;
    }
    // Grade
    var correct = 0;
    quizData.forEach(function(q,i){
        var ans = quizState.answers[i];
        if(q.type==='predict-output'||q.type==='scenario'){
            if(ans !== undefined && ans === q.correct) correct++;
        } else if(q.type==='fill-blank'){
            if(ans && q.answer && q.answer.some(function(a){return a.toLowerCase()===ans.toLowerCase();})) correct++;
        } else if(q.type==='drag-drop'||q.type==='code-arrange'){
            if(ans && JSON.stringify(ans)===JSON.stringify(q.correctOrder)) correct++;
        } else if(q.type==='error-detect'){
            if(ans && q.bugs && q.bugs.every(function(b){return ans.indexOf(b.line)!==-1;})) correct++;
        } else if(q.type==='match-pair'){
            if(ans && JSON.stringify(ans)===JSON.stringify(q.pairs)) correct++;
        }
    });
    quizState.score = correct;
    quizState.total = totalQuiz;
    quizState.passed = correct >= Math.ceil(totalQuiz * 0.7);
    quizState.submitted = true;

    if(quizState.passed){
        var xp = 25 + totalQuiz * 3;
        showXp(xp, 'purple');
        S.totalXp += xp;
        updateXpDisplay();
        $('quizStatus').textContent = '🎉 Lulus! '+correct+'/'+totalQuiz+' benar! +'+xp+' XP';
        // Save to API
        var x = new XMLHttpRequest();
        x.open('POST','../api/complete-phase.php',true);
        x.setRequestHeader('Content-Type','application/json');
        x.onreadystatechange = function(){
            if(x.readyState===4 && x.status!==200){
                console.warn('Quiz progress save failed:', x.responseText);
            }
        };
        x.send(JSON.stringify({course_id:cId, lesson_id:qLessonId, phase:'quiz', xp_reward:xp}));
        $('quizToMission').style.display = '';
        $('quizSubmit').style.display = 'none';
        renderQuiz();
    } else {
        $('quizStatus').textContent = '😅 '+correct+'/'+totalQuiz+' benar. Minimal 70% untuk lulus!';
        renderQuiz();
    }
});

$('quizToMission').addEventListener('click',function(){goPhase(2);});

/* ── MISSION ── */
var missionState = {checks:{}};
var editor = $('missionEditor');
var preview = $('missionPreview');

function renderMission(){
    var html = '';
    missionReqs.forEach(function(req,i){
        var done = missionState.checks[i] || false;
        html += '<div class="mission-task'+(done?' done':'')+'" data-req="'+i+'">';
        html += '<div class="mt-check">'+(done?'✓':'')+'</div>';
        html += '<span class="mt-label">'+es(req)+'</span></div>';
    });
    $('checklist').innerHTML = html;
}

function validateMission(){
    var code = editor.value;
    var checks = {};
    missionReqs.forEach(function(req,i){
        var rl = req.toLowerCase();
        var predicates = [];
        // HTML & attribute checks
        if(rl.includes('input') && rl.includes('email')) predicates.push(code.indexOf('type="email"') !== -1 || code.indexOf("type='email'") !== -1);
        if(rl.includes('input') && rl.includes('password')) predicates.push(code.indexOf('type="password"') !== -1 || code.indexOf("type='password'") !== -1);
        if(rl.includes('tombol') || rl.includes('button') || rl.includes('submit')) predicates.push(code.indexOf('<button') !== -1 || code.indexOf('type="submit"') !== -1 || code.indexOf("type='submit'") !== -1);
        if(rl.includes('form')) predicates.push(code.indexOf('<form') !== -1);
        if(rl.includes('label')) predicates.push(code.indexOf('<label') !== -1);
        if(rl.includes('table')) predicates.push(/<table/.test(code));
        if(rl.includes('image') || rl.includes('gambar')) predicates.push(/<img/.test(code));
        if(rl.includes('link') || rl.includes('anchor')) predicates.push(code.indexOf('href=') !== -1);
        // ul/ol with word boundaries to avoid false matches (e.g. "populasi", "simulasi")
        if(rl.includes('list') || (/\b(ul|ol)\b/.test(rl))) predicates.push(/<(ul|ol)>/.test(code));
        // Semantic HTML: check each mentioned tag independently
        if(rl.includes('navigation') || rl.includes('nav')) predicates.push(/<nav/.test(code));
        if(rl.includes('footer')) predicates.push(/<footer/.test(code));
        if(rl.includes('header') && !rl.includes('heading')) predicates.push(/<header/.test(code));
        if(rl.includes('section')) predicates.push(/<section/.test(code));
        // Heading tags (h1-h6) — match anywhere in code
        if(rl.includes('heading') || rl.includes('judul') || /\bh[1-6]\b/.test(rl)) predicates.push(/<h[1-6]/.test(code));
        // CSS layout
        if(rl.includes('flexbox') || rl.includes('flex')) predicates.push(/display\s*:\s*flex/.test(code));
        if(rl.includes('grid') && (rl.includes('layout') || rl.includes('css') || rl.includes('grid'))) predicates.push(/display\s*:\s*grid/.test(code) || /grid-template/.test(code));
        if(rl.includes('responsive')) predicates.push(/@media/.test(code) || /min-width/.test(code) || /max-width/.test(code));
        if(rl.includes('animasi') || rl.includes('animation') || rl.includes('transisi') || rl.includes('transition')) predicates.push(/@keyframes/.test(code) || /animation/.test(code) || /transition/.test(code));
        // JavaScript
        if(rl.includes('fungsi') || (rl.includes('function') && !rl.includes('fungsi'))) predicates.push(/function\s*\(/.test(code) || /\w+\s*=\s*\(/.test(code) || /=>/.test(code) || /def \w+\s*\(/.test(code));
        if(rl.includes('variable') || rl.includes('variabel')) predicates.push(/(const |let |var |int |String |boolean |float )/.test(code));
        if(rl.includes('perulangan') || rl.includes('loop')) predicates.push(/(for\s*\(|while\s*\(|forEach|\.map\(|\.filter\()/.test(code));
        if(rl.includes('kondisi') || (rl.includes('if') && rl.length < 10)) predicates.push(/if\s*\(/.test(code));
        if(rl.includes('array')) predicates.push(/\[/.test(code));
        if(rl.includes('object')) predicates.push(/\{[\s\S]*\}/.test(code) && /\w+\s*:/.test(code));
        // Python / Data Science
        if(rl.includes('import')) predicates.push(/import /.test(code) || /from /.test(code));
        if(rl.includes('class') && !rl.includes('classic') && !rl.includes('classification')) predicates.push(/class\s+\w+/.test(code));
        if(rl.includes('visual') || rl.includes('chart') || rl.includes('plot') || rl.includes('grafik')) predicates.push(/plt\./.test(code) || /plot\(/.test(code) || /\.(hist|scatter|bar|pie)\s*\(/.test(code) || /chart/.test(code));
        // PHP / Laravel
        if(rl.includes('database') || rl.includes('query') || rl.includes('sql')) predicates.push(/SELECT /.test(code) || /query\(/.test(code) || /->query/.test(code) || /execute\(/.test(code));
        if(rl.includes('route') || rl.includes('endpoint') || rl.includes('api')) predicates.push(/Route::/.test(code) || /app\.(get|post|put|delete)/.test(code) || /router\./.test(code));
        // Error handling
        if(rl.includes('error') || rl.includes('exception') || rl.includes('try')) predicates.push(/try\s*\{/.test(code) || /catch\s*\(/.test(code) || /except/.test(code) || /throw/.test(code));
        // Fallback: if nothing matched, check code is non-trivial
        if(predicates.length === 0) predicates.push(code.length > 50);
        checks[i] = predicates.every(function(p){return p;});
    });
    missionState.checks = checks;
    renderMission();
    updateProgress();
    return Object.values(checks).every(function(v){return v;});
}

function updatePreview(){
    var code = editor.value;
    var doc = preview.contentDocument || preview.contentWindow.document;
    doc.open();
    if(code.includes('<html') || code.includes('<!DOCTYPE')) doc.write(code);
    else doc.write('<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"></head><body>'+code+'</body></html>');
    doc.close();
}

function addConsole(msg, type){
    var c = $('missionConsole');
    c.innerHTML += '<div class="console-line '+type+'">● '+es(msg)+'</div>';
    c.scrollTop = c.scrollHeight;
}

$('missionRun').addEventListener('click',function(){
    addConsole('Menjalankan kode...', 'info');
    updatePreview();
    var allDone = validateMission();
    var done = Object.values(missionState.checks).filter(function(v){return v;}).length;
    var total = missionReqs.length;
    if(done > 0) addConsole(done+'/'+total+' task terpenuhi', 'success');
    if(allDone){ addConsole('Semua task terpenuhi! Submit mission untuk menyelesaikan!', 'success'); }
    else { addConsole('Ada task yang belum terpenuhi. Cek checklist.', 'warn'); }
});

editor.addEventListener('input',function(){
    validateMission();
    clearTimeout(window.previewTimer);
    window.previewTimer = setTimeout(updatePreview, 1000);
});

$('missionSubmit').addEventListener('click',function(){
    var allDone = validateMission();
    if(!allDone){
        addConsole('❌ Belum semua task terpenuhi!', 'error');
        return;
    }
    addConsole('✅ Mission submitted! Menyimpan progress...', 'success');
    // Save practice completion
    var x = new XMLHttpRequest();
    x.open('POST','../api/complete-phase.php',true);
    x.setRequestHeader('Content-Type','application/json');
    x.onreadystatechange = function(){
        if(x.readyState===4 && x.status===200){
            var res = JSON.parse(x.responseText);
            S.totalXp += res.xp_earned;
            updateXpDisplay();
            showXp(res.xp_earned, 'green');
            showXp(50, 'gold');
            burstConfetti();
            // Now complete course
            var y = new XMLHttpRequest();
            y.open('POST','../api/complete-course.php',true);
            y.setRequestHeader('Content-Type','application/json');
            y.onreadystatechange = function(){
                if(y.readyState===4 && y.status===200){
                    var cr = JSON.parse(y.responseText);
                    if(cr.success){
                        $('vXp').textContent = '+'+ cr.xp_awarded;
                        $('vCoins').textContent = '+'+cr.coins_awarded;
                        $('vLessons').textContent = totalLessons+'/'+totalLessons;
                        if(cr.achievement){
                            $('vAchievement').style.display = '';
                            $('vAchName').textContent = cr.achievement;
                        }
                        goPhase(3);
                        startCountdown();
                    }
                }
            };
            y.send(JSON.stringify({course_id:cId}));
        }
    };
    x.send(JSON.stringify({course_id:cId, lesson_id:pLessonId, phase:'mission', xp_reward:50}));
});

function startCountdown(){
    var sec = 5;
    $('countdown').textContent = sec;
    var t = setInterval(function(){
        sec--;
        $('countdown').textContent = sec;
        if(sec<=0){clearInterval(t);window.location.href='courses.php';}
    },1000);
}

/* ── XP DISPLAY ── */
function updateXpDisplay(){ $('xpDisplay').textContent = S.totalXp; }

/* ── KEYBOARD ── */
document.addEventListener('keydown',function(e){
    if(S.phase===0 && theoryData.length>0){
        if(e.key==='ArrowRight' || e.key===' '){e.preventDefault();if(S.materiIdx<totalMateri-1){S.materiIdx++;renderMateri(S.materiIdx);}}
        if(e.key==='ArrowLeft'){e.preventDefault();if(S.materiIdx>0){S.materiIdx--;renderMateri(S.materiIdx);}}
    }
});

/* ── INIT ── */
if(theoryData.length>0){
    renderMateri(0);
    updateProgress();
}
if(totalQuiz>0) renderQuiz();
console.log('🎮 Game learning loaded!', theoryData.length+' materi, '+totalQuiz+' quiz, '+missionReqs.length+' mission tasks');
})();
</script>
</body>
</html>
