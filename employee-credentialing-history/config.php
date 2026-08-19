<?php

session_start();

	// access key to the credentialing backup functions
$CorrectKey = "crlf64";

	// key not passed neither as session or as post, that means no authorization and just quit
if ((!(isset($_POST['key']))) && (!(isset($_SESSION['key']))))
{
	echo "Access denied.";
	exit;
}

	// START - check for correct key in SESSION or in POST
	
$CorrectKeyInPOST = false;
$CorrectKeyInSESSION = false;

if (isset($_POST['key']))
{
	if ($_POST['key'] == $CorrectKey)
	{
		$CorrectKeyInPOST = true;
			
			// set session variables when the Backup application is initially called
		$_SESSION['key'] = $_POST['key'];
		$_SESSION['customerID']= $_POST['customerID'];
		$_SESSION['ecafid']= $_POST['ecafid'];
	}
}

if (isset($_SESSION['key']))
{
	if ($_SESSION['key'] == $CorrectKey)
	{
		$CorrectKeyInSESSION = true;
	}
}

if ((!$CorrectKeyInPOST) && (!$CorrectKeyInSESSION))
{
	echo "Access denied.";
	exit;
}

// END - check for correct key


$CustomerID = $_SESSION['customerID'];
	
	// DB setup
if ($_SERVER["HTTP_HOST"] == "hris.me")	// local
{
	$DB = new mysqli("localhost", "root", "", "real");
}

if	($_SERVER["HTTP_HOST"] == "humanroad.ferndaleroad.com") // online
{
	if ($CustomerID == 1)
	{
		$DB = new mysqli("localhost", "humanroadferndaleroadcom_userb1", "Hris-1-userr", "humanroadferndaleroadcom_mlm");
	}
}

	// directories
$CRED_FILES_DIR = $_SERVER["DOCUMENT_ROOT"] . "/add/customer" . $CustomerID . "/uploads/credentialings";
$CredFilesBackupDir = $_SERVER["DOCUMENT_ROOT"] . "/employee-credentialing-backup/credentialing-files/customer" . $CustomerID;

	// other settings
$CredFilePrefix = "credentialing_";

?>