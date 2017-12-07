<?php

class Lib{

    function getHeader(){
        $head = "<head>
            <title>Project 1</title>
            <!-- Latest compiled and minified CSS -->
            <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'> 
            <link rel='stylesheet' href='styles/index.css' type='text/css'>
            <link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400' rel='stylesheet'> 
            <link href='https://fonts.googleapis.com/css?family=Montserrat:300,400' rel='stylesheet'>    
        </head>";
        echo $head;
    }

    function getNavBar(){
        session_start();

        $nav = "<nav>
                    <ul id='title-area'>
                        <li><img src='images/premlogo-long.png' height='100' width='200'></li>
                        <li><h1 id='nav-h1'>Premier League Fantasy Market</h1></li>
                    </ul>
                    <ul id='nav-items'>
                        <li><a href='index.php'>Home</a></li>
                        <li><a href='cart.php'>Cart</a></li>
                        <li><a href='admin.php'>Admin</a></li>";


        if(isset($_SESSION['userID'])){
            //if there is a user logged in 
            $nav .= "<li><button type='button' id='log-in-out-button'><a href='admin.php?logout=true'>Log Out</a></button></li>";
        } else { 
            //else no user is logged in 
            $nav .= "<li><button type='button' id='log-in-out-button'><a href='login.php'>Log In</a></button></li>";
        }
        $nav .= "</ul> 
            </nav>";
        echo $nav;
    }

    /*
     * outputCatalog takes a array of players which in this case is shortended to just the current page for performance. Each row or player is taken and their data is displayed.
     * $data is an array of associative arrays containing the player data.
     */
    function outputCatalog($data){
        foreach($data as $row){
            if($row['salePrice'] < 0.1){
                echo "<div class='well'><img class='playerJersey' src='{$row['img']}' height='100' width='100'>";
                echo "<p>Name: ".$row["playerName"]."</p>";
                echo "<p>Position: ".$row["position"]."</p>";
                echo "<p>{$row['description']}</p>";
                echo "<p>Price: <span class='price'>£".$row["price"]."m</span></p>";
                echo "<p>Quanitity: {$row['quantity']}</p>";
                if($row['quantity'] > 0){
                    echo "<button type='button'><a href='cart.php?pid={$row['id']}'>Add Player   <span class='glyphicon glyphicon-plus-sign'></span></a></button></div>";                
                } else {
                    echo "<button type='button' class='disabled'>Add Player   <span class='glyphicon glyphicon-plus-sign'></span></button></div>";                
                }
                echo "<hr/>";
            }
        }
    }

    /*
     * outputCatalog takes a array of players on sale. Each row or player is taken and their data is displayed.
     * The sale section is styled differently than the catalog.
     * $data is an array of associative arrays containing the player data for players on sale.
     */
    function outputSaleSection($data){
        foreach($data as $row){
            if($row['salePrice'] > 0.0){
                echo "<div class='row'><div class='col-md-2'><img class='playerJersey' src='{$row['img']}' height='50' width='50'></div>";
                echo "<div class='col-md-2'><p>Name: ".$row["playerName"]."</p></div>";
                echo "<div class='col-md-2'><p>Position: ".$row["position"]."</p>";
                echo "<p>{$row['description']}</p></div>";
                echo "<div class='col-md-2'><p>Original Price: <span class='price'>£".$row["price"]."m</span></p>";
                echo "<p>Sale Price: <span class='price'>£".$row["salePrice"]."m</span></p></div>";                
                echo "<div class='col-md-1'><p>Quanitity: {$row['quantity']}</p></div>";
                if($row['quantity'] > 0){
                    echo "<div class='col-md-3'><button type='button' class='pull-right'><a href='cart.php?pid={$row['id']}'>Add Player   <span class='glyphicon glyphicon-plus-sign'></span></a></button></div></div>";                
                } else {
                    echo "<div class='col-md-3'><button type='button' class='disabled pull-right'>Add Player   <span class='glyphicon glyphicon-plus-sign'></span></button></div></div>";                
                }
                echo "<hr/>";
            }
        }
    }

    function outputAddPlayerForm($formMessages){
        $addPlayerForm = "
        <form action='' method='post' id='frmLogin'>
        <h4>Add Player</h4>
            <div class='error-message'>{$formMessages['addErrorMessage']}</div>	
            <div class='success-message'>{$formMessages['addSuccessMessage']}</div>                        
            <div class='field-group'>
                <div><label for=''>Name of Player</label></div>
                <div><input name='playerName' type='text' class='input-field'></div>
            </div>
            <div class='field-group'>
                <div><label for=''>Position</label></div>
                <div><input name='position' type='text' class='input-field'></div>
            </div>
            <div class='field-group'>
                <div><label for=''>Description</label></div>
                <div><input name='description' type='text' class='input-field'> </div>
            </div>
            <div class='field-group'>
                <div><label for=''>Transfer Price</label></div>
                <div><input name='price' type='text' class='input-field'> </div>
            </div>
            <div class='field-group'>
                <div><label for=''>Quantity</label></div>
                <div><input name='quantity' type='text' class='input-field'> </div>
            </div>
            <div class='field-group'>
                <div><label for=''>Image</label></div>
                <div><input name='image' type='text' placeholder='images/arsenal.png' class='input-field'> </div>
            </div>
            <div class='field-group'>
                <div><label for=''>Sale Price</label></div>
                <div><input name='salePrice' type='text' class='input-field'> </div>
            </div>
            <div class='field-group'>
                <div><input type='submit' name='add-player' value='Add Player' class='form-submit-button'></div>
            </div>       
        </form>";
        echo $addPlayerForm;
    }

