<?php 

class DB{
    private $connection;

    function __construct(){
        $this->connection = new mysqli( $_SERVER['DB_SERVER'],
                                        $_SERVER['DB_USER'],
                                        $_SERVER['DB_PASSWORD'],
                                        $_SERVER['DB']); 
        /* mysqli connection with (server, username, password, and database name). 
        These are found in the.htaccess file in the sites directory */
        if($this->connection->connect_error){
            echo "Connection failed: ".mysqli_connect_error();
            die();
        }
    } //constructor

    function login($username, $password){
        $invalid_characters = array("%", "*", "=", "#", "<", ">", "|");
        //remove any characters from the given username and password that could be used for code injection
        $username = str_replace($invalid_characters, "", $username);
        $password = str_replace($invalid_characters, "", $password);
        //now the strings are 'clean'...
        //compare the user name and password to those in the database
        //for the password, take the given password and add the salt, then use md5 hash. 
        
        //if correct the salted and hashed password will match the value in the database.
        $salt = "_now_batting_for_the_yankees...number_2_derek_jeter";
        $password = md5($password.$salt); 
        $data = [];
        if($stmt = $this->connection->prepare("SELECT userID, username FROM admin WHERE username = ? AND password = ?")){
            $stmt->bind_param("ss",$username,$password); //binding params, replacing the '?'
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($user_ID, $user_name); 
            if($stmt->num_rows > 0){
                while($stmt->fetch()){
                    $data[] = array('userID'=>$user_ID,
                                    'username'=>$user_name); //adding data to the fields in the associative array.
                }
            }
        }
        return $data;
    }

