<?php

require_once __DIR__ . '/adminBase.php';

// Returns active managers for the budget assignment dropdown.
function adminManagers()
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return [];
    }

    $result = mysqli_query(
        $conn,
        "SELECT user_id, user_name, user_email FROM usertable
         WHERE user_role = 'Manager' AND user_status = 'Active' ORDER BY user_name"
    );

    $rows = [];
    while ($result && $row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_close($conn);
    return $rows;
}

// Returns active users filtered by role for general dropdowns.
function adminUsersByRole($role = 'Manager')
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return [];
    }

    $stmt = mysqli_prepare(
        $conn,
        "SELECT user_id, user_name, user_email FROM usertable
         WHERE user_role = ? AND user_status = 'Active' ORDER BY user_name"
    );
    if (!$stmt) {
        mysqli_close($conn);
        return [];
    }

    mysqli_stmt_bind_param($stmt, 's', $role);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $rows = [];
    while ($result && $row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return $rows;
}

// Fetches a single budget row by ID for editing.
function adminBudgetById($id)
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return null;
    }

    $stmt = mysqli_prepare(
        $conn,
        "SELECT b.*, u.user_name FROM budgettable b
         JOIN usertable u ON u.user_id = b.assigned_to
         WHERE b.budget_id = ?"
    );
    if (!$stmt) {
        mysqli_close($conn);
        return null;
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return $row;
}

// Checks for duplicate budgets for the same user in the same month.
function adminFindUserBudget($userId, $monthDate, $excludeId = 0)
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return 0;
    }

    $stmt = mysqli_prepare(
        $conn,
        "SELECT budget_id FROM budgettable
         WHERE assigned_to = ? AND budget_month = ? AND budget_id <> ? LIMIT 1"
    );
    if (!$stmt) {
        mysqli_close($conn);
        return 0;
    }

    mysqli_stmt_bind_param($stmt, 'isi', $userId, $monthDate, $excludeId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return $row ? (int)$row['budget_id'] : 0;
}

// Assigns or updates a budget for a Manager or Employee.
function adminSaveBudget($adminId, $targetUserId, $budgetType, $month, $amount, $budgetId = 0, $parentBudgetId = null)
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return [false, 'Could not connect to the database.'];
    }

    $monthDate = $month . '-01';

    $existingId = adminFindUserBudget($targetUserId, $monthDate, $budgetId);
    if ($existingId) {
        mysqli_close($conn);
        return [false, 'This user already has a budget for that month. Edit the existing entry instead.'];
    }

    if ($budgetId > 0) {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE budgettable 
             SET budget_amount = ?, budget_month = ?, assigned_to = ?
             WHERE budget_id = ?"
        );
        if (!$stmt) {
            mysqli_close($conn);
            return [false, 'Could not prepare update statement.'];
        }
        mysqli_stmt_bind_param($stmt, 'dsii', $amount, $monthDate, $targetUserId, $budgetId);
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO budgettable (budget_type, budget_month, budget_amount, assigned_by, assigned_to, parent_budget_id)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) {
            mysqli_close($conn);
            return [false, 'Could not prepare insert statement.'];
        }
        $parentVal = ($parentBudgetId && $parentBudgetId > 0) ? (int)$parentBudgetId : null;
        mysqli_stmt_bind_param($stmt, 'ssdiii', $budgetType, $monthDate, $amount, $adminId, $targetUserId, $parentVal);
    }

    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return [$ok, $ok ? 'Budget saved successfully.' : 'Unable to save budget.'];
}

// Wrapper to preserve compatibility with existing manager-specific calls.
function adminSaveManagerBudget($adminId, $managerId, $month, $amount, $budgetId = 0)
{
    return adminSaveBudget($adminId, $managerId, 'Manager', $month, $amount, $budgetId, null);
}

// Deletes a budget record by ID.
function adminDeleteBudget($id)
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return false;
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM budgettable WHERE budget_id = ?");
    if (!$stmt) {
        mysqli_close($conn);
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return $ok;
}

// Returns Manager budgets filtered by month ("YYYY-MM").
function adminManagerBudgets($month = '')
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return [];
    }

    $where = "b.budget_type='Manager'";
    $params = [];
    $types = '';

    if ($month !== '') {
        $where .= " AND DATE_FORMAT(b.budget_month,'%Y-%m')=?";
        $params[] = $month;
        $types .= 's';
    }

    $sql = "SELECT b.*, u.user_name, u.user_email,
                   COALESCE((SELECT SUM(eb.budget_amount) FROM budgettable eb
                             WHERE eb.parent_budget_id=b.budget_id AND eb.budget_type='Employee'), 0) AS distributed
            FROM budgettable b
            JOIN usertable u ON u.user_id=b.assigned_to
            WHERE $where
            ORDER BY b.budget_month DESC, b.budget_id DESC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        mysqli_close($conn);
        return [];
    }

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
?>