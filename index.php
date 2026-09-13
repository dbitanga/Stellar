<?php 
require_once 'Database/database.php';
include 'header.php'; 
?>

  <!-- 2. BRAND / HERO SECTION -->
  <section class="fade-section">
    <img src="Images/Homepage/Brand.jpg" alt="STELLAR Brand Hero" class="section-img">
    <img src="Images/Homepage/Brand Laptop.png" alt="STELLAR Laptop" class="brand-laptop">
    <a href="products.php" class="btn-pill explore-btn">Explore Models</a>
  </section>

  <!-- 3. CHOOSE YOURS / SELECTION -->
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

  <!-- 4. COSMIC FEATURE -->
  <section class="fade-section">
    <img src="Images/Homepage/3 Cosmic.jpg" alt="STELLAR Cosmic laptop" class="section-img">
  </section>

  <!-- 5. REVIEWS -->
  <section class="fade-section">
    <img src="Images/Homepage/4 Reviews.jpg" alt="Customer review" class="section-img">
  </section>

  <!-- 6. ABOUT US -->
  <section id="about-us" class="fade-section">
    <img src="Images/Homepage/5 About Us.jpg" alt="About STELLAR" class="section-img">

    <div class="hotspot about-hotspot">
      <a href="#about-us" class="about-link" aria-label="About Us"></a>
    </div>
  </section>

  <!-- 7. STRIP -->
  <section class="fade-section">
    <img src="Images/Homepage/6 Strip.jpg" alt="Modular, repairable, built to last" class="section-img">
    <a href="products.php" class="btn-pill shop-now-btn">Shop Now</a>
  </section>

<?php include 'footer.php'; ?>