<?php

$this_host = $_REQUEST['whichHost'];
$this_user = $_REQUEST['whichUser'];
$this_pword = $_REQUEST['whichPword'];

$source_db_name_for_sql = "'" . $_REQUEST['whichOriginalSourceDB'] . "'";
$intermediate_db_name = "intermediate_" . $_REQUEST['whichOriginalSourceDB'];

$this_step = $_REQUEST['whichStep'];
$next_step = $this_step + 1;

$progress_description_array[0] = "nothing";
$next_step_description_array[0] = "nothing"; 
$progress_description_array[1] = "Intermediate tables have been created";
$next_step_description_array[1] = "Create a new hash_mapping table containing a small_public_user_id"; 
$progress_description_array[2] = "Hash_mapping table with small_public_user_ids created";
$next_step_description_array[2] = "Copy existing csv-ready data from source to intermediate tables"; 
$progress_description_array[3] = "CSV-ready data copied to intermediate tables from source";
$next_step_description_array[3] = "Separate submissions, saved_submissions, and quiz xml from source kvs table"; 
$progress_description_array[4] = "KVS table data separated and placed in intermediate tables";
$next_step_description_array[4] = "Deflate intermediate kvs submission and saved submission data"; 
$progress_description_array[5] = "Intermediate KVS submission and saved submission data deflated";
$next_step_description_array[5] = "Extract answer text from quiz xml";
$progress_description_array[6] = "Question and answer text has been extracted from intermediate tables";
$next_step_description_array[6] = "Separate submission data into tables by assessment type and user type (admin and students)";
$progress_description_array[7] = "Submission data has been separated into tables by assessment type and student or admin";
$next_step_description_array[7] = "Update intermediate tables with small_public_user_id values";
$progress_description_array[8] = "Intermediate tables have been updated with small_public_user_id values";
$next_step_description_array[8] = "Create public skeleton tables";
$progress_description_array[9] = "Admin and student user data has been separated";
$next_step_description_array[9] = "Create Public Skeletons!";
$progress_description_array[10] = "Item ids have been made human-readable";
$next_step_description_array[10] = "Make new, junk-free metadata tables";

if($this_step == 1){ // CREATE A NEW HASH TABLE FOR THE INTERMEDIATE DATABASE THAT INCLUDES A small_public_user_id -- 3 seconds
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	$getExportAndCourse = mysql_query("SELECT `course_name`, `export_name` FROM `coursera_to_csv`.`directory` WHERE `original_db_name` = $source_db_name_for_sql")  or die(mysql_error());
	while($thisExportAndCourse = mysql_fetch_array($getExportAndCourse)){
		$this_export_for_sql = "'" . $thisExportAndCourse['export_name'] . "'";
		$this_course_for_sql = "'" . $thisExportAndCourse['course_name'] . "'";
	}
	
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	$getHash = mysql_query("SELECT `original_db_name` FROM `coursera_to_csv`.`directory` WHERE `course_name` = $this_course_for_sql && `export_name` = $this_export_for_sql && `database_type` = 'hash_map'")  or die(mysql_error());
	while ($thisHash = mysql_fetch_array($getHash)){
		$this_hash_table = $thisHash['original_db_name'];
		$this_hash_for_sql = "'" . $thisHash['original_db_name'] . "'";
	}
	
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	$getUserIDtypes = mysql_query("SELECT `user_id_types` FROM `coursera_to_csv`.`find_sets_of_dbs` WHERE `database_name` = $this_hash_for_sql && `database_type` = 'hash_map'")  or die(mysql_error());
	while ($theseUserIDtypes = mysql_fetch_array($getUserIDtypes)){
		$hash_users_id_types = explode( ", ", $theseUserIDtypes['user_id_types']);
	}
	
	$thisHashUserString = "";
	$thisHashUserStringHeader = "";
	$create_new_hash_sql = "CREATE TABLE IF NOT EXISTS `intermediate_" . $this_hash_table . "` (`small_public_user_id` INT(11) PRIMARY KEY AUTO_INCREMENT";
	foreach($hash_users_id_types as $huid){
		if($huid == "user_id"){
			$create_new_hash_sql .= ", `$huid` INT(11)";
		}
		else {
			$create_new_hash_sql .= ", `$huid` CHAR(40)";
		}
		$thisHashUserString .= $thisHashUserStringHeader . "`$huid`";
		$thisHashUserStringHeader = ", ";
	}
	
	$create_new_hash_sql .= ")";

	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	mysql_select_db($intermediate_db_name);
	mysql_query($create_new_hash_sql) or die(mysql_error());

	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	mysql_query("INSERT INTO `$intermediate_db_name`.`intermediate_" . $this_hash_table . "` ($thisHashUserString) SELECT $thisHashUserString FROM `$this_hash_table`.`hash_mapping` ORDER BY `session_user_id` ASC") or die(mysql_error());
	
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	mysql_query("UPDATE `coursera_to_csv`.`progress` SET `progress_description` = '2' WHERE `source_database` = " . $source_db_name_for_sql) or die(mysql_error());	

	echo "<h2 style=\"font-size:16px;\">" . $intermediate_db_name . "</h2><div class=\"linespace\"></div>";
}

