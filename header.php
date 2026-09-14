<?php
// Start session on all pages for authentication & cart tracking
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set base path default if not defined by the including page
$base_path = $base_path ?? '.';

// Track active page for navigation highlight
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>STELLAR Laptops</title>
  
  <script src="script.js" defer></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Goldman:wght@400;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?php echo $base_path; ?>/style.css">
</head>
<body>

  <!-- Master Layout Wrapper -->
  <div class="site-wrapper">

    <!-- 1. HEADER BACKGROUND SECTION -->
    <header class="fade-section site-header">
      <img src="<?php echo $base_path; ?>/Images/Homepage/Header.jpg" alt="STELLAR header background" class="section-img">

      <nav class="site-nav">
        <a href="<?php echo $base_path; ?>/index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">Home</a>
        <a href="<?php echo $base_path; ?>/products.php" class="<?php echo $current_page === 'products.php' ? 'active' : ''; ?>">Products</a>
        <a href="<?php echo $base_path; ?>/index.php#about-us">About Us</a>
      </nav>

      <div class="header-hotspot">
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="<?php echo $base_path; ?>/Cart/cart.php" class="account-icon-link" aria-label="Cart">
            <img src="<?php echo $base_path; ?>/Images/Account/Cart.png" alt="Cart">
          </a>
          <a href="<?php echo $base_path; ?>/Account/account.php" class="account-icon-link" aria-label="Account">
            <img src="<?php echo $base_path; ?>/Images/Account/Account.png" alt="Account">
          </a>
          <a href="<?php echo $base_path; ?>/Logout/logout.php" class="logout-link" aria-label="Log out">Log Out</a>
        <?php elseif (!isset($hide_signin) || !$hide_signin): ?>
          <a href="<?php echo $base_path; ?>/Login/login.php" class="signin-link" aria-label="Sign in">Sign In</a>
        <?php endif; ?>
      </div>
    </header>