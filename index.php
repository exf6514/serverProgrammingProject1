<!doctype html>

<html lang="en">
<?php
 require_once("LIB_project1.php");
 $lib = new Lib();
 $lib->getHeader(); //gets the head tag content
 $lib->getNavBar(); //includes the nav bar 
?>
<body>
    <?php
        require_once ("DB.class.php");

        $db = new DB();

        $data = $db->getAllPlayers();

        $start = ( (isset($_GET['start'])) ? $_GET['start'] : 0);
        //$numOfRowsDisplayed = ( (isset($_GET['l'])) ? $_GET['end'] : 5); // 'l' is the limit used in the second param in the LIMIT clause. It is always 5
        
        $limitedData = $db->getAllPlayersLimited($start,5);
        $numOfItems = count($data);
        $numPagesNeeded = ceil($numOfItems/5);

        // --- start catalog section ---
        echo "<h2>Players</h2>";
        $lib->outputCatalog($limitedData);
        //end catalog section 

        // --- start sale section ---
        echo "<h2>Player Deals</h2>";        
        echo "<div id='saleSection' class='well'>";
            $lib->outputSaleSection($data);
        echo "</div>";
        //end sale section

        // --- start pagination section ---
        $i = 1;
        echo "<div id='paginationWrapper'>";
        while($i<=$numPagesNeeded){
            $start = ($i - 1) * 5;
            echo "<a href='index.php?start=$start&l=5'>$i </a>";
            $i++;
        }
        echo "</div>";
        //end pagination section
    ?>
</body>
</html>