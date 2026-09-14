<?php
require_once __DIR__ . '/_admin_ui.php'; require_once __DIR__ . '/../../models/adminExpenseModel.php'; require_once __DIR__ . '/../../models/adminCategoryModel.php';
$q=trim($_GET['q']??'');$category=(int)($_GET['category']??0);$status=$_GET['status']??'All';$from=$_GET['from']??'';$to=$_GET['to']??'';$role=$_GET['role']??'All';$expenses=adminExpenses($q,$category,$status,$from,$to,$role);$categories=adminCategories();adminPageStart('Expenses','expenses');
?>
<section class="page-head"><div><h1>Expenses</h1></div></section>
<form class="filterbar" method="get">
<label class="filter-field"><span>From</span><input type="date" name="from" value="<?=ae($from)?>"></label>
<label class="filter-field"><span>To</span><input type="date" name="to" value="<?=ae($to)?>"></label>
<input name="q" value="<?=ae($q)?>" placeholder="Search title or user...">
<select name="role"><option value="All">All Roles</option><option <?=$role==='Manager'?'selected':''?>>Manager</option><option <?=$role==='Employee'?'selected':''?>>Employee</option></select>
<select name="category"><option value="0">All Categories</option><?php foreach($categories as $c):?><option value="<?=(int)$c['category_id']?>" <?=$category===(int)$c['category_id']?'selected':''?>><?=ae($c['category_name'])?></option><?php endforeach;?></select>
<select name="status"><option>All</option><option <?=$status==='Pending'?'selected':''?>>Pending</option><option <?=$status==='Approved'?'selected':''?>>Approved</option><option <?=$status==='Rejected'?'selected':''?>>Rejected</option></select>
<button class="btn btn-gold">Search</button>
</form>
<section class="table-wrap"><table><thead><tr><th>Date</th><th>User</th><th>Category</th><th>Amount</th><th>Status</th><th class="actions-col">Action</th></tr></thead><tbody>
<?php if(!$expenses):?><tr><td colspan="6" class="empty">No expenses found.</td></tr><?php endif; ?>
<?php foreach($expenses as $e):?><tr><td><?=ae(date('Y-m-d',strtotime($e['expense_date'])))?></td><td><?=ae($e['user_name'])?></td><td><?=ae($e['category_name'])?></td><td><?=money($e['expense_amount'])?></td><td><strong class="status-text status-<?=strtolower($e['expense_status'])?>"><?=ae($e['expense_status'])?></strong></td><td class="actions">
<?php if($e['user_role']==='Manager' && $e['expense_status']==='Pending'):?>
<a class="btn-approve-sm" href="approveExpense.php?id=<?=(int)$e['expense_id']?>">Approve</a>
<a class="btn-reject-sm" href="rejectExpense.php?id=<?=(int)$e['expense_id']?>">Reject</a>
<?php else: ?>
<a class="link-view" href="expenseDetails.php?id=<?=(int)$e['expense_id']?>">View</a>
<?php endif; ?>
</td></tr><?php endforeach;?>
</tbody></table></section>
<?php adminPageEnd(); ?>
