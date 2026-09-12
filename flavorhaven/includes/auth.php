<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
    ]);
}

// Auto-initialize the cart for every request
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Require login
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['flash'] = "Please log in to continue.";
        redirect(base_url('login.php'));
    }
}

// Require specific role
function require_role($roles = []) {
    require_login();
    if (!in_array($_SESSION['role'], (array)$roles)) {
        http_response_code(403);
        die("Access denied. You do not have permission.");
    }
}

// Get current user
function current_user() {
    return [
        'id'       => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'role'     => $_SESSION['role'] ?? null,
    ];
}