    function getPlayer($playerID){
        $data = array();
        if($stmt = $this->connection->prepare("SELECT ID, PlayerName, Position, Description, ImageName, Price, Quantity, SalePrice FROM players WHERE ID = ? ")){
            $stmt->bind_param("i",intval($playerID)); //binding param, replacing the '?'            
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $playerName,$position,$description,$img,$price,$quantity, $salePrice); 
            if($stmt->num_rows > 0){
                while($stmt->fetch()){
                    $data[] = array('id'=>$id,
                                    'playerName'=>$playerName,
                                    'position'=>$position,
                                    'description'=>$description,
                                    'img'=>$img,
                                    'price'=>$price,
                                    'quantity'=>$quantity,
                                    'salePrice'=>$salePrice); //adding data to the fields in the associative array.
                }
            }
        }
        return $data;
    }

    function getAllPlayers(){
        $data = array(); //creating empty array to hold data

        if($stmt = $this->connection->prepare("SELECT ID, PlayerName, Position, Description, ImageName, Price, Quantity, SalePrice FROM players")){
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $playerName,$position,$description,$img,$price,$quantity, $salePrice); 
            if($stmt->num_rows > 0){
                while($stmt->fetch()){
                    $data[] = array('id'=>$id,
                                    'playerName'=>$playerName,
                                    'position'=>$position,
                                    'description'=>$description,
                                    'img'=>$img,
                                    'price'=>$price,
                                    'quantity'=>$quantity,
                                    'salePrice'=>$salePrice); //adding data to the fields in the associative array.
                }
            }
        }
        return $data;
    } //getAllPlayers()

    function getAllPlayersLimited($start,$numOfRows){
        $data = array(); //creating empty array to hold data
        if($stmt = $this->connection->prepare("select ID, PlayerName, Position, Description, ImageName, Price, Quantity, SalePrice from players limit ?,?")){
            $stmt->bind_param("ii",intval($start),intval($numOfRows)); //binding param, replacing the '?'
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $playerName,$position,$description,$img,$price,$quantity, $salePrice); 
            if($stmt->num_rows > 0){
                while($stmt->fetch()){
                    $data[] = array('id'=>$id,
                                    'playerName'=>$playerName,
                                    'position'=>$position,
                                    'description'=>$description,
                                    'img'=>$img,
                                    'price'=>$price,
                                    'quantity'=>$quantity,
                                    'salePrice'=>$salePrice); //adding data to the fields in the associative array.
                }
            }
        }
        return $data;
    }

    function addPlayer($playerDataArray){

        $succeeded = false;
        //TODO: HAVE TO CHECK IF IN RANGE OF SALE ITEMS!!!
        if( ($playerDataArray['salePrice'] > 0.0) && ($this->getNumSaleItems() >= 5) ){
            //if there are already 5 items on sale and the item being added is set to be put on sale
            $succeeded = false;
            echo "<script>alert('You are trying to place an item on sale but there are already {$this->getNumSaleItems()} items on sale!')</script>";
        } else {
            //if they do not try to add a sale player, or if less than 5 items on sale...
            if($stmt = $this->connection->prepare("INSERT INTO players (PlayerName, Position, Description, Price, Quantity, ImageName, SalePrice) VALUES (?,?,?,?,?,?,?)" )){
                $stmt->bind_param("sssdisd", $playerDataArray['playerName'], $playerDataArray['position'], $playerDataArray['description'], doubleval($playerDataArray['price']),
                                    intval($playerDataArray['quantity']), $playerDataArray['image'], doubleval($playerDataArray['salePrice']));
                if($stmt->execute()) {
                    //if it was inserted
                    $succeeded = true;
                }
            }
        }
        return $succeeded;
    }

    /**
    * editPlayer takes an array which will contain the player name, position, description, price, quantity, and sale price
    * returns true or false
    */
    function editPlayer($playerDataArray){
        $succeeded = false;
        //TODO: HAVE TO CHECK IF IN RANGE OF SALE ITEMS!!! a little trickier than add, will require a transaction 
        //start transaction, update the player, then check the number, if out of range...edit is not allowed, rollback

        mysqli_autocommit($this->connection, false);

        if($stmt = $this->connection->prepare("UPDATE players SET PlayerName = ?, Position = ?, Description = ?, Price = ?, Quantity = ?, SalePrice = ? WHERE ID = ?; ")){
            $stmt->bind_param("sssdidi", $playerDataArray['playerName'],$playerDataArray['position'], $playerDataArray['description'],doubleval($playerDataArray['price']),
                                intval($playerDataArray['quantity']),doubleval($playerDataArray['salePrice']),intval($playerDataArray['id']) );
            if($stmt->execute()) {
                //if it worked
                if(($this->getNumSaleItems() > 5) || ($this->getNumSaleItems() < 3)){
                    //if sale items is now under 3 or more than 5...
                    mysqli_rollback($this->connection);
                    echo "<script>alert('You are trying to place an item on sale but there are already {$this->getNumSaleItems()} items on sale!')</script>";                    
                } else {
                    //otherwise sale items is in the right range
                    mysqli_commit($this->connection);
                    $succeeded = true;
                }
            }
        }
        return $succeeded;
    }

    function addPlayerToCart($playerID){
        // atomic, all or nothing transaction 
        // add player to cart table
        // update the quantity in the catalog

        mysqli_autocommit($this->connection, false);
        $succeeded = false;

        if($stmt = $this->connection->prepare("insert into cart (playerID, quantity) values (?,?)")){
            $stmt->bind_param("ii",intval($playerID),intval(1));
            if (!$stmt->execute()) { 
                // insert into cart failed
                $succeeded = false;
            } else{
                //otherwise insert into cart executed
                //now take away from players
                if($stmt = $this->connection->prepare("UPDATE players SET Quantity = Quantity - 1 WHERE ID = ?")){
                    $stmt->bind_param("i",intval($playerID));
                    if (!$stmt->execute()) { 
                        // it failed
                        $succeeded = false;
                    } else {
                        // else both updates succeeded
                        $succeeded = true;
                    }
                }
            }

            if (!$succeeded){
                //if there was a problem
                mysqli_rollback($this->connection);
            } else {
                //else successully entered the data...now commit. 
                mysqli_commit($this->connection);
                //echo "Successfully added player to database.";
            }
        }
        return $succeeded;
    }

    function getAllPlayersInCart(){
        //cart is full of player id's 
        //foreach player id
        //using player id, join player table, get player info

        $data = array(); //creating empty array to hold data
        
        if($stmt = $this->connection->prepare("SELECT players.ID, players.PlayerName, players.Position, players.Description, players.ImageName, players.Price, cart.quantity, players.SalePrice FROM players JOIN cart WHERE cart.playerID = players.ID")){
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $playerName,$position,$description,$img,$price,$quantity, $salePrice); 
            if($stmt->num_rows > 0){
                while($stmt->fetch()){
                    $data[] = array('id'=>$id,
                                    'playerName'=>$playerName,
                                    'position'=>$position,
                                    'description'=>$description,
                                    'img'=>$img,
                                    'price'=>$price,
                                    'quantity'=>$quantity,
                                    'salePrice'=>$salePrice); //adding data to the fields in the associative array.
                }
            }
        }
        return $data;
    }

    function getNumSaleItems(){
        $numSaleItems = 0;
        if($stmt = $this->connection->prepare("SELECT COUNT(ID) AS NumberOfSaleItems FROM players WHERE SalePrice > ?;")){
            $stmt->bind_param("i",intval(0));
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($numberOfSaleItems);
            if($stmt->num_rows > 0){
                while($stmt->fetch()){
                    $numSaleItems = $numberOfSaleItems;
                }
            }
        }
        return $numSaleItems;
    }

    function truncateTable($thisTable){
        $succeeded = false;
        // table name is passed in and then that table has all rows removed
        if($stmt = $this->connection->prepare("TRUNCATE TABLE $thisTable")){
            if($stmt->execute()){
                // query successful
                $succeeded = true;
            } 
            
        }
        return $succeeded;
    }

} //class