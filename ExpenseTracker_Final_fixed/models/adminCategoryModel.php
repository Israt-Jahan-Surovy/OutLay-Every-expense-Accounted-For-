<?php
// Data-access layer for the admin "Categories" screen.
// Plain procedural mysqli - no exceptions, no OOP.

require_once __DIR__ . '/adminBase.php';

// Returns all categories, alphabetically.
function adminCategories()
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return [];
    }

    $result = mysqli_query($conn, 'SELECT * FROM categorytable ORDER BY category_name');

    $rows = [];
    while ($result && $row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_close($conn);
    return $rows;
}

// Returns one category by id, or null if not found.
function adminCategoryById($id)
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return null;
    }

    $stmt = mysqli_prepare($conn, 'SELECT * FROM categorytable WHERE category_id=?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return $row;
}

// Creates a new category.
function adminCreateCategory($name)
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return [false, 'Could not connect to the database.'];
    }

    $stmt = mysqli_prepare($conn, 'INSERT INTO categorytable(category_name) VALUES(?)');
    mysqli_stmt_bind_param($stmt, 's', $name);
    $ok = @mysqli_stmt_execute($stmt);
    $msg = $ok ? 'Category added successfully.' : 'Category already exists or could not be added.';

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return [$ok, $msg];
}

// Updates a category's name.
function adminUpdateCategory($id, $name)
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return [false, 'Could not connect to the database.'];
    }

    $stmt = mysqli_prepare($conn, 'UPDATE categorytable SET category_name=? WHERE category_id=?');
    mysqli_stmt_bind_param($stmt, 'si', $name, $id);
    $ok = @mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return [$ok, $ok ? 'Category updated successfully.' : 'Unable to update category.'];
}

// Deletes a category (fails if an expense still uses it, thanks to the foreign key).
function adminDeleteCategory($id)
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return [false, 'Could not connect to the database.'];
    }

    $stmt = mysqli_prepare($conn, 'DELETE FROM categorytable WHERE category_id=?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = @mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return [$ok, $ok ? 'Category deleted successfully.' : 'Category is used by an expense and cannot be deleted.'];
}
?>
