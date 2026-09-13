<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require '../Database/database.php';
require '../validation.php';

$result = validateSignupInput($_POST);

if (!empty($result['errors'])) {
    $message = implode(' ', $result['errors']);
    header('Location: signup.php?status=error&message=' . urlencode($message));
    exit;
}

$fullName        = $result['data']['full_name'];
$username        = $result['data']['username'];
$email           = $result['data']['email'];
$mobileNumber    = $result['data']['mobile_number'];
$deliveryAddress = $result['data']['delivery_address'];
$password        = $result['data']['password'];

// Make sure the username or email isn't already taken
$stmt = $conn->prepare('SELECT user_id FROM users WHERE username = ? OR email = ?');
$stmt->bind_param('ss', $username, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    header('Location: signup.php?status=error&message=' . urlencode('Username or email is already taken.'));
    exit;
}
$stmt->close();

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    'INSERT INTO users (full_name, username, email, password_hash, mobile_number, delivery_address)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param('ssssss', $fullName, $username, $email, $passwordHash, $mobileNumber, $deliveryAddress);
$stmt->execute();

$newId = $stmt->insert_id;
$stmt->close();
$conn->close();

header('Location: ../success.php?id=' . $newId);
exit;