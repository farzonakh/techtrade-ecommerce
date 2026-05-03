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

  // Get the user account by email.
  $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();

  if (!$user || !password_verify($password, $user["password_hash"])) {
    $error = "Invalid email or password";
  } else {
    // Save the logged-in user in the session.
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

// Include the layout after redirects are finished.
include __DIR__ . "/../includes/header.php";
?>

<div class="form-box">
  <div class="text-center mb-4">
    <h2 class="sectionTitle">Welcome Back</h2>
    <p class="sectionSub">Login to access your account</p>
  </div>

  <?php if ($info): ?>
    <div class="form-message form-message-success">
        <?= htmlspecialchars($info) ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="form-message form-message-error">
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

    <button type="submit" class="btn btnPrimary form-submit">Login</button>
  </form>

  <p class="text-center mt-lg text-muted text-xs">
    Don't have an account? <a href="/ecommerce/public/register.php" class="link-primary">Register</a>
  </p>
</div>

<?php
include __DIR__ . "/../includes/footer.php";
?>
