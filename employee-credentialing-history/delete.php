<?php

	include ("config.php");
	include ("templates.php");
	
	global $DB, $CredFilesBackupDir, $CredFilePrefix;
	
	$ecID = $_GET["ecid"];	// employee ID

	ShowHeader();
	
		// delete credentialing
	$Statement = $DB->prepare ("DELETE FROM employee_credentialing_backup	WHERE ecID = ?");
	$Statement->bind_param("i", $ecID);
	$Statement->execute();
	
		// check what has been deleted and show a message
	$Message = "";
	
	switch ($DB->affected_rows)
	{
		case 1:
			$Message = "Backup credentialing deleted.";
			break;
		
		case 0:
			$Message = "Error - nothing deleted.";
			break;
		
		default:
			$Message = "Error - more than 1 backup credentialing deleted.";
			break;
	
	} // switch
	
	$FileToDelete = $CredFilesBackupDir . "/" . $CredFilePrefix . $ecID;
	 //echo "CredFilesBackupDir:" . $CredFilesBackupDir . "<br>";
	 //echo "FileToDelete: " . $FileToDelete; exit;
	
	if (file_exists($FileToDelete))
	{
		if (unlink($FileToDelete))
		{
			$Message .= " Backup file deleted.";
		}
		else
		{
			$Message .= " Error deleting backup file.";
		}
	}
	else
	{
		$Message .= " Backup file to be deleted not found.";
	}
	
	$Note = "Note: if you haven't deleted the original credentialing, it will be restored in this backup
	with the next credentialing upload (even if it's of another employee).";
	
	?>
	
		<div class="message">
			
			<div>
				<?= $Message ?><br>
				<a href="<?= $_SERVER["HTTP_REFERER"] ?>">&laquo; Back</a> to backup credentialing files
			</div>
			
			<div class="note">
					<?= $Note ?>
			</div>
			
		</div>
	
		
	<?php
	
	ShowFooter();

?>