<?php
session_start();
require_once __DIR__ . '/../models/usersModel.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id          = $_SESSION['user_id'];
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../views/profile/changePassword.php");
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "New passwords do not match.";
        header("Location: ../views/profile/changePassword.php");
        exit();
    }

    $user = getUserById($user_id);

    // Verify current password against stored plain/hashed password
    if ($user && (password_verify($current_password, $user['user_password']) || $current_password === $user['user_password'])) {
        if (changePassword($user_id, $new_password)) {
            $_SESSION['success'] = "Password changed successfully!";
            header("Location: ../views/profile/profile.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update password. Try again.";
        }
    } else {
        $_SESSION['error'] = "Incorrect current password.";
    }

    header("Location: ../views/profile/changePassword.php");
    exit();
}
?>