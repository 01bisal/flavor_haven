<?php
require_once 'includes/auth.php';

$data = cart_items($pdo);
$items = $data['items'];
$subtotal = $data['subtotal'];

// Calculate fees (example: 5% tax)
$tax = $subtotal * 0.05;
$total = $subtotal + $tax;

$page_title = "Your Cart";
$meta_desc = "Review your Flavor Haven order.";
include 'includes/header.php';
?>

<section class="page-header">
    <h1>Your Cart</h1>
    <p>Review your items and place your order</p>
</section>

<section class="menu-section">

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert info" role="alert"><?= e($_SESSION['flash']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div class="alert info" role="alert">
            Your cart is empty. <a href="menu.php">Browse the menu</a>.
        </div>
    <?php else: ?>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:1rem;">
                                <img src="<?= base_url(e($it['image_path'])) ?>"
                                     alt="<?= e($it['name']) ?>"
                                     style="width:60px;height:60px;object-fit:cover;border-radius:8px;">
                                <div>
                                    <strong><?= e($it['name']) ?></strong><br>
                                    <small style="color:var(--text-muted);">
                                        Stock: <?= (int)$it['stock'] ?>
                                        <?php if ($it['stock'] <= 5): ?>
                                            <span style="color:#f0ad4e;">⚠️ Low</span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>$<?= number_format($it['price'], 2) ?></td>
                        <td>
                            <form method="POST" action="cart_update.php"
                                  style="display:flex;gap:0.35rem;align-items:center;">
                                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                <input type="hidden" name="item_id" value="<?= (int)$it['id'] ?>">
                                <input type="number" name="quantity" min="0" max="<?= (int)$it['stock'] ?>"
                                       value="<?= (int)$it['quantity'] ?>"
                                       style="width:70px;padding:0.3rem 0.5rem;border:2px solid #E0D6CC;border-radius:6px;text-align:center;">
                                <button type="submit" class="btn btn-sm"
                                        style="padding:0.3rem 0.7rem;font-size:0.75rem;border-radius:6px;background:var(--primary);color:var(--secondary);font-weight:600;border:none;cursor:pointer;">
                                    <i class="fas fa-sync"></i> Update
                                </button>
                            </form>
                        </td>
                        <td><strong>$<?= number_format($it['line_total'], 2) ?></strong></td>
                        <td>
                            <form method="POST" action="cart_remove.php" style="display:inline;">
                                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                <input type="hidden" name="item_id" value="<?= (int)$it['id'] ?>">
                                <button type="submit" onclick="return confirm('Remove this item?');"
                                        style="background:none;border:none;color:#C0392B;cursor:pointer;font-size:1.1rem;"
                                        title="Remove item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align:right;padding-top:1rem;"><strong>Subtotal:</strong></td>
                    <td colspan="2" style="padding-top:1rem;">
                        <strong>$<?= number_format($subtotal, 2) ?></strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align:right;">Tax (5%):</td>
                    <td colspan="2">$<?= number_format($tax, 2) ?></td>
                </tr>
                <tr style="background:var(--bg-light);">
                    <td colspan="3" style="text-align:right;"><strong>Total:</strong></td>
                    <td colspan="2"><strong style="color:var(--primary-dark);font-size:1.15rem;">$<?= number_format($total, 2) ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top:2rem; display:flex; gap:1rem; flex-wrap:wrap; justify-content:space-between;">
            <div>
                <a href="menu.php" class="btn" style="background:#eee;color:#333;">← Continue Shopping</a>
                <form method="POST" action="cart_clear.php" style="display:inline;">
                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                    <button type="submit" onclick="return confirm('Empty your cart?');"
                            class="btn" style="background:#eee;color:#C0392B;">
                        <i class="fas fa-trash"></i> Empty Cart
                    </button>
                </form>
            </div>
            <a href="payment.php" class="btn btn-primary" style="font-size:1.1rem;padding:1rem 2rem;">
                <i class="fas fa-credit-card" aria-hidden="true"></i> Proceed to Payment
            </a>
        </div>

    <?php endif; ?>

</section>

<?php include 'includes/footer.php'; ?>