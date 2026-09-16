<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../models/dbConnect.php';
require_once '../models/approvalModel.php';
require_once '../models/notificationModel.php';

$approver_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expense_id = intval($_POST['expense_id'] ?? 0);

    // Fetch expense details (user_id & title) before updating to send notification
    $expense_user_id = 0;
    $expense_title   = 'Expense Request';
    
    if ($expense_id > 0) {
        $conn = dbConnection();
        if ($conn) {
            $info_sql = "SELECT user_id, expense_title FROM expensetable WHERE expense_id = ?";
            $info_stmt = mysqli_prepare($conn, $info_sql);
            if ($info_stmt) {
                mysqli_stmt_bind_param($info_stmt, "i", $expense_id);
                mysqli_stmt_execute($info_stmt);
                $info_res = mysqli_stmt_get_result($info_stmt);
                if ($info_row = mysqli_fetch_assoc($info_res)) {
                    $expense_user_id = (int)$info_row['user_id'];
                    $expense_title   = $info_row['expense_title'];
                }
                mysqli_stmt_close($info_stmt);
            }
            mysqli_close($conn);
        }
    }

    // APPROVE EXPENSE HANDLER
    if (isset($_POST['approve_expense'])) {
        // Record in approval table
        recordApproval($expense_id, $approver_id, 'Approved', null);
        
        // Update main expense status
        $conn = dbConnection();
        $sql = "UPDATE expensetable SET expense_status='Approved' WHERE expense_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $expense_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        // Send approval notification to the employee
        if ($expense_user_id > 0 && function_exists('addNotification')) {
            $msg = "Your expense '{$expense_title}' has been Approved.";
            addNotification($expense_user_id, $msg, 'expenses.php');
        }

        header("Location: ../views/manager/approvals.php?success=Expense Approved Successfully");
        exit();
    } 
    
    // REJECT EXPENSE HANDLER
    if (isset($_POST['reject_expense'])) {
        $reason = trim($_POST['reason'] ?? '');

        // Record rejection reason in approval table
        recordApproval($expense_id, $approver_id, 'Rejected', $reason);
        
        // Update main expense status
        $conn = dbConnection();
        $sql = "UPDATE expensetable SET expense_status='Rejected' WHERE expense_id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $expense_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        // Send rejection notification to the employee
        if ($expense_user_id > 0 && function_exists('addNotification')) {
            $msg = "Your expense '{$expense_title}' was Rejected.";
            if (!empty($reason)) {
                $msg .= " Reason: " . $reason;
            }
            addNotification($expense_user_id, $msg, 'expenses.php');
        }

        header("Location: ../views/manager/approvals.php?success=Expense Rejected Successfully");
        exit();
    }
    
}
?>