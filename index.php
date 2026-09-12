<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>STELLAR Laptops</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Goldman:wght@400;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- 1. HEADER + BRAND / HERO -->
  <section class="fade-section">
    <img src="Images/Homepage/1 Header and Brand.jpg" alt="STELLAR header and hero" class="section-img">

    <nav class="site-nav">
      <a href="index.php"    class="<?php echo $current_page === 'index.php'    ? 'active' : ''; ?>">Home</a>
      <a href="products.php" class="<?php echo $current_page === 'products.php' ? 'active' : ''; ?>">Product</a>
      <a href="stores.php"   class="<?php echo $current_page === 'stores.php'   ? 'active' : ''; ?>">Stores</a>
    </nav>

    <div class="hotspot" style="top:0%; left:0%; width:100%; height:6%;">
      <a href="signup.php" style="left:90%; width:8%; height:100%;" aria-label="Sign up"></a>
    </div>
    <a href="products.php" class="btn-pill explore-btn">Explore Models</a>
  </section>

  <!-- 2. CHOOSE YOURS / SELECTION -->
  <section class="fade-section">
    <img src="Images/Homepage/2 Selection.jpg" alt="Choose your STELLAR model" class="section-img">

    <div class="selection-cards">
      <a href="workstation.php" aria-label="Workstation">
        <img src="Images/Homepage/Workstation Card.png" alt="Workstation laptop">
      </a>
      <a href="gaming.php" aria-label="Gaming">
        <img src="Images/Homepage/Gaming Card.png" alt="Gaming laptop">
      </a>
      <a href="personal.php" aria-label="Personal">
        <img src="Images/Homepage/Personal Card.png" alt="Personal laptop">
      </a>
    </div>
  </section>

  <!-- 3. COSMIC FEATURE -->
  <section class="fade-section">
    <img src="Images/Homepage/3 Cosmic.jpg" alt="STELLAR Cosmic laptop" class="section-img">
  </section>

  <!-- 4. REVIEWS -->
  <section class="fade-section">
    <img src="Images/Homepage/4 Reviews.jpg" alt="Customer review" class="section-img">
  </section>

  <!-- 5. ABOUT US -->
  <section class="fade-section">
    <img src="Images/Homepage/5 About Us.jpg" alt="About STELLAR" class="section-img">

    <div class="hotspot" style="top:0%; left:0%; width:100%; height:100%;">
      <a href="about.php" style="top:60%; left:30%; width:40%; height:20%;" aria-label="About Us"></a>
    </div>
  </section>

  <!-- 6. STRIP -->
  <section class="fade-section">
    <img src="Images/Homepage/6 Strip.jpg" alt="Modular, repairable, built to last" class="section-img">
    <a href="products.php" class="btn-pill shop-now-btn">Shop Now</a>
  </section>

  <!-- 7. FOOTER -->
  <section class="fade-section">
    <img src="Images/Homepage/7 Footer.jpg" alt="Experience STELLAR in person" class="section-img">

    <div class="hotspot" style="top:0%; left:0%; width:100%; height:100%;">
      <a href="products.php" style="top:88%; left:80%; width:12%; height:8%;" aria-label="Shop Now"></a>
    </div>

    <div class="social-icons">
      <a href="https://instagram.com" target="_blank" aria-label="Instagram">
        <img src="Images/Social Media/Instagram.png" alt="Instagram">
      </a>
      <a href="https://youtube.com" target="_blank" aria-label="YouTube">
        <img src="Images/Social Media/Youtube.png" alt="YouTube">
      </a>
      <a href="https://linkedin.com" target="_blank" aria-label="LinkedIn">
        <img src="Images/Social Media/Linkedin.png" alt="LinkedIn">
      </a>
    </div>
  </section>

  <script src="script.js"></script>
</body>
</html>