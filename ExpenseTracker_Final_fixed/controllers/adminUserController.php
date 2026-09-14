<?php
// Controller: handles create/update/toggle/delete actions from views/admin/users.php
// Plain procedural PHP - no try-catch, no OOP.

require_once __DIR__ . '/adminCommon.php';
require_once __DIR__ . '/../models/adminUserModel.php';

requireAdminRole();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    adminRedirect('users.php');
}

$action = $_POST['action'] ?? '';
$id = (int)($_POST['user_id'] ?? 0);

if ($action === 'create') {

    $name = trim($_POST['user_name'] ?? '');
    $email = trim($_POST['user_email'] ?? '');
    $plainPassword = $_POST['user_password'] ?? '';
    $role = $_POST['user_role'] ?? '';

    if (
        $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($plainPassword) < 6 ||
        !in_array($role, ['Manager', 'Employee'], true)
    ) {
        adminFlash(
            'error',
            'Please complete all required user fields correctly (password must be at least 6 characters).'
        );
    } else {
        list($ok, $msg) = adminCreateUser($name, $email, $plainPassword, $role);
        adminFlash($ok ? 'success' : 'error', $msg);
    }

} elseif ($action === 'update') {

    $name = trim($_POST['user_name'] ?? '');
    $email = trim($_POST['user_email'] ?? '');
    $role = $_POST['user_role'] ?? '';
    $status = $_POST['user_status'] ?? '';

    if (
        $id < 1 ||
        $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !in_array($role, ['Manager', 'Employee'], true) ||
        !in_array($status, ['Active', 'Inactive'], true)
    ) {
        adminFlash('error', 'Invalid user information.');
    } else {
        list($ok, $msg) = adminUpdateUser($id, $name, $email, $role, $status);
        adminFlash($ok ? 'success' : 'error', $msg);
    }

} elseif ($action === 'toggle') {

    if ($id < 1) {
        adminFlash('error', 'Invalid user specified for status update.');
    } else {
        $ok = adminToggleUser($id);
        adminFlash(
            $ok ? 'success' : 'error',
            $ok ? 'User status updated successfully.' : 'Could not update user status.'
        );
    }

} elseif ($action === 'delete') {

    if ($id < 1) {
        adminFlash('error', 'Invalid user specified for deletion.');
    } else {
        list($ok, $msg) = adminDeleteUser($id);
        adminFlash($ok ? 'success' : 'error', $msg);
    }

}

adminRedirect('users.php');
?>