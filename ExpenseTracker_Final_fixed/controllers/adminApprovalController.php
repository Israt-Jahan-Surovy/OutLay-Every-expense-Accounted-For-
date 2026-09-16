<?php


require_once __DIR__ . '/adminCommon.php';
require_once __DIR__ . '/../models/adminApprovalModel.php';

requireAdminRole();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    adminRedirect('expenses.php?role=Manager&status=Pending');
}

adminVerifyCsrf();

$id = (int)($_POST['expense_id'] ?? 0);
$decision = $_POST['decision'] ?? '';
$reason = trim($_POST['rejected_reason'] ?? '');

if ($decision === 'Rejected' && $reason === '') {
    adminFlash('error', 'Rejection reason is required.');
    adminRedirect('rejectExpense.php?id=' . $id);
}

list($ok, $msg) = adminDecideManagerExpense($id, (int)$_SESSION['user_id'], $decision, $reason);
adminFlash($ok ? 'success' : 'error', $msg);

adminRedirect('expenses.php?role=Manager');
?>
