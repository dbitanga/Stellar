<?php
$host     = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty
$database = "stellar_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>