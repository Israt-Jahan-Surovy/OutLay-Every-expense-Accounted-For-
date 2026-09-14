<?php
require_once __DIR__ . '/_admin_ui.php';require_once __DIR__ . '/../../models/adminExpenseModel.php';$id=(int)($_GET['id']??0);$e=adminExpenseById($id);if(!$e||$e['user_role']!=='Manager'){http_response_code(404);exit('Manager expense not found.');}adminPageStart('Reject Expense','expenses');
?>
<div style="display:flex;justify-content:center"><div class="reject-card"><h1>Rejected</h1>
<div class="reject-summary"><?=ae($e['user_name'])?>, <?=ae($e['category_name'])?>, <?=money($e['expense_amount'])?>, <?=ae($e['expense_date'])?></div>
<form action="../../controllers/adminApprovalController.php" method="post"><input type="hidden" name="csrf_token" value="<?=ae(adminCsrfToken())?>"><input type="hidden" name="expense_id" value="<?=$id?>"><input type="hidden" name="decision" value="Rejected">
<label>Rejection Reason</label><textarea name="rejected_reason" required placeholder="Explain why are you rejecting"></textarea>
<div class="approval-buttons"><button class="btn btn-danger">Confirm</button><a class="btn btn-gray" href="approveExpense.php?id=<?=$id?>">Cancel</a></div>
</form></div></div>
<?php adminPageEnd(); ?>
