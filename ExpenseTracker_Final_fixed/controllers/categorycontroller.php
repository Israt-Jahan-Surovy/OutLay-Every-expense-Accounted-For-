<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../models/categoryModel.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit();
}
if (strtolower($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Access denied. Admin only.');
}

function categoryRedirect($type, $message)
{
    header('Location: ../views/admin/categories.php?' . $type . '=' . urlencode($message));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['category_name'] ?? '');

    if (($action === 'add' || $action === 'update') && ($name === '' || strlen($name) > 100)) {
        categoryRedirect('error', 'Enter a valid category name.');
    }

    if ($action === 'add') {
        addCategory($name)
            ? categoryRedirect('success', 'Category added successfully.')
            : categoryRedirect('error', 'Category already exists or could not be added.');
    }

    if ($action === 'update') {
        $id = (int)($_POST['category_id'] ?? 0);
        updateCategory($id, $name)
            ? categoryRedirect('success', 'Category updated successfully.')
            : categoryRedirect('error', 'Could not update category.');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['category_id'] ?? 0);
        deleteCategory($id)
            ? categoryRedirect('success', 'Category deleted successfully.')
            : categoryRedirect('error', 'This category is already used by an expense and cannot be deleted.');
    }
}

$categories = getAllCategories();
?>
