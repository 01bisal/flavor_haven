<?php
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$stmt = $pdo->query("
    SELECT o.id, o.status, o.total, o.order_date, u.username,
           o.delivery_method, o.delivery_address, o.delivery_city, 
           o.delivery_postal_code, o.delivery_phone,
           o.payment_method, o.card_last4
    FROM orders o
    JOIN users u ON u.id = o.user_id
    WHERE o.status IN ('pending', 'preparing')
    ORDER BY 
        CASE o.status WHEN 'pending' THEN 1 WHEN 'preparing' THEN 2 END,
        o.order_date ASC
");
$orders = $stmt->fetchAll();

if (empty($orders)) {
    echo json_encode(['orders' => [], 'server_time' => date('c')]);
    exit;
}

$ids = array_column($orders, 'id');
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $pdo->prepare("
    SELECT oi.order_id, oi.quantity, oi.price, m.name
    FROM order_items oi
    JOIN menu_items m ON m.id = oi.menu_item_id
    WHERE oi.order_id IN ($placeholders)
    ORDER BY oi.id
");
$stmt->execute($ids);

$items_by_order = [];
foreach ($stmt->fetchAll() as $row) {
    $items_by_order[$row['order_id']][] = $row;
}

foreach ($orders as &$o) {
    $o['items'] = $items_by_order[$o['id']] ?? [];
    $o['total'] = (float)$o['total'];
    $o['id']    = (int)$o['id'];
}
unset($o);

echo json_encode(['orders' => $orders, 'server_time' => date('c')]);