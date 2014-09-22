<?php

global $current_host;
global $current_uname;
global $current_pword;


// determine which databases have already been added to the directory
$exclude_string = "('intermediate_january_general_backup',";
$exclude_string_header = "";
mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error);
$look_for_databases = mysql_query("SELECT DISTINCT `database_name` FROM coursera_to_csv.directory") or die(mysql_error());
while($found_databases = mysql_fetch_array($look_for_databases)){
	$exclude_string .= $exclude_string_header . "'" . $found_databases['database_name'] . "'";
	$exclude_string_header = ", ";
}
$exclude_string .= ")";


// look through the information_schema.TABLES to find hash, forum, and general databases that aren't already in the directory by searching for common coursera table names associated with each coursera database
mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error);
$look_for_databases = mysql_query("SELECT DISTINCT `TABLE_SCHEMA` FROM `information_schema`.`TABLES` WHERE TABLE_NAME = 'quiz_metadata' && TABLE_SCHEMA NOT IN $exclude_string") or die(mysql_error());
while($found_databases = mysql_fetch_array($look_for_databases)){
	$general_db_guesses[] = $found_databases['TABLE_SCHEMA'];
}

mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error);
$look_for_databases = mysql_query("SELECT DISTINCT `TABLE_SCHEMA` FROM `information_schema`.`TABLES` WHERE TABLE_NAME = 'hash_mapping' && TABLE_SCHEMA NOT IN $exclude_string") or die(mysql_error());
while($found_databases = mysql_fetch_array($look_for_databases)){
	$hash_db_guesses[] = $found_databases['TABLE_SCHEMA'];
}

mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error);
$look_for_databases = mysql_query("SELECT DISTINCT `TABLE_SCHEMA` FROM `information_schema`.`TABLES` WHERE TABLE_NAME = 'forum_forums' && TABLE_SCHEMA NOT IN $exclude_string") or die(mysql_error());
while($found_databases = mysql_fetch_array($look_for_databases)){
	$forum_db_guesses[] = $found_databases['TABLE_SCHEMA'];
}


// add each newly found database to the directory
if(isset($forum_db_guesses)){
	foreach($forum_db_guesses as $forum_db_guess){
		$forum_db_name_for_sql = "'" . $forum_db_guess . "'";
		mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
		mysql_query("INSERT INTO coursera_to_csv.directory (`database_name`, `database_type`, `original_db_name`) VALUES ($forum_db_name_for_sql, 'forum', $forum_db_name_for_sql)") or die(mysql_error());
		mysql_connect("localhost", "root", "") or die(mysql_error());
		$getTimes = mysql_query("SELECT MAX(`timestamp`) as lastTime, MIN(`timestamp`) as firstTime FROM `$forum_db_guess`.`activity_log`") or die(mysql_error());
		while($theseTimes = mysql_fetch_array($getTimes)){
			$latest_activity = "'" . $theseTimes['lastTime'] . "'";
			$earliest_activity = "'" . $theseTimes['firstTime'] . "'";
		}

		mysql_connect("localhost", "root", "") or die(mysql_error());
		$getIDtype = mysql_query("SELECT DISTINCT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = $forum_db_name_for_sql && TABLE_NAME = 'activity_log' && COLUMN_NAME LIKE '%user_id%'") or die(mysql_error());
		while($thisIDtype = mysql_fetch_array($getIDtype)){
			$id_type = "'" . $thisIDtype['COLUMN_NAME'] . "'";
		}

		mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
		mysql_query("INSERT INTO `coursera_to_csv`.`find_sets_of_dbs` (`database_name`, `database_type`, `max_time`, `min_time`, `user_id_types`) VALUES ($forum_db_name_for_sql, 'forum', $latest_activity, $earliest_activity, $id_type)") or die(mysql_error());
	}
}


if(isset($hash_db_guesses)){
	foreach($hash_db_guesses as $hash_db_guess){
		$hash_db_name_for_sql = "'" . $hash_db_guess . "'";
		mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
		mysql_query("INSERT INTO coursera_to_csv.directory (`database_name`, `database_type`, `original_db_name`) VALUES ($hash_db_name_for_sql, 'hash_map', $hash_db_name_for_sql)") or die(mysql_error());
		mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
		$getUserCount = mysql_query("SELECT COUNT(*) as thisCount FROM `$hash_db_guess`.`hash_mapping`") or die(mysql_error());
		while($thisUserCount = mysql_fetch_array($getUserCount)){
			$hash_user_count = "'" . $thisUserCount['thisCount'] . "'";
		}

		$user_id_types = "";
		$user_id_types_header = "";
		mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
		$getIDtypes = mysql_query("SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = $hash_db_name_for_sql ORDER BY `COLUMN_NAME` ASC") or die(mysql_error());
		while($theseIDtypes = mysql_fetch_array($getIDtypes)){
			$user_id_types .= $user_id_types_header . $theseIDtypes['COLUMN_NAME'];
			$user_id_types_header = ", ";
		}
		$user_id_types_for_sql = "'" . $user_id_types . "'";

		mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
		mysql_query("INSERT INTO `coursera_to_csv`.`find_sets_of_dbs` (`database_name`, `database_type`, `user_count`, `user_id_types`) VALUES ($hash_db_name_for_sql, 'hash_map', $hash_user_count, $user_id_types_for_sql)") or die(mysql_error());
	}
}

if(isset($general_db_guesses)){
	foreach($general_db_guesses as $general_db_guess){
		$general_db_name_for_sql = "'" . $general_db_guess . "'";
		mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
		mysql_query("INSERT INTO coursera_to_csv.directory (`database_name`, `database_type`, `original_db_name`) VALUES ($general_db_name_for_sql, 'general', $general_db_name_for_sql)") or die(mysql_error());
		mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
		$getUserVars = mysql_query("SELECT COUNT(*) as thisCount, MAX(`last_access_time`) as lastAccessTime FROM `$general_db_guess`.`users`") or die(mysql_error());
		while($thisUserVar = mysql_fetch_array($getUserVars)){
			$general_user_count = "'" . $thisUserVar['thisCount'] . "'";
			$most_recent_user_time = "'" . $thisUserVar['lastAccessTime'] . "'";
		}

		mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
		$getFirstVidOpenTime = mysql_query("SELECT MIN(`open_time`) as firstVidTime from `$general_db_guess`.`lecture_metadata`") or die(mysql_error());
		while($thisFirstVidOpenTime = mysql_fetch_array($getFirstVidOpenTime)){
			$earliest_video_open_time = $thisFirstVidOpenTime['firstVidTime'];
		}

		$user_id_types = "";
		$user_id_types_header = "";
		mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
		$getIDtypes = mysql_query("SELECT DISTINCT `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = $general_db_name_for_sql && `COLUMN_NAME` LIKE '%user_id%' ORDER BY `COLUMN_NAME` ASC") or die(mysql_error());
		while($theseIDtypes = mysql_fetch_array($getIDtypes)){
			$user_id_types .= $user_id_types_header . $theseIDtypes['COLUMN_NAME'];
			$user_id_types_header = ", ";
		}
		$user_id_types_for_sql = "'" . $user_id_types . "'";

		mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
		mysql_query("INSERT INTO `coursera_to_csv`.`find_sets_of_dbs` (`database_name`, `database_type`, `user_count`, `max_time`, `min_time`, `user_id_types`) VALUES ($general_db_name_for_sql, 'general', $general_user_count, $most_recent_user_time, $earliest_video_open_time, $user_id_types_for_sql)") or die(mysql_error());
	}
}
	include 'Collate_Sources.php';
?>
