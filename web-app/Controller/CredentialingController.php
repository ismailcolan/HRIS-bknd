<?php
/** The CompanyOffice controller */
class CredentialingController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;
	
	final static function Report(){
       msg::get();
		
		$cond_id=1;
		if (isset($_GET['filter'])) $cond_id=$_GET['filter'];
		
		$today = val::currentDate(MYSQL_DATE_TIME);
		
		switch ($cond_id) {
		case 0:
			$having = '';$_SESSION['onboarding-report']='any';
			break;
		case 1:
			$having = "HAVING (Status = 'incomplete' OR Status = 'overdue')";
			$_SESSION['onboarding-report']='incomplete (incl. overdue)' ;
			break;
		case 2:
			$having = "HAVING (Status = 'overdue')";
			$_SESSION['onboarding-report']='overdue' ;;
			break;
		case 3:
			$having = "HAVING (Status = 'complete')";
			$_SESSION['onboarding-report']='complete' ;;
			break;
       }
		
		 $today = new DateTime();
        $query = "SELECT 
        concat(afFirstName, ' ', afLastName) as afName, afID, ecID, cName, ecDueDate
    FROM `employee_credentialing` 
    LEFT JOIN apply_forms on afID = ecAFID
    LEFT JOIN credentialings on cID = ecCID
    WHERE ecDueDate <'".$today->format(MYSQL_DATE_TIME)."'  and afActive = 'Hired' and cActive = 'Yes' and ecUploadedOn is null";
    //order by afID, ecDueDate";

       CredentialingView::Report(self::$pg, $query , CredentialingModel::$fldLbl, self::$rq);
    }


    final static function All(){ 
        msg::get();
        $cond = "`cActive`=\"" .(self::$rq==="list" ? "Yes": "No")."\"";
        CredentialingView::All(self::$pg, CredentialingModel::query($cond), CredentialingModel::$fldLbl, self::$rq);
    }

    static function Summary(){
		
        msg::get();
        CredentialingView::Summary(self::$data, self::$pg, CredentialingModel::$fldLbl);
    }

    public static function getBackUrl()
    {
        $url = (
		  isset (self::$data[CredentialingModel::$dt["pfx"]."Active"] ) && self::$data[CredentialingModel::$dt["pfx"]."Active"] != "No" 
		  ? 
		  self::$pg["list"]["url"] 
		  : 
		  self::$pg["inactive-list"]["url"]);
		  
		$url = (self::$data[CredentialingModel::$dt["pfx"]."Active"] != "No" ? self::$pg["list"]["url"] : self::$pg["inactive-list"]["url"]);
        $params = Array(
		"name"=>"Back to ".strtolower(self::$pg["items"]), 
		"url"=>$url, 
		"icon"=>"fa fa-chevron-circle-left", 
		"args"=>array("class"=>"btn btn-info"));
        return $params;
    }

    final static function Details($lists = array()){
        CredentialingModel::Detailsvalidate("client", $lists);
		 CredentialingView::Details(self::$data, self::$pg, CredentialingModel::$fldLbl, $lists);
    }

    final static function DetailsSave($lists = array()){

			try{ 
				CredentialingModel::DetailsValidate("server", $lists); 
			} 
			catch (Exception $e) {
			  foreach($_POST as $k=>$v){
				  self::$data[$k] = $v;
			  }
			  return self::Details();
			}

        $newRec = false;
        if(empty(self::$data)) {
            $newRec = true;
        } else {
            $oldPos = self::$data["cPositions"];
            if(empty($oldPos)) {
                $oldPos = [];
            }
            $newPos = filter_input(INPUT_POST, "cPositions", FILTER_DEFAULT, FILTER_FORCE_ARRAY);
            if(empty($newPos)) {
                $newPos = [];
            }

            foreach ($oldPos as $old) {
                if(!in_array($old, $newPos)) {
                    EmployeeCredentialingModel::RemovePosition(self::$id, $old);
                }
            }

            foreach ($newPos as $new) {
                if(!in_array($new, $oldPos)) {
                    EmployeeCredentialingModel::AddPositionIfNotExists(self::$id, $new);

                }
            }

        }
        $oldTimeFrame = self::$data["cTimeFrameNum"].self::$data["cTimeFrameDim"];
        $newTimeFrame = filter_input(INPUT_POST, "cTimeFrameNum", FILTER_SANITIZE_NUMBER_INT).
            filter_input(INPUT_POST, "cTimeFrameDim", FILTER_SANITIZE_STRING);

        if($oldTimeFrame != $newTimeFrame) {
            EmployeeCredentialingModel::ChangeCredentialingTimeFrame(self::$id,
                self::$data["cTimeFrameNum"],
                self::$data["cTimeFrameDim"],
                filter_input(INPUT_POST, "cTimeFrameNum", FILTER_SANITIZE_NUMBER_INT),
                filter_input(INPUT_POST, "cTimeFrameDim", FILTER_SANITIZE_STRING));
        }
		 if ($_POST['cType']!= self::$data['cType']) CredentialingModel::$postOverrided['cOldType']=self::$data['cType'];
		
		
		 //print_r(CredentialingModel::saveQueries());exit;
        db::Save(CredentialingModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? msg::set(TXT_SAVE_OK,"success") : msg::set(TXT_SAVE_ERROR,"error");

        $ids = (filter_has_var(INPUT_POST, "cPositions")) ? array_values(filter_input(INPUT_POST, "cPositions", FILTER_DEFAULT, FILTER_FORCE_ARRAY)) : [];
        CredentialingPositionsModel::SaveCredentialingPositions(self::$id, $ids);
	  
		  if ($_POST['cNotApplForSomeEmployees'] == 'yes')
			$q="UPDATE employee_credentialing SET ecNotApplicForMe='yes',ecIsApplicableForMe='yes' 
				WHERE ecCID=".self::$id;
		  else {
			if ($_POST['cType']=='recurring' && self::$data['cType']=='one-time'){
			
			 $q = "SELECT * FROM employee_credentialing WHERE 
				     ecUploadedOn is NOT NULL and ecFileName IS NOT NULL
					  and ecCID=".self::$id; 
			  $res= db::PrepareLoad($q);
			  if (!empty($res)) { // there is uploaded file, save it as is;
					$q="INSERT INTO employee_credentialing SET  
				     ecUploadedOn = NULL, ecFileName = NULL,
					  ecCID='".self::$id."',
					  ecAFID=".$res[0]['ecAFID'].",ecActive='Yes',
					  ecNotApplicForMe='".$res[0]['ecNotApplicForMe']."',
					  ecIsApplicableForMe='".$res[0]['ecIsApplicableForMe']."'";
			  }
			  else {
				
		     $q="UPDATE employee_credentialing SET ecNotApplicForMe='no',ecIsApplicableForMe='yes',
				ecUploadedOn = NULL, ecExpiredDate = NULL
				WHERE ecCID=".self::$id;
			  }
			}
			else 
			
		   if (($_POST['cType']=='one-time' && self::$data['cType']=='recurring'))
		   {
				$q = "SELECT * FROM employee_credentialing WHERE 
				     ecUploadedOn is NOT NULL and ecFileName IS NOT NULL
					  and ecCID=".self::$id; 
				$res= db::PrepareLoad($q);
				if (!empty($res)) {
					$q="DELETE  FROM employee_credentialing WHERE 
				     ecUploadedOn is NULL and ecFileName IS  NULL
					  and ecCID=".self::$id; 
				}
			}
			
			
			
			else {
			
			$q="UPDATE employee_credentialing SET ecNotApplicForMe='no',ecIsApplicableForMe='yes',
				ecUploadedOn = NULL
				WHERE ecCID=".self::$id .' AND ecUploadedOn IS NULL';
			
			}
		  }
			
			db::SQL($q);// exec main query

				
        if($newRec && (!empty($ids))) {
            $data = CredentialingModel::dataById(self::$id);
            $data["cPositions"] = $ids;
            EmployeeCredentialingModel::AddCredentialings($data);
        }
		
		 if ($_POST['cType'] == 'one-time' and $_POST['cType'] != self::$data['cType'])
			 self::SendMailsForUsersWithCredentialsOneTimeToRecurringWithFile();

/*********** UPDATE OLD employees *********/

		$q='SELECT * from credentialings WHERE cID ='.self::$id;		
		$data=db::PrepareLoadSingleRow($q);


		$q='SELECT * FROM credentialing_positions WHERE cpCID ='.self::$id;
		
		$positions = db::PrepareLoad($q);
		
		$pos=array();
		foreach ($positions as $p){
			$pos[]=$p['cpPID'];
		}
		
		$q='SELECT afID,afHiredForPID FROM apply_forms WHERE afActive - "Hired"';
		$emp = db::PrepareLoad($q);
		
		foreach($emp as $k=>$em ) {
			
		if (!in_array($em['afHiredForPID'],$pos)) continue;
			
			$qmodel = 'insert into employee_credentialing 
					   (ecAFID, ecCID,ecNotApplicForMe,ecDueDate, ecActive, 
					   ecAdded, ecExpiredDate, ecUploadedOn
					   )
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
			
			
			$q = sprintf($qmodel, 
			   $em['afID'],
			   $data["cID"],
			   $data['cNotApplForSomeEmployees'],
			   $data['cDueDate'], 
			   val::currentDate(MYSQL_DATE_TIME),
			   $data["cExpiredDate"]	 
			   );
		
             db::SQL($q);	

		}
		/*********** END UPDATE OLD employees *********/
        cf::Redirect(ADMIN.'credentialing');
    }
	
	 
	 static function SendMailsForUsersWithCredentialsOneTimeToRecurringWithFile(){
		$q = "SELECT * FROM apply_forms WHERE afActive = 'Hired'";	
		$employees = $data = db::PrepareLoad($q);
		foreach ($employees as $k=>$employee){
			$rec = self::CheckOneTimeToRecurringWithFile($employee['afID']);
			if (! empty($rec)){
				$to=$employee['afEmail'];
				$from_name = EMAIL_MAILER_NAME; 
				$from = EMAIL_MAILER_ADDRESS; 
				$return = EMAIL_MAILER_ADDRESS;
				$_r=array();
				foreach ($rec as $k=>$r){
					$_r[]=$r['cName'];
				}
				$creds = implode(', ',$_r);
				$subject = "The credentialing {$creds} has been changed from one-time to recurring.";
				$message="Dear {$employee['afFirstName']} {$employee['afLastName']},<br>".
"The credentialing {$creds} has been changed from one-time to recurring. 
Please go to your profile - Credentialing - Already Uploaded and specify the next renewal/expiration 
date of that credentialing.<br><br>"
.MAIL_FOOTER_HTML;
				cf::sendMail($to,$subject,$message,$from_name,$from,$return,'','');	
			}
		} 
	 }
	
	 static function CheckOneTimeToRecurringWithFile($afID){ 
	//cType ='recurring' and ecUploadedOn is null and cActive='Yes' and ecAFID = {$afID}
	// OR
	
	//and ecUploadedOn is NULL and  ecFileName is null and cID = ecCID
			$q = "select * from employee_credentialing
				left join  credentialings   ON  cID  = ecCID
				WHERE 
				(  
				  cType ='recurring' and cOldType = 'one-time' and cActive='Yes' and ecAFID = {$afID}
				  and  ecExpiredDate IS NULL
				)";
				//and ec.ecID < ecID
				/*******
				(select count(ecID) from employee_credentialing as ec 
				   WHERE ec.ecAFID = {$afID}
				   and ec.ecCID  = ecCID and ec.ecFileName IS NOT NULL  ) = 1
				********/
			//echo $q;	
			$data = db::PrepareLoad($q);
			return $data;
	 }

    static function Delete()
    {
        EmployeeCredentialingModel::RemoveCredentialings(self::$id);
        parent::Delete();
    }

    static function Reactivate()
    {
        EmployeeCredentialingModel::AddCredentialings(self::$data);
        parent::Reactivate();
    }
}