<?php
require_once "dbConnect.php";
require_once "budgetModel.php";

function recordApproval($expense_id, $approver_id, $status, $reason = null)
{
    $conn = dbConnection();
    if (!$conn) return false;

    // Ensure valid integer for user_id
    $approver_id = (int)$approver_id;
    $expense_id = (int)$expense_id;

    $sql = "INSERT INTO approvaltable (expense_id, user_id, approval_status, rejected_reason, approval_date)
            VALUES (?, ?, ?, ?, CURDATE())
            ON DUPLICATE KEY UPDATE 
                user_id = VALUES(user_id),
                approval_status = VALUES(approval_status),
                rejected_reason = VALUES(rejected_reason),
                approval_date = CURDATE()";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        mysqli_close($conn);
        return false;
    }

    mysqli_stmt_bind_param($stmt, "iiss", $expense_id, $approver_id, $status, $reason);
    $result = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return $result;
}

function getPendingTeamExpenses($manager_id)
{
    $team_ids = getTeamMemberIds($manager_id);
    if (empty($team_ids)) return null;

    $conn = dbConnection();
    if (!$conn) return null;

    $placeholders = implode(",", array_fill(0, count($team_ids), "?"));
    $sql = "SELECT e.*, u.user_name, c.category_name,
                   COALESCE(a.approval_status, 'Pending') AS expense_status
            FROM expensetable e
            INNER JOIN usertable u ON e.user_id = u.user_id
            INNER JOIN categorytable c ON e.category_id = c.category_id
            LEFT JOIN approvaltable a ON e.expense_id = a.expense_id
            WHERE e.user_id IN ($placeholders) AND (a.approval_status IS NULL OR a.approval_status = 'Pending')
            ORDER BY e.expense_date DESC, e.expense_id DESC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) { mysqli_close($conn); return null; }

    mysqli_stmt_bind_param($stmt, str_repeat("i", count($team_ids)), ...$team_ids);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return $result;
}

function getTeamExpensesWithFilter($manager_id, $from_date = null, $to_date = null)
{
    $conn = dbConnection();
    if (!$conn) return null;

    // Directly target all users with 'Employee' role to prevent missing records
    $sql = "SELECT e.*, 
                   u.user_name, 
                   c.category_name,
                   COALESCE(a.approval_status, 'Pending') AS expense_status
            FROM expensetable e
            INNER JOIN usertable u ON e.user_id = u.user_id
            INNER JOIN categorytable c ON e.category_id = c.category_id
            LEFT JOIN approvaltable a ON e.expense_id = a.expense_id
            WHERE u.user_role = 'Employee'";

    $types = "";
    $params = [];

    if (!empty(trim($from_date ?? '')) && !empty(trim($to_date ?? ''))) {
        $sql .= " AND e.expense_date BETWEEN ? AND ?";
        $types .= "ss";
        $params[] = $from_date;
        $params[] = $to_date;
    }

    $sql .= " ORDER BY e.expense_date DESC, e.expense_id DESC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) { mysqli_close($conn); return null; }

    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return $result;
}
function getApprovalHistoryForManager($manager_id)
{
    $conn = dbConnection();

    if ($conn) {
        $sql = "SELECT a.approval_date, a.approval_status, a.rejected_reason,
                       e.expense_title, e.expense_amount, u.user_name
                FROM approvaltable a
                INNER JOIN expensetable e ON a.expense_id = e.expense_id
                INNER JOIN usertable u ON e.user_id = u.user_id
                WHERE a.user_id=?
                ORDER BY a.approval_date DESC";

        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) { mysqli_close($conn); return null; }

        mysqli_stmt_bind_param($stmt, "i", $manager_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }

    return null;
}

/**
 * Fetches full detail for a single expense including categories, 
 * applicant info, and approval history for the view page.
 */
function getExpenseById($expense_id)
{
    $conn = dbConnection();
    if (!$conn) return null;

    $expense_id = (int)$expense_id;

    $sql = "SELECT e.*, 
                   c.category_name, 
                   u.user_name, 
                   a.rejected_reason AS rejection_reason,
                   a.approval_date,
                   COALESCE(a.approval_status, 'Pending') AS expense_status,
                   approver.user_name AS approver_name
            FROM expensetable e
            LEFT JOIN categorytable c ON e.category_id = c.category_id
            LEFT JOIN usertable u ON e.user_id = u.user_id
            LEFT JOIN approvaltable a ON e.expense_id = a.expense_id
            LEFT JOIN usertable approver ON a.user_id = approver.user_id
            WHERE e.expense_id = ?
            LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        mysqli_close($conn);
        return null;
    }

    mysqli_stmt_bind_param($stmt, "i", $expense_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $data;
}
?>