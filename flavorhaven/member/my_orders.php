<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$stmt = $pdo->prepare("
    SELECT o.id, o.total, o.status, o.order_date,
           GROUP_CONCAT(CONCAT(oi.quantity, 'x ', m.name) SEPARATOR ', ') AS items
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    LEFT JOIN menu_items m ON m.id = oi.menu_item_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.order_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$page_title = "My Orders";
include __DIR__ . '/../includes/header.php';
?>

<section class="page-header"><h1>My Orders</h1></section>

<section class="menu-section">
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert success" style="padding:1rem;background:#d4edda;color:#155724;border-radius:8px;margin-bottom:1rem;">
            <?= e($_SESSION['flash']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <p>No orders yet. <a href="../menu.php">Browse the menu</a>.</p>
    <?php else: ?>
        <table style="width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.1);">
            <thead style="background:#2C1810;color:#F5F0EB;">
                <tr>
                    <th style="padding:1rem;text-align:left;">Order #</th>
                    <th style="padding:1rem;text-align:left;">Items</th>
                    <th style="padding:1rem;text-align:left;">Total</th>
                    <th style="padding:1rem;text-align:left;">Status</th>
                    <th style="padding:1rem;text-align:left;">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:1rem;">#<?= (int)$o['id'] ?></td>
                        <td style="padding:1rem;"><?= e($o['items']) ?></td>
                        <td style="padding:1rem;">$<?= number_format($o['total'], 2) ?></td>
                        <td style="padding:1rem;"><?= e(ucfirst($o['status'])) ?></td>
                        <td style="padding:1rem;"><?= date('M j, Y g:i A', strtotime($o['order_date'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>