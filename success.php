<?php

// Shown after a successful signup — signup_function.php redirects here
// with ?id=<new user's user_id>.

require 'Database/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare('SELECT user_id, username FROM users WHERE user_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Created — STELLAR</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Goldman:wght@400;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="auth.css">
</head>
<body>

  <div class="login-wrap">
    <div class="login-card">

      <a href="index.php" class="logo">STELLAR</a>
      <p class="subtitle">Account created successfully</p>

      <p style="color: var(--text-light); opacity: 0.85; font-size: 14px; text-align: center; margin-bottom: 24px;">
        Welcome, <?php echo htmlspecialchars($user['username']); ?>! Your account is ready.
      </p>

      <a href="Login/login.php" style="display: block; text-align: center; background: var(--text-light); color: #181E28; font-family: 'Goldman', sans-serif; font-size: 15px; border-radius: 6px; padding: 14px; text-decoration: none;">
        Log In
      </a>

      <p class="signup-line"><a href="index.php">Back to STELLAR</a></p>

    </div>
  </div>

</body>
</html>