<?php
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? '')) {
    redirect('cart.php');
}

cart_clear();
$_SESSION['flash'] = "Cart emptied.";
redirect('cart.php');