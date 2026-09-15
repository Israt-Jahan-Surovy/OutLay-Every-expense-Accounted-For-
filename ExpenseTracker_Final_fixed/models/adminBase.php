<?php


require_once __DIR__ . '/dbConnect.php';

function adminBindParams($stmt, $types, $params)
{
    if ($types === '' || !$params) {
        return;
    }

    $refs = [];
    foreach ($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }

    mysqli_stmt_bind_param($stmt, $types, ...$refs);
}


function adminDbOrFail()
{
    return dbConnection();
}
?>
