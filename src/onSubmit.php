<?php 
//Header files
require_once('dbAcess.php');  // Connect to Database

$queryResult = array(); // Variable to store the query result
$counterID = $_POST["counterID"];

if($counterID == 1)
{
	if(ISSET($_POST["disSelected"]))
	{
	 $disease = mysqli_real_escape_string($dbConnect, $_POST["disSelected"]);
	 $query = "SELECT mirna1 AS source, mirna2 AS target, score AS type	FROM mirna_opt_v2 WHERE disease='".$disease."' ORDER by type DESC limit 500";

     $queryCSV = "SELECT mirna1 AS source, mirna2 AS target, score AS type FROM mirna_opt_v2 WHERE disease='".$disease."' ORDER by type into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirid/CSV/network.csv' fields terminated by ','";
	
	 $queryResult = mysqli_query($dbConnect, $query);
     $queryResultCSV = mysqli_query($dbConnect, $queryCSV);

	 for ($x = 0; $x < mysqli_num_rows($queryResult); $x++) 
	  {
		$data[] = mysqli_fetch_assoc($queryResult);
	  }

	  echo json_encode($data);
	 }
	else 
	  echo ("ISSET condition failed!");
}

elseif($counterID == 2)
{
   if(ISSET($_POST["disSelected"]) and ISSET($_POST["disSelected2"]))
	{
	 $disease = mysqli_real_escape_string($dbConnect, $_POST["disSelected"]);
	 $disease2 = mysqli_real_escape_string($dbConnect, $_POST["disSelected2"]);
	 
	 $query = "select a.mirna1 as source, a.mirna2 as target, a.score as type from (select mirna1, mirna2, score from mirna_rescored where disease='".$disease."')a inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease2."')b using (mirna1,mirna2) order by type desc limit 500";
	 
	 $queryCSV = "select a.mirna1 as source, a.mirna2 as target, a.score as type from (select mirna1, mirna2, score from mirna_rescored where disease='".$disease."')a inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease2."')b using (mirna1,mirna2) order by type desc into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirid/CSV/network.csv' fields terminated by ','";
	 
	 $queryResult = mysqli_query($dbConnect, $query);
	 $queryResultCSV = mysqli_query($dbConnect, $queryCSV);

	 for ($x = 0; $x < mysqli_num_rows($queryResult); $x++) 
	  {
		$data[] = mysqli_fetch_assoc($queryResult);
	  }

	  echo json_encode($data);
	 }
	else 
	  echo ("ISSET condition 1 failed!");

}

elseif($counterID == 3)
{
   if(ISSET($_POST["disSelected"]) and ISSET($_POST["disSelected2"]) and ISSET($_POST["disSelected3"]) )
	{
	 $disease = mysqli_real_escape_string($dbConnect, $_POST["disSelected"]);
	 $disease2 = mysqli_real_escape_string($dbConnect, $_POST["disSelected2"]);
	 $disease3 = mysqli_real_escape_string($dbConnect, $_POST["disSelected3"]);
	
	 $query = "select a.mirna1 as source, a.mirna2 as target, a.score as type from (select mirna1, mirna2, score from mirna_rescored where disease='".$disease."')a inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease2."')b using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease3."')c using (mirna1,mirna2) order by type desc limit 2500";
	 
	 $queryCSV = "select a.mirna1 as source, a.mirna2 as target, a.score as type from (select mirna1, mirna2, score from mirna_rescored where disease='".$disease."')a inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease2."')b using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease3."')c using (mirna1,mirna2) order by type desc into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirid/CSV/network.csv' fields terminated by ','";
	 
	 $queryResult = mysqli_query($dbConnect, $query);
	 $queryResultCSV = mysqli_query($dbConnect, $queryCSV);

	 for ($x = 0; $x < mysqli_num_rows($queryResult); $x++) 
	  {
		$data[] = mysqli_fetch_assoc($queryResult);
	  }

	  echo json_encode($data);
	 }
	else 
	  echo ("ISSET condition 2 failed!");

}
elseif($counterID == 4)
{
   if(ISSET($_POST["disSelected"]) and ISSET($_POST["disSelected2"]) and ISSET($_POST["disSelected3"]) and ISSET($_POST["disSelected4"]))
	{
	 $disease = mysqli_real_escape_string($dbConnect, $_POST["disSelected"]);
	 $disease2 = mysqli_real_escape_string($dbConnect, $_POST["disSelected2"]);
	 $disease3 = mysqli_real_escape_string($dbConnect, $_POST["disSelected3"]);
	 $disease4 = mysqli_real_escape_string($dbConnect, $_POST["disSelected4"]);
			
	$query = "select a.mirna1 as source, a.mirna2 as target, a.score as type from (select mirna1, mirna2, score from mirna_rescored where disease='".$disease."')a inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease2."')b using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease3."')c using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease4."')d using (mirna1,mirna2) order by type desc";

    $queryCSV = "select a.mirna1 as source, a.mirna2 as target, a.score as type from (select mirna1, mirna2, score from mirna_rescored where disease='".$disease."')a inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease2."')b using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease3."')c using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease4."')d using (mirna1,mirna2) order by type desc into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirid/CSV/network.csv' fields terminated by ','";	

	$queryResult = mysqli_query($dbConnect, $query);
    $queryResultCSV = mysqli_query($dbConnect, $queryCSV);
	
	 for ($x = 0; $x < mysqli_num_rows($queryResult); $x++) 
	  {
		$data[] = mysqli_fetch_assoc($queryResult);
	  }

	  echo json_encode($data);
	 }
	else 
	  echo ("ISSET condition 3 failed!");

}

elseif($counterID == 5)
{
   if(ISSET($_POST["disSelected"]) and ISSET($_POST["disSelected2"]) and ISSET($_POST["disSelected3"]) and ISSET($_POST["disSelected4"]) and ISSET($_POST["disSelected5"]))
	{
	 $disease = mysqli_real_escape_string($dbConnect, $_POST["disSelected"]);
	 $disease2 = mysqli_real_escape_string($dbConnect, $_POST["disSelected2"]);
	 $disease3 = mysqli_real_escape_string($dbConnect, $_POST["disSelected3"]);
	 $disease4 = mysqli_real_escape_string($dbConnect, $_POST["disSelected4"]);
	 $disease5 = mysqli_real_escape_string($dbConnect, $_POST["disSelected5"]);
	
	 $query = "select a.mirna1 as source, a.mirna2 as target, a.score as type from (select mirna1, mirna2, score from mirna_rescored where disease='".$disease."')a inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease2."')b using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease3."')c using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease4."')d using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease5."')e using (mirna1,mirna2) order by type desc";	

	 $queryCSV = "select a.mirna1 as source, a.mirna2 as target, a.score as type from (select mirna1, mirna2, score from mirna_rescored where disease='".$disease."')a inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease2."')b using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease3."')c using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease4."')d using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_rescored where disease='".$disease5."')e using (mirna1,mirna2) order by type desc into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirid/CSV/network.csv' fields terminated by ','";
	 
	 $queryResult = mysqli_query($dbConnect, $query);
	 $queryResultCSV = mysqli_query($dbConnect, $queryCSV);

	 for ($x = 0; $x < mysqli_num_rows($queryResult); $x++) 
	  {
		$data[] = mysqli_fetch_assoc($queryResult);
	  }

	  echo json_encode($data);
	 }
	else 
	  echo ("ISSET condition 3 failed!");

}
else echo ("All four conditions failed! Help!");


?>

