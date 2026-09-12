<?php
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? '')) {
    redirect('cart.php');
}

$item_id = (int)($_POST['item_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 0);

// Validate stock
$stmt = $pdo->prepare("SELECT stock FROM menu_items WHERE id = ?");
$stmt->execute([$item_id]);
$row = $stmt->fetch();

if ($row && $quantity > $row['stock']) {
    $quantity = (int)$row['stock'];
    $_SESSION['flash'] = "Quantity capped to available stock ({$row['stock']}).";
}

cart_update($item_id, $quantity);

if (!isset($_SESSION['flash'])) {
    $_SESSION['flash'] = "Cart updated.";
}

redirect('cart.php');