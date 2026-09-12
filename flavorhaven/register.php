<?php
require_once 'includes/auth.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid session. Please try again.";
    } else {
        $username    = clean($_POST['username'] ?? '');
        $email       = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password    = $_POST['password'] ?? '';
        $confirm     = $_POST['confirm'] ?? '';
        $address     = clean($_POST['address'] ?? '');
        $city        = clean($_POST['city'] ?? '');
        $postal_code = clean($_POST['postal_code'] ?? '');
        $phone       = clean($_POST['phone'] ?? '');

        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username))
            $errors[] = "Username must be 3–20 chars (letters, numbers, underscore).";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors[] = "Please enter a valid email.";
        if (strlen($password) < 8)
            $errors[] = "Password must be at least 8 characters.";
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password))
            $errors[] = "Password needs an uppercase letter and a number.";
        if ($password !== $confirm)
            $errors[] = "Passwords do not match.";
        if ($phone && !preg_match('/^[\+\d\s\-\(\)]{7,15}$/', $phone))
            $errors[] = "Invalid phone number format.";
        if ($postal_code && !preg_match('/^[a-zA-Z0-9\s\-]{3,10}$/', $postal_code))
            $errors[] = "Invalid postal code format.";

        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) $errors[] = "Username or email already taken.";
        }

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users 
                    (username, email, password_hash, role, address, city, postal_code, phone) 
                VALUES (?, ?, ?, 'member', ?, ?, ?, ?)
            ");
            if ($stmt->execute([$username, $email, $hash, $address, $city, $postal_code, $phone])) {
                $success = "Registration successful! You can now <a href='login.php'>log in</a>.";
            } else {
                $errors[] = "Something went wrong. Try again.";
            }
        }
    }
}

$page_title = "Register";
include 'includes/header.php';
?>

<section class="page-header">
    <h1>Create Your Account</h1>
    <p>Join Flavor Haven to order delicious food</p>
</section>

<section class="contact-section">
    <div class="contact-form-container" style="max-width:650px;margin:0 auto;">

        <?php if ($success): ?>
            <div class="alert success" role="alert"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert error" role="alert">
                <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate id="registerForm">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

            <h3 style="font-family:var(--font-heading);margin-bottom:1rem;">Account Details</h3>

            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" id="username" name="username" required minlength="3" maxlength="20"
                       value="<?= e($_POST['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required 
                       value="<?= e($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required minlength="8">
                <small>Min 8 chars, at least one uppercase and one number.</small>
            </div>

            <div class="form-group">
                <label for="confirm">Confirm Password *</label>
                <input type="password" id="confirm" name="confirm" required minlength="8">
            </div>

            <h3 style="font-family:var(--font-heading);margin:2rem 0 1rem;">Delivery Address (optional)</h3>
            <p style="color:var(--text-muted);font-size:0.9rem;margin-bottom:1rem;">
                You can add this now or fill it in at checkout. Required for delivery orders.
            </p>

            <div class="form-group">
                <label for="address">Street Address</label>
                <input type="text" id="address" name="address" maxlength="255"
                       placeholder="123 Main Street, Apt 4"
                       value="<?= e($_POST['address'] ?? '') ?>">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" maxlength="100"
                           value="<?= e($_POST['city'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="postal_code">Postal Code</label>
                    <input type="text" id="postal_code" name="postal_code" maxlength="20"
                           value="<?= e($_POST['postal_code'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" maxlength="20"
                       placeholder="+1 (234) 567-890"
                       value="<?= e($_POST['phone'] ?? '') ?>">
            </div>

            <button type="submit" class="btn btn-primary">Register</button>
            <p style="margin-top:1rem;">Already registered? <a href="login.php">Log in</a></p>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>