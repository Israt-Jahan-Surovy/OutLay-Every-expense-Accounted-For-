<?php
require_once __DIR__ . '/_admin_ui.php';require_once __DIR__ . '/../../models/adminExpenseModel.php';$id=(int)($_GET['id']??0);$e=adminExpenseById($id);if(!$e||$e['user_role']!=='Manager'){http_response_code(404);exit('Manager expense not found.');}adminPageStart('Approve Expense','expenses');
?>
<div style="display:flex;justify-content:center"><div class="approval-card"><h1>Approve Expense</h1>
<label><span>Managere name</span><div class="readonly-box"><?=ae($e['user_name'])?></div></label>
<div class="approval-grid">
<label><span>Expense Title</span><div class="readonly-box"><?=ae($e['expense_title'])?></div></label>
<label><span>Date</span><div class="readonly-box"><?=ae($e['expense_date'])?></div></label>
<label><span>Category</span><div class="readonly-box"><?=ae($e['category_name'])?></div></label>
<label><span>Amount</span><div class="readonly-box"><?=money($e['expense_amount'])?></div></label>
</div>
<label><span>Description</span><div class="readonly-box description"><?=ae($e['expense_description']?:'No description provided.')?></div></label>
<form class="approval-buttons" action="../../controllers/adminApprovalController.php" method="post"><input type="hidden" name="csrf_token" value="<?=ae(adminCsrfToken())?>"><input type="hidden" name="expense_id" value="<?=$id?>"><button class="btn btn-gold" name="decision" value="Approved">Approve</button><a class="btn btn-gray" href="rejectExpense.php?id=<?=$id?>">Reject</a></form>
</div></div>
<?php adminPageEnd(); ?>