else if($this_step == 2){ // MOVE THE CONTENT OF THE NEARLY CSV-READY TABLES STRAIGHT OVER -- 10 seconds
	$previous_table = "";
	$table_i = -1;
	
	// select a set of tables from the information_schema.TABLES which don't have a 0 in their table_rows column so as to avoid trying to insert a null set
	$tables_for_in_sql = "";
	$tables_for_in_sql_header = "";
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	$getTables = mysql_query("SELECT `TABLE_NAME` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = $source_db_name_for_sql && `TABLE_NAME` NOT LIKE '%access%' && `TABLE_NAME` NOT LIKE '%announcements%' && `TABLE_NAME` NOT LIKE '%assignment%' && `TABLE_NAME` NOT LIKE '%hg_%' && `TABLE_NAME` NOT LIKE '%kvs%' && `TABLE_NAME` NOT LIKE '%late%' && `TABLE_NAME` NOT LIKE '%list%' && `TABLE_NAME` NOT LIKE '%sections%' && `TABLE_NAME` NOT LIKE '%wiki%' && `TABLE_ROWS` != 0 ORDER BY TABLE_NAME") or die(mysql_error());
	while($gotTables = mysql_fetch_array($getTables)){
		$tables_for_in_sql .= $tables_for_in_sql_header . "'" . $gotTables['TABLE_NAME'] . "'";
		$tables_for_in_sql_header = ", ";
	}
	
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	$getTablesAndColumns = mysql_query("SELECT `TABLE_NAME`, `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = $source_db_name_for_sql && `TABLE_NAME` IN ($tables_for_in_sql) ORDER BY TABLE_NAME, ORDINAL_POSITION ASC") or die(mysql_error());
	while($theseTablesAndColumns = mysql_fetch_array($getTablesAndColumns)){
		if($theseTablesAndColumns['TABLE_NAME'] != $previous_table){
			$previous_table = $theseTablesAndColumns['TABLE_NAME'];
			$table_i++;
			$table_array[$table_i] = "`" . $theseTablesAndColumns['TABLE_NAME'] . "`";
			$column_array[$table_i] = "`" . $theseTablesAndColumns['COLUMN_NAME'] . "`";
		}
		else{
			$column_array[$table_i] .= ", `" . $theseTablesAndColumns['COLUMN_NAME'] . "`";
		}
	}
	
	for ($ti=0; $ti<count($table_array); $ti++){
		mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
		mysql_query("INSERT INTO `" . $intermediate_db_name . "`." . $table_array[$ti] . " (" . $column_array[$ti] . ") SELECT " . $column_array[$ti] . " FROM `" . $_REQUEST['whichOriginalSourceDB'] . "`." . $table_array[$ti]) or die(mysql_error());
	}
	
	// check to see if there are any kvs_course*quiz rows
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	$getKVSquizTable = mysql_query("SELECT `TABLE_NAME` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = $source_db_name_for_sql && `TABLE_NAME` LIKE '%kvs%' && `TABLE_NAME` LIKE '%quiz%'") or die(mysql_error());
	while ($thisKVSquizTable = mysql_fetch_array($getKVSquizTable)){
		$this_kvs_quiz_table = $thisKVSquizTable['TABLE_NAME'];
	}
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	$getKVScqCount = mysql_query("SELECT COUNT(*) as thisCount FROM `" . $_REQUEST['whichOriginalSourceDB'] . "`.`$this_kvs_quiz_table`") or die(mysql_error());
	while ($thisKVScqCount = mysql_fetch_array($getKVScqCount)){
		$kvs_count = $thisKVScqCount['thisCount'];
	}
	if($kvs_count > 0 ){
		mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
		mysql_query("UPDATE `coursera_to_csv`.`progress` SET `progress_description` = '3' WHERE `source_database` = " . $source_db_name_for_sql) or die(mysql_error());
	}
	else {
		$next_step = 7;
		mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
		mysql_query("UPDATE `coursera_to_csv`.`progress` SET `progress_description` = '7' WHERE `source_database` = " . $source_db_name_for_sql) or die(mysql_error());
	}
	
	echo "<h2 style=\"font-size:16px;\">" . $intermediate_db_name . "</h2><div class=\"linespace\"></div>";
}

