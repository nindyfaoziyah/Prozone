<?php
function logActivity($db, $user_id, $action, $description = '') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $query = "INSERT INTO activity_log (user_id, action, description, ip_address, user_agent) VALUES (:user_id, :action, :description, :ip, :ua)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':action', $action);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':ip', $ip);
    $stmt->bindParam(':ua', $ua);
    return $stmt->execute();
}

function getActivityLog($db, $limit = 100, $offset = 0, $action_filter = '', $user_filter = 0) {
    $query = "SELECT al.*, u.nama_lengkap, u.username, u.avatar
              FROM activity_log al
              JOIN users u ON al.user_id = u.id
              WHERE 1=1";
    $params = [];
    if ($action_filter) {
        $query .= " AND al.action = :action";
        $params[':action'] = $action_filter;
    }
    if ($user_filter > 0) {
        $query .= " AND al.user_id = :user_id";
        $params[':user_id'] = $user_filter;
    }
    $query .= " ORDER BY al.created_at DESC LIMIT :lim OFFSET :off";
    
    $stmt = $db->prepare($query);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindParam(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getActivityStats($db) {
    $stats = [];
    $stats['total'] = $db->query("SELECT COUNT(*) FROM activity_log")->fetchColumn();
    $stats['today'] = $db->query("SELECT COUNT(*) FROM activity_log WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $stats['unique_actions'] = $db->query("SELECT COUNT(DISTINCT action) FROM activity_log")->fetchColumn();
    $actions = $db->query("SELECT action, COUNT(*) as total FROM activity_log GROUP BY action ORDER BY total DESC LIMIT 10");
    $stats['top_actions'] = $actions->fetchAll(PDO::FETCH_ASSOC);
    return $stats;
}
