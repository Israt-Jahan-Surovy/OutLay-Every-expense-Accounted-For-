<?php
require_once "dbConnect.php";



// Retrieve personal budget row assigned to a specific user for a given month
function getUserBudget($user_id, $month = null)
{
    $conn = dbConnection();
    if (!$conn) return null;

    if (!$month) {
        $month = date('Y-m');
    }

    $formatted_month = date('Y-m', strtotime($month));

    $sql = "SELECT COALESCE(SUM(budget_amount), 0) AS budget_amount
            FROM budgettable
            WHERE assigned_to = ?
              AND LOWER(budget_type) = 'employee'
              AND DATE_FORMAT(budget_month, '%Y-%m') = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        return null;
    }

    mysqli_stmt_bind_param($stmt, "is", $user_id, $formatted_month);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $row;
}


// Calculate remaining balance for an Employee
function getUserRemainingBudget($user_id, $month)
{
    $budget_row = getUserBudget($user_id, $month);

    if (!$budget_row) {
        return 0.00;
    }

    $allocated = (float)$budget_row['budget_amount'];

    // Retrieve spending total from expenseModel
    require_once "expenseModel.php";

    $spent = (float)getApprovedTotalByUser($user_id, $month);

    return max(0.00, $allocated - $spent);
}



// Assign or create a budget for a Manager (Admin action)
function assignManagerBudget($admin_id, $manager_id, $amount, $month)
{
    $conn = dbConnection();

    if (!$conn) {
        return false;
    }

    $formatted_month = date('Y-m-01', strtotime($month));
    $check_month = date('Y-m', strtotime($month));

    // Check if Manager already has a budget for this month
    $check_sql = "SELECT budget_id
                  FROM budgettable
                  WHERE assigned_to = ?
                    AND LOWER(budget_type) = 'manager'
                    AND DATE_FORMAT(budget_month, '%Y-%m') = ?";

    $stmt = mysqli_prepare($conn, $check_sql);

    if (!$stmt) {
        mysqli_close($conn);
        return false;
    }

    mysqli_stmt_bind_param($stmt, "is", $manager_id, $check_month);
    mysqli_stmt_execute($stmt);

    $res = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($res)) {

        $budget_id = $row['budget_id'];

        mysqli_stmt_close($stmt);

        // Update existing Manager budget
        $update_sql = "UPDATE budgettable
                       SET budget_amount = ?,
                           assigned_by = ?
                       WHERE budget_id = ?";

        $u_stmt = mysqli_prepare($conn, $update_sql);

        if (!$u_stmt) {
            mysqli_close($conn);
            return false;
        }

        mysqli_stmt_bind_param(
            $u_stmt,
            "dii",
            $amount,
            $admin_id,
            $budget_id
        );

        $result = mysqli_stmt_execute($u_stmt);

        mysqli_stmt_close($u_stmt);

    } else {

        mysqli_stmt_close($stmt);

        // Insert new Manager budget
        $insert_sql = "INSERT INTO budgettable
                       (
                           budget_type,
                           budget_month,
                           budget_amount,
                           assigned_by,
                           assigned_to
                       )
                       VALUES
                       (
                           'Manager',
                           ?,
                           ?,
                           ?,
                           ?
                       )";

        $i_stmt = mysqli_prepare($conn, $insert_sql);

        if (!$i_stmt) {
            mysqli_close($conn);
            return false;
        }

        mysqli_stmt_bind_param(
            $i_stmt,
            "sdii",
            $formatted_month,
            $amount,
            $admin_id,
            $manager_id
        );

        $result = mysqli_stmt_execute($i_stmt);

        mysqli_stmt_close($i_stmt);
    }

    mysqli_close($conn);

    return $result;
}


// Modify an existing Manager budget (Admin action)
function updateManagerBudget($budget_id, $amount)
{
    $conn = dbConnection();

    if (!$conn) {
        return false;
    }

    $sql = "UPDATE budgettable
            SET budget_amount = ?
            WHERE budget_id = ?
              AND LOWER(budget_type) = 'manager'";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        return false;
    }

    mysqli_stmt_bind_param($stmt, "di", $amount, $budget_id);

    $result = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $result;
}


