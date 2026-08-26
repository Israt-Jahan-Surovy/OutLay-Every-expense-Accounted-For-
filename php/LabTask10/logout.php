<?php

     session_start();

     session_unset();
     session_destroy();

     setcookie("name","",time()-3600);

?>

<!Doctype html>
<html>
    <head></head>
    <body>
        <p style="color:green;"> You have been logged out successfully.</p>
        <a href='login.php'>Back to login</a>
    </body>
</html>