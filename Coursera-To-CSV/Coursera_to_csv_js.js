viewTablesDiv = document.getElementById("view_meta_or_tables");

function closeRenameInterface(){
	document.getElementById('renameHere').style.display="none";
}

function closeViewSourceTablesInterface(){
	document.getElementById('viewTableHere').style.display="none";
}

function closeTableInterface(){
	viewTablesDiv.style.display="none";
}

function rename(item_type, item_id, old_name){
	if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
		var xmlhttp=new XMLHttpRequest();
	}
	else{// code for IE6, IE5
		var xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	var url = "Rename.php";
	var params = "whichType=" + item_type + "&whichOldName=" + old_name + "&whichNewName=" + rnbInput.value + "&whichHost=" + curHost + "&whichUser=" + curUser + "&whichPword=" + curPword;
	xmlhttp.open("POST", url, true);
	//Send the proper header information along with the request
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Content-length", params.length);
	xmlhttp.setRequestHeader("Connection", "close");
	
	xmlhttp.onreadystatechange = function(){
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
			document.getElementById(item_type + "_" + item_id).innerHTML = xmlhttp.responseText;
			closeRenameInterface();
		}
	}
	xmlhttp.send(params);
}

function viewDatabase(thisDatabase){
	closeRenameInterface();
	document.getElementById("sourceTableList").innerHTML = "<h2 style=\"font-size:24px; margin:0px 0px 10px; padding:0px;\">Source Tables</h2><p>loading</p><img src=\"dots64.gif\" alt=\"loading\"><div class=\"linespace\"></div>"
	if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
		var xmlhttp=new XMLHttpRequest();
	}
	else{// code for IE6, IE5
		var xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	var url = "View_Source_Tables.php";
	var params = "whichSourceDB=" + thisDatabase + "&whichHost=" + curHost + "&whichUser=" + curUser + "&whichPword=" + curPword;
	xmlhttp.open("POST", url, true);
	//Send the proper header information along with the request
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Content-length", params.length);
	xmlhttp.setRequestHeader("Connection", "close");
	
	xmlhttp.onreadystatechange = function(){
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
			document.getElementById("sourceTableList").innerHTML = "<h2 style=\"font-size:24px; margin:0px 0px 10px; padding:0px;\">Source Tables</h2><div class=\"linespace\"></div>" + xmlhttp.responseText;
			closeRenameInterface();
			closeViewSourceTablesInterface();
			document.getElementById("intermediateTableList").innerHTML = "<h2 style=\"font-size:24px; margin:0px 0px 10px; padding:0px;\">Intermediate Tables</h2>";
		}
	}
	xmlhttp.send(params);
}

function viewIntermediateTables(thisDB, host, user, pword){
	if(document.getElementById("sourceToIntermediateButton")){
		document.getElementById("sourceToIntermediateButton").style.display="none";
	}
	document.getElementById("intermediateTableList").innerHTML = "<h2 style=\"font-size:24px; margin:0px 0px 10px; padding:0px;\">Intermediate Tables</h2><div class=\"linespace\"></div><img src=\"dots64.gif\">";
	if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
		var xmlhttp=new XMLHttpRequest();
	}
	else{// code for IE6, IE5
		var xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	var url = "Create_Intermediate_Skeleton.php";
	var params = "whichSourceDB=" + thisDB + "&whichHost=" + curHost + "&whichUser=" + curUser + "&whichPword=" + curPword;
	xmlhttp.open("POST", url, true);
	//Send the proper header information along with the request
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Content-length", params.length);
	xmlhttp.setRequestHeader("Connection", "close");
	
	xmlhttp.onreadystatechange = function(){
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
			document.getElementById("intermediateTableList").innerHTML = "<h2 style=\"font-size:24px; margin:0px 0px 10px; padding:0px;\">Intermediate Tables</h2><div class=\"linespace\"></div>" + xmlhttp.responseText;
			closeRenameInterface();
			closeViewSourceTablesInterface();
		}
	}
	xmlhttp.send(params);
}

var stepDescription = ["nothing", "creating new hash-map", "copying existing csv-ready content", "separating kvs content", "deflating kvs submission and saved submission data", "Extracting answer text from quiz xml", "Separating submission data into tables by assessment type and user type (admin and students)", "Updating intermediate tables with small_public_user_id values"];
function fillIntermediateTables(thisDB, host, user, pword, thisStep){
	document.getElementById("intermediateTableList").innerHTML = "<h2 style=\"font-size:24px; margin:0px 0px 10px; padding:0px;\">Intermediate Tables</h2><div class=\"linespace\"></div><p>" + stepDescription[thisStep] + "</p><img src=\"dots64.gif\">";
	if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
		var xmlhttp=new XMLHttpRequest();
	}
	else{// code for IE6, IE5
		var xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	var url = "Fill_Intermediate_Tables.php";
	var params = "whichOriginalSourceDB=" + thisDB + "&whichHost=" + curHost + "&whichUser=" + curUser + "&whichPword=" + curPword + "&whichStep=" + thisStep;
	xmlhttp.open("POST", url, true);
	//Send the proper header information along with the request
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Content-length", params.length);
	xmlhttp.setRequestHeader("Connection", "close");
	
	xmlhttp.onreadystatechange = function(){
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
			document.getElementById("intermediateTableList").innerHTML = "<h2 style=\"font-size:24px; margin:0px 0px 10px; padding:0px;\">Intermediate Tables</h2><div class=\"linespace\"></div>" + xmlhttp.responseText;
		}
	}
	xmlhttp.send(params);
}

