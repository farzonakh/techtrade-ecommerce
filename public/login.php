<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

$error = "";
$info = "";

if (isset($_GET["registered"])) {
  $info = "Account created! Please login to continue.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"] ?? "");
  $password = $_POST["password"] ?? "";

  $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if (!$user || !password_verify($password, $user["password_hash"])) {
    $error = "Invalid email or password";
  } else {
    // Session is started by auth.php or header.php later, but we need it now for assignment
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    $_SESSION["user"] = [
      "id" => $user["id"],
      "full_name" => $user["full_name"],
      "email" => $user["email"],
      "role" => $user["role"],
    ];

    header("Location: /ecommerce/public/index.php");
    exit;
  }
}

// Include header AFTER logic to avoid "headers already sent" issues if we redirected above
include __DIR__ . "/../includes/header.php";
?>

<div class="form-box">
  <div class="text-center mb-4">
    <h2 class="sectionTitle">Welcome Back</h2>
    <p class="sectionSub">Login to access your account</p>
  </div>

  <?php if ($info): ?>
    <div style="background: rgba(34, 197, 94, 0.1); color: var(--success); padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1rem; text-align: center;">
        <?= htmlspecialchars($info) ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1rem; text-align: center;">
        <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
        <label class="form-label">Email Address</label>
        <input name="email" type="email" class="form-input" required placeholder="john@example.com">
    </div>

    <div class="form-group">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-input" required placeholder="••••••••">
    </div>

    <button type="submit" class="btn btnPrimary" style="width: 100%; margin-top: 1rem;">Login</button>
  </form>

  <p class="text-center mt-lg" style="color: var(--text-muted); font-size: 0.9rem;">
    Don't have an account? <a href="/ecommerce/public/register.php" style="color: var(--primary);">Register</a>
  </p>
</div>

<?php
include __DIR__ . "/../includes/footer.php";
?>
