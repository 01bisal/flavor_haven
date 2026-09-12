<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

// Handle inline stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock']) && verify_csrf($_POST['csrf'] ?? '')) {
    $item_id = (int)($_POST['item_id'] ?? 0);
    $new_stock = max(0, (int)($_POST['new_stock'] ?? 0));
    if ($item_id > 0) {
        $stmt = $pdo->prepare("UPDATE menu_items SET stock = ? WHERE id = ?");
        $stmt->execute([$new_stock, $item_id]);
        $_SESSION['flash'] = "Stock updated for item #$item_id";
    }
    redirect('manage_menu.php');
}

$filter = $_GET['stock'] ?? 'all';

$sql = "
    SELECT m.*, c.name AS category
    FROM menu_items m
    JOIN categories c ON c.id = m.category_id
";
switch ($filter) {
    case 'available':
        $sql .= " WHERE m.stock > m.low_stock_threshold";
        break;
    case 'low':
        $sql .= " WHERE m.stock > 0 AND m.stock <= m.low_stock_threshold";
        break;
    case 'out':
        $sql .= " WHERE m.stock = 0";
        break;
}
$sql .= " ORDER BY m.category_id, m.name";

$items = $pdo->query($sql)->fetchAll();

$page_title = "Manage Menu";
include __DIR__ . '/../includes/header.php';
?>

<style>
    .stock-pill {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .stock-ok  { background: #d4edda; color: #155724; }
    .stock-low { background: #fff3cd; color: #856404; }
    .stock-out { background: #f8d7da; color: #721c24; }
    .stock-form {
        display: inline-flex;
        gap: 0.35rem;
        align-items: center;
    }
    .stock-form input[type="number"] {
        width: 60px;
        padding: 0.3rem 0.5rem;
        border: 2px solid #E0D6CC;
        border-radius: 6px;
        font-size: 0.85rem;
        text-align: center;
    }
    .stock-form button {
        padding: 0.3rem 0.7rem;
        font-size: 0.75rem;
        border-radius: 6px;
        border: none;
        background: var(--primary);
        color: var(--secondary);
        font-weight: 600;
        cursor: pointer;
        transition: background var(--transition);
    }
    .stock-form button:hover { background: var(--primary-dark); }
    .filter-bar {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    .filter-btn {
        padding: 0.5rem 1.25rem;
        border-radius: 50px;
        border: 2px solid var(--primary);
        background: transparent;
        color: var(--secondary);
        font-weight: 600;
        text-decoration: none;
        font-size: 0.9rem;
        transition: all var(--transition);
    }
    .filter-btn:hover { background: var(--primary-light); }
    .filter-btn.active {
        background: var(--primary);
        color: var(--secondary);
    }
    .toggle-avail-btn {
        background: none;
        border: none;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        transition: all 0.3s;
    }
    .toggle-avail-btn:hover { background: rgba(0,0,0,0.05); }
    .toggle-avail-btn[data-available="1"] { color: #5cb85c; }
    .toggle-avail-btn[data-available="0"] { color: #999; }
</style>

<section class="menu-section">

    <h1>Manage Menu</h1>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert success"><?= e($_SESSION['flash']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <p style="margin:1rem 0;">
        <a href="add_item.php" class="btn btn-primary">+ Add New Item</a>
        <a href="dashboard.php" class="btn" style="background:#eee;color:#333;">← Dashboard</a>
    </p>

    <div class="filter-bar">
        <a href="?stock=all" class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">All Items</a>
        <a href="?stock=available" class="filter-btn <?= $filter === 'available' ? 'active' : '' ?>">✅ Available</a>
        <a href="?stock=low" class="filter-btn <?= $filter === 'low' ? 'active' : '' ?>">⚠️ Low Stock</a>
        <a href="?stock=out" class="filter-btn <?= $filter === 'out' ? 'active' : '' ?>">❌ Out of Stock</a>
    </div>

    <?php if (empty($items)): ?>
        <div class="alert info">No items match this filter.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Update Stock</th>
                    <th>Visibility</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <?php
                    if ($it['stock'] == 0) {
                        $stock_class = 'stock-out';
                        $stock_label = '❌ Out of Stock';
                    } elseif ($it['stock'] <= $it['low_stock_threshold']) {
                        $stock_class = 'stock-low';
                        $stock_label = '⚠️ Low (' . $it['stock'] . ')';
                    } else {
                        $stock_class = 'stock-ok';
                        $stock_label = '✅ ' . $it['stock'] . ' in stock';
                    }
                    ?>
                    <tr>
                        <td>
                            <img src="<?= base_url(e($it['image_path'])) ?>" alt="" />
                        </td>
                        <td><strong><?= e($it['name']) ?></strong></td>
                        <td><?= e($it['category']) ?></td>
                        <td>$<?= number_format($it['price'], 2) ?></td>
                        <td>
                            <span class="stock-pill <?= $stock_class ?>">
                                <?= $stock_label ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" class="stock-form">
                                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                <input type="hidden" name="item_id" value="<?= (int)$it['id'] ?>">
                                <input type="number" name="new_stock" min="0" max="9999" value="<?= (int)$it['stock'] ?>">
                                <button type="submit" name="update_stock" value="1" title="Update stock">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </td>
                        <td>
                            <button type="button"
                                    class="toggle-avail-btn"
                                    data-item-id="<?= (int)$it['id'] ?>"
                                    data-available="<?= (int)$it['is_available'] ?>"
                                    title="Click to toggle menu visibility">
                                <?php if ($it['is_available']): ?>
                                    <i class="fas fa-eye"></i> Visible
                                <?php else: ?>
                                    <i class="fas fa-eye-slash"></i> Hidden
                                <?php endif; ?>
                            </button>
                        </td>
                        <td>
                            <a href="edit_item.php?id=<?= (int)$it['id'] ?>">Edit</a> |
                            <a href="delete_item.php?id=<?= (int)$it['id'] ?>"
                               onclick="return confirm('Delete this item?');"
                               style="color:#C0392B;">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</section>

<script>
(function () {
    const csrf = '<?= csrf_token() ?>';
    const apiUrl = '<?= base_url('api/toggle_availability.php') ?>';

    document.querySelectorAll('.toggle-avail-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const itemId = this.dataset.itemId;
            this.disabled = true;

            try {
                const form = new FormData();
                form.append('csrf', csrf);
                form.append('item_id', itemId);

                const res = await fetch(apiUrl, { method: 'POST', body: form });
                const data = await res.json();

                if (data.success) {
                    if (data.is_available) {
                        this.innerHTML = '<i class="fas fa-eye"></i> Visible';
                        this.dataset.available = '1';
                    } else {
                        this.innerHTML = '<i class="fas fa-eye-slash"></i> Hidden';
                        this.dataset.available = '0';
                    }
                    this.style.background = '#fff8e1';
                    setTimeout(() => this.style.background = '', 600);
                } else {
                    alert('Error: ' + (data.error || 'Unknown'));
                }
            } catch (err) {
                alert('Failed to toggle. Try again.');
            }
            this.disabled = false;
        });
    });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>