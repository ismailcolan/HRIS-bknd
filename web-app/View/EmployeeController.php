<?php

/** The CompanyOffice controller */
class EmployeeController extends AppController
{

    const __DUMP__ = false;

    public static $id, $data, $pg;
	
		
		final static function AdminMassTask(){
		$q = 'SELECT * FROM  apply_forms WHERE afID>=211';
		$res = db::PrepareLoad($q);
		
		/**********  SET PASSWORD ********
		foreach ($res as $r){
			$ssnpart= explode('-',$r['afSSNmb'])[2];
			$namepart = substr($r['afLastName'],0,4);
			if (strlen($namepart) == 3 ) {
				$namepart=$namepart.'_';
				echo '>>>>>>>>>>>>>>>';
			}
			$pass = $namepart.$ssnpart;
			echo $r['afID'].' '.$r['afFirstName'].' '.$r['afLastName'].' '.$r['afEmail'].' '
			.$r['afSSNmb'].' '.$pass.'<br>';
			ProfileModel::savePassword($r['afID'], $pass);
		}
		***************/
		
		//**********  SET CREDENTIALS ********
		foreach ($res as $r){
			$empID = $r['afID'];
			$dateHired = $r['afDateHired'];
			
			$posID = $r['afHiredForPID'];
			$q='SELECT * FROM credentialing_positions WHERE cpPID='.$posID;
			$creds = db::PrepareLoad($q);
			$z=array();
			foreach ($creds as $c){
				$cpID= $c['cpCID'];
				$s = "SELECT * FROM  credentialings WHERE cID={$cpID}";
				$cred_def_res = db::PrepareLoad($s);
				if (empty($cred_def_res)) continue;
				$cred_def = $cred_def_res[0];
									
				$DueDate = date('Y-m-d H:i:s',strtotime($dateHired. ' + '
				.$cred_def['cTimeFrameNum'].' '.$cred_def['cTimeFrameDim']) );
				$today = date('Y-m-d H:i:s');
				
				$qry='INSERT INTO employee_credentialing SET '
				."ecAFID ={$empID},"
				." ecCID = {$cpID},"
				."ecExpiredDate = NULL,"
				."ecDueDate ='{$DueDate}',"
				."ecActive='Yes',"
				."ecAdded ='{$today}'";
				
				if (db::SQL($qry)) $done="OK"; else $done='>>>>PROBLEM';
				
				echo $done,' '.$empID.' '.$posID.' CRED ID:'.$cred_def['cID'].' '.$cred_def['cName'].''.$dateHired.' '
			.$cred_def['cTimeFrameNum'].' '.$cred_def['cTimeFrameDim'].' '.$DueDate.'<br>';
			}
			
			
		}
		//***************/
		
		echo '<br>D O N E ';
		exit;
	 }
	 
	public static function  ViewDocsToSign(){
		
		$user_id = $_GET['id'];
		$q="SELECT * FROM employee_onboarding WHERE eoID={$_GET['onbID']} AND eoAFID={$user_id}";
		$r1=db::PrepareLoadSingleRow($q);
		
		$taskID=$r1['eoOTID'];
		$docs_to_sign=explode('|',$r1['eoDocsToSign']);
		
		
		$q="SELECT * FROM onboarding_tasks WHERE otID={$taskID}";
		$r2=db::PrepareLoadSingleRow($q);
		$task_name=$r2['otDescription'];
		
		
		$docs=explode('|', $r1['eoDocsToSign']);
		$docs_signed=explode('|', $r1['eoSignedDocs']);
		echo '
		<div class="col-xs-12 col-right">
						
			<div class="content bg-white">
				<br>	
				<span style="font-size:1.5em">View documents for task: <b>'.$task_name.'</b></span>
				<a href="'.ADMIN.'employee-details?id='.$_GET['id'].'" style="float:right"
				class="btn btn-info">Back to profile</a>
				<hr>
				'.msg::get().'
				<table >
				<thead ><tr><th style="text-align:center">Document</th>
				<th style="text-align:center">Date created</th><th></th><th></th></tr></thead>
				';
			
		//cf::pr($docs)	;cf::pr($docs_signed);
		foreach ($docs as $docID){
			$q="SELECT * FROM onboarding_files WHERE ofID={$docID}";
			$doc=db::PrepareLoadSingleRow($q);
			//cf::pr($doc);
			//<a style="float:right" href="'.$doc['otURLName'].'">Download</a>
			echo '<tr><td>
				 <a target="blank" style="float:right;margin-right:10px;color:blue" title="Click to view" href="'.$doc['otURLName'].'">view >></a>
			    <a style="color:#000;float:left" title="Click to download"  href="'.$doc['otURLName'].'">'.$doc['ofTitle']
				.'</a>			 
			     </td>'.
				'<td style="text-align:center">'.val::convDate(MYSQL_DATE_TIME, USER_DATE_TIME_SHORT,$doc['ofDateTimeSaved']).'</td>';
			if(in_array($docID,$docs_signed)){
				$q="SELECT * FROM docs_signed WHERE dsDocID={$docID} AND dsAFID={$user_id}";
				$signData=db::PrepareLoadSingleRow($q);
				echo '<td style="text-align:center"><span style="color:green">SIGNED</span></td>
				<td><a class="bstooltip" data-trigger="click" href="javascript:void(0)"  
				data-toggle="tooltip" data-placement="top" data-html="true" title="Date: '.val::convDate(MYSQL_DATE_TIME, USER_DATE_TIME_SHORT,$signData['dsDateTime'])
				.'<br>From IP: '.$signData['dsIP'].'<br>(click link again to close)">details >></a>
				<a style="float:right;color:blue" href="employee-details?cmd=undo-sign-doc&id='.$_GET['id'].'&docid='.$docID.'&onbID='.$_GET['onbID'].'" onclick="return confirm(\'Are you sure to mark this document as unsingned?\')">Remove signature</a>
				</td>';
			}
			else {
				if (isset($_SESSION['webapp-admin-token'])){
					echo '<td style="text-align:center"><span style="color:red">not signed</span></td><td></td>';
				}
				else {
				echo '<td><a href="javascript:void(0)" 
				onclick="if (confirm(\'Are you sure you have read and you agree to the document?\')) 
					location.href=\'?cmd=sign-doc&emp='.$user_id.'&doc='.$docID.'&task='.$taskID.'\'">Sign >></a>
				<span style="float:right;font-size:0.8em;color:#000">Signing the document means you\'ve read it and you agree to it.</span>
				</td>';
				}
			}
			
			echo '</tr>';
		}
		
		echo '</table>';		
		echo '</div>
		</div>
		<script>
		$( document ).ready(function() {
			jQuery(".bstooltip").tooltip();
		});
		</script>	';
		
		
		
		
	}

