<?php

/** The EmployeeOffice model */
class EmployeeCredentialingModel extends AppModel
{

    static $dataAddons = array(), $dt = array("tbl" => "employee_credentialing", "key" => "ecID", "pfx" => "eo", "controller" => "");
    static $mainTblFields = array("ecAFID", "ecCID", "ecExpiredDate", "ecDueDate", "ecUploadedOn", "ecFileName", "ecActive");
    static $fldLbl = array("ecAFID" => "Employee", "ecCID" => "Credentialing", "ecDueDate" => "Due Date", "ecUploadedOn" => "Uploaded On",
        "ecFileName" => "File", "eoActive" => "Status");
    static $postOverrided = array();


    static function query($conditions = '', array $orderby = array(), $limit = 0)
    {
        $query = "SELECT *                           
                  FROM " . self::$dt["tbl"] . " left join credentialings on cID = ecCID ";
        $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);

        $addons = array();

        return str_replace("*", (empty($addons) ? "*" : ("*,\n" . implode(",\n", $addons))), $q);
    }
	
	 public static function CheckEmployeeCredentialings($afID )
    {
       
		 $today  = val::currentDate(MYSQL_DATE_TIME);
		 
		 $q = "select * from employee_credentialing
			left join  credentialings   ON  cID  = ecCID
			where 
			(ecDueDate < '{$today}' and cType ='recurring' and ecUploadedOn IS NULL
			and cActive='Yes' and ecAFID = {$afID}
			)
			XOR
			( ecDueDate < '{$today}' and cType = 'one-time' and ecUploadedOn IS NULL
			and cActive='Yes' and ecAFID = {$afID}
			)";

		
		
		 $data = db::PrepareLoad($q);
		 
        
			 
        return ! empty($data);
		
		
    }
	
	 public static function LoadEmployeeCredentialings($afID, $cond = "", 
								$order = " ecUploadedOn ASC, ecDueDate DESC",$due='',$mode='completed')
    {
       if (! $cond =='') $cond = ' and '.$cond;
       if (! $due =='') $due .=' and ';
	   
	   if ($mode=='admin'){
			$q = "select * from employee_credentialing
				LEFT join  credentialings   ON  ecCID=cID  
				where 
				( ".$due." cType ='recurring' ".$cond." and cActive='Yes' and ecAFID = {$afID})
				XOR
				( cType = 'one-time' ".$cond."and cActive='Yes' and ecAFID = {$afID})
				";
				//echo $q;
		}
		
		if ($mode=='completed'){
			$q = "select * from employee_credentialing
				left join  credentialings   ON  cID  = ecCID
				where 
				( 
				  (
				   (".$due." cType ='recurring' ".$cond.") 
				   
				  )
				  AND
				  (cActive='Yes' and ecAFID = {$afID})
				)
				XOR
				(
				( cType = 'one-time' ".$cond." and cActive='Yes' and ecAFID = {$afID}  )
				)";
		}

//if ($mode=='completed') echo $q.'<hr>';
		
		if ($mode=='pending'){
			$q = "select * from employee_credentialing
				left join  credentialings   ON  cID  = ecCID
				WHERE 
				(
					(
						(ecIsApplicableForMe = 'yes' and  ecFileName IS NULL )
						
						AND (
							NOT (EXISTS( 
										SELECT * FROM  employee_credentialing  
										WHERE ecAFID = {$afID} AND ecCID = cID AND ecIsApplicableForMe='no'
									)
							)
						)
					)
					OR  
							( cType ='recurring' and cActive='Yes' and ecAFID = {$afID} and 
							  ecUploadedOn IS NULL and ecFileName IS NOT NULL ) 
				)
				AND
				
				( 	
						(".$due." cType ='recurring' ".$cond."  and cActive='Yes' and ecAFID = {$afID})
					OR 
						(cOldType = 'one-time' and cActive='Yes' and ecAFID = {$afID}
						and ecUploadedOn IS NOT NULL and cID = ecCID )
					
						
				)
				XOR
				(
					( cType = 'one-time' ".$cond."and cActive='Yes' and ecAFID = {$afID} )
				AND
					NOT ( 
						cOldType = 'recurring' and cActive='Yes' and ecAFID = {$afID}
					AND 
						EXISTS( 
							SELECT * FROM  employee_credentialing  
							WHERE ecAFID = {$afID} AND ecCID = cID AND ecUploadedOn IS NOT NULL 
						)
					)
				)";	
		}
		
/***********

XOR
	(
		( cType = 'one-time' ".$cond."and cActive='Yes' and ecAFID = {$afID} )
	AND
		NOT ( 
			cOldType = 'recurring' and cActive='Yes' and ecAFID = {$afID}
		AND 
			EXISTS( 
				SELECT * FROM  employee_credentialing  
				WHERE ecAFID = {$afID} AND ecCID = cID AND ecUploadedOn IS NOT NULL 
			)
		)
	)";		
***********/
//if ($mode=='pending') echo $q.'<br><br>';
   		 $data = db::PrepareLoad($q);

        if ($data === false) {
            $data = [];
        } else {
            foreach ($data as &$item) {
                $item["cName"] = htmlspecialchars($item["cName"]);
            }
        }
        //print ($q);
        $Processed = SkipUloadedCredentials($data);
        
        return ($Processed);
        
    }


    public static function LoadEmployeeCredentialingsOLD($afID, $cond = "", $order = " ecUploadedOn ASC, ecDueDate DESC")
    {
        $afid = intval($afID);
        $q = "select *                  
              from `" . self::$dt['tbl'] . "`
              left join `credentialings` on `cID` = `ecCID` 
              where `ecAFID` = " . $afid;

        if (!empty($cond)) {
            $q .= " and " . $cond;
        }

        if (!empty($order)) {
            $q .= " order by " . $order;
        }

        if (!empty($cond)) {
            $cond = " and " . $cond;
        }
        $q =
'select * from (SELECT * FROM `employee_credentialing` LEFT JOIN `credentialings` on cID=ecCID WHERE ecAFID='.$afid.' and ecUploadedOn is null and DATE(ecDueDate) < CURRENT_DATE '.$cond.' order by ecDueDate ASC) as a
union all
select * from (SELECT * FROM `employee_credentialing` LEFT JOIN `credentialings` on cID=ecCID WHERE ecAFID='.$afid.' and ecUploadedOn is null and DATE(ecDueDate) >= CURRENT_DATE '.$cond.' order by ecDueDate ASC) as b
union all
select * from (SELECT * FROM `employee_credentialing` LEFT JOIN `credentialings` on cID=ecCID WHERE ecAFID='.$afid.' and ecUploadedOn is not null '.$cond.' order by ecUploadedOn DESC) as c';

        $data = db::PrepareLoad($q);
        if ($data === false) {
            $data = [];
        } else {
            foreach ($data as &$item) {
                $item["cName"] = htmlspecialchars($item["cName"]);
            }
        }
		 
        return $data;
		
		
    }

    public static function AssignEmployeeCredentialings($afID, $afHiredForPID, $startDate)
    {
        $q = "SELECT * FROM credentialings LEFT JOIN credentialing_positions ON cID=cpCID WHERE cActive='Yes' AND cpPID=" . $afHiredForPID;
		
        $tasks = db::PrepareLoad($q);
		
        if(is_array($tasks)) {
            foreach ($tasks as $task) {
                $task["ecExpiredDateObj"] = $startDate;
                self::AddRecord($afID, $task);
            }
        }
		
		
    }

    public static function SetCredentialRenewalDate($ecID, $newDate,$emptyFileName)
    {
        if (isset(AppModel::$usr["afID"]) && !empty(AppModel::$usr["afID"])) {
			  $q = "select * from employee_credentialing WHERE ecID={$ecID}";
			  $emp_cred =db::PrepareLoadSingleRow($q);
			  
			  $q = "select * from credentialings WHERE cID={$emp_cred['ecCID']}";
			  $data = db::PrepareLoadSingleRow($q);
			  
			  
			  $newDate_ms = DateTime::createFromFormat(USER_DATE, $newDate);
            $timeframe = $data["cTimeFrameNum"].' '.$data["cTimeFrameDim"];
            
		  
            if($newDate_ms) {
					$today = new DateTime();
					
					$newDueDate_ms = DateTime::createFromFormat(USER_DATE, $newDate);
					$newDueDate_ms->add(date_interval_create_from_date_string($timeframe));
					
				
					
                if ($newDate_ms->format(MYSQL_DATE_TIME)  > $today->format(MYSQL_DATE_TIME)) {
                    if ($emptyFileName=='yes'){
							$q = 'update employee_credentialing set 
									ecExpiredDate="%s", ecDueDate="%s",ecFileName=NULL 
									where ecID=%d and ecAFID=%d';
									
							$q = sprintf($q,
								$newDate_ms->format(MYSQL_DATE_TIME) 
							   ,$newDueDate_ms->format(MYSQL_DATE_TIME)
								//$today->format(MYSQL_DATE_TIME)
							   ,$ecID
							   ,AppModel::$usr["afID"]);
						}
						else {
						   $q = 'update employee_credentialing set 
						   ecExpiredDate="%s", ecDueDate="%s" 
						   where ecID=%d and ecAFID=%d';
						   
						   $q = sprintf($q,
						  $newDate_ms->format(MYSQL_DATE_TIME) 
						   ,$newDueDate_ms->format(MYSQL_DATE_TIME) 
						   , $ecID
						   ,AppModel::$usr["afID"]);
						}
					   
					   db::SQL($q);
					   
					   msg::put("Date was changed.", "success");
                } else {
                    msg::put("Expiration/renewal can not be in the past.", "error");
                }
            } else {
                msg::put($newDate . " is not a valid date. Please enter date in \"m/d/y\" format.", "error");
            }
        }
    }
	

    public static function SaveCredentialingFile()
    {
        $target_dir = CREDENTIALING_UPLOAD_DIR;
        $fileName = basename($_FILES[CREDENTIALING_FIELD_NAME]["name"]);
        $error = $_FILES[CREDENTIALING_FIELD_NAME]["error"];
		
        if (($error > 0) || (empty($_FILES[CREDENTIALING_FIELD_NAME]["tmp_name"]))) {
            msg::set("Sorry, there was an error uploading your file.", "error");
            return false;
        }

        if ($_FILES[CREDENTIALING_FIELD_NAME]["size"] > CREDENTIALING_MAX_FILE_SIZE) {
            msg::set("File too big. Maximum allowed is ".round(CREDENTIALING_MAX_FILE_SIZE / 1048576)."Mb.", "error");
            $error = 1;
        }

        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($fileType, explode(',', CREDENTIALING_EXT_ALLOWED))) {
            msg::set("Sorry, only PDF, DOC, DOCX, XLS, XLSX, JPG, PNG files are allowed." . $fileType, "error");
            $error = 1;
        }

        if ($error === 0) {
            $ecID = filter_input(INPUT_POST, "ecID", FILTER_SANITIZE_NUMBER_INT);
            $data = EmployeeCredentialingModel::dataById($ecID);

            if ($data) {

                if ($data["ecAFID"] != AppModel::$usr["afID"]) {
                    msg::set("Sorry, there was an error uploading your file.", "error");
                    return false;
                }

                if ($data["cType"] === "recurring") {
                    val::init("server");
                    if (val::required("credentialingExpDate", "") && val::validDate("credentialingExpDate")) {
                        $data["ecExpiredDateObj"] = DateTime::createFromFormat(USER_DATE, filter_input(INPUT_POST, "credentialingExpDate", FILTER_SANITIZE_STRING));
                        if ($data["ecExpiredDateObj"]) {
                            $now = new DateTime();
                            if($data["ecExpiredDateObj"] < $now) {
                                msg::set("Expiration/renewal can not be in the past.", "error");
                                return false;
                            } else {
                            }
                        } else {
                            msg::set("Sorry, there was an error uploading your file.", "error");
                            return false;
                        }
                    } else {
						
						    if ($data['cHasExpirationDate'] !='no'){
								msg::set("Sorry, there was an error uploading your file.", "error");
								return false;
							}
                    }
                }
                
                //ini_set('error_reporting', E_ALL);
                //ini_set('display_errors', '1');
                
                $targetFileName = CREDENTIALING_FILE_NAME . $data["ecID"];
                if (move_uploaded_file($_FILES[CREDENTIALING_FIELD_NAME]["tmp_name"], $target_dir . $targetFileName)) {
                    $qFormat = 'update ' . self::$dt["tbl"] . ' set ecFileName="%s", ecUploadedOn=CURRENT_TIMESTAMP where ecID=%d';
                    $q = sprintf($qFormat, $fileName, $data["ecID"]);
                    db::SQL($q);

                    if($data["cType"] === 'recurring') {
                        self::AddRecord($data["ecAFID"], $data, true);
                    }

                    msg::set('File uploaded successfully!', 'success');
                    
                        // call Credentialing Backup update script i.e.
                        // copy credentialing TO BACKUP folder and BACKUP DB table
                    //$_SESSION['key'] = ""; // WRITE THE KEY HERE
                    //include($_SERVER["DOCUMENT_ROOT"] . "/employee-credentialing-backup/update.php");
                    
                    
                } else {
                    msg::set("Sorry, there was an error uploading your file.", "error");
                    return false;
                }
            }
        }

        return true;
    }

    public static function AddRecord($afID, $data, $skipCheck = false) {
	
		if ($data['cHasExpirationDate'] =='no') {
			$ti=  $data['cRenewalTimeFrameNum'].' '.$data['cRenewalTimeFrameDim'];		
			$date = new DateTime();
			$date->add(date_interval_create_from_date_string($ti));
			$data["ecExpiredDate"] = $date->format(MYSQL_DATE_TIME); 
			$expDate = strtotime($data["ecExpiredDate"], time());
			$timeframe = $expDate;
		} else {
			if(isset($data["ecExpiredDateObj"]) && (!empty($data["ecExpiredDateObj"]))) {
				$data["ecExpiredDate"] = $data["ecExpiredDateObj"]->format(MYSQL_DATE_TIME);//GLOBAL_PHP_DATETIME);
				$expDate = strtotime($data["ecExpiredDate"], time());
				  
				$timeframe = $expDate;
			}
			else {
				$data["ecExpiredDate"] = null;
				$timeframe = time();
			}
		}	
		
        switch ($data["cTimeFrameDim"]) {
            case 'day(s)':
                $timeframe = strtotime("+{$data["cTimeFrameNum"]} days", $timeframe);
                break;
            case 'week(s)':
                $timeframe = strtotime("+{$data["cTimeFrameNum"]} weeks", $timeframe);
                break;
            case 'month(s)':
                $timeframe = strtotime("+{$data["cTimeFrameNum"]} months", $timeframe);
                break;
            case 'year(s)':
                $timeframe = strtotime("+{$data["cTimeFrameNum"]} years", $timeframe);
                break;
        }

        $insert = true;
        if(!$skipCheck) {
            if(($data["cKeepFiles"] == "Yes")) {
                $qmodel = 'select ecID from employee_credentialing where ecAFID=%d and ecCID=%d and ecUploadedOn is null';
                $q = sprintf($qmodel, $afID, $data["cID"]);
                $old = db::PrepareLoadSingleRow($q);
                if(!empty($old)) {
                   $qmodel = 
					  'update employee_credentialing set ecDueDate = "%s" where ecAFID=%d and ecCID=%d and ecUploadedOn is null';
               
					  $q = sprintf($qmodel, date("Y-m-d H:i:s", $timeframe), $afID, $data["cID"]);
                    db::SQL($q);
                    $insert = false;
                } else {
                    $qmodel = 'select ecID from employee_credentialing where ecAFID=%d and ecCID=%d and ecUploadedOn is not null';
                    $q = sprintf($qmodel, $afID, $data["cID"]);
                    $old = db::PrepareLoadSingleRow($q);
                    if(!empty($old)) {
                        if($data["cType"] !== "recurring") {
                            $insert = false;
                        }
                    }
                }
            } else {
                $qmodel = 'delete from '.self::$dt["tbl"].' where ecAFID=%d and ecCID=%d and ecUploadedOn is null';
                $q = sprintf($qmodel, $afID, $data["cID"]);
                db::SQL($q);
            }
        }

        if($insert) {
		
          $qmodel = 'insert into employee_credentialing 
		   (ecAFID, ecCID,ecNotApplicForMe,ecDueDate, ecActive, ecAdded, ecExpiredDate, ecUploadedOn)
   		   values (%d, %d, "%s","%s", "Yes", "%s", "%s",NULL)';
            
			
			if ($data['cType'] == 'one-time'){
				$data["cExpiredDate"] = val::currentDate(MYSQL_DATE);
				$ti = $data["cTimeFrameNum"].' '.$data["cTimeFrameDim"];
				$DueDate=DateTime::createFromFormat(MYSQL_DATE,$data["cExpiredDate"] );
				$DueDate->add(date_interval_create_from_date_string($ti));
				$data["cDueDate"] = $DueDate->format(MYSQL_DATE_TIME);
			}
			
			if ($data['cType'] == 'recurring'){
				if (! isset($data['ecAFID']) || self::firstTimeInsert($data['ecAFID'],$data['ecCID'])){
					$data["cExpiredDate"] = val::currentDate(MYSQL_DATE);
					$ti = $data["cTimeFrameNum"].''.$data["cTimeFrameDim"];
					$DueDate=DateTime::createFromFormat(MYSQL_DATE,$data["cExpiredDate"] );
					$DueDate->add(date_interval_create_from_date_string($ti));
					$data["cDueDate"] = $DueDate->format(MYSQL_DATE_TIME);
					//echo $data["cExpiredDate"].' '.$data["cDueDate"];exit;
				}
				else {
					$data["cExpiredDate"] = $data["ecDueDate"];
					$ti = $data['cRenewalTimeFrameNum'].''.$data['cRenewalTimeFrameDim'];
					$DueDate=DateTime::createFromFormat(MYSQL_DATE_TIME,$data["cExpiredDate"] );
					$DueDate->add(date_interval_create_from_date_string($ti));
					$data["cDueDate"] = $DueDate->format(MYSQL_DATE_TIME);
				}
			}
			
			if ($data['cHasExpirationDate'] == 'yes' && isset($_POST['credentialingExpDate'])){
				$date=DateTime::createFromFormat(USER_DATE,$_POST['credentialingExpDate']);
				$data["cExpiredDate"]=$date->format(MYSQL_DATE_TIME);
				$ti= $data['cRenewalTimeFrameNum'].' '.$data['cRenewalTimeFrameDim'];
				
				$date->add(date_interval_create_from_date_string($ti));
				//$data["cDueDate"]=$today->format(MYSQL_DATE_TIME);
				$data["cDueDate"]=$data["cExpiredDate"];
			}
			
			if ($data['cHasExpirationDate'] == 'no' && isset($data['ecAFID'])){
				//Auto-set Expiration Date to: Today + Renewal Time Frame
				$ti= $data['cRenewalTimeFrameNum'].' '.$data['cRenewalTimeFrameDim'];
				$today = new DateTime();
				$today->add(date_interval_create_from_date_string($ti));
				$data["cExpiredDate"]=$today->format(MYSQL_DATE_TIME);
				$data["cDueDate"]=$data["cExpiredDate"];
				
				if ($data['cImportantWhenDocCompleated'] == 'no'){
					// nothing to do
				}
				if ($data['cImportantWhenDocCompleated'] == 'yes'){
					// Auto-set Expiration Date to: (Document Completion Date) + (Renewal Time Frame)
					$Date=DateTime::createFromFormat(USER_DATE,$_POST['credentialingComplDate']);
					//$data["cExpiredDate"]=$Date->format(MYSQL_DATE_TIME);
					$ti= $data['cRenewalTimeFrameNum'].' '.$data['cRenewalTimeFrameDim'];
					$Date->add(date_interval_create_from_date_string($ti));
					$data["cDueDate"] = $Date->format(MYSQL_DATE_TIME);
					$data["cExpiredDate"]= $data["cDueDate"];
					//echo '>>>>'.$data["cExpiredDate"].' '.$data["cDueDate"];exit;
				}
				
			}
			
			/********
			insert into employee_credentialing 
		   (ecAFID, ecCID,ecNotApplicForMe,ecDueDate, ecActive, ecAdded, ecExpiredDate, ecUploadedOn)
   		   values (%d, %d, "%s","%s", "Yes", "%s", "%s",NULL)';
			******/
			$q = sprintf($qmodel, 
			   $afID, 
			   $data["cID"],
			   $data['cNotApplForSomeEmployees'],
			   $data['cDueDate'], 
			   val::currentDate(MYSQL_DATE_TIME),
			   $data["cExpiredDate"]	 
			   );
		
             db::SQL($q);
        }
    }
	
	 public static function  firstTimeInsert($empID,$credID){
		 $q = "SELECT * FROM employee_credentialing
		 WHERE ecAFID={$empID} AND ecCID={$credID} AND ecUploadedOn IS NOT NULL";
		 $res = db::PrepareLoad($q);
		 return empty($res);		 
	 }

    public static function RemoveRecord($ecID)
    {
        $q = 'delete from `' . self::$dt["tbl"] . '` where `' . self::$dt["key"] . '`=' . intval($ecID);
        db::SQL($q);
    }
	
	public static function RemoveNotApplicForMe($ecID) {
		$q="SELECT * FROM employee_credentialing WHERE ecID=".$ecID;
		$res = db::PrepareLoadSingleRow($q);
		if (is_null($res['ecFileName'])) $add=',ecUploadedOn = NULL ';
		else $add='';
		
		$q="UPDATE employee_credentialing SET ecIsApplicableForMe = 'yes' {$add} WHERE ecID=".$ecID;
		db::SQL($q);
		return true;
	}

    public static function RemoveFile($data) {
        $filename = CREDENTIALING_UPLOAD_DIR . CREDENTIALING_FILE_NAME . $data["ecID"];
        if(file_exists($filename)) {
            unlink($filename);
        }

        $eData = EmployeeModel::dataById($data["ecAFID"]);
        $cData = CredentialingModel::dataById($data["ecCID"]);

        if(!in_array($eData["afHiredForPID"], $cData["cPositions"])) {
            self::RemoveRecord($data["ecID"]);
            return true;
        }

        if($data["cType"] === "recurring") {
            $q = 'select max(ecID) maxID from '.self::$dt["tbl"] . ' where ecAFID = '.$data['ecAFID'].' and ecCID='.$data['ecCID'].' and ecUploadedOn is not null';
            $maxID = db::PrepareLoadSingleCellValue($q);
            if($maxID > $data["ecID"]) {
                self::RemoveRecord($data["ecID"]);
            } else {
                $q = 'delete from '.self::$dt["tbl"] . ' where ecAFID = '.$data['ecAFID'].' and ecCID='.$data['ecCID'].' and ecUploadedOn is null';
                db::SQL($q);
                $q = 'update '.self::$dt["tbl"] . ' set ecUploadedOn = null, ecFileName = null where ecID = '.$data["ecID"];
                db::SQL($q);
            }
            return true;
        } else {
            $q = 'update '.self::$dt["tbl"] . ' set ecUploadedOn = null, ecFileName = null where ecID = '.$data["ecID"];
            db::SQL($q);
            return true;
        }

        return false;
    }

    public static function RemovePosition($cID, $pID) {

        $q = 'delete from '.self::$dt["tbl"]. ' 
              where ecUploadedOn is null and ecCID = '.$cID.' and exists(select afID from apply_forms where afID = ecAFID and afHiredForPID='.$pID.')';
        db::SQL($q);
    }

    public static function AddPosition($cID, $pID) {
        $cData = CredentialingModel::dataById($cID);
        $empls = EmployeeModel::getActiveEmployeesHiredFor($pID);

        foreach ($empls as $empl) {
            self::AddRecord($empl["afID"], $cData);
        }

    }

    public static function AddPositionIfNotExists($cID, $pID) {
        $cData = CredentialingModel::dataById($cID);
        $empls = EmployeeModel::getActiveEmployeesHiredFor($pID);

        foreach ($empls as $empl) {
            $qmodel = 'select count(ecID) cnt from employee_credentialing where ecAFID=%d and ecCID=%d';

            if($cData["cType"] === "recurring") {
                $qmodel .= ' and ecUploadedOn is null ';
            }

            $q = sprintf($qmodel, $empl["afID"], $cID);

            $cnt = db::PrepareLoadSingleCellValue($q);
            if($cnt == 0) {
                self::AddRecord($empl["afID"], $cData, true);
            }
        }
    }

    public static function RemoveCredentialings($cID) {
        $q = 'delete from '.self::$dt["tbl"].' where ecUploadedOn is null and ecCID='.$cID;
        db::SQL($q);
    }

    public static function AddCredentialings($cData) {
        $positions = implode(',', $cData["cPositions"]);
        $empls = EmployeeModel::getActiveEmployeesHiredFor($positions);

        foreach ($empls as $empl) {
            $afID = $empl["afID"];
            if($cData["cType"] === "recurring") {
                self::AddRecord($afID, $cData);
            } else {
                $qmodel = 'select ecID from employee_credentialing where ecAFID=%d and ecCID=%d';
                $q = sprintf($qmodel, $afID, $cData["cID"]);
                $old = db::PrepareLoadSingleRow($q);
                if(empty($old)) {
                    self::AddRecord($afID, $cData);
                }
            }
        }
    }

    public static function ChangeEmployeePosition($afID, $newPosition) {
        $creds = array_column(CredentialingPositionsModel::LoadPositionCredentialings($newPosition), "cpCID");
        if(!empty($creds)) {
            $qmodel = 'delete from '.self::$dt["tbl"].' where ecAFID=%d and ecCID not in ('.implode(',', $creds).') and ecUploadedOn is null';
            $q = sprintf($qmodel, $afID);
            db::SQL($q);
        } else {
            $qmodel = 'delete from '.self::$dt["tbl"].' where ecAFID=%d and ecUploadedOn is null';
            $q = sprintf($qmodel, $afID);
            db::SQL($q);
        }
        self::AssignEmployeeCredentialings($afID, $newPosition, new DateTime());
    }

    public static function ChangeCredentialingTimeFrame($cID, $oldTFNum, $oldTFDim, $newTFNum, $newTFDim) {
        if (isset($cID)){
		 $q = 'select * from employee_credentialing where ecCID='.$cID;
        $tasks = self::PrepareLoad($q);
		} else $tasks=array();

        if(is_array($tasks)) {
            $zeroTime = new DateTime();
            $oTF = EmployeeOnboardingModel::ConvertTimeFrameToTimestamp($oldTFNum, $oldTFDim, $zeroTime);
            $nTF = EmployeeOnboardingModel::ConvertTimeFrameToTimestamp($newTFNum, $newTFDim, $zeroTime);
            $tfDiv = $nTF - $oTF;

            foreach ($tasks as $task) {
                $oldDate = strtotime($task["ecDueDate"]);
                $newDate = $oldDate + $tfDiv;
                $q = 'update employee_credentialing set ecDueDate="'.date('Y-m-d H:i:s', $newDate).'" where ecID='.$task["ecID"];
                db::SQL($q);
            }
        }
    }

}
