<?php

$serverName = "localhost";
$userName = "root";
$password = "";
$db = "expense_budget_db";

function dbConnection()
{
    global $serverName, $userName, $password, $db;

    $conn = mysqli_connect($serverName, $userName, $password, $db);

    if ($conn)
    {
        mysqli_set_charset($conn, "utf8mb4");
        return $conn;
    }

    echo "connection failed: " . mysqli_connect_error();
    return null;
}

?>