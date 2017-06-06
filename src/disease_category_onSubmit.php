<?php

//Header files
require_once('dbAcess.php'); //connect to database


//variable assignment
$disCategorySelected = mysqli_real_escape_string($dbConnect, $_POST["disCategorySelected"]);
$netGenMethod =  mysqli_real_escape_string($dbConnect, $_POST["netGenMethod"]);
$influenceMethodSelected = mysqli_real_escape_string($dbConnect, $_POST["influenceMethodSelected"]);

//Remember: These echo statements are only to check how the the program is coming. They should be removed before running because the calling Ajax function in index.php file is expecting only JSON response. Mixing it with echo statements will not make it a JSON response and the json.parse function will fail!
//echo "\n dis Category is -> ".$disCategorySelected;
//echo "\n net gen method is -> ".$netGenMethod;
//echo "\n Influence Method Selected is -> ".$influenceMethodSelected;

$queryResult = array(); //Varible to store the query result

if(ISSET($_POST["disCategorySelected"]) and ISSET($_POST["netGenMethod"]) and ISSET($_POST["influenceMethodSelected"]))
 {
   // The following piece of code is now not necessary because we have defaulted the value of netGenMethod to 'Optimized network'. So the following would never be executed. 
   //if($netGenMethod == 'All edges above 0.9 score, rescored to 0.05' )
 // {
 //   $query = "SELECT mirna1 AS source, mirna2 AS target, score AS type FROM mirna_rescored WHERE dis_category='".$disCategorySelected."' ORDER BY type DESC LIMIT 500";
 //  $queryCSV = "SELECT mirna1 AS source, mirna2 AS target, score AS type FROM mirna_rescored WHERE dis_category='".$disCategorySelected."' ORDER BY type DESC into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirid/CSV/network.csv' fields terminated by ','"; //Dummy query for test

 // } //Ending if($netGenMethod...)

//else 
  if($netGenMethod == 'Optimized network based on expression scores' and $influenceMethodSelected == 'Intersection (Logical AND) approach') 
  {
    
    $query = "SELECT mirna1 AS source, mirna2 AS target, score AS type FROM mirna_opt_v2 WHERE dis_category='".$disCategorySelected."' and comment_code='intersection' ORDER BY type DESC LIMIT 500";
    $queryCSV = "SELECT mirna1 AS source, mirna2 AS target, score AS type FROM mirna_opt_v2 WHERE dis_category='".$disCategorySelected."' and comment_code='intersection' ORDER BY type DESC into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/network.csv' fields terminated by ','"; //Dummy query for test

  }

else if($netGenMethod == 'Optimized network based on expression scores' and $influenceMethodSelected == 'Cumulative Union') 
  {
    
    $query = "SELECT distinct mirna1 AS source, mirna2 AS target, score AS type FROM mirna_opt_v2 WHERE dis_category='".$disCategorySelected."' and comment_code IS NULL ORDER BY type DESC LIMIT 500";
    $queryCSV = "SELECT mirna1 AS source, mirna2 AS target, score AS type FROM mirna_opt_v2 WHERE dis_category='".$disCategorySelected."' and comment_code IS NULL ORDER BY type DESC into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/network.csv' fields terminated by ','"; //Dummy query for test

  }


  $queryResult = mysqli_query($dbConnect, $query);
  $queryResultCSV = mysqli_query($dbConnect, $queryCSV);
	 
  for ($x = 0; $x < mysqli_num_rows($queryResult); $x++) 
	  {
		 //echo "\nI am inside the loop for the -> ".$x;
         $data[] = mysqli_fetch_assoc($queryResult);
	  }

	  echo json_encode($data);
	 
 } 

else echo ("ISSET condition failed");

?>

