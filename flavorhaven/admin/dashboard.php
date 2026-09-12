<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$totalItems   = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
$totalUsers   = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalOrders  = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$unreadMsgs   = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
$totalReviews = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();

$availableItems = $pdo->query("
    SELECT COUNT(*) FROM menu_items 
    WHERE stock > low_stock_threshold
")->fetchColumn();

$lowStockItems = $pdo->query("
    SELECT COUNT(*) FROM menu_items 
    WHERE stock > 0 AND stock <= low_stock_threshold
")->fetchColumn();

$outOfStockItems = $pdo->query("
    SELECT COUNT(*) FROM menu_items 
    WHERE stock = 0
")->fetchColumn();

$recentOrders = $pdo->query("
    SELECT o.id, o.total, o.status, o.order_date, u.username
    FROM orders o
    JOIN users u ON u.id = o.user_id
    ORDER BY o.order_date DESC
    LIMIT 5
")->fetchAll();

$recentMessages = $pdo->query("
    SELECT id, name, subject, created_at
    FROM contact_messages
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll();

$page_title = "Admin Dashboard";
include __DIR__ . '/../includes/header.php';
?>

<style>
    .dash-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }
    .dash-card {
        display: block;
        text-decoration: none;
        color: inherit;
        background: var(--bg-white);
        padding: 1.75rem 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        text-align: center;
        transition: all var(--transition);
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }
    .dash-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: var(--primary);
        transform: scaleX(0);
        transition: transform var(--transition);
    }
    .dash-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-hover);
        border-color: var(--primary);
    }
    .dash-card:hover::before { transform: scaleX(1); }
    .dash-card .icon {
        font-size: 2rem;
        color: var(--primary-dark);
        margin-bottom: 0.5rem;
    }
    .dash-card .num {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--secondary);
        line-height: 1;
        margin-bottom: 0.35rem;
        transition: color 0.3s;
    }
    .dash-card .label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-muted);
        font-weight: 600;
    }
    .dash-card.alert-low .num { color: #f0ad4e; }
    .dash-card.alert-out .num { color: #C0392B; }
    .dash-card.alert-msg .num { color: #C0392B; }
    .dash-card.alert-msg .icon { color: #C0392B; }
    .section-heading {
        font-family: var(--font-heading);
        font-size: 1.4rem;
        margin: 2rem 0 1rem;
        color: var(--secondary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .quick-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    .activity-list {
        background: var(--bg-white);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }
    .activity-list .row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.9rem 1.25rem;
        border-bottom: 1px solid #eee;
        font-size: 0.95rem;
        text-decoration: none;
        color: inherit;
        transition: background var(--transition);
    }
    .activity-list .row:last-child { border-bottom: none; }
    .activity-list .row:hover { background: var(--bg-light); }
    .activity-list .row .meta {
        color: var(--text-muted);
        font-size: 0.85rem;
    }
    .status-pill {
        display: inline-block;
        padding: 0.15rem 0.65rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #fff;
    }
    .status-pending { background: #f0ad4e; }
    .status-preparing { background: #5bc0de; }
    .status-delivered { background: #5cb85c; }
    .status-cancelled { background: #d9534f; }
    .live-dot {
        display: inline-block;
        width: 8px; height: 8px;
        background: #5cb85c;
        border-radius: 50%;
        margin-left: 0.5rem;
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0%   { box-shadow: 0 0 0 0 rgba(92,184,92,0.7); }
        70%  { box-shadow: 0 0 0 10px rgba(92,184,92,0); }
        100% { box-shadow: 0 0 0 0 rgba(92,184,92,0); }
    }
</style>

<section class="page-header">
    <h1>Admin Dashboard</h1>
    <p>Welcome, <?= e($_SESSION['username']) ?> <span class="live-dot"></span></p>
</section>

<section class="menu-section">

    <h2 class="section-heading">📊 Overview <small style="font-size:0.75rem;color:var(--text-muted);font-weight:400;text-transform:none;letter-spacing:0;">· auto-updating</small></h2>

    <div class="dash-grid">
        <a href="manage_menu.php" class="dash-card">
            <div class="icon"><i class="fas fa-utensils"></i></div>
            <div class="num" data-stat="menu_items"><?= (int)$totalItems ?></div>
            <div class="label">Menu Items</div>
        </a>

        <a href="users.php" class="dash-card">
            <div class="icon"><i class="fas fa-users"></i></div>
            <div class="num" data-stat="users"><?= (int)$totalUsers ?></div>
            <div class="label">Users</div>
        </a>

        <a href="view_orders.php" class="dash-card">
            <div class="icon"><i class="fas fa-receipt"></i></div>
            <div class="num" data-stat="orders"><?= (int)$totalOrders ?></div>
            <div class="label">Orders</div>
        </a>

        <a href="reviews.php" class="dash-card">
            <div class="icon"><i class="fas fa-star"></i></div>
            <div class="num" data-stat="reviews"><?= (int)$totalReviews ?></div>
            <div class="label">Reviews</div>
        </a>

        <a href="messages.php" class="dash-card <?= $unreadMsgs > 0 ? 'alert-msg' : '' ?>">
            <div class="icon"><i class="fas fa-envelope"></i></div>
            <div class="num" data-stat="unread_msgs"><?= (int)$unreadMsgs ?></div>
            <div class="label">Unread Messages</div>
        </a>
    </div>

    <h2 class="section-heading">📦 Stock Status</h2>

    <div class="dash-grid">
        <a href="manage_menu.php?stock=available" class="dash-card">
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <div class="num" data-stat="available"><?= (int)$availableItems ?></div>
            <div class="label">Available</div>
        </a>

        <a href="manage_menu.php?stock=low" class="dash-card alert-low">
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="num" data-stat="low_stock"><?= (int)$lowStockItems ?></div>
            <div class="label">Low Stock</div>
        </a>

        <a href="manage_menu.php?stock=out" class="dash-card alert-out">
            <div class="icon"><i class="fas fa-times-circle"></i></div>
            <div class="num" data-stat="out_of_stock"><?= (int)$outOfStockItems ?></div>
            <div class="label">Out of Stock</div>
        </a>
    </div>

    <h2 class="section-heading">⚡ Quick Actions</h2>

    <div class="quick-actions">
        <a href="live_orders.php" class="btn btn-primary"><i class="fas fa-bell"></i> Live Orders</a>
        <a href="manage_menu.php" class="btn btn-primary"><i class="fas fa-list"></i> Manage Menu</a>
        <a href="add_item.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Item</a>
        <a href="view_orders.php" class="btn btn-primary"><i class="fas fa-receipt"></i> View Orders</a>
        <a href="users.php" class="btn btn-primary"><i class="fas fa-users"></i> View Users</a>
        <a href="reviews.php" class="btn btn-primary"><i class="fas fa-star"></i> Manage Reviews</a>
        <a href="messages.php" class="btn btn-primary"><i class="fas fa-envelope"></i> Messages</a>
    </div>

    <h2 class="section-heading">🧾 Recent Orders</h2>

    <?php if (empty($recentOrders)): ?>
        <div class="alert info">No orders yet.</div>
    <?php else: ?>
        <div class="activity-list">
            <?php foreach ($recentOrders as $o): ?>
                <a href="view_orders.php" class="row">
                    <div>
                        <strong>Order #<?= (int)$o['id'] ?></strong>
                        — by <strong><?= e($o['username']) ?></strong>
                    </div>
                    <div class="meta">
                        <span class="status-pill status-<?= e($o['status']) ?>">
                            <?= e(ucfirst($o['status'])) ?>
                        </span>
                        &nbsp; $<?= number_format($o['total'], 2) ?>
                        &nbsp; <?= date('M j, g:i A', strtotime($o['order_date'])) ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h2 class="section-heading">✉️ Recent Messages</h2>

    <?php if (empty($recentMessages)): ?>
        <div class="alert info">No messages yet.</div>
    <?php else: ?>
        <div class="activity-list">
            <?php foreach ($recentMessages as $m): ?>
                <a href="messages.php" class="row">
                    <div>
                        <strong><?= e($m['name']) ?></strong>
                        — <?= e($m['subject']) ?>
                    </div>
                    <div class="meta">
                        <?= date('M j, g:i A', strtotime($m['created_at'])) ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</section>

<script>
(function () {
    const apiUrl = '<?= base_url('api/get_dashboard_stats.php') ?>';

    async function refreshStats() {
        try {
            const res = await fetch(apiUrl, { cache: 'no-store' });
            if (!res.ok) return;
            const data = await res.json();
            if (!data.success) return;

            Object.keys(data.stats).forEach(key => {
                const el = document.querySelector(`[data-stat="${key}"]`);
                if (!el) return;
                const newVal = String(data.stats[key]);
                if (el.textContent !== newVal) {
                    el.textContent = newVal;
                    el.style.color = '#5cb85c';
                    setTimeout(() => el.style.color = '', 800);
                }
            });
        } catch (err) { /* silent */ }
    }

    setInterval(refreshStats, 10000);
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>