<?php
require_once '../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../models/UserProgress.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->level_id) || !isset($data->quest_idx)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'level_id and quest_idx required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$level_id = (int)$data->level_id;
$quest_idx = (int)$data->quest_idx;
$course_id = isset($data->course_id) ? (int)$data->course_id : null;

try {
    $db->beginTransaction();

    // Check existing
    $stmt = $db->prepare("SELECT id, status FROM user_quest_progress WHERE user_id = :uid AND level_id = :lid AND quest_idx = :qidx");
    $stmt->execute([':uid' => $user_id, ':lid' => $level_id, ':qidx' => $quest_idx]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing && $existing['status'] === 'completed') {
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Already completed', 'xp_earned' => 0]);
        exit;
    }

    // Upsert
    if ($existing) {
        $upd = $db->prepare("UPDATE user_quest_progress SET status = 'completed', completed_at = NOW() WHERE id = :id");
        $upd->execute([':id' => $existing['id']]);
    } else {
        $ins = $db->prepare("INSERT INTO user_quest_progress (user_id, level_id, quest_idx, course_id, status, completed_at) VALUES (:uid, :lid, :qidx, :cid, 'completed', NOW())");
        $ins->execute([':uid' => $user_id, ':lid' => $level_id, ':qidx' => $quest_idx, ':cid' => $course_id]);
    }

    // Award XP
    $xp_reward = isset($data->xp) ? (int)$data->xp : 10;
    $upd_user = $db->prepare("UPDATE users SET total_xp = total_xp + :xp, level = FLOOR(SQRT((total_xp + :xp2) / 100)) + 1 WHERE id = :uid");
    $upd_user->execute([':xp' => $xp_reward, ':xp2' => $xp_reward, ':uid' => $user_id]);

    // Count completed quests for this level
    $cnt = $db->prepare("SELECT COUNT(*) as done FROM user_quest_progress WHERE user_id = :uid AND level_id = :lid AND status = 'completed'");
    $cnt->execute([':uid' => $user_id, ':lid' => $level_id]);
    $completed_quests = (int)$cnt->fetch(PDO::FETCH_ASSOC)['done'];

    // Get total quests for this level from the levels definition
    $total_quests = isset($data->total_quests) ? (int)$data->total_quests : 0;
    $level_completed = $total_quests > 0 && $completed_quests >= $total_quests;

    // Update enrollment if course_id provided
    if ($course_id && $level_completed) {
        $enroll = $db->prepare("INSERT INTO enrollments (user_id, course_id, progress_percent, completed_lessons, status, enrolled_at)
                                VALUES (:uid, :cid, 100, 1, 'completed', NOW())
                                ON DUPLICATE KEY UPDATE progress_percent = 100, status = 'completed', completed_lessons = completed_lessons + 1");
        $enroll->execute([':uid' => $user_id, ':cid' => $course_id]);
    }

    // Update leaderboard
    $lb = $db->prepare("INSERT INTO leaderboard_solo (user_id, total_xp, completed_lessons)
                        VALUES (:uid, :xp, 1)
                        ON DUPLICATE KEY UPDATE total_xp = total_xp + :xp2, completed_lessons = completed_lessons + 1");
    $lb->execute([':uid' => $user_id, ':xp' => $xp_reward, ':xp2' => $xp_reward]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Quest completed!',
        'xp_earned' => $xp_reward,
        'level_completed' => $level_completed,
        'completed_quests' => $completed_quests,
        'total_quests' => $total_quests,
        'progress' => $total_quests > 0 ? round(($completed_quests / $total_quests) * 100) : 0
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
