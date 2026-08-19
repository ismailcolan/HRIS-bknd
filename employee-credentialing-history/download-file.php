<?php
	
	// credentialing files are in format: credentialing_0000
	// where 0000 ID the "ecID" of the credentialing
	// this is passed as a GET parameter;
	// then I connect to the DB table "employee_credentialing" and get the actual filename - $DownloadFilename, under
	// which name I download it to the user computer
	
	include ("config.php");
	
	global $DB, $CRED_FILES_DIR, $CredFilePrefix;
	
	
		// SETTINGS
	$CredentialingDir = $CRED_FILES_DIR;
	$CredentialingFilesPrefix = $CredFilePrefix;
	
		// read the credentialing file ID
	$ecID = $_GET["ecid"];
	
			// Set the file source name (format: "credentialing_0000")
	$SourceFilename = $CredentialingDir . "/" . $CredentialingFilesPrefix . $ecID;
	
		// connect to the DB and get the actual Filename (e.g. "my-document.pdf")
	$Statement = $DB->prepare ("SELECT ecID, ecFileName FROM employee_credentialing	WHERE (ecID = ?)");
	$Statement->bind_param("i", $ecID);
	$Statement->execute();
	$Result = $Statement->get_result();
	
	$ResultArray = $Result->fetch_array();
	$DownloadFilename = $ResultArray["ecFileName"];
	
	// print_r ($_SERVER);exit; // debug
	// print($SourceFilename); exit; // debug
	
	
		// download file to user's computer
	if (file_exists($SourceFilename))
	{
			header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
			header("Cache-Control: public"); // needed for Internet Explorer
			header("Content-Type: application/octet-stream");	// file type unknown
			header("Content-Transfer-Encoding: Binary");
			header("Content-Length:".filesize($SourceFilename));
			header("Content-Disposition: attachment; filename=" . $DownloadFilename);
			readfile($SourceFilename);
			exit();        
	}
	else
	{
			exit("Error: File not found.");
	} 
				
?>