<?php
require_once __DIR__ . '/_admin_ui.php';require_once __DIR__ . '/../../models/adminExpenseModel.php';$id=(int)($_GET['id']??0);$e=adminExpenseById($id);if(!$e){http_response_code(404);exit('Expense not found.');}adminPageStart('Expense Details','expenses');
?>
<section class="detail-card">
<h1>Expense Details</h1>
<div class="detail-row"><span class="label">Title</span><span class="value"><?=ae($e['expense_title'])?></span></div>
<div class="detail-row"><span class="label">Category</span><span class="value"><?=ae($e['category_name'])?></span></div>
<div class="detail-row"><span class="label">Amount</span><span class="value"><?=money($e['expense_amount'])?></span></div>
<div class="detail-row"><span class="label">Date</span><span class="value"><?=ae(date('Y-m-d',strtotime($e['expense_date'])))?></span></div>
<div class="detail-row"><span class="label">Description</span><span class="value"><?=nl2br(ae($e['expense_description']?:'No description provided.'))?></span></div>
<div class="detail-row"><span class="label">Status</span><span class="value"><strong class="status-text status-<?=strtolower($e['expense_status'])?>"><?=ae($e['expense_status'])?></strong></span></div>
<?php if($e['approval_date']):?><div class="detail-row"><span class="label">Approval date</span><span class="value"><?=ae(date('Y-m-d',strtotime($e['approval_date'])))?></span></div><?php endif;?>
<?php if($e['expense_status']==='Rejected'&&$e['rejected_reason']):?><div class="detail-row"><span class="label">Rejection reason</span><span class="value"><?=nl2br(ae($e['rejected_reason']))?></span></div><?php endif;?>
<div style="text-align:center"><a class="back-link" href="expenses.php">Back to expense</a></div>
</section>
<?php adminPageEnd(); ?>
