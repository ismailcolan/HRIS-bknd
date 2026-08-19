<?php

	// read credentialings of an employee and shows them in table format

	include ("config.php");
	include ("templates.php");
	
	global $DB, $CredFilesBackupDir, $CredFilePrefix;
	
	if (isset ($_SESSION["ecafid"]))
	{
		$ecafID = $_SESSION["ecafid"];	// employee ID
	}
	
	
	

	// ------------------------------------------------------------------------------------------
	// generate ordering link
function OrderLink ($Type)
{
	
		$ecafID = "";
		
		if (isset ($_POST["ecafid"]))
		{
			$ecafID = $_POST["ecafid"];
		}
		
		if (isset ($_GET["ecafid"]))
		{
			$ecafID = $_GET["ecafid"];
		}
		
	
		$s = $_SERVER["SCRIPT_NAME"] . "?ecafid=" . $ecafID;
		$s .= "&ord_type=" . $Type;
		
		$OrderDir = "asc";
		
			// if currently oredered by that type/criteria, check if it's ascending ($_GET["ord_dir"] == "asc")
			// if yes - order link must be descending, otherwise remains ascending
		if (isset($_GET["ord_type"]))
		{
			if ($_GET["ord_type"] == $Type)
			{
				if (isset($_GET["ord_dir"]))
				{
					if ($_GET["ord_dir"] == "asc")
					{
						$OrderDir = "desc";
					}
				}
			}
		}
		else // default
		{
			$OrderDir = "desc";
		}
		
		$s .= "&ord_dir=" . $OrderDir;
		
		return $s;
		
} // function	
	

	// ----------------------------------------------------------------------------------------------
	// show date in US format; $s must be in UTF format, date only, e.g.: 2025-01-10
	
function DateUS ($s)
{

	$Date = date_create($s);
	//return ($s); // debug
	return date_format($Date, "n/j/y g:i:s A");

} // function


	// --------------------------------------------------------------------------------------------------------------------
function ShowDataHeaders ($ResultArray, $OrderIcons)
{
	?>
	
		<div class="employee-name">
			<label>Employee:</label> <?= $ResultArray["afFirstName"] . " " . $ResultArray["afLastName"] ?>
			
			<!--
			<a href="https://humanroad.ferndaleroad.com/admin/employee-details?id=<?= $ResultArray["ecAFID"] ?>"
										target="blank"><?= $ResultArray["afFirstName"] ?> <?= $ResultArray["afLastName"] ?></a>
			-->
		</div>
				
			
		<div><!-- table header -->
			<div class="column headercolumn column-header col-width-long">
				<a href="<?= OrderLink("cred_name") ?>">Credentialing</a> <?= $OrderIcons["cred_name"] ?>
			</div>
			<div class="column column-header col-width-long">
				<a href="<?= OrderLink("filename") ?>">Filename</a> <?= $OrderIcons["filename"] ?>
			</div>
			<div class="column column-header col-width-mid">
				<a href="<?= OrderLink("uploaded") ?>">Uploaded</a> <?= $OrderIcons["uploaded"] ?>
			</div>
			<div class="column column-header col-width-mid">
				<a href="<?= OrderLink("due_date") ?>">Due date</a> <?= $OrderIcons["due_date"] ?>
			</div>
			<div class="column column-header col-width-mid">
				<a href="<?= OrderLink("expire") ?>">Expire</a> <?= $OrderIcons["expire"] ?>
			</div>
		<div>
	<?php
	
} // function


	// -----------------------------------------------------------------------------------------------------------------------
function ShowDataRows($ResultArray, $OddOrEven)
{
	
	?>
	
		<div class="row <?= $OddOrEven ?>"><!-- table row -->
			
				<div class="column col-width-long">
					<a href="https://humanroad.ferndaleroad.com/admin/credentialing-details?id=<?= $ResultArray["ecCID"] ?>"
					target="blank"><?= $ResultArray["cName"] ?></a>
				</div>
			
				<div class="column col-width-long">
					<a href="download-file.php?ecid=<?= $ResultArray["ecID"] ?>" target="blank"><?= $ResultArray["ecFileName"] ?></a>
				</div>
			
				<div class="column col-width-mid">
					<?= DateUS($ResultArray["ecUploadedOn"]) ?>
				</div>
			
				<div class="column col-width-mid">
					<?= DateUS($ResultArray["ecDueDate"]) ?>
				</div>
			
				<div class="column col-width-mid">
					<?= DateUS($ResultArray["ecExpiredDate"]) ?>
				</div>

				<!--
				<div class="column col-width-short">
					<a href="delete.php?ecid=<?= $ResultArray["ecID"] ?>" 
					onclick="return(confirm('Are you sure you want to delete this credentialing?'))">delete</a>
				</div>
				-->
				
			</div>

	<?php

} // function




