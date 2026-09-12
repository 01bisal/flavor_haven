<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    redirect('manage_menu.php');
}

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
                UPDATE menu_items 
                SET name = ?, description = ?, price = ?, category_id = ?, 
                    image_path = ?, is_available = ?, stock = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $desc, $price, $cat, $img, $avail, $stock, $id]);

            $_SESSION['flash'] = "Item updated successfully!";
            redirect('manage_menu.php');
        }
    }

    // If there were errors, refresh the $item array with submitted values so the form shows them
    $item = array_merge($item, [
        'name'         => $_POST['name'] ?? $item['name'],
        'description'  => $_POST['description'] ?? $item['description'],
        'price'        => $_POST['price'] ?? $item['price'],
        'category_id'  => $_POST['category_id'] ?? $item['category_id'],
        'image_path'   => $_POST['image_path'] ?? $item['image_path'],
        'is_available' => isset($_POST['is_available']) ? 1 : 0,
        'stock'        => $_POST['stock'] ?? $item['stock'],
    ]);
}

$page_title = "Edit Item";
include __DIR__ . '/../includes/header.php';
?>

<section class="menu-section" style="max-width:600px;">

    <h1>Edit: <?= e($item['name']) ?></h1>

    <?php if ($errors): ?>
        <div class="alert error" role="alert">
            <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <!-- Preview current image -->
    <?php if (!empty($item['image_path'])): ?>
        <div style="margin-bottom:1.5rem; text-align:center;">
            <img src="<?= base_url(e($item['image_path'])) ?>" 
                 alt="Current image"
                 style="max-width:200px; border-radius:var(--border-radius); box-shadow:var(--shadow);">
            <p style="font-size:0.85rem; color:var(--text-muted); margin-top:0.5rem;">Current image</p>
        </div>
    <?php endif; ?>

    <form method="POST" style="background:var(--bg-white);padding:2rem;border-radius:var(--border-radius);box-shadow:var(--shadow);">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" required
                   value="<?= e($item['name']) ?>">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?= e($item['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="price">Price ($) *</label>
            <input type="number" id="price" name="price" step="0.01" min="0.01" required
                   value="<?= e($item['price']) ?>">
        </div>

        <div class="form-group">
            <label for="category_id">Category *</label>
            <select id="category_id" name="category_id" required>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id'] ?>"
                        <?= ($c['id'] == $item['category_id']) ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="image_path">Image Path</label>
            <input type="text" id="image_path" name="image_path"
                   value="<?= e($item['image_path']) ?>">
            <small>Relative to project root. E.g. <code>assets/images/salmon.jpg</code></small>
        </div>

        <div class="form-group">
            <label for="stock">Stock Quantity *</label>
            <input type="number" id="stock" name="stock" min="0" max="9999" required
                   value="<?= e($item['stock']) ?>">
            <small>
                Set to <strong>0</strong> = Out of Stock.
                Set to <strong>1–5</strong> = Low Stock warning.
                Above 5 = Available.
            </small>
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_available" <?= $item['is_available'] ? 'checked' : '' ?>>
                Show on menu (available)
            </label>
        </div>

        <button type="submit" class="btn btn-primary">Update Item</button>
        <a href="manage_menu.php" style="margin-left:1rem;">Cancel</a>
    </form>

</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>