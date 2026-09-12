<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();

$stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE email = ? ORDER BY created_at DESC");
$stmt->execute([$user['email']]);
$messages = $stmt->fetchAll();

$page_title = "My Messages";
include __DIR__ . '/../includes/header.php';
?>

<style>
    .msg-card { background:var(--bg-white);padding:1.5rem;border-radius:var(--border-radius);box-shadow:var(--shadow);margin-bottom:1.25rem;border-left:4px solid var(--primary); }
    .msg-card.replied { border-left-color:#5cb85c; }
    .msg-header { display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.5rem; }
    .msg-header h3 { margin:0;font-family:var(--font-heading);font-size:1.15rem; }
    .msg-body { background:var(--bg-light);padding:1rem;border-radius:var(--border-radius-sm);margin:0.75rem 0;line-height:1.6; }
    .reply-block { background:#e7f5ee;border-left:3px solid #5cb85c;padding:1rem;border-radius:var(--border-radius-sm);margin-top:0.75rem; }
    .reply-block .reply-label { font-size:0.75rem;font-weight:700;text-transform:uppercase;color:#2d6a4f;letter-spacing:0.5px;margin-bottom:0.35rem; }
</style>

<section class="menu-section">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
        <h1 style="margin:0;">My Messages</h1>
        <a href="dashboard.php" class="btn" style="background:#eee;color:#333;">← Back to Dashboard</a>
    </div>

    <p style="color:var(--text-muted);margin-bottom:1.5rem;">Your messages to Flavor Haven and replies from our team.</p>

    <?php if (empty($messages)): ?>
        <div class="alert info" role="alert">You haven't sent any messages yet. <a href="<?= base_url('contact.php') ?>">Contact us</a>.</div>
    <?php else: ?>
        <?php foreach ($messages as $m): ?>
            <?php $has_reply = !empty($m['admin_reply']); ?>
            <article class="msg-card <?= $has_reply ? 'replied' : '' ?>">
                <div class="msg-header">
                    <h3><?= e($m['subject']) ?></h3>
                    <span style="font-size:0.85rem;color:var(--text-muted);">Sent <?= date('M j, Y g:i A', strtotime($m['created_at'])) ?></span>
                </div>
                <div class="msg-body"><?= nl2br(e($m['message'])) ?></div>
                <?php if ($has_reply): ?>
                    <div class="reply-block">
                        <div class="reply-label">✅ Reply from Flavor Haven · <?= date('M j, Y g:i A', strtotime($m['replied_at'])) ?></div>
                        <?= nl2br(e($m['admin_reply'])) ?>
                    </div>
                <?php else: ?>
                    <p style="color:var(--text-muted);font-size:0.9rem;font-style:italic;margin-top:0.5rem;">⏳ Awaiting reply from our team...</p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>