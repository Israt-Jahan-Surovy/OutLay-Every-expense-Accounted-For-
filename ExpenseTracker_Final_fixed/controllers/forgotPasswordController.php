<?php
session_start();
require_once "../models/usersModel.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email            = trim($_POST["user_email"] ?? "");
    $new_password     = $_POST["new_password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        header("Location: ../views/forgotPassword.php?err=" . urlencode("All fields are required."));
        exit();
    }

    if ($new_password !== $confirm_password) {
        header("Location: ../views/forgotPassword.php?err=" . urlencode("Passwords do not match."));
        exit();
    }

    if (resetPasswordByEmail($email, $new_password)) {
        header("Location: ../views/login.php?notFoundErr=" . urlencode("Password reset successful. Please login."));
    } else {
        header("Location: ../views/forgotPassword.php?err=" . urlencode("Email address not found."));
    }
    exit();
}

header("Location: ../views/forgotPassword.php");
exit();