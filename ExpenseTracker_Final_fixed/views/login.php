<?php
session_start();

// Auto-fill remembered email if cookie exists
$remembered_email = $_COOKIE['remember_user_email'] ?? '';
$is_remembered = !empty($remembered_email);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Expense Tracker</title>
    <link rel="stylesheet" href="css/login.css">
    <style>
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; text-align: center; }
        .field-error { color: #ff6b6b; font-size: 12px; margin-top: 5px; display: block; }
    </style>
</head>
<body>

<div class="login-card">
    <h1 class="brand-title">EXPENSE TRACKER</h1>
    <h2 class="welcome-title">Welcome Back</h2>
    <p class="subtitle">Login to access your account</p>

    <?php if (isset($_GET['notFoundErr'])): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($_GET['notFoundErr']); ?>
        </div>
    <?php endif; ?>

    <form action="../controllers/logincontroller.php" method="POST">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="user_email" 
                   value="<?php echo htmlspecialchars($remembered_email); ?>" 
                   placeholder="name@company.com">
            <?php if (isset($_GET['emailErr'])): ?>
                <span class="field-error"><?php echo htmlspecialchars($_GET['emailErr']); ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="user_password" placeholder="Enter your password">
            <?php if (isset($_GET['passwordErr'])): ?>
                <span class="field-error"><?php echo htmlspecialchars($_GET['passwordErr']); ?></span>
            <?php endif; ?>
        </div>

        <div class="form-options">
            <label class="remember-me">
                <input type="checkbox" name="remember" <?php echo $is_remembered ? 'checked' : ''; ?>> Remember me
            </label>
            <a href="forgotPassword.php" class="forgot-password">Forgot Password ?</a>
        </div>

        <button type="submit" class="btn-login">Login</button>
    </form>
</div>

</body>
</html>