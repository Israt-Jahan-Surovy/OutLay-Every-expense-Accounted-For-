<!DOCTYPE html>
<html>
<head>
    <title>Calculator</title>
</head>

<body>

<form action="calculator.php" method="post">

    <label>First number:</label>
    <input type="text" name="x"><br><br>

    <label>Second number:</label>
    <input type="text" name="y"><br><br>

    <select name="operation" id="calc">

        <option value="">Calculate</option>
        <option value="+">+</option>
        <option value="-">-</option>
        <option value="*">*</option>
        <option value="/">/</option>

    </select>

    <br><br>

    <button type="submit">Submit</button>

</form>

<?php
if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $x=$_POST["x"];
    $y=$_POST["y"];
    $operation=$_POST["operation"];

    if(!is_numeric($x) || !is_numeric($y))
        {
            echo"Plese enter valid numbers";
        }
    else{
        switch($operation){
            case "+":
                $result=$x+$y;
                echo"Result: $x+$y=$result";
                break;
            case "-":
                $result =$x-$y;
                echo"Result: $x-$y=$result";
                break;
            case "*":
                $result =$x*$y;
                echo"Result: $x*$y=$result";
                break;
            case "/":
                if($y==0)
                {
                    echo "Error: Cannot divide by zero.";
                }
                else{
                    $result = $x / $y;
                    echo "Result: $x / $y = $result";
                }
                break;
            default:
                echo "Please select an operation.";
        }
    }
}
?>
















