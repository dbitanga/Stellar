<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    header('Location: ../Login/login.php');
    exit;
}

require_once '../Database/database.php';

// Fetch user profile info using correct database column names
$user_id = $_SESSION['user_id'];
$user_stmt = $conn->prepare('SELECT username, full_name, delivery_address, mobile_number FROM users WHERE user_id = ?');
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

$default_name = $user_result['full_name'] ?? $user_result['username'] ?? '';
$default_address = $user_result['delivery_address'] ?? '';
$default_phone = $user_result['mobile_number'] ?? '';

$base_path = '..';

// Handle AJAX Checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    if (empty($_SESSION['cart'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit;
    }

    $name = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $payment_method = 'COD';

    $cart_items = $_SESSION['cart'];
    $subtotal = 0;
    $total_qty = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['qty'];
        $total_qty += $item['qty'];
    }
    $tax = $subtotal * 0.10;
    $shipping_protection = $subtotal * 0.02;
    $shipping = $subtotal > 0 ? (250 + (100 * $total_qty)) : 0.00;
    $grand_total = $subtotal + $tax + $shipping_protection + $shipping;

    // Calculate delivery window (10 to 15 days from now)
    $current_date = new DateTime();
    $delivery_start = (clone $current_date)->modify('+10 days')->format('F j, Y');
    $delivery_end = (clone $current_date)->modify('+15 days')->format('F j, Y');
    $delivery_string = "$delivery_start - $delivery_end";

    // Insert order into database matching the exact schema columns (shipping_name, shipping_mobile, shipping_address)
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_method, shipping_name, shipping_mobile, shipping_address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("dsssss", $user_id, $grand_total, $payment_method, $name, $phone, $address);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        // Insert individual items into order_items table
        foreach ($cart_items as $item) {
            $product_id = $item['product_id'] ?? $item['id'] ?? 1;
            $price = $item['price'];
            $qty = $item['qty'];

            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, price_at_purchase, quantity) VALUES (?, ?, ?, ?)");
            if ($item_stmt) {
                $item_stmt->bind_param("iidi", $order_id, $product_id, $price, $qty);
                $item_stmt->execute();
                $item_stmt->close();
            }
        }
    }

    // Clear cart session
    unset($_SESSION['cart']);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'delivery_window' => $delivery_string,
        'grand_total' => number_format($grand_total, 2)
    ]);
    exit;
}

