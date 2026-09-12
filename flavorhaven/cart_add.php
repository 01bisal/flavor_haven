<?php
require_once 'includes/auth.php';

// Require login before adding to cart
if (!is_logged_in()) {
    $_SESSION['flash'] = "Please log in to add items to your cart.";
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? '')) {
    redirect('menu.php');
}

$item_id = (int)($_POST['menu_item_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? 1));

// Validate item exists and has stock
$stmt = $pdo->prepare("SELECT id, name, stock, is_available FROM menu_items WHERE id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item || !$item['is_available'] || $item['stock'] <= 0) {
    $_SESSION['flash'] = "That item is unavailable.";
    redirect('menu.php');
}

// Cap quantity to available stock
$current_in_cart = $_SESSION['cart'][$item_id] ?? 0;
$new_qty = min($quantity + $current_in_cart, $item['stock']);

if ($new_qty >= $item['stock']) {
    $_SESSION['flash'] = "Added to cart (max available: {$item['stock']}).";
} else {
    $_SESSION['flash'] = "{$item['name']} added to cart.";
}

cart_add($item_id, $quantity);
// Cap immediately after add
$_SESSION['cart'][$item_id] = $new_qty;

// Redirect back to where they came from
redirect($_POST['redirect'] ?? 'menu.php');