renameBox = "";
function openRenameInterface(item_type, item_id, item_name, thisEvent){
	if(renameBox == ""){
	renameBox = document.createElement("DIV");
	renameBox.id = "renameHere";
	renameBox.style.position = "absolute";
	renameBox.style.height = "150px";
	renameBox.style.width = "300px";
	renameBox.style.display = "none";
	renameBox.style.left = "0px";
	renameBox.style.top = "0px";
	renameBox.style.backgroundColor = "rgb(235,235,235)";
	renameBox.style.border = "10px solid rgb(205,205,205)";
	renameBox.style.borderRadius = "5px";

	rnbClose = document.createElement("P");
	rnbClose.id = "closeRenameInterface";
	rnbClose.innerHTML = "x";
	rnbClose.style.textAlign = "center";
	rnbClose.style.backgroundColor = "rgb(255,55,55)";
	rnbClose.style.width = "5%";
	rnbClose.style.margin = "5px 0px 10px 20px";
	rnbClose.style.paddingBottom = "3px";
	rnbClose.style.paddingLeft = "1xp";
	rnbClose.style.cursor = "pointer";
	rnbClose.style.borderRadius = "2px";
	rnbClose.onclick = function(){closeRenameInterface()};
	
	rnbInput = document.createElement("INPUT");
	rnbInput.id = "newIDinput";
	rnbInput.type = "text";
	rnbInput.style.width = "80%";
	rnbInput.style.margin = "30px 5px 0px 10px";

	rnbSubmit = document.createElement("BUTTON");
	rnbSubmit.innerHTML = "rename it";
	rnbSubmit.style.margin = "10px";
	
	rnbView = document.createElement("BUTTON");
	rnbView.innerHTML = "manipulate database content";
	rnbView.style.width = "80%";
	rnbView.style.margin = "10px";
	rnbView.style.display="none";
	rnbView.style.backgroundColor = "rgb(180,255,180)";
	
	renameBox.appendChild(rnbInput);
	renameBox.appendChild(rnbClose);
	renameBox.appendChild(rnbSubmit);
	renameBox.appendChild(rnbView);
	document.getElementById("content").appendChild(renameBox);
	}

	if(thisEvent.pageX){
		var pos_x = thisEvent.pageX + "px";
		var pos_y = thisEvent.pageY + "px";
	}
	else if (thisEvent.clientX){
		var pos_x = thisEvent.clientX + "px";
		var pos_y = thisEvent.clientY + "px";
	}

	if(item_type == "database"){rnbView.style.display="block"; rnbView.onclick = function(){viewDatabase(item_name)};}
	else {rnbView.style.display="none";}
	renameBox.style.left = pos_x;
	renameBox.style.top = pos_y;
	rnbSubmit.innerHTML = "rename " + item_type;
	rnbSubmit.onclick = function(){rename(item_type, item_id, item_name)};
	rnbInput.value = item_name;
	document.getElementById("renameHere").style.display = "block";
}

