<?php 
//Filename: onSubmit_v2.php
require_once('dbAcess.php');  // Connect to Database

error_reporting(E_ALL ^ E_WARNING);

//Deleting the files if previously existed
if(file_exists('ic_code/intersectionOutput.txt')) { unlink('ic_code/intersectionOutput.txt'); }//Delete it  
if(file_exists('coverage_inference.txt')) { unlink('coverage_inference.txt'); }//Delete it 


$queryResult = array(); // Variable to store the query result
$counterID = $_POST["counterID"];
$influenceMethodSelected = $_POST["influenceMethodSelected"];
$netGenMethod = $_POST["netGenMethod"];

if($counterID == 1)
{
	if(ISSET($_POST["disSelected"]))
	{
	 $disease = mysqli_real_escape_string($dbConnect, $_POST["disSelected"]);
	 $query = "SELECT mirna1 AS source, mirna2 AS target, score AS type	FROM mirna_opt_v2 WHERE disease='".$disease."' ORDER by type DESC limit 500";

     $queryCSV = "SELECT mirna1 AS source, mirna2 AS target, score AS type FROM mirna_opt_v2 WHERE disease='".$disease."' ORDER by type into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/network.csv' fields terminated by ','";
	
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
	 $filenameString ="'$disease.txt','$disease2.txt'";
	
     if($influenceMethodSelected == 'Intersection (Logical AND) approach')
     {
       // Query for visualization for intersection approach 
       $query = "select a.mirna1 as source, a.mirna2 as target, a.score as type from (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease."')a inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease2."')b using (mirna1,mirna2) order by type desc limit 500";
     	 
      // Query for generating network.csv file
	 $queryGraph = "select a.mirna1 as source, a.mirna2 as target, a.score as type from (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease."')a inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease2."')b using (mirna1,mirna2) order by type desc into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/network.csv' fields terminated by ','";
    }
   else if($influenceMethodSelected == 'Cumulative Union') 
    {
      // Query for visualization for CUMULATIVE UNION approach 
      $query = "select distinct mirna1 as source, mirna2 as target, score as type from mirna_opt_v2 where disease IN ('".$disease."','".$disease2."') order by type desc limit 500";
     	 
      // Query for generating network.csv file
	 $queryGraph = "select distinct mirna1 as source, mirna2 as target, score as type from mirna_opt_v2 where disease IN ('".$disease."','".$disease2."') order by type desc into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/network.csv' fields terminated by ','";
    }


	 // Query for generating individual disease files for MATLAB code RankFinder.m/ intersection.m
     $queryCSV = "select m1_id, m2_id, score from mirna_opt_v2 where disease='".$disease."' into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/files/".$disease.".txt'";
     $queryCSV2= "select m1_id, m2_id, score from mirna_opt_v2 where disease='".$disease2."' into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/files/".$disease2.".txt'";
	 
  	 $queryResult = mysqli_query($dbConnect, $query); // Query to display the graph. Regardless of Influence Diffusion methodology
     $queryGraphResult = mysqli_query($dbConnect, $queryGraph); //Query to write the graph to network.csv
	 
     $queryResultCSV = mysqli_query($dbConnect, $queryCSV);  // Execute 1st query to generate disease network file
     $queryResultCSV = mysqli_query($dbConnect, $queryCSV2); //Execute 2nd query to generate disease network file

	 for ($x = 0; $x < mysqli_num_rows($queryResult); $x++) 
	  {
		$data[] = mysqli_fetch_assoc($queryResult);
	  }

	  echo json_encode($data);
      if($influenceMethodSelected == 'Cumulative Union')
        {
          shell_exec("matlab -nojvm -nodisplay -r \"try Ranking_finder({'CSV/files/".$disease.".txt','CSV/files/".$disease2.".txt'}); catch; end; quit\""); //It works!
       
          if(file_exists('CSV/files/RankFinderResult.txt')){ }
          else echo ("RankFinderResult.txt not found. MATLAB result not generated");
        }
      else // I.e. its an 'Intersection (Logical AND) approach'
        {
          shell_exec("matlab -nojvm -nodisplay -r \"try intersection({'CSV/files/".$disease.".txt','CSV/files/".$disease2.".txt'}); catch; end; quit\""); //It works!

          if(file_exists('ic_code/intersectionOutput.txt')){ }
          else echo ("intersectionOutput.txt not found. MATLAB result not generated");
          
          exec("ic_code/InfluenceModels -c ic_code/input.txt"); // This will run the IC code and output a file 'coverage_inference.txt'

          if(file_exists('coverage_inference.txt'))
           {
              $COV_array = array(); // declare associative array                                                                                    
              $lineCount = 0;                                                                                           
                                                                                                          
              if($fp = fopen('coverage_inference.txt','r'))                                                             
                {                                                                                                        
                  while(!feof($fp))                                                                                      
                   {                                                                                                     
                      $line = fgets($fp, 2048); //gets the 1st line                                                       
                      $data = str_getcsv($line,"\t");                                                                     
                      if($data[0]!== NULL)                                                                                
                        {                                                                                                   
                          //echo "<br /> data[0] is: ".$data[0]." and data[1] is: ".$data[1];                               
                          $COV_array[$data[0]]=$data[1]; // Store the contents in the asociative array                                                         
                        }                                                                                                   
                   }                                                                                                     
                  fclose($fp);                                                                                          
                                                                                                          
                  //Sorts the array based on value                                                                      
                  arsort($COV_array);                                                                                   
                                                                                                          
                  $fp = fopen('php-files/intersectionResult.txt','w') or die ("Cannot open intersectionResult.txt");    
                  $topmiRcount = 0; //counter to take only top 5 miRNAs                                                 
                                                                                                          
                  foreach ($COV_array as $key => $value)                                                               
                   {                                                                                                    
                      if($topmiRcount<5)                                                                                 
                       {                                                                                                 
                         fwrite($fp, $key." ".$value."\n"); // Write array contents in a tab-delimited text file         
                         $topmiRcount = $topmiRcount + 1;                                                                
                       }                                                                                                 
                   }                                                                                                    
                  fclose($fp);                                                                                          
                                                                                                          
                } //End of IF condition                                                                                  
 
           } 
          else echo ("coverage_inference.txt file not found.");
          
        }
    
	}
	else 
	  echo ("ISSET condition 2 failed!");

}

