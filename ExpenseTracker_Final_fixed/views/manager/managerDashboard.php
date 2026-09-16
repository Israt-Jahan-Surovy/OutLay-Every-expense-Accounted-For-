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
    <link rel="stylesheet" href="../css/manager.css">
    <script src="../js/expense.js"></script>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        
    
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <h1 class="greeting" style="margin: 0;">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>

            <div style="position: relative; display: inline-flex; align-items: center;">
                <button id="topNotifBtn" style="background: none; border: none; cursor: pointer; font-size: 22px; color: #fff; position: relative; padding: 0; line-height: 1;">
                    🔔
                    <span id="topNotifBadge" style="display: none; position: absolute; top: -5px; right: -8px; background: #e74c3c; color: white; font-size: 10px; font-weight: bold; border-radius: 50%; padding: 2px 5px;">0</span>
                </button>

        
                <div id="topNotifMenu" style="display: none; position: absolute; right: 0; top: 32px; width: 280px; background: #1a1a1a; border: 1px solid #333; border-radius: 8px; padding: 12px; z-index: 9999; box-shadow: 0 4px 15px rgba(0,0,0,0.6);">
                    <strong style="display: block; border-bottom: 1px solid #333; padding-bottom: 6px; margin-bottom: 8px; color: #fff; font-size: 14px;">Notifications</strong>
                    <div id="topNotifList" style="max-height: 200px; overflow-y: auto; font-size: 13px; color: #ccc;">
                        No notification
                    </div>
                </div>
            </div>
        </div>

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

    <!-- Notification JavaScript Polling -->
    <script>
document.addEventListener("DOMContentLoaded", function () {
    const controllerPath = "../../controllers/notificationController.php";

    function fetchHeaderNotifications() {
        fetch(controllerPath + '?action=fetch')
            .then(res => res.json())
            .then(data => {
                const badge = document.getElementById('topNotifBadge');
                const list = document.getElementById('topNotifList');

                if (Array.isArray(data) && data.length > 0) {
                    // Count only unread items for badge
                    const unreadCount = data.filter(item => parseInt(item.is_read) === 0).length;
                    
                    if (unreadCount > 0) {
                        badge.innerText = unreadCount;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }

                    list.innerHTML = data.map(item => {
                        let rawUrl = (item.redirect_url && item.redirect_url.trim() !== '') 
                            ? item.redirect_url 
                            : 'budget.php';
                        
                        const targetUrl = rawUrl.split('/').pop();
                        const opacity = parseInt(item.is_read) === 1 ? '0.6' : '1';

                        return `
                            <div style="padding: 8px 0; border-bottom: 1px solid #2a2a2a; display: flex; justify-content: space-between; align-items: flex-start; opacity: ${opacity};">
                                <a href="${targetUrl}" style="text-decoration: none; color: inherit; flex-grow: 1; margin-right: 8px;">
                                    <div style="color: #fff; line-height: 1.3;">${item.message}</div>
                                    <small style="color: #777; font-size: 10px;">${item.created_at}</small>
                                </a>
                                <button onclick="deleteNotification(event, ${item.notification_id || item.id})" 
                                        style="background: none; border: none; color: #888; cursor: pointer; font-size: 14px; padding: 0 4px; line-height: 1;"
                                        onmouseover="this.style.color='#e74c3c'" 
                                        onmouseout="this.style.color='#888'">&times;</button>
                            </div>
                        `;
                    }).join('');
                } else {
                    badge.style.display = 'none';
                    list.innerHTML = '<div style="color: #777; text-align: center; padding: 10px 0;">No notifications</div>';
                }
            })
            .catch(err => console.error("Notification Error:", err));
    }

    // Handle individual deletion
    window.deleteNotification = function(event, notifId) {
        event.stopPropagation();
        fetch(controllerPath + '?action=delete&id=' + notifId)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    fetchHeaderNotifications();
                }
            });
    };

    const btn = document.getElementById('topNotifBtn');
    const menu = document.getElementById('topNotifMenu');

    if (btn && menu) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isHidden = menu.style.display === 'none';
            menu.style.display = isHidden ? 'block' : 'none';

            if (isHidden) {
                fetch(controllerPath + '?action=read')
                    .then(() => {
                        document.getElementById('topNotifBadge').style.display = 'none';
                    });
            }
        });

        document.addEventListener('click', function () {
            menu.style.display = 'none';
        });

        fetchHeaderNotifications();
        setInterval(fetchHeaderNotifications, 8000);
    }
});
</script>
</body>
</html>