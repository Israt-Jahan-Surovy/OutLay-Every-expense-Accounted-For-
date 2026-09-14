<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../models/expenseModel.php';

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0; 
$expense_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Call model function
$expense = getExpenseDetailsById($expense_id, $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Details - Expense Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .details-container { max-width: 600px; }
        .details-title { font-size: 2rem; margin-bottom: 35px; color: #ffffff; font-weight: normal; }
        .details-grid { display: grid; grid-template-columns: 160px 1fr; row-gap: 20px; column-gap: 20px; font-size: 1.05rem; }
        .details-label { color: #a0a8b9; }
        .details-value { color: #ffffff; }
        .rejected-reason { color: #ff6b6b; font-weight: 500; }
        .back-link-container { margin-top: 50px; text-align: center; }
        .back-link { color: #ffffff; text-decoration: underline; font-size: 0.95rem; }
        .back-link:hover { color: #e5c185; }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="details-container">
            <h1 class="details-title">Expense details</h1>

            <?php if ($expense): ?>
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
                <div class="details-value"><?= htmlspecialchars($expense['expense_description'] ?? ''); ?></div>

                <div class="details-label">Status</div>
                <div class="details-value status-<?= strtolower(trim($expense['expense_status'] ?? 'pending')); ?>">
                    <?= htmlspecialchars(trim($expense['expense_status'] ?? 'Pending')); ?>
                </div>

                <?php 
                $status = strtolower(trim($expense['expense_status'] ?? ''));
                
                // DISPLAY FOR APPROVED EXPENSES
                if ($status === 'approved'): 
                ?>
                    <div class="details-label">Approved by</div>
                    <div class="details-value">
                        <?= htmlspecialchars(!empty($expense['approver_name']) ? $expense['approver_name'] : 'System Admin / Manager'); ?>
                    </div>
                    <div class="details-label">Approval date</div>
                    <div class="details-value"><?= htmlspecialchars($expense['approval_date'] ?? $expense['expense_date']); ?></div>

                <?php 
                // DISPLAY FOR REJECTED EXPENSES
                elseif ($status === 'rejected'): 
                ?>
                    <div class="details-label">Rejected by</div>
                    <div class="details-value">
                        <?= htmlspecialchars(!empty($expense['approver_name']) ? $expense['approver_name'] : 'System Admin / Manager'); ?>
                    </div>
                    <div class="details-label">Rejection Reason</div>
                    <div class="details-value rejected-reason">
                        <?= htmlspecialchars(!empty($expense['rejected_reason']) ? $expense['rejected_reason'] : 'No reason provided'); ?>
                    </div>
                <?php endif; ?>

            </div>
            <?php else: ?>
                <p style="color: #ffffff;">Expense record not found.</p>
            <?php endif; ?>

            <div class="back-link-container">
                <a href="expenses.php" class="back-link">Back to my expense</a>
            </div>
        </div>
    </div>

</body>
</html>