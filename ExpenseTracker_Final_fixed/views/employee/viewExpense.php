<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../models/dbConnect.php';

$conn = dbConnection();
if (!$conn) {
    die("Database connection failed.");
}

$expense_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = "
    SELECT 
        e.expense_id, 
        e.expense_date, 
        e.expense_title, 
        e.expense_description,
        e.expense_amount, 
        e.expense_status, 
        c.category_name, 
        u.user_name AS employee_name,
        a.rejected_reason,
        a.approval_date,
        mgr.user_name AS manager_name,
        mgr.user_role AS manager_role
    FROM expensetable e
    LEFT JOIN categorytable c ON e.category_id = c.category_id
    LEFT JOIN usertable u ON e.user_id = u.user_id
    LEFT JOIN approvaltable a ON e.expense_id = a.expense_id
    LEFT JOIN usertable mgr ON a.user_id = mgr.user_id
    WHERE e.expense_id = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $expense_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$expense = mysqli_fetch_assoc($result);

if (!$expense) {
    die("<h2 style='color:white; text-align:center; margin-top:50px;'>Expense record not found.</h2>");
}

$status = trim($expense['expense_status'] ?? '');
$reason = trim($expense['rejected_reason'] ?? '');

$isRejected = (strcasecmp($status, 'Rejected') === 0);
$isApproved = (strcasecmp($status, 'Approved') === 0);

// Format manager display name with role
$managerName = !empty($expense['manager_name']) ? $expense['manager_name'] : 'System Admin';
$managerRole = !empty($expense['manager_role']) ? '(' . ucfirst($expense['manager_role']) . ')' : '';
$approvedByText = trim("$managerName $managerRole");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Details - Expense Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .details-container { max-width: 600px; margin-top: 20px; }
        .details-grid { display: grid; grid-template-columns: 160px 1fr; row-gap: 20px; font-size: 1.1rem; align-items: center; }
        .details-label { color: #a0a6b5; }
        .details-value { color: #ffffff; font-weight: 500; }
        .rejected-reason { color: #ff6b6b; font-weight: 500; }
        .back-container { margin-top: 40px; text-align: center; max-width: 600px; }
        .back-link { color: #ffffff; text-decoration: underline; font-size: 1.1rem; }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <h1 class="page-title" style="font-size: 2.2rem; margin-bottom: 30px;">Expense details</h1>

        <div class="details-container">
            <div class="details-grid">
                
                <div class="details-label">Employee</div>
                <div class="details-value"><?= htmlspecialchars($expense['employee_name'] ?? 'N/A'); ?></div>

                <div class="details-label">Title</div>
                <div class="details-value"><?= htmlspecialchars($expense['expense_title']); ?></div>

                <div class="details-label">Category</div>
                <div class="details-value"><?= htmlspecialchars($expense['category_name'] ?? 'N/A'); ?></div>

                <div class="details-label">Amount</div>
                <div class="details-value"><?= number_format($expense['expense_amount']); ?> Tk</div>

                <div class="details-label">Date</div>
                <div class="details-value"><?= htmlspecialchars($expense['expense_date']); ?></div>

                <div class="details-label">Description</div>
                <div class="details-value"><?= htmlspecialchars($expense['expense_description']); ?></div>

                <div class="details-label">Status</div>
                <div class="details-value">
                    <strong><?= htmlspecialchars($status); ?></strong>
                </div>

       
                <?php if ($isApproved): ?>
                    <div class="details-label">Approved by</div>
                    <div class="details-value">
                        <?= htmlspecialchars($approvedByText); ?>
                    </div>

                    <?php if (!empty($expense['approval_date'])): ?>
                        <div class="details-label">Approval date</div>
                        <div class="details-value">
                            <?= htmlspecialchars($expense['approval_date']); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

          
                <?php if ($isRejected): ?>
                    <div class="details-label">Rejection Reason</div>
                    <div class="details-value rejected-reason">
                        <?= !empty($reason) ? htmlspecialchars($reason) : 'No reason provided by manager.'; ?>
                    </div>
                <?php endif; ?>

            </div>

            <div class="back-container">
                <a href="expenses.php" class="back-link">Back</a>
            </div>
        </div>
    </div>

</body>
</html>

<?php 
mysqli_stmt_close($stmt);
mysqli_close($conn); 
?>