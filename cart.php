<!doctype html>

<html lang="en">
<?php
require_once("LIB_project1.php");
$lib = new Lib();
$lib->getHeader(); //gets the head tag content
$lib->getNavBar(); //includes the nav bar 
?>
<body>
    
    <h2>Cart</h2>
    <?php
        require_once ("DB.class.php");

        $db = new DB();

        //if this request came from an 'add player' button...
        //check & update tables for specific rows
        //send back to last page with alert message
        if(isset($_GET['pid'])){
            $playerID = $_GET['pid'];

            if(!$db->addPlayerToCart($playerID)){
                echo "<a href='index.php'><- Back to players</a>";                
                echo "<script type='text/javascript'>alert('Woops! Error adding player to cart.');</script>";
            } else {
                echo "<h2><a href='index.php'><- Back to players</a></h2>";                
                echo "<script type='text/javascript'>alert('Successfully added item to your cart!');</script>";
            }
        } else {
            //if no params in url (eg. just clicked in global nav) display cart items

            if(isset($_GET['empty'])){
                if(!$db->truncateTable("cart")){
                    echo "<script type='text/javascript'>alert('Woops! An error occurred emptying your cart.');</script>";                    
                } else {
                    header("Location: cart.php");
                }

            }

            echo "<div id='emptyCartWrapper' class='row'><div class='col lg 12'>";
            echo "<button class='pull-right'><a href='cart.php?empty=true'>Empty Cart</a></button>";
            echo "</div></div>";
            $cartItems = $db->getAllPlayersInCart();
            if(count($cartItems) > 0){
                $totalPrice = 0;
                foreach($cartItems as $row){
                    echo "<div class='well'>";
                    echo "<p>Name: ".$row["playerName"]."</p>";
                    echo "<p>Position: ".$row["position"].", {$row['description']}</p>";
                    echo "<p>Quantity: {$row['quantity']}</p>";
                    if ($row['salePrice'] == 0.0){
                        echo "<p>Price: <span class='price'>£".$row["price"]."m</span></p>";
                        $totalPrice = ($totalPrice + $row["price"]);
                    } else {
                        echo "<p>Sale Price: <span class='price'>£".$row["salePrice"]."m</span></p>";
                        $totalPrice = ($totalPrice + $row["salePrice"]);
                    }
                    echo "</div>";                    
                    echo "<hr/>";
                }
                echo "<h2>Total cost of transfers: <span class='price'>£{$totalPrice}m</span></h2>";
            } else {
                echo "<h2>Your cart is empty!</h2>";
            }
        }


    ?>
</body>
</html>