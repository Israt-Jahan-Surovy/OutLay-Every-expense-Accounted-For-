<?php
session_start();
require_once '../../models/dbConnect.php';

$conn = dbConnection();
if (!$conn) {
    die("Database connection failed.");
}

// Ensure user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$expense_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch expense details ensuring it belongs to the user and is still Pending
$expense_query = "
    SELECT expense_id, expense_title, category_id, expense_amount, expense_status 
    FROM expensetable 
    WHERE expense_id = ? AND user_id = ?
";
$stmt = mysqli_prepare($conn, $expense_query);
mysqli_stmt_bind_param($stmt, "ii", $expense_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$expense = mysqli_fetch_assoc($result);

// Redirect or block if expense doesn't exist or is already Approved/Rejected
if (!$expense) {
    die("<h2 style='color:white; text-align:center; margin-top:50px;'>Expense not found.</h2>");
}

if ($expense['expense_status'] !== 'Pending') {
    die("<h2 style='color:white; text-align:center; margin-top:50px;'>Only pending expenses can be edited.</h2>");
}

// Fetch categories for dropdown
$category_query = "SELECT category_id, category_name FROM categorytable ORDER BY category_name ASC";
$category_result = mysqli_query($conn, $category_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Expense - Expense Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .form-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
        }

        .form-container {
            width: 100%;
            max-width: 480px;
            background-color: #121620;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        }

        .form-title {
            color: #e5c185;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            color: #ffffff;
            font-size: 1rem;
            margin-bottom: 8px;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select {
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
        .form-group select:focus {
            border-color: #e5c185;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 35px;
        }

        .btn-submit {
            flex: 1;
            padding: 12px;
            background-color: #e5c185;
            color: #0b0e14;
            border: none;
            border-radius: 4px;
            font-size: 1.05rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: #d4ae6e;
        }

        .btn-cancel {
            flex: 1;
            padding: 12px;
            background-color: #a0a6b5;
            color: #0b0e14;
            border: none;
            border-radius: 4px;
            font-size: 1.05rem;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .btn-cancel:hover {
            background-color: #8c939d;
        }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="form-wrapper">
            <div class="form-container">
                <h1 class="form-title">Edit Expense</h1>

                <form action="../../controllers/expensecontroller.php" method="POST">
                    <!-- Form Identifiers -->
                    <input type="hidden" name="expense_id" value="<?php echo $expense['expense_id']; ?>">
                    <input type="hidden" name="action" value="update">

                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="expense_title" value="<?php echo htmlspecialchars($expense['expense_title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" required>
                            <?php while ($cat = mysqli_fetch_assoc($category_result)): ?>
                                <option value="<?php echo $cat['category_id']; ?>" <?php echo ($cat['category_id'] == $expense['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" step="0.01" name="expense_amount" value="<?php echo htmlspecialchars($expense['expense_amount']); ?>" required>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn-submit">Edit</button>
                        <a href="expenses.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>

<?php 
mysqli_stmt_close($stmt);
mysqli_close($conn); 
?>