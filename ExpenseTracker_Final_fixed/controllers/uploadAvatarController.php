<?php
// Handles the profile picture upload from views/profile/profile.php.
// (This file was missing before, so the "click avatar to upload" feature
// on the profile page never actually worked - it just 404'd.)
// Plain procedural PHP - no try-catch, no OOP.

session_start();
require_once __DIR__ . '/../models/usersModel.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {

    $file = $_FILES['profile_image'];

    // Only accept real uploads with no upload error.
    if ($file['error'] === UPLOAD_ERR_OK) {

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed, true)) {

            $uploadDir = __DIR__ . '/../uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Unique file name so users don't overwrite each other's pictures.
            $newName = 'user_' . $user_id . '_' . time() . '.' . $ext;

            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
                updateUserProfileImage($user_id, $newName);
            }
        }
    }
}

header("Location: ../views/profile/profile.php");
exit();
?>
