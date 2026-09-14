<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$current_page = basename($_SERVER['PHP_SELF']);
$user_role = strtolower($_SESSION['user_role'] ?? 'employee');

// Dynamically compute project base path (works anywhere: local XAMPP or production server)
$project_root = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -3));
$base_views = $project_root . '/views';
$base_controllers = $project_root . '/controllers';
?>
<aside class="sidebar">
    <div class="sidebar-top">
        <div class="brand-header">
    <div class="brand-text">
        <span class="brand-title">OutLay</span>
        <span class="brand-tagline">Every Expense, Accounted For</span>
    </div>
    <span class="menu-icon">&#9776;</span>
</div>

        <ul class="nav-links">
            <?php if ($user_role === 'admin'): ?>
                <li class="<?= $current_page === 'adminDashboard.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/admin/adminDashboard.php">Dashboard</a>
                </li>
                <li class="<?= $current_page === 'users.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/admin/users.php">Users</a>
                </li>
                <li class="<?= $current_page === 'categories.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/admin/categories.php">Categories</a>
                </li>
                <li class="<?= $current_page === 'budgets.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/admin/budgets.php">Budgets</a>
                </li>
                <li class="<?= $current_page === 'expenses.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/admin/expenses.php">Expenses</a>
                </li>
                <li class="<?= $current_page === 'reports.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/reports/reports.php">Reports</a>
                </li>
                <li class="<?= $current_page === 'profile.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/profile/profile.php">My Profile</a>
                </li>

            <?php elseif ($user_role === 'manager'): ?>
                <li class="<?= $current_page === 'managerDashboard.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/manager/managerDashboard.php">Dashboard</a>
                </li>
                <li class="<?= $current_page === 'addExpense.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/manager/addExpense.php">Add Expense</a>
                </li>
                <li class="<?= $current_page === 'expenses.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/manager/expenses.php">My Expenses</a>
                </li>
                <li class="<?= $current_page === 'approvals.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/manager/approvals.php">Approvals</a>
                </li>
                <li class="<?= $current_page === 'budgets.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/manager/budgets.php">Budgets</a>
                </li>
                <li class="<?= $current_page === 'reports.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/reports/reports.php">Reports</a>
                </li>
                <li class="<?= $current_page === 'profile.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/profile/profile.php">My Profile</a>
                </li>

            <?php else: ?>
                <li class="<?= $current_page === 'employeeDashboard.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/employee/employeeDashboard.php">Dashboard</a>
                </li>
                <li class="<?= $current_page === 'addExpense.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/employee/addExpense.php">Add Expense</a>
                </li>
                <li class="<?= $current_page === 'expenses.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/employee/expenses.php">My Expenses</a>
                </li>
                <li class="<?= $current_page === 'budget.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/employee/budget.php">Budget</a>
                </li>
                <li class="<?= $current_page === 'reports.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/reports/reports.php">Reports</a>
                </li>
                <li class="<?= $current_page === 'profile.php' ? 'active' : '' ?>">
                    <a href="<?= $base_views ?>/profile/profile.php">My Profile</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="sidebar-bottom">
        <a href="<?= $base_controllers ?>/logoutcontroller.php" class="logout-btn">Log Out</a>
    </div>
</aside>