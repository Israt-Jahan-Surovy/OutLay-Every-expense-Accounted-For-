<?php
// Controller: handles create/update/delete actions from views/admin/categories.php
// Plain procedural PHP - no try-catch, no OOP.

require_once __DIR__ . '/adminCommon.php';
require_once __DIR__ . '/../models/adminCategoryModel.php';

requireAdminRole();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    adminRedirect('categories.php');
}

adminVerifyCsrf();

$action = $_POST['action'] ?? '';
$id = (int)($_POST['category_id'] ?? 0);
$name = trim($_POST['category_name'] ?? '');

if ($action === 'create') {

    if ($name === '') {
        adminFlash('error', 'Category name is required.');
    } else {
        list($ok, $msg) = adminCreateCategory($name);
        adminFlash($ok ? 'success' : 'error', $msg);
    }

} elseif ($action === 'update') {

    if ($id < 1 || $name === '') {
        adminFlash('error', 'Invalid category.');
    } else {
        list($ok, $msg) = adminUpdateCategory($id, $name);
        adminFlash($ok ? 'success' : 'error', $msg);
    }

} elseif ($action === 'delete') {

    list($ok, $msg) = adminDeleteCategory($id);
    adminFlash($ok ? 'success' : 'error', $msg);

}

adminRedirect('categories.php');
?>
