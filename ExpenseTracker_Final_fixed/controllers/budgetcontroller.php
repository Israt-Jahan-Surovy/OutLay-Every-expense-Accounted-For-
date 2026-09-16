<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once "../models/dbConnect.php";
require_once "../models/budgetModel.php";
require_once "../models/notificationModel.php";


if (!isset($_SESSION["user_id"])) {
    header("Location: ../views/login.php");
    exit();
}

$user_id   = (int)$_SESSION["user_id"];
$user_role = strtolower($_SESSION["user_role"] ?? '');

$redirect_url = ($user_role === 'admin') 
    ? "../views/admin/budgets.php" 
    : "../views/manager/budgets.php";

// ASSIGN BUDGEt
if (isset($_POST['assign_budget'])) {
    $manager_id    = $_SESSION['user_id'];
    $employee_id   = (int)$_POST['employee_id'];
    $budget_amount = (float)$_POST['budget_amount'];
    $raw_month     = $_POST['budget_month'] ?? date('Y-m');
    $budget_month  = date('Y-m-01', strtotime($raw_month));

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

    $result = assignEmployeeBudget($manager_id, $employee_id, $budget_amount, $budget_month);

    if (is_array($result) && !empty($result['success'])) {
        $msg = "A new budget of " . number_format($budget_amount, 2) . " Tk has been assigned to you for " . date('F Y', strtotime($budget_month)) . ".";

        // Dispatch notification to employee
        addNotification($employee_id, $msg, 'budget.php');

        $msg_text = !empty($result['message']) ? $result['message'] : "Budget assigned successfully";
        header("Location: ../views/manager/budgets.php?success=" . urlencode($msg_text));
    } else {
        $error_msg = is_array($result) && !empty($result['message']) ? $result['message'] : "Failed to assign budget";
        header("Location: ../views/manager/budgets.php?error=" . urlencode($error_msg));
    }
    exit();
}

if (isset($_POST["update_budget"]) || isset($_POST["update_employee_budget"])) {
    $budget_id = (int)($_POST["budget_id"] ?? 0);
    $amount    = (float)($_POST["budget_amount"] ?? $_POST["amount"] ?? 0);

    if ($budget_id <= 0 || $amount <= 0) {
        header("Location: " . $redirect_url . "?error=" . urlencode("Please enter a valid amount."));
        exit();
    }

    if ($user_role === "admin") {
        $result = updateManagerBudget($budget_id, $amount);

        //notification to manager on successful budget update by Admin
        if ($result) {
            $conn = dbConnection();
            if ($conn) {
                $get_mgr_sql = "SELECT assigned_to FROM budgettable WHERE budget_id = ?";
                $mgr_stmt = mysqli_prepare($conn, $get_mgr_sql);
                if ($mgr_stmt) {
                    mysqli_stmt_bind_param($mgr_stmt, "i", $budget_id);
                    mysqli_stmt_execute($mgr_stmt);
                    $mgr_res = mysqli_stmt_get_result($mgr_stmt);
                    if ($row = mysqli_fetch_assoc($mgr_res)) {
                        $target_mgr_id = (int)$row['assigned_to'];
                        $notif_msg = "Admin updated your manager budget allocation to " . number_format($amount, 2) . " Tk.";
                        addNotification($target_mgr_id, $notif_msg, 'budgets.php');
                    }
                    mysqli_stmt_close($mgr_stmt);
                }
                mysqli_close($conn);
            }
        }
    } elseif ($user_role === "manager") {
        $result = updateEmployeeBudget($budget_id, $user_id, $amount);
        

        if ($result) {
            $conn = dbConnection();
            if ($conn) {
                // Fetch target assigned_to ID linked to this budget ID
                $get_emp_sql = "SELECT assigned_to FROM budgettable WHERE budget_id = ?";
                $emp_stmt = mysqli_prepare($conn, $get_emp_sql);
                if ($emp_stmt) {
                    mysqli_stmt_bind_param($emp_stmt, "i", $budget_id);
                    mysqli_stmt_execute($emp_stmt);
                    $emp_res = mysqli_stmt_get_result($emp_stmt);
                    if ($row = mysqli_fetch_assoc($emp_res)) {
                        $target_emp_id = (int)$row['assigned_to'];
                        $notif_msg = "Your budget allocation has been updated to " . number_format($amount, 2) . " Tk.";
                        
                        // Send notification 
                        addNotification($target_emp_id, $notif_msg, 'budget.php');
                    }
                    mysqli_stmt_close($emp_stmt);
                }
                mysqli_close($conn);
            }
        }
    }

    if (!empty($result)) {
        header("Location: " . $redirect_url . "?success=" . urlencode("Budget updated successfully."));
    } else {
        header("Location: " . $redirect_url . "?error=" . urlencode("Failed to update budget."));
    }
    exit();
}


header("Location: " . $redirect_url);
exit();
?>