<?php
declare(strict_types=1);



if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . "/lang.php";

/** True if logged in */
function is_logged_in(): bool
{
  return isset($_SESSION["user"]) && is_array($_SESSION["user"]);
}

/** Current user */
function current_user(): ?array
{
  return $_SESSION["user"] ?? null;
}

/** Force login */
function require_login(): void
{
  if (!is_logged_in()) {
    header("Location: /ecommerce/public/login.php");
    exit;
  }
}

/** Redirect away from login/register if already logged in */
function redirect_if_logged_in(): void
{
  if (is_logged_in()) {
    header("Location: /ecommerce/public/index.php");
    exit;
  }
}

/** Admin-only */
function require_admin(): void
{
  require_login();
  $role = $_SESSION["user"]["role"] ?? "user";
  if ($role !== "admin") {
    http_response_code(403);
    die("Admins only");
  }
}

/** Logout */
function logout_user(): void
{
  $_SESSION = [];

  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
      session_name(),
      "",
      time() - 42000,
      $params["path"],
      $params["domain"] ?? "",
      $params["secure"] ?? false,
      $params["httponly"] ?? true
    );
  }

  session_destroy();
}
