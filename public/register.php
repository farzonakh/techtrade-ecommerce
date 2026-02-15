<?php
declare(strict_types=1);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";

$error = "";
$full_name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $full_name = trim($_POST["full_name"] ?? "");
  $email = trim($_POST["email"] ?? "");
  $password = $_POST["password"] ?? "";

  if ($full_name === "" || $email === "" || $password === "") {
    $error = "Please fill in all fields";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "That email doesn’t look valid";
  } elseif (strlen($password) < 6) {
    $error = "Password must be at least 6 characters";
  } else {
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
      $error = "This email is already registered";
    } else {
      $hash = password_hash($password, PASSWORD_BCRYPT);

      $stmt = $pdo->prepare(
        "INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, 'user')"
      );
      $stmt->execute([$full_name, $email, $hash]);

      header("Location: /ecommerce/public/login.php?registered=1");
      exit;
    }
  }
}

include __DIR__ . "/../includes/header.php";
?>

<div class="form-box">
  <div class="text-center mb-4">
    <h2 class="sectionTitle">Join TechTrade</h2>
    <p class="sectionSub">Create your account today</p>
  </div>

  <?php if ($error): ?>
    <div style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1rem; text-align: center;">
        <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
        <label class="form-label">Full Name</label>
        <input name="full_name" class="form-input" value="<?= htmlspecialchars($full_name) ?>" required placeholder="John Doe">
    </div>

    <div class="form-group">
        <label class="form-label">Email Address</label>
        <input name="email" type="email" class="form-input" value="<?= htmlspecialchars($email) ?>" required placeholder="john@example.com">
    </div>

    <div class="form-group">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-input" required placeholder="Min 6 characters">
    </div>

    <button type="submit" class="btn btnPrimary" style="width: 100%; margin-top: 1rem;">Create Account</button>
  </form>

  <p class="text-center mt-lg" style="color: var(--text-muted); font-size: 0.9rem;">
    Already have an account? <a href="/ecommerce/public/login.php" style="color: var(--primary);">Login</a>
  </p>
</div>

<?php
include __DIR__ . "/../includes/footer.php";
?>
