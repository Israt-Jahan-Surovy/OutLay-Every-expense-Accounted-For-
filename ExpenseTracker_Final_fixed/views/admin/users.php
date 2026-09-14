a<?php
require_once __DIR__ . '/_admin_ui.php'; require_once __DIR__ . '/../../models/adminUserModel.php';
$q=trim($_GET['q']??'');$role=$_GET['role']??'All';$status=$_GET['status']??'All';$users=adminUsers($q,$role,$status);$editId=(int)($_GET['edit']??0);$editUser=$editId?adminUserById($editId):null;
$backToList='users.php?q='.urlencode($q).'&role='.urlencode($role).'&status='.urlencode($status);
adminPageStart('Users','users');
?>
<section class="page-head"><div><h1>Manage Users</h1></div><button class="btn btn-gold" data-open-modal="userCreate">+ Add Users</button></section>
<form class="filterbar" method="get"><input name="q" value="<?=ae($q)?>" placeholder="Search by name or email..."><select name="role"><option>All</option><option <?=$role==='Manager'?'selected':''?>>Manager</option><option <?=$role==='Employee'?'selected':''?>>Employee</option></select><select name="status"><option>All</option><option <?=$status==='Active'?'selected':''?>>Active</option><option <?=$status==='Inactive'?'selected':''?>>Inactive</option></select><button class="btn btn-outline">Search</button></form>
<section class="table-wrap"><table><thead><tr><th>Name</th><th>E-mail</th><th>Role</th><th>Status</th><th class="actions-col">Action</th></tr></thead><tbody>
<?php if(!$users):?><tr><td colspan="5" class="empty">No users found.</td></tr><?php endif; ?>
<?php foreach($users as $u):?><tr><td><?=ae($u['user_name'])?></td><td><?=ae($u['user_email'])?></td><td><?=ae($u['user_role'])?></td><td>
<form action="../../controllers/adminUserController.php" method="post" data-confirm="Change this user's status?">
<input type="hidden" name="csrf_token" value="<?=ae(adminCsrfToken())?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="user_id" value="<?=(int)$u['user_id']?>">
<button class="status-toggle" title="Click to toggle Active/Inactive"><strong class="status-text status-<?=strtolower($u['user_status'])?>"><?=ae($u['user_status'])?></strong></button>
</form>
</td><td class="actions">
<a class="icon-link" href="<?=ae($backToList)?>&edit=<?=(int)$u['user_id']?>" title="Edit">&#9998;</a>
<form action="../../controllers/adminUserController.php" method="post" data-confirm="Delete this user? If the account has records, deletion will be blocked."><input type="hidden" name="csrf_token" value="<?=ae(adminCsrfToken())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="user_id" value="<?=(int)$u['user_id']?>"><button class="icon-link danger" title="Delete">&#128465;</button></form>
</td></tr><?php endforeach;?>
</tbody></table></section>

<div class="modal-backdrop" id="userCreate" hidden><div class="form-modal"><button class="modal-x" data-close-modal>&times;</button><h2>Add User</h2><form action="../../controllers/adminUserController.php" method="post"><input type="hidden" name="csrf_token" value="<?=ae(adminCsrfToken())?>"><input type="hidden" name="action" value="create">
<label><span>Username:</span><input name="user_name" required></label>
<label><span>Email:</span><input type="email" name="user_email" required></label>
<label><span>Password (min. 6 characters)</span><input type="password" name="user_password" minlength="6" required></label>
<label><span>Role:</span><select name="user_role" required><option value="">Select Role</option><option>Manager</option><option>Employee</option></select></label>
<div class="modal-actions"><button type="button" class="btn btn-gray" data-close-modal>Cancel</button><button class="btn btn-gold">Create</button></div>
</form></div></div>

<?php if($editUser):?><div class="modal-backdrop show"><div class="form-modal"><a class="modal-x" href="<?=ae($backToList)?>">&times;</a><h2>Edit User</h2><form action="../../controllers/adminUserController.php" method="post"><input type="hidden" name="csrf_token" value="<?=ae(adminCsrfToken())?>"><input type="hidden" name="action" value="update"><input type="hidden" name="user_id" value="<?=(int)$editUser['user_id']?>"><input type="hidden" name="user_status" value="<?=ae($editUser['user_status'])?>">
<label><span>Username:</span><input name="user_name" required value="<?=ae($editUser['user_name'])?>"></label>
<label><span>Email:</span><input type="email" name="user_email" required value="<?=ae($editUser['user_email'])?>"></label>
<label><span>Role:</span><select name="user_role"><option <?=$editUser['user_role']==='Manager'?'selected':''?>>Manager</option><option <?=$editUser['user_role']==='Employee'?'selected':''?>>Employee</option></select></label>
<div class="modal-actions"><a class="btn btn-gray" href="<?=ae($backToList)?>">Cancel</a><button class="btn btn-gold">Edit</button></div>
</form></div></div><?php endif;?>
<?php adminPageEnd(); ?>
