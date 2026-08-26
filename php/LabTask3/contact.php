<?php

$success =false;
if($_SERVER["REQUEST_METHOD"]=="POST"){
    $name=trim($_POST["name"]);
    $email=trim($_POST["email"]);
    $subject=trim($_POST["subject"]);
    $mes=trim($_POST["mes"]);

    $attach=$_FILES["file"]["name"];
    $fileSize=$_FILES["file"]["size"];
    $fileErr=$_FILES["file"]["error"];
    $filetype=$_FILES["file"]["type"];

    $attachType=["application/pdf","image/jpeg"];
   

    $hasErr=false;
    $nameErr="";
    $emailErr="";
    $subjectErr="";
    $mesErr="";
    $attachErr="";

    //name
    if(empty($name))
        {
            $nameErr="Name have to be filled";
             $hasErr=true;
        }

        
    //email
    if(empty($email))
        {
            $emailErr="Email have to be filled";
             $hasErr=true;
        }      
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL))
        {
            $emailErr="Invalid email format";
            $hasErr=true;
        }
    //subject
    if(empty($subject))
        {
            $subjectErr="Subject have to be filled";
             $hasErr=true;
        }
    //message
    if(empty($mes))
        {
            $mesErr="Message have to be filled";
             $hasErr=true;
        }
    elseif(strlen($mes)<10)
        {
            $mesErr="Message have to be in minimum 10 characters";
             $hasErr=true;
        }
    //attachment
    if($fileErr!=4){

        if(!in_array($filetype,$attachType))
        {
           $attachErr="PDF and JPEG type file is allowed";
           $hasErr=true;
        }
        elseif($fileSize>(2*1024*1024))
            {
                $attachErr="File size is too big. File must be within 2MB";
                $hasErr=true;
            }

    }
    
    if($hasErr)
        {
            $url="Location:contact.php?".
            "nameErr=".urlencode($nameErr).
            "&emailErr=".urlencode($emailErr).
            "&subjectErr=".urlencode($subjectErr).
            "&mesErr=".urlencode($mesErr).
            "&attachErr=".urlencode($attachErr);
            //"&mes=".urlencode($mes);


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
    <form action="contact.php" method="post" enctype="multipart/form-data">
        Name:<input type="text" name="name" required>
        <span style="color:red">
            <?php
                 if(isset($_GET["nameErr"]))
                    {
                        echo $_GET["nameErr"];
                    }
            ?>
        </span><br><br>
        Email:<input type="email" name="email" required>
        <span style="color:red">
            <?php
                 if(isset($_GET["emailErr"]))
                    {
                        echo $_GET["emailErr"];
                    }
            ?>
        </span>
        <br><br>
        Subject:
        <select name="subject">
            <option value="">Select one subject</option>
            <option value="General">General</option>
            <option value="Support">Support</option>
            <option value="Feedback">Feedback</option>
        </select>
        <span style="color:red">
            <?php
                 if(isset($_GET["subjectErr"]))
                    {
                        echo $_GET["subjectErr"];
                    }
            ?>
        </span><br><br>
        Message:<br><br>
        <textarea name="mes">
            <?php
                 if(isset($_GET["mes"]))
                    {
                        echo htmlspecialchars($_GET["mes"]);
                    }
            ?>
        </textarea>
        <span style="color:red">
            <?php
                 if(isset($_GET["mesErr"]))
                    {
                        echo $_GET["mesErr"];
                    }
            ?>
        </span><br><br>
        Attachment:<input type="file" name="file">
        <span style="color:red">
            <?php
                 if(isset($_GET["attachErr"]))
                    {
                        echo $_GET["attachErr"];
                    }
            ?>
        </span><br><br>

        <input type="submit" value="Send Email">
</form>

<?php
if($success){
     echo "Email sent successfully<br><br>";

         echo"Name:".htmlspecialchars($name)."<br>";
         echo "Email:".htmlspecialchars($email)."<br>";
         echo "Subject:".htmlspecialchars($subject)."<br>";
         echo "Message:".htmlspecialchars($mes)."<br>";

         if($fileErr!=4)
            {
                echo "Attachment:".htmlspecialchars($attach)."<br>";
            }
    }
?>  
</body>
    
</html>