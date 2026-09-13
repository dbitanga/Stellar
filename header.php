<?php
// Start session on all pages for authentication & cart tracking
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Track active page for navigation highlight
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>STELLAR Laptops</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Goldman:wght@400;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Master Layout Wrapper -->
  <div class="site-wrapper">

    <!-- 1. HEADER BACKGROUND SECTION -->
    <header class="fade-section site-header">
      <img src="Images/Homepage/Header.jpg" alt="STELLAR header background" class="section-img">

      <nav class="site-nav">
        <a href="index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">Home</a>
        <a href="products.php" class="<?php echo $current_page === 'products.php' ? 'active' : ''; ?>">Products</a>
        <a href="index.php#about-us">About Us</a>
      </nav>

      <div class="header-hotspot">
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="Cart/cart.php" class="account-icon-link" aria-label="Cart">
            <img src="Images/Account/Cart.png" alt="Cart">
          </a>
          <a href="Account/account.php" class="account-icon-link" aria-label="Account">
            <img src="Images/Account/Account.png" alt="Account">
          </a>
          <a href="Logout/logout.php" class="logout-link" aria-label="Log out">Log Out</a>
        <?php else: ?>
          <a href="Signup/signup.php" class="signin-link" aria-label="Sign up">Sign Up</a>
        <?php endif; ?>
      </div>
    </header>