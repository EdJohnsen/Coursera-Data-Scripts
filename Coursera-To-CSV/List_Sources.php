<?php

global $current_host;
global $current_uname;
global $current_pword;


$previous_course_name = "";
$previous_export_name = "";
echo "<div id=\"databaseList\" class=\"horizontalGroup\" style=\"background-color:rgb(255,255,255)\">
	<h2 style=\"margin:0px 0px 10px 0px; padding:0px; font-size:24px;\">Source Databases</h2>
	<div class=\"linespace\"></div>
";
mysql_connect($current_host,$current_uname,$current_pword) or die(mysql_error());
$getList = mysql_query("SELECT `p_id`, `course_name`, `export_name`, `database_name` FROM `coursera_to_csv`.`directory` WHERE `course_name` IS NOT NULL ORDER BY `original_course_name` DESC, `original_export_name` DESC") or die(mysql_error());
while($thisList = mysql_fetch_array($getList)){
	if($thisList['course_name'] != $previous_course_name){echo "	<button class=\"course\" id=\"course_" . $thisList['p_id'] . "\"  onclick=\"openRenameInterface('course', '" . $thisList['p_id'] . "', '" . $thisList['course_name'] . "', event)\">" . $thisList['course_name'] . "</button>
	<div class=\"linespace\"></div>
	";}
	if($thisList['export_name'] != $previous_export_name){
		echo "<button class=\"export\" id=\"export_" . $thisList['p_id'] . "\" onclick=\"openRenameInterface('export', '" . $thisList['p_id'] . "', '" . $thisList['export_name'] . "', event)\">" . $thisList['export_name'] . "</button>
	<div class=\"linespace\"></div>
	";
	}
	echo "<button class=\"database\" id=\"database_" . $thisList['p_id'] . "\" onclick=\"openRenameInterface('database', '" . $thisList['p_id'] . "', '" . $thisList['database_name'] . "', event)\">" . $thisList['database_name'] . "</button>
	<div class=\"linespace\"></div>
	";
	$previous_course_name = $thisList['course_name'];
	$previous_export_name = $thisList['export_name'];
	$vert_course_div_header = "</div></div>
	<div class=\"verticalGroup\">";
	$vert_export_div_header = "</div>
	<div class=\"verticalGroup\">
	";
}
echo "</div>
";
echo "<script type=\"text/javascript\">var curHost = \"$current_host\"; var curUser = \"$current_uname\"; var curPword = \"$current_pword\";</script>
<div id=\"sourceTableList\" class=\"horizontalGroup\" style=\"background-color:rgb(255,255,255)\">
	<h2 style=\"margin:0px 0px 10px 0px; padding:0px; font-size:24px;\">Source Tables</h2>
	<div class=\"linespace\"></div>
</div>
<div id=\"intermediateTableList\" class=\"horizontalGroup\" style=\"background-color:rgb(255,255,255)\">
	<h2 style=\"margin:0px 0px 10px 0px; padding:0px; font-size:24px;\">Intermediate Tables</h2>
	<div class=\"linespace\"></div>
</div>
<div id=\"publicTableList\" class=\"horizontalGroup\" style=\"background-color:rgb(255,255,255)\">
	<h2 style=\"margin:0px 0px 10px 0px; padding:0px; font-size:24px;\">Public Tables</h2>
	<div class=\"linespace\"></div>
</div>
<div id=\"viewTableHere\" style=\"position:absolute; height:350px;width:400px; display:none; left:0px;top:0px; background-color:rgb(235,235,235); border:10px solid rgb(155,155,155);border-radius:5px;\">
</div>
<div id=\"view_meta_or_tables\" style=\"position:absolute; height:2000px;height:auto;width:95%;width:auto; display:none; left:2%;top:20px; background-color:rgb(140,140,140); border:10px solid rgb(155,155,155);border-radius:5px;\">
	<img src=\"dots64.gif\">
</div>
</div>
";
?>
