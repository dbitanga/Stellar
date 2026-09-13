<?php

function validateRequired(string $value, string $label): ?string
{
    return trim($value) === '' ? "$label is required." : null;
}

function validateEmailFormat(string $value): ?string
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) ? null : "Enter a valid email address.";
}

function validatePasswordStrength(string $value): ?string
{
    if (strlen($value) < 8) {
        return "Password must be at least 8 characters long.";
    }
    if (!preg_match('/[a-z]/', $value)) {
        return "Password must contain at least one lowercase letter.";
    }
    if (!preg_match('/[0-9]/', $value)) {
        return "Password must contain at least one number.";
    }
    return null;
}

function validatePasswordMatch(string $password, string $confirm): ?string
{
    return $password === $confirm ? null : "Passwords do not match.";
}

function validateMobileNumber(string $value): ?string
{
    $digitsOnly = preg_replace('/[^0-9]/', '', $value);
    if (strlen($digitsOnly) < 7 || strlen($digitsOnly) > 15) {
        return "Enter a valid mobile number.";
    }
    return null;
}

function validateLoginInput(array $post): array
{
    $username = trim($post['username'] ?? '');
    $password = $post['password'] ?? '';

    $errors = array_filter([
        validateRequired($username, 'Username'),
        validateRequired($password, 'Password'),
    ]);
    $errors = array_values($errors);

    return [
        'errors' => $errors,
        'data'   => ['username' => $username, 'password' => $password],
    ];
}

function validateSignupInput(array $post): array
{
    $fullName        = trim($post['full_name'] ?? '');
    $username        = trim($post['username'] ?? '');
    $email           = trim($post['email'] ?? '');
    $mobileNumber    = trim($post['mobile_number'] ?? '');
    $deliveryAddress = trim($post['delivery_address'] ?? '');
    $password        = $post['password'] ?? '';
    $confirm         = $post['confirm_password'] ?? '';

    $errors = array_filter([
        validateRequired($fullName, 'Full name'),
        validateRequired($username, 'Username'),
        validateEmailFormat($email),
        validateMobileNumber($mobileNumber),
        validateRequired($deliveryAddress, 'Delivery address'),
        validatePasswordStrength($password),
        validatePasswordMatch($password, $confirm),
    ]);
    $errors = array_values($errors);

    if (empty($errors)) {
        $fullName = htmlspecialchars($fullName);
        $username = htmlspecialchars($username);
    }

    return [
        'errors' => $errors,
        'data'   => [
            'full_name'        => $fullName,
            'username'         => $username,
            'email'            => $email,
            'mobile_number'    => $mobileNumber,
            'delivery_address' => $deliveryAddress,
            'password'         => $password,
        ],
    ];
}