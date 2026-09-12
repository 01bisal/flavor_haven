<?php
require_once 'includes/auth.php';
require_login();

// Only allow checkout when user is a member (not admin)
if ($_SESSION['role'] === 'admin') {
    $_SESSION['flash'] = "Admins cannot place orders.";
    redirect('cart.php');
}

$data = cart_items($pdo);
$items = $data['items'];
$subtotal = $data['subtotal'];

if (empty($items)) {
    $_SESSION['flash'] = "Your cart is empty.";
    redirect('menu.php');
}

// Double-check each item still has enough stock
$errors = [];
foreach ($items as $it) {
    if ($it['quantity'] > $it['stock']) {
        $errors[] = "{$it['name']}: only {$it['stock']} in stock (you have {$it['quantity']} in cart).";
    }
    if (!$it['is_available']) {
        $errors[] = "{$it['name']} is no longer available.";
    }
}

if ($errors) {
    $page_title = "Checkout Failed";
    include 'includes/header.php';
    echo '<section class="menu-section">';
    echo '<h1>Checkout Failed</h1>';
    echo '<div class="alert error" role="alert"><ul>';
    foreach ($errors as $err) {
        echo '<li>' . e($err) . '</li>';
    }
    echo '</ul></div>';
    echo '<a href="cart.php" class="btn btn-primary">Back to Cart</a>';
    echo '</section>';
    include 'includes/footer.php';
    exit;
}

$tax = $subtotal * 0.05;
$total = $subtotal + $tax;

try {
    $pdo->beginTransaction();

    // 1. Create the order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$_SESSION['user_id'], $total]);
    $order_id = $pdo->lastInsertId();

    // 2. Insert each order item + decrement stock
    $insert_item = $pdo->prepare("
        INSERT INTO order_items (order_id, menu_item_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    $decrement = $pdo->prepare("UPDATE menu_items SET stock = stock - ? WHERE id = ?");

    foreach ($items as $it) {
        $insert_item->execute([$order_id, $it['id'], $it['quantity'], $it['price']]);
        $decrement->execute([$it['quantity'], $it['id']]);
    }

    $pdo->commit();

    // 3. Clear the cart
    cart_clear();

    $_SESSION['flash'] = "Order #$order_id placed successfully! Total: $" . number_format($total, 2);
    redirect('member/my_orders.php');

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Checkout error: " . $e->getMessage());
    $_SESSION['flash'] = "Failed to place order. Please try again.";
    redirect('cart.php');
}