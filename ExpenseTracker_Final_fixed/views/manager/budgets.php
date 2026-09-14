<?php
session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? '') !== "manager") {
    header("Location: ../login.php");
    exit();
}

require_once '../../models/expenseModel.php';
require_once '../../models/budgetModel.php';

$manager_id    = $_SESSION["user_id"];
$current_month = date("Y-m");
$budget_month  = date("Y-m-01");

// Get Admin -> Manager total allocation
$admin_allocated = getManagerAdminBudget(
    $manager_id,
    $current_month
);

// Manager's personal budget = 30%
$my_allocated = $admin_allocated * 0.30;

// Team budget = 70%
$team_allocated = $admin_allocated * 0.70;

// Manager's own spending
$my_spent = (float)getApprovedTotalByUser(
    $manager_id,
    $current_month
);

// Manager's remaining personal budget
$my_remaining = $my_allocated - $my_spent;

// Team members
$team_members = getTeamMembers($manager_id);

// Team employee spending
$team_spent = (float)getTeamSpentTotal(
    $manager_id,
    $current_month
);

// Team remaining budget
$team_remaining = $team_allocated - $team_spent;

$team_table      = getTeamSpendingTable($manager_id, $current_month);
$team_members = getTeamMembers($manager_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Budget - Expense Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/manager.css">
    <script src="../js/expense.js"></script>
    <style>
        .alert-success {
            background-color: #142a1e;
            color: #4ade80;
            padding: 10px 14px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-error {
            background-color: #2a1414;
            color: #f87171;
            padding: 10px 14px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .assign-form {
            display: flex;
            gap: 12px;
            align-items: flex-end;
            margin: 20px 0 30px;
            flex-wrap: wrap;
        }
        .assign-form .form-group label {
            display: block;
            color: #a0a8b9;
            font-size: 0.85rem;
            margin-bottom: 6px;
        }
        .assign-form select,
        .assign-form input {
            padding: 10px 12px;
            background-color: #1c2230;
            border: 1px solid #283042;
            border-radius: 4px;
            color: #ffffff;
        }
        .inline-update-form {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .inline-update-form input[type="number"] {
            width: 100px;
            padding: 6px 8px;
            background-color: #1c2230;
            border: 1px solid #283042;
            border-radius: 4px;
            color: #ffffff;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
            background-color: #2563eb;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-sm:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <h1 class="greeting">Team budget</h1>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>

        <!-- PERSONAL BUDGET SECTION -->
        <div class="section-header">
            <div class="section-title">My budget</div>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Allocated</div>
                <div class="stat-value"><?php echo number_format($my_allocated, 2); ?> Tk</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Spent</div>
                <div class="stat-value"><?php echo number_format($my_spent, 2); ?> Tk</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Remaining</div>
                <div class="stat-value"><?php echo number_format($my_remaining, 2); ?> Tk</div>
            </div>
        </div>

        <!-- TEAM OVERALL BUDGET SECTION -->
        <div class="section-header">
            <div class="section-title">Team budget</div>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Allocated</div>
                <div class="stat-value"><?php echo number_format($team_allocated, 2); ?> Tk</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Spent</div>
                <div class="stat-value"><?php echo number_format($team_spent, 2); ?> Tk</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Remaining</div>
                <div class="stat-value"><?php echo number_format($team_remaining, 2); ?> Tk</div>
            </div>
        </div>

        <!-- ASSIGN EMPLOYEE BUDGET FORM -->
        <div class="section-header">
            <div class="section-title">Assign employee budget</div>
        </div>
        <form action="../../controllers/budgetController.php" method="POST" class="assign-form">
            <div class="form-group">
                <label>Employee</label>
<select name="employee_id" required>
    <option value="" disabled selected>Select employee</option>
    <?php foreach ($team_members as $emp): ?>
        <option value="<?php echo $emp['user_id']; ?>">
            <?php echo htmlspecialchars($emp['user_name']); ?>
        </option>
    <?php endforeach; ?>
</select>
            </div>
            <div class="form-group">
                <label>Monthly budget</label>
                <input type="number" step="0.01" name="budget_amount" placeholder="Enter amount" required>
            </div>
            <input type="hidden" name="budget_month" value="<?php echo $budget_month; ?>">
            <button type="submit" name="assign_budget" class="btn-add-expense">Assign</button>
        </form>

        <!-- TEAM SPENDING & EDIT TABLE -->
        <div class="section-header">
            <div class="section-title">Team spending this month</div>
        </div>
        <table class="expense-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Allocated</th>
                    <th>Spent</th>
                    <th>Remaining</th>
                    <th>Action / Modify</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($team_table)): ?>
                    <?php foreach ($team_table as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo number_format($row['budget_amount'], 2); ?> Tk</td>
                            <td><?php echo number_format($row['spent'], 2); ?> Tk</td>
                            <td><?php echo number_format($row['remaining'], 2); ?> Tk</td>
                            <td>
                                <!-- Inline Update Form -->
                                <form action="../../controllers/budgetController.php" method="POST" class="inline-update-form">
                                    <input type="hidden" name="budget_id" value="<?php echo $row['budget_id']; ?>">
                                    <input type="number" step="0.01" name="budget_amount" value="<?php echo $row['budget_amount']; ?>" required>
                                    <button type="submit" name="update_budget" class="btn-sm">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">No employee budgets assigned yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>