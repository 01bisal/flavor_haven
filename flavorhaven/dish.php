<?php
require_once 'includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('menu.php');
}

$stmt = $pdo->prepare("
    SELECT m.*, c.name AS category_name, c.slug AS category_slug
    FROM menu_items m
    JOIN categories c ON c.id = m.category_id
    WHERE m.id = ?
");
$stmt->execute([$id]);
$dish = $stmt->fetch();

if (!$dish) {
    http_response_code(404);
    $page_title = "Dish Not Found";
    include 'includes/header.php';
    echo '<section class="menu-section"><h1>Dish not found.</h1><a href="menu.php" class="btn btn-primary">Back to Menu</a></section>';
    include 'includes/footer.php';
    exit;
}

$stats = get_rating_stats($pdo, $id);

$stmt = $pdo->prepare("
    SELECT r.*, u.username
    FROM reviews r
    JOIN users u ON u.id = r.user_id
    WHERE r.menu_item_id = ?
    ORDER BY r.created_at DESC
    LIMIT 10
");
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

$can_review = false;
$review_reason = '';

if (!is_logged_in()) {
    $review_reason = 'Please <a href="login.php">log in</a> to leave a review.';
} elseif ($_SESSION['role'] === 'admin') {
    $review_reason = 'Admins cannot post reviews.';
} elseif (has_user_reviewed($pdo, $_SESSION['user_id'], $id)) {
    $review_reason = 'You have already reviewed this dish.';
} elseif (!has_user_ordered($pdo, $_SESSION['user_id'], $id)) {
    $review_reason = 'Only verified buyers can review this dish. Order it first!';
} else {
    $can_review = true;
}

$page_title = $dish['name'];
$meta_desc = mb_substr($dish['description'] ?? '', 0, 155);
include 'includes/header.php';
?>

<style>
    .dish-hero {
        max-width: 1000px;
        margin: 2rem auto;
        padding: 0 1rem;
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 2rem;
        align-items: start;
    }
    .dish-hero img {
        width: 100%;
        height: 350px;
        object-fit: cover;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
    }
    .dish-info-box h1 {
        font-family: var(--font-heading);
        margin-bottom: 0.5rem;
    }
    .dish-price {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--primary-dark);
        margin: 0.5rem 0;
    }
    .dish-meta {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
        margin: 1rem 0;
    }
    .rating-summary {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1rem;
    }
    .rating-summary .avg {
        font-weight: 700;
        font-size: 1.3rem;
        color: var(--secondary);
    }
    .review-card {
        background: var(--bg-white);
        padding: 1.25rem 1.5rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-bottom: 1rem;
        border-left: 4px solid var(--primary);
    }
    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .review-author {
        font-weight: 600;
        color: var(--secondary);
    }
    .review-date {
        color: var(--text-muted);
        font-size: 0.85rem;
    }
    .review-text {
        color: var(--text-dark);
        margin-top: 0.5rem;
        line-height: 1.6;
    }
    .review-form {
        background: var(--bg-white);
        padding: 2rem;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        margin-top: 2rem;
    }
    .star-input {
        display: flex;
        gap: 0.3rem;
        font-size: 1.8rem;
        cursor: pointer;
        margin: 0.5rem 0 1rem;
    }
    .star-input i {
        color: #ccc;
        transition: color 0.15s ease, transform 0.15s ease;
    }
    .star-input i:hover { transform: scale(1.15); }
    .star-input i.active { color: #f0ad4e; }
    @media (max-width: 768px) {
        .dish-hero { grid-template-columns: 1fr; }
        .dish-hero img { height: 250px; }
    }
</style>

<section class="page-header">
    <h1><?= e($dish['name']) ?></h1>
    <p><?= e($dish['category_name']) ?></p>
</section>

<div class="dish-hero">
    <img src="<?= base_url(e($dish['image_path'])) ?>" alt="<?= e($dish['name']) ?>">

    <div class="dish-info-box">
        <div class="rating-summary">
            <span class="avg"><?= $stats['total'] > 0 ? number_format($stats['avg'], 1) : '—' ?></span>
            <div><?= render_stars($stats['avg']) ?></div>
            <span style="color:var(--text-muted);">
                (<?= $stats['total'] ?> review<?= $stats['total'] == 1 ? '' : 's' ?>)
            </span>
        </div>

        <div class="dish-price">$<?= number_format($dish['price'], 2) ?></div>

        <p style="color:var(--text-muted); line-height:1.7;">
            <?= nl2br(e($dish['description'])) ?>
        </p>

        <div class="dish-meta">
            <?php if ((int)$dish['stock'] > 0): ?>
                <span style="color:#5cb85c; font-weight:600;">
                    ✅ In stock (<?= (int)$dish['stock'] ?> available)
                </span>
            <?php else: ?>
                <span style="color:#d9534f; font-weight:600;">❌ Out of stock</span>
            <?php endif; ?>
        </div>

        <?php if (is_logged_in() && $_SESSION['role'] !== 'admin' && (int)$dish['stock'] > 0): ?>
            <form method="POST" action="cart_add.php" style="margin-top:1rem;">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="menu_item_id" value="<?= (int)$dish['id'] ?>">
                <input type="hidden" name="redirect" value="dish.php?id=<?= (int)$dish['id'] ?>">
                <input type="number" name="quantity" value="1" min="1" max="<?= (int)$dish['stock'] ?>"
                       style="width:80px;padding:0.5rem;border:2px solid #E0D6CC;border-radius:8px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-cart-plus"></i> Add to Cart
                </button>
            </form>
        <?php elseif (!is_logged_in()): ?>
            <p style="margin-top:1rem;"><a href="login.php" class="btn btn-primary">Log in to order</a></p>
        <?php endif; ?>
    </div>
</div>

<section class="menu-section">

    <h2 style="font-family:var(--font-heading); margin-bottom:1rem;">
        Reviews <span style="font-size:0.9rem;color:var(--text-muted);font-weight:400;">(<?= $stats['total'] ?>)</span>
    </h2>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert info" role="alert"><?= e($_SESSION['flash']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div id="reviewsContainer">
        <?php if (empty($reviews)): ?>
            <div class="alert info" role="alert" id="noReviewsMsg">
                No reviews yet. Be the first!
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $r): ?>
                <article class="review-card">
                    <div class="review-header">
                        <div>
                            <span class="review-author"><?= e($r['username']) ?></span>
                            <span style="margin-left:0.5rem;"><?= render_stars($r['rating']) ?></span>
                        </div>
                        <span class="review-date"><?= date('M j, Y', strtotime($r['created_at'])) ?></span>
                    </div>
                    <?php if (!empty($r['comment'])): ?>
                        <p class="review-text"><?= nl2br(e($r['comment'])) ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($stats['total'] > 10): ?>
        <div style="text-align:center;margin:1rem 0;">
            <button id="loadMoreBtn" class="btn btn-primary"
                    data-offset="10">
                <i class="fas fa-sync"></i> Load More Reviews
            </button>
        </div>
    <?php endif; ?>

    <?php if ($can_review): ?>
        <div class="review-form">
            <h3 style="font-family:var(--font-heading); margin-bottom:0.5rem;">Write a Review</h3>

            <form method="POST" action="add_review.php">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="menu_item_id" value="<?= (int)$dish['id'] ?>">
                <input type="hidden" name="rating" id="ratingInput" value="5" required>

                <label>Your rating *</label>
                <div class="star-input" id="starInput" role="radiogroup" aria-label="Rating">
                    <i class="fas fa-star active" data-value="1"></i>
                    <i class="fas fa-star active" data-value="2"></i>
                    <i class="fas fa-star active" data-value="3"></i>
                    <i class="fas fa-star active" data-value="4"></i>
                    <i class="fas fa-star active" data-value="5"></i>
                </div>

                <div class="form-group">
                    <label for="comment">Your review (optional)</label>
                    <textarea id="comment" name="comment" rows="4" maxlength="1000"
                              placeholder="Share your experience..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Submit Review</button>
            </form>
        </div>

        <script>
        (function () {
            const stars = document.querySelectorAll('#starInput i');
            const input = document.getElementById('ratingInput');

            function setRating(value) {
                input.value = value;
                stars.forEach(s => {
                    s.classList.toggle('active', parseInt(s.dataset.value) <= value);
                });
            }

            stars.forEach(star => {
                star.addEventListener('click', () => setRating(parseInt(star.dataset.value)));
                star.addEventListener('mouseenter', () => {
                    const v = parseInt(star.dataset.value);
                    stars.forEach(s => s.classList.toggle('active', parseInt(s.dataset.value) <= v));
                });
            });

            document.getElementById('starInput').addEventListener('mouseleave', () => {
                setRating(parseInt(input.value));
            });
        })();
        </script>

    <?php elseif ($review_reason): ?>
        <div class="alert info" role="alert"><?= $review_reason ?></div>
    <?php endif; ?>

    <p style="margin-top:2rem;">
        <a href="menu.php" class="btn" style="background:#eee;color:#333;">← Back to Menu</a>
    </p>

</section>

<script>
(function () {
    const btn = document.getElementById('loadMoreBtn');
    if (!btn) return;

    const container = document.getElementById('reviewsContainer');
    const itemId = <?= (int)$dish['id'] ?>;
    const apiUrl = '<?= base_url('api/get_reviews.php') ?>';

    btn.addEventListener('click', async function () {
        const offset = parseInt(this.dataset.offset);
        this.disabled = true;
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

        try {
            const res = await fetch(apiUrl + '?item_id=' + itemId + '&offset=' + offset + '&limit=10');
            const data = await res.json();

            if (data.reviews && data.reviews.length > 0) {
                const noMsg = document.getElementById('noReviewsMsg');
                if (noMsg) noMsg.remove();

                data.reviews.forEach(r => {
                    const card = document.createElement('article');
                    card.className = 'review-card';
                    card.innerHTML = `
                        <div class="review-header">
                            <div>
                                <span class="review-author">${escapeHtml(r.username)}</span>
                                <span style="margin-left:0.5rem;">${renderStars(r.rating)}</span>
                            </div>
                            <span class="review-date">${r.created_at}</span>
                        </div>
                        ${r.comment ? '<p class="review-text">' + escapeHtml(r.comment).replace(/\n/g, '<br>') + '</p>' : ''}
                    `;
                    container.appendChild(card);
                });
            }

            this.dataset.offset = offset + data.reviews.length;

            if (data.has_more) {
                this.disabled = false;
                this.innerHTML = originalText;
            } else {
                this.parentNode.innerHTML = '<p style="color:var(--text-muted);font-size:0.9rem;">— End of reviews —</p>';
            }
        } catch (err) {
            console.error(err);
            this.disabled = false;
            this.innerHTML = originalText;
        }
    });

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function renderStars(rating) {
        let html = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= Math.floor(rating)) html += '<i class="fas fa-star" style="color:#f0ad4e;"></i>';
            else if (i === Math.floor(rating) + 1 && (rating - Math.floor(rating)) >= 0.5) html += '<i class="fas fa-star-half-alt" style="color:#f0ad4e;"></i>';
            else html += '<i class="far fa-star" style="color:#ccc;"></i>';
        }
        return html;
    }
})();
</script>

<?php include 'includes/footer.php'; ?>