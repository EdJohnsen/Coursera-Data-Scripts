<?php

echo "<div style=\"width:95%; background-color:rgb(205,50,50); padding:5px; margin-bottom:10px;\">
	<h3 style=\"background-color:white; padding:5px; width:800px;\">An attempt by this software to connect to your mysql server failed:<br></h3>
	<p style=\"background-color:white; padding:5px; width:800px;\">This software needs a valid set of three mysql server connection values.<br></p>
	<br>
</div>
<div style=\"width:95%; background-color:rgb(50,50,205); padding:5px;  margin-bottom:10px;\">
	<h3 style=\"background-color:white; padding:5px; width:800px;\">In its latest attempt, this software tried the following set of server connection values:<br></h3>
	<table style=\"background-color:white; padding:5px; width:800px;\"><tbody>
		<tr><td style=\"background-color:rgb(190,190,240);\"><b>\"$current_host\"</b></td><td>was tried as the mysql server <b>host</b> value.</td><td>(Default value assumed by the program is: <b>\"localhost\"</b>)
		</td></tr>
		<tr><td style=\"background-color:rgb(190,190,240);\"><b>\"$current_uname\"</b></td><td>was tried as the mysql server <b><i>user name</i></b> value.</td><td>(Default value assumed by the program is: <b>\"root\"</b>)
		</td></tr>
		<tr><td style=\"background-color:rgb(190,190,240);\"><b>\"$current_pword\"</b></td><td>was tried as the mysql server <b><i>password</i></b> value.</td><td>(By default, the password is assumed to have no value, <b>\"\"</b>)
		</td></tr>
</tbody></table><br>
<br>
</div>
<div style=\"width:95%; background-color:rgb(50,205,50); padding:5px;\">
	<h3 style=\"background-color:white; padding:5px; width:800px;\">To attempt to resolve this mysql server connection problem:<br></h3>
	<p style=\"background-color:white; padding:5px; width:800px;\">Please determine the host, username and password associated with your mysql server's most completely privileged user and enter those values into this form:<br></p>
	<form name=\"updateConnection\" style=\"background-color:white; padding:5px; width:800px;\" action=\"Update_The_Connection_Variables_php_File.php\" method=\"get\">
		<table style=\"text-align:center\"><tbody>
			<tr><td><b>host</b> </td><td><input type=\"text\" name=\"thisLocal\"></td></tr>
			<tr><td><b>user name</b> </td><td><input type=\"text\" name=\"thisUser\"></td></tr>
			<tr><td><b>password</b> </td><td><input type=\"text\" name=\"thisPW\"></td></tr>
		</tbody></table>
		<br>
		<input type=\"submit\" value=\"Try connection with new variables\"><br><br>
	</form>
</div>";
?>