	public static function TerminateTodayEmployees(){
		
		$today = new DateTime();
		$q ='UPDATE apply_forms SET afActive = "Terminated"
			 WHERE afDateLeaving = "'.$today->format('Y-m-d').'"';
		
		$res = db::SQL($q);
		
	}
	
	public static function  AskNoMoreConnect(){
		$id=$_GET['id'];
		$q="UPDATE apply_forms SET afConnectedToProfile=' ' WHERE afId={$id}";
		$res = db::SQL($q);
		if ($res) echo 'OK'; else echo 'BAD';
		exit;
	}
	
	public static function ConnectProfiles(){
		$id=$_GET['id'];
		$former_ids = $_GET['former_ids'];
		$former_id = $_GET['former_id'];
		
		if ($former_ids !='') $_former_id_array=explode('|',$former_ids);
		else $_former_id_array = array();
		$_former_id_array[]=$id;
		$_former_id_array[]=$former_id;

		$new_ids=implode('|',array_unique($_former_id_array));
		
		//echo $former_ids.' '.$id.' '.$former_id.' '.$new_ids;exit;
		
		$q="UPDATE apply_forms SET afConnectedToProfile='{$new_ids}' WHERE afId={$former_id}";
		$res1 = db::SQL($q);
		
		$q="UPDATE apply_forms SET afConnectedToProfile='{$new_ids}' WHERE afId={$id}";
		$res2 = db::SQL($q);
		
		if ($res1 && $res2) echo 'OK'; else echo 'BAD';
		exit;
	}
	
	public static function UndoTerminateEmployee(){

		$q="UPDATE apply_forms SET afDateLeaving = NULL, afLeavingReason = NULL,
			afTerminatedOrRetired = NULL  WHERE afId=".$_GET['id'];
			
		$res = db::SQL($q);
		if (! $res) msg::set('There was problem to undo termination of employee.','error');
		else {
			EmployeeOnboardingModel::ClearEmployeeOffboardingTasks($_GET['id']);
		}	
		cf::Redirect(ADMIN.'employee-details?id='.$_GET['id']);
	}
	
