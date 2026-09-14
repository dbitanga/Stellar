<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$status  = $_GET['status'] ?? null;
$message = $_GET['message'] ?? null;

$base_path = '..';
$hide_signin = true;
include '../header.php';
?>

<link rel="stylesheet" href="../auth.css">

<main class="account-wrapper login-page-wrapper">
  <div class="login-wrap">
    <div class="login-card">

      <a href="../index.php" class="logo">STELLAR</a>
      <p class="subtitle">Log in to your account</p>

      <?php if ($status === 'error' && $message): ?>
        <p class="form-error"><?php echo htmlspecialchars($message); ?></p>
      <?php endif; ?>

      <form method="POST" action="login_function.php">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Your username" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required>

        <button type="submit" name="login">Log In</button>
      </form>

      <p class="signup-line">Don't have an account? <a href="../Signup/signup.php">Sign up</a></p>

    </div>
  </div>
</main>

<?php include '../footer.php'; ?>