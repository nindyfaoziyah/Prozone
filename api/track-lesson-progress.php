<?php
require_once '../config/config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->course_id) || !isset($data->lesson_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'course_id and lesson_id required']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];
$course_id = (int)$data->course_id;
$lesson_id = (int)$data->lesson_id;
$status = $data->status ?? 'in_progress';

try {
    $stmt = $db->prepare("INSERT INTO user_progress (user_id, course_id, lesson_id, status, started_at)
                          VALUES (:uid, :cid, :lid, :status, NOW())
                          ON DUPLICATE KEY UPDATE
                              status = IF(status = 'completed', 'completed', :status2),
                              started_at = IF(status = 'not_started', NOW(), started_at)");
    $stmt->execute([
        ':uid' => $user_id,
        ':cid' => $course_id,
        ':lid' => $lesson_id,
        ':status' => $status,
        ':status2' => $status
    ]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