else if($this_step == 3){ // MOVE SEPARATE KVS SETS TO NEW TABLES IN THE INTERMEDIATE FROM THE SOURCE (XML, SAVED, SUBMISSIONS) -- 45ish seconds
	// separate the kvss
	// three separate inserts where you will be all like... insert into intermed_this select from original_that where key like 'whatevs'
	$kvs_key_sql = array("xml", "save", "submission");
	$target_kvss = array("kvs_quiz_xml", "kvs_saved_submissions", "kvs_submissions");
	for ($ti=0; $ti<3; $ti++){	
		mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
		mysql_query("INSERT INTO `" . $intermediate_db_name . "`.`" . $target_kvss[$ti] . "` (`key`, `value`) SELECT `key`, `value` FROM `" . $_REQUEST['whichOriginalSourceDB'] . "`.`kvs_course.quiz` WHERE `key` NOT LIKE '%backup%' && `key` LIKE '%" . $kvs_key_sql[$ti] . "%'") or die(mysql_error());
	}
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	mysql_query("UPDATE `coursera_to_csv`.`progress` SET `progress_description` = '4' WHERE `source_database` = " . $source_db_name_for_sql) or die(mysql_error());	
}

else if($this_step == 4){ // DEFLATE SUBMISSIONS AND SAVES -- 4 min 45 sec
	// determine number of rows in the intermediate kvs_submissions table for use in loop max value
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	$getRowCount = mysql_query("SELECT COUNT(*) as rowCount FROM `" . $intermediate_db_name . "`.`kvs_submissions`") or die(mysql_error());
	while($gotRowCount = mysql_fetch_array($getRowCount)){
		$kvs_submissions_row_count = $gotRowCount['rowCount'];
	}
	for ($oLi=0; $oLi<$kvs_submissions_row_count; $oLi+=100){
		$limitSQLoLi="LIMIT $oLi, 100";
		$sqlForInsert = "INSERT INTO `deflated_submissions` (`submission_id`, `start_time`, `saved_time`, `questions_per_submission`, `question_id`, `answer_or_answer_id`) VALUES ";
		$sqlFIheader = "";
		mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
		$result = mysql_query("SELECT `key`, `value` FROM `" . $intermediate_db_name . "`.`kvs_submissions` $limitSQLoLi;") or die(mysql_error());
		while ($row = mysql_fetch_array($result)){
			$whichRow = $row['key'];
			$whichRow = preg_replace('/submission.submission_id:/','',$whichRow);
			$rawSerial = $row['value'];
		
			$afterUn = unserialize(unserialize($rawSerial));
			$howMany=count($afterUn['answers']);
			if ($howMany && $howMany >= 1){
				$answerKey = array_keys($afterUn['answers']);
	
				for ($hMi=0; $hMi<$howMany; $hMi++){
					$thisAnsKey = $answerKey[$hMi];
					foreach ($afterUn['answers'][$thisAnsKey] as $thisAnsVal){
						$sqlForInsert .= $sqlFIheader . "('" . $whichRow . "', '" . $afterUn['start_time'] . "', '" . $afterUn['saved_time'] . "', '" . $howMany . "', '" . $thisAnsKey . "', '" . mysql_real_escape_string($thisAnsVal) . "')";
						$sqlFIheader = ", ";
					}
				}
			}
		}
		// perform the inserts, 100 at a time
		mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
		mysql_select_db($intermediate_db_name) or die(mysql_error());
		mysql_query("$sqlForInsert;") or die(mysql_error());
	}

	// determine number of rows in the intermediate kvs_saved_submissions table
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	$getRowCount = mysql_query("SELECT COUNT(*) as rowCount FROM `" . $intermediate_db_name . "`.`kvs_saved_submissions`") or die(mysql_error());
	while($gotRowCount = mysql_fetch_array($getRowCount)){
		$kvs_saves_row_count = $gotRowCount['rowCount'];
	}
	
	// determine what user_type was used for the kvs_saved_submissions
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	$getKVSsavedUtype = mysql_query("SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = '" . $intermediate_db_name . "' && `TABLE_NAME` = 'deflated_saves' && `COLUMN_NAME` LIKE '%user_id%' && `COLUMN_NAME` NOT LIKE '%small_public%'") or die(mysql_error());
	while ($thisKVSsavedUtype = mysql_fetch_array($getKVSsavedUtype)){
		$kvs_saved_user_type = $thisKVSsavedUtype['COLUMN_NAME'];
	}
	for ($oLi=0; $oLi<$kvs_saves_row_count; $oLi+=100){
		$limitSQLoLi="LIMIT $oLi, 100";
		$sqlForInsert = "INSERT INTO `deflated_saves` (`item_id`, `$kvs_saved_user_type`, `start_time`, `saved_time`, `questions_per_submission`, `question_id`, `answer_or_answer_id`) VALUES ";
		$sqlFIheader = "";
		mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
		$result = mysql_query("SELECT `key`, `value` FROM `" . $intermediate_db_name . "`.`kvs_saved_submissions` $limitSQLoLi;") or die(mysql_error());
		while ($row = mysql_fetch_array($result)){
			$whichRow = $row['key'];
			$whichRow = preg_replace('/saved.quiz_id:/','',$whichRow);
			$userStringStart = strpos($whichRow, ".u");
			$userString = substr($whichRow, $userStringStart);
			$whichRow = substr($whichRow, 0, $userStringStart);
			$userString = preg_replace('/.user_id:/', '', $userString);
			$rawSerial = $row['value'];
		
			$afterUn = unserialize(unserialize($rawSerial));
			$howMany=count($afterUn['answers']);
			if ($howMany && $howMany >= 1){
			$answerKey = array_keys($afterUn['answers']);
			for ($hMi=0; $hMi<$howMany; $hMi++){
				$thisAnsKey = $answerKey[$hMi];
				foreach ($afterUn['answers'][$thisAnsKey] as $thisAnsVal){
					$sqlForInsert .= $sqlFIheader . "('" . $whichRow . "', '" . $userString . "', '" . $afterUn['start_time'] . "', '" . $afterUn['saved_time'] . "', '" . $howMany . "', '" . $thisAnsKey . "', '" . mysql_real_escape_string($thisAnsVal) . "')";
					$sqlFIheader = ", ";
				}
			}
			}
			else {
				$thisAnsKey = "BLANK";
				$thisAnsVal = "BLANK";
				$howMany = 0;
				$sqlForInsert .= $sqlFIheader . "('" . $whichRow . "', '" . $userString . "', '" . $afterUn['start_time'] . "', '" . $afterUn['saved_time'] . "', '" . $howMany . "', '" . $thisAnsKey . "', '" . $thisAnsVal . "')";
				$sqlFIheader = ", ";
			}
		}
	
		mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
		mysql_select_db($intermediate_db_name) or die(mysql_error());
		mysql_query("$sqlForInsert;") or die(mysql_error());
	}
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	mysql_query("UPDATE `coursera_to_csv`.`progress` SET `progress_description` = '5' WHERE `source_database` = " . $source_db_name_for_sql) or die(mysql_error());	
}