// Get all Manager budgets with names
function getAllManagerBudgets($month)
{
    $conn = dbConnection();
    $rows = [];

    if (!$conn) {
        return $rows;
    }

    $formatted_month = date('Y-m', strtotime($month));

    $sql = "SELECT
                b.budget_id,
                b.budget_amount,
                b.budget_month,
                u_to.user_name AS manager_name,
                u_by.user_name AS assigned_by_name
            FROM budgettable b
            INNER JOIN usertable u_to
                ON u_to.user_id = b.assigned_to
            INNER JOIN usertable u_by
                ON u_by.user_id = b.assigned_by
            WHERE LOWER(b.budget_type) = 'manager'
              AND DATE_FORMAT(b.budget_month, '%Y-%m') = ?
            ORDER BY u_to.user_name ASC";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        return $rows;
    }

    mysqli_stmt_bind_param($stmt, "s", $formatted_month);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $rows;
}





// Get team members of a Manager
function getTeamMembers($manager_id)
{
    $conn = dbConnection();
    $employees = [];

    if (!$conn) {
        return $employees;
    }

    // Retrieves employees under this manager OR employees with no manager assigned
    $sql = "SELECT user_id, user_name
            FROM usertable
            WHERE (manager_id = ? OR manager_id IS NULL OR manager_id = 0)
              AND LOWER(user_role) = 'employee'
              AND LOWER(user_status) = 'active'
            ORDER BY user_name ASC";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        return $employees;
    }

    mysqli_stmt_bind_param($stmt, "i", $manager_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $employees;
}


// Helper function: Get IDs of all employees reporting to a Manager
function getTeamMemberIds($manager_id)
{
    $conn = dbConnection();

    if (!$conn) {
        return [];
    }

    $sql = "SELECT user_id
            FROM usertable
            WHERE manager_id = ?
              AND LOWER(user_status) = 'active'";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        return [];
    }

    mysqli_stmt_bind_param($stmt, "i", $manager_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $team_ids = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $team_ids[] = (int)$row['user_id'];
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $team_ids;
}



function getManagerAdminBudget($manager_id, $month)
{
    $conn = dbConnection();

    if (!$conn) {
        return 0.00;
    }

    $formatted_month = date('Y-m', strtotime($month));

    $sql = "SELECT COALESCE(SUM(budget_amount), 0) AS total
            FROM budgettable
            WHERE assigned_to = ?
              AND LOWER(budget_type) = 'manager'
              AND DATE_FORMAT(budget_month, '%Y-%m') = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        return 0.00;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "is",
        $manager_id,
        $formatted_month
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return (float)$row["total"];
}



function getManagerPersonalBudget($manager_id, $month)
{
    $admin_budget = getManagerAdminBudget(
        $manager_id,
        $month
    );

    return $admin_budget * 0.30;
}


function getManagerTeamBudget($manager_id, $month)
{
    $admin_budget = getManagerAdminBudget(
        $manager_id,
        $month
    );

    return $admin_budget * 0.70;
}


function getTeamBudgetTotal($manager_id, $month)
{
    return getManagerAdminBudget(
        $manager_id,
        $month
    );
}


function getEmployeeBudgetTotal($manager_id, $month)
{
    $conn = dbConnection();

    if (!$conn) {
        return 0.00;
    }

    $formatted_month = date('Y-m', strtotime($month));

    $sql = "SELECT COALESCE(SUM(budget_amount), 0) AS total
            FROM budgettable
            WHERE assigned_by = ?
              AND LOWER(budget_type) = 'employee'
              AND DATE_FORMAT(budget_month, '%Y-%m') = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        return 0.00;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "is",
        $manager_id,
        $formatted_month
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return (float)$row["total"];
}



function getTeamSpentTotal($manager_id, $month)
{
    $team_ids = getTeamMemberIds($manager_id);

    if (empty($team_ids)) {
        return 0.00;
    }

    $conn = dbConnection();

    if (!$conn) {
        return 0.00;
    }

    $formatted_month = date('Y-m', strtotime($month));

    $placeholders = implode(
        ",",
        array_fill(0, count($team_ids), "?")
    );

    $sql = "SELECT COALESCE(SUM(expense_amount), 0) AS total
            FROM expensetable
            WHERE LOWER(expense_status) = 'approved'
              AND DATE_FORMAT(expense_date, '%Y-%m') = ?
              AND user_id IN ($placeholders)";

    $types = "s" . str_repeat(
        "i",
        count($team_ids)
    );

    $params = array_merge(
        [$formatted_month],
        $team_ids
    );

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        return 0.00;
    }

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return (float)$row["total"];
}


