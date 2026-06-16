<?php
require_once '../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"));
if (!$data || !isset($data->course_id)) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'course_id required']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$course_id = (int)$data->course_id;

$database = new Database();
$db = $database->getConnection();

try {
    // Fetch course info first (read-only, no lock needed)
    $stmt = $db->prepare("SELECT xp_reward, judul_course FROM courses WHERE id = :id");
    $stmt->execute([':id'=>$course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$course) {
        http_response_code(404);
        echo json_encode(['success'=>false,'message'=>'Course not found']);
        exit;
    }

    $db->beginTransaction();

    // Lock enrollment row to prevent race condition
    $stmt = $db->prepare("SELECT status FROM enrollments WHERE user_id = :uid AND course_id = :cid FOR UPDATE");
    $stmt->execute([':uid'=>$user_id, ':cid'=>$course_id]);
    $enroll = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($enroll && $enroll['status'] === 'completed') {
        $db->commit();
        echo json_encode(['success'=>true, 'already_completed'=>true, 'course_title'=>$course['judul_course']]);
        exit;
    }

    $course_xp = (int)$course['xp_reward'];
    $bonus_xp = $course_xp;
    $coins_reward = (int)max(10, $course_xp / 2);

    // Update enrollment
    $upd = $db->prepare("UPDATE enrollments SET status = 'completed', progress_percent = 100, completed_at = NOW() WHERE user_id = :uid AND course_id = :cid");
    $upd->execute([':uid'=>$user_id, ':cid'=>$course_id]);

    // Award bonus XP and coins
    $db->prepare("UPDATE users SET total_xp = total_xp + :xp, coins = coins + :coins WHERE id = :uid")
        ->execute([':xp'=>$bonus_xp, ':coins'=>$coins_reward, ':uid'=>$user_id]);

    // Update leaderboard_solo
    $lb = $db->prepare("INSERT INTO leaderboard_solo (user_id, total_xp, completed_courses, completed_lessons, last_updated)
        VALUES (:uid, (SELECT total_xp FROM users WHERE id = :uid2), 1, (SELECT COUNT(*) FROM user_progress WHERE user_id = :uid3 AND status='completed'), NOW())
        ON DUPLICATE KEY UPDATE
            total_xp = (SELECT total_xp FROM users WHERE id = :uid4),
            completed_courses = completed_courses + 1,
            completed_lessons = (SELECT COUNT(*) FROM user_progress WHERE user_id = :uid5 AND status='completed'),
            last_updated = NOW()");
    $lb->execute([':uid'=>$user_id, ':uid2'=>$user_id, ':uid3'=>$user_id, ':uid4'=>$user_id, ':uid5'=>$user_id]);

    // Check for achievements (course completion)
    $achievement = null;
    $ach_stmt = $db->prepare("SELECT id, nama_achievement, icon FROM achievements WHERE nama_achievement LIKE '%course%' OR nama_achievement LIKE '%complete%' OR nama_achievement LIKE '%selesai%' LIMIT 1");
    $ach_stmt->execute();
    $ach = $ach_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ach) {
        $chk = $db->prepare("SELECT id FROM user_achievements WHERE user_id = :uid AND achievement_id = :aid");
        $chk->execute([':uid'=>$user_id, ':aid'=>$ach['id']]);
        if (!$chk->fetch()) {
            $db->prepare("INSERT INTO user_achievements (user_id, achievement_id, earned_at) VALUES (:uid, :aid, NOW())")
                ->execute([':uid'=>$user_id, ':aid'=>$ach['id']]);
            $achievement = $ach['nama_achievement'];
        }
    }

    $db->commit();

    echo json_encode([
        'success'=>true,
        'course_title'=>$course['judul_course'],
        'xp_awarded'=>(int)$bonus_xp,
        'coins_awarded'=>(int)$coins_reward,
        'achievement'=>$achievement,
        'already_completed'=>false
    ]);
} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
}
