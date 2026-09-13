<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require '../Database/database.php';
require '../validation.php';

$result = validateLoginInput($_POST);

if (!empty($result['errors'])) {
    $message = implode(' ', $result['errors']);
    header('Location: login.php?status=error&message=' . urlencode($message));
    exit;
}

$username = $result['data']['username'];
$password = $result['data']['password'];

$stmt = $conn->prepare('SELECT user_id, username, password_hash FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password_hash'])) {
    $conn->close();
    header('Location: login.php?status=error&message=' . urlencode('Invalid username or password.'));
    exit;
}

session_regenerate_id(true);
$_SESSION['user_id']  = $user['user_id'];
$_SESSION['username'] = $user['username'];

$conn->close();
header('Location: ../index.php');
exit;