<?php
// Prevent "Session Already Started" warning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/adminCommon.php';
require_once __DIR__ . '/../models/usersModel.php'; 

// Verify user is logged in regardless of role
if (!isset($_SESSION['user_id'])) {
    adminFlash('error', 'User session expired.');
    header("Location: ../views/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userRole = strtolower($_SESSION['user_role'] ?? '');

// Check if form was submitted with a file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    
    $file = $_FILES['profile_image'];

    // Validate upload status
    if ($file['error'] === UPLOAD_ERR_OK) {
        
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // 1. Validate File Extension
        if (in_array($fileExt, $allowedTypes, true)) {
            
            // 2. Validate File Size (Max 2MB)
            if ($file['size'] <= 2 * 1024 * 1024) {

                // Set upload directory path
                $uploadDir = __DIR__ . '/../uploads/avatars/';
                
                // Create directory if it does not exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Generate unique filename to prevent caching issues & overwrites
                $newFileName = 'user_' . $userId . '_' . time() . '.' . $fileExt;
                $targetPath = $uploadDir . $newFileName;

                // Move file from temp folder to target directory
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    
                    // Update database record
                    if (function_exists('updateUserProfileImage')) {
                        updateUserProfileImage($userId, $newFileName);
                    }

                    // Update session variable for immediate UI rendering
                    $_SESSION['user_avatar'] = $newFileName;

                    adminFlash('success', 'Profile picture updated successfully!');
                } else {
                    adminFlash('error', 'Failed to save uploaded image file.');
                }
            } else {
                adminFlash('error', 'File size exceeds the 2MB limit.');
            }
        } else {
            adminFlash('error', 'Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.');
        }
    } else {
        adminFlash('error', 'An error occurred during file upload.');
    }
}

// Redirect dynamically based on role/location
if ($userRole === 'admin') {
    header("Location: ../views/admin/profile.php");
} else {
    header("Location: ../views/profile/profile.php");
}
exit();
?>