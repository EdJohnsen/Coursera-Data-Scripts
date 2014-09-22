<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<link rel="stylesheet" type="text/css" href="Coursera_to_csv_css.css">
<script type="text/javascript" src="Coursera_to_csv_js.js"></script>
</head>
<body>
<div id="content">
<?php

// provide default values for connections to a mysql server
$current_host = "localhost";
$current_uname = "root";
$current_pword = "";


// select user defined values for connections to a mysql server
include 'Your_Connection_Variables.php';


// test the current connection values
$connection_good = "no";
$already_started = "no";
@mysql_connect($current_host, $current_uname, $current_pword);
$find_progress = @mysql_query("SELECT `progress_description` FROM `coursera_to_csv`.`progress` ORDER BY `p_k` ASC LIMIT 1");
if($find_progress && print_r($find_progress, true) == "Resource id #5"){
while($found_progress = mysql_fetch_array($find_progress)){
	if ($found_progress['progress_description'] && $found_progress['progress_description'] != ""){
		$connection_good = "yes";
		$already_started = "yes";
	}
}
}

if ($connection_good == "no"){
@mysql_connect($current_host, $current_uname, $current_pword);
$check_info = @mysql_query("SELECT * FROM `information_schema`.`TABLES` ORDER BY `TABLE_SCHEMA` DESC LIMIT 1");
while($found_info = mysql_fetch_array($check_info)){
	if ($found_info['TABLE_SCHEMA'] && $found_info['TABLE_SCHEMA'] != "information_schema"){
		$connection_good = "yes";
	}
}
}
// react to connection test
if($connection_good == "no"){ // if connection test fails, ask user to try new connection values:
	include 'Update_Connection_Variables.php';
}
else { // otherwise, if connection test succeeds:
	// check if this program previously ran with some success on this server
	@mysql_connect($current_host, $current_uname, $current_pword);
	$find_progress = @mysql_query("SELECT `progress_description` FROM `coursera_to_csv`.`progress` ORDER BY `p_k` ASC LIMIT 1");
	if($find_progress && print_r($find_progress, true) == "Resource id #6"){
	while($found_progress = mysql_fetch_array($find_progress)){
		if ($found_progress['progress_description'] && $found_progress['progress_description'] == "started program"){
			$already_started = "yes";
		}
	}
	}

	// if user hasn't created a progress tracking table, then create that table
	if($already_started == "no"){
		include 'Create_Progress_Table.php';
		include 'The_Magic.php';
	}
	// otherwise, if a user has created a progress tracking table, then make the magic happen
	else {
		include 'The_Magic.php';
	}
}
echo "<div style=\"margin-top:100px;\">
<br>
<span style=\"color:gray;\">Software provided \"as is\" and without any warranty or guarantee.
<br>
Some tech support available via <a href=\"mailto:edmond.johnsen@colorado.edu?subject=Coursera_To_CSV\">edmond.johnsen@colorado.edu</a>
</div>
";
?>
</div>
</body>
</html>
