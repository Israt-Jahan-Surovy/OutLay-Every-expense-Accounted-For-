<?php

require_once '../../controllers/expensecontroller.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Budget - Expense Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/employee.css">
    <script src="../js/expense.js"></script>
    <style>
        .progress-card {
            background-color: #121620;
            border: 1px solid #283042;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            color: #ffffff;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .progress-bar-bg {
            width: 100%;
            height: 10px;
            background-color: #1c2230;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
    
        <h1 class="greeting">Personal Expense Budget Monitoring</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Assigned Monthly Budget</div>
                <div class="stat-value"><?php echo number_format($my_budget ?? 0); ?> Tk</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Spent (Approved)</div>
                <div class="stat-value"><?php echo number_format($spent ?? 0); ?> Tk</div>
            </div>
            
   
            <div class="stat-card">
                <div class="stat-label">Remaining Available Budget</div>
                <div class="stat-value" style="color: <?php echo (($remaining ?? 0) < 0) ? '#f87171' : '#4ade80'; ?>;">
                    <?php echo number_format($remaining ?? 0); ?> Tk
                </div>
            </div>
        </div>

     
        <?php 
            $budget_val = $my_budget ?? 0;
            $spent_val = $spent ?? 0;
            $percentage = ($budget_val > 0) ? min(100, round(($spent_val / $budget_val) * 100)) : 0;
            $bar_color = ($percentage >= 90) ? '#f87171' : '#e5c185';
        ?>
        <div class="progress-card">
            <div class="progress-header">
                <span>Budget Usage</span>
                <span><?php echo $percentage; ?>% Spent</span>
            </div>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: <?php echo $percentage; ?>%; background-color: <?php echo $bar_color; ?>;"></div>
            </div>
        </div>

        <div class="section-header">
            <div class="section-title">Personal Expenses Against Budget</div>
        </div>

        <table class="expense-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (isset($recent_result) && mysqli_num_rows($recent_result) > 0): 
                    while ($expense = mysqli_fetch_assoc($recent_result)): 
                ?>
                        <tr>
                            <td><?php echo htmlspecialchars($expense['expense_date']); ?></td>
                            <td><?php echo htmlspecialchars($expense['category_name']); ?></td>
                            <td><?php echo number_format($expense['expense_amount']); ?> Tk</td>
                            <td class="status-<?php echo strtolower($expense['expense_status']); ?>">
                                <?php echo htmlspecialchars($expense['expense_status']); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No expenses recorded against this month's budget.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="../js/employee.js"></script>
</body>
</html>