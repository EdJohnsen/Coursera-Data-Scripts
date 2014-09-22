<?php

$which_table = $_POST['whichTable'];
$which_db = $_POST['whichDB'];

$column_name_string = "";
$column_name_string_sql = "";
$column_name_header = "";

$csv_timestamp = time();


mysql_connect("localhost","root","") or die(mysql_error());
$getColumnNames = mysql_query("SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = '" . $which_db . "' && `TABLE_NAME` = '" . $which_table . "' ORDER BY `ORDINAL_POSITION` ASC") or die(mysql_error());
while ($column_names = mysql_fetch_array($getColumnNames)){
	$column_name_string .= $column_name_header . "\"" . $column_names['COLUMN_NAME'] . "\"";
	$column_name_string_sql .= $column_name_header . "REPLACE(IFNULL(`" . $column_names['COLUMN_NAME'] . "`, ''), '\r\n','<br>')";
	$column_name_header = ", ";
}

$sql = "SELECT $column_name_string UNION SELECT $column_name_string_sql FROM `$which_db`.`$which_table` INTO OUTFILE '../../htdocs/exportedCSVs/" . $which_table . "_" . $csv_timestamp . "_" . $which_db . ".csv' FIELDS ESCAPED BY '\"' TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\n'";


mysql_connect("localhost","root","") or die(mysql_error());
mysql_query($sql) or die(mysql_error());

echo "../exportedCSVs/" . $which_table . "_" . $csv_timestamp . "_" . $which_db . ".csv";

?>
