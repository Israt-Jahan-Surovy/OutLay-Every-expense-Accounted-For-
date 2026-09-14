<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../models/dbConnect.php';
require_once '../models/approvalModel.php';

$approver_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expense_id = intval($_POST['expense_id'] ?? 0);

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

        header("Location: ../views/manager/approvals.php?success=Expense Approved Successfully");
        exit();
    } 
    
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

        header("Location: ../views/manager/approvals.php?success=Expense Rejected Successfully");
        exit();
    }
}
?>