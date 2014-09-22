<?php

$which_table = $_POST['whichTable'];
$which_db = $_POST['whichDB'];

// check to see if the table is already in the table_info table
mysql_connect("localhost","root","") or die(mysql_error());
$alreadyExists = mysql_query("SELECT COUNT(*) as thisCount FROM `coursera_to_csv`.`table_info` WHERE `db_name` = '" . $which_db . "' && `table_name` = '" . $which_table . "'") or die(mysql_error());
while($existenceCount = mysql_fetch_array($alreadyExists)){
	$existence_count = $existenceCount['thisCount'];
}

mysql_connect("localhost","root","") or die(mysql_error());
$getCount = mysql_query("SELECT COUNT(*) as countOfRows FROM `$which_db`.`$which_table`") or die(mysql_error());
while($thisCount = mysql_fetch_array($getCount)){
	$row_count = $thisCount['countOfRows'];
}
	
// if not, add it to that table
if ($existence_count == 0){
	
	mysql_connect("localhost","root","") or die(mysql_error());
	$getColumns = mysql_query("SELECT `COLUMN_NAME`, `COLUMN_TYPE` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = '" . $which_db . "' && `TABLE_NAME` = '" . $which_table . "'") or die(mysql_error());
	while($theseColumns = mysql_fetch_array($getColumns)){
		$these_column_names[] = $theseColumns['COLUMN_NAME'];
		$these_column_types[] = $theseColumns['COLUMN_TYPE'];
	}

	for ($cI=0; $cI<count($these_column_names); $cI++){
		$this_column = $these_column_names[$cI];
		mysql_connect("localhost","root","") or die(mysql_error());
		$getExamples = mysql_query("SELECT DISTINCT `$this_column` FROM `$which_db`.`$which_table` LIMIT 3") or die(mysql_error());
		while($theseExamples = mysql_fetch_array($getExamples)){
			$these_examples[] = $theseExamples[$this_column];
		}
		if(count($these_examples)<3){
			if(count($these_examples)<2){
				$these_examples[] = "";
			}
			$these_examples[] = "";
		}
		
		mysql_connect("localhost","root","") or die(mysql_error());
		$getCount = mysql_query("SELECT COUNT(DISTINCT `$this_column`) as distCount FROM `$which_db`.`$which_table`") or die(mysql_error());
		while($thisCount = mysql_fetch_array($getCount)){
			$distinct_count = $thisCount['distCount'];
		}
		
		mysql_connect("localhost","root","") or die(mysql_error());
		mysql_query("INSERT INTO `coursera_to_csv`.`table_info` (`db_name`, `table_name`, `column_name`, `how_many_distinct`, `column_type`, `example_1`, `example_2`, `example_3`) VALUES ('" . $which_db . "', '" . $which_table . "', '" . $these_column_names[$cI] . "', '" . $distinct_count . "', '" . mysql_real_escape_string($these_column_types[$cI]) . "', '" . mysql_real_escape_string($these_examples[0]) . "', '" . mysql_real_escape_string($these_examples[1]) . "', '" . mysql_real_escape_string($these_examples[2]) . "') ") or die(mysql_error());
		unset($these_examples);
	}
}


// echo the results
echo "<table><tbody>
	<tr style=\"background-color:rgb(200,200,50)\">
		<th><b>Database</b></th>
		<th><b>Table</b></th>
		<th><b>Row Count</b></th>
	</tr>
	<tr class=\"odd\">
		<td><b>$which_db</b></td>
		<td><b>$which_table</b></td>
		<td><b>$row_count</b></td>
	</tr>
</tbody></table><br>";

echo "<br><p id=\"closeTableInterface\" style=\"text-align:center; background-color:rgb(255,55,55); width:5%; margin:5px 0px 10px 20px;padding-bottom:3px;padding-left:1px; cursor:pointer; border-radius:2px;\" onclick=\"closeTableInterface()\">
		x
	 </p><br><br>";

echo "<table><tbody>
	<tr style=\"background-color:rgb(200,200,50)\">
		<th><b>Column Title</b></th>
		<th><b>Column Type</b></th>
		<th><b>How many Distinct</b></th>
		<th><b>Example 1</b></th>
		<th><b>Example 2</b></th>
		<th><b>Example 3</b></th>
	</tr>";

$which_tr_background=3;
mysql_connect("localhost","root","") or die(mysql_error());
$getInfo = mysql_query("SELECT `column_name`, `column_type`, `how_many_distinct`, `example_1`, `example_2`, `example_3` FROM `coursera_to_csv`.`table_info` WHERE `db_name` = '" . $which_db . "' && `table_name` = '" . $which_table . "'") or die(mysql_error());
while($table_data = mysql_fetch_array($getInfo)){
	if ($which_tr_background % 2 == 1){
		echo "<tr class=\"odd\">";
		$which_tr_background++;
	}
	else {
		echo "<tr class=\"even\">";
		$which_tr_background++;
	}
	echo "	<td>" . $table_data['column_name'] . "</td>
		<td>" . $table_data['column_type'] . "</td>
		<td>" . $table_data['how_many_distinct'] . "</td>
		<td>" . $table_data['example_1'] . "</td>
		<td>" . $table_data['example_2'] . "</td>
		<td>" . $table_data['example_3'] . "</td>
	</tr>";
}
echo "</tbody></table>";

?>
