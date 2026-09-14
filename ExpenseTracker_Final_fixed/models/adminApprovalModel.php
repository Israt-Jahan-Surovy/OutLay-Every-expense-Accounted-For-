<?php
// Data-access layer for Admin approving/rejecting Manager expenses.
// Plain procedural mysqli - no exceptions, no try-catch.

require_once __DIR__ . '/adminBase.php';

// Approves or rejects a Manager's expense and records the decision.
// Returns [true, message] on success or [false, message] on failure.
function adminDecideManagerExpense($expenseId, $adminId, $decision, $reason = '')
{
    if (!in_array($decision, ['Approved', 'Rejected'], true)) {
        return [false, 'Invalid decision.'];
    }

    $conn = adminDbOrFail();
    if (!$conn) {
        return [false, 'Could not connect to the database.'];
    }

    mysqli_begin_transaction($conn);

    // 1. Verify expense belongs to a Manager and is currently Pending
    $check = mysqli_prepare(
        $conn,
        "SELECT e.expense_id FROM expensetable e
         JOIN usertable u ON u.user_id = e.user_id
         WHERE e.expense_id = ? AND u.user_role = 'Manager' AND e.expense_status = 'Pending'
         FOR UPDATE"
    );

    if (!$check) {
        mysqli_rollback($conn);
        mysqli_close($conn);
        return [false, 'Could not check the expense.'];
    }

    mysqli_stmt_bind_param($check, 'i', $expenseId);
    mysqli_stmt_execute($check);
    $checkResult = mysqli_stmt_get_result($check);
    $exists = mysqli_num_rows($checkResult) === 1;
    mysqli_stmt_close($check);

    if (!$exists) {
        mysqli_rollback($conn);
        mysqli_close($conn);
        return [false, 'Only pending Manager expenses can be processed by Admin.'];
    }

    // 2. Update status in expensetable
    $update = mysqli_prepare($conn, "UPDATE expensetable SET expense_status = ? WHERE expense_id = ?");
    if (!$update) {
        mysqli_rollback($conn);
        mysqli_close($conn);
        return [false, 'Could not update the expense.'];
    }
    mysqli_stmt_bind_param($update, 'si', $decision, $expenseId);
    $updateOk = mysqli_stmt_execute($update);
    mysqli_stmt_close($update);

    if (!$updateOk) {
        mysqli_rollback($conn);
        mysqli_close($conn);
        return [false, 'Could not update the expense.'];
    }

    // 3. Insert or update record in approvaltable
    $reasonVal = $decision === 'Rejected' ? trim($reason) : null;
    $approve = mysqli_prepare(
        $conn,
        "INSERT INTO approvaltable (approval_date, approval_status, rejected_reason, user_id, expense_id)
         VALUES (CURDATE(), ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            approval_date = CURDATE(),
            approval_status = VALUES(approval_status),
            rejected_reason = VALUES(rejected_reason),
            user_id = VALUES(user_id)"
    );

    if (!$approve) {
        mysqli_rollback($conn);
        mysqli_close($conn);
        return [false, 'Could not save the approval record.'];
    }

    mysqli_stmt_bind_param($approve, 'ssii', $decision, $reasonVal, $adminId, $expenseId);
    $approveOk = mysqli_stmt_execute($approve);
    mysqli_stmt_close($approve);

    if (!$approveOk) {
        mysqli_rollback($conn);
        mysqli_close($conn);
        return [false, 'Could not save the approval record.'];
    }

    mysqli_commit($conn);
    mysqli_close($conn);

    return [
        true,
        $decision === 'Approved'
            ? 'Manager expense approved successfully.'
            : 'Manager expense rejected successfully.'
    ];
}
?>