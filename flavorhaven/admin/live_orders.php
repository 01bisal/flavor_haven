<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$page_title = "Live Orders";
include __DIR__ . '/../includes/header.php';
?>

<style>
    .live-header { display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1rem; }
    .live-badge { display:inline-flex;align-items:center;gap:0.5rem;background:#d4edda;color:#155724;padding:0.4rem 1rem;border-radius:50px;font-weight:600;font-size:0.9rem; }
    .pulse { width:10px;height:10px;background:#5cb85c;border-radius:50%;animation:pulse 1.5s infinite; }
    @keyframes pulse { 0%{box-shadow:0 0 0 0 rgba(92,184,92,0.7);} 70%{box-shadow:0 0 0 12px rgba(92,184,92,0);} 100%{box-shadow:0 0 0 0 rgba(92,184,92,0);} }
    .order-card { background:var(--bg-white);border-radius:var(--border-radius);box-shadow:var(--shadow);padding:1.5rem;margin-bottom:1rem;border-left:6px solid var(--primary);transition:all var(--transition); }
    .order-card.new-order { animation:highlightNew 1.5s ease-out; }
    @keyframes highlightNew { 0%{background:#fff8e1;transform:scale(1.01);} 100%{background:var(--bg-white);transform:scale(1);} }
    .order-card.status-pending { border-left-color:#f0ad4e; }
    .order-card.status-preparing { border-left-color:#5bc0de; }
    .order-card-header { display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid #eee; }
    .order-card h3 { margin:0;font-family:var(--font-heading); }
    .order-meta-grid { display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin:1rem 0; }
    .order-meta-box { background:var(--bg-light);padding:0.9rem 1rem;border-radius:var(--border-radius-sm); }
    .order-meta-box .label { font-size:0.75rem;text-transform:uppercase;color:var(--text-muted);font-weight:700;letter-spacing:0.5px;margin-bottom:0.35rem; }
    .order-meta-box .value { font-size:0.95rem;font-weight:600;color:var(--text-dark);line-height:1.5; }
    .delivery-tag { display:inline-flex;align-items:center;gap:0.35rem;padding:0.3rem 0.75rem;border-radius:50px;font-weight:700;font-size:0.85rem; }
    .delivery-tag.delivery { background:#d1ecf1;color:#0c5460; }
    .delivery-tag.pickup { background:#fff3cd;color:#856404; }
    .order-items-live { list-style:none;padding:0;margin:0.5rem 0 1rem;font-size:0.95rem; }
    .order-items-live li { padding:0.3rem 0;display:flex;justify-content:space-between; }
    .order-items-live li::before { content:'•';color:var(--primary-dark);font-weight:700;margin-right:0.4rem; }
    .order-actions { display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:1rem;padding-top:1rem;border-top:1px solid #eee; }
    .btn-status { padding:0.6rem 1.25rem;border-radius:50px;border:none;font-weight:600;cursor:pointer;font-size:0.9rem;color:#fff;transition:all var(--transition); }
    .btn-status:hover { transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,0.15); }
    .btn-preparing { background:#5bc0de; }
    .btn-delivered { background:#5cb85c; }
    .btn-cancelled { background:#d9534f; }
    .status-pill { display:inline-block;padding:0.25rem 0.85rem;border-radius:50px;font-size:0.75rem;font-weight:700;text-transform:uppercase;color:#fff;letter-spacing:0.5px; }
    .status-pill.status-pending { background:#f0ad4e; }
    .status-pill.status-preparing { background:#5bc0de; }
    .empty-state { text-align:center;padding:3rem 1rem;background:var(--bg-white);border-radius:var(--border-radius);box-shadow:var(--shadow);color:var(--text-muted); }
    @media (max-width:640px) { .order-meta-grid { grid-template-columns:1fr; } }
</style>

<section class="menu-section">
    <div class="live-header">
        <div>
            <h1 style="margin:0;">🔔 Active Orders</h1>
            <p style="color:var(--text-muted);margin:0.25rem 0 0;">Pending & preparing orders appear here — completed ones are in <a href="view_orders.php">All Orders</a></p>
        </div>
        <div style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
            <span class="live-badge"><span class="pulse"></span> Live</span>
            <a href="dashboard.php" class="btn" style="background:#eee;color:#333;">← Dashboard</a>
        </div>
    </div>

    <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:1rem;">
        Last updated: <span id="lastUpdated">just now</span> · Auto-refresh every 5s
    </p>

    <div id="ordersContainer">
        <div class="empty-state">
            <i class="fas fa-spinner fa-spin" style="font-size:2rem;color:var(--primary);"></i>
            <p style="margin-top:1rem;">Loading active orders...</p>
        </div>
    </div>
</section>

<script>
(function () {
    const apiUrl = '<?= base_url('api/get_live_orders.php') ?>';
    const container = document.getElementById('ordersContainer');
    const lastUpdatedEl = document.getElementById('lastUpdated');
    const csrfToken = '<?= csrf_token() ?>';
    const seenOrders = new Set();
    let firstLoad = true;

    function timeAgo(dateStr) {
        const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
        if (diff < 60) return diff + 's ago';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        return Math.floor(diff / 86400) + 'd ago';
    }

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function renderOrder(o) {
        const isNew = !firstLoad && !seenOrders.has(o.id);
        const itemLines = o.items.map(it =>
            `<li><span>${it.quantity}× ${esc(it.name)}</span><span>$${(it.price * it.quantity).toFixed(2)}</span></li>`
        ).join('');

        let deliveryBlock = '';
        if (o.delivery_method === 'delivery') {
            deliveryBlock = `
                <div class="order-meta-box">
                    <div class="label">📍 Delivery To</div>
                    <div class="value">${esc(o.delivery_address)}<br>${esc(o.delivery_city)} ${esc(o.delivery_postal_code)}<br>📞 ${esc(o.delivery_phone)}</div>
                </div>`;
        } else {
            deliveryBlock = `<div class="order-meta-box"><div class="label">🏪 Pickup</div><div class="value">Customer will pick up at store</div></div>`;
        }

        const payLabels = { 'google_pay':'Google Pay','apple_pay':'Apple Pay','card':'Card' + (o.card_last4 ? ' •••• ' + o.card_last4 : '') };
        const payLabel = payLabels[o.payment_method] || 'Unknown';

        let actions = '';
        if (o.status === 'pending') {
            actions = `<button class="btn-status btn-preparing" onclick="changeStatus(${o.id}, 'preparing')"><i class="fas fa-fire"></i> Start Preparing</button>
                       <button class="btn-status btn-cancelled" onclick="changeStatus(${o.id}, 'cancelled')"><i class="fas fa-times"></i> Cancel</button>`;
        } else if (o.status === 'preparing') {
            actions = `<button class="btn-status btn-delivered" onclick="changeStatus(${o.id}, 'delivered')"><i class="fas fa-check"></i> Mark Delivered</button>
                       <button class="btn-status btn-cancelled" onclick="changeStatus(${o.id}, 'cancelled')"><i class="fas fa-times"></i> Cancel</button>`;
        }

        const methodTag = o.delivery_method === 'delivery'
            ? '<span class="delivery-tag delivery">🚚 Delivery</span>'
            : '<span class="delivery-tag pickup">🏪 Pickup</span>';

        return `
            <article class="order-card status-${o.status} ${isNew ? 'new-order' : ''}">
                <div class="order-card-header">
                    <div>
                        <h3>Order #${o.id} ${methodTag}</h3>
                        <small style="color:var(--text-muted);">by <strong>${esc(o.username)}</strong> · ${timeAgo(o.order_date)}</small>
                    </div>
                    <div style="text-align:right;">
                        <span class="status-pill status-${o.status}">${o.status}</span>
                        <div style="font-weight:700;color:var(--primary-dark);font-size:1.3rem;margin-top:0.35rem;">$${o.total.toFixed(2)}</div>
                    </div>
                </div>
                <div class="order-meta-grid">
                    ${deliveryBlock}
                    <div class="order-meta-box">
                        <div class="label">💳 Payment</div>
                        <div class="value">${payLabel}</div>
                    </div>
                </div>
                <div style="font-size:0.75rem;text-transform:uppercase;color:var(--text-muted);font-weight:700;letter-spacing:0.5px;margin-bottom:0.25rem;">Order Items</div>
                <ul class="order-items-live">${itemLines}</ul>
                <div class="order-actions">${actions}</div>
            </article>`;
    }

    async function fetchOrders() {
        try {
            const res = await fetch(apiUrl, { cache: 'no-store' });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            if (!data.orders || data.orders.length === 0) {
                container.innerHTML = `<div class="empty-state"><i class="fas fa-check-circle" style="font-size:2.5rem;color:#5cb85c;"></i><p style="margin-top:1rem;">All caught up! No pending or preparing orders.</p></div>`;
            } else {
                container.innerHTML = data.orders.map(renderOrder).join('');
            }
            data.orders.forEach(o => seenOrders.add(o.id));
            lastUpdatedEl.textContent = new Date().toLocaleTimeString();
            firstLoad = false;
        } catch (err) { console.error('Live orders error:', err); }
    }

    window.changeStatus = async function (orderId, newStatus) {
        try {
            const form = new FormData();
            form.append('csrf', csrfToken);
            form.append('order_id', orderId);
            form.append('status', newStatus);
            const res = await fetch('<?= base_url('admin/update_order_status.php') ?>', { method:'POST', body: form });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            if (data.success) fetchOrders();
            else alert('Failed: ' + (data.error || 'Unknown error'));
        } catch (err) { console.error('Update error:', err); alert('Failed to update order.'); }
    };

    fetchOrders();
    setInterval(fetchOrders, 5000);
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>