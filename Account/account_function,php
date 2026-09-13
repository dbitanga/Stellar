<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../Login/login.php');
    exit;
}

require '../Database/database.php';
require '../validation.php';

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'delete_account') {
    $stmt = $conn->prepare('DELETE FROM users WHERE user_id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();

    header('Location: ../index.php?status=success&message=' . urlencode('Your account has been deleted.'));
    exit;
}

if ($action === 'update_info') {
    $fullName        = trim($_POST['full_name'] ?? '');
    $username        = trim($_POST['username'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $mobileNumber    = trim($_POST['mobile_number'] ?? '');
    $deliveryAddress = trim($_POST['delivery_address'] ?? '');

    $errors = array_filter([
        validateRequired($fullName, 'Full name'),
        validateRequired($username, 'Username'),
        validateEmailFormat($email),
        validateMobileNumber($mobileNumber),
        validateRequired($deliveryAddress, 'Delivery address'),
    ]);
    $errors = array_values($errors);

    if (!empty($errors)) {
        $conn->close();
        header('Location: account.php?status=error&message=' . urlencode(implode(' ', $errors)));
        exit;
    }

    // Make sure the new username/email isn't already used by someone else
    $stmt = $conn->prepare('SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?');
    $stmt->bind_param('ssi', $username, $email, $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        $conn->close();
        header('Location: account.php?status=error&message=' . urlencode('That username or email is already in use.'));
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare(
        'UPDATE users SET full_name = ?, username = ?, email = ?, mobile_number = ?, delivery_address = ?
         WHERE user_id = ?'
    );
    $stmt->bind_param('sssssi', $fullName, $username, $email, $mobileNumber, $deliveryAddress, $userId);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    $_SESSION['username'] = $username;

    header('Location: account.php?status=success&message=' . urlencode('Your information has been updated.'));
    exit;
}

$conn->close();
header('Location: account.php');
exit;