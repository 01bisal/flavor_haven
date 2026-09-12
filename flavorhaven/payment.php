<?php
require_once 'includes/auth.php';
require_login();

if ($_SESSION['role'] === 'admin') {
    $_SESSION['flash'] = "Admins cannot place orders.";
    redirect('cart.php');
}

$stmt = $pdo->prepare("SELECT username, email, address, city, postal_code, phone FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$data = cart_items($pdo);
$items = $data['items'];
$subtotal = $data['subtotal'];

if (empty($items)) {
    $_SESSION['flash'] = "Your cart is empty.";
    redirect('menu.php');
}

$delivery_fee = 5.00;
$tax_rate = 0.05;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid session.";
    } else {
        $delivery_method  = $_POST['delivery_method'] ?? 'delivery';
        $payment_method   = clean($_POST['payment_method'] ?? '');
        $delivery_address = clean($_POST['delivery_address'] ?? '');
        $delivery_city    = clean($_POST['delivery_city'] ?? '');
        $delivery_postal  = clean($_POST['delivery_postal_code'] ?? '');
        $delivery_phone   = clean($_POST['delivery_phone'] ?? '');

        $card_number = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
        $card_name   = clean($_POST['card_name'] ?? '');
        $card_expiry = clean($_POST['card_expiry'] ?? '');
        $card_cvv    = clean($_POST['card_cvv'] ?? '');

        if (!in_array($delivery_method, ['delivery', 'pickup'])) {
            $errors[] = "Invalid delivery method.";
        }

        if ($delivery_method === 'delivery') {
            if (strlen($delivery_address) < 5) $errors[] = "Please enter your delivery address.";
            if (strlen($delivery_city) < 2) $errors[] = "Please enter your city.";
            if (!preg_match('/^[a-zA-Z0-9\s\-]{3,10}$/', $delivery_postal)) $errors[] = "Please enter a valid postal code.";
            if (!preg_match('/^[\+\d\s\-\(\)]{7,15}$/', $delivery_phone)) $errors[] = "Please enter a valid phone number.";
        }

        if (!in_array($payment_method, ['google_pay', 'apple_pay', 'card'])) {
            $errors[] = "Please select a payment method.";
        }

        if ($payment_method === 'card') {
            if (!preg_match('/^\d{13,19}$/', $card_number)) $errors[] = "Please enter a valid card number.";
            if (strlen($card_name) < 2) $errors[] = "Please enter the name on your card.";
            if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $card_expiry)) $errors[] = "Expiry must be MM/YY.";
            if (!preg_match('/^\d{3,4}$/', $card_cvv)) $errors[] = "Please enter a valid CVV.";
        }

        if (empty($errors)) {
            foreach ($items as $it) {
                if ($it['quantity'] > $it['stock']) $errors[] = "{$it['name']}: only {$it['stock']} in stock.";
                if (!$it['is_available']) $errors[] = "{$it['name']} is no longer available.";
            }
        }

        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                $fee = ($delivery_method === 'delivery') ? $delivery_fee : 0;
                $tax = round($subtotal * $tax_rate, 2);
                $total = $subtotal + $tax + $fee;
                $card_last4 = ($payment_method === 'card') ? substr($card_number, -4) : null;

                $stmt = $pdo->prepare("
                    INSERT INTO orders 
                        (user_id, total, status, delivery_method, 
                         delivery_address, delivery_city, delivery_postal_code, delivery_phone,
                         payment_method, card_last4, payment_status)
                    VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, 'paid')
                ");
                $stmt->execute([
                    $_SESSION['user_id'], $total, $delivery_method,
                    $delivery_method === 'delivery' ? $delivery_address : null,
                    $delivery_method === 'delivery' ? $delivery_city : null,
                    $delivery_method === 'delivery' ? $delivery_postal : null,
                    $delivery_method === 'delivery' ? $delivery_phone : null,
                    $payment_method, $card_last4,
                ]);
                $order_id = $pdo->lastInsertId();

                $insert_item = $pdo->prepare("
                    INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)
                ");
                $decrement = $pdo->prepare("
                    UPDATE menu_items SET stock = stock - ? WHERE id = ? AND stock >= ?
                ");

                foreach ($items as $it) {
                    $insert_item->execute([$order_id, $it['id'], $it['quantity'], $it['price']]);
                    $decrement->execute([$it['quantity'], $it['id'], $it['quantity']]);
                    if ($decrement->rowCount() === 0) {
                        throw new Exception("Stock ran out for {$it['name']}.");
                    }
                }

                if ($delivery_method === 'delivery') {
                    $pdo->prepare("
                        UPDATE users SET address = ?, city = ?, postal_code = ?, phone = ? WHERE id = ?
                    ")->execute([$delivery_address, $delivery_city, $delivery_postal, $delivery_phone, $_SESSION['user_id']]);
                }

                $pdo->commit();
                cart_clear();

                $_SESSION['flash'] = "Payment successful! Order #$order_id placed.";
                redirect("member/track_order.php?id=$order_id");
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Payment error: " . $e->getMessage());
                $errors[] = "Payment failed: " . $e->getMessage();
            }
        }
    }
}

