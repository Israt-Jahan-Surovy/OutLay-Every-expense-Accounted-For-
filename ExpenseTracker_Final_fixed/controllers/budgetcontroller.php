<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once "../models/dbConnect.php";
require_once "../models/budgetModel.php";

// Authenticate session existence
if (!isset($_SESSION["user_id"])) {
    header("Location: ../views/login.php");
    exit();
}

$user_id   = (int)$_SESSION["user_id"];
$user_role = strtolower($_SESSION["user_role"] ?? '');

// Set view redirect paths according to user role
$redirect_url = ($user_role === 'admin') 
    ? "../views/admin/budgets.php" 
    : "../views/manager/budgets.php";

/**
 * ==========================================
 * 1. ASSIGN BUDGET (POST HANDLER)
 * ==========================================
 */
if (isset($_POST['assign_budget'])) {
    $manager_id    = $_SESSION['user_id'];
    $employee_id   = (int)$_POST['employee_id'];
    $budget_amount = (float)$_POST['budget_amount'];
    $raw_month     = $_POST['budget_month'] ?? date('Y-m');

    // Standardize month to YYYY-MM-01 format so DB date queries match consistently
    $budget_month  = date('Y-m-01', strtotime($raw_month));

    // 1. Permanently link employee to this manager if unassigned
    $conn = dbConnection();
    if ($conn) {
        $update_sql = "UPDATE usertable 
                       SET manager_id = ? 
                       WHERE user_id = ? AND (manager_id IS NULL OR manager_id = 0)";
        $stmt = mysqli_prepare($conn, $update_sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $manager_id, $employee_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        mysqli_close($conn);
    }

    // 2. Assign the employee budget
    $success = assignEmployeeBudget($manager_id, $employee_id, $budget_amount, $budget_month);

    if ($success) {
        header("Location: ../views/manager/budgets.php?success=" . urlencode("Budget assigned successfully"));
    } else {
        header("Location: ../views/manager/budgets.php?error=" . urlencode("Failed to assign budget"));
    }
    exit();
}

/**
 * ==========================================
 * 2. UPDATE / EDIT BUDGET (POST HANDLER)
 * ==========================================
 */
if (isset($_POST["update_budget"]) || isset($_POST["update_employee_budget"])) {
    $budget_id = (int)($_POST["budget_id"] ?? 0);
    $amount    = (float)($_POST["budget_amount"] ?? $_POST["amount"] ?? 0);

    if ($budget_id <= 0 || $amount <= 0) {
        header("Location: " . $redirect_url . "?error=" . urlencode("Please enter a valid amount."));
        exit();
    }

    if ($user_role === "admin") {
        $result = updateManagerBudget($budget_id, $amount);
    } elseif ($user_role === "manager") {
        $result = updateEmployeeBudget($budget_id, $user_id, $amount);
    } else {
        $result = false;
    }

    if ($result) {
        header("Location: " . $redirect_url . "?success=" . urlencode("Budget updated successfully."));
    } else {
        header("Location: " . $redirect_url . "?error=" . urlencode("Failed to update budget."));
    }
    exit();
}

// Fallback redirect if accessed directly without POST parameters
header("Location: " . $redirect_url);
exit();
?>