	public static function UndoSignDocEmployee(){
	//echo 'HERE';exit;
		if (isset($_GET['id']))$emp=$_GET['id'];
		if (isset($_GET['docid']))$doc=$_GET['docid'];
		if (isset($doc) && isset($emp)){
			
			$q="SELECT * FROM onboarding_files WHERE ofID={$doc}";
			$file= db::PrepareLoadSingleRow($q);
			$eoOTID=$file['ofOBID'];
			
			
			$q="DELETE FROM docs_signed WHERE dsAFID={$emp} AND dsDocID={$doc}";
			$r1=db::SQL($q);
			
			$q="SELECT * FROM employee_onboarding WHERE eoAFID={$emp} AND eoDocsToSign LIKE '%{$doc}%'";
			$res=db::PrepareLoadSingleRow($q);
			$eoID=$res['eoID'];
			
			
			
			if(is_null($res['eoSignedDocs']) || $res['eoSignedDocs']=='')
				$signedDocs=array();
			else
				$signedDocs=explode('|',$res['eoSignedDocs']);
			
			if (($key = array_search($doc, $signedDocs)) !== false) {
				unset($signedDocs[$key]);
			}
			$signedDocsStr=implode('|',$signedDocs);
			
			/***********  GET NEWEST DOC TO SIGN **********/
			$new_ver='';
			$old_doc=$doc;
			
			while ( db::PrepareLoadSingleRow('SELECT * FROM onboarding_files WHERE ofReplaceFile ='.$doc) !== false ) {
				echo '<br>'.'SELECT * FROM onboarding_files WHERE ofReplaceFile ='.$doc;
				$_RES = db::PrepareLoadSingleRow('SELECT * FROM onboarding_files WHERE ofReplaceFile ='.$doc);
				$doc=$_RES['ofID'];
				$new_ver = ' The newest version of document was assigned for signing.';
			}
			/*****************/
			
			if(is_null($res['eoDocsRemainToSign']) || $res['eoDocsRemainToSign']==''  )
				$docsRemainToSign=array();
			else
				$docsRemainToSign = explode('|',$res['eoDocsRemainToSign']);
			
			$docsRemainToSign[]=$doc;
			$docsRemainToSignStr=implode('|',$docsRemainToSign);
			
			$DocsToSignStr=$res['eoDocsToSign'];
			$DocsToSignStr=str_replace($old_doc,$doc,$DocsToSignStr);
			
			$q="UPDATE employee_onboarding SET eoSignedDocs='{$signedDocsStr}', 
			eoDocsRemainToSign='{$docsRemainToSignStr}',eoDocsToSign='{$DocsToSignStr}'
			WHERE eoID={$eoID} ";
			$r2=db::SQL($q);
			
			$q="UPDATE employee_onboarding SET eoCompletedOn=NULL,eoCompletedBy=NULL
				WHERE 	eoAFID={$emp}  AND eoOTID={$eoOTID}";
			$r3=db::SQL($q);
			 
			if ($r1 && $r2 && $r3) 
			msg::set('The document was marked as unsigned.'.$new_ver,'success');
			else msg::set('There was a problem to remember sign.','error');
		}
		else msg::set('Not correct data provided.','error');
	 
	   cf::Redirect('employee-details?id='.$emp.'&cmd=view-docs&onbID='.$_GET['onbID']);
		
	}
	
