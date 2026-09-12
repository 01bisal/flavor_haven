<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (in_array($status, ['pending','preparing','delivered','cancelled'])) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        $_SESSION['flash'] = "Order #$order_id status updated to " . ucfirst($status) . ".";
    }
    redirect('view_orders.php');
}

// Optional filter by status
$filter = $_GET['status'] ?? 'all';
$allowed = ['pending','preparing','delivered','cancelled'];

$sql = "
    SELECT o.*, u.username, u.email
    FROM orders o
    JOIN users u ON u.id = o.user_id
";
$params = [];

if (in_array($filter, $allowed)) {
    $sql .= " WHERE o.status = ?";
    $params[] = $filter;
}
$sql .= " ORDER BY o.order_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Fetch all items for these orders in one query (avoids N+1)
$order_ids = array_column($orders, 'id');
$items_by_order = [];

if (!empty($order_ids)) {
    $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
    $stmt = $pdo->prepare("
        SELECT oi.order_id, oi.quantity, oi.price, m.name
        FROM order_items oi
        JOIN menu_items m ON m.id = oi.menu_item_id
        WHERE oi.order_id IN ($placeholders)
        ORDER BY oi.id
    ");
    $stmt->execute($order_ids);
    foreach ($stmt->fetchAll() as $row) {
        $items_by_order[$row['order_id']][] = $row;
    }
}

$page_title = "All Orders";
include __DIR__ . '/../includes/header.php';
?>

<style>
    .filter-bar {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin: 1.5rem 0;
    }
    .filter-btn {
        padding: 0.5rem 1.25rem;
        border-radius: 50px;
        border: 2px solid var(--primary);
        background: transparent;
        color: var(--secondary);
        font-weight: 600;
        text-decoration: none;
        font-size: 0.9rem;
        transition: all var(--transition);
    }
    .filter-btn:hover { background: var(--primary-light); }
    .filter-btn.active {
        background: var(--primary);
        color: var(--secondary);
    }

    .order-items {
        list-style: none;
        padding: 0;
        margin: 0;
        font-size: 0.9rem;
    }
    .order-items li {
        padding: 0.2rem 0;
        color: var(--text-dark);
    }
    .order-items li::before {
        content: '•';
        color: var(--primary-dark);
        font-weight: 700;
        margin-right: 0.4rem;
    }
    .order-items .qty {
        color: var(--text-muted);
        font-weight: 600;
    }

    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #fff;
        letter-spacing: 0.5px;
    }
    .status-pending   { background: #f0ad4e; }
    .status-preparing { background: #5bc0de; }
    .status-delivered { background: #5cb85c; }
    .status-cancelled { background: #d9534f; }
</style>

<section class="menu-section">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
        <h1 style="margin:0;">All Orders</h1>
        <a href="dashboard.php" class="btn" style="background:#eee;color:#333;">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> Back to Dashboard
        </a>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert success" role="alert" style="margin-top:1rem;"><?= e($_SESSION['flash']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- Status filter -->
    <div class="filter-bar">
        <a href="?status=all" class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">All</a>
        <?php foreach ($allowed as $s): ?>
            <a href="?status=<?= $s ?>" class="filter-btn <?= $filter === $s ? 'active' : '' ?>">
                <?= ucfirst($s) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($orders)): ?>
        <div class="alert info" role="alert">
            No orders<?= $filter !== 'all' ? " with status '{$filter}'" : '' ?> found.
        </div>
    <?php else: ?>

        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Items Ordered</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                    <?php $items = $items_by_order[$o['id']] ?? []; ?>
                    <tr>
                        <td><strong>#<?= (int)$o['id'] ?></strong></td>

                        <td>
                            <strong><?= e($o['username']) ?></strong><br>
                            <small style="color:var(--text-muted);"><?= e($o['email']) ?></small>
                        </td>

                        <td>
                            <?php if (empty($items)): ?>
                                <em style="color:var(--text-muted);">No items</em>
                            <?php else: ?>
                                <ul class="order-items">
                                    <?php foreach ($items as $it): ?>
                                        <li>
                                            <span class="qty"><?= (int)$it['quantity'] ?>×</span>
                                            <?= e($it['name']) ?>
                                            <span style="color:var(--text-muted);">
                                                — $<?= number_format($it['price'], 2) ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </td>

                        <td><strong>$<?= number_format($o['total'], 2) ?></strong></td>

                        <td>
                            <?= date('M j, Y', strtotime($o['order_date'])) ?><br>
                            <small style="color:var(--text-muted);">
                                <?= date('g:i A', strtotime($o['order_date'])) ?>
                            </small>
                        </td>

                        <td>
                            <span class="status-badge status-<?= e($o['status']) ?>">
                                <?= e($o['status']) ?>
                            </span>
                            <form method="POST" style="margin-top:0.5rem;">
                                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                                <select name="status" onchange="this.form.submit()"
                                        aria-label="Change status for order #<?= (int)$o['id'] ?>"
                                        style="padding:0.35rem 0.6rem;border:2px solid #E0D6CC;border-radius:6px;font-size:0.85rem;background:var(--bg-light);cursor:pointer;">
                                    <?php foreach (['pending','preparing','delivered','cancelled'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>>
                                            <?= ucfirst($s) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

    <p style="margin-top:2rem;">
        <a href="dashboard.php" class="btn btn-primary">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> Back to Dashboard
        </a>
    </p>

</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>