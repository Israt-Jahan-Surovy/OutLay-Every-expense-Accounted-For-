<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../models/expenseModel.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: ../../login.php"); 
    exit;
}
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'All';

// Fetch user's own expenses list
$expenses = getExpensesByUser($user_id, $status_filter);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Expenses - Expense Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .expenses-title {
            font-size: 2rem;
            color: #ffffff;
            margin-bottom: 25px;
            font-weight: normal;
        }

        .filter-container {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .filter-btn {
            padding: 8px 18px;
            border-radius: 20px;
            text-decoration: none;
            color: #a0a8b9;
            background-color: transparent;
            font-size: 0.95rem;
        }

        .filter-btn.active, .filter-btn:hover {
            background-color: #1c2230;
            color: #ffffff;
        }

        .expense-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #8A8F9C;
            border-radius: 6px;
            overflow: hidden;
        }

        .expense-table th {
            text-align: left;
            padding: 16px 20px;
            color: #ffffff;
            font-weight: 600;
            border-bottom: 1px solid #1c2230;
        }

        .expense-table td {
            padding: 16px 20px;
            color: #000;
            border-bottom: 1px solid #1c2230;
        }

        .status-pending { color: #facc15; font-weight: bold; }
        .status-approved { color: #4ade80; font-weight: bold; }
        .status-rejected { color: #f87171; font-weight: bold; }

        .btn-view {
            color: #4169e1;
            text-decoration: none;
            font-weight: bold;
        }

        .btn-view:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <h1 class="expenses-title">My expenses</h1>

        <div class="filter-container">
            <a href="expenses.php?status=All" class="filter-btn <?= $status_filter === 'All' ? 'active' : '' ?>">All</a>
            <a href="expenses.php?status=Pending" class="filter-btn <?= $status_filter === 'Pending' ? 'active' : '' ?>">Pending</a>
            <a href="expenses.php?status=Approved" class="filter-btn <?= $status_filter === 'Approved' ? 'active' : '' ?>">Approved</a>
            <a href="expenses.php?status=Rejected" class="filter-btn <?= $status_filter === 'Rejected' ? 'active' : '' ?>">Rejected</a>
        </div>

        <table class="expense-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($expenses && mysqli_num_rows($expenses) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($expenses)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['expense_date']); ?></td>
                            <td><?= htmlspecialchars($row['expense_title']); ?></td>
                            <td><?= htmlspecialchars($row['category_name']); ?></td>
                            <td><?= number_format($row['expense_amount']); ?> Tk</td>
                            <td class="status-<?= strtolower($row['expense_status']); ?>">
                                <?= htmlspecialchars($row['expense_status']); ?>
                            </td>
                            <td>
                                <a href="expenseDetails.php?id=<?= $row['expense_id']; ?>" class="btn-view">View</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #a0a8b9;">No expenses found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>