	public static function TerminateEmployee(){
		$in_post = false;
		if (!empty($_POST)){
		  try	{
			  EmployeeModel::validateTerminateEmployee("server",true);
			  $today = new DateTime();
			  $date = DateTime::createFromFormat(USER_DATE,$_POST['afDateLeaving']);
			  if ($today->format('Y-m-d') == $date->format('Y-m-d')){
				  $q='UPDATE apply_forms SET `afDateLeaving`="%s", `afLeavingReason`="%s",
				  `afTerminatedOrRetired` = "%s",afRMID ='.MANAGER_ID.', afActive ="Terminated" WHERE afID='.$_GET['id'];
			  }
			  else {
				  $q='UPDATE apply_forms SET `afDateLeaving`="%s", `afLeavingReason`="%s",
				  `afTerminatedOrRetired` = "%s"   WHERE afID='.$_GET['id'];
			  }
			   $sql=sprintf(
						$q,
						val::convDate(USER_DATE,MYSQL_DATE,$_POST['afDateLeaving']),
						$_POST['afLeavingReason'],
						$_POST['afTerminatedOrRetired']
					);
			  $res = db::SQL($sql);
			  if (! $res){
				msg::set('There was error in saving termination data','error');
			  }
			  else {
				  
				if (isset($_POST['afNewManager']) && $_POST['afNewManager'] != '{!--Empty--!}') {
					$q='UPDATE apply_forms SET afRMID = '.$_POST['afNewManager'].'
				    WHERE afRMID ='.$_GET['id'];
					$res = db::SQL($q);
				}
			
				if (isset($_POST['afNewResponsible']) && $_POST['afNewResponsible'] !='{!--Empty--!}' ){
					$q='UPDATE onboarding_tasks SET otAFID = '.$_POST['afNewResponsible'].'
				    WHERE otAFID ='.$_GET['id'];
					$res = db::SQL($q);
				}
				
				$q='SELECT * FROM apply_forms WHERE afID='.$_GET['id'];
				$r=db::PrepareLoadSingleRow($q);
				EmployeeOnboardingModel::AssignEmployeeOffboardingTasks($_GET['id'],$r['afHiredForPID'],
				$r['afTerminatedOrRetired']);

				
				$today = new DateTime();
				$date = DateTime::createFromFormat(USER_DATE,$_POST['afDateLeaving']);
				
				if ($today->format('Y-m-d') == $date->format('Y-m-d')){
					msg::set('The employee has been terminated.',
				   'success');
				   cf::Redirect(ADMIN.'employee-details?id='.$_GET['id'].'&type=terminated');
				}
				else {
					msg::set('The employee is now in leave notice. Leaving '.$date->format(USER_DATE).'.',
					'success');
					cf::Redirect(ADMIN.'employee-details?id='.$_GET['id']);
				}
			  }
			  
		  }
		  catch (Exception $e) 
		   {
			  $in_post = true;
			 
			  foreach ($_POST as $k=>$v){
				 self::$data[$k] = $v;
			  } 
			 
		   }
			
		}
		
		
		if (empty($_POST) && ! $in_post){
			$r = db::PrepareLoadSingleRow('SELECT * FROM apply_forms WHERE afID='.$_GET['id']);
			self::$data['afDateLeaving']=$r['afDateLeaving'];
			self::$data['afLeavingReason']=$r['afLeavingReason'];
			self::$data['afTerminatedOrRetired']=$r['afTerminatedOrRetired'];
			
			if (!isset(self::$data['afTerminatedOrRetired']))
				self::$data['afTerminatedOrRetired'] = 'terminated';
		}
		
		EmployeeModel::validateTerminateEmployee("client",true);
		EmployeeView::TerminateEmployeeView(self::$data, self::$pg, EmployeeModel::$fldLbl);
	
	}


	 
	 final static function DeleteApplication(){
		 
		 if (isset($_GET['deid']) and $_GET['deid']!=0 ) {
			 $ID = $_GET['deid'];
			 
			 if ( db::SQL('DELETE FROM apply_forms WHERE afID='.$_GET['deid']))	{
				$txt="<b>Application with ID {$ID} was deleted.</b><br>
				The following data was deleted:<br>
				&#8226; User profile<br>";
				
				//********  CV delete *********************
				$q="SELECT * FROM cvs WHERE cvafID={$ID}";
				$r=db::PrepareLoadSingleRow($q);
				$CVfileName=$r['cvFileName'];
				$CVID=$r['cvID'];
				$CVfile = UPLOADS_DIR."application_cv{$CVID}.file";
				unlink($CVfile);
				$q="DELETE FROM cvs WHERE cvafID={$ID}";
				$r=db::SQL($q);
				$txt.="&#8226; Application CV incl. the CV file {$CVfileName} ({$CVfile})<br>";
				
				//  Photo delete
				$photoFile=IMAGES_DIR."form_photo/{$ID}.jpg";
				$res = unlink($photoFile);
				if ($res) $txt.="&#8226; Photo file ({$photoFile})<br>";
				else $txt.="&#8226; No photo file found<br>";
				
				// **********  DELETE CREDENTIALINGS ************
				$q='SELECT * FROM employee_credentialing WHERE ecAFID={$ID}';
				$res=db::PrepareLoad($q);	
				$cr = array();
				foreach ($res as $r){
					if (!empty($r['ecFileName'])){
						$cr[]=$r['ecFileName'];
						$fileName=UPLOADS_DIR."credentialings/credentialing_{$ID}";
						//unlink($fileName);
					}
				}
				if (!empty($cr)){
					$txt.='&#8226; Credentialing incl. the files: '.implode('',$cr).'<br>';
				}
				else {
					$txt.='&#8226; Credentialing. No files found.<br>';
				}
				$q="DELETE FROM employee_credentialing WHERE ecAFID={$ID}";
				db::SQL($q);
				
				//************* DELETE BOARDINGS**********
				$q="DELETE FROM employee_onboarding  WHERE eoAFID={$ID}";
				db::SQL($q);
				$txt.='&#8226; Onboarding and off-boarding tasks<br>';
				
				//******** DELETE LOGIN  RECORDS ************
				$q="DELETE FROM  fields_history WHERE 	fhAFID={$ID}";
				db::SQL($q);
				
				$q="DELETE FROM logs WHERE lafID={$ID}";
				db::SQL($q);
				
				$txt.='&#8226; Login records and position history<br>';
				
				
				//********** DELETE EMPLOYEE FILES **********
				$q="SELECT * FROM fcfilesbyuser  WHERE  fcfbuUserID={$ID}";
				$res=db::PrepareLoad($q);	
				$files = array();
				foreach ($res as $r){
					$file = UPLOADS_DIR.'files/'.$r['fcfbuFileSavedAs'];
					$files[]=$r['fcfbuFileName'];
					unlink($file);
				}
				
				$q="DELETE FROM fcfilesbyuser  WHERE  fcfbuUserID={$ID}";
				db::SQL($q);
				
				if(!empty($files))
					$txt.='&#8226; Employee files incl. the files: '.implode(', ',$files).'<br>';
				else
					$txt.='&#8226; No employee files found<br>';
				
				
				//************  CLEAR CONNECTIONS ***********
				$q="SELECT * FROM apply_forms WHERE afConnectedToProfile LIKE '%{$ID}%'";
				$res=db::PrepareLoad($q);		
				$unprof = array();
				foreach ($res as $r){
					$appID=$r['afID'];
					$cons = explode('|',$r['afConnectedToProfile']);
					$newCons=array();
					foreach ($cons as $c){
						if ($c!=$ID) $newCons[]=$c;
					}
					$q = "UPDATE apply_forms SET afConnectedToProfile='".implode('|',$newCons)."' WHERE afID={$appID}";
					db::SQL($q);
					$unprof[]= $appID;
				}
				if (empty($unprof))
					$txt.='&#8226; No connection with other profiles found<br>';
				else
				$txt.='&#8226; Connection with other profiles was cleared. Application unconnected from profiles: '
				.implode(',',$unprof).'<br>';
				
				
				//**********  DONE ALL ****************
				
				$success='success';
			 } 
			 else {
				$txt='There was a problem to deleta application.';
				$success='error'; 
			}
		 
		 $msg='<span style="display:inline-block;text-align:center;width:100%">
				<span style="text-align:left;display:inline-block;">
				'.$txt.'
				</span></span>';
		 
		 msg::set($msg,$success);
		 cf::Redirect(ADMIN .'delete-employee');
	 }
	 }
	 
