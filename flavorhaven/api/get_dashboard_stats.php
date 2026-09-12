<?php
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

try {
    $stats = [
        'menu_items'    => (int)$pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn(),
        'users'         => (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'orders'        => (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
        'reviews'       => (int)$pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn(),
        'unread_msgs'   => (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn(),

        'available'     => (int)$pdo->query("SELECT COUNT(*) FROM menu_items WHERE stock > low_stock_threshold")->fetchColumn(),
        'low_stock'     => (int)$pdo->query("SELECT COUNT(*) FROM menu_items WHERE stock > 0 AND stock <= low_stock_threshold")->fetchColumn(),
        'out_of_stock'  => (int)$pdo->query("SELECT COUNT(*) FROM menu_items WHERE stock = 0")->fetchColumn(),

        'active_orders' => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending','preparing')")->fetchColumn(),
    ];

    echo json_encode([
        'success' => true,
        'stats'   => $stats,
        'server_time' => date('c'),
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}