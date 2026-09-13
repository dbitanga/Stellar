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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log In — STELLAR</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Goldman:wght@400;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../auth.css">
</head>
<body>

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

</body>
</html>