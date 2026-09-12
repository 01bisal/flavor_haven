<?php
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

try {
    // Low stock items
    $stmt = $pdo->query("
        SELECT id, name, stock, low_stock_threshold
        FROM menu_items
        WHERE stock > 0 AND stock <= low_stock_threshold
        ORDER BY stock ASC
        LIMIT 20
    ");
    $low = $stmt->fetchAll();

    // Out of stock
    $stmt = $pdo->query("
        SELECT id, name, stock
        FROM menu_items
        WHERE stock = 0
        ORDER BY name ASC
        LIMIT 20
    ");
    $out = $stmt->fetchAll();

    $low_items = [];
    foreach ($low as $r) {
        $low_items[] = [
            'id'    => (int)$r['id'],
            'name'  => $r['name'],
            'stock' => (int)$r['stock'],
        ];
    }

    $out_items = [];
    foreach ($out as $r) {
        $out_items[] = [
            'id'    => (int)$r['id'],
            'name'  => $r['name'],
        ];
    }

    echo json_encode([
        'success'         => true,
        'low_stock'       => $low_items,
        'out_of_stock'    => $out_items,
        'low_count'       => count($low_items),
        'out_count'       => count($out_items),
        'total_alerts'    => count($low_items) + count($out_items),
        'server_time'     => date('c'),
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}