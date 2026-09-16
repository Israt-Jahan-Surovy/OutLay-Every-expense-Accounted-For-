<?php

require_once __DIR__ . '/adminCommon.php';
require_once __DIR__ . '/../models/adminBudgetModel.php';
require_once __DIR__ . '/../models/notificationModel.php';

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
        
        if ($ok) {
            // Determine target landing page based on recipient role
            $redirectPath = ($budgetType === 'Manager') ? 'budgets.php' : 'budget.php';
            $formattedMonth = date('F Y', strtotime($month . '-01'));
            
            if ($budgetId > 0) {
                $notifMsg = "Admin updated your budget allocation to " . number_format($amount, 2) . " Tk for " . $formattedMonth . ".";
            } else {
                $notifMsg = "Admin assigned you a budget allocation of " . number_format($amount, 2) . " Tk for " . $formattedMonth . ".";
            }

            // Dispatch notification to recipient
            addNotification($targetUserId, $notifMsg, $redirectPath);
        }

        adminFlash($ok ? 'success' : 'error', $msg);
    }

} elseif ($action === 'delete') {

    if ($budgetId < 1) {
        adminFlash('error', 'Invalid budget record specified.');
    } else {
        // Fetch budget details before deleting to inform the user
        $conn = dbConnection();
        $targetUserId = 0;
        $budgetType = 'Manager';

        if ($conn) {
            // FIXED: Removed non-existent manager_id column from field list
            $getBudgetSql = "SELECT assigned_to, budget_type FROM budgettable WHERE budget_id = ?";
            $stmt = mysqli_prepare($conn, $getBudgetSql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $budgetId);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($res)) {
                    $targetUserId = (int)($row['assigned_to'] ?? 0);
                    $budgetType   = $row['budget_type'] ?? 'Manager';
                }
                mysqli_stmt_close($stmt);
            }
            mysqli_close($conn);
        }

        $ok = adminDeleteBudget($budgetId);

        if ($ok && $targetUserId > 0) {
            $redirectPath = ($budgetType === 'Manager') ? 'budgets.php' : 'budget.php';
            $notifMsg = "Admin removed one of your assigned budget allocations.";
            addNotification($targetUserId, $notifMsg, $redirectPath);
        }

        adminFlash(
            $ok ? 'success' : 'error',
            $ok ? 'Budget deleted successfully.' : 'Unable to delete budget.'
        );
    }

}

adminRedirect('budgets.php');
?>