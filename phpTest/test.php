
<!-->HTML</-->
 
<form id="forma" method="post">
	<label>Total:</label><input type="number" name="total" required><br>
	<label>Baseline:</label><input type="number" name="baseline" min="0" max="100" required><br>
	<label>Start Date:</label> <input type="date" name="start" required><br>
	<label>End Date:</label> <input type="date" name="end" required><br>
	<label></label> <input type="submit"><br>
</form> 


<div id="result"></div>
 
<script type="text/javascript">
$(document).ready(function() {
    $('#forma').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: 'test.php',
            data: $(this).serialize(),
            success: function(response)
            {
            //console.log(JSON.stringify(response));
            document.querySelector("#result").innerHTML = JSON.stringify(response);
           }
       });
     });
});
</script>






<!-->PHP</-->

<?php

//to see how many days are there
 function getNumWeekdays($start_day, $end_day)
{
	//use time stamp
	$start_day_ts = strtotime($start_day);
	$end_day_ts = strtotime($end_day);
	$num_weekdays = 0;
	while ($start_day_ts <= $end_day_ts ) {
		if (date("N", $start_day_ts) < 6){
			++$num_weekdays; 
		}
		$start_day_ts += 86400;//add one day of seconds
	}
	return $num_weekdays;
}


//Create an array of random numbers that is num weekdays long
//make sure we have normalized the array
//returns a normalized array
function getArrayRandNumbers($num_week_days){
	$total = 0;
	$rand_num = 0;
	$weekday_array = array();
	$norm_array = array();		
	for ($i= 0 ; $i < $num_week_days ; $i++) {
		//generate a random number with 3 significant figures.  
		//If we have a long interval of weekdays, we may need higher precision here.
		$rand_num = mt_rand(1,1000);
		//add it to the total
		$total += $rand_num;
		//put the random number into an array
		array_push($weekday_array , $rand_num);
	}
	//Normalize the array.  (make the sum of elements equal to one)
	//keep three digits for acuracy
	$total = floatval($total);
	foreach ( $weekday_array as $wd ) {	
		array_push( $norm_array, floatval($wd) / $total);		
	}
	return $norm_array;
}

//main function: Take the start and end date, fill an array with the values.
function dist_amount_randomly($amount, $baseline, $start_date, $end_date){
	//use time stamp
	$start_day_ts = strtotime($start_date);
	$end_day_ts = strtotime($end_date);
	//calculate num of weekdays
	$num_weekdays = getNumWeekdays($start_date, $end_date);
	//a unidimentional normalized array of size $num_weekdays
	$norm_array = getArrayRandNumbers($num_weekdays);
	$index_of_norm_array = 0;
	$output_array = array();
	//calculate the baseline number
	//min number in given weekday
	$min_in_days = floatval($amount)*floatval($baseline)/(floatval($num_weekdays)*100.0);
	$amount_to_be_dist = floatval(100-$baseline) *floatval($amount)/100.0;
	//fill the output array
	while ($start_day_ts <= $end_day_ts ) {		
		if (date("N", $start_day_ts) < 6){
			//make a non-zero array element
			$amount_for_weekday = $norm_array[$index_of_norm_array]*$amount_to_be_dist+$min_in_days;
			//convert to a 2 decimal number
			$formatted_amount = number_format($amount_for_weekday, 2,'.','').",";
			//increment the index of the norm array	
 			$index_of_norm_array++;
		} else {
			//make a zero array element
			$formatted_amount = "0.00, " . " &nbsp; &nbsp; // &nbsp;" .date('l',$start_day_ts);	
		}
		//conver start_day_ts to a string date
		$curr_date = date('Y-m-d',$start_day_ts);
		//add it to the end of the array.
		$output_array += [$curr_date => $formatted_amount];	
		//print out the values to check the math.				
		$start_day_ts += 86400;//add one day of seconds
	}
	return $output_array;
}



// declaire.
$total = $baseline = $start = $end = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $total = test_input($_POST["total"]);
  $baseline = test_input($_POST["baseline"]);
  $start = test_input($_POST["start"]);
  $end = test_input($_POST["end"]); 
}

//some security stuff
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}



$final_output = dist_amount_randomly( $total, $baseline, $start, $end); 

$min_amount_per_weekday = number_format(floatval($total)*floatval($baseline)/floatval($weekdays*100), 2,'.','');

$sum = 0;
 
foreach ($final_output  as $key => $val){
	echo $key. "  =>  "  .$val. "  <br>";
	//$sum += $val;
}
 
   
    
 //echo json_encode(array($final_output));