function getTeamSpendingTable($manager_id, $month)
{
    $conn = dbConnection();
    $rows = [];

    if (!$conn) {
        return $rows;
    }

    $formatted_month = date('Y-m', strtotime($month));

    $sql = "SELECT
                b.budget_id,
                u.user_id,
                u.user_name,
                b.budget_amount,
                COALESCE(
                    (
                        SELECT SUM(ex.expense_amount)
                        FROM expensetable ex
                        WHERE ex.user_id = u.user_id
                          AND LOWER(ex.expense_status) = 'approved'
                          AND DATE_FORMAT(ex.expense_date, '%Y-%m') = ?
                    ),
                    0
                ) AS spent
            FROM budgettable b
            INNER JOIN usertable u
                ON u.user_id = b.assigned_to
            WHERE b.assigned_by = ?
              AND LOWER(b.budget_type) = 'employee'
              AND DATE_FORMAT(b.budget_month, '%Y-%m') = ?
            ORDER BY u.user_name ASC";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        return $rows;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sis",
        $formatted_month,
        $manager_id,
        $formatted_month
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {

        $row["spent"] = (float)$row["spent"];

        $row["budget_amount"] = (float)$row["budget_amount"];

        $row["remaining"] = max(
            0.00,
            $row["budget_amount"] - $row["spent"]
        );

        $rows[] = $row;
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $rows;
}


function getUnassignedTeamMembers($manager_id, $month)
{
    $conn = dbConnection();
    $rows = [];

    if (!$conn) {
        return $rows;
    }

    $formatted_month = date('Y-m', strtotime($month));

    $sql = "SELECT user_id, user_name
            FROM usertable
            WHERE LOWER(user_role) = 'employee'
              AND LOWER(user_status) = 'active'
              AND manager_id = ?
              AND user_id NOT IN
              (
                  SELECT assigned_to
                  FROM budgettable
                  WHERE assigned_by = ?
                    AND LOWER(budget_type) = 'employee'
                    AND DATE_FORMAT(budget_month, '%Y-%m') = ?
              )
            ORDER BY user_name ASC";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        mysqli_close($conn);
        return $rows;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "iis",
        $manager_id,
        $manager_id,
        $formatted_month
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $rows;
}




function assignEmployeeBudget(
    $manager_id,
    $employee_id,
    $amount,
    $month,
    $parent_budget_id = null
) {
    $conn = dbConnection();

    if (!$conn) {
        return [
            'success' => false,
            'message' => 'Database connection failed.'
        ];
    }

    $formatted_month = date(
        'Y-m-01',
        strtotime($month)
    );

    $check_month = date(
        'Y-m',
        strtotime($month)
    );

    $amount = (float)$amount;


    $parent_sql = "SELECT
                       budget_id,
                       budget_amount
                   FROM budgettable
                   WHERE assigned_to = ?
                     AND LOWER(budget_type) = 'manager'
                     AND DATE_FORMAT(budget_month, '%Y-%m') = ?
                   LIMIT 1";

    $parent_stmt = mysqli_prepare(
        $conn,
        $parent_sql
    );

    if (!$parent_stmt) {
        mysqli_close($conn);

        return [
            'success' => false,
            'message' => 'Failed to find Manager budget.'
        ];
    }

    mysqli_stmt_bind_param(
        $parent_stmt,
        "is",
        $manager_id,
        $check_month
    );

    mysqli_stmt_execute($parent_stmt);

    $parent_result = mysqli_stmt_get_result(
        $parent_stmt
    );

    $parent = mysqli_fetch_assoc(
        $parent_result
    );

    mysqli_stmt_close($parent_stmt);


  
    if (!$parent) {

        mysqli_close($conn);

        return [
            'success' => false,
            'message' => 'No budget has been allocated to you by the Admin for this month.'
        ];
    }


    $parent_budget_id = $parent['budget_id'];

    $admin_budget = (float)$parent['budget_amount'];

    $team_budget = $admin_budget * 0.70;


    $existing_sql = "SELECT budget_id, budget_amount
                     FROM budgettable
                     WHERE assigned_to = ?
                       AND LOWER(budget_type) = 'employee'
                       AND DATE_FORMAT(budget_month, '%Y-%m') = ?
                     LIMIT 1";

    $existing_stmt = mysqli_prepare(
        $conn,
        $existing_sql
    );

    if (!$existing_stmt) {
        mysqli_close($conn);

        return [
            'success' => false,
            'message' => 'Failed to check existing employee budget.'
        ];
    }

    mysqli_stmt_bind_param(
        $existing_stmt,
        "is",
        $employee_id,
        $check_month
    );

    mysqli_stmt_execute($existing_stmt);

    $existing_result = mysqli_stmt_get_result(
        $existing_stmt
    );

    $existing = mysqli_fetch_assoc(
        $existing_result
    );

    mysqli_stmt_close($existing_stmt);


    $total_sql = "SELECT COALESCE(SUM(budget_amount), 0) AS total
                  FROM budgettable
                  WHERE assigned_by = ?
                    AND LOWER(budget_type) = 'employee'
                    AND DATE_FORMAT(budget_month, '%Y-%m') = ?";

    $total_stmt = mysqli_prepare(
        $conn,
        $total_sql
    );

    if (!$total_stmt) {
        mysqli_close($conn);

        return [
            'success' => false,
            'message' => 'Failed to calculate team allocation.'
        ];
    }

    mysqli_stmt_bind_param(
        $total_stmt,
        "is",
        $manager_id,
        $check_month
    );

    mysqli_stmt_execute($total_stmt);

    $total_result = mysqli_stmt_get_result(
        $total_stmt
    );

    $total_row = mysqli_fetch_assoc(
        $total_result
    );

    mysqli_stmt_close($total_stmt);

    $current_employee_allocations =
        (float)$total_row['total'];


   
    if ($existing) {

        $current_employee_allocations -=
            (float)$existing['budget_amount'];

    }


   
    $new_total =
        $current_employee_allocations + $amount;


    if ($new_total > $team_budget) {

        $available =
            $team_budget - $current_employee_allocations;

        if ($available < 0) {
            $available = 0;
        }

        mysqli_close($conn);

        return [
            'success' => false,
            'message' =>
                'Employee budget cannot exceed the 70% team budget. Available team budget: ' .
                number_format($available, 2)
        ];
    }


    if ($existing) {

        $budget_id = $existing['budget_id'];

        // Preserve parent_budget_id from found manager parent budget if not passed explicitly
        $target_parent_id = $parent_budget_id ? $parent_budget_id : $parent['budget_id'];

        $update_sql = "UPDATE budgettable
                       SET budget_amount = ?,
                           assigned_by = ?,
                           parent_budget_id = ?
                       WHERE budget_id = ?";

        $u_stmt = mysqli_prepare(
            $conn,
            $update_sql
        );

        if (!$u_stmt) {
            mysqli_close($conn);

            return [
                'success' => false,
                'message' => 'Failed to prepare budget update.'
            ];
        }

        mysqli_stmt_bind_param(
            $u_stmt,
            "diii",
            $amount,
            $manager_id,
            $target_parent_id,
            $budget_id
        );

        $result = mysqli_stmt_execute(
            $u_stmt
        );

        mysqli_stmt_close($u_stmt);
        mysqli_close($conn);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Employee budget updated successfully.'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to update employee budget.'
        ];
    }


   
    $insert_sql = "INSERT INTO budgettable
                   (
                       budget_type,
                       budget_month,
                       budget_amount,
                       assigned_by,
                       assigned_to,
                       parent_budget_id
                   )
                   VALUES
                   (
                       'Employee',
                       ?,
                       ?,
                       ?,
                       ?,
                       ?
                   )";

    $i_stmt = mysqli_prepare(
        $conn,
        $insert_sql
    );

    if (!$i_stmt) {
        mysqli_close($conn);

        return [
            'success' => false,
            'message' => 'Failed to prepare budget insertion.'
        ];
    }

    mysqli_stmt_bind_param(
        $i_stmt,
        "sdiii",
        $formatted_month,
        $amount,
        $manager_id,
        $employee_id,
        $parent_budget_id
    );

    $result = mysqli_stmt_execute(
        $i_stmt
    );

    mysqli_stmt_close($i_stmt);

    mysqli_close($conn);

    if ($result) {

        return [
            'success' => true,
            'message' => 'Employee budget assigned successfully.'
        ];
    }

    return [
        'success' => false,
        'message' => 'Failed to assign employee budget.'
    ];
}