elseif($counterID == 3)
{
   if(ISSET($_POST["disSelected"]) and ISSET($_POST["disSelected2"]) and ISSET($_POST["disSelected3"]) )
   {
	 $disease = mysqli_real_escape_string($dbConnect, $_POST["disSelected"]);
	 $disease2 = mysqli_real_escape_string($dbConnect, $_POST["disSelected2"]);
	 $disease3 = mysqli_real_escape_string($dbConnect, $_POST["disSelected3"]);
     $filenameString ="'$disease.txt','$disease2.txt','$disease3.txt'";
	
     if($influenceMethodSelected == 'Intersection (Logical AND) approach')
     {
       // Query for visualization for intersection approach 
       $query = "select a.mirna1 as source, a.mirna2 as target, a.score as type from (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease."')a inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease2."')b using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease3."')c using (mirna1, mirna2) order by type desc limit 500";
       // Query for generating network.csv file
	   $queryGraph = "select a.mirna1 as source, a.mirna2 as target, a.score as type from (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease."')a inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease2."')b using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease3."')c using (mirna1, mirna2) order by type desc into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/network.csv' fields terminated by ','";
     }
   else if($influenceMethodSelected == 'Cumulative Union') 
     {
      // Query for visualization for CUMULATIVE UNION approach 
      $query = "select distinct mirna1 as source, mirna2 as target, score as type from mirna_opt_v2 where disease IN ('".$disease."','".$disease2."','".$disease3."') order by type desc limit 500";
     	 
      // Query for generating network.csv file
	 $queryGraph = "select distinct mirna1 as source, mirna2 as target, score as type from mirna_opt_v2 where disease IN ('".$disease."','".$disease2."','".$disease3."') order by type desc into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/network.csv' fields terminated by ','";
     }
	 // Query for generating individual disease files for MATLAB code RankFinder.m
     $queryCSV = "select m1_id, m2_id, score from mirna_opt_v2 where disease='".$disease."' into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/files/".$disease.".txt'";
     $queryCSV2= "select m1_id, m2_id, score from mirna_opt_v2 where disease='".$disease2."' into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/files/".$disease2.".txt'";
	 $queryCSV3= "select m1_id, m2_id, score from mirna_opt_v2 where disease='".$disease3."' into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/files/".$disease3.".txt'";
  	 
     $queryResult = mysqli_query($dbConnect, $query); // Query to display the graph. Regardless of Influence Diffusion methodology
     $queryGraphResult = mysqli_query($dbConnect, $queryGraph); //Query to write the graph to network.csv
	 
     $queryResultCSV = mysqli_query($dbConnect, $queryCSV);  // Execute 1st query to generate disease network file
     $queryResultCSV = mysqli_query($dbConnect, $queryCSV2); //Execute 2nd query to generate disease network file
     $queryResultCSV = mysqli_query($dbConnect, $queryCSV3); //Execute 3nd query to generate disease network file
	 
    for ($x = 0; $x < mysqli_num_rows($queryResult); $x++) 
	  {
		$data[] = mysqli_fetch_assoc($queryResult);
	  }
	  echo json_encode($data);    
     
      if($influenceMethodSelected == 'Cumulative Union')
        {
         shell_exec("matlab -nojvm -nodisplay -r \"try Ranking_finder({'CSV/files/".$disease.".txt','CSV/files/".$disease2.".txt','CSV/files/".$disease3.".txt'}); catch; end; quit\""); //It works!
       
          if(file_exists('CSV/files/RankFinderResult.txt')){ }
          else echo ("RankFinderResult.txt not found. MATLAB result not generated");
        }
      else // I.e. its an 'Intersection (Logical AND) approach'
        {
         shell_exec("matlab -nojvm -nodisplay -r \"try intersection({'CSV/files/".$disease.".txt','CSV/files/".$disease2.".txt','CSV/files/".$disease3.".txt'}); catch; end; quit\""); //It works!

          if(file_exists('ic_code/intersectionOutput.txt')){ }
          else echo ("intersectionOutput.txt not found. MATLAB result not generated");
          
          exec("ic_code/InfluenceModels -c ic_code/input.txt"); // This will run the IC code and output a file 'coverage_inference.txt'

          if(file_exists('coverage_inference.txt'))
           {
              $COV_array = array(); // declare associative array                                                                                    
              $lineCount = 0;                                                                                           
                                                                                                          
              if($fp = fopen('coverage_inference.txt','r'))                                                             
                {                                                                                                        
                  while(!feof($fp))                                                                                      
                   {                                                                                                     
                      $line = fgets($fp, 2048); //gets the 1st line                                                       
                      $data = str_getcsv($line,"\t");                                                                     
                      if($data[0]!== NULL)                                                                                
                        {                                                                                                   
                          //echo "<br /> data[0] is: ".$data[0]." and data[1] is: ".$data[1];                               
                          $COV_array[$data[0]]=$data[1]; // Store the contents in the asociative array                                                         
                        }                                                                                                   
                   }                                                                                                     
                  fclose($fp);                                                                                          
                                                                                                          
                  //Sorts the array based on value                                                                      
                  arsort($COV_array);                                                                                   
                                                                                                          
                  $fp = fopen('php-files/intersectionResult.txt','w') or die ("Cannot open intersectionResult.txt");    
                  $topmiRcount = 0; //counter to take only top 5 miRNAs                                                 
                                                                                                          
                  foreach ($COV_array as $key => $value)                                                               
                   {                                                                                                    
                      if($topmiRcount<5)                                                                                 
                       {                                                                                                 
                         fwrite($fp, $key." ".$value."\n"); // Write array contents in a tab-delimited text file         
                         $topmiRcount = $topmiRcount + 1;                                                                
                       }                                                                                                 
                   }                                                                                                    
                  fclose($fp);                                                                                          
                                                                                                          
                } //End of IF condition                                                                                  
 
           } 
          else echo ("coverage_inference.txt file not found.");
          
        }     
   }
	else 
	  echo ("ISSET condition 3 failed!");
}
elseif($counterID == 4)
{
   if(ISSET($_POST["disSelected"]) and ISSET($_POST["disSelected2"]) and ISSET($_POST["disSelected3"]) and ISSET($_POST["disSelected4"]))
	{
	 $disease = mysqli_real_escape_string($dbConnect, $_POST["disSelected"]);
	 $disease2 = mysqli_real_escape_string($dbConnect, $_POST["disSelected2"]);
	 $disease3 = mysqli_real_escape_string($dbConnect, $_POST["disSelected3"]);
	 $disease4 = mysqli_real_escape_string($dbConnect, $_POST["disSelected4"]);
     $filenameString="'$disease.txt','$disease2.txt','$disease3.txt','$disease4.txt'";

     if($influenceMethodSelected == 'Intersection (Logical AND) approach')
     {
       // Query for visualization for intersection approach 
       $query = "select a.mirna1 as source, a.mirna2 as target, a.score as type from (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease."')a inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease2."')b using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease3."')c using (mirna1, mirna2) inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease4."')d using (mirna1, mirna2) order by type desc limit 500";
       // Query for generating network.csv file
	   $queryGraph = "select a.mirna1 as source, a.mirna2 as target, a.score as type from (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease."')a inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease2."')b using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease3."')c using (mirna1, mirna2) inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease4."')d using (mirna1, mirna2) order by type desc into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/network.csv' fields terminated by ','";
     }
   else if($influenceMethodSelected == 'Cumulative Union') 
    {
      // Query for visualization for CUMULATIVE UNION approach 
      $query = "select distinct mirna1 as source, mirna2 as target, score as type from mirna_opt_v2 where disease IN ('".$disease."','".$disease2."','".$disease3."','".$disease4."') order by type desc limit 500";
     	 
      // Query for generating network.csv file
	 $queryGraph = "select distinct mirna1 as source, mirna2 as target, score as type from mirna_opt_v2 where disease IN ('".$disease."','".$disease2."','".$disease3."','".$disease4."') order by type desc into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/network.csv' fields terminated by ','";
    }

	 // Query for generating individual disease files for MATLAB code RankFinder.m
     $queryCSV = "select m1_id, m2_id, score from mirna_opt_v2 where disease='".$disease."' into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/files/".$disease.".txt'";
     $queryCSV2= "select m1_id, m2_id, score from mirna_opt_v2 where disease='".$disease2."' into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/files/".$disease2.".txt'";
	 $queryCSV3= "select m1_id, m2_id, score from mirna_opt_v2 where disease='".$disease3."' into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/files/".$disease3.".txt'";
  	 $queryCSV4= "select m1_id, m2_id, score from mirna_opt_v2 where disease='".$disease4."' into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/files/".$disease4.".txt'";
	
	 $queryResult = mysqli_query($dbConnect, $query);
     $queryGraphResult = mysqli_query($dbConnect, $queryGraph);
    
     $queryResultCSV = mysqli_query($dbConnect, $queryCSV);
     $queryResultCSV = mysqli_query($dbConnect, $queryCSV2);
     $queryResultCSV = mysqli_query($dbConnect, $queryCSV3);
     $queryResultCSV = mysqli_query($dbConnect, $queryCSV4);

	 for ($x = 0; $x < mysqli_num_rows($queryResult); $x++) 
	  {
		$data[] = mysqli_fetch_assoc($queryResult);
	  }

	  echo json_encode($data);

      if($influenceMethodSelected == 'Cumulative Union')
        {
         shell_exec("matlab -nojvm -nodisplay -r \"try Ranking_finder({'CSV/files/".$disease.".txt','CSV/files/".$disease2.".txt','CSV/files/".$disease3.".txt','CSV/files/".$disease4.".txt'}); catch; end; quit\""); //It works!
       
          if(file_exists('CSV/files/RankFinderResult.txt')){ }
          else echo ("RankFinderResult.txt not found. MATLAB result not generated");
        }
      else // I.e. its an 'Intersection (Logical AND) approach'
        {
          shell_exec("matlab -nojvm -nodisplay -r \"try intersection({'CSV/files/".$disease.".txt','CSV/files/".$disease2.".txt','CSV/files/".$disease3.".txt','CSV/files/".$disease4.".txt'}); catch; end; quit\""); //It works!

          if(file_exists('ic_code/intersectionOutput.txt')){ }
          else echo ("intersectionOutput.txt not found. MATLAB result not generated");
          
          exec("ic_code/InfluenceModels -c ic_code/input.txt"); // This will run the IC code and output a file 'coverage_inference.txt'

          if(file_exists('coverage_inference.txt'))
           {
              $COV_array = array(); // declare associative array                                                                                    
              $lineCount = 0;                                                                                           
                                                                                                          
              if($fp = fopen('coverage_inference.txt','r'))                                                             
                {                                                                                                        
                  while(!feof($fp))                                                                                      
                   {                                                                                                     
                      $line = fgets($fp, 2048); //gets the 1st line                                                       
                      $data = str_getcsv($line,"\t");                                                                     
                      if($data[0]!== NULL)                                                                                
                        {                                                                                                   
                          //echo "<br /> data[0] is: ".$data[0]." and data[1] is: ".$data[1];                               
                          $COV_array[$data[0]]=$data[1]; // Store the contents in the asociative array                                                         
                        }                                                                                                   
                   }                                                                                                     
                  fclose($fp);                                                                                          
                                                                                                          
                  //Sorts the array based on value                                                                      
                  arsort($COV_array);                                                                                   
                                                                                                          
                  $fp = fopen('php-files/intersectionResult.txt','w') or die ("Cannot open intersectionResult.txt");    
                  $topmiRcount = 0; //counter to take only top 5 miRNAs                                                 
                                                                                                          
                  foreach ($COV_array as $key => $value)                                                               
                   {                                                                                                    
                      if($topmiRcount<5)                                                                                 
                       {                                                                                                 
                         fwrite($fp, $key." ".$value."\n"); // Write array contents in a tab-delimited text file         
                         $topmiRcount = $topmiRcount + 1;                                                                
                       }                                                                                                 
                   }                                                                                                    
                  fclose($fp);                                                                                          
                                                                                                          
                } //End of IF condition                                                                                  
 
           } 
          else echo ("coverage_inference.txt file not found.");
          
        }

	 }
	else 
	  echo ("ISSET condition 4 failed!");

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
     $filenameString="'$disease.txt','$disease2.txt','$disease3.txt','$disease4.txt','$disease5.txt'";
	
     if($influenceMethodSelected == 'Intersection (Logical AND) approach')
     {
       // Query for visualization for intersection approach 
       $query = "select a.mirna1 as source, a.mirna2 as target, a.score as type from (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease."')a inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease2."')b using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease3."')c using (mirna1, mirna2) inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease4."')d using (mirna1, mirna2) inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease5."')e using (mirna1, mirna2) order by type desc limit 500";
       // Query for generating network.csv file
	   $queryGraph = "select a.mirna1 as source, a.mirna2 as target, a.score as type from (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease."')a inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease2."')b using (mirna1,mirna2) inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease3."')c using (mirna1, mirna2) inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease4."')d using (mirna1, mirna2) inner join (select mirna1, mirna2, score from mirna_opt_v2 where disease='".$disease5."')e using (mirna1, mirna2) order by type desc into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/network.csv' fields terminated by ','";
     }
   else if($influenceMethodSelected == 'Cumulative Union') 
    {
      // Query for visualization for CUMULATIVE UNION approach 
      $query = "select distinct mirna1 as source, mirna2 as target, score as type from mirna_opt_v2 where disease IN ('".$disease."','".$disease2."','".$disease3."','".$disease4."','".$disease5."') order by type desc limit 500";
     	 
      // Query for generating network.csv file
	 $queryGraph = "select distinct mirna1 as source, mirna2 as target, score as type from mirna_opt_v2 where disease IN ('".$disease."','".$disease2."','".$disease3."','".$disease4."','".$disease5."') order by type desc into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/network.csv' fields terminated by ','";
    }

	 // Query for generating individual disease files for MATLAB code RankFinder.m
     $queryCSV = "select m1_id, m2_id, score from mirna_opt_v2 where disease='".$disease."' into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/files/".$disease.".txt'";
     $queryCSV2= "select m1_id, m2_id, score from mirna_opt_v2 where disease='".$disease2."' into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/files/".$disease2.".txt'";
	 $queryCSV3= "select m1_id, m2_id, score from mirna_opt_v2 where disease='".$disease3."' into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/files/".$disease3.".txt'";
  	 $queryCSV4= "select m1_id, m2_id, score from mirna_opt_v2 where disease='".$disease4."' into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/files/".$disease4.".txt'";
     $queryCSV5= "select m1_id, m2_id, score from mirna_opt_v2 where disease='".$disease5."' into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/files/".$disease5.".txt'";
	 
     $queryResult = mysqli_query($dbConnect, $query);
     $queryGraphResult = mysqli_query($dbConnect, $queryGraph);

	 $queryResultCSV = mysqli_query($dbConnect, $queryCSV);
	 $queryResultCSV = mysqli_query($dbConnect, $queryCSV2);
	 $queryResultCSV = mysqli_query($dbConnect, $queryCSV3);
	 $queryResultCSV = mysqli_query($dbConnect, $queryCSV4);
	 $queryResultCSV = mysqli_query($dbConnect, $queryCSV5);
	 
     for ($x = 0; $x < mysqli_num_rows($queryResult); $x++) 
	  {
		$data[] = mysqli_fetch_assoc($queryResult);
	  }

	  echo json_encode($data);
      //shell_exec("matlab -nojvm -nodisplay -r \"try Ranking_finder({'CSV/files/".$disease.".txt','CSV/files/".$disease2.".txt','CSV/files/".$disease3.".txt','CSV/files/".$disease4.".txt','CSV/files/".$disease5.".txt'}); catch; end; quit\""); //It works!       
      //if(file_exists('CSV/files/RankFinderResult.txt')){ }
      //else echo ("RankFinderResult.txt not found. MATLAB result not generated");      
     
      if($influenceMethodSelected == 'Cumulative Union')
        {
         shell_exec("matlab -nojvm -nodisplay -r \"try Ranking_finder({'CSV/files/".$disease.".txt','CSV/files/".$disease2.".txt','CSV/files/".$disease3.".txt','CSV/files/".$disease4.".txt','CSV/files/".$disease5.".txt'}); catch; end; quit\""); //It works!
       
          if(file_exists('CSV/files/RankFinderResult.txt')){ }
          else echo ("RankFinderResult.txt not found. MATLAB result not generated");
        }
      else // I.e. its an 'Intersection (Logical AND) approach'
        {
          shell_exec("matlab -nojvm -nodisplay -r \"try intersection({'CSV/files/".$disease.".txt','CSV/files/".$disease2.".txt','CSV/files/".$disease3.".txt','CSV/files/".$disease4.".txt','CSV/files/".$disease5.".txt'}); catch; end; quit\""); //It works!

          if(file_exists('ic_code/intersectionOutput.txt')){ }
          else echo ("intersectionOutput.txt not found. MATLAB result not generated");
          
          exec("ic_code/InfluenceModels -c ic_code/input.txt"); // This will run the IC code and output a file 'coverage_inference.txt'

          if(file_exists('coverage_inference.txt'))
           {
              $COV_array = array(); // declare associative array                                                                                    
              $lineCount = 0;                                                                                           
                                                                                                          
              if($fp = fopen('coverage_inference.txt','r'))                                                             
                {                                                                                                        
                  while(!feof($fp))                                                                                      
                   {                                                                                                     
                      $line = fgets($fp, 2048); //gets the 1st line                                                       
                      $data = str_getcsv($line,"\t");                                                                     
                      if($data[0]!== NULL)                                                                                
                        {                                                                                                   
                          //echo "<br /> data[0] is: ".$data[0]." and data[1] is: ".$data[1];                               
                          $COV_array[$data[0]]=$data[1]; // Store the contents in the asociative array                                                         
                        }                                                                                                   
                   }                                                                                                     
                  fclose($fp);                                                                                          
                                                                                                          
                  //Sorts the array based on value                                                                      
                  arsort($COV_array);                                                                                   
                                                                                                          
                  $fp = fopen('php-files/intersectionResult.txt','w') or die ("Cannot open intersectionResult.txt");    
                  $topmiRcount = 0; //counter to take only top 5 miRNAs                                                 
                                                                                                          
                  foreach ($COV_array as $key => $value)                                                               
                   {                                                                                                    
                      if($topmiRcount<5)                                                                                 
                       {                                                                                                 
                         fwrite($fp, $key." ".$value."\n"); // Write array contents in a tab-delimited text file         
                         $topmiRcount = $topmiRcount + 1;                                                                
                       }                                                                                                 
                   }                                                                                                    
                  fclose($fp);                                                                                          
                                                                                                          
                } //End of IF condition                                                                                  
 
           } 
          else echo ("coverage_inference.txt file not found.");
          
        }


	 }
	else 
	  echo ("ISSET condition 5 failed!");

}
else echo ("All four conditions failed! Help!");


?>

