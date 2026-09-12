<?php
require_once 'includes/auth.php';

$categories = $pdo->query("SELECT * FROM categories ORDER BY id")->fetchAll();

$items = $pdo->query("
    SELECT m.*, c.slug AS category_slug
    FROM menu_items m
    JOIN categories c ON c.id = m.category_id
    WHERE m.is_available = 1 AND m.stock > 0
    ORDER BY m.category_id, m.name
")->fetchAll();

$page_title = "Our Menu";
$meta_desc = "Explore our delicious menu at Flavor Haven.";
include 'includes/header.php';
?>

<section class="page-header">
    <h1>Our Menu</h1>
    <p>Explore our carefully crafted dishes made with the freshest ingredients</p>
</section>

<nav class="menu-nav" aria-label="Menu categories">
    <ul>
        <li><button class="menu-category-btn active" data-category="all">All</button></li>
        <?php foreach ($categories as $cat): ?>
            <li>
                <button class="menu-category-btn" data-category="<?= e($cat['slug']) ?>">
                    <?= e($cat['name']) ?>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

<section class="menu-section" aria-labelledby="menu-heading">
    <h2 id="menu-heading" class="sr-only">Menu Items</h2>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert info" role="alert"><?= e($_SESSION['flash']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div class="alert info" role="alert">
            No dishes are currently available. Please check back soon!
        </div>
    <?php else: ?>
        <div class="menu-grid">
            <?php foreach ($items as $item): ?>
                <?php
                $stock = (int)$item['stock'];
                $threshold = (int)$item['low_stock_threshold'];
                $stock_note = '';
                if ($stock <= $threshold) {
                    $stock_note = '<span style="color:#f0ad4e;font-size:0.8rem;font-weight:600;">⚠️ Only ' . $stock . ' left</span>';
                }
                $rstat = get_rating_stats($pdo, $item['id']);
                ?>
                <article class="menu-item" data-category="<?= e($item['category_slug']) ?>">
                    <img src="<?= base_url(e($item['image_path'])) ?>" 
                         alt="<?= e($item['name']) ?>" loading="lazy" />
                    <div class="menu-item-info">
                        <div class="menu-item-header">
                            <h3>
                                <a href="dish.php?id=<?= (int)$item['id'] ?>" style="color:inherit;text-decoration:none;">
                                    <?= e($item['name']) ?>
                                </a>
                            </h3>
                            <span class="price">$<?= number_format($item['price'], 2) ?></span>
                        </div>

                        <?php if ($rstat['total'] > 0): ?>
                            <div style="font-size:0.85rem;margin-bottom:0.35rem;">
                                <?= render_stars($rstat['avg']) ?>
                                <span style="color:var(--text-muted);">
                                    <?= number_format($rstat['avg'], 1) ?> (<?= $rstat['total'] ?>)
                                </span>
                            </div>
                        <?php else: ?>
                            <div style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.35rem;">
                                No reviews yet
                            </div>
                        <?php endif; ?>

                        <p><?= e($item['description']) ?></p>
                        <span class="badge"><?= e($item['category_slug']) ?></span>

                        <?php if ($stock_note): ?>
                            <div style="margin-top:0.35rem;"><?= $stock_note ?></div>
                        <?php endif; ?>

                        <?php if (is_logged_in() && $_SESSION['role'] !== 'admin'): ?>
                            <form method="POST" action="cart_add.php" style="margin-top:0.5rem;">
                                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                <input type="hidden" name="menu_item_id" value="<?= (int)$item['id'] ?>">
                                <input type="hidden" name="redirect" value="menu.php">
                                <input type="number" name="quantity" value="1" min="1" max="<?= $stock ?>"
                                       aria-label="Quantity" style="width:70px;">
                                <button type="submit" class="btn btn-primary" 
                                        style="padding:0.4rem 1rem;font-size:0.85rem;">
                                    <i class="fas fa-cart-plus" aria-hidden="true"></i> Add to Cart
                                </button>
                            </form>
                        <?php elseif (!is_logged_in()): ?>
                            <p style="margin-top:0.5rem;font-size:0.85rem;">
                                <a href="login.php">Log in</a> to order
                            </p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>