	 final static function AllApplications()
    { 
        msg::get();

		 $query='SELECT * FROM apply_forms
		 LEFT JOIN positions ON pID = afHiredForPID WHERE afID > 1';
        ApplicationView::AllApplications(self::$pg, $query, EmployeeModel::$fldLbl, self::$rq);
    }
	
	
    final static function All()
    { 
        msg::get();
        $cond = EmployeeModel::StatusCond(self::$rq);
        ApplicationView::All(self::$pg, EmployeeModel::query($cond), EmployeeModel::$fldLbl, self::$rq);
    }

    static function Summary()
    {
        msg::get();
        ApplicationView::Summary(self::$data, self::$pg, EmployeeModel::$fldLbl);
    }

    public static function ActiveToUrl()
    {
        $url = self::$data[EmployeeModel::$dt["pfx"] . "Active"];

        switch ($url) {
            case 'Hired':
                if (self::$data["afHiredForPID"] == 9999) {
                    $url = "manager-employees";
                } else {
                    $url = "employees";
                }
                break;
			  case 'Inactive':
                $url = "employees-terminated";
                break;
            case 'Inactive':
                $url = "inactive-employees";
                break;
            default:
                $url = "employees";
        }

        return $url;
    }

    public static function getBackUrl()
    {
        if (isset(AppModel::$admin["waaAFID"])) {
            $url = self::$pg["hired"]["url"];
        } else {
            $url = self::ActiveToUrl();
        }

        $params = Array("name" => "Back to " . strtolower(self::$pg["items"]), "url" => $url, "icon" => "fa fa-chevron-circle-left", "args" => array("class" => "btn btn-info"));
        return $params;
    }

    final static function Details($lists = array())
    {
        msg::get();

        if (filter_has_var(INPUT_GET, "cmd")) {
            if (filter_input(INPUT_GET, "cmd") === "remove") {
				
                $ecID = intval(filter_input(INPUT_GET, "ecID", FILTER_SANITIZE_NUMBER_INT));
                if ($ecID > 0) {
                    $ecData = EmployeeCredentialingModel::dataById($ecID);
                    if ($ecData) {
                        if (EmployeeCredentialingModel::RemoveFile($ecData)) {
                            msg::set("File has been removed!", "success");
                            cf::Redirect(ADMIN . 'employee-details?id='.self::$id.'&cmd=remove&ecID=0');
                        }
                    }
                }
            }
			
			  if (filter_input(INPUT_GET, "cmd") === "undo_applicable") {
				  
                $ecID = intval(filter_input(INPUT_GET, "ecID", FILTER_SANITIZE_NUMBER_INT));
                if ($ecID > 0) {
					
					   if (EmployeeCredentialingModel::RemoveNotApplicForMe($ecID)) {
						   
                            msg::set("Credential is applicable to employee again.", "success");
							
                    }                          
                 }
				 cf::Redirect(ADMIN . 'employee-details?id='.self::$id);
              }
 

            if(filter_has_var(INPUT_GET, "cmd") && filter_input(INPUT_GET, "cmd") === "autologin") {
                EmployeeController::Autologin();
            }
        }


        if (empty($lists)) {
            $lists = ["positions" => PositionModel::AllActiveKeyName(), "jobs" => JobModel::AllIdNameArr()];
        }
        $lists["position"] = ApplyFormController::LoadPosition(self::$data["afPID"]);
		 
       
		  
		 $lists['data'] = self::$data;

        self::$data["cvs"] = CVModel::getCVsForApplication(self::$id);
		
        self::$data["onboarding_tasks"] = OnboardingModel::AllActiveKeyNameEmpl(self::$id,'on');
		
		
		  self::$data["offboarding_tasks"] = OnboardingModel::AllActiveKeyNameEmpl(self::$id,'off');
		
		
        EmployeeModel::Detailsvalidate("client", $lists, true);
		
        EmployeeView::Details(self::$data, self::$pg, EmployeeModel::$fldLbl, $lists);
    }

