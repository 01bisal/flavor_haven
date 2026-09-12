<?php
/* ============================
   General Helper Functions
   ============================ */

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function clean($data) {
    return trim(strip_tags($data));
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf($token) {
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function redirect($path) {
    header("Location: $path");
    exit;
}

function base_url($path = '') {
    return '/flavorhaven/' . ltrim($path, '/');
}

/* ============================
   Cart Helper Functions
   ============================ */

function cart_init() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function cart_add($item_id, $quantity = 1) {
    cart_init();
    $item_id = (int)$item_id;
    $quantity = max(1, (int)$quantity);

    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id] += $quantity;
    } else {
        $_SESSION['cart'][$item_id] = $quantity;
    }
}

function cart_update($item_id, $quantity) {
    cart_init();
    $item_id = (int)$item_id;
    $quantity = (int)$quantity;

    if ($quantity <= 0) {
        cart_remove($item_id);
    } else {
        $_SESSION['cart'][$item_id] = $quantity;
    }
}

function cart_remove($item_id) {
    cart_init();
    unset($_SESSION['cart'][(int)$item_id]);
}

function cart_clear() {
    $_SESSION['cart'] = [];
}

function cart_count() {
    cart_init();
    return array_sum($_SESSION['cart']);
}

function cart_is_empty() {
    cart_init();
    return empty($_SESSION['cart']);
}

function cart_items($pdo) {
    cart_init();
    if (empty($_SESSION['cart'])) {
        return ['items' => [], 'subtotal' => 0];
    }

    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("
        SELECT id, name, price, image_path, stock, is_available
        FROM menu_items
        WHERE id IN ($placeholders)
    ");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();

    $items = [];
    $subtotal = 0;

    foreach ($rows as $row) {
        $qty = (int)($_SESSION['cart'][$row['id']] ?? 0);
        if ($qty <= 0) continue;

        $line_total = $row['price'] * $qty;
        $items[] = [
            'id'           => $row['id'],
            'name'         => $row['name'],
            'price'        => (float)$row['price'],
            'image_path'   => $row['image_path'],
            'stock'        => (int)$row['stock'],
            'is_available' => (int)$row['is_available'],
            'quantity'     => $qty,
            'line_total'   => $line_total,
        ];
        $subtotal += $line_total;
    }

    return ['items' => $items, 'subtotal' => $subtotal];
}

/* ============================
   Review Helper Functions
   ============================ */

function get_rating_stats($pdo, $menu_item_id) {
    $stmt = $pdo->prepare("
        SELECT ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS total
        FROM reviews
        WHERE menu_item_id = ?
    ");
    $stmt->execute([$menu_item_id]);
    $row = $stmt->fetch();
    return [
        'avg'   => $row['avg_rating'] ? (float)$row['avg_rating'] : 0,
        'total' => (int)$row['total'],
    ];
}

function has_user_reviewed($pdo, $user_id, $menu_item_id) {
    $stmt = $pdo->prepare("
        SELECT id FROM reviews 
        WHERE user_id = ? AND menu_item_id = ?
    ");
    $stmt->execute([$user_id, $menu_item_id]);
    return (bool)$stmt->fetch();
}

function has_user_ordered($pdo, $user_id, $menu_item_id) {
    $stmt = $pdo->prepare("
        SELECT oi.id
        FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        WHERE o.user_id = ? AND oi.menu_item_id = ?
        LIMIT 1
    ");
    $stmt->execute([$user_id, $menu_item_id]);
    return (bool)$stmt->fetch();
}

function render_stars($rating) {
    $rating = (float)$rating;
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5;
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $full) {
            $html .= '<i class="fas fa-star" style="color:#f0ad4e;"></i>';
        } elseif ($i == $full + 1 && $half) {
            $html .= '<i class="fas fa-star-half-alt" style="color:#f0ad4e;"></i>';
        } else {
            $html .= '<i class="far fa-star" style="color:#ccc;"></i>';
        }
    }
    return $html;
}