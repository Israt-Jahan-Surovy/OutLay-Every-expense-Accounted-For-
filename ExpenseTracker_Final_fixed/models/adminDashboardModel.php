<?php
require_once __DIR__ . '/adminBase.php';

function adminDashboardStats() {
    $conn = adminDbOrFail();
    $sql = "SELECT
        (SELECT COUNT(*) FROM usertable WHERE user_role IN ('Manager','Employee')) AS users_total,
        (SELECT COUNT(*) FROM usertable WHERE user_role='Manager') AS managers_total,
        (SELECT COUNT(*) FROM usertable WHERE user_role='Employee') AS employees_total,
        (SELECT COUNT(*) FROM categorytable) AS categories_total,
        (SELECT COUNT(*) FROM expensetable) AS expenses_total,
        (SELECT COUNT(*) FROM expensetable WHERE expense_status='Pending') AS pending_total,
        (SELECT COUNT(*) FROM expensetable WHERE expense_status='Approved') AS approved_total,
        (SELECT COUNT(*) FROM expensetable WHERE expense_status='Rejected') AS rejected_total,
        (SELECT COALESCE(SUM(budget_amount),0) FROM budgettable WHERE budget_type='Manager' AND DATE_FORMAT(budget_month,'%Y-%m')=DATE_FORMAT(CURDATE(),'%Y-%m')) AS manager_budget_total,
        (SELECT COALESCE(SUM(expense_amount),0) FROM expensetable WHERE expense_status='Approved') AS approved_amount_total,
        (SELECT COALESCE(SUM(expense_amount),0) FROM expensetable) AS expense_amount_grand_total";
    $result = mysqli_query($conn, $sql);
    $row = $result ? mysqli_fetch_assoc($result) : [];
    mysqli_close($conn);
    return $row ?: [];
}

function adminMonthlyExpenseTrend($months = 6) {
    $conn = adminDbOrFail();
    $months = max(1, min(12, (int)$months));
    $sql = "SELECT DATE_FORMAT(expense_date,'%Y-%m') AS ym,
                   DATE_FORMAT(expense_date,'%b') AS label,
                   COALESCE(SUM(expense_amount),0) AS total
            FROM expensetable
            WHERE expense_date >= DATE_SUB(DATE_FORMAT(CURDATE(),'%Y-%m-01'), INTERVAL " . ($months - 1) . " MONTH)
            GROUP BY ym, label ORDER BY ym ASC";
    $result = mysqli_query($conn, $sql);
    $rows = [];
    if ($result) while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
    mysqli_close($conn);
    return $rows;
}

function adminRecentExpenses($limit = 5) {
    $conn = adminDbOrFail();
    $limit = max(1, min(20, (int)$limit));
    $sql = "SELECT e.expense_id,e.expense_title,e.expense_amount,e.expense_date,e.expense_status,
                   u.user_name,u.user_role,c.category_name
            FROM expensetable e
            JOIN usertable u ON u.user_id=e.user_id
            JOIN categorytable c ON c.category_id=e.category_id
            ORDER BY e.expense_id DESC LIMIT $limit";
    $result = mysqli_query($conn, $sql);
    $rows = [];
    if ($result) while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
    mysqli_close($conn);
    return $rows;
}
?>
