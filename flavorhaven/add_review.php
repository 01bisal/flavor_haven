<?php
require_once 'includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? '')) {
    redirect('menu.php');
}

if ($_SESSION['role'] === 'admin') {
    $_SESSION['flash'] = "Admins cannot post reviews.";
    redirect('menu.php');
}

$menu_item_id = (int)($_POST['menu_item_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($menu_item_id <= 0) {
    $_SESSION['flash'] = "Invalid dish.";
    redirect('menu.php');
}

if ($rating < 1 || $rating > 5) {
    $_SESSION['flash'] = "Please choose a rating between 1 and 5.";
    redirect("dish.php?id=$menu_item_id");
}

if (strlen($comment) > 1000) {
    $comment = substr($comment, 0, 1000);
}

$stmt = $pdo->prepare("SELECT id FROM menu_items WHERE id = ?");
$stmt->execute([$menu_item_id]);
if (!$stmt->fetch()) {
    $_SESSION['flash'] = "Dish not found.";
    redirect('menu.php');
}

if (has_user_reviewed($pdo, $_SESSION['user_id'], $menu_item_id)) {
    $_SESSION['flash'] = "You have already reviewed this dish.";
    redirect("dish.php?id=$menu_item_id");
}

if (!has_user_ordered($pdo, $_SESSION['user_id'], $menu_item_id)) {
    $_SESSION['flash'] = "Only verified buyers can review this dish.";
    redirect("dish.php?id=$menu_item_id");
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO reviews (user_id, menu_item_id, rating, comment)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$_SESSION['user_id'], $menu_item_id, $rating, $comment]);
    $_SESSION['flash'] = "✅ Thank you! Your review has been posted.";
} catch (PDOException $e) {
    error_log("Review error: " . $e->getMessage());
    $_SESSION['flash'] = "Failed to submit review. Please try again.";
}

redirect("dish.php?id=$menu_item_id");