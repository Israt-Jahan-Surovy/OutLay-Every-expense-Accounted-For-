<?php
require_once "dbConnect.php";
//add expense
function addExpense($expense_title,$expense_amount, $expense_date,$expense_description,$user_id,$category_id) {
    $conn = dbConnection();

    if ($conn) {
        $sql = "INSERT INTO expensetable
                (expense_title, expense_amount, expense_date,
                 expense_status, expense_description, user_id, category_id)
                VALUES (?, ?, ?, 'Pending', ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) return false;

        mysqli_stmt_bind_param(
            $stmt,
            "sdssii",
            $expense_title,
            $expense_amount,
            $expense_date,
            $expense_description,
            $user_id,
            $category_id
        );

        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }

    return false;
}
//
function getExpenseById($expense_id)
{
    $conn = dbConnection();

    if ($conn) {
        $sql = "SELECT 
                    e.*, 
                    c.category_name, 
                    u_owner.user_name AS owner_name,
                    a.approval_date, 
                    a.approval_status,
                    a.rejected_reason,
                    u_mgr.user_name AS manager_name
                FROM expensetable e
                INNER JOIN categorytable c ON e.category_id = c.category_id
                INNER JOIN usertable u_owner ON e.user_id = u_owner.user_id
                LEFT JOIN approvaltable a ON e.expense_id = a.expense_id
                LEFT JOIN usertable u_mgr ON a.user_id = u_mgr.user_id
                WHERE e.expense_id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) return null;

        mysqli_stmt_bind_param($stmt, "i", $expense_id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $data = (mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $data;
    }

    return null;
}

function getExpenseDetailsById($expense_id, $user_id = null) {
    $conn = dbConnection();

    if ($conn) {
        $query = "SELECT 
                    e.expense_id,
                    e.expense_title,
                    e.expense_amount,
                    e.expense_date,
                    e.expense_description,
                    e.expense_status,
                    c.category_name,
                    u_owner.user_name AS employee_name,
                    a.approval_date,
                    a.approval_status,
                    a.rejected_reason,
                    u_app.user_name AS approver_name,
                    u_app.user_role AS approver_role
                  FROM expensetable e
                  LEFT JOIN categorytable c ON e.category_id = c.category_id
                  INNER JOIN usertable u_owner ON e.user_id = u_owner.user_id
                  LEFT JOIN approvaltable a ON e.expense_id = a.expense_id
                  LEFT JOIN usertable u_app ON a.user_id = u_app.user_id
                  WHERE e.expense_id = ?";

        // Restrict to owner only if $user_id is passed explicitly
        if ($user_id !== null) {
            $query .= " AND e.user_id = ?";
        }

        $stmt = mysqli_prepare($conn, $query);

        if (!$stmt) return null;

        if ($user_id !== null) {
            mysqli_stmt_bind_param($stmt, "ii", $expense_id, $user_id);
        } else {
            mysqli_stmt_bind_param($stmt, "i", $expense_id);
        }

        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $data;
    }

    return null;
}
function getExpensesByUser($user_id, $status = 'All')
{
    $conn = dbConnection();

    if ($conn) {
        if (in_array($status, ['Pending', 'Approved', 'Rejected'])) {
            $sql = "SELECT e.*, c.category_name
                    FROM expensetable e
                    INNER JOIN categorytable c ON e.category_id = c.category_id
                    WHERE e.user_id = ? AND e.expense_status = ?
                    ORDER BY e.expense_date DESC, e.expense_id DESC";

            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) return null;
            mysqli_stmt_bind_param($stmt, "is", $user_id, $status);
        } else {
            $sql = "SELECT e.*, c.category_name
                    FROM expensetable e
                    INNER JOIN categorytable c ON e.category_id = c.category_id
                    WHERE e.user_id = ?
                    ORDER BY e.expense_date DESC, e.expense_id DESC";

            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) return null;
            mysqli_stmt_bind_param($stmt, "i", $user_id);
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }

    return null;
}

function getAllExpenses()
{
    $conn = dbConnection();

    if ($conn) {
        $sql = "SELECT expensetable.*, usertable.user_name,
                       usertable.user_role, categorytable.category_name
                FROM expensetable
                INNER JOIN usertable ON expensetable.user_id=usertable.user_id
                INNER JOIN categorytable ON expensetable.category_id=categorytable.category_id
                ORDER BY expensetable.expense_date DESC, expensetable.expense_id DESC";

        $result = mysqli_query($conn, $sql);
        mysqli_close($conn);
        return $result;
    }

    return null;
}

function getEmployeeExpenses()
{
    $conn = dbConnection();

    if ($conn) {
        $sql = "SELECT expensetable.*, usertable.user_name, categorytable.category_name
                FROM expensetable
                INNER JOIN usertable ON expensetable.user_id=usertable.user_id
                INNER JOIN categorytable ON expensetable.category_id=categorytable.category_id
                WHERE usertable.user_role='Employee'
                ORDER BY expensetable.expense_date DESC, expensetable.expense_id DESC";

        $result = mysqli_query($conn, $sql);
        mysqli_close($conn);
        return $result;
    }

    return null;
}

