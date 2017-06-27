<?php
//Filename: single_disease_onSubmit.php
  
require_once('dbAcess.php');

if(file_exists('php-files/intersectionResult.txt')) { unlink('php-files/intersectionResult.txt'); }   
if(file_exists('coverage_inference.txt')) { unlink('coverage_inference.txt'); }//Delete it

$disSelected = mysqli_real_escape_string($dbConnect, $_POST["disSelected"]);
$netGenMethod = mysqli_real_escape_string($dbConnect, $_POST["netGenMethod"]);

$queryResult = array();

if(ISSET($_POST["disSelected"]) and ISSET($_POST["netGenMethod"])) 
 {
  if($netGenMethod == 'Optimized network based on expression scores')
    {
      $query = "SELECT mirna1 AS source, mirna2 AS target, score AS type FROM mirna_opt_v2 WHERE disease='".$disSelected."' ORDER BY type DESC LIMIT 500";
      $queryGraph = "SELECT mirna1 AS source, mirna2 AS target, score AS type FROM mirna_opt_v2 WHERE disease='".$disSelected."' ORDER BY type DESC into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/network.csv' fields terminated by ','";
      $queryCSV = "select m1_id, m2_id, score from mirna_opt_v2 where disease='".$disSelected."' into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/files/intersectionOutput.txt'";
    } //Ending if.

else if($netGenMethod == 'All edges above 0.9 score, rescored to 0.05')
    {
      $query = "SELECT mirna1 AS source, mirna2 AS target, rescored AS type FROM mirna_opt_v2 WHERE disease='".$disSelected."' ORDER BY type DESC LIMIT 500";
      $queryGraph = "SELECT mirna1 AS source, mirna2 AS target, rescored AS type FROM mirna_opt_v2 WHERE disease='".$disSelected."' ORDER BY type DESC into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/network.csv' fields terminated by ','";
      $queryCSV = "select m1_id, m2_id, rescored from mirna_opt_v2 where disease='".$disSelected."' into outfile '/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/files/intersectionOutput.txt'";
    } //Ending if.
  
  $queryResult = mysqli_query($dbConnect, $query); // execute query to generate results
    $queryResultGraph = mysqli_query($dbConnect, $queryGraph); // execute query to save to network.csv
    $queryResultCSV = mysqli_query($dbConnect, $queryCSV); // execute query to save to network.csv

    for ($x = 0; $x < mysqli_num_rows($queryResult); $x++)                                                                                                                                                             {
         $data[] = mysqli_fetch_assoc($queryResult);
     }
    
    echo json_encode($data); // Send results back to index.php
     
    exec("ic_code/InfluenceModels -c ic_code/input_SD.txt"); // This will run the IC code and output a file 'coverage_inference.txt'
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
                      if($topmiRcount<10)
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

else echo("ISSET condition failed");   

?>

