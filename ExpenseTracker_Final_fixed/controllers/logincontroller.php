<?php
session_start();
require_once "../models/usersModel.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_email    = trim($_POST["user_email"] ?? "");
    $user_password = $_POST["user_password"] ?? "";
    $remember      = isset($_POST["remember"]);

    $emailErr = "";
    $passwordErr = "";
    $hasErr = false;

    if (empty($user_email)) {
        $hasErr = true;
        $emailErr = "Email Cannot be Empty";
    }

    if (empty($user_password)) {
        $hasErr = true;
        $passwordErr = "Password Cannot be Empty";
    }

    if ($hasErr) {
        header(
            "Location: ../views/login.php?emailErr=" . urlencode($emailErr) .
            "&passwordErr=" . urlencode($passwordErr)
        );
        exit();
    }

    $user = login($user_email, $user_password);

    if ($user) {
        // Save session data
        $_SESSION["user_id"]    = $user["user_id"];
        $_SESSION["user_role"]  = strtolower($user["user_role"]);
        $_SESSION["user_name"]  = $user["user_name"];
        $_SESSION["user_email"] = $user["user_email"]; 

        // Handle Remember Me Cookie (Expires in 30 days)
        if ($remember) {
            setcookie("remember_user_id", $user["user_id"], time() + (86400 * 30), "/");
            setcookie("remember_user_email", $user["user_email"], time() + (86400 * 30), "/");
        } else {
            // Clear existing cookies if unchecked
            setcookie("remember_user_id", "", time() - 3600, "/");
            setcookie("remember_user_email", "", time() - 3600, "/");
        }

        $role = strtolower($user["user_role"]);

        if ($role === "admin") {
            header("Location: ../views/admin/adminDashboard.php");
        } else if ($role === "manager") {
            header("Location: ../views/manager/managerDashboard.php");
        } else if ($role === "employee") {
            header("Location: ../views/employee/employeeDashboard.php");
        } else {
            header("Location: ../views/login.php?notFoundErr=" . urlencode("User Role Not Found"));
        }

        exit();
    }

    header("Location: ../views/login.php?notFoundErr=" . urlencode("Invalid Email, Password, or Inactive Account"));
    exit();
}

header("Location: ../views/login.php");
exit();