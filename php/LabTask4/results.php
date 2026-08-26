<?php

session_start();

// Clear results using POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $_SESSION["students"] = [];

}

?>

<!DOCTYPE html>
<html>
<head>
    <title>All Results</title>
</head>

<body>

<h1>All Student Results</h1>

<a href="index.php">Add Student</a> |
<a href="results.php">View Results</a>

<br><br>

<?php

if (!isset($_SESSION["students"]) || count($_SESSION["students"]) == 0) {

    echo "No student results available.";

}
else {

?>

<table border="1" cellpadding="10">

    <tr>
        <th>Name</th>
        <th>Subject 1</th>
        <th>Subject 2</th>
        <th>Subject 3</th>
        <th>Subject 4</th>
        <th>Subject 5</th>
        <th>Total</th>
        <th>Average</th>
        <th>Grade</th>
    </tr>

    <?php

    foreach ($_SESSION["students"] as $student) {

    ?>

    <tr>

        <td><?php 
           echo $student["name"]; 
        ?></td>
        <td><?php 
           echo $student["s1"]; 
        ?></td>
        <td><?php 
           echo $student["s2"]; 
        ?></td>
        <td><?php 
           echo $student["s3"]; 
        ?></td>
        <td><?php 
           echo $student["s4"]; 
        ?></td>
        <td><?php 
            echo $student["s5"]; 
        ?></td>
        <td><?php 
            echo $student["total"]; 
        ?></td>
        <td><?php 
            echo $student["average"]; 
        ?></td>
        <td><?php 
            echo $student["grade"]; 
        ?></td>

    </tr>

    <?php

    }

    ?>

</table>

<br>

<form method="post">

    <button type="submit">Clear All Results</button>

</form>

<?php

}

?>

</body>
</html>