// --------------------------------------------------------------------------------------------------------------------
// MAIN CODE
// --------------------------------------------------------------------------------------------------------------------

	ShowHeader();
	
	
	
		// ----------------------------------------------------------------------------------------------------------------
		// SET ORDERING PARAMETERS
		// example: ?ot=uploaded&od=asc - means: order type = by upload date, order direction = ascending
		// ----------------------------------------------------------------------------------------------------------------
		
	$OrderType = "ecUploadedOn";
	$OrderDir = "ASC";	// Dir = direction
	
	$OrderIcons = [];
	$OrderIcons ["uploaded"] = "";
	$OrderIcons ["cred_name"] = "";
	$OrderIcons ["filename"] = "";
	$OrderIcons ["due_date"] = "";
	$OrderIcons ["expire"] = "";
	
	
	$Arrow = "";
	$ArrowUp = "&#11205;";
	$ArrowDown = "&#11206;";
	
	
		// if order direction GET parameter passed	
	if (isset($_GET["ord_dir"]))	// order direction
	{
		if ($_GET["ord_dir"] == "desc")
		{
			$OrderDir = "DESC";
			$Arrow = $ArrowDown;
		}
		else
		{
			$OrderDir = "ASC";
			$Arrow = $ArrowUp;
		}
	}
	else // default
	{
		$Arrow = $ArrowUp;
	}
		
		// if order type GET parameter passed
	if (isset($_GET["ord_type"]))
	{
		
		switch ($_GET["ord_type"])
		{
			case "uploaded":
				$OrderType = "ecUploadedOn";
				break;
				
			case "cred_name":
				$OrderType = "cName";
				break;
		
			case "filename":
				$OrderType = "ecFileName";
				break;
		
			case "due_date":
				$OrderType = "ecDueDate";
				break;
			
			case "expire":
				$OrderType = "ecExpiredDate";
				break;
		
		} // switch
		
		$OrderIcons [$_GET["ord_type"]] = $Arrow;
		
	}	// if
	else // default
	{
		$OrderIcons ["uploaded"] = $Arrow;
	}
		
	// ----------------------------------------------------------------------------------------------------------------
	// END SET ORDERING PARAMETERS
	// ----------------------------------------------------------------------------------------------------------------

	
		
	// GET CREDENTIALING DATA
	// ----------------------------------------------------------------------------------------------------------------
	
	$Statement = $DB->prepare
		("SELECT * FROM employee_credentialing, credentialings, apply_forms
			WHERE (ecAFID = afID) AND (ecCID = cID) AND (ecAFID = ?) AND (ecFileName IS NOT NULL) AND (ecFileName <> '') 
			ORDER BY $OrderType $OrderDir");
	
	$Statement->bind_param("i", $ecafID);
	$Statement->execute();
	$Result = $Statement->get_result();
	
	$ResultArray = [];
	
	$RowsReturned = 0;
	$CSSRowOddOrEven = "";
	
	
	// DISPLAY CREDENTIALING DATA
	// ----------------------------------------------------------------------------------------------------------------
	
	while ($ResultArray = $Result->fetch_array())
	{
		$RowsReturned++;

		if ($RowsReturned % 2)
		{
			$CSSRowOddOrEven = "row-odd";
		}
		else
		{
			$CSSRowOddOrEven = "row-even";
		}
		
			// use first iteration ot show employee name and table headers
		if ($RowsReturned == 1)
		{
			ShowDataHeaders ($ResultArray, $OrderIcons);
			
		}
			
		ShowDataRows ($ResultArray, $CSSRowOddOrEven);
		
	} // while	- display credentialing data
	
	
	if ($RowsReturned == 0)
	{
		?>
			No credentialing files found for that employee.
		<?php
	} // if
	
	
	ShowFooter();
	
?>