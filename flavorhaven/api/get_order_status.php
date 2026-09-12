<?php
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$order_id = (int)($_GET['id'] ?? 0);
if ($order_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing order id']);
    exit;
}

if ($_SESSION['role'] === 'admin') {
    $stmt = $pdo->prepare("SELECT id, status, total FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
} else {
    $stmt = $pdo->prepare("SELECT id, status, total FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
}

$order = $stmt->fetch();
if (!$order) {
    http_response_code(404);
    echo json_encode(['error' => 'Order not found']);
    exit;
}

echo json_encode([
    'id'          => (int)$order['id'],
    'status'      => $order['status'],
    'total'       => (float)$order['total'],
    'server_time' => date('c'),
]);