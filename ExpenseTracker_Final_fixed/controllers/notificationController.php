<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once "../models/dbConnect.php";
require_once "../models/notificationModel.php";

header('Content-Type: application/json');

$user_id   = $_SESSION['user_id'] ?? 0;
$user_role = strtolower($_SESSION['user_role'] ?? '');
$action    = $_GET['action'] ?? '';

if ($user_id <= 0) {
    echo json_encode([]);
    exit();
}

// 1. Fetch full notification history based on role
if ($action === 'fetch') {
    if ($user_role === 'admin') {
        $notifications = getAllAdminNotifications();
    } else {
        $notifications = getAllNotifications($user_id);
    }
    echo json_encode($notifications);
    exit();
}

// 2. Mark all notifications as read when dropdown is opened
if ($action === 'read') {
    if ($user_role === 'admin') {
        markAdminNotificationsAsRead();
    } else {
        markNotificationsAsRead($user_id);
    }
    echo json_encode(['status' => 'success']);
    exit();
}

// 3. Delete a specific notification manually
if ($action === 'delete') {
    $notif_id = (int)($_GET['id'] ?? 0);
    
    // Admins can delete admin-targeted notifications; regular users delete their own
    $target_user = ($user_role === 'admin') ? null : $user_id;
    $deleted = deleteNotification($notif_id, $target_user);
    
    echo json_encode(['status' => $deleted ? 'success' : 'error']);
    exit();
}

echo json_encode([]);
exit();
?>