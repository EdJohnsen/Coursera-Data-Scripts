<?php

$which_table = $_POST['whichTable'];
$which_db = $_POST['whichDB'];


mysql_connect("localhost","root","") or die(mysql_error());
$getColumnNames = mysql_query("SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = '" . $which_db . "' && `TABLE_NAME` = '" . $which_table . "' ORDER BY `ORDINAL_POSITION` ASC") or die(mysql_error());
while ($column_names = mysql_fetch_array($getColumnNames)){
	$column_name_array[] = $column_names['COLUMN_NAME'];
}

$response_table = "<table><tbody><tr style=\"background-color:rgb(50,200,50)\">";
for ($cI = 0; $cI<count($column_name_array); $cI++){
	$response_table .= "<th>" . $column_name_array[$cI] . "</th>";
}
$response_table .= "</tr>";

$even_or_odd = 3;
mysql_connect("localhost","root","") or die(mysql_error());
$getInfo = mysql_query("SELECT * FROM `$which_db`.`$which_table` LIMIT 30") or die(mysql_error());
while($table_data = mysql_fetch_array($getInfo)){
	if($even_or_odd % 2 == 1){
		$response_table .= "<tr class=\"odd\">";
		$even_or_odd++;
	}
	else{
		$response_table .= "<tr class=\"even\">";
		$even_or_odd++;
	}
	for ($cI = 0; $cI<count($column_name_array); $cI++){
		$response_table .= "<td>" . $table_data[$cI] . "</td>";
	}
	$response_table .= "</tr>";
}

$response_table .= "</tbody></table>";

mysql_connect("localhost","root","") or die(mysql_error());
$getCount = mysql_query("SELECT COUNT(*) as countOfRows FROM `$which_db`.`$which_table`") or die(mysql_error());
while($thisCount = mysql_fetch_array($getCount)){
	$row_count = $thisCount['countOfRows'];
}

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
	 
echo $response_table;
?>
