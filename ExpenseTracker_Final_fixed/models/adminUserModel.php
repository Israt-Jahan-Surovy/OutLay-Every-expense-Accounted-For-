<?php
// Data-access layer for the admin "Users" screen.
// Provides: adminUsers(), adminUserById(), adminCreateUser(),
// adminUpdateUser(), adminToggleUser(), adminDeleteUser().
// Plain procedural mysqli - no exceptions, no OOP.

require_once __DIR__ . '/adminBase.php';

// Returns the list of users matching the search box / role filter / status filter.
function adminUsers($q, $role, $status)
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return [];
    }

    $sql = "SELECT user_id, user_name, user_email, user_role, user_status
            FROM usertable WHERE 1=1";
    $types = '';
    $params = [];

    if ($q !== '') {
        $sql .= " AND (user_name LIKE ? OR user_email LIKE ?)";
        $like = '%' . $q . '%';
        $types .= 'ss';
        $params[] = $like;
        $params[] = $like;
    }

    if ($role !== '' && $role !== 'All') {
        $sql .= " AND user_role = ?";
        $types .= 's';
        $params[] = $role;
    }

    if ($status !== '' && $status !== 'All') {
        $sql .= " AND user_status = ?";
        $types .= 's';
        $params[] = $status;
    }

    $sql .= " ORDER BY user_id DESC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        mysqli_close($conn);
        return [];
    }

    adminBindParams($stmt, $types, $params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return $users;
}

// Returns a single user's data by id, or null if not found.
function adminUserById($id)
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return null;
    }

    $sql = "SELECT user_id, user_name, user_email, user_role, user_status
            FROM usertable WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        mysqli_close($conn);
        return null;
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result) ?: null;

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return $user;
}

// Checks if an email is already used by another user (used for create/update validation).
function adminEmailExists($email, $excludeId = 0)
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return true; // fail closed: treat as "exists" so we don't create a bad row
    }

    $sql = "SELECT user_id FROM usertable WHERE user_email = ? AND user_id != ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        mysqli_close($conn);
        return true;
    }

    mysqli_stmt_bind_param($stmt, 'si', $email, $excludeId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $exists = mysqli_num_rows($result) > 0;

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    return $exists;
}

// Creates a new Manager/Employee account.
function adminCreateUser($name, $email, $password, $role)
{
    if (adminEmailExists($email)) {
        return [false, 'A user with that email already exists.'];
    }

    $conn = adminDbOrFail();
    if (!$conn) {
        return [false, 'Could not connect to the database.'];
    }

    $sql = "INSERT INTO usertable (user_name, user_email, user_password, user_role, user_status)
            VALUES (?, ?, ?, ?, 'Active')";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        mysqli_close($conn);
        return [false, 'Could not create user.'];
    }

    mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $password, $role);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $ok
        ? [true, 'User created successfully.']
        : [false, 'Could not create user.'];
}

// Updates a user's name, email, role, and status.
function adminUpdateUser($id, $name, $email, $role, $status)
{
    if (adminEmailExists($email, $id)) {
        return [false, 'Another user already uses that email.'];
    }

    $conn = adminDbOrFail();
    if (!$conn) {
        return [false, 'Could not connect to the database.'];
    }

    $sql = "UPDATE usertable
            SET user_name = ?, user_email = ?, user_role = ?, user_status = ?
            WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        mysqli_close($conn);
        return [false, 'Could not update user.'];
    }

    mysqli_stmt_bind_param($stmt, 'ssssi', $name, $email, $role, $status, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $ok
        ? [true, 'User updated successfully.']
        : [false, 'Could not update user.'];
}

// Flips a user's status between Active and Inactive.
function adminToggleUser($id)
{
    $user = adminUserById($id);
    if (!$user) {
        return false;
    }

    $newStatus = $user['user_status'] === 'Active' ? 'Inactive' : 'Active';

    $conn = adminDbOrFail();
    if (!$conn) {
        return false;
    }

    $sql = "UPDATE usertable SET user_status = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        mysqli_close($conn);
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'si', $newStatus, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $ok;
}

// Checks whether a user has related expenses/approvals/budgets,
// so we can block deleting them (used by adminDeleteUser()).
function adminUserHasActivity($id)
{
    $conn = adminDbOrFail();
    if (!$conn) {
        return true; // fail closed: block deletion if we can't check
    }

    $sql = "SELECT
                (SELECT COUNT(*) FROM expensetable WHERE user_id = ?) AS expense_count,
                (SELECT COUNT(*) FROM approvaltable WHERE user_id = ?) AS approval_count,
                (SELECT COUNT(*) FROM budgettable WHERE assigned_by = ? OR assigned_to = ?) AS budget_count";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        mysqli_close($conn);
        return true;
    }

    mysqli_stmt_bind_param($stmt, 'iiii', $id, $id, $id, $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return (
        ($row['expense_count'] ?? 0) > 0 ||
        ($row['approval_count'] ?? 0) > 0 ||
        ($row['budget_count'] ?? 0) > 0
    );
}

// Deletes a user, unless they have related records.
function adminDeleteUser($id)
{
    if (adminUserHasActivity($id)) {
        return [false, 'This user has related records and cannot be deleted.'];
    }

    $conn = adminDbOrFail();
    if (!$conn) {
        return [false, 'Could not connect to the database.'];
    }

    $sql = "DELETE FROM usertable WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        mysqli_close($conn);
        return [false, 'Could not delete user.'];
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    return $ok
        ? [true, 'User deleted successfully.']
        : [false, 'Could not delete user.'];
}
?>