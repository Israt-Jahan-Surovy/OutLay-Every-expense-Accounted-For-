<?php
session_start();
require_once '../../models/dbConnect.php';

$conn = dbConnection();

if (!$conn) {
    die("Database connection failed.");
}
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: ../../login.php"); 
    exit;
}
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : 'All';

if (in_array($status_filter, ['Pending', 'Approved', 'Rejected'])) {
    $base_query = "
        SELECT e.expense_id, e.expense_date, e.expense_title, c.category_name, e.expense_amount, e.expense_status, a.rejected_reason 
        FROM expensetable e
        JOIN categorytable c ON e.category_id = c.category_id
        LEFT JOIN approvaltable a ON e.expense_id = a.expense_id
        WHERE e.user_id = ? AND e.expense_status = ?
        ORDER BY e.expense_date DESC
    ";
    $stmt = mysqli_prepare($conn, $base_query);
    mysqli_stmt_bind_param($stmt, "is", $user_id, $status_filter);
    
} else {
    $base_query = "
        SELECT e.expense_id, e.expense_date, e.expense_title, c.category_name, e.expense_amount, e.expense_status, a.rejected_reason 
        FROM expensetable e
        JOIN categorytable c ON e.category_id = c.category_id
        LEFT JOIN approvaltable a ON e.expense_id = a.expense_id
        WHERE e.user_id = ?
        ORDER BY e.expense_date DESC
    ";
    $stmt = mysqli_prepare($conn, $base_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
}

mysqli_stmt_execute($stmt);
$expenses_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Expenses - Expense Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/expense.js"></script>
    <style>
        .page-header {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: normal;
        }

        /* Status Filter Tabs */
        .filter-tabs {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filter-tab {
            color: #ffffff;
            text-decoration: none;
            padding: 6px 18px;
            border-radius: 20px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .filter-tab.active {
            background-color: #121620;
            color: #ffffff;
            border: 1px solid #283042;
        }

        .filter-tab:hover:not(.active) {
            color: #e5c185;
        }

        /* Alert Messages */
        .alert {
            padding: 12px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .alert-success {
            background-color: #1a382b;
            color: #4ade80;
            border: 1px solid #22543d;
        }

        .alert-error {
            background-color: #3f1d1d;
            color: #f87171;
            border: 1px solid #632323;
        }

        /* Table Action Link Styling */
        .action-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .action-link {
            color: #4169e1;
            text-decoration: none;
        }

        .action-link:hover {
            text-decoration: underline;
        }

        .action-link.edit-link {
            color: #e5c185;
        }

        .action-link.delete-link {
            color: #f87171;
            background: none;
            border: none;
            padding: 0;
            font: inherit;
            cursor: pointer;
        }

        .action-link.delete-link:hover {
            text-decoration: underline;
        }

        .no-data {
            text-align: center;
            color: #888;
            padding: 20px;
        }
    </style>
</head>
<body>

    <!-- Render Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="main-content">
        
        <div class="page-header">
            <h1 class="page-title">My expenses</h1>
            
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <a href="expenses.php?status=All" class="filter-tab <?php echo ($status_filter === 'All') ? 'active' : ''; ?>">All</a>
                <a href="expenses.php?status=Pending" class="filter-tab <?php echo ($status_filter === 'Pending') ? 'active' : ''; ?>">Pending</a>
                <a href="expenses.php?status=Approved" class="filter-tab <?php echo ($status_filter === 'Approved') ? 'active' : ''; ?>">Approved</a>
                <a href="expenses.php?status=Rejected" class="filter-tab <?php echo ($status_filter === 'Rejected') ? 'active' : ''; ?>">Rejected</a>
            </div>
        </div>

        <!-- Flash Notifications -->
        <?php if (isset($_SESSION['status_success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo htmlspecialchars($_SESSION['status_success']); 
                    unset($_SESSION['status_success']); 
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['status_error'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo htmlspecialchars($_SESSION['status_error']); 
                    unset($_SESSION['status_error']); 
                ?>
            </div>
        <?php endif; ?>

        <!-- Expenses Table -->
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
                <?php if ($expenses_result && mysqli_num_rows($expenses_result) > 0): ?>
                    <?php while ($expense = mysqli_fetch_assoc($expenses_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($expense['expense_date']); ?></td>
                            <td><?php echo htmlspecialchars($expense['expense_title']); ?></td>
                            <td><?php echo htmlspecialchars($expense['category_name']); ?></td>
                            <td><?php echo number_format($expense['expense_amount']); ?> Tk</td>
                            <td class="status-<?php echo strtolower($expense['expense_status']); ?>">
                                <?php echo htmlspecialchars($expense['expense_status']); ?>
                            </td>
                            <td>
                                <div class="action-group">
                                    <a href="viewExpense.php?id=<?php echo $expense['expense_id']; ?>" class="action-link">View</a>
                                    
                                    <?php if ($expense['expense_status'] === 'Pending'): ?>
                                        <a href="editExpense.php?id=<?php echo $expense['expense_id']; ?>" class="action-link edit-link">Edit</a>
                                        
                                        <form action="../../controllers/expensecontroller.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                            <input type="hidden" name="expense_id" value="<?php echo $expense['expense_id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="action-link delete-link">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-data">No expense records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

</body>
</html>

<?php 
mysqli_stmt_close($stmt);
mysqli_close($conn); 
?>