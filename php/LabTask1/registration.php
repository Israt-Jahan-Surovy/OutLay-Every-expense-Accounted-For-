<?php
  
  $success=false;

  if($_SERVER["REQUEST_METHOD"]=="POST"){
     
  $name=trim($_POST["name"]);
  $email=trim($_POST["email"]);
  $pass=trim($_POST["pass"]);
  $confpass=trim($_POST["confpass"]);

  $hasErr=false;
  $nameErr="";
  $emailErr="";
  $passErr="";
  $confpassErr="";
//name
  if(empty($name))
    {
       $nameErr="Name has to be filled";
       $hasErr=true;
    }
//email
  if(empty($email))
    {
         $emailErr="Email has to be filled";
         $hasErr=true;
    }
  elseif(!filter_var($email,FILTER_VALIDATE_EMAIL))
    {
        $emailErr="Invalid Email format";
        $hasErr=true;
    }
//pass
    if(empty($pass))
        {
            $passErr="Password required to be filled";
            $hasErr=true;
        }
//confpass
    if(empty($confpass))
        {
            $confpassErr="Confirm Password required to be filled";
            $hasErr=true;
        }
    if($pass!=$confpass)
        {
            $confpassErr="Password didn't match";
            $hasErr=true;
        }
    



    if($hasErr)
        {
            $url="Location:registration.php?".
            "nameErr=".urlencode($nameErr).
            "&emailErr=".urlencode($emailErr).
            "&passErr=".urlencode($confpassErr).
            "&confpassErr=".urlencode($confpassErr);

            header($url);
            exit();
        }
    else{
        $success=true;
    }

}



?>

<!Doctype html>
<html>
    <head></head>
    <body>
        <form action="registration.php" method="post">
            Name:<input type="text" name="name"><br>
            <span style = "color:red">
                <?php
                  if(isset($_GET["nameErr"]))
                    {
                        echo($_GET["nameErr"]);
                    }
                ?>
            </span><br><br>
            Email:<input type="email" name="email"><br>
             <span style = "color:red">
                <?php
                  if(isset($_GET["emailErr"]))
                    {
                        echo($_GET["emailErr"]);
                    }
                ?>
            </span><br><br>

            Password:<input type="password" name="pass"><br>
            <span style = "color:red">
                <?php
                  if(isset($_GET["passErr"]))
                    {
                        echo($_GET["passErr"]);
                    }
                ?>
            </span><br><br>
            Confirm Password: <input type="password" name="confpass"><br>
            <span style = "color:red">
                <?php
                  if(isset($_GET["confpassErr"]))
                    {
                        echo($_GET["confpassErr"]);
                    }
                ?>
            </span><br><br>
            <input type="submit" name="valid">

        </form>
    <?php
      if($success){  
        echo"Registration Successfully.<br><br>";

        echo"Name:".htmlspecialchars($name)."<br>";
        echo"Email:".htmlspecialchars($email)."<br>";
        echo"Password:".htmlspecialchars($pass)."<br>";
        echo"Confirm Password:".htmlspecialchars($confpass)."<br>";
      }
    ?>
</body>

</html>