else if($this_step == 5){ // MOVE SOME OF THE XML DATA TO THE answer_text TABLE -- 2 min 40 sec

	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	$getQuizIDs = mysql_query("SELECT `id`, `quiz_type` FROM `$intermediate_db_name`.`quiz_metadata` WHERE `deleted` = 0 && `open_time` IS NOT NULL && `parent_id` = -1 && `maximum_submissions` != 0") or die(mysql_error());
	while ($theseQuizIDs = mysql_fetch_array($getQuizIDs)){
		$quiz_ID_array[] = $theseQuizIDs['id'];
		$quiz_type_array[] = $theseQuizIDs['quiz_type'];
	}
	//print_r($quiz_ID_array);
	//print_r($quiz_type_array);
	for ($qI=0; $qI<count($quiz_ID_array); $qI++){
		mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
		$getXML = mysql_query("SELECT `value` FROM `$intermediate_db_name`.`kvs_quiz_xml` WHERE `key` = 'xml.quiz_id:" . $quiz_ID_array[$qI] . "'")  or die(mysql_error());
		while($thisXML = mysql_fetch_array($getXML)){
			$xml_start_position = strpos($thisXML['value'], "<");
			ini_set('mbstring.substitute_character', "none");
			$xml_from_db = mb_convert_encoding(substr($thisXML['value'], $xml_start_position, -2), 'UTF-8', 'UTF-8'); 	
		}

$create_xml=<<<XML
$xml_from_db
XML;

		$xml=simplexml_load_string($create_xml, null, LIBXML_NOCDATA);
		// echo "<br>" . (string) $xml->metadata->title;

		if($quiz_type_array[$qI] != "video"){
			for ($i=0; $i<count($xml->data->question_groups->question_group); $i++){
				$i_and_1 = $i +1;
				$which_question = (string) $xml->data->question_groups->question_group[$i]->question[id];
				if($xml->data->question_groups->question_group[$i]->question->metadata->parameters->type){
					$grader_type = (string) $xml->data->question_groups->question_group[$i]->question->metadata->parameters->type;
				}
				else if ($xml->data->question_groups->question_group[$i]->question->metadata->parameters->choice_type){
					$grader_type = (string) $xml->data->question_groups->question_group[$i]->question->metadata->parameters->choice_type;
				}
				// echo "<br>" . (string) $xml->data->question_groups->question_group[$i]->question->data->text;
				for($j=0; $j<count($xml->data->question_groups->question_group[$i]->question->data->option_groups->option_group->option); $j++){
					$j_and_1 = $j +1;
					$which_answer = (string) $xml->data->question_groups->question_group[$i]->question->data->option_groups->option_group->option[$j][id];
					$is_correct = (string) $xml->data->question_groups->question_group[$i]->question->data->option_groups->option_group->option[$j][selected_score];
					$answer_text = (string) $xml->data->question_groups->question_group[$i]->question->data->option_groups->option_group->option[$j]->text;
					mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
					mysql_query("INSERT INTO `$intermediate_db_name`.`answer_text` (`assessment_id`, `question_order`, `answer_order`, `grader_type`, `question_id`, `answer_id`, `is_correct`, `answer_text`) VALUES ('" . $quiz_ID_array[$qI] . "', '" . $i_and_1 . "', '" . $j_and_1 . "', '" . $grader_type . "', '" . $which_question . "', '" . $which_answer . "', '" . $is_correct . "', '" . mysql_real_escape_string($answer_text) . "')") or die(mysql_error());
				}
			}
		}
		else {
			for ($i=0; $i<count($xml->data->question_groups->question_group->question); $i++){
				$i_and_1 = $i +1;
				$which_question = (string) $xml->data->question_groups->question_group->question[$i][id];
				$video_time = (string) $xml->data->question_groups->question_group->question[$i]->data->video[time];
				if($xml->data->question_groups->question_group->question[$i]->metadata->parameters->type){
					$grader_type = (string) $xml->data->question_groups->question_group->question[$i]->metadata->parameters->type;
				}
				else if ($xml->data->question_groups->question_group->question[$i]->metadata->parameters->choice_type){
					$grader_type = (string) $xml->data->question_groups->question_group->question[$i]->metadata->parameters->choice_type;
				}
				// echo "<br>" . (string) $xml->data->question_groups->question_group->question[$i]->data->text;
				for($j=0; $j<count($xml->data->question_groups->question_group->question[$i]->data->option_groups->option_group->option); $j++){
					$j_and_1 = $j +1;
					$which_answer = (string) $xml->data->question_groups->question_group->question[$i]->data->option_groups->option_group->option[$j][id];
					$is_correct = (string) $xml->data->question_groups->question_group->question[$i]->data->option_groups->option_group->option[$j][selected_score];
					$answer_text = (string) $xml->data->question_groups->question_group->question[$i]->data->option_groups->option_group->option[$j]->text;
					mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
					mysql_query("INSERT INTO `$intermediate_db_name`.`answer_text` (`assessment_id`, `question_order`, `answer_order`, `if_video_when`, `grader_type`, `question_id`, `answer_id`, `is_correct`, `answer_text`) VALUES ('" . $quiz_ID_array[$qI] . "', '" . $i_and_1 . "', '" . $j_and_1 . "', '" . $video_time . "', '" . $grader_type . "', '" . $which_question . "', '" . $which_answer . "', '" . $is_correct . "', '" . mysql_real_escape_string($answer_text) . "')") or die(mysql_error());
				}
			}
		}
	}
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	mysql_query("UPDATE `coursera_to_csv`.`progress` SET `progress_description` = '6' WHERE `source_database` = " . $source_db_name_for_sql) or die(mysql_error());	
}

