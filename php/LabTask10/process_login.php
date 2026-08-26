<?php
session_start();

if($_SERVER["REQUEST_METHOD"] == "POST")
{
   $username=$_POST["username"];
   $pass=$_POST["pass"];

   $testusername="admin";
   $testpassword="1234";


   if($username == $testusername && $pass == $testpassword){

      $_SESSION["username"]=$username;
      if(isset($_POST["remember"])){
           setcookie("username", $username, time()+(86000*30));
           header("Location:dashboard.php");
       }
            header("Location:dashboard.php");
            exit();
   }
    else{
        echo "Invalid username or password<br>";
        echo "<a href='login.php'>Back to home</a>";
       }
}
else{
    header("Location:login.php");
    exit();
}

?>