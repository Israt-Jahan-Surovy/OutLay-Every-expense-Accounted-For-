<?php
require_once "dbConnect.php";

function login($user_email, $user_password)
{
    $conn = dbConnection();

    if ($conn)
    {
        $sql = "SELECT * FROM usertable WHERE user_email=?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            mysqli_close($conn);
            return null;
        }

        mysqli_stmt_bind_param($stmt, "s", $user_email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result))
        {
            // Reject inactive users immediately
            if ($user["user_status"] === "Active" && $user_password === $user["user_password"])
            {
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                return $user;
            }
        }
        
        // Clean up connection for all failed attempts (wrong password, inactive, or email not found)
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }

    return null;
}

function getAllUsers()
{
    $conn = dbConnection();

    if ($conn)
    {
        $sql = "SELECT * FROM usertable ORDER BY user_id DESC";
        $result = mysqli_query($conn, $sql);
        return $result;
    }

    return null;
}

function getUserById($user_id)
{
    $conn = dbConnection();

    if ($conn)
    {
        $sql = "SELECT * FROM usertable WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            mysqli_close($conn);
            return null;
        }

        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $user = null;
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        return $user;
    }

    return null;
}

function getUserByEmail($user_email)
{
    $conn = dbConnection();

    if ($conn)
    {
        $sql = "SELECT * FROM usertable WHERE user_email=?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) return null;

        mysqli_stmt_bind_param($stmt, "s", $user_email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $user = null;
        if ($result && mysqli_num_rows($result) > 0)
            $user = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        return $user;
    }

    return null;
}

function addUser($user_name, $user_email, $user_password, $user_role)
{
    $conn = dbConnection();

    if ($conn)
    {
        if (getUserByEmail($user_email))
            return false;

        $sql = "INSERT INTO usertable
                (user_name, user_email, user_password, user_role, user_status)
                VALUES (?, ?, ?, ?, 'Active')";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) return false;

        // Save plain text password directly
        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $user_name,
            $user_email,
            $user_password,
            $user_role
        );

        $status = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $status;
    }

    return false;
}

function updateUser($user_id, $user_name, $user_email, $user_role)
{
    $conn = dbConnection();

    if ($conn)
    {
        $sql = "UPDATE usertable
                SET user_name=?, user_email=?, user_role=?
                WHERE user_id=?";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) return false;

        mysqli_stmt_bind_param(
            $stmt,
            "sssi",
            $user_name,
            $user_email,
            $user_role,
            $user_id
        );

        $status = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $status;
    }

    return false;
}

function userHasActivity($user_id)
{
    $conn = dbConnection();

    if ($conn)
    {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM expensetable WHERE user_id=?) AS expense_count,
                    (SELECT COUNT(*) FROM approvaltable WHERE user_id=?) AS approval_count,
                    (SELECT COUNT(*) FROM budgettable
                     WHERE assigned_by=? OR assigned_to=?) AS budget_count";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) return true;

        mysqli_stmt_bind_param(
            $stmt,
            "iiii",
            $user_id,
            $user_id,
            $user_id,
            $user_id
        );

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        return (
            $row["expense_count"] > 0 ||
            $row["approval_count"] > 0 ||
            $row["budget_count"] > 0
        );
    }

    return true;
}

function deleteUser($user_id)
{
    $conn = dbConnection();

    if ($conn)
    {
        if (userHasActivity($user_id))
            return false;

        $sql = "DELETE FROM usertable WHERE user_id=?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) return false;

        mysqli_stmt_bind_param($stmt, "i", $user_id);

        $status = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $status;
    }

    return false;
}

function updateUserStatus($user_id, $user_status)
{
    $conn = dbConnection();

    if ($conn)
    {
        $sql = "UPDATE usertable SET user_status=? WHERE user_id=?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) return false;

        mysqli_stmt_bind_param($stmt, "si", $user_status, $user_id);

        $status = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $status;
    }

    return false;
}

function changePassword($user_id, $new_password)
{
    $conn = dbConnection();

    if ($conn)
    {
        $sql = "UPDATE usertable SET user_password=? WHERE user_id=?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) return false;

        mysqli_stmt_bind_param($stmt, "si", $new_password, $user_id);

        $status = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $status;
    }

    return false;
}

function updateProfile($user_id, $user_name, $user_email)
{
    $conn = dbConnection();

    if ($conn)
    {
        $sql = "UPDATE usertable
                SET user_name=?, user_email=?
                WHERE user_id=?";

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) return false;

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $user_name,
            $user_email,
            $user_id
        );

        $status = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $status;
    }

    return false;
}

function updateUserProfileImage($user_id, $image_name)
{
    $conn = dbConnection();

    if ($conn)
    {
        $sql = "UPDATE usertable SET profile_image=? WHERE user_id=?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) return false;

        mysqli_stmt_bind_param($stmt, "si", $image_name, $user_id);

        $status = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $status;
    }

    return false;
}
function resetPasswordByEmail($email, $new_password)
{
    $conn = dbConnection();
    if ($conn) {
        // Verify email exists
        $checkSql = "SELECT user_id FROM usertable WHERE user_email = ?";
        $checkStmt = mysqli_prepare($conn, $checkSql);
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        $result = mysqli_stmt_get_result($checkStmt);
        
        if (mysqli_num_rows($result) === 0) {
            mysqli_stmt_close($checkStmt);
            mysqli_close($conn);
            return false; // Email not found
        }
        mysqli_stmt_close($checkStmt);

        // Update password
        $sql = "UPDATE usertable SET user_password = ? WHERE user_email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $new_password, $email);
        $status = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        return $status;
    }
    return false;
}

?>