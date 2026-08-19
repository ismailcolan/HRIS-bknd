<?php
	
	//print_r($_SERVER); // debug
	
	$LogFile = "update-script.log";
	//$s = "\r\n\r\n" . date("d.m.Y H:i:s") . "\r\n" . "update script STARTED";
	//file_put_contents($LogFile, $s , FILE_APPEND);
	
		
		// compare backup DB table to the original one, import any new records and copy their files

	include ($_SERVER["DOCUMENT_ROOT"] . "/employee-credentialing-backup/config.php");
	
	// include ("templates.php");
	
	
	

// --------------------------------------------------------------------------------------------------------------------
// MAIN CODE
// --------------------------------------------------------------------------------------------------------------------

	// ShowHeader();
		
		// select ecID's of the ORIGINAL DB table and push them into an array ($ArrayIDsOriginal)
	$Statement = $DB->prepare ("SELECT ecID FROM employee_credentialing");
	$Statement->execute();
	$Result = $Statement->get_result();
	
	$ResultArray = [];
	$ArrayIDsOriginal = [];
	
	while ($ResultArray = $Result->fetch_array())
	{
		$ArrayIDsOriginal[] = $ResultArray["ecID"];
	}
	
		// select ecID's of the BACKUP DB table and push them into an array ($ArrayIDsBackup)
	$Statement = $DB->prepare ("SELECT ecID FROM employee_credentialing_backup");
	$Statement->execute();
	$Result = $Statement->get_result();
	
	$ResultArray = [];
	$ArrayIDsBackup = [];
	
	while ($ResultArray = $Result->fetch_array())
	{
		$ArrayIDsBackup[] = $ResultArray["ecID"];
	}
	
		// create an array with the ecIDs missing in the backup DB table
	$NewRecordsArray = array_diff($ArrayIDsOriginal, $ArrayIDsBackup);
		
		// if no new records, just exit
	if (count ($NewRecordsArray) == 0)
	{
		//$s = "\r\n" . "update script COMPLETED";
		//file_put_contents($LogFile, $s , FILE_APPEND);
		exit;
	}
	
		// generate string "?, ?, ?" with as many ? as the elements count in $NewRecordsArray
		// that will be used in the SQL query below
	$QuestionMarksString = str_repeat("?, ", count ($NewRecordsArray));
	$QuestionMarksString = substr($QuestionMarksString, 0, strlen($QuestionMarksString) - 2); // remove trailing ", "
	
		// in a similar fashion generate a string "iii" with as many i's as the elements count in $NewRecordsArray
		// again used for the "bind_param" call below
	$VariableTypesString = str_repeat("i", count($NewRecordsArray));
	
		// select the missing records from the original DB table; here we use the above-generated $QuestionMarksString
	$Statement = $DB->prepare ("SELECT * FROM employee_credentialing WHERE ecID IN ($QuestionMarksString)");
	
		// and here we use the above-generated $VariableTypesString
		// the "..." notation breaks array into single values, e.g. [1, 2, 3] becomes: 1, 2, 3
	$Statement->bind_param($VariableTypesString, ...$NewRecordsArray);
	
	$Statement->execute();
	$Result = $Statement->get_result();
	
	$NewRecordsArray = [];
	
	
		// $NewRecordsArray will be the array with records present in the original DB table and missing in the backup one
		// those needs to be copied to the backup one
		// the "while" loop below does that, one record at a time (in each loop)
	while ($NewRecordsArray = $Result->fetch_array())
	{
		
		// Extract only associative keys (which are DB table column names) and genarate an array with them
		$KeysArray = array_filter(array_keys($NewRecordsArray), 'is_string');
		$KeysString = implode (", ", $KeysArray);
		
		
		$ValuesArray = array_intersect_key($NewRecordsArray, array_flip($KeysArray));
		$ValuesString = implode (", ", $ValuesArray);
		$ValuesString = substr($ValuesString, 0, strlen($ValuesString) - 2); // remove trailing ", "
		
		
			// generate string "?, ?, ?" with as many ? as the elements count in $NewRecordsArray
			// that will be used in the SQL query below
		$QuestionMarksString = str_repeat("?, ", count ($ValuesArray));
		$QuestionMarksString = substr($QuestionMarksString, 0, strlen($QuestionMarksString) - 2); // remove trailing ", "
		
		$VariableTypesString = "iiisssssssssi";
		
			// insert the missing records into the original DB table; here we use the above-generated $QuestionMarksString
			// missing records are inserted one by one
		$Statement = $DB->prepare ("INSERT INTO employee_credentialing_backup ($KeysString) VALUES ($QuestionMarksString)");
		
			// convert $NewRecordsArray into indexed array
		$ValuesArray = array_values($NewRecordsArray);
		
			// however the above-made indexed array $ValuesArray contains each value twice, e.g.: 1, 1, 2, 2, 3, 3
			// to fix this I get only every odd value
		$UniqueValuesArray = [];
		$i = 0;
		foreach ($ValuesArray as $Value)
		{
			$i++;
			if ($i % 2)
			{
				$UniqueValuesArray[] = $Value;
			}
		} // foreach
		
	

			// and here we use the above-generated $VariableTypesString
			// the "..." notation breaks array into single values, e.g. [1, 2, 3] becomes: 1, 2, 3
		$Statement->bind_param($VariableTypesString, ...$UniqueValuesArray);
		
		
		
			// insert missing record into the BACKUP database table
		$Statement->execute();
		$ResultInsert = $Statement->get_result();	
			
			
			// Done inserting missing records; now copy the missing files
		
			// settings
		$SourceDir = $CRED_FILES_DIR;
		$DestinatonDir = $CredFilesBackupDir;
		$FilenamePrefix = $CredFilePrefix;
		
			// copy
		$SourceFilename = $SourceDir . "/" . $FilenamePrefix . $NewRecordsArray["ecID"];
		$DestinationFilename = $DestinatonDir . "/" . $FilenamePrefix . $NewRecordsArray["ecID"];
		copy($SourceFilename, $DestinationFilename);
		
		//echo "SourceFilename = '$SourceFilename'" . "<br>";
		//echo "DestinationFilename = '$DestinationFilename'" . "<br>";
		
		// done updating the current record, moving to the next one
		
	} // while - copying from the original DB table records missing in the backup one
	
	// ShowFooter();
	//$s = "\r\n" . "update script COMPLETED";
	//file_put_contents($LogFile, $s , FILE_APPEND);
	
?>