<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$reviews = $pdo->query("
    SELECT r.*, u.username, m.name AS dish_name, m.id AS dish_id
    FROM reviews r
    JOIN users u ON u.id = r.user_id
    JOIN menu_items m ON m.id = r.menu_item_id
    ORDER BY r.created_at DESC
")->fetchAll();

$page_title = "Manage Reviews";
include __DIR__ . '/../includes/header.php';
?>

<section class="menu-section">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
        <h1 style="margin:0;">Manage Reviews</h1>
        <a href="dashboard.php" class="btn" style="background:#eee;color:#333;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert success" role="alert" style="margin-top:1rem;"><?= e($_SESSION['flash']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <p style="color:var(--text-muted); margin:1rem 0;">
        <?= count($reviews) ?> review<?= count($reviews) === 1 ? '' : 's' ?> total
    </p>

    <?php if (empty($reviews)): ?>
        <div class="alert info">No reviews yet.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Dish</th>
                    <th>Reviewer</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $r): ?>
                    <tr>
                        <td>
                            <a href="<?= base_url('dish.php?id=' . (int)$r['dish_id']) ?>" target="_blank">
                                <strong><?= e($r['dish_name']) ?></strong>
                            </a>
                        </td>
                        <td><?= e($r['username']) ?></td>
                        <td><?= render_stars($r['rating']) ?></td>
                        <td style="max-width:400px;">
                            <?= e(mb_strimwidth($r['comment'] ?? '', 0, 100, '...')) ?>
                        </td>
                        <td><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                        <td>
                            <form method="POST" action="delete_review.php" style="display:inline;">
                                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                <input type="hidden" name="review_id" value="<?= (int)$r['id'] ?>">
                                <button type="submit"
                                        onclick="return confirm('Delete this review?');"
                                        style="background:none;border:none;color:#C0392B;cursor:pointer;">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>