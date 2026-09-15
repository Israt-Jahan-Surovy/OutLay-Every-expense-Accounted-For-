<?php
require_once "dbConnect.php";
require_once "budgetModel.php";

function canAllocateEmployeeBudget($manager_id, $employee_id, $proposed_amount, $month, $current_budget_id = 0)
{
    $conn = dbConnection();
   if (!$conn) {
    echo "Database connection failed: " . mysqli_connect_error();
    exit();
}

  
    $manager_total = getTeamBudgetTotal($manager_id, $month);
    if ($manager_total <= 0) {
        mysqli_close($conn);
        return [false, "No active team budget assigned by Admin for this month."];
    }

    $sql = "SELECT COALESCE(SUM(budget_amount), 0) AS total_allocated
            FROM budgettable
            WHERE assigned_by = ? 
              AND budget_type = 'Employee'
              AND DATE_FORMAT(budget_month, '%Y-%m') = ?
              AND budget_id != ?";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        mysqli_close($conn);
        return [false, "Database error preparing query."];
    }

    mysqli_stmt_bind_param($stmt, "isi", $manager_id, $month, $current_budget_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $existing_allocated = (float)$res['total_allocated'];
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    $projected_total = $existing_allocated + (float)$proposed_amount;
    if ($projected_total > $manager_total) {
        $remaining_pool = max(0, $manager_total - $existing_allocated);
        return [
            false, 
            "Allocation limit exceeded. Remaining pool balance: $" . number_format($remaining_pool, 2)
        ];
    }

    return [true, "Allocation valid."];
}
?>