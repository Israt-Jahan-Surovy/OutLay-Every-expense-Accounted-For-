<?php
// Shared helpers used by every admin model file (users, categories,
// budgets, expenses, approvals, dashboard).
// Kept as plain procedural mysqli - no classes, no exceptions.

require_once __DIR__ . '/dbConnect.php';

// Binds a dynamic list of params onto a prepared statement.
// $types is the usual mysqli type string ("ssi", "i", etc.)
// $params is a plain array of values in the same order as $types.
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

// Simple wrapper around dbConnection().
// Returns the mysqli connection, or null if the connection failed.
// dbConnection() already prints an error message on failure,
// same as the rest of the project (no exceptions/try-catch used here).
function adminDbOrFail()
{
    return dbConnection();
}
?>
