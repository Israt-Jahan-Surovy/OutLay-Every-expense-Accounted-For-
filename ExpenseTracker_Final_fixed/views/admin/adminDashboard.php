<?php
require_once __DIR__ . '/_admin_ui.php';
require_once __DIR__ . '/../../models/adminDashboardModel.php';
$stats=adminDashboardStats(); $recent=adminRecentExpenses(5);
adminPageStart('Dashboard','dashboard');
?>
<section class="page-head"><div><h1>Welcome, Admin !</h1></div></section>
<div class="metric-grid">
<div class="metric-card"><small>Total Users</small><strong><?=number_format((int)($stats['users_total']??0))?></strong></div>
<div class="metric-card"><small>Total Expenses</small><strong><?=money($stats['expense_amount_grand_total']??0)?></strong></div>
<div class="metric-card"><small>Approved</small><strong><?=number_format((int)($stats['approved_total']??0))?></strong></div>
<div class="metric-card"><small>Pending</small><strong><?=number_format((int)($stats['pending_total']??0))?></strong></div>
</div>
<section class="table-wrap"><table><thead><tr><th>Date</th><th>User</th><th>Category</th><th>Amount</th><th>Status</th></tr></thead><tbody>
<?php if(!$recent): ?><tr><td colspan="5" class="empty">No expenses yet.</td></tr><?php endif; ?>
<?php foreach($recent as $r):?><tr><td><?=ae(date('Y-m-d',strtotime($r['expense_date'])))?></td><td><?=ae($r['user_name'])?></td><td><?=ae($r['category_name'])?></td><td><?=money($r['expense_amount'])?></td><td><strong class="status-text status-<?=strtolower($r['expense_status'])?>"><?=ae($r['expense_status'])?></strong></td></tr><?php endforeach;?>
</tbody></table></section>
<?php adminPageEnd(); ?>
