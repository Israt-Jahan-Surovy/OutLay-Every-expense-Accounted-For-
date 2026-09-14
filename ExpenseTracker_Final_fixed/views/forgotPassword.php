<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Expense Tracker</title>
    <script src="js/login.js"></script>
    <link rel="stylesheet" href="css/login.css">
    <style>
        .error-message { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
        .success-message { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>

<div class="login-card">
    <h1 class="brand-title">EXPENSE TRACKER</h1>
    <h2 class="welcome-title">Reset Password</h2>
    <p class="subtitle">Enter your account email and new password</p>

    <?php if (isset($_GET['err'])): ?>
        <div class="error-message"><?php echo htmlspecialchars($_GET['err']); ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['msg'])): ?>
        <div class="success-message"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>

    <form action="../controllers/forgotPasswordController.php" method="POST">
        <div class="form-group">
            <label for="email">Account Email:</label>
            <input type="email" id="email" name="user_email" placeholder="name@company.com" required>
        </div>

        <div class="form-group">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
        </div>

        <button type="submit" class="btn-login" style="margin-top: 15px;">Reset Password</button>
        
        <div class="form-options" style="justify-content: center; margin-top: 15px;">
            <a href="login.php" class="forgot-password">Back to Login</a>
        </div>
    </form>
</div>

</body>
</html>