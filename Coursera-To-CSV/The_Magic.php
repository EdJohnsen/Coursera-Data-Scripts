<?php

global $current_host;
global $current_uname;
global $current_pword;


mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error);
$check_progress = mysql_query("SELECT `progress_description` FROM `coursera_to_csv`.`progress` ORDER BY `p_k` ASC LIMIT 1") or die(mysql_error);
while ($current_progress = mysql_fetch_array($check_progress)){
	$starting_point = $current_progress['progress_description'];
}

if($starting_point == "started program"){
	include 'Find_Sources.php';
}
else if($starting_point == "found source"){
	include 'List_Sources.php';
}
else if($starting_point == "created skeleton"){
	echo "do you see the map?";
}
else if($starting_point == "created skeleton"){
	echo "is your map complete? Would you like to start a new one?";
}
?>
