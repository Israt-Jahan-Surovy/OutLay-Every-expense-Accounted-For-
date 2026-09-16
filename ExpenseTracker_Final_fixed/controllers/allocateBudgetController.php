<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once "../models/dbConnect.php";
require_once "../models/budgetModel.php";
require_once "../models/budgetValidation.php";
require_once "../models/notificationModel.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../views/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $manager_id = (int)$_SESSION['user_id'];
    $employee_id = (int)$_POST['employee_id'];
    $amount = (float)$_POST['budget_amount'];
    $month = $_POST['budget_month']; 
    $parent_budget_id = !empty($_POST['parent_budget_id']) ? (int)$_POST['parent_budget_id'] : null;

    list($is_valid, $message) = canAllocateEmployeeBudget($manager_id, $employee_id, $amount, $month);

    if (!$is_valid) {
        header("Location: ../views/manager/budgets.php?error=" . urlencode($message));
        exit();
    }

    $month_full_date = $month . "-01";
    $result = assignEmployeeBudget($manager_id, $employee_id, $amount, $month_full_date, $parent_budget_id);

    $is_success = is_array($result) ? !empty($result['success']) : (bool)$result;

    if ($is_success) {
        // Send Notification to Employee
        $formatted_month = date('F Y', strtotime($month_full_date));
        $notif_msg = "Your manager assigned you a budget of " . number_format($amount, 2) . " Tk for " . $formatted_month . ".";
        
        // Target full view path for the employee notification click
        addNotification($employee_id, $notif_msg, 'views/employee/budget.php');

        // Redirect MANAGER back to their own budgets dashboard
        header("Location: ../views/manager/budgets.php?success=" . urlencode("Employee budget successfully assigned."));
    } else {
        $error_msg = (is_array($result) && !empty($result['message'])) ? $result['message'] : "Failed to assign budget.";
        header("Location: ../views/manager/budgets.php?error=" . urlencode($error_msg));
    }
    exit();
}
?>