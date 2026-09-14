<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $name = $_POST['name'] ?? '';
    $specs = $_POST['specs'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $image = $_POST['image'] ?? '';

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] === $product_id) {
            $item['qty']++;
            $found = true;
            break;
        }
    }
    unset($item);

    if (!$found) {
        $_SESSION['cart'][] = [
            'product_id' => $product_id,
            'name' => $name,
            'specs' => $specs,
            'price' => $price,
            'image' => $image,
            'qty' => 1
        ];
    }

    echo json_encode([
        'success' => true,
        'item' => [
            'name' => $name,
            'image' => $image
        ]
    ]);
    exit;
}