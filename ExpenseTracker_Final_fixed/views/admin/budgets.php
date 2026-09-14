<?php
require_once __DIR__ . '/_admin_ui.php'; 
require_once __DIR__ . '/../../models/adminBudgetModel.php';

$month = $_GET['month'] ?? date('Y-m');
$budgets = adminManagerBudgets($month);
$managers = adminManagers();
$editId = (int)($_GET['edit'] ?? 0);
$edit = $editId ? adminBudgetById($editId) : null;

adminPageStart('Budget', 'budgets');
?>
<section class="page-head">
    <div><h1>Team Budgets</h1></div>
    <button class="btn btn-gold" data-open-modal="budgetCreate">+ Assign Budget</button>
</section>

<form class="filterbar" method="get">
    <label style="color:var(--muted);font-size:13px">
        Month&nbsp;<input type="month" name="month" value="<?=ae($month)?>">
    </label>
    <button class="btn btn-outline">Apply</button>
</form>

<section class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Manager</th>
                <th>Budget amount</th>
                <th>Month</th>
                <th class="actions-col">Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if(!$budgets): ?>
            <tr><td colspan="4" class="empty">No manager budget assigned for this month.</td></tr>
        <?php endif; ?>
        <?php foreach($budgets as $b): ?>
            <tr>
                <td><?=ae($b['user_name'])?></td>
                <td><?=money($b['budget_amount'])?></td>
                <td><?=ae(date('M Y', strtotime($b['budget_month'])))?></td>
                <td class="actions">
                    <a class="icon-link" href="budgets.php?month=<?=ae($month)?>&edit=<?=(int)$b['budget_id']?>" title="Edit">&#9998;</a>
                    <form action="../../controllers/adminBudgetController.php" method="post" data-confirm="Delete this manager budget?">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="budget_id" value="<?=(int)$b['budget_id']?>">
                        <button class="icon-link danger" title="Delete">&#128465;</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<!-- Create Budget Modal -->
<div class="modal-backdrop" id="budgetCreate" hidden>
    <div class="form-modal">
        <button class="modal-x" data-close-modal>&times;</button>
        <h2>Assign Budget</h2>
        <form action="../../controllers/adminBudgetController.php" method="post">
            <input type="hidden" name="action" value="save">
            <label>
                <span>Manager:</span>
                <select name="assigned_to" required>
                    <option value="">Select a manager</option>
                    <?php foreach($managers as $m): ?>
                        <option value="<?=(int)$m['user_id']?>"><?=ae($m['user_name'])?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Budget amount:</span>
                <input type="number" min="0.01" step="0.01" name="budget_amount" required placeholder="Enter an amount">
            </label>
            <label>
                <span>Budget Month:</span>
                <input type="month" name="budget_month" required value="<?=ae($month)?>">
            </label>
            <div class="modal-actions">
                <button type="button" class="btn btn-gray" data-close-modal>Cancel</button>
                <button class="btn btn-gold">Assign</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Budget Modal -->
<?php if($edit): ?>
<div class="modal-backdrop show">
    <div class="form-modal">
        <a class="modal-x" href="budgets.php?month=<?=ae($month)?>">&times;</a>
        <h2>Edit Budget</h2>
        <form action="../../controllers/adminBudgetController.php" method="post">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="budget_id" value="<?=(int)$edit['budget_id']?>">
            <label>
                <span>Manager:</span>
                <select name="assigned_to" required>
                    <?php foreach($managers as $m): ?>
                        <option value="<?=(int)$m['user_id']?>" <?=$m['user_id'] == $edit['assigned_to'] ? 'selected' : ''?>>
                            <?=ae($m['user_name'])?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Budget amount:</span>
                <input type="number" min="0.01" step="0.01" name="budget_amount" value="<?=ae($edit['budget_amount'])?>" required>
            </label>
            <label>
                <span>Budget Month:</span>
                <input type="month" name="budget_month" value="<?=ae(date('Y-m', strtotime($edit['budget_month'])))?>" required>
            </label>
            <div class="modal-actions">
                <a class="btn btn-gray" href="budgets.php?month=<?=ae($month)?>">Cancel</a>
                <button class="btn btn-gold">Save Changes</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php adminPageEnd(); ?>