$tax = round($subtotal * $tax_rate, 2);
$estimated_delivery_fee = $delivery_fee;
$estimated_total = $subtotal + $tax + $estimated_delivery_fee;

$page_title = "Payment & Checkout";
include 'includes/header.php';
?>

<style>
    .payment-wrap { max-width:1000px;margin:2rem auto;padding:0 1rem;display:grid;grid-template-columns:1.5fr 1fr;gap:2rem;align-items:start; }
    .payment-card { background:var(--bg-white);padding:2rem;border-radius:var(--border-radius);box-shadow:var(--shadow);margin-bottom:1.5rem; }
    .payment-card h2 { font-family:var(--font-heading);font-size:1.3rem;margin-bottom:1rem; }
    .choice-grid { display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem; }
    .choice-card { border:2px solid #e0d6cc;border-radius:var(--border-radius);padding:1.25rem;cursor:pointer;text-align:center;transition:all var(--transition);background:var(--bg-light);position:relative; }
    .choice-card:hover { border-color:var(--primary);background:var(--bg-white); }
    .choice-card input[type="radio"] { position:absolute;opacity:0; }
    .choice-card.selected { border-color:var(--primary);background:var(--primary-light);box-shadow:0 4px 12px rgba(212,165,116,0.3); }
    .choice-card .icon { font-size:2rem;color:var(--primary-dark);margin-bottom:0.5rem; }
    .choice-card .label { font-weight:700;color:var(--secondary); }
    .choice-card .sub { font-size:0.8rem;color:var(--text-muted);margin-top:0.25rem; }
    .summary-item { display:flex;justify-content:space-between;padding:0.4rem 0;font-size:0.9rem; }
    .summary-item.total { border-top:2px solid var(--secondary);margin-top:0.5rem;padding-top:0.75rem;font-weight:700;font-size:1.15rem; }
    .card-fields { display:none; }
    .card-fields.visible { display:block; }
    .card-row { display:grid;grid-template-columns:1fr 1fr;gap:1rem; }
    @media (max-width:768px) { .payment-wrap{grid-template-columns:1fr;} .choice-grid{grid-template-columns:1fr;} .card-row{grid-template-columns:1fr;} }
</style>

<section class="page-header">
    <h1>Payment & Checkout</h1>
    <p>Complete your order</p>
</section>

<div class="payment-wrap">
    <div>
        <?php if ($errors): ?>
            <div class="alert error" role="alert"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <form method="POST" id="paymentForm">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

            <div class="payment-card">
                <h2>1. How would you like your order?</h2>
                <div class="choice-grid">
                    <label class="choice-card selected" data-method="delivery">
                        <input type="radio" name="delivery_method" value="delivery" checked>
                        <div class="icon"><i class="fas fa-truck"></i></div>
                        <div class="label">Delivery</div>
                        <div class="sub">+$<?= number_format($delivery_fee, 2) ?> fee · 30–45 min</div>
                    </label>
                    <label class="choice-card" data-method="pickup">
                        <input type="radio" name="delivery_method" value="pickup">
                        <div class="icon"><i class="fas fa-store"></i></div>
                        <div class="label">Pickup</div>
                        <div class="sub">No fee · Ready in ~20 min</div>
                    </label>
                </div>

                <div id="deliveryFields">
                    <h3 style="font-size:1rem;margin:1rem 0 0.5rem;">Delivery Details</h3>
                    <div class="form-group">
                        <label for="delivery_address">Street Address *</label>
                        <input type="text" id="delivery_address" name="delivery_address" maxlength="255"
                               value="<?= e($_POST['delivery_address'] ?? $user['address'] ?? '') ?>">
                    </div>
                    <div class="card-row">
                        <div class="form-group">
                            <label for="delivery_city">City *</label>
                            <input type="text" id="delivery_city" name="delivery_city" maxlength="100"
                                   value="<?= e($_POST['delivery_city'] ?? $user['city'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="delivery_postal_code">Postal Code *</label>
                            <input type="text" id="delivery_postal_code" name="delivery_postal_code" maxlength="20"
                                   value="<?= e($_POST['delivery_postal_code'] ?? $user['postal_code'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="delivery_phone">Phone Number *</label>
                        <input type="tel" id="delivery_phone" name="delivery_phone" maxlength="20"
                               value="<?= e($_POST['delivery_phone'] ?? $user['phone'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="payment-card">
                <h2>2. Payment Method</h2>
                <div class="choice-grid" style="grid-template-columns:1fr 1fr 1fr;">
                    <label class="choice-card" data-method="google_pay">
                        <input type="radio" name="payment_method" value="google_pay">
                        <div class="icon" style="font-size:1.5rem;"><i class="fab fa-google-pay"></i></div>
                        <div class="label" style="font-size:0.9rem;">Google Pay</div>
                    </label>
                    <label class="choice-card" data-method="apple_pay">
                        <input type="radio" name="payment_method" value="apple_pay">
                        <div class="icon" style="font-size:1.5rem;"><i class="fab fa-apple-pay"></i></div>
                        <div class="label" style="font-size:0.9rem;">Apple Pay</div>
                    </label>
                    <label class="choice-card" data-method="card">
                        <input type="radio" name="payment_method" value="card" checked>
                        <div class="icon" style="font-size:1.5rem;"><i class="fas fa-credit-card"></i></div>
                        <div class="label" style="font-size:0.9rem;">Card</div>
                    </label>
                </div>

                <div id="cardFields" class="card-fields visible">
                    <h3 style="font-size:1rem;margin:1rem 0 0.5rem;">Card Details</h3>
                    <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:1rem;">🔒 Demo only — no real payment is processed.</p>

                    <div class="form-group">
                        <label for="card_name">Name on Card *</label>
                        <input type="text" id="card_name" name="card_name" maxlength="100"
                               value="<?= e($_POST['card_name'] ?? $user['username']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="card_number">Card Number *</label>
                        <input type="text" id="card_number" name="card_number" inputmode="numeric" autocomplete="cc-number"
                               placeholder="4242 4242 4242 4242" maxlength="23">
                    </div>
                    <div class="card-row">
                        <div class="form-group">
                            <label for="card_expiry">Expiry (MM/YY) *</label>
                            <input type="text" id="card_expiry" name="card_expiry" placeholder="12/28" maxlength="5">
                        </div>
                        <div class="form-group">
                            <label for="card_cvv">CVV *</label>
                            <input type="text" id="card_cvv" name="card_cvv" inputmode="numeric" placeholder="123" maxlength="4">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;font-size:1.15rem;padding:1.25rem;">
                <i class="fas fa-lock"></i> Pay $<span id="totalBtn"><?= number_format($estimated_total, 2) ?></span>
            </button>
        </form>
    </div>

    <aside>
        <div class="payment-card" style="position:sticky;top:100px;">
            <h2>Order Summary</h2>
            <?php foreach ($items as $it): ?>
                <div class="summary-item">
                    <span><?= (int)$it['quantity'] ?>× <?= e($it['name']) ?></span>
                    <span>$<?= number_format($it['line_total'], 2) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="summary-item" style="margin-top:0.75rem;padding-top:0.5rem;border-top:1px solid #eee;">
                <span>Subtotal</span><span>$<?= number_format($subtotal, 2) ?></span>
            </div>
            <div class="summary-item"><span>Tax (5%)</span><span>$<?= number_format($tax, 2) ?></span></div>
            <div class="summary-item" id="deliveryFeeRow">
                <span>Delivery Fee</span><span>$<?= number_format($delivery_fee, 2) ?></span>
            </div>
            <div class="summary-item total">
                <span>Total</span><span id="totalDisplay">$<?= number_format($estimated_total, 2) ?></span>
            </div>
            <a href="cart.php" style="display:block;text-align:center;margin-top:1rem;font-size:0.9rem;">← Back to Cart</a>
        </div>
    </aside>
</div>

<script>
(function () {
    const subtotal = <?= json_encode($subtotal) ?>;
    const tax = <?= json_encode($tax) ?>;
    const deliveryFee = <?= json_encode($delivery_fee) ?>;

    const deliveryFields = document.getElementById('deliveryFields');
    const deliveryFeeRow = document.getElementById('deliveryFeeRow');
    const cardFields = document.getElementById('cardFields');

    function updateDeliveryMethod() {
        const val = document.querySelector('input[name="delivery_method"]:checked').value;
        document.querySelectorAll('input[name="delivery_method"]').forEach(r => {
            r.closest('.choice-card').classList.toggle('selected', r.checked);
        });
        if (val === 'pickup') {
            deliveryFields.style.display = 'none';
            deliveryFeeRow.style.display = 'none';
        } else {
            deliveryFields.style.display = 'block';
            deliveryFeeRow.style.display = 'flex';
        }
        updateTotal();
    }

    function updatePaymentMethod() {
        const val = document.querySelector('input[name="payment_method"]:checked').value;
        document.querySelectorAll('input[name="payment_method"]').forEach(r => {
            r.closest('.choice-card').classList.toggle('selected', r.checked);
        });
        cardFields.classList.toggle('visible', val === 'card');
    }

    function updateTotal() {
        const val = document.querySelector('input[name="delivery_method"]:checked').value;
        const fee = (val === 'delivery') ? deliveryFee : 0;
        const total = subtotal + tax + fee;
        document.getElementById('totalDisplay').textContent = '$' + total.toFixed(2);
        document.getElementById('totalBtn').textContent = total.toFixed(2);
    }

    document.querySelectorAll('input[name="delivery_method"]').forEach(r => r.addEventListener('change', updateDeliveryMethod));
    document.querySelectorAll('input[name="payment_method"]').forEach(r => r.addEventListener('change', updatePaymentMethod));

    const cn = document.getElementById('card_number');
    cn?.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 19);
        this.value = v.replace(/(.{4})/g, '$1 ').trim();
    });

    const ce = document.getElementById('card_expiry');
    ce?.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 4);
        if (v.length >= 2) v = v.slice(0, 2) + '/' + v.slice(2);
        this.value = v;
    });

    document.getElementById('card_cvv')?.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 4);
    });

    updateDeliveryMethod();
    updatePaymentMethod();
})();
</script>

<?php include 'includes/footer.php'; ?>