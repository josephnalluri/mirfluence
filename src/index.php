<?php

error_reporting(E_ALL ^ E_WARNING);

//Header files
require_once('dbAcess.php');  // Connect to Database
//$query = "SELECT d1 as disease from d1_from_consensus order by d1 asc";
$query = "SELECT distinct disease from mirna_opt_v2 where disease not like '%intersection%' order by disease asc"; // The DISEASE column has other names which don't need to be included here. Hence, the query discards them.
$queryResult = mysqli_query($dbConnect, $query) or die("Error in the query" . mysqli_error($dbConnect));

$diseaseArray = array();
for($i = 0; $i < mysqli_num_rows($queryResult); $i++)
{
  $diseaseArray[] = mysqli_fetch_assoc($queryResult);
}
$diseaseDropdown = json_encode($diseaseArray);


if(file_exists('CSV/network.csv') or file_exists('CSV/files/network.csv') or count(glob('CSV/files/*')!==0)) 
{
unlink('CSV/network.csv'); // To delete the previous network CSV file
array_map('unlink', glob("/var/www/bnet.egr.vcu.edu/public_html/mirfluence/CSV/files/*.txt"));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="description" content="information diffusion in miRNA-diffusion networks, miRNA-disease interaction network, communication in miRNA-disease networks, top miRNAs in diseases, top diseases of miRNAs, miRNA-disease regulation, miRNA analysis">
  <meta name="author" content="Biological Networks Lab, VCU">
  <title>Identifying influential miRNA targets in diseases via influence diffusion model</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
  <link rel="stylesheet" href="googleTableCss.css">
  <link rel="stylesheet" href="graphCSS.css"> 
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
  <script src="drawGraph.js"></script>
  <!-- <script src="newGraph.js"></script> -->
  <script src="http://d3js.org/d3.v3.min.js" charset="utf-8"></script>
 
 </head>
 
<body onload="fillDropdown(); singleDisease_fillDropdown();">
<div class="container">

   <!-- Modal. Recommeded to place it at the top of the DOM tree -->
   <!-- Taken from: https://myzeroorone.wordpress.com/2015/03/02/creating-simple-please-wait-dialog-with-twitter-bootstrap/--> 
   <!-- Modal Start here-->
   <div class="modal fade bs-example-modal-sm" id="myPleaseWait" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
 	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">
					<span class="glyphicon glyphicon-time">
					</span> Please Wait... loading results and visualization
				 </h5>
			</div> <!-- End div for class modal-header -->
			<div class="modal-body">
				<div class="progress">
					<div class="progress-bar progress-bar-info progress-bar-striped active" style="width: 100%"></div>
				</div> <!-- End div for class=progress  -->
			</div><!-- End div tag for class=modal-body  -->
		</div> <!-- End div tag for class=modal-content -->
	</div><!-- End div tag for class=modal-dialog -->
   </div><!-- End div tag for class=modal fade -->
   <!-- Modal ends Here -->

  <div class="jumbotron">
    <h1><i>miRfluence</i></h1><h2> ~ Identifying <b><u>miR</u></b>NAs which in<b><u>fluence</u></b> other miRNAs in diseases via influence diffusion model</h2>
  </div><!-- End div for jumbotron-->
  <div class="row">
    <div class="col-sm-10">
	
      <h4>This tool predicts influential disease-miRNAs in several diseases using an influence diffusion algorithm. The detected miRNAs have the highest coverage and impact-ability in a miRNA-miRNA network in a particular disease </h4>
	
	  <div class="alert alert-success">
         <strong>Note:</strong> <br>
		 1. Users can view miRNAs which have maximum influence in a miRNA-miRNA network for a specific disease or disease category <br>
		 2. Users can run the influence-diffusion algorithm on a <u>user specified</u> miRNA-miRNA network of a group of diseases by clicking <strong>Create your own category</strong><br>
		 3. Some queries can take upto 1 minute to load based on the selection <br>
         4. The miRNA-miRNA networks used in this tool are predicted miRNA-miRNA interactions based on [1] </br>
		 4. Upon <strong>Submit</strong> the miRNA-miRNA interactions will be displayed below in a network visualization <br>
      </div> <!-- End div for class=alert-sucess-->
  
      <!-- Implementing tab-panel navigation -->
      <ul class = "nav nav-tabs">
         <li class="active"><a href="#disease_category" data-toggle="tab">Disease Category</a></li>
         <li><a href="#individual_disease" data-toggle="tab">Individual Disease</a></li>
         <li><a href="#create_category" data-toggle="tab">Create your own category</a></li>
      </ul>

    <!-- Implementing tab panel content -->
     <div class="tab-content">

     <!-- Code for 1st tab: Disease category -->
     <div class="tab-pane active" id="disease_category">
       <h5>Please choose a disease category</h5>
		 <form id = "disease_category_form">
		  <div id = "disease_category_selectDiseaseform">
		     <select name ="disease_category_dis" id = "disease_category_selectDropdown" class="form-control"> 
               <option>Choose disease category</option>
               <option>Gastrointestinal cancers</option>
               <option>Leukemia cancers</option>
               <option>Endocrine cancers</option>
               <option>Brain systems</option>
             </select><br>
          
            <!-- Option for network generation. Option 1 is checked by default -->
            <h5>Please choose a network generation method</h5>
            <select name="network_gen_method" id="network_gen_method_dropdown" class="form-control">
              <option>All edges above 0.9 score, rescored to 0.01</option>
              <option selected="selected">Optimized network based on expression scores</option>
            </select><br>

            
            <!-- Option for infleunce diffusion methodology. Option 1 is checked by default -->
            <h5>Please choose an influence diffusion methodology </h5>
            <select name="infusion_diff_method" id="infusion_diff_method_dropdown" class="form-control">
              <option>Intersection (Logical AND) approach</option>
              <option selected="selected">Cumulative Union</option>
            </select>
          
	      </div> <!-- End div tag for id=disease_category_selectDiseaseform -->
          <br>		  
	      <hr>
		  <button onclick = "disease_category_onSubmit()" type="button" class="btn btn-success" id="btn-submit"> SUBMIT</button>  &nbsp;  &nbsp;
		  <input type="reset" class="btn btn-info" id="btn-reset" value="RESET" onClick="window.location.reload()"> </button>
	      <br><br>
	    </form>
      
       <a href="/mirfluence/CSV/network.csv" id="disease_category_downloadCSV" style="display:none;"> Download the network (CSV) </a>

      <!-- placeholder for the table -->
      <div id="topmiRtable_DC" style="display:none">
      
      </div>
      <!-- Placeholder for disease category graph -->
      <div id="disease_category_graph" tabindex="0"></div>
     </div> <!-- End div for id=disease_category -->

     <!-- Implementing tab content for 2nd tab - Individual Disease tab-->
      <div class="tab-pane" id="individual_disease">
        <h5>Please select a disease below</h5>
        <form id="single_disease_form">
         <div id= "singleDiseaseForm">
           <select name = "single_dis" id = "singlediseaseDropdown" class="form-control"> </select> <br>

            <!-- Option for network generation. Option 1 is checked by default -->
            <h5>Please choose a network generation method</h5>
            <select name="single_dis_network_gen_method" id="single_dis_network_gen_method_dropdown" class="form-control">
              <option>All edges above 0.9 score, rescored to 0.05</option>
              <option selected="selected">Optimized network based on expression scores</option>
            </select><br>

            <!-- Option for infusion diffusion methodology. Option 1 is checked by default 
            <h5>Please choose an infusion diffusion methodology </h5>
            <select name="single_dis_infusion_diff_method" id="single_dis_infusion_diff_method_dropdown" class="form-control">
              <option selected="selected">Intersection (Logical OR) approach</option>
              <option>Cumulative Union</option>
            </select> -->
        </div> <!-- End div for div id="singleDiseaseForm"-->  
        
           <br>
           <button onclick = "singleDisease_onSubmit()" type="button" class="btn btn-success" id="btn-submit">SUBMIT</button> &nbsp; &nbsp;
           <input type="reset" class="btn btn-info" id="btn-reset" value="RESET" onclick="window.location.reload()"> </button>
         </form>

     
      <a href="/mirfluence/CSV/network.csv" id="single_disease_downloadCSV" style="display:none;">Download the network (CSV)</a>

      <!-- placeholder for the table -->
      <div id="topmiRtable_SD" style="display:none">
      
      </div>
 	  <!-- Placeholder for graph --> 
	  <div id="single_disease_graph" tabindex="0"></div>
     </div> <!-- End div tag for id=individual_disease-->
   

      <!-- Code for 3rd tab: Create your own category-->
      <div class="tab-pane" id="create_category">
       <h5>Please select a disease below</h5>
		 <form id = "form">
		  <div id = "selectDiseaseform">
		   <select name ="dis" id = "selectDropdown" class="form-control">  </select> <br>
  
		  </div> <!-- End div tag for id selectDiseaseform -->
            <br>		  
			<button  type="button" onclick = "addDisease()" class="btn btn-primary" id="btn-addDisease"> Select more diseases</button>
			<br><br>
			
            <!-- Option for network generation. Option 1 is checked by default -->
            <h5>Please choose a network generation method</h5>
            <select name="category_network_gen_method" id="category_network_gen_method_dropdown" class="form-control">
              <option>All edges above 0.9 score, rescored to 0.01</option>
              <option selected="selected">Optimized network based on expression scores</option>
            </select><br>
 
            <!-- Option for infusion diffusion methodology. Option 1 is checked by default -->
            <h5>Please choose an influence diffusion methodology </h5>
            <select name="category_infusion_diff_method" id="category_infusion_diff_method_dropdown" class="form-control">
               <option selected="selected">Cumulative Union</option>
               <option>Intersection (Logical AND) approach</option>
            </select>
			<!-- 
            Maximum Score: <input type="text" id="max" name="max" size="4">  &nbsp;  &nbsp;
			Minimum Score: <input type="text" id="min" name="min" size="4"> &nbsp;  &nbsp; <i>[<b>Default</b>: Max is 1 and Min is 0.5000]</i>		
		    
            Display <input type="number" name="numberOfEdges" value="500" style="width: 4em"> results. (Edges in the visual network)
            --> 
		    <hr>
			  <button onclick = "onSubmit()" type="button" class="btn btn-success" id="btn-submit"> SUBMIT</button>  &nbsp;  &nbsp;
			  
			   <input type="reset" class="btn btn-info" id="btn-reset" value="RESET" onClick="window.location.reload()"> </button>
	        <br><br>
	     </form>
	 
      <a href="/mirfluence/CSV/network.csv" id="downloadCSV" style="display:none;">Download the network (CSV)</a> <br><br>

      <!-- placeholder for the table -->
      <div id="topmiRtable" style="display:none">
      
      </div>

 	  <!-- Placeholder for graph --> 
	  <div id="graph" tabindex="0"></div>
	  <div id = "graph-bottom"> </div>
      </div><!-- End div tag for id=create_category -->
     
    
  </div> <!-- End div tag for class=tab-content-->
 </div> <!-- End div tag for class="col-sm-10"-->
</div><!-- End div tag for class=row -->
</div><!-- End div tag for class=container -->

<!-- PHP code to POST the form and run thequery -->
<?php
//Do it later if there are 3 diseases:		
//	$dis = $_POST["dis"];
//	$min = $_POST["min"];
//	$max = $_POST["max"];
	
if (!empty($_POST["min"]))
 {
    $min = $_POST["min"];
 }
else
{
    $min = 0.5;
}
if (!empty($_POST["max"]))
{
    $max = $_POST["max"];
}
else
{
    $max = 1;
}


if (!empty($_POST["dis"]))
{
   //$dis= '"' . $_POST["dis"] . '"';
   $dis = $_POST["dis"];
}

?> 

<!-- Script to send disease names to JavaScript and populate the dropdown   -->
<script type="text/javascript">
var diseaseList = <?php echo $diseaseDropdown; ?>;
var counterID = 1;

function fillDropdown()
{
  var selectDisease = document.getElementById("selectDropdown");
  var option = document.createElement("option");
  option.textContent = "Select Disease";
  option.value = "Select Disease";
  selectDisease.appendChild(option);
  
  for(var i = 0; i<diseaseList.length; i++)
   { 
	  var disName = diseaseList[i].disease; <!-- disease is the column name derived from the query-->
	  var option = document.createElement("option");
	  option.textContent = disName;
	  option.value = disName;
	  selectDisease.appendChild(option);
    }
 }

function singleDisease_fillDropdown()
 {
   var selectDisease = document.getElementById("singlediseaseDropdown");
   var option = document.createElement("option");
   option.textContent = "Select Disease";
   option.value = "Select Disease";
   selectDisease.appendChild(option);
  
   for(var i = 0; i<diseaseList.length; i++)
    { 
	  var disName = diseaseList[i].disease; <!-- disease is the column name derived from the query-->
	  var option = document.createElement("option");
	  option.textContent = disName;
	  option.value = disName;
	  selectDisease.appendChild(option);
    }
 }
   
function addDisease() 
 {
   if(counterID < 5)
   {  
	   var lineBreak = document.createElement("br");
	   var addDisease = document.createElement("select");
	   var classAttr = document.createAttribute("class");
	   var nameAttr = document.createAttribute("name");
	   
	   classAttr.value = "form-control";
	   nameAttr.value = "dis" + counterID;
	   
	   addDisease.setAttributeNode(classAttr);
	   addDisease.setAttributeNode(nameAttr)
	   addDisease.id = "selectDropdown" + counterID;
	   addDisease.textContent = "Select Disease";
	   document.getElementById("selectDiseaseform").appendChild(addDisease);
	   document.getElementById("selectDiseaseform").appendChild(lineBreak);
	   counterID +=1;
	  	   
	   var selectDisease = document.getElementById(addDisease.id);
	   var option = document.createElement("option");
	   option.textContent = "Select Disease";
	   option.value = "Select Disease";
	   selectDisease.appendChild(option);
	  
	   for(var i = 0; i<diseaseList.length; i++)
		{ 
		  var disName = diseaseList[i].disease;
		  var option = document.createElement("option");
		  option.textContent = disName;
		  option.value = disName;
		  selectDisease.appendChild(option);
		}
	}
   else
    {	
	 var para = $("<input>", {id:"para", class: "alert alert-danger", value:"Cannot exceed 5 diseases"});
	 $("#selectDiseaseform").append(para);
	 $("#btn-addDisease").attr("disabled","disabled");
	 
	}   	
 }

//Taken from - http://stackoverflow.com/questions/154059/how-do-you-check-for-an-empty-string-in-javascript/154068
function isEmpty(str) 
{ return (!str || 0 === str.length); }

function isBlank(str)
{ return (!str || /^\s*$/.test(str)); }

</script>

<!-- <script src="http://code.jquery.com/jquery-1.11.3.js"></script> -->
<script src="https://rawgit.com/gka/d3-jetpack/master/d3-jetpack.js"></script>
 
<script type="text/javascript">
function onSubmit(){
   	var disSelected = document.getElementById("selectDropdown").value;
	var influenceMethodSelected = document.getElementById("category_infusion_diff_method_dropdown").value; 
    var netGenMethod = document.getElementById("category_network_gen_method_dropdown").value;

    //window.alert(disSelected + " " + influenceMethodSelected); 
   
   // To decide the AJAX request based on number of inputs.
	switch(counterID)
	{ 
	  case 1: var params = {'disSelected':disSelected, 'influenceMethodSelected':influenceMethodSelected, 'netGenMethod':netGenMethod,
			  'counterID':counterID};	
	          break;
			  
	  case 2: var params = {'disSelected':disSelected,
	          'disSelected2': document.getElementById("selectDropdown1").value, 'influenceMethodSelected':influenceMethodSelected,'netGenMethod':netGenMethod,
              'counterID':counterID};	
			  break;
	  case 3: var params = {'disSelected':disSelected,
	          'disSelected2': document.getElementById("selectDropdown1").value,
			  'disSelected3': document.getElementById("selectDropdown2").value, 'influenceMethodSelected':influenceMethodSelected,'netGenMethod':netGenMethod,
			  'counterID':counterID};
	          break;
	  case 4: var params = {'disSelected':disSelected,
	          'disSelected2': document.getElementById("selectDropdown1").value,
			  'disSelected3': document.getElementById("selectDropdown2").value,
			  'disSelected4': document.getElementById("selectDropdown3").value, 'influenceMethodSelected':influenceMethodSelected,'netGenMethod':netGenMethod,
			  'counterID':counterID};
	          break;
	  case 5: var params = {'disSelected':disSelected,
	          'disSelected2': document.getElementById("selectDropdown1").value,
			  'disSelected3': document.getElementById("selectDropdown2").value,
			  'disSelected4': document.getElementById("selectDropdown3").value,
			  'disSelected5': document.getElementById("selectDropdown4").value, 'influenceMethodSelected':influenceMethodSelected,'netGenMethod':netGenMethod,  
			  'counterID':counterID};
	          break;
	}	
	$("#myPleaseWait").modal("show");
	//Ajax request
	$.ajax({
	type: "POST",
   // dataType: "json",
	url: "onSubmit_v2.php",
   	data: params,
	success: function(dataReceived) {
	   $('#myPleaseWait').modal('hide');
	  if(dataReceived)
		{ 
		    $("#graph").empty(); $("#topmiRtable").empty();
            $("#downloadCSV").show();
		   // Don't know what the deal is with this
		   //console.log("data.index of NULL is - ".concat(dataReceived.indexOf("null")));
		   if (dataReceived.indexOf("null")> -1)
		   {
			 alert("No results for this selection. Please try reducing the number of diseases or widening the range of score. ");
		   }
		  else
		   {  //console.log(dataReceived);
			  createGraph(JSON.parse(dataReceived),"#graph", counterID);
              $.callTable(); // Function to generate the top miRs table           
		   }
	    }
		 else
		   {
	    alert("No results from the selected specification"); 
		   }
	},
	error: function(jqXHR, textStatus, errorThrown) 
	{
	  $('#myPleaseWait').modal('hide');
	  console.log(jqXHR.responseText);
	  console.log(errorThrown);
	 }	
});

//Function to generate the top miRs table
//------------------------------------------
$.callTable = function (){
  //Ajax request to fetch top miRs
  $.ajax({
  type: "POST",
  data: {'influenceMethodSelected':influenceMethodSelected},
  url: "topmiRtable.php",
  success: function(topMirRecieved) {
  if(topMirRecieved)
   {  
    $("#topmiRtable").show(); //Make the table visible in HTML
    //console.log(JSON.parse(topMirRecieved)); // Just for checking if the response is good
    var tableData = JSON.parse(topMirRecieved);
    var table = $.drawTable(tableData); //Passing the string to function drawTable
    $(table).appendTo("#topmiRtable"); //Positing the table HTML to its DIV tag
   }
 
},
error: function(jqXHR, textStatus, errorThrown)
 {
   console.log(jqXHR.responseText);
   console.log(errorThrown);
 }

}); //End of Ajax Request   
} //End of callTable function

// Function to draw the table: This function takes an array of strings and converts them into table.
// Taken from http://stackoverflow.com/questions/10612667/create-html-table-from-comma-separated-strings-javascript
//-------------------------------------------------------------------
$.drawTable = function (mydata) {
    var table = "<table class = \"table\"> <thead> <th> Rank </th> <th> miRNAs with largest coverage </th> </thead> <tbody>";
    
    for (var i= 0; i < mydata.length; i++)
     {
      table = table + "<tr><td>"+ (i+1) +"</td><td>"+ mydata[i] + "</td></tr>"; 
     }
    table = table + "</tbody> </table>";
    return table;

}; // End of drawTable function
} //End of javascript function onSubmit()
</script>

<!-- Script to implement disease_Category_onSubmit() -->
<script type="text/javascript">
function disease_category_onSubmit(){
  var disCategorySelected = document.getElementById("disease_category_selectDropdown").value;
  var netGenMethod = document.getElementById("network_gen_method_dropdown").value;
  var influenceMethodSelected = document.getElementById("infusion_diff_method_dropdown").value;

  // A quick way to check if the variables have captured the values properly
  // window.alert(disCategorySelected + " " + netGenMethod + " " + influenceMethodSelected); 
 
  $("#myPleaseWait").modal("show");
  // Ajax request
  $.ajax({
  type: "POST",
  //dataType: "json",
  url: "disease_category_onSubmit.php",
  data: {'disCategorySelected':disCategorySelected,
         'netGenMethod':netGenMethod,
         'influenceMethodSelected': influenceMethodSelected},
  success: function(dataReceived){
   $('#myPleaseWait').modal("hide");
   if (dataReceived){
       $("#disease_category_graph").empty();
       $("#disease_category_downloadCSV").show();
       $("#topmiRtable_DC").empty();
       if (dataReceived.indexOf("null")> -1)
           {
              //console.log(dataReceived);
              alert("No results for this selection. Please try reducing the number of diseases or widening the range of score. ");
           }
          else
           {  //console.log("Data received!!! now create graph - > ");
              //console.log(dataReceived);
              createGraph(JSON.parse(dataReceived),"#disease_category_graph");
              $.callTable_DC(); // Function to draw table for Disease Category 
           }
         }
   else
       {
         alert("No results from the selected specification"); 
       }
    },
    error: function(jqXHR, textStatus, errorThrown) 
    {
      $('#myPleaseWait').modal('hide');
      console.log(jqXHR.responseText);
      console.log(errorThrown);
    }  
}); 

$.callTable_DC = function ()
 {
   //Ajax Request to fetch top miRs
   $.ajax({
    type: "POST",
    data: {'disCategorySelected':disCategorySelected,'influenceMethodSelected': influenceMethodSelected},
    url: "topmiRtable_DC.php",
    success: function(topMirRecieved)
    {
      if(topMirRecieved)
       {
         $("#topmiRtable_DC").show(); //make the table visible
         var tableData = JSON.parse(topMirRecieved);
         var table = $.drawTable_DC(tableData);
         $(table).appendTo("#topmiRtable_DC");
       }
    },
    error: function(jqXHR, textStatus, errorThrown)
    {
      console.log(jqXHR.responseText);
      console.log(errorThrown);
    }

}); //End of Ajax request
}//End of callTable_DC function

$.drawTable_DC = function (mydata) {
    var table = "<table class = \"table\"> <thead> <th> Rank </th> <th> miRNAs with largest coverage </th> </thead> <tbody>";
    
    for (var i= 0; i < mydata.length; i++)
     {
      table = table + "<tr><td>"+ (i+1) +"</td><td>"+ mydata[i] + "</td></tr>"; 
     }
    table = table + "</tbody> </table>";
    return table;

}; // End of drawTable function
}
</script>

<!-- Script to implement individual_ -->
<!-- single disease graph ;  -->
<script type="text/javascript">
function singleDisease_onSubmit(){
 var disSelected = document.getElementById("singlediseaseDropdown").value;
 var netGenMethod = document.getElementById("single_dis_network_gen_method_dropdown").value;
 
 //window.alert(disSelected + "  " + netGenMethod);
   
  $("#myPleaseWait").modal("show");
  // Ajax request
  $.ajax({
  type: "POST",
  //dataType: "json",
  url: "single_disease_onSubmit.php",
  data: {'disSelected':disSelected,
         'netGenMethod':netGenMethod},
  success: function(dataReceived){
   $('#myPleaseWait').modal("hide");
   if (dataReceived){
       $("#single_disease_graph").empty();
       $("#single_disease_downloadCSV").show();
       $("#topmiRtable_SD").empty();
       if (dataReceived.indexOf("null")> -1)
           {
              //console.log(dataReceived);
              alert("No results for this selection. Please try reducing the number of diseases or widening the range of score. ");
           }
          else
           {  //console.log("Data received!!! now create graph - > ");
              //console.log(dataReceived);
              createGraph(JSON.parse(dataReceived),"#single_disease_graph"); 
              $.callTable_SD();              
           }
         }
   else
       {
         alert("No results from the selected specification"); 
       }
    },
    error: function(jqXHR, textStatus, errorThrown) 
    {
      $('#myPleaseWait').modal('hide');
      console.log(jqXHR.responseText);
      console.log(errorThrown);
    }  
}); // end of ajax request 

$.callTable_SD = function ()
 {
   //Ajax Request to fetch top miRs
   $.ajax({
    type: "POST",
    data: {'disSelected':disSelected},
    url: "topmiRtable_SD.php",
    success: function(topMirRecieved)
    {
      if(topMirRecieved)
       {
         $("#topmiRtable_SD").show(); //make the table visible
         var tableData = JSON.parse(topMirRecieved);
         var table = $.drawTable_SD(tableData);
         $(table).appendTo("#topmiRtable_SD");
       }
    },
    error: function(jqXHR, textStatus, errorThrown)
    {
      console.log(jqXHR.responseText);
      console.log(errorThrown);
    }

}); //End of Ajax request
}//End of callTable_DC function

$.drawTable_SD = function (mydata) {
    var table = "<table class = \"table\"> <thead> <th> Rank </th> <th> miRNAs with largest coverage </th> </thead> <tbody>";
    
    for (var i= 0; i < mydata.length; i++)
     {
      table = table + "<tr><td>"+ (i+1) +"</td><td>"+ mydata[i] + "</td></tr>"; 
     }
    table = table + "</tbody> </table>";
    return table;

}; // End of drawTable function
} // end of function
</script> <!-- End script tag for singleDisease_onSubmit() -->

<br><br><br>
<div id="footer">
 <div class="container">
  <p class="muted credit"> <b>References</b></br>[1] Nalluri, J. J. et al. <a href="http://www.nature.com/articles/srep39684" target="_blank"><i>miRsig</i>: a consensus-based network inference methodology to identify pan-cancer miRNA miRNA interaction signatures. </a><i>Sci. Rep.</i> <b>6</b>, 39684; doi: 10.1038/srep39684 (2016)</i></p>

<hr>
This tool was developed by <a href="http://bnet.egr.vcu.edu" target=_blank">Biological Networks Lab, VCU</a> 
<hr>
 </div>
</div>

</body>
</html>
