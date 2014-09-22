<?php

$new_host = $_REQUEST['thisLocal'];
if(!$new_host || $new_host == ""){$new_host = "";}
$new_user = $_REQUEST['thisUser'];
if(!$new_user || $new_user == ""){$new_user = "";}
$new_pwrd = $_REQUEST['thisPW'];
if(!$new_pwrd || $new_pwrd == ""){$new_pwrd = "";}

$variable_file = fopen("Your_Connection_Variables.php","w");

fwrite($variable_file, "<?php \$current_host = \"$new_host\"; \$current_uname = \"$new_user\"; \$current_pword = \"$new_pwrd\"; ?>");

fclose($variable_file);

header('Location:Coursera_to_CSV.php');
?>
