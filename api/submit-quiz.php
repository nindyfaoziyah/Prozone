<?php
require_once '../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"));
if (!$data || !isset($data->course_id) || !isset($data->lesson_id) || !isset($data->answers)) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'course_id, lesson_id, answers required']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$course_id = (int)$data->course_id;
$lesson_id = (int)$data->lesson_id;
$answers = (array)$data->answers;

$database = new Database();
$db = $database->getConnection();

// Fetch quiz questions
$stmt = $db->prepare("SELECT konten, xp_reward FROM lessons WHERE id = :id AND course_id = :cid");
$stmt->execute([':id'=>$lesson_id, ':cid'=>$course_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$lesson) {
    http_response_code(404);
    echo json_encode(['success'=>false,'message'=>'Lesson not found']);
    exit;
}

$questions = json_decode($lesson['konten'], true);
if (!$questions || !is_array($questions)) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Invalid quiz data']);
    exit;
}

$total = count($questions);
$correct = 0;
$details = [];

foreach ($questions as $i => $q) {
    $selected = isset($answers[$i]) ? (int)$answers[$i] : -1;
    $isCorrect = ($selected === (int)$q['correct']);
    if ($isCorrect) $correct++;
    $details[] = [
        'idx' => $i,
        'selected' => $selected,
        'correct' => (int)$q['correct'],
        'isCorrect' => $isCorrect
    ];
}

$percentage = $total > 0 ? round(($correct / $total) * 100) : 0;
$passed = $percentage >= 70;
$xp_reward = $passed ? ((int)($lesson['xp_reward'] ?? 15)) : 0;

// Save result to user_progress
try {
    $check = $db->prepare("SELECT id, status FROM user_progress WHERE user_id = :uid AND lesson_id = :lid");
    $check->execute([':uid'=>$user_id, ':lid'=>$lesson_id]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing && $existing['status'] === 'completed') {
        // Already completed — don't overwrite
    } elseif ($existing) {
        $upd = $db->prepare("UPDATE user_progress SET skor = :skor, status = IF(:passed=1, 'completed', 'in_progress'), xp_earned = :xp WHERE id = :id");
        $upd->execute([':skor'=>$percentage, ':passed'=>$passed ? 1 : 0, ':xp'=>$xp_earned = $xp_reward, ':id'=>$existing['id']]);
    } else {
        $ins = $db->prepare("INSERT INTO user_progress (user_id, course_id, lesson_id, status, skor, xp_earned, started_at) VALUES (:uid, :cid, :lid, :status, :skor, :xp, NOW())");
        $ins->execute([':uid'=>$user_id, ':cid'=>$course_id, ':lid'=>$lesson_id, ':status'=>$passed ? 'completed' : 'in_progress', ':skor'=>$percentage, ':xp'=>$xp_reward]);
    }

    if ($passed && $xp_reward > 0) {
        $db->prepare("UPDATE users SET total_xp = total_xp + :xp WHERE id = :uid")->execute([':xp'=>$xp_reward, ':uid'=>$user_id]);
    }
} catch (Exception $e) {
    // Log error but still return success
}

echo json_encode([
    'success'=>true,
    'score'=>$correct,
    'total'=>$total,
    'percentage'=>$percentage,
    'passed'=>$passed,
    'xp_earned'=>$xp_reward,
    'details'=>$details
]);
