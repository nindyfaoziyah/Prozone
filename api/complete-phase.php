<?php
require_once '../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"));
if (!$data || !isset($data->course_id) || !isset($data->lesson_id) || !isset($data->phase)) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'course_id, lesson_id, phase required']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$course_id = (int)$data->course_id;
$lesson_id = (int)$data->lesson_id;
$phase = $data->phase; // 'practice' or 'challenge'
$xp_reward = isset($data->xp_reward) ? (int)$data->xp_reward : 30;

$database = new Database();
$db = $database->getConnection();

try {
    $db->beginTransaction();
    // Lock row to prevent race condition
    $check = $db->prepare("SELECT id, status FROM user_progress WHERE user_id = :uid AND lesson_id = :lid FOR UPDATE");
    $check->execute([':uid'=>$user_id, ':lid'=>$lesson_id]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing && $existing['status'] === 'completed') {
        // Already done — skip XP award
    } elseif ($existing) {
        $upd = $db->prepare("UPDATE user_progress SET status = 'completed', xp_earned = xp_earned + :xp, completed_at = NOW() WHERE id = :id");
        $upd->execute([':xp'=>$xp_reward, ':id'=>$existing['id']]);
        $db->prepare("UPDATE users SET total_xp = total_xp + :xp WHERE id = :uid")->execute([':xp'=>$xp_reward, ':uid'=>$user_id]);
    } else {
        $ins = $db->prepare("INSERT INTO user_progress (user_id, course_id, lesson_id, status, xp_earned, started_at, completed_at) VALUES (:uid, :cid, :lid, 'completed', :xp, NOW(), NOW())");
        $ins->execute([':uid'=>$user_id, ':cid'=>$course_id, ':lid'=>$lesson_id, ':xp'=>$xp_reward]);
        $db->prepare("UPDATE users SET total_xp = total_xp + :xp WHERE id = :uid")->execute([':xp'=>$xp_reward, ':uid'=>$user_id]);
    }
    $db->commit();

    // Calculate overall course progress and update enrollment
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM lessons WHERE course_id = :cid");
    $stmt->execute([':cid'=>$course_id]);
    $total_lessons = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $db->prepare("SELECT COUNT(*) as completed FROM user_progress WHERE user_id = :uid AND course_id = :cid AND status = 'completed'");
    $stmt->execute([':uid'=>$user_id, ':cid'=>$course_id]);
    $completed = (int)$stmt->fetch(PDO::FETCH_ASSOC)['completed'];

    $pct = $total_lessons > 0 ? round(($completed / $total_lessons) * 100) : 0;

    $upd = $db->prepare("UPDATE enrollments SET progress_percent = :pct, completed_lessons = :cl WHERE user_id = :uid AND course_id = :cid");
    $upd->execute([':pct'=>$pct, ':cl'=>$completed, ':uid'=>$user_id, ':cid'=>$course_id]);

    echo json_encode(['success'=>true, 'phase'=>$phase, 'xp_earned'=>$xp_reward, 'progress'=>$pct, 'completed_lessons'=>$completed, 'total_lessons'=>$total_lessons]);
} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
}
