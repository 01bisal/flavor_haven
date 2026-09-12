<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$order_id = (int)($_GET['id'] ?? 0);
if ($order_id <= 0) {
    $_SESSION['flash'] = "Invalid order.";
    redirect('my_orders.php');
}

// Fetch order (customer can only view their own; admin can view any)
if ($_SESSION['role'] === 'admin') {
    $stmt = $pdo->prepare("
        SELECT o.*, u.username 
        FROM orders o
        JOIN users u ON u.id = o.user_id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
} else {
    $stmt = $pdo->prepare("
        SELECT o.*, u.username 
        FROM orders o
        JOIN users u ON u.id = o.user_id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
}
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['flash'] = "Order not found.";
    redirect('my_orders.php');
}

// Fetch items
$stmt = $pdo->prepare("
    SELECT oi.quantity, oi.price, m.name, m.image_path
    FROM order_items oi
    JOIN menu_items m ON m.id = oi.menu_item_id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

$page_title = "Order #$order_id";
include __DIR__ . '/../includes/header.php';
?>

<style>
    .track-wrap {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .success-banner {
        background: linear-gradient(135deg, #d4edda, #c3e6cb);
        border: 2px solid #5cb85c;
        border-radius: var(--border-radius);
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
        text-align: center;
        animation: popIn 0.5s ease-out;
    }
    @keyframes popIn {
        0%   { transform: scale(0.9); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    .success-banner .checkmark {
        font-size: 3rem;
        color: #5cb85c;
        margin-bottom: 0.5rem;
        animation: bounceIn 0.6s ease-out;
    }
    @keyframes bounceIn {
        0%   { transform: scale(0); }
        50%  { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    .success-banner h2 {
        color: #155724;
        font-family: var(--font-heading);
        font-size: 1.6rem;
        margin-bottom: 0.35rem;
    }
    .success-banner p {
        color: #155724;
        margin: 0.25rem 0;
    }
    .success-banner .order-num {
        display: inline-block;
        background: #155724;
        color: #fff;
        padding: 0.3rem 1rem;
        border-radius: 50px;
        font-weight: 700;
        margin-top: 0.5rem;
    }

    .track-card {
        background: var(--bg-white);
        padding: 2rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 1.5rem;
    }

    .progress-steps {
        display: flex;
        justify-content: space-between;
        margin: 2rem 0;
        position: relative;
    }
    .progress-steps::before {
        content: '';
        position: absolute;
        top: 22px;
        left: 8%;
        right: 8%;
        height: 4px;
        background: #e5e0d8;
        z-index: 0;
    }
    .step {
        flex: 1;
        text-align: center;
        position: relative;
        z-index: 1;
    }
    .step .dot {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #e5e0d8;
        color: #999;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        border: 4px solid var(--bg-white);
    }
    .step .label {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .step.active .dot {
        background: var(--primary);
        color: var(--secondary);
        box-shadow: 0 0 0 3px rgba(212, 165, 116, 0.35);
        transform: scale(1.1);
    }
    .step.active .label { color: var(--secondary); }
    .step.done .dot {
        background: #5cb85c;
        color: #fff;
    }
    .step.done .label { color: #5cb85c; }
    .step.cancelled .dot {
        background: #d9534f;
        color: #fff;
    }

    .live-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #d4edda;
        color: #155724;
        padding: 0.35rem 0.9rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .pulse {
        width: 8px;
        height: 8px;
        background: #5cb85c;
        border-radius: 50%;
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0%   { box-shadow: 0 0 0 0 rgba(92,184,92,0.7); }
        70%  { box-shadow: 0 0 0 10px rgba(92,184,92,0); }
        100% { box-shadow: 0 0 0 0 rgba(92,184,92,0); }
    }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin: 1.5rem 0;
    }
    .info-box {
        background: var(--bg-light);
        padding: 1rem 1.25rem;
        border-radius: var(--border-radius-sm);
    }
    .info-box .label {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: var(--text-muted);
        font-weight: 700;
        letter-spacing: 0.5px;
        margin-bottom: 0.35rem;
    }
    .info-box .value {
        font-size: 1rem;
        color: var(--text-dark);
        font-weight: 600;
        line-height: 1.5;
    }
    .info-box .value small {
        font-weight: 400;
        color: var(--text-muted);
    }

    .items-list {
        margin: 1.5rem 0;
        border-top: 1px solid #eee;
        padding-top: 1rem;
    }
    .items-list .item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f5f5f5;
    }
    .items-list .item-row:last-child { border-bottom: none; }
    .items-list .item-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .items-list img {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        object-fit: cover;
    }
    .items-list .item-name {
        font-weight: 600;
    }
    .items-list .item-qty {
        color: var(--text-muted);
        font-size: 0.85rem;
    }
    .items-list .item-price {
        font-weight: 700;
        color: var(--primary-dark);
    }

    .totals {
        background: var(--bg-light);
        padding: 1rem 1.25rem;
        border-radius: var(--border-radius-sm);
        margin-top: 1rem;
    }
    .totals .row {
        display: flex;
        justify-content: space-between;
        padding: 0.35rem 0;
        font-size: 0.95rem;
    }
    .totals .row.grand {
        border-top: 2px solid var(--secondary);
        margin-top: 0.5rem;
        padding-top: 0.75rem;
        font-weight: 700;
        font-size: 1.2rem;
        color: var(--primary-dark);
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        justify-content: center;
        margin-top: 2rem;
    }

    @media (max-width: 640px) {
        .info-grid { grid-template-columns: 1fr; }
        .action-buttons { flex-direction: column; }
        .action-buttons .btn { width: 100%; }
    }
</style>

<div class="track-wrap">

    <!-- Success banner -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="success-banner">
            <div class="checkmark">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Order Placed Successfully!</h2>
            <p>Thank you for ordering from Flavor Haven</p>
            <p style="font-size:0.9rem; opacity:0.85;">
                <?= e($_SESSION['flash']) ?>
            </p>
            <span class="order-num">Order #<?= (int)$order['id'] ?></span>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- Live status card -->
    <div class="track-card">

        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
            <h2 style="font-family:var(--font-heading);margin:0;">Order Status</h2>
            <span class="live-badge">
                <span class="pulse"></span> Live tracking
            </span>
        </div>

        <p style="color:var(--text-muted);font-size:0.9rem;margin-bottom:1rem;">
            Placed on <?= date('F j, Y \a\t g:i A', strtotime($order['order_date'])) ?>
        </p>

        <div class="progress-steps" id="progressSteps">
            <div class="step" data-step="pending">
                <div class="dot"><i class="fas fa-receipt"></i></div>
                <div class="label">Pending</div>
            </div>
            <div class="step" data-step="preparing">
                <div class="dot"><i class="fas fa-fire"></i></div>
                <div class="label">Preparing</div>
            </div>
            <div class="step" data-step="delivered">
                <div class="dot"><i class="fas fa-check"></i></div>
                <div class="label">Delivered</div>
            </div>
        </div>

        <p style="text-align:center;font-size:1.1rem;margin:1.5rem 0;" id="statusText">
            Status: <strong><?= e(ucfirst($order['status'])) ?></strong>
        </p>

    </div>

    <!-- Order details card -->
    <div class="track-card">

        <h2 style="font-family:var(--font-heading);margin:0 0 1rem;">Order Details</h2>

        <div class="info-grid">
            <div class="info-box">
                <div class="label">Order Method</div>
                <div class="value">
                    <?php if ($order['delivery_method'] === 'delivery'): ?>
                        🚚 Delivery
                    <?php else: ?>
                        🏪 Pickup at store
                    <?php endif; ?>
                </div>
            </div>

            <div class="info-box">
                <div class="label">Payment</div>
                <div class="value">
                    <?php
                    $payLabels = [
                        'google_pay' => 'Google Pay',
                        'apple_pay'  => 'Apple Pay',
                        'card'       => 'Card' . ($order['card_last4'] ? ' •••• ' . e($order['card_last4']) : ''),
                    ];
                    echo e($payLabels[$order['payment_method']] ?? 'Unknown');
                    ?>
                </div>
            </div>

            <?php if ($order['delivery_method'] === 'delivery'): ?>
                <div class="info-box" style="grid-column:1/-1;">
                    <div class="label">Delivering To</div>
                    <div class="value">
                        <?= e($order['delivery_address']) ?><br>
                        <?= e($order['delivery_city']) ?> <?= e($order['delivery_postal_code']) ?><br>
                        <small>📞 <?= e($order['delivery_phone']) ?></small>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <h3 style="font-family:var(--font-heading);margin:1.5rem 0 0.5rem;">Items</h3>
        <div class="items-list">
            <?php foreach ($items as $it): ?>
                <div class="item-row">
                    <div class="item-info">
                        <img src="<?= base_url(e($it['image_path'])) ?>" alt="<?= e($it['name']) ?>">
                        <div>
                            <div class="item-name"><?= e($it['name']) ?></div>
                            <div class="item-qty">Quantity: <?= (int)$it['quantity'] ?></div>
                        </div>
                    </div>
                    <div class="item-price">
                        $<?= number_format($it['price'] * $it['quantity'], 2) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="totals">
            <div class="row grand">
                <span>Total Paid</span>
                <span>$<?= number_format($order['total'], 2) ?></span>
            </div>
        </div>

    </div>

    <!-- Action buttons -->
    <div class="action-buttons">
        <a href="<?= base_url('index.php') ?>" class="btn btn-primary">
            <i class="fas fa-home"></i> Back to Home
        </a>
        <a href="<?= base_url('menu.php') ?>" class="btn" style="background:#eee;color:#333;">
            <i class="fas fa-utensils"></i> Order More
        </a>
        <a href="my_orders.php" class="btn" style="background:#eee;color:#333;">
            <i class="fas fa-receipt"></i> All My Orders
        </a>
    </div>

</div>

<script>
(function () {
    const orderId = <?= (int)$order['id'] ?>;
    const apiUrl = '<?= base_url('api/get_order_status.php') ?>?id=' + orderId;
    const steps = document.querySelectorAll('.step');
    const statusText = document.getElementById('statusText');

    const stepOrder = ['pending', 'preparing', 'delivered'];

    function renderStatus(status) {
        const currentIdx = stepOrder.indexOf(status);
        steps.forEach(step => {
            const s = step.getAttribute('data-step');
            step.classList.remove('active', 'done', 'cancelled');
            if (status === 'cancelled') {
                step.classList.add('cancelled');
                return;
            }
            const sIdx = stepOrder.indexOf(s);
            if (sIdx < currentIdx) step.classList.add('done');
            if (sIdx === currentIdx) step.classList.add('active');
        });

        let display = status.charAt(0).toUpperCase() + status.slice(1);
        let color = 'var(--secondary)';
        if (status === 'delivered') color = '#5cb85c';
        if (status === 'cancelled') color = '#d9534f';
        if (status === 'preparing') color = '#5bc0de';

        statusText.innerHTML = 'Status: <strong style="color:' + color + '">' + display + '</strong>';
    }

    async function poll() {
        try {
            const res = await fetch(apiUrl, { cache: 'no-store' });
            if (!res.ok) return;
            const data = await res.json();
            if (data.status) renderStatus(data.status);
        } catch (err) { /* silent */ }
    }

    renderStatus('<?= e($order['status']) ?>');
    poll();
    setInterval(poll, 5000);
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>