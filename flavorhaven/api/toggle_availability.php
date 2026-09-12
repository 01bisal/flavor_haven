<?php
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!verify_csrf($_POST['csrf'] ?? '')) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$item_id = (int)($_POST['item_id'] ?? 0);
if ($item_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid item']);
    exit;
}

try {
    // Read current
    $stmt = $pdo->prepare("SELECT name, is_available FROM menu_items WHERE id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();

    if (!$item) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Item not found']);
        exit;
    }

    $new_value = $item['is_available'] ? 0 : 1;

    $stmt = $pdo->prepare("UPDATE menu_items SET is_available = ? WHERE id = ?");
    $stmt->execute([$new_value, $item_id]);

    echo json_encode([
        'success'      => true,
        'item_id'      => $item_id,
        'item_name'    => $item['name'],
        'is_available' => (int)$new_value,
        'message'      => $new_value ? "{$item['name']} is now visible on the menu" : "{$item['name']} is now hidden from the menu",
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}