    final static function SetPassword($lists = array())
    {
        msg::get();
        if (filter_has_var(INPUT_POST, "save-btn")) {
            if (ProfileModel::changePassword(false, false, self::$id)) {
                cf::Redirect(self::getBackUrl()["url"]);
            }
        }
        EmployeeView::SetPassword(self::$data, self::$pg, EmployeeModel::$fldLbl, $lists);
    }

    public static function CheckForUndoOnboarding($renderDetails = true)
    {
        if (filter_has_var(INPUT_GET, "cmd") && (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "undo")) {
            $eoID = intval(filter_input(INPUT_GET, "eoid", FILTER_SANITIZE_NUMBER_INT));
            if ($eoID > 0) {
                $q = 'update employee_onboarding set eoCompletedOn=null, eoCompletedBy=null where eoID=' . $eoID;
                db::SQL($q);

                if($renderDetails) {
                    cf::Redirect(ADMIN . "employee-details?id=".self::$id."&cmd=undo&eoid=0");
                }
            }
        }
		
		 if (filter_has_var(INPUT_GET, "cmd") && (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "mark")) {
            $eoID = intval(filter_input(INPUT_GET, "eoid", FILTER_SANITIZE_NUMBER_INT));
            if ($eoID > 0) {
                $q = 'update employee_onboarding set eoCompletedOn="'.val::currentDate('Y-m-d H:i:s')
				        .'", eoCompletedBy='.AppModel::$usr["afID"].' where eoID=' . $eoID;
                db::SQL($q);

                if($renderDetails) {
                    cf::Redirect(ADMIN . "employee-details?id=".self::$id."&cmd=undo&eoid=0");
                }
            }
        }
		
		
    }

