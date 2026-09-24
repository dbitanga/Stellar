<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'Database/database.php';

// Fetch all products from the database, ordered by category and tier level
$query = "SELECT * FROM products ORDER BY FIELD(category, 'Workstation', 'Gaming', 'Personal'), FIELD(tier_level, 'SE', 'Pro', 'Eclipse')";
$result = $conn->query($query);

$categories = [
    'Workstation' => ['title' => 'Workstation.', 'prefix' => 'ws', 'products' => []],
    'Gaming' => ['title' => 'Gaming.', 'prefix' => 'gm', 'products' => []],
    'Personal' => ['title' => 'Personal.', 'prefix' => 'ps', 'products' => []]
];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (isset($categories[$row['category']])) {
            $categories[$row['category']]['products'][] = $row;
        }
    }
}
$conn->close();

include 'header.php'; 
?>

  <!-- MAIN PRODUCTS CONTAINER -->
  <main class="products-wrapper">
    
    <div class="products-container">
      
      <!-- Products Header -->
      <section class="products-header-text">
        <h1>STELLAR PRODUCTS</h1>
        <p>Explore our lineup of high-performance, modular laptops.</p>
      </section>

      <!-- Vertical Product Sections & Cards -->
      <div class="products-content-area">
        
        <?php foreach ($categories as $cat_key => $cat_data): ?>
        <!-- SECTION: <?php echo strtoupper($cat_key); ?> -->
        <div class="product-category-group">
          <div class="product-section-item"><?php echo $cat_data['title']; ?></div>
          <div class="cards-grid">
            
            <?php if (empty($cat_data['products'])): ?>
              <p style="color: #A8C4DA; padding: 20px;">No products available in this category.</p>
            <?php else: ?>
              <?php foreach ($cat_data['products'] as $index => $product): 
                $card_id = 'card-' . $cat_data['prefix'] . '-' . ($index + 1);
                $short_specs = htmlspecialchars($product['processor'] . ' • ' . $product['ram'] . ' • ' . $product['storage']);
              ?>
              
              <!-- Product Card -->
              <div class="product-card" id="<?php echo $card_id; ?>">
                <div class="card-inner">
                  <div>
                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['category']); ?>" class="card-thumb">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="card-price">₱<?php echo number_format($product['price'], 2); ?></p>
                    <div class="card-details">
                      <p class="spec-text"><strong>Processor:</strong> <?php echo htmlspecialchars($product['processor']); ?></p>
                      <p class="spec-text"><strong>Graphics:</strong> <?php echo htmlspecialchars($product['graphics']); ?></p>
                      <p class="spec-text"><strong>Memory:</strong> <?php echo htmlspecialchars($product['ram']); ?></p>
                      <p class="spec-text"><strong>Storage:</strong> <?php echo htmlspecialchars($product['storage']); ?></p>
                    </div>
                  </div>
                  <div class="card-actions">
                    <button class="btn-text-toggle" data-card-id="<?php echo $card_id; ?>">View More</button>
                    <form action="Cart/add_to_cart.php" method="POST" style="display: inline; width: 100%;">
                      <input type="hidden" name="product_id" value="<?php echo (int)$product['product_id']; ?>">
                      <input type="hidden" name="name" value="<?php echo htmlspecialchars($product['name']); ?>">
                      <input type="hidden" name="specs" value="<?php echo $short_specs; ?>">
                      <input type="hidden" name="price" value="<?php echo $product['price']; ?>">
                      <input type="hidden" name="image" value="<?php echo htmlspecialchars($product['image_path']); ?>">
                      <button type="submit" class="btn-pill card-btn-pill">Add to Cart</button>
                    </form>
                  </div>
                </div>
              </div>

              <?php endforeach; ?>
            <?php endif; ?>

          </div>
        </div>
        <?php endforeach; ?>

      </div>

    </div>
  </main>

<?php include 'footer.php'; ?>