
<!doctype html>

<html lang="en">
<?php
require_once("LIB_project1.php");
$lib = new Lib();
$lib->getHeader(); //gets the head tag content
$lib->getNavBar(); //includes the nav bar 
?>
<body>
    <h2>Admin Log In</h2>
    <?php 
        //login for admin
        //two sections add and edit... (use url variable to know which?)
        //Have a dropdown with all player names to edit
        //When admin selects a name to edit, js function is used to execute php echo for form (AJAX?)
        //Enters data...sanitized and updated in DB Class
        require_once ("DB.class.php");
        
        $db = new DB(); 
        
        $message = "";
        
        if(isset($_POST["user_name"]) && isset($_POST["password"])){
            $data = $db->login($_POST["user_name"], $_POST["password"]);
            if(!empty($data)){
                session_start();
                $_SESSION["userID"] = $data[0]["userID"];
                $_SESSION["username"] = $data[0]["username"];
                header("Location: admin.php");
            } else {
                $message = "Invalid Username or Password!";
            }         
        } else {
            $message = "Please Log In";            
        }
    ?>

    <div class='centerContentWrapper'>     
        <form action="" method="post" id="frmLogin">
            <div class="error-message"><?php if(isset($message)) { echo $message; } ?></div>	
            <div class="field-group">
                <div><label for="login">Username</label></div>
                <div><input name="user_name" type="text" class="input-field"></div>
            </div>
            <div class="field-group">
                <div><label for="password">Password</label></div>
                <div><input name="password" type="password" class="input-field"> </div>
            </div>
            <div class="field-group">
                <div><input type="submit" name="login" value="Login" class="form-submit-button"></span></div>
            </div>       
        </form>
    </div>



</body>
</html>