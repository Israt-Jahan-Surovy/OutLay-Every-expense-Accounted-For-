<?php
session_start();
  if(!isset($_SESSION["username"]))
    {
        header("Location:login.php");
        exit();
    }
   $username=$_SESSION["username"];
?>
<!Doctype html>
<html>
    <head></head>
    <body>
        <h2>Welcome,
            <?php
               echo htmlspecialchars($username);
            ?>
        </h2>
        <h3>Information retrieved from session
            <?php
              echo htmlspecialchars($_SESSION["username"]);
            ?>
        </h3>
        <p>
            
            <?php
            echo "Session ID: ".session_ID();
            ?>
        </p>
        <?php
            if(isset($_COOKIE["username"]))
                {
                    echo "Cookie is set<br><br>";
                    echo"Cookie value:".htmlspecialchars($_COOKIE["username"]);
                }
            else{
                echo"Cookie is not set";
            }
        ?>
    <br><br>
    <a href='logout.php'>Log Out</a>
    </body>
</html>