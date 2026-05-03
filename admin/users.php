<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

// Fetch all registered users.
$stmt = $pdo->query("SELECT id, full_name, email, role FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . "/includes/header.php";
?>

<div class="action-bar">
    <div class="page-title">
        <h2>Users</h2>
        <p class="text-muted">Manage registered accounts</p>
    </div>
</div>

<div class="admin-table-container">
  <?php if (count($users) === 0): ?>
    <div class="admin-empty">
        <p class="text-muted">No users found.</p>
    </div>
  <?php else: ?>
    <table class="admin-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th class="text-right">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
            <td class="admin-id">#<?= (int)$u["id"] ?></td>
            <td class="admin-name"><?= htmlspecialchars($u["full_name"]) ?></td>
            <td><?= htmlspecialchars($u["email"]) ?></td>
            <td>
                <?php if ($u['role'] === 'admin'): ?>
                    <span class="badge admin-role-badge">Admin</span>
                <?php else: ?>
                    <span class="badge">User</span>
                <?php endif; ?>
            </td>
            <td class="text-right">
                <button class="btn admin-action-small admin-disabled-action" disabled>Edit</button>
            </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
  <?php endif; ?>
</div>

<?php
include __DIR__ . "/includes/footer.php";
?>
