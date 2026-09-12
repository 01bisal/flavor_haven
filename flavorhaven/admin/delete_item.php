<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash'] = "Item deleted.";
}
redirect('manage_menu.php');