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

$base_path = '..';

// Handle quantity updates or item removal if posted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $index = $_POST['index'] ?? null;

    if ($index !== null && isset($_SESSION['cart'][$index])) {
        if ($action === 'increase') {
            $_SESSION['cart'][$index]['qty']++;
        } elseif ($action === 'decrease') {
            $_SESSION['cart'][$index]['qty']--;
            if ($_SESSION['cart'][$index]['qty'] <= 0) {
                unset($_SESSION['cart'][$index]);
                $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex array
            }
        } elseif ($action === 'remove') {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex array
        }
    }

    $cart_items = $_SESSION['cart'] ?? [];
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['qty'];
    }
    $shipping = $subtotal > 0 ? 25.00 : 0.00;
    $grand_total = $subtotal + ($subtotal > 0 ? $shipping : 0);

    // Return JSON response if requested asynchronously to prevent header/footer reload
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'cart_empty' => empty($cart_items),
            'subtotal' => number_format($subtotal, 2),
            'shipping' => number_format($shipping, 2),
            'grand_total' => number_format($grand_total, 2)
        ]);
        exit;
    }

    header('Location: cart.php');
    exit;
}

// Initialize cart as an empty array if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart_items = $_SESSION['cart'];
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['qty'];
}
$shipping = $subtotal > 0 ? 25.00 : 0.00;
$grand_total = $subtotal + ($subtotal > 0 ? $shipping : 0);

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
        
        <!-- Left Side: Cart Items List -->
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

        <!-- Right Side: Order Summary -->
        <div class="cart-summary-card">
          <h2>Order Summary</h2>
          <div class="summary-row">
            <span>Subtotal</span>
            <span>₱<?php echo number_format($subtotal, 2); ?></span>
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
          <button type="button" class="btn-pill checkout-btn" onclick="alert('Proceeding to checkout workflow...');">Proceed to Checkout</button>
        </div>

      </div>
    <?php endif; ?>
  </div>
</main>

<?php include '../footer.php'; ?>