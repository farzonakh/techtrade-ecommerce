<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_admin();

// Fetch all users
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
    <div style="padding: 3rem; text-align:center;">
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
            <td style="color:var(--text-muted); font-family:monospace;">#<?= (int)$u["id"] ?></td>
            <td style="font-weight:600;"><?= htmlspecialchars($u["full_name"]) ?></td>
            <td><?= htmlspecialchars($u["email"]) ?></td>
            <td>
                <?php if ($u['role'] === 'admin'): ?>
                    <span class="badge" style="background:rgba(99, 102, 241, 0.1); color:var(--primary);">Admin</span>
                <?php else: ?>
                    <span class="badge">User</span>
                <?php endif; ?>
            </td>
            <td class="text-right">
                <button class="btn" style="padding: 0.25rem 0.75rem; font-size:0.8rem; opacity:0.5; cursor:not-allowed;" disabled>Edit</button>
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
