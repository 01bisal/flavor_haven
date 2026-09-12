<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

// Fetch user info (including new address fields)
$stmt = $pdo->prepare("
    SELECT username, email, role, created_at, address, city, postal_code, phone 
    FROM users 
    WHERE id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Fetch order stats
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total_orders, COALESCE(SUM(total), 0) AS total_spent 
    FROM orders 
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

// Fetch 5 most recent orders
$stmt = $pdo->prepare("
    SELECT o.id, o.total, o.status, o.order_date,
           GROUP_CONCAT(CONCAT(oi.quantity, '× ', m.name) SEPARATOR ', ') AS items
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    LEFT JOIN menu_items m ON m.id = oi.menu_item_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.order_date DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_orders = $stmt->fetchAll();

$page_title = "My Dashboard";
$meta_desc  = "Your personal Flavor Haven account dashboard.";
include __DIR__ . '/../includes/header.php';
?>

<section class="page-header">
    <h1>Welcome, <?= e($user['username']) ?> 👋</h1>
    <p>Manage your orders and account details</p>
</section>

<section class="menu-section">

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert success" role="alert">
            <?= e($_SESSION['flash']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- Account Overview Cards -->
    <div class="stats">
        <div class="stat">
            <h2><?= (int)$stats['total_orders'] ?></h2>
            <p>Total Orders</p>
        </div>
        <div class="stat">
            <h2>$<?= number_format($stats['total_spent'], 2) ?></h2>
            <p>Total Spent</p>
        </div>
        <div class="stat">
            <h2><?= e(ucfirst($user['role'])) ?></h2>
            <p>Account Type</p>
        </div>
        <div class="stat">
            <h2><?= date('M Y', strtotime($user['created_at'])) ?></h2>
            <p>Member Since</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div style="margin: 2rem 0; display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="<?= base_url('menu.php') ?>" class="btn btn-primary">
            <i class="fas fa-utensils" aria-hidden="true"></i> Order Food
        </a>
        <a href="<?= base_url('member/my_orders.php') ?>" class="btn btn-primary">
            <i class="fas fa-receipt" aria-hidden="true"></i> View All Orders
        </a>
        <a href="<?= base_url('member/my_messages.php') ?>" class="btn btn-primary">
            <i class="fas fa-envelope" aria-hidden="true"></i> My Messages
        </a>
        <a href="<?= base_url('contact.php') ?>" class="btn btn-primary">
            <i class="fas fa-paper-plane" aria-hidden="true"></i> Contact Us
        </a>
    </div>

    <!-- Recent Orders -->
    <h2 style="font-family: var(--font-heading); margin: 2rem 0 1rem;">Recent Orders</h2>

    <?php if (empty($recent_orders)): ?>
        <div class="alert info" role="alert">
            You haven't placed any orders yet.
            <a href="<?= base_url('menu.php') ?>">Browse the menu</a> to get started!
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $o): ?>
                    <tr>
                        <td>#<?= (int)$o['id'] ?></td>
                        <td><?= e($o['items']) ?></td>
                        <td>$<?= number_format($o['total'], 2) ?></td>
                        <td>
                            <?php
                            $badge_colors = [
                                'pending'   => '#f0ad4e',
                                'preparing' => '#5bc0de',
                                'delivered' => '#5cb85c',
                                'cancelled' => '#d9534f',
                            ];
                            $color = $badge_colors[$o['status']] ?? '#999';
                            ?>
                            <span style="display:inline-block;padding:0.2rem 0.7rem;border-radius:50px;background:<?= $color ?>;color:#fff;font-size:0.8rem;font-weight:600;">
                                <?= e(ucfirst($o['status'])) ?>
                            </span>
                        </td>
                        <td><?= date('M j, Y g:i A', strtotime($o['order_date'])) ?></td>
                        <td>
                            <a href="track_order.php?id=<?= (int)$o['id'] ?>" 
                               class="btn btn-primary" 
                               style="padding:0.3rem 0.8rem;font-size:0.8rem;">
                                <i class="fas fa-satellite-dish" aria-hidden="true"></i> Track
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Account Details -->
    <h2 style="font-family: var(--font-heading); margin: 3rem 0 1rem;">Account Details</h2>

    <div style="background: var(--bg-white); padding: 1.5rem; border-radius: var(--border-radius); box-shadow: var(--shadow); max-width: 650px;">

        <h3 style="font-family: var(--font-heading); font-size: 1.1rem; margin-bottom: 1rem; color: var(--secondary);">
            <i class="fas fa-user-circle"></i> Basic Information
        </h3>

        <p><strong>Username:</strong> <?= e($user['username']) ?></p>
        <p><strong>Email:</strong> <?= e($user['email']) ?></p>
        <p><strong>Role:</strong> <?= e(ucfirst($user['role'])) ?></p>
        <p><strong>Joined:</strong> <?= date('F j, Y', strtotime($user['created_at'])) ?></p>

        <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #eee;">

        <h3 style="font-family: var(--font-heading); font-size: 1.1rem; margin-bottom: 1rem; color: var(--secondary);">
            <i class="fas fa-map-marker-alt"></i> Delivery Address
        </h3>

        <?php if (!empty($user['address']) || !empty($user['city']) || !empty($user['phone'])): ?>
            <p><strong>Street:</strong> 
                <?= !empty($user['address']) ? e($user['address']) : '<em style="color:var(--text-muted);">Not set</em>' ?>
            </p>
            <p><strong>City:</strong> 
                <?= !empty($user['city']) ? e($user['city']) : '<em style="color:var(--text-muted);">Not set</em>' ?>
            </p>
            <p><strong>Postal Code:</strong> 
                <?= !empty($user['postal_code']) ? e($user['postal_code']) : '<em style="color:var(--text-muted);">Not set</em>' ?>
            </p>
            <p><strong>Phone:</strong> 
                <?= !empty($user['phone']) ? e($user['phone']) : '<em style="color:var(--text-muted);">Not set</em>' ?>
            </p>
        <?php else: ?>
            <p style="color: var(--text-muted); font-style: italic;">
                You haven't set your delivery address yet. You'll need one to place delivery orders, 
                or you can choose pickup at checkout.
            </p>
        <?php endif; ?>

        <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <a href="edit_profile.php" class="btn btn-primary">
                <i class="fas fa-edit" aria-hidden="true"></i> Edit Profile
            </a>
            <a href="my_messages.php" class="btn" style="background:#eee;color:#333;">
                <i class="fas fa-envelope" aria-hidden="true"></i> My Messages
            </a>
        </div>

    </div>

</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>