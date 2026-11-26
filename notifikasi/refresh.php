<?php
// notifikasi/refresh.php - Endpoint untuk update badge notifikasi
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();
header('Content-Type: application/json');

$user = current_user();

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifikasi WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user['id']]);
    $unread = (int)$stmt->fetchColumn();
    echo json_encode(['unread' => $unread]);
} catch (PDOException $e) {
    http_response_code(500);
    // Jangan expose error detail di production
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo json_encode(['unread' => 0, 'error' => $e->getMessage()]);
    } else {
        echo json_encode(['unread' => 0, 'error' => 'Server error']);
    }
}