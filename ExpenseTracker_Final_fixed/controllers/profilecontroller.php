<?php
session_start();
require_once __DIR__ . '/../models/usersModel.php';

// Ensure user is authenticated regardless of role
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Fallback to session variables if database return is empty
if (!$user) {
    $user = [
        'user_name'  => $_SESSION['user_name'] ?? 'User',
        'user_email' => $_SESSION['user_email'] ?? 'N/A',
        'username'   => $_SESSION['username'] ?? 'N/A',
        'user_role'  => $_SESSION['user_role'] ?? 'Employee',
        'created_at' => $_SESSION['created_at'] ?? '2025-01-10'
    ];
}
?>