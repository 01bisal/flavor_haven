<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$errors = [];
$success = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid session.";
    } else {
        $address     = clean($_POST['address'] ?? '');
        $city        = clean($_POST['city'] ?? '');
        $postal_code = clean($_POST['postal_code'] ?? '');
        $phone       = clean($_POST['phone'] ?? '');

        if ($phone && !preg_match('/^[\+\d\s\-\(\)]{7,15}$/', $phone))
            $errors[] = "Invalid phone number.";
        if ($postal_code && !preg_match('/^[a-zA-Z0-9\s\-]{3,10}$/', $postal_code))
            $errors[] = "Invalid postal code.";

        if (empty($errors)) {
            $stmt = $pdo->prepare("
                UPDATE users SET address = ?, city = ?, postal_code = ?, phone = ?
                WHERE id = ?
            ");
            $stmt->execute([$address, $city, $postal_code, $phone, $_SESSION['user_id']]);
            $user['address'] = $address;
            $user['city'] = $city;
            $user['postal_code'] = $postal_code;
            $user['phone'] = $phone;
            $success = "Profile updated successfully!";
        }
    }
}

$page_title = "Edit Profile";
include __DIR__ . '/../includes/header.php';
?>

<section class="menu-section" style="max-width:650px;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
        <h1 style="margin:0;">Edit Profile</h1>
        <a href="dashboard.php" class="btn" style="background:#eee;color:#333;">← Back</a>
    </div>

    <?php if ($success): ?>
        <div class="alert success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert error"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="POST" style="background:var(--bg-white);padding:2rem;border-radius:var(--border-radius);box-shadow:var(--shadow);">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <h3 style="font-family:var(--font-heading);margin-bottom:1rem;">Delivery Address</h3>
        <p style="color:var(--text-muted);font-size:0.9rem;margin-bottom:1rem;">
            Used for delivery orders. You can also add it at checkout.
        </p>

        <div class="form-group">
            <label for="address">Street Address</label>
            <input type="text" id="address" name="address" maxlength="255" value="<?= e($user['address'] ?? '') ?>">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" maxlength="100" value="<?= e($user['city'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="postal_code">Postal Code</label>
                <input type="text" id="postal_code" name="postal_code" maxlength="20" value="<?= e($user['postal_code'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" maxlength="20" value="<?= e($user['phone'] ?? '') ?>">
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>