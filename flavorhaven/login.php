<?php
require_once 'includes/auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid session.";
    } else {
        $username = clean($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $errors[] = "Please fill in both fields.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];
                redirect($user['role'] === 'admin' ? 'admin/dashboard.php' : 'member/dashboard.php');
            } else {
                $errors[] = "Invalid credentials.";
            }
        }
    }
}

$page_title = "Login";
include 'includes/header.php';
?>

<section class="page-header"><h1>Login</h1></section>

<section class="contact-section">
    <div class="contact-form-container" style="max-width:500px;margin:0 auto;">
        <?php if ($errors): ?>
            <div class="alert error" style="padding:1rem;background:#f8d7da;color:#721c24;border-radius:8px;margin-bottom:1rem;" role="alert">
                <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <div class="form-group">
                <label for="username">Username or Email *</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
            <p style="margin-top:1rem;">No account? <a href="register.php">Register here</a></p>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>