function updateExpense(
    $expense_id,
    $expense_title,
    $expense_amount,
    $expense_date,
    $expense_description,
    $category_id,
    $user_id
) {
    $conn = dbConnection();

    if ($conn) {
        $sql = "UPDATE expensetable
                SET expense_title=?, expense_amount=?, expense_date=?,
                    expense_description=?, category_id=?
                WHERE expense_id=? AND user_id=? AND expense_status='Pending'";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) return false;

        mysqli_stmt_bind_param(
            $stmt,
            "sdssiii",
            $expense_title,
            $expense_amount,
            $expense_date,
            $expense_description,
            $category_id,
            $expense_id,
            $user_id
        );

        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }

    return false;
}

function deleteExpense($expense_id, $user_id)
{
    $conn = dbConnection();

    if ($conn) {
        $sql = "DELETE FROM expensetable
                WHERE expense_id=? AND user_id=? AND expense_status='Pending'";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) return false;

        mysqli_stmt_bind_param($stmt, "ii", $expense_id, $user_id);

        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }

    return false;
}

function updateExpenseStatus($expense_id, $expense_status)
{
    $conn = dbConnection();

    if ($conn) {
        $sql = "UPDATE expensetable SET expense_status=? WHERE expense_id=?";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) return false;

        mysqli_stmt_bind_param($stmt, "si", $expense_status, $expense_id);

        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $result;
    }

    return false;
}

function getApprovedTotalByUser($user_id, $month)
{
    $conn = dbConnection();

    if ($conn) {
        $sql = "SELECT COALESCE(SUM(expense_amount),0) AS total
                FROM expensetable
                WHERE user_id=? AND expense_status='Approved'
                  AND DATE_FORMAT(expense_date,'%Y-%m')=?";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) return 0;

        mysqli_stmt_bind_param($stmt, "is", $user_id, $month);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $row["total"];
    }

    return 0;
}

function getApprovedTotalByCategory($category_id, $month)
{
    $conn = dbConnection();

    if ($conn) {
        $sql = "SELECT COALESCE(SUM(expense_amount),0) AS total
                FROM expensetable
                WHERE category_id=? AND expense_status='Approved'
                  AND DATE_FORMAT(expense_date,'%Y-%m')=?";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) return 0;

        mysqli_stmt_bind_param($stmt, "is", $category_id, $month);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $row["total"];
    }

    return 0;
}

function getSystemExpensesByCategory($from_date = null, $to_date = null)
{
    $conn = dbConnection();

    if ($conn) {
        $sql = "SELECT c.category_name, SUM(e.expense_amount) AS total_amount 
                FROM expensetable e
                INNER JOIN categorytable c ON e.category_id = c.category_id";

        if (!empty($from_date) && !empty($to_date)) {
            $sql .= " WHERE e.expense_date BETWEEN ? AND ?";
        }

        $sql .= " GROUP BY c.category_id, c.category_name";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            mysqli_close($conn);
            return [];
        }

        if (!empty($from_date) && !empty($to_date)) {
            mysqli_stmt_bind_param($stmt, "ss", $from_date, $to_date);
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $reportData = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $reportData[] = $row;
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        return $reportData;
    }

    return [];
}

function updateEmployeeExpense(int $expense_id, int $user_id, string $title, int $category_id, float $amount): bool {
    $conn = dbConnection();
    if (!$conn) return false;

    
    $sql = "UPDATE expensetable 
            SET expense_title = ?, category_id = ?, expense_amount = ? 
            WHERE expense_id = ? AND user_id = ? AND expense_status = 'Pending'";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        mysqli_close($conn);
        return false;
    }

    mysqli_stmt_bind_param($stmt, "sidii", $title, $category_id, $amount, $expense_id, $user_id);
    $result = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $result;
}


function getExpenseByIdAndUser(int $expense_id, int $user_id): ?array {
    $conn = dbConnection();
    if (!$conn) return null;

    $sql = "SELECT expense_id, expense_title, category_id, expense_amount, expense_status 
            FROM expensetable 
            WHERE expense_id = ? AND user_id = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $expense_id, $user_id);
    mysqli_stmt_execute($stmt);
    
    $res = mysqli_stmt_get_result($stmt);
    $expense = mysqli_fetch_assoc($res) ?: null;

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $expense;
}
// Delete a pending expense for an employee.

function deleteEmployeeExpense(int $expense_id, int $user_id): bool {
    $conn = dbConnection();
    if (!$conn) return false;

    // Enforce user_id matching and status = 'Pending' 
    $sql = "DELETE FROM expensetable 
            WHERE expense_id = ? AND user_id = ? AND expense_status = 'Pending'";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        mysqli_close($conn);
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ii", $expense_id, $user_id);
    $result = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $result;
}
?>