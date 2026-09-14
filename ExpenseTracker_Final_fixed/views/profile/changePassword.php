<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Compute dynamic paths based on execution context
$project_root = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -3));
$base_css = $project_root . '/views/css';
$base_controllers = $project_root . '/controllers';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Expense Tracker</title>

    <!-- Dynamic CSS & JS Paths -->
    <link rel="stylesheet" href="<?= $base_css ?>/style.css">
    <link rel="stylesheet" href="<?= $base_css ?>/profile.css">
    <link rel="stylesheet" href="<?= $base_css ?>/employee.css">
    <script src="../js/expense.js"></script>
</head>

<body>
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content Wrapper (Class matches employee.css layout rules) -->
    <main class="main-content">
        <h1 class="page-title">Change Password</h1>

        <div class="profile-container">

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert-error" style="color: #ff4d4d; background: rgba(255, 77, 77, 0.1); padding: 10px 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?php
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert-success" style="color: #4caf50; background: rgba(76, 175, 80, 0.1); padding: 10px 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?php
                        echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <form action="<?= $base_controllers ?>/changePasswordController.php" method="POST">
                <div class="profile-details">
                    <div class="detail-row">
                        <label for="current_password" class="detail-label">Current Password:</label>
                        <input type="password" id="current_password" name="current_password" class="inline-input" required>
                    </div>

                    <div class="detail-row">
                        <label for="new_password" class="detail-label">New Password:</label>
                        <input type="password" id="new_password" name="new_password" class="inline-input" required>
                    </div>

                    <div class="detail-row">
                        <label for="confirm_password" class="detail-label">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="inline-input" required>
                    </div>
                </div>

                <div class="profile-actions" style="margin-top: 25px;">
                    <button type="submit" class="btn-gold">Update Password</button>
                    <a href="profile.php" class="btn-grey">Cancel</a>
                </div>
            </form>
        </div>
    </main>

</body>
</html>