<?php
session_start();
require_once "../models/budgetModel.php";
require_once "../models/budgetValidation.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $manager_id = (int)$_SESSION['user_id'];
    $employee_id = (int)$_POST['employee_id'];
    $amount = (float)$_POST['budget_amount'];
    $month = $_POST['budget_month']; // Format: 'YYYY-MM'
    $parent_budget_id = !empty($_POST['parent_budget_id']) ? (int)$_POST['parent_budget_id'] : null;

    // Run dynamic allocation check
    list($is_valid, $message) = canAllocateEmployeeBudget($manager_id, $employee_id, $amount, $month);

    if (!$is_valid) {
        header("Location: ../views/allocate_budget.php?error=" . urlencode($message));
        exit();
    }

    // Save allocation upon successful validation
    $month_full_date = $month . "-01";
    $success = assignEmployeeBudget($manager_id, $employee_id, $amount, $month_full_date, $parent_budget_id);

    if ($success) {
        header("Location: ../views/team_budget.php?success=" . urlencode("Employee budget successfully assigned."));
    } else {
        header("Location: ../views/allocate_budget.php?error=" . urlencode("Failed to assign budget."));
    }
    exit();
}
?>