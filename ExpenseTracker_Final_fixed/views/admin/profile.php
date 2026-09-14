<?php
require_once __DIR__ . '/_admin_ui.php';
require_once __DIR__ . '/../../controllers/profilecontroller.php';
$is_editing = isset($_GET['edit']) && $_GET['edit'] === 'true';
adminPageStart('My Profile','profile');
?>
<section class="page-head"><div><h1>My Profile</h1></div></section>
<section class="detail-card" style="max-width:600px">
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px">
        <!-- Hidden Upload Form -->
        <form id="avatarUploadForm" action="../../controllers/uploadAvatarController.php" method="POST" enctype="multipart/form-data" style="display:none;">
            <input type="file" id="avatarInput" name="profile_image" accept="image/*" onchange="previewAndSubmitAvatar(this);">
        </form>

        <!-- Clickable Photo Avatar -->
        <label for="avatarInput" id="avatarPreviewContainer" class="avatar" style="width:56px;height:56px;font-size:22px;cursor:pointer;display:grid;place-items:center;overflow:hidden;border-radius:50%;" title="Click to upload profile picture">
            <?php 
            $avatarFile = $user['user_avatar'] ?? $_SESSION['user_avatar'] ?? '';
            if (!empty($avatarFile)): 
            ?>
                <img src="../../uploads/avatars/<?=ae($avatarFile)?>"  style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
                &#128247;
            <?php endif; ?>
        </label>

        <div>
            <strong style="font-size:20px"><?=ae($user['user_name'])?></strong><br>
            <span class="status-text" style="border:1px solid var(--line);padding:2px 10px;border-radius:3px;font-size:12px"><?=ae(ucwords($user['user_role']))?></span>
        </div>
    </div>

    <form action="../../controllers/updateProfileController.php" method="POST">
        <div class="detail-row">
            <span class="label">Name:</span>
            <span class="value">
                <?php if($is_editing):?>
                    <input type="text" name="user_name" value="<?=ae($user['user_name'])?>" required>
                <?php else: ?>
                    <?=ae($user['user_name'])?>
                <?php endif; ?>
            </span>
        </div>
        <div class="detail-row">
            <span class="label">Email:</span>
            <span class="value">
                <?php if($is_editing):?>
                    <input type="email" name="user_email" value="<?=ae($user['user_email'])?>" required>
                <?php else: ?>
                    <?=ae($user['user_email'])?>
                <?php endif; ?>
            </span>
        </div>
        <div class="detail-row">
            <span class="label">Username:</span>
            <span class="value">
                <?php if($is_editing):?>
                    <input type="text" name="username" value="<?=ae($user['username'] ?? $user['user_name'])?>" required>
                <?php else: ?>
                    <?=ae($user['username'] ?? $user['user_name'])?>
                <?php endif; ?>
            </span>
        </div>
        <div class="detail-row">
            <span class="label">Role:</span>
            <span class="value"><?=ae(ucwords($user['user_role']))?></span>
        </div>

        <div class="modal-actions" style="justify-content:flex-start;margin-top:24px">
            <?php if($is_editing): ?>
                <button type="submit" class="btn btn-gold">Save Changes</button>
                <a href="profile.php" class="btn btn-gray">Cancel</a>
            <?php else: ?>
                <a href="profile.php?edit=true" class="btn btn-gold">Edit Profile</a>
                <a href="../profile/changePassword.php" class="btn btn-gray">Change Password</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<script>
function previewAndSubmitAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const container = document.getElementById('avatarPreviewContainer');
            container.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
        };
        reader.readAsDataURL(input.files[0]);
        document.getElementById('avatarUploadForm').submit();
    }
}
</script>

<?php adminPageEnd(); ?>