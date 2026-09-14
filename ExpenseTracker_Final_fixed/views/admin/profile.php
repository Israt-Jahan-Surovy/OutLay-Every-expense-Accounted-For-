<?php
require_once __DIR__ . '/_admin_ui.php';
require_once __DIR__ . '/../../controllers/profilecontroller.php';
$is_editing = isset($_GET['edit']) && $_GET['edit'] === 'true';
adminPageStart('My Profile','profile');
?>
<section class="page-head"><div><h1>My Profile</h1></div></section>
<section class="detail-card" style="max-width:600px">
<div style="display:flex;align-items:center;gap:16px;margin-bottom:24px">
<span class="avatar" style="width:56px;height:56px;font-size:22px">&#128247;</span>
<div><strong style="font-size:20px"><?=ae($user['user_name'])?></strong><br><span class="status-text" style="border:1px solid var(--line);padding:2px 10px;border-radius:3px;font-size:12px"><?=ae(ucwords($user['user_role']))?></span></div>
</div>
<form action="../../controllers/updateProfileController.php" method="POST">
<div class="detail-row"><span class="label">Name:</span><span class="value"><?php if($is_editing):?><input type="text" name="user_name" value="<?=ae($user['user_name'])?>" required><?php else: ?><?=ae($user['user_name'])?><?php endif; ?></span></div>
<div class="detail-row"><span class="label">Email:</span><span class="value"><?php if($is_editing):?><input type="email" name="user_email" value="<?=ae($user['user_email'])?>" required><?php else: ?><?=ae($user['user_email'])?><?php endif; ?></span></div>
<div class="detail-row"><span class="label">Username:</span><span class="value"><?php if($is_editing):?><input type="text" name="username" value="<?=ae($user['username'] ?? $user['user_name'])?>" required><?php else: ?><?=ae($user['username'] ?? $user['user_name'])?><?php endif; ?></span></div>
<div class="detail-row"><span class="label">Role:</span><span class="value"><?=ae(ucwords($user['user_role']))?></span></div>
<div class="detail-row"><span class="label">Member Since:</span><span class="value"><?=!empty($user['created_at']) && $user['created_at']!=='N/A' ? ae(date('d M, Y', strtotime($user['created_at']))) : 'Not Given'?></span></div>
<div class="modal-actions" style="justify-content:flex-start;margin-top:24px">
<?php if($is_editing): ?>
<button type="submit" class="btn btn-gold">Save Changes</button><a href="profile.php" class="btn btn-gray">Cancel</a>
<?php else: ?>
<a href="profile.php?edit=true" class="btn btn-gold">Edit Profile</a><a href="../profile/changePassword.php" class="btn btn-gray">Change Password</a>
<?php endif; ?>
</div>
</form>
</section>
<?php adminPageEnd(); ?>
