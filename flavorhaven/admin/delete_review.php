<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['csrf'] ?? '')) {
    redirect('reviews.php');
}

$review_id = (int)($_POST['review_id'] ?? 0);
if ($review_id > 0) {
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->execute([$review_id]);
    $_SESSION['flash'] = "Review deleted.";
}

redirect('reviews.php');