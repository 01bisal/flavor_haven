<?php
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? '')) {
    redirect('cart.php');
}

$item_id = (int)($_POST['item_id'] ?? 0);
cart_remove($item_id);
$_SESSION['flash'] = "Item removed from cart.";
redirect('cart.php');