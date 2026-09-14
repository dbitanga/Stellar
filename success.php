<?php
// Shown after a successful signup
require 'Database/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare('SELECT user_id, username FROM users WHERE user_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user) {
    header('Location: index.php');
    exit;
}

$base_path = '.';
include 'header.php';
?>

<main class="cart-wrapper">
  <div class="cart-container" style="max-width: 600px; margin: 0 auto; text-align: center;">
    <h1 class="cart-title">Account Created</h1>

    <div class="cart-summary-card" style="align-items: center; gap: 1.5rem; padding: 3rem;">
      <p style="color: var(--text-light); font-size: 1.1rem;">
        Welcome, <?php echo htmlspecialchars($user['username']); ?>! Your account is ready.
      </p>

      <a href="Login/login.php" class="btn-pill" style="position: relative; display: inline-block; width: 100%; text-align: center; text-decoration: none;">
        Log In Now
      </a>

      <a href="index.php" class="btn-text-toggle" style="color: #A8C4DA; text-decoration: none; margin-top: 1rem;">
        Back to STELLAR
      </a>
    </div>
  </div>
</main>

<?php include 'footer.php'; ?>