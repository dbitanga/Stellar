<?php 
require_once 'Database/database.php';
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
        
        <!-- SECTION 1: WORKSTATION -->
        <div class="product-category-group">
          <div class="product-section-item">Workstation.</div>
          <div class="cards-grid">
            
            <!-- Forge SE -->
            <div class="product-card" id="card-ws-1">
              <div class="card-inner">
                <div>
                  <img src="Images/Products/Workstation.png" alt="Workstation" class="card-thumb">
                  <h3>Forge SE</h3>
                  <p class="card-price">₱38,999</p>
                  <div class="card-details">
                    <p class="spec-text"><strong>Processor:</strong> AMD Ryzen 5 7530U (6C/12T, up to 4.5 GHz)</p>
                    <p class="spec-text"><strong>Graphics:</strong> Integrated AMD Radeon Graphics</p>
                    <p class="spec-text"><strong>Memory:</strong> 8GB DDR4-3200 (upgradable to 32GB)</p>
                    <p class="spec-text"><strong>Storage:</strong> 256GB PCIe NVMe SSD</p>
                    <p class="spec-text"><strong>Display:</strong> 15.6" FHD (1920×1080) IPS, 60Hz</p>
                    <p class="spec-text"><strong>OS:</strong> Windows 11 Home</p>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="btn-text-toggle" data-card-id="card-ws-1">View More</button>
                  <form action="Cart/add_to_cart.php" method="POST" style="display: inline; width: 100%;">
                    <input type="hidden" name="product_id" value="ws-1">
                    <input type="hidden" name="name" value="Forge SE">
                    <input type="hidden" name="specs" value="AMD Ryzen 5 • 8GB RAM • 256GB SSD">
                    <input type="hidden" name="price" value="38999">
                    <input type="hidden" name="image" value="Images/Products/Workstation.png">
                    <button type="submit" class="btn-pill card-btn-pill">Add to Cart</button>
                  </form>
                </div>
              </div>
            </div>

            <!-- Forge Pro -->
            <div class="product-card" id="card-ws-2">
              <div class="card-inner">
                <div>
                  <img src="Images/Products/Workstation.png" alt="Workstation" class="card-thumb">
                  <h3>Forge Pro</h3>
                  <p class="card-price">₱64,999</p>
                  <div class="card-details">
                    <p class="spec-text"><strong>Processor:</strong> Intel Core i5-1340P (12-core, up to 4.6 GHz)</p>
                    <p class="spec-text"><strong>Graphics:</strong> NVIDIA RTX 2050 (4GB GDDR6)</p>
                    <p class="spec-text"><strong>Memory:</strong> 16GB DDR5-5200 (upgradable to 32GB)</p>
                    <p class="spec-text"><strong>Storage:</strong> 512GB PCIe Gen4 NVMe SSD</p>
                    <p class="spec-text"><strong>Display:</strong> 15.6" FHD+ (1920×1200) IPS, 60Hz</p>
                    <p class="spec-text"><strong>OS:</strong> Windows 11 Pro</p>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="btn-text-toggle" data-card-id="card-ws-2">View More</button>
                  <form action="Cart/add_to_cart.php" method="POST" style="display: inline; width: 100%;">
                    <input type="hidden" name="product_id" value="ws-2">
                    <input type="hidden" name="name" value="Forge Pro">
                    <input type="hidden" name="specs" value="Intel Core i5 • 16GB RAM • 512GB SSD">
                    <input type="hidden" name="price" value="64999">
                    <input type="hidden" name="image" value="Images/Products/Workstation.png">
                    <button type="submit" class="btn-pill card-btn-pill">Add to Cart</button>
                  </form>
                </div>
              </div>
            </div>

            <!-- Forge Eclipse -->
            <div class="product-card" id="card-ws-3">
              <div class="card-inner">
                <div>
                  <img src="Images/Products/Workstation.png" alt="Workstation" class="card-thumb">
                  <h3>Forge Eclipse</h3>
                  <p class="card-price">₱114,999</p>
                  <div class="card-details">
                    <p class="spec-text"><strong>Processor:</strong> Intel Core i7-13700H (14-core, up to 5.0 GHz)</p>
                    <p class="spec-text"><strong>Graphics:</strong> NVIDIA RTX 3000 Ada Gen (6GB GDDR6)</p>
                    <p class="spec-text"><strong>Memory:</strong> 32GB DDR5-5600 (upgradable to 64GB)</p>
                    <p class="spec-text"><strong>Storage:</strong> 1TB PCIe Gen4 NVMe SSD</p>
                    <p class="spec-text"><strong>Display:</strong> 16" QHD+ (2560×1600) IPS, 60Hz</p>
                    <p class="spec-text"><strong>OS:</strong> Windows 11 Pro</p>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="btn-text-toggle" data-card-id="card-ws-3">View More</button>
                  <form action="Cart/add_to_cart.php" method="POST" style="display: inline; width: 100%;">
                    <input type="hidden" name="product_id" value="ws-3">
                    <input type="hidden" name="name" value="Forge Eclipse">
                    <input type="hidden" name="specs" value="Intel Core i7 • 32GB RAM • 1TB SSD">
                    <input type="hidden" name="price" value="114999">
                    <input type="hidden" name="image" value="Images/Products/Workstation.png">
                    <button type="submit" class="btn-pill card-btn-pill">Add to Cart</button>
                  </form>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- SECTION 2: GAMING -->
        <div class="product-category-group">
          <div class="product-section-item">Gaming.</div>
          <div class="cards-grid">
            
            <!-- Nova SE -->
            <div class="product-card" id="card-gm-1">
              <div class="card-inner">
                <div>
                  <img src="Images/Products/Gaming.png" alt="Gaming" class="card-thumb">
                  <h3>Nova SE</h3>
                  <p class="card-price">₱36,999</p>
                  <div class="card-details">
                    <p class="spec-text"><strong>Processor:</strong> AMD Ryzen 5 7535HS (6C/12T, up to 4.55 GHz)</p>
                    <p class="spec-text"><strong>Graphics:</strong> NVIDIA RTX 3050 Laptop GPU (4GB GDDR6)</p>
                    <p class="spec-text"><strong>Memory:</strong> 8GB DDR5-4800 (upgradable to 32GB)</p>
                    <p class="spec-text"><strong>Storage:</strong> 512GB PCIe NVMe SSD</p>
                    <p class="spec-text"><strong>Display:</strong> 15.6" FHD (1920×1080) IPS, 144Hz</p>
                    <p class="spec-text"><strong>OS:</strong> Windows 11 Home</p>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="btn-text-toggle" data-card-id="card-gm-1">View More</button>
                  <form action="Cart/add_to_cart.php" method="POST" style="display: inline; width: 100%;">
                    <input type="hidden" name="product_id" value="gm-1">
                    <input type="hidden" name="name" value="Nova SE">
                    <input type="hidden" name="specs" value="AMD Ryzen 5 • 8GB RAM • 512GB SSD">
                    <input type="hidden" name="price" value="36999">
                    <input type="hidden" name="image" value="Images/Products/Gaming.png">
                    <button type="submit" class="btn-pill card-btn-pill">Add to Cart</button>
                  </form>
                </div>
              </div>
            </div>

            <!-- Nova Pro -->
            <div class="product-card" id="card-gm-2">
              <div class="card-inner">
                <div>
                  <img src="Images/Products/Gaming.png" alt="Gaming" class="card-thumb">
                  <h3>Nova Pro</h3>
                  <p class="card-price">₱67,999</p>
                  <div class="card-details">
                    <p class="spec-text"><strong>Processor:</strong> AMD Ryzen 7 7735HS (8C/16T, up to 4.75 GHz)</p>
                    <p class="spec-text"><strong>Graphics:</strong> NVIDIA RTX 4060 Laptop GPU (8GB GDDR6)</p>
                    <p class="spec-text"><strong>Memory:</strong> 16GB DDR5-5600 (upgradable to 32GB)</p>
                    <p class="spec-text"><strong>Storage:</strong> 1TB PCIe Gen4 NVMe SSD</p>
                    <p class="spec-text"><strong>Display:</strong> 15.6" QHD (2560×1440) IPS, 165Hz</p>
                    <p class="spec-text"><strong>OS:</strong> Windows 11 Home</p>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="btn-text-toggle" data-card-id="card-gm-2">View More</button>
                  <form action="Cart/add_to_cart.php" method="POST" style="display: inline; width: 100%;">
                    <input type="hidden" name="product_id" value="gm-2">
                    <input type="hidden" name="name" value="Nova Pro">
                    <input type="hidden" name="specs" value="AMD Ryzen 7 • 16GB RAM • 1TB SSD">
                    <input type="hidden" name="price" value="67999">
                    <input type="hidden" name="image" value="Images/Products/Gaming.png">
                    <button type="submit" class="btn-pill card-btn-pill">Add to Cart</button>
                  </form>
                </div>
              </div>
            </div>

            <!-- Nova Eclipse -->
            <div class="product-card" id="card-gm-3">
              <div class="card-inner">
                <div>
                  <img src="Images/Products/Gaming.png" alt="Gaming" class="card-thumb">
                  <h3>Nova Eclipse</h3>
                  <p class="card-price">₱119,999</p>
                  <div class="card-details">
                    <p class="spec-text"><strong>Processor:</strong> Intel Core i7-14700HX (20-core, up to 5.5 GHz)</p>
                    <p class="spec-text"><strong>Graphics:</strong> NVIDIA RTX 4070 Laptop GPU (8GB GDDR6)</p>
                    <p class="spec-text"><strong>Memory:</strong> 32GB DDR5-5600 (dual-channel, up to 64GB)</p>
                    <p class="spec-text"><strong>Storage:</strong> 1TB PCIe Gen4 NVMe SSD</p>
                    <p class="spec-text"><strong>Display:</strong> 16" QHD (2560×1440) IPS, 165Hz</p>
                    <p class="spec-text"><strong>OS:</strong> Windows 11 Home</p>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="btn-text-toggle" data-card-id="card-gm-3">View More</button>
                  <form action="Cart/add_to_cart.php" method="POST" style="display: inline; width: 100%;">
                    <input type="hidden" name="product_id" value="gm-3">
                    <input type="hidden" name="name" value="Nova Eclipse">
                    <input type="hidden" name="specs" value="Intel Core i7 • 32GB RAM • 1TB SSD">
                    <input type="hidden" name="price" value="119999">
                    <input type="hidden" name="image" value="Images/Products/Gaming.png">
                    <button type="submit" class="btn-pill card-btn-pill">Add to Cart</button>
                  </form>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- SECTION 3: PERSONAL -->
        <div class="product-category-group">
          <div class="product-section-item">Personal.</div>
          <div class="cards-grid">
            
            <!-- Cosmic SE -->
            <div class="product-card" id="card-ps-1">
              <div class="card-inner">
                <div>
                  <img src="Images/Products/Personal.png" alt="Personal" class="card-thumb">
                  <h3>Cosmic SE</h3>
                  <p class="card-price">₱32,999</p>
                  <div class="card-details">
                    <p class="spec-text"><strong>Processor:</strong> Intel Core i3-1315U (6-core, up to 4.5 GHz)</p>
                    <p class="spec-text"><strong>Graphics:</strong> Integrated Intel UHD Graphics</p>
                    <p class="spec-text"><strong>Memory:</strong> 8GB LPDDR5-4800 (onboard)</p>
                    <p class="spec-text"><strong>Storage:</strong> 256GB PCIe NVMe SSD</p>
                    <p class="spec-text"><strong>Display:</strong> 14" FHD (1920×1080) IPS, 60Hz</p>
                    <p class="spec-text"><strong>OS:</strong> Windows 11 Home</p>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="btn-text-toggle" data-card-id="card-ps-1">View More</button>
                  <form action="Cart/add_to_cart.php" method="POST" style="display: inline; width: 100%;">
                    <input type="hidden" name="product_id" value="ps-1">
                    <input type="hidden" name="name" value="Cosmic SE">
                    <input type="hidden" name="specs" value="Intel Core i3 • 8GB RAM • 256GB SSD">
                    <input type="hidden" name="price" value="32999">
                    <input type="hidden" name="image" value="Images/Products/Personal.png">
                    <button type="submit" class="btn-pill card-btn-pill">Add to Cart</button>
                  </form>
                </div>
              </div>
            </div>

            <!-- Cosmic Pro -->
            <div class="product-card" id="card-ps-2">
              <div class="card-inner">
                <div>
                  <img src="Images/Products/Personal.png" alt="Personal" class="card-thumb">
                  <h3>Cosmic Pro</h3>
                  <p class="card-price">₱58,999</p>
                  <div class="card-details">
                    <p class="spec-text"><strong>Processor:</strong> Intel Core i5-1340P (12-core, up to 4.6 GHz)</p>
                    <p class="spec-text"><strong>Graphics:</strong> Integrated Intel Iris Xe Graphics</p>
                    <p class="spec-text"><strong>Memory:</strong> 16GB LPDDR5-6400 (onboard)</p>
                    <p class="spec-text"><strong>Storage:</strong> 512GB PCIe Gen4 NVMe SSD</p>
                    <p class="spec-text"><strong>Display:</strong> 14" 2.2K (2240×1400) OLED, 60Hz</p>
                    <p class="spec-text"><strong>OS:</strong> Windows 11 Home</p>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="btn-text-toggle" data-card-id="card-ps-2">View More</button>
                  <form action="Cart/add_to_cart.php" method="POST" style="display: inline; width: 100%;">
                    <input type="hidden" name="product_id" value="ps-2">
                    <input type="hidden" name="name" value="Cosmic Pro">
                    <input type="hidden" name="specs" value="Intel Core i5 • 16GB RAM • 512GB SSD">
                    <input type="hidden" name="price" value="58999">
                    <input type="hidden" name="image" value="Images/Products/Personal.png">
                    <button type="submit" class="btn-pill card-btn-pill">Add to Cart</button>
                  </form>
                </div>
              </div>
            </div>

            <!-- Cosmic Eclipse -->
            <div class="product-card" id="card-ps-3">
              <div class="card-inner">
                <div>
                  <img src="Images/Products/Personal.png" alt="Personal" class="card-thumb">
                  <h3>Cosmic Eclipse</h3>
                  <p class="card-price">₱99,999</p>
                  <div class="card-details">
                    <p class="spec-text"><strong>Processor:</strong> Intel Core Ultra 7 155H (16-core, up to 4.8 GHz)</p>
                    <p class="spec-text"><strong>Graphics:</strong> Integrated Intel Arc Graphics</p>
                    <p class="spec-text"><strong>Memory:</strong> 32GB LPDDR5X-7500 (onboard)</p>
                    <p class="spec-text"><strong>Storage:</strong> 1TB PCIe Gen4 NVMe SSD</p>
                    <p class="spec-text"><strong>Display:</strong> 14" 3K (2880×1800) OLED, 120Hz</p>
                    <p class="spec-text"><strong>OS:</strong> Windows 11 Home</p>
                  </div>
                </div>
                <div class="card-actions">
                  <button class="btn-text-toggle" data-card-id="card-ps-3">View More</button>
                  <form action="Cart/add_to_cart.php" method="POST" style="display: inline; width: 100%;">
                    <input type="hidden" name="product_id" value="ps-3">
                    <input type="hidden" name="name" value="Cosmic Eclipse">
                    <input type="hidden" name="specs" value="Intel Core Ultra 7 • 32GB RAM • 1TB SSD">
                    <input type="hidden" name="price" value="99999">
                    <input type="hidden" name="image" value="Images/Products/Personal.png">
                    <button type="submit" class="btn-pill card-btn-pill">Add to Cart</button>
                  </form>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>

    </div>
  </main>

<?php include 'footer.php'; ?>