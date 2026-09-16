<?php


require_once __DIR__ . '/adminBase.php';

// Returns expenses across the whole system, with optional search/filter.
function adminExpenses($q = '', $category = 0, $status = 'All', $from = '', $to = '', $role = 'All')
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return [];
    }

    $where = ['1=1'];
    $params = [];
    $types = '';

    if ($q !== '') {
        $where[] = '(u.user_name LIKE ? OR e.expense_title LIKE ?)';
        $like = "%$q%";
        $params[] = $like;
        $params[] = $like;
        $types .= 'ss';
    }

    if ($category > 0) {
        $where[] = 'e.category_id=?';
        $params[] = $category;
        $types .= 'i';
    }

    if (in_array($status, ['Pending', 'Approved', 'Rejected'], true)) {
        $where[] = 'e.expense_status=?';
        $params[] = $status;
        $types .= 's';
    }

    if ($from !== '') {
        $where[] = 'e.expense_date>=?';
        $params[] = $from;
        $types .= 's';
    }

    if ($to !== '') {
        $where[] = 'e.expense_date<=?';
        $params[] = $to;
        $types .= 's';
    }

    if (in_array($role, ['Manager', 'Employee'], true)) {
        $where[] = 'u.user_role=?';
        $params[] = $role;
        $types .= 's';
    }

    $sql = "SELECT e.*, u.user_name, u.user_role, c.category_name, a.rejected_reason, a.approval_date
            FROM expensetable e
            JOIN usertable u ON u.user_id=e.user_id
            JOIN categorytable c ON c.category_id=e.category_id
            LEFT JOIN approvaltable a ON a.expense_id=e.expense_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY e.expense_date DESC, e.expense_id DESC";

    $stmt = mysqli_prepare($conn, $sql);
    if ($params) {
        adminBindParams($stmt, $types, $params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return $rows;
}

// Returns one expense by id, with the requester's name/role/category/approval info.
function adminExpenseById($id)
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return null;
    }

    $stmt = mysqli_prepare(
        $conn,
        "SELECT e.*, u.user_name, u.user_email, u.user_role, c.category_name,
                a.rejected_reason, a.approval_date, a.approval_status
         FROM expensetable e
         JOIN usertable u ON u.user_id=e.user_id
         JOIN categorytable c ON c.category_id=e.category_id
         LEFT JOIN approvaltable a ON a.expense_id=e.expense_id
         WHERE e.expense_id=?"
    );
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return $row;
}

// Shortcut used by the approval screens: pending Manager expenses only.
function adminPendingManagerExpenses()
{
    return adminExpenses('', 0, 'Pending', '', '', 'Manager');
}
?>
