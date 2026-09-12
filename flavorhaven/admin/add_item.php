<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf'] ?? '')) {
        $errors[] = "Invalid session.";
    } else {
        $name  = clean($_POST['name'] ?? '');
        $desc  = clean($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $cat   = (int)($_POST['category_id'] ?? 0);
        $img   = clean($_POST['image_path'] ?? '');
        $avail = isset($_POST['is_available']) ? 1 : 0;
        $stock = max(0, (int)($_POST['stock'] ?? 20));

        if (strlen($name) < 2) $errors[] = "Name is too short.";
        if ($price <= 0) $errors[] = "Price must be positive.";
        if ($cat <= 0) $errors[] = "Select a category.";
        if ($stock < 0) $errors[] = "Stock cannot be negative.";

        if (empty($errors)) {
            $stmt = $pdo->prepare("
                INSERT INTO menu_items 
                    (name, description, price, category_id, image_path, is_available, stock, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $desc, $price, $cat, $img, $avail, $stock, $_SESSION['user_id']]);
            $_SESSION['flash'] = "Item added successfully!";
            redirect('manage_menu.php');
        }
    }
}

$page_title = "Add Item";
include __DIR__ . '/../includes/header.php';
?>

<section class="menu-section" style="max-width:600px;">

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert success"><?= e($_SESSION['flash']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <h1>Add New Menu Item</h1>

    <?php if ($errors): ?>
        <div class="alert error" role="alert">
            <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="POST" style="background:var(--bg-white);padding:2rem;border-radius:var(--border-radius);box-shadow:var(--shadow);">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" required
                   value="<?= e($_POST['name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?= e($_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="price">Price ($) *</label>
            <input type="number" id="price" name="price" step="0.01" min="0.01" required
                   value="<?= e($_POST['price'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="category_id">Category *</label>
            <select id="category_id" name="category_id" required>
                <option value="">Choose...</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id'] ?>"
                        <?= (($_POST['category_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="image_path">Image Path</label>
            <input type="text" id="image_path" name="image_path"
                   placeholder="assets/images/example.jpg"
                   value="<?= e($_POST['image_path'] ?? '') ?>">
            <small>Relative to project root. E.g. <code>assets/images/salmon.jpg</code></small>
        </div>

        <div class="form-group">
            <label for="stock">Stock Quantity *</label>
            <input type="number" id="stock" name="stock" min="0" max="9999" required
                   value="<?= e($_POST['stock'] ?? '20') ?>">
            <small>
                Set to <strong>0</strong> to mark as <em>Out of Stock</em>.
                Set to <strong>1–5</strong> to trigger a <em>Low Stock</em> warning.
                Above 5 = Available.
            </small>
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_available" checked> Show on menu (available)
            </label>
        </div>

        <button type="submit" class="btn btn-primary">Add Item</button>
        <a href="manage_menu.php" style="margin-left:1rem;">Cancel</a>
    </form>

</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>