<?php

if (session_status() == PHP_SESSION_NONE)
{
    session_start();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense & Budget Management System</title>
   
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="../css/profile.css">
    <link rel="stylesheet" href="../css/report.css">
</head>
<body>

<header class="top-header">
    <div>
        <h1>Expense & Budget Management System</h1>
    </div>

    <?php if (isset($_SESSION["user_id"])) { ?>
        <div class="header-user">
            <?php echo htmlspecialchars($_SESSION["user_name"]); ?>
        </div>
    <?php } ?>
</header>