else if($this_step == 6){ // SEPARATE THE DEFLATED SUBMISSIONS BY USER TYPE AND ITEM TYPE -- 5 min 15 sec
	// determine which *user_id is used in the kvs_saved_submissions table
	// determine what user_type was used for the kvs_saved_submissions
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	$getKVSsavedUtype = mysql_query("SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = '" . $intermediate_db_name . "' && `TABLE_NAME` = 'deflated_saves' && `COLUMN_NAME` LIKE '%user_id%' && `COLUMN_NAME` NOT LIKE '%small_public%'") or die(mysql_error());
	while ($thisKVSsavedUtype = mysql_fetch_array($getKVSsavedUtype)){
		$kvs_saved_user_type = $thisKVSsavedUtype['COLUMN_NAME'];
	}
	// determine which *user_id appears in the users table for the kvs_submissions
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	$getUserIdType = mysql_query("SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` LIKE '" . $intermediate_db_name . "' && `COLUMN_NAME` LIKE '%user_id%' && `COLUMN_NAME` NOT LIKE '%small_public%'") or die(mysql_error());
	while($thisUserIdType = mysql_fetch_array($getUserIdType)){
		$this_user_id_type = $thisUserIdType['COLUMN_NAME'];
	}
	
	// pull a list of all of the admin user ids
	$admin_user_id_string = "";
	$admin_user_id_string_header = "";
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	$getAdminUids = mysql_query("SELECT `$this_user_id_type` FROM `$intermediate_db_name`.`users` WHERE `access_group_id` != '4'") or die(mysql_error());
	while ($theseAdminUids = mysql_fetch_array($getAdminUids)){
		$admin_user_id_string .= $admin_user_id_string_header . "'" . $theseAdminUids[$this_user_id_type] . "'";
		$admin_user_id_string_header = ", ";
	}
		// took less than 3 seconds
	if($this_user_id_type != $kvs_saved_user_type){
		$pattern = '/general/';
		$replacement = 'hash';
		mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
		$getOtherAdminUids = mysql_query("SELECT `$kvs_saved_user_type` FROM `$intermediate_db_name`.`" . preg_replace($pattern, $replacement, $intermediate_db_name) . "` WHERE `$this_user_id_type` IN ($admin_user_id_string)") or die(mysql_error());
		while ($theseOtherAdminUids = mysql_fetch_array($getOtherAdminUids)){
			$admin_user_id_string .= ", '" . $theseOtherAdminUids[$kvs_saved_user_type] . "'";
		}
	}
	// split the deflated_saves table by admin and student.
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	mysql_query("INSERT INTO `$intermediate_db_name`.`deflated_saves_admin` (`item_id`, `$kvs_saved_user_type`, `start_time`, `saved_time`, `questions_per_submission`, `question_id`, `answer_or_answer_id`) SELECT `item_id`, `$kvs_saved_user_type`, `start_time`, `saved_time`, `questions_per_submission`, `question_id`, `answer_or_answer_id` FROM `$intermediate_db_name`.`deflated_saves` WHERE `$kvs_saved_user_type` IN ($admin_user_id_string)") or die(mysql_error());
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	mysql_query("INSERT INTO `$intermediate_db_name`.`deflated_saves_students` (`item_id`, `$kvs_saved_user_type`, `start_time`, `saved_time`, `questions_per_submission`, `question_id`, `answer_or_answer_id`) SELECT `item_id`, `$kvs_saved_user_type`, `start_time`, `saved_time`, `questions_per_submission`, `question_id`, `answer_or_answer_id` FROM `$intermediate_db_name`.`deflated_saves` WHERE `$kvs_saved_user_type` NOT IN ($admin_user_id_string)") or die(mysql_error());
		// took about 5 seconds
	
	// pull and then loop through sets of exam, homework, survey, clicker and quiz item_ids
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	$getQuizIDs = mysql_query("SELECT `id`, `quiz_type` FROM `$intermediate_db_name`.`quiz_metadata` WHERE `deleted` = 0 && `open_time` IS NOT NULL && `parent_id` = -1 && `maximum_submissions` != 0 ORDER BY `quiz_type`") or die(mysql_error());
	while ($theseQuizIDs = mysql_fetch_array($getQuizIDs)){
		$quiz_ID_array[] = $theseQuizIDs['id'];
		$quiz_type_array[] = $theseQuizIDs['quiz_type'];
	}
		// took less than 1 second
	
	// add index to the submission_id column of the deflated_submissions table so that you can quickly update it from the id column in the quiz_submission_metadata table
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	mysql_query("ALTER TABLE `$intermediate_db_name`.`deflated_submissions` ADD INDEX ( `submission_id` )") or die(mysql_error());
		// took less than 3 seconds
	
	// update the deflated submissions table with $this_user_id_type and item_id data
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	mysql_query("UPDATE `$intermediate_db_name`.`deflated_submissions` t1 
				INNER JOIN `$intermediate_db_name`.`quiz_submission_metadata` t2 
				ON t1.submission_id = t2.id
				SET t1.`$this_user_id_type` = t2.`$this_user_id_type`") or die(mysql_error());
		// took less than 20 seconds
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	mysql_query("UPDATE `$intermediate_db_name`.`deflated_submissions` t1 
				INNER JOIN `$intermediate_db_name`.`quiz_submission_metadata` t2 
				ON t1.submission_id = t2.id
				SET t1.item_id = t2.item_id") or die(mysql_error());
		// took less than seconds
	
	// remove indexes from the submission_id columns of the quiz_submission_metadata and deflated_submissions tables
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	mysql_query("ALTER TABLE `$intermediate_db_name`.`deflated_submissions` DROP INDEX `submission_id`") or die(mysql_error());
		// took less than 1 second
	
	// in each loop, conditionally insert from deflated_saves or deflated_submissions where item_id matches loop item_id for admin or not admin
	// my goal here will be to put all of the admin submissions saves in their own tables, and all of the student stuff in tables by type (quiz, video, save)
	for($i=0; $i<count($quiz_ID_array); $i++){ 
		mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
		mysql_query("INSERT INTO `$intermediate_db_name`.`deflated_submissions_admin` (`item_id`, `$this_user_id_type`, `submission_id`, `start_time`, `saved_time`, `questions_per_submission`, `question_id`, `answer_or_answer_id`) SELECT `item_id`, `$this_user_id_type`, `submission_id`, `start_time`, `saved_time`, `questions_per_submission`, `question_id`, `answer_or_answer_id` FROM `$intermediate_db_name`.`deflated_submissions` WHERE `$this_user_id_type` IN ($admin_user_id_string) && `item_id` = '" . $quiz_ID_array[$i] . "'") or die(mysql_error());
		mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
		mysql_query("INSERT INTO `$intermediate_db_name`.`deflated_submissions_" . $quiz_type_array[$i] . "_students` (`item_id`, `$this_user_id_type`, `submission_id`, `start_time`, `saved_time`, `questions_per_submission`, `question_id`, `answer_or_answer_id`) SELECT `item_id`, `$this_user_id_type`, `submission_id`, `start_time`, `saved_time`, `questions_per_submission`, `question_id`, `answer_or_answer_id` FROM `$intermediate_db_name`.`deflated_submissions` WHERE `$this_user_id_type` NOT IN ($admin_user_id_string) && `item_id` = '" . $quiz_ID_array[$i] . "'") or die(mysql_error());
	}
		// took about 3 minutes and 14 seconds
	
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	mysql_query("UPDATE `coursera_to_csv`.`progress` SET `progress_description` = '7' WHERE `source_database` = " . $source_db_name_for_sql) or die(mysql_error());	
	
}

else if($this_step == 7){ // Update small_public_user_id columns -- 45 seconds
	// determine the name of the related, intermediate hash table (which should include your small_public_user_id data)
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	$getHashTable = mysql_query("SELECT `TABLE_NAME` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = '" . $intermediate_db_name . "' && `TABLE_NAME` LIKE '%_hash'") or die(mysql_error());
	while($thisHashTable = mysql_fetch_array($getHashTable)){
		$this_hash_table = $thisHashTable['TABLE_NAME'];
	}
	// select the appropriate *user_id and table combinations from your intermediate database and generate some UPDATE sql from it
	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	$getTableArray = mysql_query("SELECT `TABLE_NAME`, `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA` = '" . $intermediate_db_name . "' && `TABLE_NAME` NOT LIKE '%hash%' && `COLUMN_NAME` IN ('session_user_id', 'anon_user_id', 'user_id')") or die(mysql_error());
	while($thisTableArray = mysql_fetch_array($getTableArray)){
		$column_name_for_index_array = $thisTableArray['COLUMN_NAME'];
		$hash_index_array[$column_name_for_index_array] = "ALTER TABLE `$intermediate_db_name`.`" . $this_hash_table . "` ADD INDEX ( `$column_name_for_index_array` )";
		$remove_hash_index_array[$column_name_for_index_array] = "ALTER TABLE `$intermediate_db_name`.`" . $this_hash_table . "` DROP INDEX `$column_name_for_index_array`";
		$table_index_array[] = "ALTER TABLE `$intermediate_db_name`.`" . $thisTableArray['TABLE_NAME'] . "` ADD INDEX ( `$column_name_for_index_array` )";
		$remove_table_index_array[] = "ALTER TABLE `$intermediate_db_name`.`" . $thisTableArray['TABLE_NAME'] . "` DROP INDEX `$column_name_for_index_array`";
		$update_array[] = "UPDATE `" . $thisTableArray['TABLE_NAME'] . "` t1
		INNER JOIN `" . $this_hash_table . "` t2
		ON t1." . $thisTableArray['COLUMN_NAME'] . " = t2." . $thisTableArray['COLUMN_NAME'] . "
		SET t1.`small_public_user_id` = t2.`small_public_user_id`";
	}

	// add indexes to the relevant hast table columns
	foreach ($hash_index_array as $hia){
		mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
		mysql_query($hia) or die(mysql_error());
	}
	
	// update tables with small_public_user_ids from the queries built above, adding and then removing indexes
	for ($uaI=0; $uaI<count($update_array); $uaI++){
		// add the appropriate *user_id index to the current table
		mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
		mysql_query($table_index_array[$uaI]) or die(mysql_error());
		
		// update the current table with small_public_user id values
		mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
		mysql_select_db("$intermediate_db_name");
		mysql_query($update_array[$uaI]) or die(mysql_error());
		
		// remove the appropriate *user_id index from the current table
		mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
		mysql_query($remove_table_index_array[$uaI]) or die(mysql_error());
	}	
	
	// remove the non primary indexes from the hash table
	foreach ($remove_hash_index_array as $rhia){
		mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
		mysql_query($rhia) or die(mysql_error());
	}
	// took 1 minute 50 seconds to update all tables!!

	mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
	mysql_query("UPDATE `coursera_to_csv`.`progress` SET `progress_description` = '8' WHERE `source_database` = " . $source_db_name_for_sql) or die(mysql_error());	
}

else if($this_step >= 8){
	include 'Create_Public_Skeleton.php';
}


echo "<p>" . $progress_description_array[$next_step] . "</p>
<button onclick=\"fillIntermediateTables('" . $_REQUEST['whichOriginalSourceDB'] . "', '" . $this_host . "', '" . $this_user . "', '" . $this_pword . "', '" . $next_step . "')\" style=\"background-color:rgb(90,230,90)\">" . $next_step_description_array[$next_step] . "</button>
<div class=\"linespace\"></div>";

mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
$getResultantTables = mysql_query("SELECT `TABLE_NAME`, `TABLE_ROWS` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` LIKE '" . $intermediate_db_name . "'") or die(mysql_error());
while($theseResultantTables = mysql_fetch_array($getResultantTables)){
	if($theseResultantTables['TABLE_ROWS'] == 0){
		echo "<p title=\"not yet filled\" style=\"color:grey; margin:0px; padding:0px;\">" . $theseResultantTables['TABLE_NAME'] . "</p>
		<div class=\"linespace\"></div>
		";
	}
	else{ 
		echo "<button class=\"table\" onclick=\"openSourceTableInterface('" . $theseResultantTables['TABLE_NAME'] . "', '" . $intermediate_db_name . "', event)\">" . $theseResultantTables['TABLE_NAME'] . "</button>
		<div class=\"linespace\"></div>
		";
	}
}
?>
