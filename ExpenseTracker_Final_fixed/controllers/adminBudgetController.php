<?php
// Controller: handles create, update, and delete actions for Manager and Team budgets.
// Plain procedural PHP - no try-catch, no OOP.

require_once __DIR__ . '/adminCommon.php';
require_once __DIR__ . '/../models/adminBudgetModel.php';

requireAdminRole();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    adminRedirect('budgets.php');
}

$action = $_POST['action'] ?? '';
$budgetId = (int)($_POST['budget_id'] ?? 0);
$adminId = (int)($_SESSION['user_id'] ?? 0);

if ($action === 'save') {

    // Fallback allows form submission using either 'assigned_to' or 'manager_id'
    $targetUserId = (int)($_POST['assigned_to'] ?? ($_POST['manager_id'] ?? 0));
    $budgetType = $_POST['budget_type'] ?? 'Manager'; // Expects 'Manager' or 'Employee'
    $month = trim($_POST['budget_month'] ?? '');     // Expects 'YYYY-MM'
    $amount = (float)($_POST['budget_amount'] ?? 0);
    $parentBudgetId = (int)($_POST['parent_budget_id'] ?? 0);

    if (!in_array($budgetType, ['Manager', 'Employee'], true)) {
        adminFlash('error', 'Invalid budget type specified.');
    } elseif ($targetUserId < 1 || $month === '' || $amount <= 0) {
        adminFlash('error', 'Please select a valid user, target month, and positive amount.');
    } else {
        list($ok, $msg) = adminSaveBudget($adminId, $targetUserId, $budgetType, $month, $amount, $budgetId, $parentBudgetId);
        adminFlash($ok ? 'success' : 'error', $msg);
    }

} elseif ($action === 'delete') {

    if ($budgetId < 1) {
        adminFlash('error', 'Invalid budget record specified.');
    } else {
        $ok = adminDeleteBudget($budgetId);
        adminFlash(
            $ok ? 'success' : 'error',
            $ok ? 'Budget deleted successfully.' : 'Unable to delete budget.'
        );
    }

}

adminRedirect('budgets.php');
?>