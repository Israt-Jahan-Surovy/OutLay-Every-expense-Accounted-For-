<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../../models/dbConnect.php';

$conn = dbConnection();

if (!$conn)
{
    echo "Database connection failed: " . mysqli_connect_error();
    exit();
}
// Fetch categories from database for dropdown
$category_query = "SELECT category_id, category_name FROM categorytable ORDER BY category_name ASC";
$category_result = mysqli_query($conn, $category_query);

// Determine dashboard redirect based on user role
$user_role = $_SESSION['user_role'] ?? 'Employee';
$dashboard_url = ($user_role === 'Manager') ? 'managerDashboard.php' : 'employeeDashboard.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Personal Expense - Expense Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/expense.js"></script>
    <style>
        .form-container {
            max-width: 500px;
        }

        .form-title {
            color: #e5c185;
            font-size: 1.8rem;
            font-weight: normal;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #ffffff;
            font-size: 1rem;
            margin-bottom: 8px;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            background-color: #1c2230;
            border: 1px solid #283042;
            border-radius: 4px;
            color: #ffffff;
            font-size: 0.95rem;
            outline: none;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #e5c185;
        }

        .form-group select option {
            background-color: #1c2230;
            color: #ffffff;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-cancel {
            flex: 1;
            padding: 12px;
            background-color: #e5c185;
            color: #0b0e14;
            border: none;
            border-radius: 4px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }

        .btn-submit {
            flex: 1;
            padding: 12px;
            background-color: #8c939d;
            color: #0b0e14;
            border: none;
            border-radius: 4px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-submit:hover {
            background-color: #a0a8b9;
        }

        .btn-cancel:hover {
            background-color: #d4ae6e;
        }

        .alert-error {
            background-color: #2c1619;
            color: #f87171;
            padding: 10px 14px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #142a1e;
            color: #4ade80;
            padding: 10px 14px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="form-container">
            <h1 class="form-title">Add personal expense</h1>

            <?php if (isset($_SESSION['status_error'])): ?>
                <div class="alert-error"><?php echo $_SESSION['status_error']; unset($_SESSION['status_error']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['status_success'])): ?>
                <div class="alert-success"><?php echo $_SESSION['status_success']; unset($_SESSION['status_success']); ?></div>
            <?php endif; ?>

            <form action="../../controllers/expensecontroller.php" method="POST">
                <div class="form-group">
                    <label>Title*</label>
                    <input type="text" name="expense_title" placeholder="e.g. Lunch with team" required>
                </div>

                <div class="form-group">
                    <label>Category*</label>
                    <select name="category_id" required>
                        <option value="" disabled selected>Select category</option>
                        <?php if ($category_result && mysqli_num_rows($category_result) > 0): ?>
                            <?php while ($cat = mysqli_fetch_assoc($category_result)): ?>
                                <option value="<?php echo $cat['category_id']; ?>">
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <option value="1">Food</option>
                            <option value="2">Travel</option>
                            <option value="3">Office Supplies</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Amount*</label>
                    <input type="number" step="0.01" name="expense_amount" placeholder="Enter amount" required>
                </div>

                <div class="form-group">
                    <label>Date*</label>
                    <input type="date" name="expense_date" required>
                </div>

                <div class="form-group">
                    <label>Description*</label>
                    <textarea name="expense_description" placeholder="Enter description" required></textarea>
                </div>

                <div class="btn-group">
                    <a href="<?php echo $dashboard_url; ?>" class="btn-cancel">Cancel</a>
                    <button type="submit" name="add_expense" class="btn-submit">Submit</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>

<?php mysqli_close($conn); ?>