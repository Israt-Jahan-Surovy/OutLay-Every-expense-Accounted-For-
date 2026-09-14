<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../models/approvalModel.php';

$expense_id = (int)($_GET['id'] ?? 0);
$expense = getExpenseById($expense_id); // Fetch full detail including rejection_reason, approver_name, and approval_date

if (!$expense) {
    header("Location: approvals.php?error=Expense not found");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee View Expense - Expense Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .page-title { 
            font-size: 2.2rem; 
            color: #ffffff; 
            margin-bottom: 35px; 
            font-family: serif; 
            font-weight: normal; 
        }

        .details-grid {
            display: grid;
            grid-template-columns: 180px 1fr;
            row-gap: 18px;
            column-gap: 20px;
            max-width: 650px;
            font-size: 1.05rem;
        }

        .details-label {
            color: #a0a8b9;
        }

        .details-value {
            color: #ffffff;
        }

        .back-link-wrap {
            margin-top: 45px;
            max-width: 650px;
            text-align: center;
        }

        .btn-back {
            color: #ffffff;
            text-decoration: underline;
            font-size: 1rem;
        }

        .btn-back:hover {
            color: #e5c185;
        }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <h1 class="page-title">Expense details</h1>

        <div class="details-grid">
            <div class="details-label">Title</div>
            <div class="details-value"><?= htmlspecialchars($expense['expense_title'] ?? ''); ?></div>

            <div class="details-label">Category</div>
            <div class="details-value"><?= htmlspecialchars($expense['category_name'] ?? ''); ?></div>

            <div class="details-label">Amount</div>
            <div class="details-value"><?= number_format($expense['expense_amount'] ?? 0); ?> Tk</div>

            <div class="details-label">Date</div>
            <div class="details-value"><?= htmlspecialchars($expense['expense_date'] ?? ''); ?></div>

            <div class="details-label">Description</div>
            <div class="details-value"><?= htmlspecialchars($expense['expense_description'] ?? 'N/A'); ?></div>

            <div class="details-label">Status</div>
            <div class="details-value"><?= htmlspecialchars($expense['expense_status'] ?? ''); ?></div>

            <?php if ($expense['expense_status'] === 'Rejected'): ?>
                <div class="details-label">Rejection Reason</div>
                <div class="details-value"><?= htmlspecialchars($expense['rejection_reason'] ?? 'N/A'); ?></div>

                <div class="details-label">Approval date</div>
                <div class="details-value"><?= htmlspecialchars($expense['approval_date'] ?? 'N/A'); ?></div>
            <?php endif; ?>

            <?php if ($expense['expense_status'] === 'Approved'): ?>
                <div class="details-label">Approved by</div>
                <div class="details-value"><?= htmlspecialchars($expense['approver_name'] ?? 'Admin'); ?></div>

                <div class="details-label">Approval date</div>
                <div class="details-value"><?= htmlspecialchars($expense['approval_date'] ?? 'N/A'); ?></div>
            <?php endif; ?>
        </div>

        <div class="back-link-wrap">
            <a href="approvals.php" class="btn-back">Back to my expense</a>
        </div>
    </div>

</body>
</html>