    public static function Autologin() {
        $afID = intval(filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT));
        if($afID > 0) {
            $key = ProfileModel::generateKey();
            $q = 'update apply_forms set afKey="' . $key . '" where afID=' . $afID;
            db::SQL($q);

            cf::Redirect(WP.'?autologin='.$key);
        }
    }

    final static function DetailsSave($lists = array())
    { 
        if (self::CheckForOnboardingTasksComplete(isset(AppModel::$admin["waaAFID"]) ? AppModel::$admin["waaAFID"] : 0)) {
            return self::Details();
        }

        $lists["position"] = ApplyFormController::LoadPosition(self::$data["afPID"]);

        try {
			
			  if (isset($_POST['HiredForPID'])) self::$data['HiredForPID']= $_POST['HiredForPID'];
            $lists["data"] = self::$data;
			  
            ApplyFormModel::DetailsValidate("server", $lists, true, true);
			
            CVModel::validateUploadedFiles();
			

            $mail = filter_input(INPUT_POST, "afEmail", FILTER_SANITIZE_EMAIL);
            $q = "select count(afID) as cnt from apply_forms where afID<>%d and afActive='Hired' and afEmail='%s'";
            $q = sprintf($q, self::$id, $mail);
            $cnt = db::PrepareLoadSingleCellValue($q);
			
			if (isset(EmployeeController::$data['afActive']) && EmployeeController::$data['afActive']=='Terminated' )
			$cnt = 0;   

            if ($cnt > 0) {
                val::setError("afEmail", "There's already an employee with this email address.");
            }

            val::throwServerRunError();
        } catch (Exception $e) {
			
			   foreach ($_POST as $k => $v) {
				
					 if ($k=='afAdded')  {
					    $v=val::convDate(GLOBAL_PHP_DATETIME,MYSQL_DATE_TIME,$v );}
					 if ($k=='afBirthday') {
					    $v=val::convDate(USER_DATE,MYSQL_DATE_TIME,$v );}
					 if ($k=='afDateHired') {
						$v=val::convDate(USER_DATE,MYSQL_DATE,$v );}
					 if ($k=='afStartDate') {
						$v=val::convDate(USER_DATE,MYSQL_DATE,$v );}
					 if ($k=='afDateLeaving') { 
						$v=val::convDate(USER_DATE,MYSQL_DATE,$v );}
				 
                self::$data[$k] = $v;
            }
			
            return self::Details();
        }


        $id = self::$id;

        db::Save(ApplyFormModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? msg::set(TXT_SAVE_OK, "success") : msg::set(TXT_SAVE_ERROR, "error");

        CVModel::processUploadedFiles($id);

        $oldSalary = EmployeeController::$data["afSalary"];
        $oldSalaryType = EmployeeController::$data["afSalaryType"];
        $newSalary = filter_input(INPUT_POST, "afSalary", FILTER_SANITIZE_STRING);
        $newSalaryType = filter_input(INPUT_POST, "afSalaryType", FILTER_SANITIZE_STRING);

        if (($oldSalary !== $newSalary) || ($oldSalaryType !== $newSalaryType)) {
            FieldHistoryModel::AddHistory("salary", $id, PositionModel::formatSalary($newSalary) . " " . strtolower($newSalaryType));
        }

        $oldPosition = EmployeeController::$data["afHiredForPID"];
        $newPosition = filter_input(INPUT_POST, "afHiredForPID", FILTER_SANITIZE_NUMBER_INT);
        if ($oldPosition !== $newPosition) {
            $oldPosition = PositionModel::dataById($newPosition);
            $position = PositionModel::dataById($newPosition);

            $q = "update apply_forms set afRMID=null where afRMID=".$id;
            db::SQL($q);

            FieldHistoryModel::AddHistory("position", $id, $position["pName"]);
            EmployeeOnboardingModel::ChangeEmployeePosition($id, $newPosition);
            EmployeeCredentialingModel::ChangeEmployeePosition($id, $newPosition);
        }

        $oldDepartment = EmployeeController::$data["afDID"];
        $newDepartment = filter_input(INPUT_POST, "afDID", FILTER_SANITIZE_NUMBER_INT);
        if ($oldDepartment !== $newDepartment) {
            $department = DepartmentModel::dataById($newDepartment);
            FieldHistoryModel::AddHistory("department", $id, $department["dName"]);
        }

        $oldOffice = EmployeeController::$data["afCOID"];
        $newOffice = filter_input(INPUT_POST, "afCOID", FILTER_SANITIZE_NUMBER_INT);
        if ($oldOffice !== $newOffice) {
            $office = CompanyOfficeModel::dataById($newOffice);
            FieldHistoryModel::AddHistory("office", $id, $office["coName"]);
        }

        $fileName = str_replace("{---LiveID---}", $id, ApplyFormModel::IMG_FILENAME);
        if (filter_has_var(INPUT_POST, "photoCmd") && (filter_input(INPUT_POST, "photoCmd", FILTER_SANITIZE_STRING) === "remove")) {
           
			$photoFile = "../" . ApplyFormModel::IMG_PATH . $fileName;
            if (file_exists($photoFile)) {
                unlink($photoFile);
            }
        }
        img::move_images_from_temp("afPhoto", true, $fileName);

        $ids = (filter_has_var(INPUT_POST, "afResponsibleForPID")) ? array_values(filter_input(INPUT_POST, "afResponsibleForPID", FILTER_DEFAULT, FILTER_FORCE_ARRAY)) : [];
        ManagerPositionsModel::SaveManagerPositions($id, $ids);

        $ids = (filter_has_var(INPUT_POST, "afResponsibleForCOID")) ? array_values(filter_input(INPUT_POST, "afResponsibleForCOID", FILTER_DEFAULT, FILTER_FORCE_ARRAY)) : [];
        ManagerOfficesModel::SaveManagerOffices($id, $ids);
		
		
        ApplyFormModel::UpdateAdminRecord($id, $newPosition, filter_input(INPUT_POST, "afEmail", FILTER_SANITIZE_EMAIL), self::$data["afToken"]);

        cf::Redirect(self::getBackUrl()["url"]);
    }

    public static function AllForManager()
    {
        $afID = intval(AppModel::$admin["waaAFID"]);
        $cond = 'afActive="Hired" and afRMID=' . $afID;
        $q = EmployeeModel::query($cond);
        EmployeeView::All(self::$pg, $q, EmployeeModel::$fldLbl, self::$rq);
    }

    public static function DetailsForManager()
    {
        $afID = intval(AppModel::$admin["waaAFID"]);

        if (in_array(self::$data["afActive"], ["Hired"])) {
            if (self::$data["afRMID"] == $afID) {
                self::Details([]);
            } else {
                cf::Redirect(self::$pg["hired"]["url"]);
            }
        } else {
            cf::Redirect(self::$pg["hired"]["url"]);
        }
    }

    public static function DetailsSaveForManager()
    {

        if (self::CheckForOnboardingTasksComplete(isset(AppModel::$admin["waaAFID"]) ? AppModel::$admin["waaAFID"] : 0)) {
            cf::Redirect(self::$pg["hired"]["url"]);
        }

        $afID = intval(AppModel::$admin["waaAFID"]);

        if (in_array(self::$data["afActive"], ["Hired"])) {
            if (self::$data["afRMID"] == $afID) {
                self::DetailsSave([]);
            } else {
                cf::Redirect(self::$pg["hired"]["url"]);
            }
        } else {
            cf::Redirect(self::$pg["hired"]["url"]);
        }
    }

    public static function CheckForOnboardingTasksComplete($id)
    {
        if (filter_has_var(INPUT_POST, "complete-onboarding-tasks")) {
            EmployeeOnboardingModel::CompleteEmployeeOnboardingTasks(
                $id,
                filter_input(INPUT_POST, "onboarding_tasks", FILTER_DEFAULT, FILTER_FORCE_ARRAY)
            );
            return true;
        } else {
            return false;
        }
    }
	
	public static function deleteFile(){
		$fid = intval(filter_input(INPUT_GET, 'fid', FILTER_SANITIZE_NUMBER_INT));
       $rec = db::PrepareLoadSingleRow("select * from  fcfilesbyuser where 	fcfbuID={$fid}");
	   if($rec == false) {msg::set('No such file', 'error');}
	   else {
	   $realFilePath="../add/uploads/files/".$rec['fcfbuFileSavedAs'];
	   unlink($realFilePath );
	   db::SQL('DELETE  FROM fcfilesbyuser WHERE fcfbuID='.$fid);
		msg::set('File was deleted', "success");
		cf::redirect('/admin/employee-details?tab=ass_files&id='.$_GET['id']);
	   }
	}
	
	public static function downloadFile(){
        $fid = intval(filter_input(INPUT_GET, 'fid', FILTER_SANITIZE_NUMBER_INT));
   
        $rec = db::PrepareLoadSingleRow("select * from  fcfilesbyuser where 	fcfbuID={$fid}");
      
        if($rec == false) {
            die();
        } else {
            $filePath = "../add/uploads/files/".$rec['fcfbuFileSavedAs'];
			
            if(file_exists($filePath)) { 
			      $name=$rec['fcfbuFileName'].'.'.$rec['fcfbuFileЕxt'];
                header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
                header("Cache-Control: public");
                header("Content-Type: application/binary");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length:".filesize($filePath));
                header('Content-Disposition: attachment; filename="'.$name.'"');
                readfile($filePath);
                die();
            } else {            
                die();
            }
        }
    }
	
	public static function getExtension($fname){
		
	}
	
	public static function uploadFile(){
		msg::prepare();
		$msg = array();
			
		$filename =  filter_input(INPUT_POST, 'filename', FILTER_SANITIZE_STRING);
		$userID =  intval(filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT));
		$cat_id =  intval(filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT));
		$visible = filter_input(INPUT_POST, 'visible', FILTER_SANITIZE_STRING);
		
		if($cat_id == 0) {$m='Please select a category.';
		   $msg[]=$m;
		   val::setError('category_id',$m);}
		if($filename == '') {
			$m='Description is required.';
			 $msg[]=$m;
		    val::setError('filename',$m);}
		/****
		if(strpos($filename,'.') !== false) {
			$m='Dot is not allowed in description.Please enter description as you want employee to see it <b>w/o extension</b>.';
			 $msg[]=$m;
		    val::setError('filename',$m);}
		***/	
		if ($_FILES['myfile']['size'] == 0 ) {
			$m='File is required.';
			$msg[]=$m;
		   val::setError('myfile',$m);}
		else {
		if ($_FILES['myfile']['error'] != 0 ) {
			$m='There is error in file upload.';
			$msg[]=$m;
		   val::setError('myfile',$m);}
		else {
			$ext = pathinfo($_FILES['myfile']['name'],PATHINFO_EXTENSION);
			
			if (!in_array(strtoupper($ext),array('PDF','DOC','DOCX','XLS','XLSX','JPG'))){
				$m='<b>'.$_FILES['myfile']['name']."</b> is not allowed file<br>The only allowed file types are 'PDF','DOC','DOCX','XLS','XLSX','JPG'.";
				$msg[]=$m;
				val::setError('myfile',$m);}
			}
		}
		if (!empty($msg)) 
			msg::set('<b>There are errors in file upload:</b><br>'.implode('<br>',$msg),'error');
		else {             
			$newFileName=val::currentDate('YmdHis');
			$newFile='../add/uploads/files/'.$newFileName;
			move_uploaded_file($_FILES["myfile"]["tmp_name"], $newFile);
			$sql='INSERT INTO fcfilesbyuser SET fcfbuUserID='.$userID
			.',	fcfbuVisible="'.$visible.'"'
			.',fcfbuFileCatID='.$cat_id
			.',fcfbuFileSavedAs="'.$newFileName.'"'
			.',fcfbuFileName="'.$filename.' ('.$_FILES["myfile"]["name"].')"'
			.',	fcfbuFileЕxt="'.$ext.'"';
			
			db::SQL($sql);
    		msg::set('File <b>'.$_FILES["myfile"]["name"].'</b> was saved.','success');
			$_POST = array();
			cf::redirect('/admin/employee-details?tab=ass_files&id='.$_GET['id']);
			
       }
	}	

	
}