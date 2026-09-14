<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Every response from this file must be JSON — this endpoint is only ever
// called via fetch(), so a redirect or an HTML error page here breaks the
// frontend with a confusing "unexpected error" instead of the real reason.
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You need to be logged in to check out.']);
    exit;
}

if (empty($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
    exit;
}

$user_id        = $_SESSION['user_id'];
$name           = trim($_POST['name'] ?? '');
$address        = trim($_POST['address'] ?? '');
$phone          = trim($_POST['phone'] ?? '');
$payment_method = 'COD';

if ($name === '' || $address === '' || $phone === '') {
    echo json_encode(['success' => false, 'message' => 'Please fill in your name, address, and phone number.']);
    exit;
}

$cart_items = $_SESSION['cart'];
$subtotal   = 0;
$total_qty  = 0;
foreach ($cart_items as $item) {
    $subtotal  += $item['price'] * $item['qty'];
    $total_qty += $item['qty'];
}
$tax                 = $subtotal * 0.10;
$shipping_protection = $subtotal * 0.02;
$shipping            = $subtotal > 0 ? (250 + (100 * $total_qty)) : 0.00;
$grand_total         = $subtotal + $tax + $shipping_protection + $shipping;

// Calculate delivery window (10 to 15 days from now)
$current_date    = new DateTime();
$delivery_start  = (clone $current_date)->modify('+10 days')->format('F j, Y');
$delivery_end    = (clone $current_date)->modify('+15 days')->format('F j, Y');
$delivery_string = "$delivery_start - $delivery_end";

// Everything that can fail — including the DB connection itself — is
// inside this try block, so ANY failure still returns valid JSON instead
// of a raw PHP error page that breaks the frontend's fetch().
try {
    require_once '../Database/database.php';

    $stmt = $conn->prepare(
        "INSERT INTO orders (user_id, total_amount, payment_method, shipping_name, shipping_mobile, shipping_address, created_at)
         VALUES (?, ?, ?, ?, ?, ?, NOW())"
    );
    $stmt->bind_param('idssss', $user_id, $grand_total, $payment_method, $name, $phone, $address);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    $item_stmt = $conn->prepare(
        "INSERT INTO order_items (order_id, product_id, price_at_purchase, quantity) VALUES (?, ?, ?, ?)"
    );
    foreach ($cart_items as $item) {
        $product_id = $item['product_id'] ?? null;

        if ($product_id === null) {
            throw new Exception('A cart item is missing its product_id — cannot place order.');
        }

        $price = $item['price'];
        $qty   = $item['qty'];

        $item_stmt->bind_param('iidi', $order_id, $product_id, $price, $qty);
        $item_stmt->execute();
    }
    $item_stmt->close();

    $conn->close();

    unset($_SESSION['cart']);

    echo json_encode([
        'success'         => true,
        'delivery_window' => $delivery_string,
        'grand_total'     => number_format($grand_total, 2),
    ]);
    exit;

} catch (Throwable $e) {
    // Log the real error for you to debug; never leak raw DB errors to the client.
    error_log('Checkout failed: ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'We could not place your order. Please try again.',
    ]);
    exit;
}