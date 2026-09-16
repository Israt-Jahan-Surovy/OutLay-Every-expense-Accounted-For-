<?php
require_once '../../controllers/profilecontroller.php';
$is_editing = isset($_GET['edit']) && $_GET['edit'] === 'true';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Expense Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/profile.css">
    <script src="../js/expense.js"></script>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <h1 class="page-title">My Profile</h1>

        <div class="profile-container">
            <!-- Profile Avatar Header -->
            <div class="profile-header">
                <form id="avatarForm" action="../../controllers/uploadAvatarController.php" method="POST" enctype="multipart/form-data">
                    <label for="profile_pic_input" class="avatar-box" title="Click to upload profile picture">
                        <?php if (!empty($user['profile_image']) && file_exists("../../uploads/avatars/" . $user['profile_image'])): ?>
                            <img src="../../uploads/avatars/<?php echo htmlspecialchars($user['user_avatar']); ?>" class="avatar-img">
                        <?php else: ?>
                            <span class="camera-icon">📷</span>
                        <?php endif; ?>
                    </label>
                    <input type="file" id="profile_pic_input" name="profile_image" accept="image/*" onchange="document.getElementById('avatarForm').submit();" style="display: none;">
                </form>

                <div class="profile-name-badge">
                    <h2><?php echo htmlspecialchars($user['user_name']); ?></h2>
                    <span class="role-badge"><?php echo htmlspecialchars(ucwords($user['user_role'])); ?></span>
                </div>
            </div>

            <!-- Profile Details Form -->
            <form action="../../controllers/updateProfileController.php" method="POST">
                <div class="profile-details">
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <?php if ($is_editing): ?>
                            <input type="text" name="user_name" class="inline-input" value="<?php echo htmlspecialchars($user['user_name']); ?>" required>
                        <?php else: ?>
                            <span class="detail-value"><?php echo htmlspecialchars($user['user_name']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <?php if ($is_editing): ?>
                            <input type="email" name="user_email" class="inline-input" value="<?php echo htmlspecialchars($user['user_email']); ?>" required>
                        <?php else: ?>
                            <span class="detail-value"><?php echo htmlspecialchars($user['user_email']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Username:</span>
                        <?php if ($is_editing): ?>
                            <input type="text" name="username" class="inline-input" value="<?php echo htmlspecialchars($user['username'] ?? $user['user_name']); ?>" required>
                        <?php else: ?>
                            <span class="detail-value"><?php echo htmlspecialchars($user['username'] ?? $user['user_name']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Role:</span>
                        <span class="detail-value"><?php echo htmlspecialchars(ucwords($user['user_role'])); ?></span>
                    </div>

                    
                </div>

                <!-- Dynamic Action Buttons -->
                <div class="profile-actions">
                    <?php if ($is_editing): ?>
                        <button type="submit" class="btn-gold">Save Changes</button>
                        <a href="profile.php" class="btn-grey">Cancel</a>
                    <?php else: ?>
                        <a href="profile.php?edit=true" class="btn-gold">Edit Profile</a>
                        <a href="changePassword.php" class="btn-grey">Change Password</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </main>

</body>
</html>