<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? '')) {
    redirect('../menu.php');
}

$menu_item_id = (int)($_POST['menu_item_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? 1));

// Fetch item and check availability
$stmt = $pdo->prepare("SELECT id, name, price, stock, is_available FROM menu_items WHERE id = ?");
$stmt->execute([$menu_item_id]);
$item = $stmt->fetch();

// Reject if item missing, unavailable, or out of stock
if (!$item || !$item['is_available'] || $item['stock'] <= 0) {
    $_SESSION['flash'] = "Sorry, that item is currently unavailable.";
    redirect('../menu.php');
}

// Reject if requested quantity exceeds stock
if ($quantity > $item['stock']) {
    $_SESSION['flash'] = "Only {$item['stock']} left in stock for {$item['name']}.";
    redirect('../menu.php');
}

try {
    $pdo->beginTransaction();

    $total = $item['price'] * $quantity;

    // 1. Insert order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$_SESSION['user_id'], $total]);
    $order_id = $pdo->lastInsertId();

    // 2. Insert order item
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$order_id, $menu_item_id, $quantity, $item['price']]);

    // 3. Decrease stock
    $stmt = $pdo->prepare("UPDATE menu_items SET stock = stock - ? WHERE id = ?");
    $stmt->execute([$quantity, $menu_item_id]);

    $pdo->commit();

    $_SESSION['flash'] = "Order placed successfully! Order #$order_id — {$quantity}× {$item['name']}";
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Order error: " . $e->getMessage());
    $_SESSION['flash'] = "Failed to place order. Please try again.";
}

redirect('../member/my_orders.php');