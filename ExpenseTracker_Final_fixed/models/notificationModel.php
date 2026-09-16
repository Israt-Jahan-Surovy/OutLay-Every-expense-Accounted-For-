<?php
require_once "dbConnect.php";

// Create a new notification for a specific user (or Admin if $user_id is null)
function addNotification($user_id, $message, $redirect_url = '') {
    $conn = dbConnection();
    if (!$conn) return false;

    $sql = "INSERT INTO notificationtable (user_id, message, redirect_url, is_read, created_at) VALUES (?, ?, ?, 0, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iss", $user_id, $message, $redirect_url);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }
    
    mysqli_close($conn);
    return false;
}

// Get ALL notifications for a standard user (maintains history)
function getAllNotifications($user_id) {
    $conn = dbConnection();
    if (!$conn) return [];

    $sql = "SELECT notification_id, message, redirect_url, is_read, created_at 
            FROM notificationtable 
            WHERE user_id = ? 
            ORDER BY created_at DESC";
            
    $stmt = mysqli_prepare($conn, $sql);
    $notifications = [];

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
    return $notifications;
}

// Get ALL notifications for Admin (maintains history for Admin)
function getAllAdminNotifications() {
    $conn = dbConnection();
    $rows = [];
    if (!$conn) return $rows;

    $sql = "SELECT n.notification_id, n.message, n.redirect_url, n.is_read, n.created_at 
            FROM notificationtable n
            LEFT JOIN usertable u ON n.user_id = u.user_id
            WHERE n.user_id IS NULL OR u.user_role = 'Admin'
            ORDER BY n.created_at DESC";
            
    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }

    mysqli_close($conn);
    return $rows;
}

// Delete a specific notification manually (works for both users and Admin)
function deleteNotification($notification_id, $user_id = null) {
    $conn = dbConnection();
    if (!$conn) return false;

    if ($user_id !== null) {
        $sql = "DELETE FROM notificationtable WHERE notification_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $notification_id, $user_id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            return $result;
        }
    } else {
        // Direct deletion for admin-targeted notifications
        $sql = "DELETE FROM notificationtable WHERE notification_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $notification_id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            return $result;
        }
    }

    mysqli_close($conn);
    return false;
}

// Mark standard user notifications as read
function markNotificationsAsRead($user_id) {
    $conn = dbConnection();
    if (!$conn) return false;

    $sql = "UPDATE notificationtable SET is_read = 1 WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }

    mysqli_close($conn);
    return false;
}

// Mark all Admin notifications as read
function markAdminNotificationsAsRead() {
    $conn = dbConnection();
    if (!$conn) return false;

    $sql = "UPDATE notificationtable n
            LEFT JOIN usertable u ON n.user_id = u.user_id
            SET n.is_read = 1 
            WHERE n.user_id IS NULL OR u.user_role = 'Admin'";

    $res = mysqli_query($conn, $sql);
    mysqli_close($conn);
    return $res;
}
?>