vstbClose = "";
function openSourceTableInterface(whichTable, whichDB, thisEvent){
	if(vstbClose == ""){
		vstbClose = document.createElement("P");
		vstbClose.id = "closeRenameInterface";
		vstbClose.innerHTML = "x";
		vstbClose.style.textAlign = "center";
		vstbClose.style.backgroundColor = "rgb(255,55,55)";
		vstbClose.style.width = "5%";
		vstbClose.style.margin = "5px 0px 10px 20px";
		vstbClose.style.paddingBottom = "3px";
		vstbClose.style.paddingLeft = "1xp";
		vstbClose.style.cursor = "pointer";
		vstbClose.style.borderRadius = "2px";
		vstbClose.onclick = function(){closeViewSourceTablesInterface()};
	
		vstbViewMeta = document.createElement("BUTTON");
		vstbViewMeta.style.width = "80%";
		vstbViewMeta.style.margin = "10px";
		vstbViewMeta.style.wordWrap = "break-word";
		vstbViewMeta.innerHTML = "";


		vstbViewTableInBrowser = document.createElement("BUTTON");
		vstbViewTableInBrowser.style.width = "80%";
		vstbViewTableInBrowser.style.margin = "10px";
		vstbViewTableInBrowser.style.wordWrap = "break-word";
		vstbViewTableInBrowser.innerHTML = "";
		
		vstbDownloadTable = document.createElement("BUTTON");
		vstbDownloadTable.innerHTML = "";
		vstbDownloadTable.style.width = "80%";
		vstbDownloadTable.style.margin = "10px";
		vstbDownloadTable.style.wordWrap = "break-word";
		vstbDownloadTable.style.backgroundColor = "rgb(180,255,180)";
	
		viewSourceTableBox = document.getElementById('viewTableHere');
		viewSourceTableBox.appendChild(vstbViewMeta);
		viewSourceTableBox.appendChild(vstbClose);
		viewSourceTableBox.appendChild(vstbViewTableInBrowser);
		viewSourceTableBox.appendChild(vstbDownloadTable);
		document.getElementById("content").appendChild(viewSourceTableBox);
	}

	if(thisEvent.pageX){
		var pos_x = thisEvent.pageX + "px";
		var pos_y = thisEvent.pageY + "px";
	}
	else if (thisEvent.clientX){
		var pos_x = thisEvent.clientX + "px";
		var pos_y = thisEvent.clientY + "px";
	}

	vstbViewMeta.innerHTML = "View meta info related to the:<br><b>`" + whichTable + "`</b> table";
	vstbViewMeta.onclick = function(){viewMeta(whichTable, whichDB)};
	vstbViewTableInBrowser.innerHTML = "View the <b>`" + whichTable + "`</b> table";
	vstbViewTableInBrowser.onclick = function(){viewTable(whichTable, whichDB)};
	vstbDownloadTable.innerHTML = "Download the <b>`" + whichTable + "`</b> table as a csv <span style=\"font-size:8px;\">(warning, this is an unaltered source table... so the csv downlaod may not be well formed and may contain private student data)</span>";
	vstbDownloadTable.onclick = function(){downloadTable(whichTable, whichDB)};
	viewSourceTableBox.style.left = pos_x;
	viewSourceTableBox.style.top = pos_y;
	document.getElementById("viewTableHere").style.display = "block";
}

function viewMeta(thisTable, thisDB){
	// alert("view meta in browser: " + thisTable + " " + thisDB);
	viewTablesDiv = document.getElementById("view_meta_or_tables");
	viewTablesDiv.innerHTML = "<img src=\"dots64.gif\" alt=\"loading\">";
	viewTablesDiv.style.display = "block";
	closeViewSourceTablesInterface();
	if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
		var xmlhttp=new XMLHttpRequest();
	}
	else{// code for IE6, IE5
		var xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	var url = "C_T_CSV_Table_Info.php";
	var params = "whichTable=" + thisTable + "&whichDB=" + thisDB;
	xmlhttp.open("POST", url, true);
	//Send the proper header information along with the request
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Content-length", params.length);
	xmlhttp.setRequestHeader("Connection", "close");
	
	xmlhttp.onreadystatechange = function(){
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
			viewTablesDiv.innerHTML = xmlhttp.responseText;
		}
	}
	xmlhttp.send(params);
}
function viewTable(thisTable, thisDB){
	// alert("view in browser: " + thisTable + " " + thisDB);
	viewTablesDiv = document.getElementById("view_meta_or_tables");
	viewTablesDiv.innerHTML = "<img src=\"dots64.gif\" alt=\"loading\">";
	viewTablesDiv.style.display = "block";
	closeViewSourceTablesInterface();
	if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
		var xmlhttp=new XMLHttpRequest();
	}
	else{// code for IE6, IE5
		var xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	var url = "C_T_CSV_View_Table.php";
	var params = "whichTable=" + thisTable + "&whichDB=" + thisDB;
	xmlhttp.open("POST", url, true);
	//Send the proper header information along with the request
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Content-length", params.length);
	xmlhttp.setRequestHeader("Connection", "close");
	
	xmlhttp.onreadystatechange = function(){
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
			document.getElementById("view_meta_or_tables").innerHTML = xmlhttp.responseText;
		}
	}
	xmlhttp.send(params);
}
function downloadTable(thisTable, thisDB){
	closeViewSourceTablesInterface();
	if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
		var xmlhttp=new XMLHttpRequest();
	}
	else{// code for IE6, IE5
		var xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	var url = "C_T_CSV_Download_Table.php";
	var params = "whichTable=" + thisTable + "&whichDB=" + thisDB;
	xmlhttp.open("POST", url, true);
	//Send the proper header information along with the request
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.setRequestHeader("Content-length", params.length);
	xmlhttp.setRequestHeader("Connection", "close");
	
	xmlhttp.onreadystatechange = function(){
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
			var what_file = xmlhttp.responseText;
			window.open(what_file);
		}
	}
	xmlhttp.send(params);
}
