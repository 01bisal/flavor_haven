<?php
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$item_id = (int)($_GET['item_id'] ?? 0);
$offset  = max(0, (int)($_GET['offset'] ?? 0));
$limit   = min(50, max(1, (int)($_GET['limit'] ?? 10)));

if ($item_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing item_id']);
    exit;
}

try {
    // Total count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE menu_item_id = ?");
    $stmt->execute([$item_id]);
    $total = (int)$stmt->fetchColumn();

    // Average rating
    $stmt = $pdo->prepare("SELECT ROUND(AVG(rating), 1) FROM reviews WHERE menu_item_id = ?");
    $stmt->execute([$item_id]);
    $avg = (float)($stmt->fetchColumn() ?: 0);

    // Fetch page of reviews (LIMIT/OFFSET must be integers — safe here since cast)
    $sql = "
        SELECT r.id, r.rating, r.comment, r.created_at, u.username
        FROM reviews r
        JOIN users u ON u.id = r.user_id
        WHERE r.menu_item_id = ?
        ORDER BY r.created_at DESC
        LIMIT $limit OFFSET $offset
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$item_id]);
    $reviews = $stmt->fetchAll();

    // Clean output
    $out = [];
    foreach ($reviews as $r) {
        $out[] = [
            'id'         => (int)$r['id'],
            'rating'     => (int)$r['rating'],
            'comment'    => $r['comment'],
            'username'   => $r['username'],
            'created_at' => date('M j, Y', strtotime($r['created_at'])),
        ];
    }

    echo json_encode([
        'success'  => true,
        'total'    => $total,
        'avg'      => $avg,
        'offset'   => $offset,
        'limit'    => $limit,
        'has_more' => ($offset + $limit) < $total,
        'reviews'  => $out,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}