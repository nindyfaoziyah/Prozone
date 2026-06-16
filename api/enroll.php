<?php
require_once '../config/config.php';
require_once '../models/Enrollment.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->course_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'course_id required']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];
$course_id = (int)$data->course_id;

try {
    $enrollment = new Enrollment($db);
    if ($enrollment->isEnrolled($user_id, $course_id)) {
        echo json_encode(['success' => true, 'message' => 'Already enrolled']);
        exit;
    }

    $enrollment->user_id = $user_id;
    $enrollment->course_id = $course_id;
    $enrollment->status = 'enrolled';

    if ($enrollment->enroll()) {
        // Update student count in courses table
        $upd = $db->prepare("UPDATE courses SET total_students = total_students + 1 WHERE id = :cid");
        $upd->execute([':cid' => $course_id]);

        echo json_encode(['success' => true, 'message' => 'Enrolled successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to enroll']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