function updateEmployeeBudget(
    $budget_id,
    $manager_id,
    $amount
) {
    $conn = dbConnection();

    if (!$conn) {
        return false;
    }

   
    $find_sql = "SELECT
                     budget_amount,
                     budget_month
                 FROM budgettable
                 WHERE budget_id = ?
                   AND assigned_by = ?
                   AND LOWER(budget_type) = 'employee'";

    $find_stmt = mysqli_prepare(
        $conn,
        $find_sql
    );

    if (!$find_stmt) {
        mysqli_close($conn);
        return false;
    }

    mysqli_stmt_bind_param(
        $find_stmt,
        "ii",
        $budget_id,
        $manager_id
    );

    mysqli_stmt_execute($find_stmt);

    $find_result = mysqli_stmt_get_result(
        $find_stmt
    );

    $existing = mysqli_fetch_assoc(
        $find_result
    );

    mysqli_stmt_close($find_stmt);


    if (!$existing) {
        mysqli_close($conn);
        return false;
    }


    $month = date(
        'Y-m',
        strtotime($existing['budget_month'])
    );


  
    $parent_sql = "SELECT budget_amount
                   FROM budgettable
                   WHERE assigned_to = ?
                     AND LOWER(budget_type) = 'manager'
                     AND DATE_FORMAT(budget_month, '%Y-%m') = ?
                   LIMIT 1";

    $parent_stmt = mysqli_prepare(
        $conn,
        $parent_sql
    );

    if (!$parent_stmt) {
        mysqli_close($conn);
        return false;
    }

    mysqli_stmt_bind_param(
        $parent_stmt,
        "is",
        $manager_id,
        $month
    );

    mysqli_stmt_execute($parent_stmt);

    $parent_result = mysqli_stmt_get_result(
        $parent_stmt
    );

    $parent = mysqli_fetch_assoc(
        $parent_result
    );

    mysqli_stmt_close($parent_stmt);


    if (!$parent) {
        mysqli_close($conn);
        return false;
    }


    $team_budget =
        (float)$parent['budget_amount'] * 0.70;


   
    $total_sql = "SELECT COALESCE(SUM(budget_amount), 0) AS total
                  FROM budgettable
                  WHERE assigned_by = ?
                    AND LOWER(budget_type) = 'employee'
                    AND DATE_FORMAT(budget_month, '%Y-%m') = ?
                    AND budget_id != ?";

    $total_stmt = mysqli_prepare(
        $conn,
        $total_sql
    );

    if (!$total_stmt) {
        mysqli_close($conn);
        return false;
    }

    mysqli_stmt_bind_param(
        $total_stmt,
        "isi",
        $manager_id,
        $month,
        $budget_id
    );

    mysqli_stmt_execute($total_stmt);

    $total_result = mysqli_stmt_get_result(
        $total_stmt
    );

    $total_row = mysqli_fetch_assoc(
        $total_result
    );

    mysqli_stmt_close($total_stmt);

    $other_allocations =
        (float)$total_row['total'];


    if (($other_allocations + $amount) > $team_budget) {

        mysqli_close($conn);

        return false;
    }


    $sql = "UPDATE budgettable
            SET budget_amount = ?
            WHERE budget_id = ?
              AND assigned_by = ?
              AND LOWER(budget_type) = 'employee'";

    $stmt = mysqli_prepare(
        $conn,
        $sql
    );

    if (!$stmt) {
        mysqli_close($conn);
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "dii",
        $amount,
        $budget_id,
        $manager_id
    );

    $result = mysqli_stmt_execute(
        $stmt
    );

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $result;
}



// Count pending expense requests for a Manager's team
function countPendingTeamExpenses($manager_id)
{
    $team_ids = getTeamMemberIds($manager_id);

    if (empty($team_ids)) {
        return 0;
    }

    $conn = dbConnection();

    if (!$conn) {
        return 0;
    }

    $placeholders = implode(
        ",",
        array_fill(
            0,
            count($team_ids),
            "?"
        )
    );

    $sql = "SELECT COUNT(*) AS cnt
            FROM expensetable
            WHERE LOWER(expense_status) = 'pending'
              AND user_id IN ($placeholders)";

    $stmt = mysqli_prepare(
        $conn,
        $sql
    );

    if (!$stmt) {
        mysqli_close($conn);
        return 0;
    }

    mysqli_stmt_bind_param(
        $stmt,
        str_repeat(
            "i",
            count($team_ids)
        ),
        ...$team_ids
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result(
        $stmt
    );

    $row = mysqli_fetch_assoc(
        $result
    );

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return (int)$row["cnt"];
}

?>