<?php

// Fetch the disease category selected, influence method from the requesting Ajax call
$disCategorySelected = $_POST["disCategorySelected"];
$influenceMethodSelected = $_POST["influenceMethodSelected"];

$mirna_array = array();                                                                                           
$lineCount = 0;                                                                                                   
                                                                                                                  
if($influenceMethodSelected == 'Cumulative Union')                                                                
 {                                                                                                                
   $topmiRlimit = 10;                                                                                             
   
   if($disCategorySelected == 'Endocrine cancers')  { $fp = fopen('php-files/EndocrineCumulative.txt','r'); }
   if($disCategorySelected == 'Gastrointestinal cancers')  { $fp = fopen('php-files/GastrointestinalCumulative.txt','r'); }
   if($disCategorySelected == 'Brain systems')  { $fp = fopen('php-files/BrainCumulative.txt','r'); }
   if($disCategorySelected == 'Leukemia cancers')  { $fp = fopen('php-files/LeukemiaCumulative.txt','r'); }
 }                                                                                                                
                                                                                                                  
else                                                                                                              
 {  
   $topmiRlimit = 5;                                                                                              
   
   if($disCategorySelected == 'Endocrine cancers')  { $fp = fopen('php-files/EndocrineIntersection.txt','r'); }
   if($disCategorySelected == 'Gastrointestinal cancers')  { $fp = fopen('php-files/GastrointestinalIntersection.txt','r'); $topmiRlimit = 3; }
   if($disCategorySelected == 'Brain systems')  { $fp = fopen('php-files/BrainIntersection.txt','r'); }
   if($disCategorySelected == 'Leukemia cancers')  { $fp = fopen('php-files/LeukemiaIntersection.txt','r'); }
 }                                                                                                                
                                                                                                                  
 while ($lineCount<$topmiRlimit)                                                                                  
   {                                                                                                              
     $line = fgets($fp, 2048); // gets the 1st line of the file                                                   
     $data = str_getcsv($line," ");  // splits the line into two entities separated by space                      
    // $mirna_name = array_search($data[0],$mirna_int_hashmap); //Searches for the miRNA name based on the number   
     array_push($mirna_array, $data[0]);                                                                       
     $lineCount = $lineCount+1;                                                                                   
   }                                                                                                              
                                                                                                                  
  echo json_encode($mirna_array); //Send it back to index.php page                                                

?>
