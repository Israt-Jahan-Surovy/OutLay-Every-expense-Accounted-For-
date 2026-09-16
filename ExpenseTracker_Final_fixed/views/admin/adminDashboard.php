<?php
require_once __DIR__ . '/_admin_ui.php';
require_once __DIR__ . '/../../models/adminDashboardModel.php';
$stats=adminDashboardStats(); 
$recent=adminRecentExpenses(5);
adminPageStart('Dashboard','dashboard');
?>

<section class="page-head">
    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
        <h1 style="margin: 0;">Welcome, Admin !</h1>


        <div style="position: relative; display: inline-flex; align-items: center;">
            <button id="topNotifBtn" style="background: none; border: none; cursor: pointer; font-size: 22px; color: #fff; position: relative; padding: 0; line-height: 1;">
                🔔
                <span id="topNotifBadge" style="display: none; position: absolute; top: -5px; right: -8px; background: #e74c3c; color: white; font-size: 10px; font-weight: bold; border-radius: 50%; padding: 2px 5px;">0</span>
            </button>

      
            <div id="topNotifMenu" style="display: none; position: absolute; right: 0; top: 32px; width: 280px; background: #1a1a1a; border: 1px solid #333; border-radius: 8px; padding: 12px; z-index: 9999; box-shadow: 0 4px 15px rgba(0,0,0,0.6);">
                <strong style="display: block; border-bottom: 1px solid #333; padding-bottom: 6px; margin-bottom: 8px; color: #fff; font-size: 14px;">Notifications</strong>
                <div id="topNotifList" style="max-height: 200px; overflow-y: auto; font-size: 13px; color: #ccc;">
                    Loading...
                </div>
            </div>
        </div>
    </div>
</section>

<div class="metric-grid">
    <div class="metric-card"><small>Total Users</small><strong><?=number_format((int)($stats['users_total']??0))?></strong></div>
    <div class="metric-card"><small>Total Expenses</small><strong><?=money($stats['expense_amount_grand_total']??0)?></strong></div>
    <div class="metric-card"><small>Approved</small><strong><?=number_format((int)($stats['approved_total']??0))?></strong></div>
    <div class="metric-card"><small>Pending</small><strong><?=number_format((int)($stats['pending_total']??0))?></strong></div>
</div>

<section class="table-wrap">
    <table>
        <thead>
            <tr><th>Date</th><th>User</th><th>Category</th><th>Amount</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php if(!$recent): ?><tr><td colspan="5" class="empty">No expenses yet.</td></tr><?php endif; ?>
            <?php foreach($recent as $r):?>
                <tr>
                    <td><?=ae(date('Y-m-d',strtotime($r['expense_date'])))?></td>
                    <td><?=ae($r['user_name'])?></td>
                    <td><?=ae($r['category_name'])?></td>
                    <td><?=money($r['expense_amount'])?></td>
                    <td><strong class="status-text status-<?=strtolower($r['expense_status'])?>"><?=ae($r['expense_status'])?></strong></td>
                </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</section>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const controllerPath = "../../controllers/notificationController.php";

    function fetchAdminNotifications() {
        fetch(controllerPath + '?action=fetch')
            .then(res => res.json())
            .then(data => {
                const badge = document.getElementById('topNotifBadge');
                const list = document.getElementById('topNotifList');

                if (Array.isArray(data) && data.length > 0) {
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
                            : 'expenses.php';
                        
                        const targetUrl = rawUrl.split('/').pop();
                        const opacity = parseInt(item.is_read) === 1 ? '0.6' : '1';
                        const notifId = item.notification_id || item.id;

                        return `
                            <div style="padding: 8px 0; border-bottom: 1px solid #2a2a2a; display: flex; justify-content: space-between; align-items: flex-start; opacity: ${opacity};">
                                <a href="${targetUrl}" style="text-decoration: none; color: inherit; flex-grow: 1; margin-right: 8px;">
                                    <div style="color: #fff; line-height: 1.3;">${item.message}</div>
                                    <small style="color: #777; font-size: 10px;">${item.created_at}</small>
                                </a>
                                <button onclick="deleteAdminNotification(event, ${notifId})" 
                                        style="background: none; border: none; color: #888; cursor: pointer; font-size: 16px; padding: 0 4px; line-height: 1; font-weight: bold;"
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
            .catch(err => console.error("Admin Notification Error:", err));
    }

    window.deleteAdminNotification = function(event, notifId) {
        event.stopPropagation();
        fetch(controllerPath + '?action=delete&id=' + notifId)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    fetchAdminNotifications();
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

        fetchAdminNotifications();
        setInterval(fetchAdminNotifications, 8000);
    }
});
</script>
<?php adminPageEnd(); ?>