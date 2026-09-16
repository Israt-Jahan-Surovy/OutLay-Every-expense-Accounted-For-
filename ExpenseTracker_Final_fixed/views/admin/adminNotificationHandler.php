<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Include the notification model using the correct path relative to views/admin/
require_once __DIR__ . '/../../models/notificationModel.php';

header('Content-Type: application/json');

// Disable HTML error displaying to avoid breaking JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL);

$action = $_GET['action'] ?? '';

// 1. Fetch ALL admin notifications (history)
if ($action === 'fetch') {
    $notifications = getAllAdminNotifications();
    echo json_encode(is_array($notifications) ? $notifications : []);
    exit();
} 

// 2. Mark all admin notifications as read
if ($action === 'read') {
    markAdminNotificationsAsRead();
    echo json_encode(["status" => "success"]);
    exit();
}

// 3. Delete a specific admin notification
if ($action === 'delete') {
    $notif_id = (int)($_GET['id'] ?? 0);
    $deleted = deleteNotification($notif_id, null); // Pass null for admin-targeted notifications
    echo json_encode(["status" => $deleted ? "success" : "error"]);
    exit();
}

echo json_encode([]);
exit();
?>