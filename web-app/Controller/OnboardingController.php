<?php
/** The CompanyOffice controller */
class OnboardingController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg, $type='on';
	
	 final static function Report(){
       msg::get();
		
		$cond_id=1;
		if (isset($_GET['filter'])) $cond_id=$_GET['filter'];
		
		$today = val::currentDate(MYSQL_DATE_TIME);
		//$cond_id=3;
		switch ($cond_id) {
		case 0:
			$having = "HAVING otEmployeer IS NOT NULL AND otResponsibleEmployeer IS NOT NULL";
			$_SESSION['onboarding-report']='any';
			break;
		case 1:
			$having = "HAVING (Status = 'incomplete' OR Status = 'overdue') AND otEmployeer IS NOT NULL AND otResponsibleEmployeer IS NOT NULL";
			$_SESSION['onboarding-report']='incomplete (incl. overdue)' ;
			break;
		case 2:
			$having = "HAVING (Status = 'overdue' AND otEmployeer IS NOT NULL AND otResponsibleEmployeer IS NOT NULL )";
			$_SESSION['onboarding-report']='overdue' ;
			break;
		case 3:
			$having = "HAVING (Status = 'complete') AND otEmployeer IS NOT NULL  AND otResponsibleEmployeer IS NOT NULL ";
			$_SESSION['onboarding-report']='complete' ;;
			break;
       }
		/*******
		THEN 'incomplete'
			  
			  
			  WHEN eoCompletedOn IS NULL AND  eoTimeFrame > '{$today}' THEN 'overdue'
		********/
		if (self::$type =='on') $add=" AND afActive = 'Hired' ";  else $add = ''; //<<<<<< added 14 March 2019 PV
		$query=
		"SELECT eoAFID,otAFID, eoID,otDescription,eoTimeFrame,DATE_FORMAT(eoTimeFrame, '%m/%d/%Y') as TimeFrame ,
		(CASE
            WHEN eoCompletedOn IS NOT NULL  THEN 'complete'
            WHEN eoCompletedOn IS NULL THEN
						IF(eoTimeFrame >= '{$today}' ,'incomplete','overdue')
	        ELSE ''
        END) AS Status,
		(select concat(afFirstName, ' ', afLastName) from apply_forms where afID = eoAFID    {$add}  ) as otEmployeer ,
		(select concat(afFirstName, ' ', afLastName) from apply_forms where afID = otAFID AND afActive = 'Hired') as otResponsibleEmployeer 
		FROM `employee_onboarding` 
		left join `onboarding_tasks` on `otID` = `eoOTID` 
		WHERE otActive='Yes' AND eoActive='Yes' AND otType ='".self::$type."' ".$having;

       OnboardingView::Report(self::$pg, $query , OnboardingModel::$fldLbl, self::$rq);
    }

    final static function All(){
		//echo '<br>58 OnbC>>>>'.self::$type;
        msg::get();
        $cond = "`otActive`=\"" .(self::$rq==="list" ? "Yes": "No")."\"";
        OnboardingView::All(self::$pg, OnboardingModel::query($cond,array(),0,self::$type), OnboardingModel::$fldLbl, self::$rq);
    }

    static function Summary(){
        msg::get();
        OnboardingView::Summary(self::$data, self::$pg, OnboardingModel::$fldLbl);
    }

    public static function getBackUrl()
    {  
		if (!isset(self::$data[OnboardingModel::$dt["pfx"]."Active"])){
			if (self::$type == 'off')
			 $params = array ( 'name' => 'Back to off-boarding',
					 'url' => 'offboarding',
					 'icon' => 'fa fa-chevron-circle-left',
					 'args' => Array ( 'class' => 'btn btn-info' ) 
					 );
			else 
				$params = array ( 'name' => 'Back to onboarding',
					 'url' => 'onboarding',
					 'icon' => 'fa fa-chevron-circle-left',
					 'args' => Array ( 'class' => 'btn btn-info' ) 
					 );
		}
		else {
        $url = (self::$data[OnboardingModel::$dt["pfx"]."Active"] != "No" ? self::$pg["list"]["url"] : self::$pg["inactive-list"]["url"]);
        $params = Array("name"=>"Back to ".strtolower(self::$pg["items"]), "url"=>$url, "icon"=>"fa fa-chevron-circle-left", "args"=>array("class"=>"btn btn-info"));
		}
		 
        return $params;
    }

    final static function Details($lists = array()){
		
        OnboardingModel::Detailsvalidate("client", $lists);
        // Copy existing onboarding/offboarding
if (isset($_GET['copy']) && intval($_GET['copy']) > 0) {

    $copyId = intval($_GET['copy']);

    self::$data = OnboardingModel::dataById($copyId);

    // Treat it as a new record
    self::$id = null;

    if (!empty(self::$data)) {

        // Clear description so user enters a new one
        self::$data['otDescription'] = '';

        // Remove primary key
        unset(self::$data['otID']);
    }
}
		
        OnboardingView::Details(self::$data, self::$pg, OnboardingModel::$fldLbl, $lists);
    }

    final static function DetailsSave($lists = array()){ 
        // Copy existing onboarding/offboarding
if (isset($_GET['copy']) && intval($_GET['copy']) > 0) {
    self::$id = null;
}
		//cf::pr($lists);cf::pr($_POST);exit;
        try{ OnboardingModel::DetailsValidate("server", $lists); } 
		    catch (Exception $e) 
			{
			  if(!isset($_POST['otApplayTo'])) $_POST['otApplayTo']=array();
			  foreach ($_POST as $k=>$v){
				 
				 if ($k == 'otApplayTo') {
					 self::$data[$k] = implode('|',$_POST[$k]);
					 $_POST[$k] = self::$data[$k];

				 }
				 else
				 self::$data[$k]=$_POST[$k];
			  }
			  
			 return self::Details();
			}
        OnboardingModel::$postOverrided['otType']=self::$type;

		 if(isset($_POST['otApplayTo']) && is_array($_POST['otApplayTo'])) 
			 $res=implode('|',$_POST['otApplayTo']); 
		 else 
			 $res='';
        OnboardingModel::$postOverrided['otApplayTo']=$res;
		//cf::pr(OnboardingModel::saveQueries());exit;
        db::Save(OnboardingModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? msg::set(TXT_SAVE_OK,"success") : msg::set(TXT_SAVE_ERROR,"error");

        if(!empty(self::$data)) {
            $oldRespEmpl = self::$data["otAFID"];
            $newRespEmpl = filter_input(INPUT_POST, "otAFID", FILTER_SANITIZE_NUMBER_INT);
			
			  
			
            if($oldRespEmpl != $newRespEmpl) {
                // MailController::OnboardingTaskNewResponsibleEmployee(self::$id);
                MailController::OnboardingTaskNewResponsibleEmployee($newId);
                // MailController::OnboardingTaskRemoveResponsibleEmployee($oldRespEmpl, self::$id);
                MailController::OnboardingTaskRemoveResponsibleEmployee($oldRespEmpl, $newId);
            } else {
                $oldTimeFrame = self::$data["otTimeFrameNum"].self::$data["otTimeFrameDim"];
                $newTimeFrame = filter_input(INPUT_POST, "otTimeFrameNum", FILTER_SANITIZE_NUMBER_INT).
                    filter_input(INPUT_POST, "otTimeFrameDim", FILTER_SANITIZE_STRING);

                if($oldTimeFrame != $newTimeFrame) {
                    // EmployeeOnboardingModel::ChangeOnboardingTaskTimeFrame(self::$id,
                    EmployeeOnboardingModel::ChangeOnboardingTaskTimeFrame($newId,
                        self::$data["otTimeFrameNum"],
                        self::$data["otTimeFrameDim"],
                        filter_input(INPUT_POST, "otTimeFrameNum", FILTER_SANITIZE_NUMBER_INT),
                        filter_input(INPUT_POST, "otTimeFrameDim", FILTER_SANITIZE_STRING));
                    // MailController::OnboardingTaskNewTimeFrame(self::$id);
                    MailController::OnboardingTaskNewTimeFrame($newId);
                }
            }
        } else {
            // MailController::OnboardingTaskNewResponsibleEmployee(self::$id);
            MailController::OnboardingTaskNewResponsibleEmployee($newId);
        }
        
        $ids = (filter_has_var(INPUT_POST, "otPositions")) ? array_values(filter_input(INPUT_POST, "otPositions", FILTER_DEFAULT, FILTER_FORCE_ARRAY)) : [];
        
// 		 OnboardingPositionsModel::SaveOnboardingPositions(self::$id, $ids);
$newId = self::$id;

if (empty($newId)) {
    $newId = db::LastID();
}

OnboardingPositionsModel::SaveOnboardingPositions($newId, $ids);
		
		 if (isset($_POST['otTaskOrDocsToSign']) && $_POST['otTaskOrDocsToSign'] == 'docstosign'){
			 // processing files
			 $uploads_dir=UPLOADS_DIR.'onboarding_files';
			
			 foreach ($_FILES['upl']['name'] as $k=>$real_name){
				 if (!isset($real_name)) continue;
				 $today=new DateTime();
				 $saved=$today->format(MYSQL_DATE_TIME);
				 $for_name =$today->format("Y-m-d H-i-s");//USER_DATETIME_FOR_FILE
				 if ($_FILES['upl']['error'][$k]==0){
					  
					 $name=$_POST['upl_title'][$k].' '.$for_name.'.pdf';//.$real_name;
					 $disabled = array("\\", "/", ":", "*", "?", '"', "<", ">", "|");
					 $name = str_replace($disabled, "", $name);
					 
					 $cnt=0;
					 while(file_exists($uploads_dir.'/'.$name)){
						 $_name = basename($name, ".pdf");
						 $cnt++;
						 $name=$_name.'-'.$cnt.'.pdf';
					 }
					 
					 $tmp_name =$_FILES['upl']['tmp_name'][$k];
					 
					 
					 $server_name = $uploads_dir.'/'.$name;
					 
					 
					 $server_name=str_replace('\\','/',$server_name);
					 $url_name=db::my_escape_str(UPLOADS_URL.'onboarding_files/'.$name);
					 
					 $title=db::my_escape_str($_POST['upl_title'][$k]);
					 $title=str_replace($disabled, "", $title);
					 
					 $type=$_POST['upl_type'][$k];
					 
				    if (move_uploaded_file($tmp_name, $server_name))
					{	
						$server_name = db::my_escape_str($server_name);
						$q="INSERT INTO onboarding_files SET
						ofOBID=".$newId.",ofName='{$server_name}',ofTitle='{$title}',otURLName='{$url_name}',
						ofReplaceFile={$type},ofDateTimeSaved='{$saved}'";
						
						db::SQL($q);
						$newDocID=db::LastID();
						
						$q="UPDATE onboarding_files SET ofActive='disabled' WHERE ofID={$type}";
						db::SQL($q);
						
						/*******  UPDATE UNSIGNED **************/
						$q="SELECT * FROM employee_onboarding WHERE eoDocsRemainToSign LIKE '%{$type}%'";
						
						$to_change=db::PrepareLoad($q);
						if ($to_change === false) $to_change=array();
						foreach ($to_change as $k=>$ch){
							//$q1="SELECT * FROM  employee_onboarding WHERE eoID={$ch['eoID']}";
							//$onb=db::PrepareLoadSingleRow($q);
							$docsToSign=str_replace($type,$newDocID,$ch['eoDocsToSign']);
							$docsRemainToSign=str_replace($type,$newDocID,$ch['eoDocsRemainToSign']);
							$q="UPDATE employee_onboarding SET eoDocsToSign='{$docsToSign}',
							eoDocsRemainToSign='{$docsRemainToSign}'  WHERE eoID={$ch['eoID']}";
							
							db::SQL($q);
						}
						
						
					}
				 
				 
				 }
			 }
			 
		 }
		 else {
			$q="DELETE FROM onboarding_files WHERE  ofOBID=".self::$id;
			db::SQL($q);
		 }
        
        // cf::Redirect(ADMIN.self::getBackUrl()["url"]);//self::$pg["summary"]["url"]."?id=".self::$id);
        cf::Redirect(ADMIN . self::$pg["summary"]["url"] . "?id=" . $newId);
    }

    public static function Delete() {
        MailController::OnboardingTaskRemoveResponsibleEmployee(self::$data["otAFID"], self::$data["otID"]);
        parent::Delete();
    }

    public static function Reactivate() {
        MailController::OnboardingTaskNewResponsibleEmployee(self::$data["otID"]);
        parent::Reactivate();
    }
	
	public static function ChangeDocState(){
		$docID=$_GET['docid'];
		$state=$_GET['state'];
		$q="UPDATE onboarding_files SET ofActive ='{$state}' WHERE ofID={$docID}";
		$r=db::SQL($q);
		
		if ($state=='active') {
			$new_state='disabled';$text='disable';
			
		}
		else {
			
			/******  Remove doc to be signed ********/
			$q="SELECT * FROM employee_onboarding WHERE eoDocsRemainToSign LIKE '%{$docID}%'";
			
			$docs=db::PrepareLoad($q);
			
			if ($docs== false) $docs=array();
			foreach ($docs as $doc){
				$docs_to_sign=$doc['eoDocsToSign'];
				$docs_remain_sign=$doc['eoDocsRemainToSign'];
				
				//echo $docs_remain_sign."\n";
				$docs_remain_sign=str_replace($docID,'',$docs_remain_sign);
				$docs_remain_sign=str_replace('||','|',$docs_remain_sign);
				$docs_remain_sign=trim($docs_remain_sign,'|');
				//echo $docs_remain_sign."\n";
				$docs_to_sign=str_replace($docID,'',$docs_to_sign);
				$docs_to_sign=str_replace('||','|',$docs_to_sign);
				$docs_to_sign=trim($docs_to_sign,'|');
				
				$q1="UPDATE employee_onboarding SET eoDocsToSign='{$docs_to_sign}', 	eoDocsRemainToSign='{$docs_remain_sign}'
				WHERE eoID=".$doc['eoID'];
				
				
				$r=db::SQL($q1);
				
				if ($docs_remain_sign==''){
					$date=new DateTime;
					$date_str=$date->format(MYSQL_DATE_TIME);
					$q2="UPDATE employee_onboarding SET eoCompletedOn='{$date_str}' WHERE eoID=".$doc['eoID'];
					$r=db::SQL($q2);
				}
			}

			$new_state='active';$text='enable'; 
		}
		
		$q="SELECT * FROM onboarding_files WHERE ofID={$docID}";
		$file=db::PrepareLoadSingleRow($q);
		
		$ret=array();
		
		
		if ($r) 
			$ret=array('result'=>'ok','state'=>$state,'new_state'=>$new_state,'text'=>$text,
				'doc'=>$docID,'title'=>$file['ofTitle']);
		else  
			$ret=array('result'=>'FAIL','message'=>'There was a problem to change state of document.');
		
		echo json_encode($ret);
		exit;
	}
    
}