<?php
// This following execution command is only trial
//$output = shell_exec("ic_code/InfluenceModels -c ic_code/input.txt");
//echo $output;

$COV_array = array();
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
        $COV_array[$data[0]]=$data[1];
      }
    }
    fclose($fp);  
   
    //Sorts the array based on value
    arsort($COV_array);
   
    $fp = fopen('php-files/intersectionResult.txt','w') or die ("Cannot open intersectionResult.txt"); 
      
     foreach ($COV_array as $key => $value)
     {
      fwrite($fp, $key." ".$value."\n"); // Write array contents in a tab-delimited text file
    }
    fclose($fp); 
      
 } //End of IF condition

?>