// Handle quantity updates or item removal asynchronously
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'] ?? '';
    $index = $_POST['index'] ?? null;

    if ($index !== null && isset($_SESSION['cart'][$index])) {
        if ($action === 'increase') {
            $_SESSION['cart'][$index]['qty']++;
        } elseif ($action === 'decrease') {
            $_SESSION['cart'][$index]['qty']--;
            if ($_SESSION['cart'][$index]['qty'] <= 0) {
                unset($_SESSION['cart'][$index]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
            }
        } elseif ($action === 'remove') {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
    }

    $cart_items = $_SESSION['cart'] ?? [];
    $subtotal = 0;
    $total_qty = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['qty'];
        $total_qty += $item['qty'];
    }
    $tax = $subtotal * 0.10;
    $shipping_protection = $subtotal * 0.02;
    $shipping = $subtotal > 0 ? (250 + (100 * $total_qty)) : 0.00;
    $grand_total = $subtotal + $tax + $shipping_protection + $shipping;

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        ob_start();
        ?>
        <h1 class="cart-title">Your Shopping Cart</h1>

        <?php if (empty($cart_items)): ?>
          <div class="empty-cart-state">
            <p>Your cart is currently empty.</p>
            <a href="../products.php" class="btn-pill shop-more-btn">Explore Products</a>
          </div>
        <?php else: ?>
          <div class="cart-grid">
            <div class="cart-items-list">
              <?php foreach ($cart_items as $index => $item): 
                $item_total = $item['price'] * $item['qty'];
              ?>
                <div class="cart-item-card">
                  <div class="cart-item-img-wrap">
                    <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-img">
                  </div>

                  <div class="cart-item-details">
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p class="cart-item-specs"><?php echo htmlspecialchars($item['specs']); ?></p>
                    <div class="cart-item-pricing">
                      <span class="unit-price">Unit Price: ₱<?php echo number_format($item['price'], 2); ?></span>
                      <span class="total-price">Total: ₱<?php echo number_format($item_total, 2); ?></span>
                    </div>
                  </div>

                  <div class="cart-item-actions">
                    <form method="POST" class="qty-form">
                      <input type="hidden" name="index" value="<?php echo $index; ?>">
                      <button type="submit" name="action" value="decrease" class="qty-btn">−</button>
                      <span class="qty-display"><?php echo $item['qty']; ?></span>
                      <button type="submit" name="action" value="increase" class="qty-btn">+</button>
                    </form>

                    <form method="POST">
                      <input type="hidden" name="index" value="<?php echo $index; ?>">
                      <button type="submit" name="action" value="remove" class="remove-btn">Remove</button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="cart-summary-card">
              <h2>Order Summary</h2>
              <div class="summary-row">
                <span>Subtotal</span>
                <span>₱<?php echo number_format($subtotal, 2); ?></span>
              </div>
              <div class="summary-row">
                <span>Tax</span>
                <span>₱<?php echo number_format($tax, 2); ?></span>
              </div>
              <div class="summary-row">
                <span>Shipping Protection</span>
                <span>₱<?php echo number_format($shipping_protection, 2); ?></span>
              </div>
              <div class="summary-row">
                <span>Estimated Shipping</span>
                <span>₱<?php echo number_format($shipping, 2); ?></span>
              </div>
              <div class="summary-divider"></div>
              <div class="summary-row grand-total">
                <span>Total</span>
                <span>₱<?php echo number_format($grand_total, 2); ?></span>
              </div>
              <button type="button" id="openCheckoutBtn" class="btn-pill checkout-btn">Proceed to Checkout</button>
            </div>
          </div>

          <!-- Checkout Modal Overlay -->
          <div id="checkoutModal" class="checkout-modal-overlay">
            <div class="checkout-modal-content custom-checkout-modal">
              
              <!-- Step 1: Checkout Form View -->
              <div id="checkoutFormView">
                <h2>Complete Your Order</h2>
                
                <div class="checkout-section">
                  <h3>Order Summary</h3>
                  <div class="checkout-items-scroll">
                    <?php foreach ($cart_items as $ci): ?>
                      <div class="checkout-summary-row">
                        <span><?php echo htmlspecialchars($ci['name']); ?> (x<?php echo $ci['qty']; ?>)</span>
                        <span>₱<?php echo number_format($ci['price'] * $ci['qty'], 2); ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <div class="summary-divider"></div>
                  <div class="checkout-breakdown-box">
                    <div class="checkout-sub-row">
                      <span>Subtotal:</span>
                      <span>₱<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="checkout-sub-row">
                      <span>Tax:</span>
                      <span>₱<?php echo number_format($tax, 2); ?></span>
                    </div>
                    <div class="checkout-sub-row">
                      <span>Shipping Protection:</span>
                      <span>₱<?php echo number_format($shipping_protection, 2); ?></span>
                    </div>
                    <div class="checkout-sub-row">
                      <span>Estimated Shipping:</span>
                      <span>₱<?php echo number_format($shipping, 2); ?></span>
                    </div>
                  </div>
                  <div class="summary-divider"></div>
                  <p class="checkout-total-text">
                    <span>Total Amount:</span>
                    <span>₱<?php echo number_format($grand_total, 2); ?></span>
                  </p>
                </div>

                <form id="checkoutForm" method="POST">
                  <input type="hidden" name="action" value="checkout">
                  <input type="hidden" name="payment_method" value="cod">
                  <div class="checkout-section">
                    <h3>Delivery Information</h3>
                    <div class="form-group">
                      <label class="checkout-label">Full Name</label>
                      <input type="text" name="name" value="<?php echo htmlspecialchars($default_name); ?>" required class="checkout-input">
                    </div>
                    <div class="form-group">
                      <label class="checkout-label">Delivery Address</label>
                      <textarea name="address" required class="checkout-input" rows="2"><?php echo htmlspecialchars($default_address); ?></textarea>
                    </div>
                    <div class="form-group">
                      <label class="checkout-label">Phone Number</label>
                      <input type="tel" name="phone" value="<?php echo htmlspecialchars($default_phone); ?>" required class="checkout-input">
                    </div>
                  </div>

                  <div class="checkout-section">
                    <h3>Payment Method</h3>
                    <p class="checkout-label" style="color: #A8C4DA; font-size: 0.9rem;">Only Cash on Delivery (COD) is available at this time.</p>
                  </div>

                  <div class="checkout-actions">
                    <button type="submit" class="btn-pill confirm-order-btn">Confirm Order</button>
                  </div>
                </form>
              </div>

              <!-- Step 2: Order Confirmed Success View -->
              <div id="orderConfirmedView" style="display: none;" class="order-confirmed-container">
                <h2>Order Confirmed!</h2>
                <p class="confirmation-message">Thank you for your purchase. Your order has been successfully placed and saved to your account profile.</p>
                <div class="delivery-estimate-box">
                  <span class="delivery-label">Estimated Delivery Window:</span>
                  <span id="deliveryDateRange" class="delivery-date-highlight"></span>
                </div>
                <div class="checkout-actions">
                  <button type="button" id="closeConfirmationBtn" class="btn-pill confirm-order-btn">View Products / Return</button>
                </div>
              </div>

            </div>
          </div>
        <?php endif;
        $html_content = ob_get_clean();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'cart_html' => $html_content
        ]);
        exit;
    }

    header('Location: cart.php');
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart_items = $_SESSION['cart'];
$subtotal = 0;
$total_qty = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['qty'];
    $total_qty += $item['qty'];
}
$tax = $subtotal * 0.10;
$shipping_protection = $subtotal * 0.02;
$shipping = $subtotal > 0 ? (250 + (100 * $total_qty)) : 0.00;
$grand_total = $subtotal + $tax + $shipping_protection + $shipping;

