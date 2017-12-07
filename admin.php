<!doctype html>
<html lang="en">
<?php
require_once("LIB_project1.php");
$lib = new Lib();
$lib->getHeader(); //gets the head tag content
$lib->getNavBar(); //includes the nav bar 
?>
<body>
    <h2>Admin</h2>
    <?php 

        require_once ("DB.class.php");    
        $db = new DB();

        //if the user pressed the logout button, unset their credential variables, destroy the session, and send them to the login page
        if(isset($_GET['logout'])){
            session_unset();
            session_destroy();
            header("Location: login.php");            
        }

        //if the session variables are not set, send them to login page
        if(!isset($_SESSION['userID']) || !isset($_SESSION['username'])){
            header("Location: login.php");
        } else {
            //otherwise if they have the right credentials, display the information that the logged in user needs. 
            echo "<div class='centerContentWrapper'><h3 class='title'>Hi {$_SESSION['username']}!</h3></div>";

            $addErrorMessage = "";
            $editErrorMessage = "";
            $addSuccessMessage =  "";
            $editSuccessMessage = "";

            if(!empty($_POST['add-player'])){
    
                $fields = array('playerName', 'position', 'description', 'price', 'quantity', 'image', 'salePrice');
                
                $error = false; //No errors yet
                foreach($fields AS $fieldname) { //Loop trough each field
                    if(!isset($_POST[$fieldname]) || empty($_POST[$fieldname])) {
                    $addErrorMessage = 'Please make sure all fields are complete'; //Display error with field
                    $error = true; //Yup there are errors
                    }
                }
                
                if(!$error) { //Only create queries when no error occurs
                    $playerDataArray = array(
                    "playerName"=>$_POST["playerName"],
                    "position"=>$_POST["position"],
                    "description"=>$_POST["description"],
                    "price"=>$_POST["price"],
                    "quantity"=>$_POST["quantity"],
                    "image"=>$_POST["image"],
                    "salePrice"=>$_POST["salePrice"]);
                    foreach($playerDataArray as $key=>$value){
                        $invalid_characters = array("%", "*", "=", "#", "<", ">", "|", "!");
                        //remove any characters from the given values that could be used for code injection
                        $playerDataArray[$key] = str_replace($invalid_characters, "", $value);
                        //now the strings are 'clean'...
                    }
                    if($db->addPlayer($playerDataArray)){
                        $addSuccessMessage = "{$playerDataArray['playerName']} Added!";
                    } else {
                        $addErrorMessage = "Error Adding Player. Please Check All Input.";
                    }
                }
            }
            if(!empty($_POST['edit-player'])){
                if (!empty($_GET['pid'])){

                    $fields = array('playerName', 'position', 'description', 'price', 'quantity', 'salePrice');
                    
                    $error = false; //No errors yet
                    foreach($fields AS $fieldname) { //Loop trough each field
                      if(!isset($_POST[$fieldname]) || empty($_POST[$fieldname])) {
                        $editErrorMessage = 'Please make sure all fields are complete'; //Display error with field
                        $error = true; //Yup there are errors
                      }
                    }
                    
                    if(!$error) { //Only create queries when no error occurs
                        $playerDataArray = array(
                        "id"=>$_GET["pid"],
                        "playerName"=>$_POST["playerName"],
                        "position"=>$_POST["position"],
                        "description"=>$_POST["description"],
                        "price"=>$_POST["price"],
                        "quantity"=>$_POST["quantity"],
                        "salePrice"=>$_POST["salePrice"]);
                        foreach($playerDataArray as $key=>$value){
                            $invalid_characters = array("%", "*", "=", "#", "<", ">", "|", "!");
                            //remove any characters from the given values that could be used for code injection
                            $playerDataArray[$key] = str_replace($invalid_characters, "", $value);
                            //now the strings are 'clean'...
                        }
                        if($db->editPlayer($playerDataArray)){
                            $editSuccessMessage = "Player Updated!";
                        } else {
                            $editErrorMessage = "Error Updating Player. Please Check All Input.";
                        }
                    }
                }
            }
            echo "<div class='row'>"; //start the bootstrap row containing both the forms. The add player and edit player forms are in seperate columns in the same row.
            echo"<div class='col-md-6 centerContentWrapper'>"; //start the column that will contain the add player form
                //output the add player form
                $addPlayerFormMessages = array( "addErrorMessage" => $addErrorMessage,
                                                "addSuccessMessage" => $addSuccessMessage);
                $lib->outputAddPlayerForm($addPlayerFormMessages);
            echo"</div>"; // end the column containing the add player form

            echo"<div class='col-md-6 centerContentWrapper'>"; //start the column that will contain the edit player form 
            $editPlayerFormMessages = array( "editErrorMessage" => $editErrorMessage,
                                             "editSuccessMessage" => $editSuccessMessage);    
                if(!isset($_GET['pid'])){
                    //if a player ID is not provided, a blank form will appear
                    $lib->outputEditPlayerForm($editPlayerFormMessages);
                } else {
                    //else if a player ID is provided, their respective information will be filled in to the form
                    $playerData = $db->getPlayer($_GET['pid']);
                    $thePlayer = $playerData[0];
                    $lib->autoFillPlayerForm($editPlayerFormMessages,$thePlayer);
                }
            echo"</div>";// end the column containing the edit player form
            echo"</div>";//end the bootstrap row containing both forms
        }
        echo "<p>Number of sale items: {$db->getNumSaleItems()}</p>"
    
    ?>
    <script>
        function populateForm(){
            var e = document.getElementById("playerSelect");
            var selectedPlayerStr = e.options[e.selectedIndex].value;
            console.log("Player ID is: " + selectedPlayerStr);
            window.location = "http://serenity.ist.rit.edu/~exf6514/341/project1/admin.php?pid=" + selectedPlayerStr;
        }
    </script>
</body>
</html>