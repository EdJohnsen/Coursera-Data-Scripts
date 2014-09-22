<?php

global $current_host;
global $current_uname;
global $current_pword;

// find out which databases in the directory have not yet been associated with a course_name so as to avoid overwriting custom database names & save time upon rerun of Coursera_to_csv.php
$already_catalogued_dbs = "";
$acdbs_header = "";
mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
$getCataloguedDBs = mysql_query("SELECT `database_name` FROM `coursera_to_csv`.`directory` WHERE `course_name` IS NULL && (`database_type` = 'general' || `database_type` IS NULL)") or die(mysql_error());
while ($theseCataloguedDBs = mysql_fetch_array($getCataloguedDBs)){
	$not_yet_catalogued_dbs .= $acdbs_header . "'" . $theseCataloguedDBs['database_name'] . "'";
	$acdbs_header = ", ";	
}

// check for matches between general and hash databases
$loopi = 0;
mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
$getGeneralCounts = mysql_query("SELECT `user_count`, `database_name`, `user_id_types`, `max_time`, `min_time` FROM `coursera_to_csv`.`find_sets_of_dbs` WHERE `database_name` IN ($not_yet_catalogued_dbs) && `database_type` = 'general'") or die(mysql_error());
while($theseGeneralCounts = mysql_fetch_array($getGeneralCounts)){
	$general_name_array[$loopi] = $theseGeneralCounts['database_name'];
	$general_count_array[$loopi] = $theseGeneralCounts['user_count'];
	$general_user_type_array[$loopi] = $theseGeneralCounts['user_id_types'];
	$general_max_array[$loopi] = $theseGeneralCounts['max_time'];
	$general_min_array[$loopi] = $theseGeneralCounts['min_time'];
	$loopi++;
}

if($general_name_array && is_array($general_name_array)){
	for($i=0; $i<$loopi; $i++){
		mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
		$getMatchedHash = mysql_query("SELECT `database_name` FROM `coursera_to_csv`.`find_sets_of_dbs` WHERE `user_count` = '" . $general_count_array[$i] . "' && `database_type` = 'hash_map'") or die(mysql_error());
		while($thisMatchedHash = mysql_fetch_array($getMatchedHash)){
			$hash_name_array[$i] = $thisMatchedHash['database_name'];
			$double_check_query_array[$i] = "SELECT COUNT( DISTINCT `" . $hash_name_array[$i] . "`.`hash_mapping`.`" . $general_user_type_array[$i] . "`) as distCount FROM `" . $hash_name_array[$i] . "`.`hash_mapping` INNER JOIN `" . $general_name_array[$i] . "`.`users` ON `" . $general_name_array[$i] . "`.`users`.`" . $general_user_type_array[$i] . "` = `" . $hash_name_array[$i] . "`.`hash_mapping`.`" . $general_user_type_array[$i] . "`";
		}
	}
}

for($j=0; $j<$loopi; $j++){
	mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
	$getDBLcheckCounts = mysql_query($double_check_query_array[$j]) or die(mysql_error());
	while($theseDBLcheckCounts = mysql_fetch_array($getDBLcheckCounts)){
		IF($theseDBLcheckCounts['distCount'] == $general_count_array[$j]){
			$update_directory_array[] = "UPDATE `coursera_to_csv`.`directory` SET `course_name` = 'demo_vid_opened_at_" . $general_min_array[$j] . "', `export_name` =  'last_user_access_time_" . $general_max_array[$j] . "', `original_course_name` = 'demo_vid_opened_at_" . $general_min_array[$j] . "', `original_export_name` =  'last_user_access_time_" . $general_max_array[$j] . "' WHERE `database_name` IN ('" . $general_name_array[$j] . "', '" . $hash_name_array[$j] . "')";
		}
	}
}

if($update_directory_array && is_array($update_directory_array)){
	foreach($update_directory_array as $this_update_query){
		mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
		mysql_query($this_update_query) or die(mysql_error());
	}
	mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
	mysql_query("UPDATE `coursera_to_csv`.`progress` SET `progress_description` = 'found source'") or die(mysql_error());
}

include 'List_Sources.php';

?>
