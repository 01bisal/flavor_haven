<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

// Optional filter by role
$role_filter = $_GET['role'] ?? 'all';

if ($role_filter !== 'all' && in_array($role_filter, ['admin','member','normal'])) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.email, u.role, u.created_at,
               (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS order_count
        FROM users u
        WHERE u.role = ?
        ORDER BY u.created_at DESC
    ");
    $stmt->execute([$role_filter]);
} else {
    $stmt = $pdo->query("
        SELECT u.id, u.username, u.email, u.role, u.created_at,
               (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS order_count
        FROM users u
        ORDER BY u.created_at DESC
    ");
}
$users = $stmt->fetchAll();

$page_title = "Users";
include __DIR__ . '/../includes/header.php';
?>

<section class="menu-section">

    <h1>Registered Users</h1>
    <p style="margin-bottom:1rem; color:var(--text-muted);">
        <?= count($users) ?> user<?= count($users) === 1 ? '' : 's' ?> found
    </p>

    <!-- Role filter -->
    <div class="quick-actions" style="margin-bottom:1.5rem;">
        <a href="?role=all" class="btn <?= $role_filter === 'all' ? 'btn-primary' : '' ?>" style="<?= $role_filter === 'all' ? '' : 'background:#eee;color:#333;' ?>">All</a>
        <a href="?role=admin" class="btn <?= $role_filter === 'admin' ? 'btn-primary' : '' ?>" style="<?= $role_filter === 'admin' ? '' : 'background:#eee;color:#333;' ?>">Admins</a>
        <a href="?role=member" class="btn <?= $role_filter === 'member' ? 'btn-primary' : '' ?>" style="<?= $role_filter === 'member' ? '' : 'background:#eee;color:#333;' ?>">Members</a>
        <a href="?role=normal" class="btn <?= $role_filter === 'normal' ? 'btn-primary' : '' ?>" style="<?= $role_filter === 'normal' ? '' : 'background:#eee;color:#333;' ?>">Normal</a>
    </div>

    <?php if (empty($users)): ?>
        <div class="alert info">No users found.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Orders</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= (int)$u['id'] ?></td>
                        <td><strong><?= e($u['username']) ?></strong></td>
                        <td><?= e($u['email']) ?></td>
                        <td>
                            <?php
                            $role_colors = [
                                'admin'  => '#B8926A',
                                'member' => '#5cb85c',
                                'normal' => '#6c757d',
                            ];
                            $color = $role_colors[$u['role']] ?? '#999';
                            ?>
                            <span style="display:inline-block;padding:0.2rem 0.7rem;border-radius:50px;background:<?= $color ?>;color:#fff;font-size:0.8rem;font-weight:600;">
                                <?= e(ucfirst($u['role'])) ?>
                            </span>
                        </td>
                        <td><?= (int)$u['order_count'] ?></td>
                        <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p style="margin-top:1.5rem;">
        <a href="dashboard.php" class="btn btn-primary">← Back to Dashboard</a>
    </p>

</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>