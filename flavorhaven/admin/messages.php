<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf'] ?? '')) {
    $message_id = (int)($_POST['message_id'] ?? 0);
    $reply = trim($_POST['reply'] ?? '');

    if ($message_id <= 0) {
        $errors[] = "Invalid message.";
    } elseif (strlen($reply) < 5) {
        $errors[] = "Reply must be at least 5 characters.";
    } elseif (strlen($reply) > 2000) {
        $errors[] = "Reply too long (max 2000 characters).";
    } else {
        $stmt = $pdo->prepare("
            UPDATE contact_messages SET admin_reply = ?, replied_at = NOW(), replied_by = ? WHERE id = ?
        ");
        if ($stmt->execute([$reply, $_SESSION['user_id'], $message_id])) {
            $_SESSION['flash'] = "Reply sent to user.";
            redirect('messages.php');
        } else {
            $errors[] = "Failed to save reply.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $pdo->exec("UPDATE contact_messages SET is_read = 1 WHERE is_read = 0");
}

$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT m.*, u.username AS replied_by_name FROM contact_messages m LEFT JOIN users u ON u.id = m.replied_by";
if ($filter === 'unreplied') $sql .= " WHERE m.admin_reply IS NULL";
elseif ($filter === 'replied') $sql .= " WHERE m.admin_reply IS NOT NULL";
$sql .= " ORDER BY m.created_at DESC";

$messages = $pdo->query($sql)->fetchAll();

$page_title = "Messages";
include __DIR__ . '/../includes/header.php';
?>

<style>
    .msg-card { background:var(--bg-white);padding:1.5rem;border-radius:var(--border-radius);box-shadow:var(--shadow);margin-bottom:1.25rem;border-left:4px solid var(--primary); }
    .msg-card.replied { border-left-color:#5cb85c; }
    .msg-header { display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.75rem; }
    .msg-header h3 { margin:0;font-family:var(--font-heading);font-size:1.15rem; }
    .msg-meta { color:var(--text-muted);font-size:0.85rem;margin-bottom:0.5rem; }
    .msg-body { background:var(--bg-light);padding:1rem;border-radius:var(--border-radius-sm);margin:0.75rem 0;line-height:1.6; }
    .reply-block { background:#e7f5ee;border-left:3px solid #5cb85c;padding:1rem;border-radius:var(--border-radius-sm);margin-top:0.75rem; }
    .reply-block .reply-label { font-size:0.75rem;font-weight:700;text-transform:uppercase;color:#2d6a4f;letter-spacing:0.5px;margin-bottom:0.35rem; }
    .reply-form { margin-top:1rem;padding-top:1rem;border-top:1px dashed #ccc; }
    .filter-bar { display:flex;gap:0.75rem;flex-wrap:wrap;margin:1.5rem 0; }
    .filter-btn { padding:0.5rem 1.25rem;border-radius:50px;border:2px solid var(--primary);background:transparent;color:var(--secondary);font-weight:600;text-decoration:none;font-size:0.9rem; }
    .filter-btn.active { background:var(--primary);color:var(--secondary); }
</style>

<section class="menu-section">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
        <h1 style="margin:0;">Contact Messages</h1>
        <a href="dashboard.php" class="btn" style="background:#eee;color:#333;">← Back to Dashboard</a>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert success" role="alert" style="margin-top:1rem;"><?= e($_SESSION['flash']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert error" role="alert" style="margin-top:1rem;"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <div class="filter-bar">
        <a href="?filter=all" class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">All</a>
        <a href="?filter=unreplied" class="filter-btn <?= $filter === 'unreplied' ? 'active' : '' ?>">Unreplied</a>
        <a href="?filter=replied" class="filter-btn <?= $filter === 'replied' ? 'active' : '' ?>">Replied</a>
    </div>

    <?php if (empty($messages)): ?>
        <div class="alert info">No messages<?= $filter !== 'all' ? " in '{$filter}'" : '' ?> yet.</div>
    <?php else: ?>
        <?php foreach ($messages as $m): ?>
            <?php $has_reply = !empty($m['admin_reply']); ?>
            <article class="msg-card <?= $has_reply ? 'replied' : '' ?>">
                <div class="msg-header">
                    <h3><?= e($m['subject']) ?></h3>
                    <?php if ($has_reply): ?>
                        <span style="color:#5cb85c;font-weight:600;font-size:0.85rem;">✅ Replied</span>
                    <?php else: ?>
                        <span style="color:#f0ad4e;font-weight:600;font-size:0.85rem;">⏳ Awaiting reply</span>
                    <?php endif; ?>
                </div>

                <div class="msg-meta">
                    From: <strong><?= e($m['name']) ?></strong> &lt;<?= e($m['email']) ?>&gt;
                    <?php if ($m['phone']): ?> · 📞 <?= e($m['phone']) ?><?php endif; ?>
                    · <?= date('M j, Y g:i A', strtotime($m['created_at'])) ?>
                </div>

                <div class="msg-body"><?= nl2br(e($m['message'])) ?></div>

                <?php if ($has_reply): ?>
                    <div class="reply-block">
                        <div class="reply-label">Your Reply · <?= date('M j, Y g:i A', strtotime($m['replied_at'])) ?></div>
                        <?= nl2br(e($m['admin_reply'])) ?>
                    </div>
                <?php else: ?>
                    <div class="reply-form">
                        <form method="POST">
                            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                            <input type="hidden" name="message_id" value="<?= (int)$m['id'] ?>">
                            <div class="form-group">
                                <label for="reply_<?= (int)$m['id'] ?>">Your Reply</label>
                                <textarea id="reply_<?= (int)$m['id'] ?>" name="reply" rows="4" required minlength="5" maxlength="2000"
                                          placeholder="Type your reply to <?= e($m['name']) ?>..."
                                          style="width:100%;padding:0.75rem;border:2px solid #E0D6CC;border-radius:var(--border-radius-sm);font-family:var(--font-primary);"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Reply</button>
                        </form>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>

</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>