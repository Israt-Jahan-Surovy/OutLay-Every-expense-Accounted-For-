<?php

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST["student_name"];
    $s1 = $_POST["subject1"];
    $s2 = $_POST["subject2"];
    $s3 = $_POST["subject3"];
    $s4 = $_POST["subject4"];
    $s5 = $_POST["subject5"];

    // Check empty fields
    if (
        $name == "" ||
        $s1 == "" ||
        $s2 == "" ||
        $s3 == "" ||
        $s4 == "" ||
        $s5 == ""
    ) {

        echo "Please fill in all fields.";
        echo "<br><a href='index.php'>Go Back</a>";

    }

    // Check numeric values
    elseif (
        !is_numeric($s1) ||
        !is_numeric($s2) ||
        !is_numeric($s3) ||
        !is_numeric($s4) ||
        !is_numeric($s5)
    ) {

        echo "Please enter numeric marks only.";
        echo "<br><a href='index.php'>Go Back</a>";

    }

    // Check marks between 0 and 100
    elseif (
        $s1 < 0 || $s1 > 100 ||
        $s2 < 0 || $s2 > 100 ||
        $s3 < 0 || $s3 > 100 ||
        $s4 < 0 || $s4 > 100 ||
        $s5 < 0 || $s5 > 100
    ) {

        echo "Marks must be between 0 and 100.";
        echo "<br><a href='index.php'>Go Back</a>";

    }

    else {

        // Calculate total
        $total = $s1 + $s2 + $s3 + $s4 + $s5;

        // Calculate average
        $average = $total / 5;

        // Determine grade
        if ($average >= 90) {
            $grade = "A";
        }
        elseif ($average >= 80) {
            $grade = "B";
        }
        elseif ($average >= 70) {
            $grade = "C";
        }
        elseif ($average >= 60) {
            $grade = "D";
        }
        else {
            $grade = "F";
        }

        // Create student array
        $student = [
            "name" => $name,
            "s1" => $s1,
            "s2" => $s2,
            "s3" => $s3,
            "s4" => $s4,
            "s5" => $s5,
            "total" => $total,
            "average" => $average,
            "grade" => $grade
        ];

        // Create session if it doesn't exist
        if (!isset($_SESSION["students"])) {
            $_SESSION["students"] = [];
        }

        // Store student
        $_SESSION["students"][] = $student;

        ?>

        <!DOCTYPE html>
        <html>
        <head>
            <title>Grade Result</title>
        </head>

        <body>

        <h1>Student Grade Result</h1>

        <table border="1" cellpadding="10">

            <tr>
                <th>Student Name</th>
                <th>Total</th>
                <th>Average</th>
                <th>Grade</th>
            </tr>

            <tr>
                <td><?php echo $name; ?></td>
                <td><?php echo $total; ?></td>
                <td><?php echo $average; ?></td>
                <td><?php echo $grade; ?></td>
            </tr>

        </table>

        <br>

        <a href="index.php">Add Another Student</a> |
        <a href="results.php">View All Results</a>

        </body>
        </html>

        <?php
    }
}

?>