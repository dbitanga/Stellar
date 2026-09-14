<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../Login/login.php');
    exit;
}

require '../Database/database.php';

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare(
    'SELECT user_id, full_name, username, email, mobile_number, delivery_address, created_at
     FROM users WHERE user_id = ?'
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: ../Logout/logout.php');
    exit;
}

$stmt = $conn->prepare(
    'SELECT order_id, total_amount, payment_method, payment_status, order_status, created_at
     FROM orders WHERE user_id = ? ORDER BY created_at DESC'
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$itemStmt = $conn->prepare(
    'SELECT oi.quantity, oi.price_at_purchase, p.name
     FROM order_items oi
     JOIN products p ON p.product_id = oi.product_id
     WHERE oi.order_id = ?'
);
foreach ($orders as &$order) {
    $itemStmt->bind_param('i', $order['order_id']);
    $itemStmt->execute();
    $order['items'] = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
unset($order);
$itemStmt->close();
$conn->close();

$status  = $_GET['status'] ?? null;
$message = $_GET['message'] ?? null;

$base_path = '..';
require '../header.php';
?>

<link rel="stylesheet" href="../auth.css">

<!-- MAIN ACCOUNT CONTAINER -->
<main class="account-wrapper">
  <div class="account-page">

    <?php if ($status === 'error' && $message): ?>
      <p class="form-error"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if ($status === 'success' && $message): ?>
      <p class="form-success"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- ORDERS -->
    <section class="account-card">
      <h2>My Orders</h2>

      <?php if (empty($orders)): ?>
        <p class="empty-state">You have no orders yet.</p>
      <?php else: ?>
        <div class="orders-list">
          <?php foreach ($orders as $order): ?>
            <div class="order-item">
              <div class="order-item-top">
                <span class="order-summary">Order #<?php echo (int) $order['order_id']; ?></span>
                <span class="order-status order-status-<?php echo strtolower($order['order_status']); ?>">
                  <?php echo htmlspecialchars($order['order_status']); ?>
                </span>
              </div>

              <ul class="order-line-items">
                <?php foreach ($order['items'] as $item): ?>
                  <li>
                    <?php echo htmlspecialchars($item['name']); ?> × <?php echo (int) $item['quantity']; ?>
                    — ₱<?php echo number_format($item['price_at_purchase'], 2); ?> each
                  </li>
                <?php endforeach; ?>
              </ul>

              <div class="order-item-bottom">
                <span class="order-price">Total: ₱<?php echo number_format($order['total_amount'], 2); ?></span>
                <span class="order-delivery">
                  <?php echo htmlspecialchars($order['payment_method']); ?> ·
                  <?php echo htmlspecialchars($order['payment_status']); ?> ·
                  <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- ACCOUNT INFO -->
    <section class="account-card">
      <h2>Account Information</h2>

      <form method="POST" action="account_function.php">
        <input type="hidden" name="action" value="update_info">

        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label for="mobile_number">Mobile Number</label>
        <input type="text" id="mobile_number" name="mobile_number" value="<?php echo htmlspecialchars($user['mobile_number']); ?>" required>

        <label for="delivery_address">Delivery Address</label>
        <textarea id="delivery_address" name="delivery_address" required><?php echo htmlspecialchars($user['delivery_address']); ?></textarea>

        <p class="member-since">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>

        <button type="submit" class="btn-account-save">Save Changes</button>
      </form>
    </section>

    <!-- DANGER ZONE -->
    <section class="account-card danger-zone">
      <h2>Delete Account</h2>
      <p class="danger-text">This permanently deletes your account and order history. This cannot be undone.</p>
      <button type="button" id="delete-account-trigger" class="btn-danger-outline">Delete Account</button>
    </section>

  </div>
</main>

<!-- DELETE CONFIRMATION MODAL -->
<div id="delete-modal" class="modal-overlay">
  <div class="modal-box">
    <h3>Delete your account?</h3>
    <p>This will permanently delete your account and all order history. This cannot be undone.</p>
    <div class="modal-actions">
      <button type="button" id="cancel-delete" class="btn-secondary">Cancel</button>
      <form method="POST" action="account_function.php">
        <input type="hidden" name="action" value="delete_account">
        <button type="submit" class="btn-danger">Confirm Delete</button>
      </form>
    </div>
  </div>
</div>

<?php include '../footer.php'; ?>