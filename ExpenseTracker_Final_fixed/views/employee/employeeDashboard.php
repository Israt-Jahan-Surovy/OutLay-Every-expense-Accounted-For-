<?php
// Include the controller to access data variables
require_once '../../controllers/expensecontroller.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - Expense Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/employee.css">
    <script src="../js/expense.js"></script>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <h1 class="greeting">Good Morning, <?php echo htmlspecialchars($user_name); ?>!</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">My budget</div>
                <div class="stat-value"><?php echo number_format($my_budget); ?> Tk</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Spent</div>
                <div class="stat-value"><?php echo number_format($spent); ?> Tk</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending</div>
                <div class="stat-value"><?php echo $pending_count; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Remaining</div>
                <div class="stat-value"><?php echo number_format($remaining); ?> Tk</div>
            </div>
        </div>

        <div class="section-header">
            <div class="section-title">Recent expenses</div>
            <a href="addExpense.php" class="btn-add-expense">Add Expense</a>
        </div>

        <table class="expense-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $count = 0;
                if ($recent_result && mysqli_num_rows($recent_result) > 0): 
                    while ($expense = mysqli_fetch_assoc($recent_result)): 
                        if ($count >= 5) break; 
                        $count++;
                ?>
                        <tr>
                            <td><?php echo htmlspecialchars($expense['expense_title']); ?></td>
                            <td><?php echo htmlspecialchars($expense['category_name']); ?></td>
                            <td><?php echo number_format($expense['expense_amount']); ?> Tk</td>
                            <td class="status-<?php echo strtolower($expense['expense_status']); ?>">
                                <?php echo htmlspecialchars($expense['expense_status']); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No recent expenses found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="../js/employee.js"></script>
</body>
</html>
