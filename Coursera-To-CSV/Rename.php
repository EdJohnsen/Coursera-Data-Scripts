<?php

$this_host = $_REQUEST['whichHost'];
$this_user = $_REQUEST['whichUser'];
$this_pword = $_REQUEST['whichPword'];

$this_column = $_REQUEST['whichType'] . "_name";
$this_old_name = "'" . $_REQUEST['whichOldName'] . "'";
$this_new_name = "'" . $_REQUEST['whichNewName'] . "'";

mysql_connect($this_host, $this_user, $this_pword) or die(mysql_error());
mysql_query("UPDATE `coursera_to_csv`.`directory` SET `$this_column` = $this_new_name WHERE `$this_column` = $this_old_name") or die(mysql_error());

echo $_REQUEST['whichNewName'];
?>