include '../header.php';
?>

<main class="cart-wrapper">
  <div class="cart-container">
    <h1 class="cart-title">Your Shopping Cart</h1>

    <?php if (empty($cart_items)): ?>
      <div class="empty-cart-state">
        <p>Your cart is currently empty.</p>
        <a href="../products.php" class="btn-pill shop-more-btn">Explore Products</a>
      </div>
    <?php else: ?>
      <div class="cart-grid">
        
        <div class="cart-items-list">
          <?php foreach ($cart_items as $index => $item): 
            $item_total = $item['price'] * $item['qty'];
          ?>
            <div class="cart-item-card">
              <div class="cart-item-img-wrap">
                <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-img">
              </div>

              <div class="cart-item-details">
                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                <p class="cart-item-specs"><?php echo htmlspecialchars($item['specs']); ?></p>
                <div class="cart-item-pricing">
                  <span class="unit-price">Unit Price: ₱<?php echo number_format($item['price'], 2); ?></span>
                  <span class="total-price">Total: ₱<?php echo number_format($item_total, 2); ?></span>
                </div>
              </div>

              <div class="cart-item-actions">
                <form method="POST" class="qty-form">
                  <input type="hidden" name="index" value="<?php echo $index; ?>">
                  <button type="submit" name="action" value="decrease" class="qty-btn">−</button>
                  <span class="qty-display"><?php echo $item['qty']; ?></span>
                  <button type="submit" name="action" value="increase" class="qty-btn">+</button>
                </form>

                <form method="POST">
                  <input type="hidden" name="index" value="<?php echo $index; ?>">
                  <button type="submit" name="action" value="remove" class="remove-btn">Remove</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="cart-summary-card">
          <h2>Order Summary</h2>
          <div class="summary-row">
            <span>Subtotal</span>
            <span>₱<?php echo number_format($subtotal, 2); ?></span>
          </div>
          <div class="summary-row">
            <span>Tax</span>
            <span>₱<?php echo number_format($tax, 2); ?></span>
          </div>
          <div class="summary-row">
            <span>Shipping Protection</span>
            <span>₱<?php echo number_format($shipping_protection, 2); ?></span>
          </div>
          <div class="summary-row">
            <span>Estimated Shipping</span>
            <span>₱<?php echo number_format($shipping, 2); ?></span>
          </div>
          <div class="summary-divider"></div>
          <div class="summary-row grand-total">
            <span>Total</span>
            <span>₱<?php echo number_format($grand_total, 2); ?></span>
          </div>
          <button type="button" id="openCheckoutBtn" class="btn-pill checkout-btn">Proceed to Checkout</button>
        </div>

      </div>

      <!-- Checkout Modal Overlay -->
      <div id="checkoutModal" class="checkout-modal-overlay">
        <div class="checkout-modal-content custom-checkout-modal">
          
          <!-- Step 1: Checkout Form View -->
          <div id="checkoutFormView">
            <h2>Complete Your Order</h2>
            
            <div class="checkout-section">
              <h3>Order Summary</h3>
              <div class="checkout-items-scroll">
                <?php foreach ($cart_items as $ci): ?>
                  <div class="checkout-summary-row">
                    <span><?php echo htmlspecialchars($ci['name']); ?> (x<?php echo $ci['qty']; ?>)</span>
                    <span>₱<?php echo number_format($ci['price'] * $ci['qty'], 2); ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="summary-divider"></div>
              <div class="checkout-breakdown-box">
                <div class="checkout-sub-row">
                  <span>Subtotal:</span>
                  <span>₱<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="checkout-sub-row">
                  <span>Tax:</span>
                  <span>₱<?php echo number_format($tax, 2); ?></span>
                </div>
                <div class="checkout-sub-row">
                  <span>Shipping Protection:</span>
                  <span>₱<?php echo number_format($shipping_protection, 2); ?></span>
                </div>
                <div class="checkout-sub-row">
                  <span>Estimated Shipping:</span>
                  <span>₱<?php echo number_format($shipping, 2); ?></span>
                </div>
              </div>
              <div class="summary-divider"></div>
              <p class="checkout-total-text">
                <span>Total Amount:</span>
                <span>₱<?php echo number_format($grand_total, 2); ?></span>
              </p>
            </div>

            <form id="checkoutForm" method="POST">
              <input type="hidden" name="action" value="checkout">
              <input type="hidden" name="payment_method" value="cod">
              <div class="checkout-section">
                <h3>Delivery Information</h3>
                <div class="form-group">
                  <label class="checkout-label">Full Name</label>
                  <input type="text" name="name" value="<?php echo htmlspecialchars($default_name); ?>" required class="checkout-input">
                </div>
                <div class="form-group">
                  <label class="checkout-label">Delivery Address</label>
                  <textarea name="address" required class="checkout-input" rows="2"><?php echo htmlspecialchars($default_address); ?></textarea>
                </div>
                <div class="form-group">
                  <label class="checkout-label">Phone Number</label>
                  <input type="tel" name="phone" value="<?php echo htmlspecialchars($default_phone); ?>" required class="checkout-input">
                </div>
              </div>

              <div class="checkout-section">
                <h3>Payment Method</h3>
                <p class="checkout-label" style="color: #A8C4DA; font-size: 0.9rem;">Only Cash on Delivery (COD) is available at this time.</p>
              </div>

              <div class="checkout-actions">
                <button type="submit" class="btn-pill confirm-order-btn">Confirm Order</button>
              </div>
            </form>
          </div>

          <!-- Step 2: Order Confirmed Success View -->
          <div id="orderConfirmedView" style="display: none;" class="order-confirmed-container">
            <h2>Order Confirmed!</h2>
            <p class="confirmation-message">Thank you for your purchase. Your order has been successfully placed and saved to your account profile.</p>
            <div class="delivery-estimate-box">
              <span class="delivery-label">Estimated Delivery Window:</span>
              <span id="deliveryDateRange" class="delivery-date-highlight"></span>
            </div>
            <div class="checkout-actions">
              <button type="button" id="closeConfirmationBtn" class="btn-pill confirm-order-btn">View Products / Return</button>
            </div>
          </div>

        </div>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php include '../footer.php'; ?>