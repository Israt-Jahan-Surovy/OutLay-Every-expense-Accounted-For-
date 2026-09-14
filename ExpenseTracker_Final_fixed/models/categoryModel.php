<?php
require_once "dbConnect.php";

function getAllCategories()
{
    $conn = dbConnection();

    if ($conn)
    {
        $sql = "SELECT * FROM categorytable ORDER BY category_name ASC";
        return mysqli_query($conn, $sql);
    }

    return null;
}

function getCategoryById($category_id)
{
    $conn = dbConnection();

    if ($conn)
    {
        $sql = "SELECT * FROM categorytable WHERE category_id=?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) return null;

        mysqli_stmt_bind_param($stmt, "i", $category_id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0)
            return mysqli_fetch_assoc($result);
    }

    return null;
}

?>