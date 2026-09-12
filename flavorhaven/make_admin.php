<?php
require_once 'config/db.php';
$hash = password_hash('Admin@123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
$stmt->execute(['admin', 'admin@flavorhaven.com', $hash]);
echo "✅ Admin created! user: admin / pass: Admin@123";