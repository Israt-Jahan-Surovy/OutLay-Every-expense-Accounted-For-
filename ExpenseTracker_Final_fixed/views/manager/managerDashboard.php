<?php
session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? '') !== "manager") {
    header("Location: ../login.php");
    exit();
}

require_once '../../models/expenseModel.php';
require_once '../../models/budgetModel.php';

$manager_id     = $_SESSION["user_id"];
$user_name      = $_SESSION["user_name"] ?? "Manager";
$current_month  = date("Y-m");

$team_ids       = getTeamMemberIds($manager_id);
$team_count     = count($team_ids);
$pending_count  = countPendingTeamExpenses($manager_id);

// Admin's total allocation to the Manager
$admin_allocated = getManagerAdminBudget($manager_id, $current_month);

// Manager gets 30%
$my_allocated = $admin_allocated * 0.30;

// Team gets 70%
$team_allocated = $admin_allocated * 0.70;

// Manager's own approved spending
$my_spent = (float)getApprovedTotalByUser(
    $manager_id,
    $current_month
);

// Manager's remaining personal budget
$my_remaining = $my_allocated - $my_spent;

// Team's approved employee spending
$team_spent = (float)getTeamSpentTotal(
    $manager_id,
    $current_month
);

// Team's remaining budget
$team_remaining = $team_allocated - $team_spent;

$pending_result = getExpensesByUser($manager_id, "All");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - Expense Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/expense.js"></script>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <h1 class="greeting">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Team members</div>
                <div class="stat-value"><?php echo $team_count; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending review</div>
                <div class="stat-value"><?php echo $pending_count; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">My remaining budget</div>
                <div class="stat-value"><?php echo number_format($my_remaining); ?> Tk</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Team's remaining budget</div>
                <div class="stat-value"><?php echo number_format($team_remaining); ?> Tk</div>
            </div>
        </div>

        <div class="section-header">
            <div class="section-title">My recent expenses</div>
            <a href="../employee/addExpense.php" class="btn-add-expense">Add Expense</a>
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
                if ($pending_result && mysqli_num_rows($pending_result) > 0):
                    while ($expense = mysqli_fetch_assoc($pending_result)):
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

</body>
</html>