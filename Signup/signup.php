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
  <title>Sign Up — STELLAR</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Goldman:wght@400;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../auth.css">
</head>
<body>

  <div class="login-wrap">
    <div class="login-card">

      <a href="../index.php" class="logo">STELLAR</a>
      <p class="subtitle">Create your account</p>

      <?php if ($status === 'error' && $message): ?>
        <p class="form-error"><?php echo htmlspecialchars($message); ?></p>
      <?php endif; ?>

      <form method="POST" action="signup_function.php">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" placeholder="Your full name" required>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Choose a username" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" required>

        <label for="mobile_number">Mobile Number</label>
        <input type="text" id="mobile_number" name="mobile_number" placeholder="09XX XXX XXXX" required>

        <label for="delivery_address">Delivery Address</label>
        <textarea id="delivery_address" name="delivery_address" placeholder="Street, Barangay, City, Province" required></textarea>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="At least 8 characters" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>

        <button type="submit" name="signup">Sign Up</button>
      </form>

      <p class="signup-line">Already have an account? <a href="../Login/login.php">Log in</a></p>

    </div>
  </div>

</body>
</html>