    function outputEditPlayerForm($formMessages){

        require_once ("DB.class.php");
        $db = new DB();

        $editPlayerForm = "
        <form action='' method='post' id='frmLogin'>
            <h4>Edit Player</h4>
            <div class='error-message'>{$formMessages['editErrorMessage']}</div>
            <div class='success-message'>{$formMessages['editSuccessMessage']}</div>
            <div class='field-group'>
            <select id='playerSelect' onchange='populateForm()'>";
        $data = $db->getAllPlayers();
        $editPlayerForm .= "<option value='' selected>--Select A Player--</option>"; //this is the base option for the dropdown
        foreach($data as $row){
            $editPlayerForm .= "<option value='{$row['id']}'>{$row['playerName']}</option>"; //add each player name to the dropdown so the admin can select the player they wish to edit
        }
        $editPlayerForm .= "         
            </select>
            </div>
            <div class='field-group'>
                <div><label for=''>Name of Player</label></div>
                <div><input name='playerName' type='text' class='input-field'></div>
            </div>
            <div class='field-group'>
                <div><label for=''>Position</label></div>
                <div><input name='position' type='text' class='input-field'> </div>
            </div>
            <div class='field-group'>
                <div><label for=''>Description</label></div>
                <div><input name='description' type='text' class='input-field'> </div>
            </div>
            <div class='field-group'>
                <div><label for=''>Transfer Price</label></div>
                <div><input name='price' type='text' class='input-field'> </div>
            </div>
            <div class='field-group'>
                <div><label for=''>Quantity</label></div>
                <div><input name='quantity' type='text' class='input-field'> </div>
            </div>
            <div class='field-group'>
                <div><label for=''>Sale Price</label></div>
                <div><input name='salePrice' type='text' class='input-field'> </div>
            </div>
            <div class='field-group'>
                <div><input type='submit' name='edit-player' value='Submit Changes' class='form-submit-button'></div>
            </div>       
        </form>";
        echo $editPlayerForm;
    }

    function autoFillPlayerForm($formMessages, $thePlayer){

        require_once ("DB.class.php");
        $db = new DB();

        $editPlayerForm = "
        <form action='' method='post' id='frmLogin'>
            <h4>Edit Player</h4>
            <div class='error-message'>{$formMessages['editErrorMessage']}</div>
            <div class='success-message'>{$formMessages['editSuccessMessage']}</div>
            <div class='field-group'>
            <select id='playerSelect' onchange='populateForm()'>";
        $data = $db->getAllPlayers();
        $editPlayerForm .= "<option value='' selected>--Select A Player--</option>"; //this is the base option for the dropdown
        foreach($data as $row){
            $editPlayerForm .= "<option value='{$row['id']}'>{$row['playerName']}</option>"; //add each player name to the dropdown so the admin can select the player they wish to edit
        }
        $editPlayerForm .= "
            </select>
            </div>
            <div class='field-group'>
                <div><label for=''>Name of Player</label></div>
                <div><input name='playerName' type='text' value='{$thePlayer['playerName']}' class='input-field'></div>
            </div>
            <div class='field-group'>
                <div><label for=''>Position</label></div>
                <div><input name='position' type='text' value='{$thePlayer['position']}' class='input-field'> </div>
            </div>
            <div class='field-group'>
                <div><label for=''>Description</label></div>
                <div><input name='description' type='text' value='{$thePlayer['description']}' class='input-field'> </div>
            </div>
            <div class='field-group'>
                <div><label for=''>Transfer Price</label></div>
                <div><input name='price' type='text' value='{$thePlayer['price']}' class='input-field'> </div>
            </div>
            <div class='field-group'>
                <div><label for=''>Quantity</label></div>
                <div><input name='quantity' type='text' value='{$thePlayer['quantity']}' class='input-field'> </div>
            </div>
            <div class='field-group'>
                <div><label for=''>Sale Price</label></div>
                <div><input name='salePrice' type='text' value='{$thePlayer['salePrice']}'class='input-field'> </div>
            </div>
            <div class='field-group'>
                <div><input type='submit' name='edit-player' value='Submit Changes' class='form-submit-button'></div>
            </div>       
        </form>";
        echo $editPlayerForm;
    }











}
?>