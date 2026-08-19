<?php

/** The CompanyOffice controller */
class EmployeeController extends AppController
{


    const __DUMP__ = false;

    public static $id, $data, $pg;
		
		final static function AdminMassTask(){
		error_reporting(E_ALL);
		$q="SELECT * FROM `apply_forms` WHERE afEmplNumber <> '' AND afEmplNumber IS NOT NULL AND `afSalaryType` = 'Hourly' AND `afActive` = 'Hired' AND `afNoPayrollJA` = 'no'";
		$res=db::PrepareLoad($q);
		if ($res !== false){
			$i=1;
			foreach ($res as $r){
				echo $i.' '.$r['afID'].' '.$r['afEmplNumber'].' '.$r['afFirstName'].' '.$r['afLastName'].'<br>';
				$i++;
				$q="INSERT INTO p_timeclock_worksegment SET RecordId=0,
				EmployeeNumber='{$r['afEmplNumber']}',
				TimeIn='2018-11-22 00:00:00',
				TimeOut='2018-11-22 00:00:00',
				WorkingHours=8,
				Updated_On='2018-11-22 00:00:00',
				Type='Holiday'";
				
				$z=db::SQL($q);
				
				$sql="SELECT * FROM  p_listofweeks WHERE EMPLOYEE_NUMBER='{$r['afEmplNumber']}' AND STARTDATE='2018-11-19'";
				$__res=db::PrepareLoad($sql);
				if ($__res == false){
					$qry1="INSERT INTO  p_listofweeks SET  EMPLOYEE_NUMBER='{$r['afEmplNumber']}',STARTDATE='2018-11-19',ENDDATE ='2018-11-25',
					EMPLOYEE_ID={$r['afID']}, MANAGER_ID={$r['afRMID']}, SIGNED_STATUS='No'";
					$_res=db::SQL($qry1);
				}
			}
				
				
				
		}
		
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
			
			$doc['otURLName']=str_replace('https://hris.maidenlanemedical.com',WP,$doc['otURLName']);// due bad routing
			
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
				    $details = new ReflectionFunction('printing');
                    print $details->getFileName() . ':' . $details->getStartLine();
                    print ("graa");
                    
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
		$q ='UPDATE apply_forms SET afActive = "Terminated",afEmplNumber = ""
			 WHERE afDateLeaving = "'.$today->format('Y-m-d').'"';
		//echo $q;exit;
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
			afTerminatedOrRetired = NULL, afChangedToTerminated = NULL, afPaymentStatus = 'Active'
			WHERE afId=".$_GET['id'];
			
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
			else msg::set('There was a problem to save data.','error');
		}
		else msg::set('Not correct data provided.','error');
	 
	   cf::Redirect('employee-details?id='.$emp.'&cmd=view-docs&onbID='.$_GET['onbID']);
		
	}
	
	public static function DaysOffEmployee(){
		echo '<h4>Days Off</h4>';
		EmployeeView::DaysOffEmployee($_GET['id']);
	}
	
	public static function TerminateEmployee(){ 
		$in_post = false;
		if (!empty($_POST)){
		  try	{
			  EmployeeModel::validateTerminateEmployee("server",true);
			  $today = new DateTime();
			  $date = DateTime::createFromFormat(USER_DATE,$_POST['afDateLeaving']);
			  if ($today->format('Y-m-d') == $date->format('Y-m-d')){
				  $now = $today->format('Y-m-d');
				  $q='UPDATE apply_forms SET `afDateLeaving`="%s", `afLeavingReason`="%s",
				  `afTerminatedOrRetired` = "%s", afChangedToTerminated="'.$now.'",afPaymentStatus="Terminated",
				  afRMID ='.MANAGER_ID.', afActive ="Terminated" WHERE afID='.$_GET['id'];
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
				
				if ($today->format('Y-m-d') >= $date->format('Y-m-d')){
					$q ='UPDATE apply_forms SET afActive = "Terminated",afEmplNumber = ""
					WHERE afDateLeaving <= "'.$today->format('Y-m-d').'"';
		
					$res = db::SQL($q);
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
		RoleHelper::requireAdmin();
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
	
	
	public static function RenderChangeRequests($state)
	{
		//%c/%e/%Y
			$qry = "
			SELECT *, 
			CONCAT(afFirstName,' ',afLastName) AS UserName,
			date_format(tcrDate,'%m/%d/%Y')  AS USdate,
			date_format(tcrDateTimeSent,'%m/%d/%Y %h:%i %p')  AS USRequested,
			date_format(tcrDateTimeProcessed,'%m/%d/%Y %h:%i %p')  AS USProcessed
			
			FROM timeclock_change_requests  
			LEFT JOIN apply_forms ON tcrAFID = afID
			WHERE tcrState = '{$state}'";
			
			table::create("tsr__".$state, "tcrDate","DESC",ADMIN_TABLES_ROWS_PER_PAGE);
			

			table::$cols[] = Array("Date", "USdate",array('width'=>'55px'));
			table::$cols[] = Array("Employee name", "UserName",array('width'=>'200px'));
			table::$cols[] = Array("Requested", "USRequested",array('width'=>'150px'));
			table::$cols[] = Array("Request", "tcrRequest");
			//table::$cols[] = Array("Reason", "tcrReason");
			
			
			if ($state === 'pending')
				table::$cols[] = Array("Action", "tcrAction",array("filtered" => false,'width'=>'165px'));
			else  
				table::$cols[] = Array("Approved/Declined", "USProcessed",array("filtered" => false,'width'=>'150px'));
			
            table::execute($qry);
           
            foreach(table::$data as $r => &$cells){
	            $q="SELECT * FROM apply_forms  WHERE afID={$cells['tcrAFID']}";
				$user=db::PrepareLoadSingleRow($q);
				
				
				$week = TimeClockFrontController::calculatestartdateandenddateofweek($cells['tcrDate']);
				$cells['UserName']='<a target="blank" class="emp-link"  
				href="employee-details?timeclock_tab=true&id='.$cells['tcrAFID'].'&empnumber='.$user['afEmplNumber']
				.'&sdate='.$week['weekstartdate'].'&edate='.$week['weekenddate'].'" >'
				.$cells['UserName'].'</a>';
				
				$cells['USdate']='<a target="blank" class="emp-link"  
				href="employee-details?timeclock_tab=true&id='.$cells['tcrAFID'].'&empnumber='.$user['afEmplNumber']
				.'&sdate='.$week['weekstartdate'].'&edate='.$week['weekenddate'].'" >'
				.$cells['USdate'].'</a>';
				
				
				
				if ($cells['tcrState']==='pending') { 
					$cells['tcrAction']= '<a href="#" class="emp-link" onclick="ask_for(\'approve\','.$cells['tcrID'].',\''.val::convDate( MYSQL_DATE,USER_DATE,$cells['tcrDate']).'\')">
					<span class="emo green"><i class="fa fa-thumbs-up" aria-hidden="true"></i></span>Aprove</a>&nbsp;
					
					<a href="#" class="emp-link" onclick="ask_for(\'decline\','.$cells['tcrID'].',\''.val::convDate( MYSQL_DATE,USER_DATE,$cells['tcrDate']).'\')" >
					<span class="emo red"><i class="fa fa-thumbs-down" aria-hidden="true"></i></span>Decline</a>';
				}
				else {
					// $cells['tcrDateTimeProcessed'] = val::convDate( MYSQL_DATE_TIME,USER_DATE_TIME,$cells['tcrDateTimeProcessed']);
				}
		
				//$cells['tcrReason']= '<pre style="border:0;background:transparent">'.$cells['tcrReason'].'</pre>';
			
            }
			table::load();
	}
	
	public static function ShowTSRequests(){ 
		form::openDiv(array("id" => "timeclock_request_content", "class" => "container-fluid"));
echo '
<style>

input[name="tabs"] {
display: none;
}
input[name="tabs"]#tab-1:checked ~ .content .tab:nth-of-type(1) {
display: inline-block;
}
input[name="tabs"]#tab-1:checked ~ .tabs label:nth-child(1) {
color: #242b36;
}
input[name="tabs"]#tab-1:checked ~ .tabs label:nth-child(1):after {
width: calc(100% - 40px);
transition: .3s;
}
input[name="tabs"]#tab-2:checked ~ .content .tab:nth-of-type(2) {
display: inline-block;
}
input[name="tabs"]#tab-2:checked ~ .tabs label:nth-child(2) {
color: #242b36;
}
input[name="tabs"]#tab-2:checked ~ .tabs label:nth-child(2):after {
width: calc(100% - 40px);
transition: .3s;
}
input[name="tabs"]#tab-3:checked ~ .content .tab:nth-of-type(3) {
display: inline-block;
}
input[name="tabs"]#tab-3:checked ~ .tabs label:nth-child(3) {
color: #242b36;
}
input[name="tabs"]#tab-3:checked ~ .tabs label:nth-child(3):after {
width: calc(100% - 40px);
transition: .3s;
}
input[name="tabs"]#tab-4:checked ~ .content .tab:nth-of-type(4) {
display: inline-block;
}
input[name="tabs"]#tab-4:checked ~ .tabs label:nth-child(4) {
color: #242b36;
}
input[name="tabs"]#tab-4:checked ~ .tabs label:nth-child(4):after {
width: calc(100% - 40px);
transition: .3s;
}



.tabs {
/*box-shadow: 0 20px 50px -25px rgba(0, 0, 0, 0.3);*/
overflow-X: auto;
-webkit-overflow-scrolling: touch;
position: relative;
white-space: nowrap;
background:transparent ! important;
}

.tabs label {
display: inline-block;
padding: 18px;
position: relative;
color: #bbc3d1;
font-weight: 700;
font-size: 16px;
text-transform: uppercase;
letter-spacing: 1.5px;
cursor: pointer;

}

.tabs label:after {
content: "";
background: #337ab7;/* #D22C3F;*/
width: 0;
height: 4px;
position: absolute;
bottom: 0;
left: 50%;
transform: translateX(-50%);
transition: .1s;
}

.container {
background: #FFF;
width: 100%;
margin: 0;/* 5px auto;*/
overflow: hidden;
}

.container .tab {
/* background-image: linear-gradient(#FAFAFA,#FFFFFF);*/
border-top:1px solid lightgray;
display: none;
padding: 0; /*10px;*/
width:100% ;
}



.red {color:red;position:relative;}
.container table tr td a:hover{text-decoration:none;color:blue;}
.green{color:#5cb85c;position:relative;}
.emo{font-size:1.5em;}
.emp-link{color:#000}
.emp-link:hover{color:#00F;text-decoration:underline ! important}

body{overflow-y:scroll;}
</style>

<script>
function updatePending(){  
	if(window._loadData === 1) setTimeout(updatePending , 1000);
	else {
		var rq={colNo: 1,colOrd: "desc",filterByLabel: "All...",tableId: "tsr__pending-table"};
		var tableID = "tsr__pending-table";
		table.LoadData(tableID,rq);
	}
}

function ask_for(action,request,date){ 
	if(confirm("Are you sure to "+action+ " request from "+date+"?")) {
	  var url="employee-details?to_do=tsr&task=update_request&action="+action+"&req="+request;
	  
	  var jqxhr = jQuery.get( url, function() {})
		  .done(function(data) {
			//  alert(data);
			var d = JSON.parse(data);
			if (d.result === "OK") {
				/*******
				var rq={
						colNo: 0,
						colOrd: "desc",
						filter: date,
						filterBy: 0,
						filterByLabel: "For date",
						tableId: "tsr__"+action+"d-table",
					};
					
				jQuery(	"#tsr__"+action+"d-list select").val(0);
				jQuery(	"#tsr__"+action+"d-list input").val(date);
				*******/
				rq={colNo: 1,colOrd: "desc",filterByLabel: "All...",tableId: "tsr__"+action+"d-table"};
					
				


					var tableID = "tsr__"+action+"d-table";
					table.LoadData(tableID,rq);
				
					setTimeout(updatePending , 1000);
				
				//jQuery("#"+action).click();
				myalert("Success",d.message );
				
				if(d.action=="hide") {
					jQuery(".timeclock_icon").hide();
					jQuery("#timeclock_request_button").removeClass("btn-danger").addClass("btn-secondary");
				}
				if(d.global=="hide") jQuery("#timeclock_request_span").hide();
			}
		  })
		  .fail(function() {
			myalert( "Error","Something bad happens. Probably no connection to server. Please tray again later." );
		  });
	}
}
</script>		


<div class="container" >
<input id="tab-1" class="pending" type="radio" name="tabs" value="pending" checked="checked" />
<input id="tab-2" class="approve" type="radio" value="approve" name="tabs" />
<input id="tab-3" class="decline" type="radio" value="decline" name="tabs" />

<div class="tabs">
<label for="tab-1" id="pending" >Pending</label>
<label for="tab-2" id="approve">Approved</label>
<label for="tab-3" id="decline">Declined</label>
</div>

<div class="content">

<div class="tab">';
	EmployeeController::RenderChangeRequests('pending');
echo '
</div>

<div class="tab">';
	EmployeeController::RenderChangeRequests('approved');
echo '
</div>

<div class="tab">';
	EmployeeController::RenderChangeRequests('declined');
echo '
</div>


</div>
</div>';
		form::closeDiv();
	}
	
	
	
	public static function UpdateRequest(){
		$res = array();
		
		$request=$_GET['req'];
		$action =$_GET['action'].'d';
		
		$sql="SELECT * FROM  timeclock_change_requests WHERE tcrID={$request}";
		$req=db::PrepareLoadSingleRow($sql);
		
		$req_date=val::convDate(MYSQL_DATE,USER_DATE,$req['tcrDate']);
		
		
		$userID=$req['tcrAFID'];
		$sql="SELECT * FROM apply_forms WHERE afID = {$userID} ";
		$user = db::PrepareLoadSingleRow($sql);
		
		$subject="Correction request {$action}";
		$message ="
		Dear {$user['afFirstName']} {$user['afLastName']},<br><br>
		Your request for a report to be correct has been {$action}.
		<br>
		<br>Report date:{$req_date}
		<br>Correction required: {$req['tcrRequest']}
		<br><br>
		".MAIL_FOOTER_HTML;
		
		//<br>Reason:{$req['tcrReason']}
		
		$now=date('Y-m-d H:i:s');
		$sql="UPDATE  timeclock_change_requests 
		SET 
		tcrDateTimeProcessed='{$now}',
		tcrState='{$action}'

		WHERE 	tcrID={$request}";
		
		$r = db::SQL($sql);
		
		@cf::sendMail($user['afEmail'],
					$subject, 
					$message, 
					$GLOBALS['EMAIL_MAILER_NAME'], 
					$GLOBALS['EMAIL_MAILER_ADDRESS']
				);
		
		
		if ($r){
			$res['message'] = '<b>Request was '.$action.'.</b> '.
			'The employee has been notified.';

			if ($action =='approved') $res['message'] .= '<br><b>Please correct the report as soon as possible.</b>';
			
					
			$res['result'] = 'OK';
		}
		else {
			$res['message'] = 'There was error in write action to DB. Please contact admin.';
			$res['result'] = 'FAIL';
		}
		
		$q="SELECT * FROM timeclock_change_requests WHERE tcrState='pending' AND tcrAFID={$user['afID']}";
		$r = db::PrepareLoad($q);
		if($r === false) $r=array();
		$z=count($r);
		if ($z == 0) $res['action']='hide';
		else $res['action']='none';
		
		$q="SELECT * FROM timeclock_change_requests WHERE tcrState='pending'";
		$r = db::PrepareLoad($q);
		if($r === false) $r=array();
		$z=count($r);
		if ($z == 0) $res['global']='hide';
		else $res['global']='none';
	
		echo json_encode($res);
		exit;
	}
	

    final static function Details($lists = array())  
    { 
		if (!filter_has_var(INPUT_GET, "id")) {
			RoleHelper::requireAdmin(); // ← ADD - block adding new
		}

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

//     final static function SetPassword($lists = array())
// {
//     msg::get();
//     if (filter_has_var(INPUT_POST, "save-btn")) {
//         if (ProfileModel::changePassword(false, false, self::$id)) {

//             // ===== SYNC PASSWORD TO ADMIN ACCOUNT IF EXISTS =====
//             $employeeId = (int)self::$id;
//             $newPass = filter_input(INPUT_POST, "pass", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

//             // Check if this employee has an admin account
//             $adminAccount = db::PrepareLoadSingleRow(
//                 "SELECT wa.waaID, wat.waatSalt, wa.waaEmail
//                  FROM webapp_admins wa
//                  LEFT JOIN webapp_admins_tokens wat ON wat.waatID = wa.waaID
//                  WHERE wa.waaAFID = $employeeId"
//             );

//             if ($adminAccount) {
//                 $salt = $adminAccount['waatSalt'];

//                 // If no salt yet, generate one from email
//                 if (empty($salt)) {
//                     $salt = cf::saltAnWord($adminAccount['waaEmail']);
//                     db::SQL(
//                         "UPDATE webapp_admins_tokens 
//                          SET waatSalt = '" . db::EscapeStr($salt) . "' 
//                          WHERE waatID = " . (int)$adminAccount['waaID']
//                     );
//                 }

//                 // Hash the password the same way admin login expects it
//                 $token = cf::hashTokenByPassAndSalt($newPass, $salt);

//                 db::SQL(
//                     "UPDATE webapp_admins_tokens 
//                      SET waatToken = AES_ENCRYPT('" . $token . "', '" . WEBAPP_SQL_SECRET_KEY . "')
//                      WHERE waatID = " . (int)$adminAccount['waaID']
//                 );
//             }
//             // ===== END SYNC =====

//             cf::Redirect(self::getBackUrl()["url"]);
//         }
//     }
//     EmployeeView::SetPassword(self::$data, self::$pg, EmployeeModel::$fldLbl, $lists);
// }

final static function SetPassword($lists = array())
{
    msg::get();
    if (filter_has_var(INPUT_POST, "save-btn")) {
        if (ProfileModel::changePassword(false, false, self::$id)) {
            
            // ============================================
            // SYNC PASSWORD TO ADMIN TOKEN IF APPLICABLE
            // ============================================
            $employeeId = (int)self::$id;
            $newPass = filter_input(INPUT_POST, "pass", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
            
            self::syncAdminToken($employeeId, $newPass);
            // ============================================
            
            cf::Redirect(self::getBackUrl()["url"]);
        }
    }
    EmployeeView::SetPassword(self::$data, self::$pg, EmployeeModel::$fldLbl, $lists);
}

/**
 * Syncs employee password to webapp_admins_tokens
 * Called after employee password is set/changed
 */
private static function syncAdminToken($employeeId, $plainPassword)
{
    if (empty($plainPassword) || $employeeId <= 0) return;

    // Check if this employee has an admin account
    $adminAccount = db::PrepareLoadSingleRow(
        "SELECT wa.waaID, wa.waaEmail, wat.waatSalt 
         FROM webapp_admins wa
         LEFT JOIN webapp_admins_tokens wat ON wat.waatID = wa.waaID
         WHERE wa.waaAFID = " . $employeeId
    );

    if (!$adminAccount) return; // Not an admin-linked employee

    $adminId = (int)$adminAccount['waaID'];
    $salt = $adminAccount['waatSalt'];

    // Generate salt if empty
    if (empty($salt)) {
        $salt = cf::saltAnWord($adminAccount['waaEmail']);
        
        // Check if token row exists
        $tokenExists = db::PrepareLoadSingleCellValue(
            "SELECT COUNT(*) FROM webapp_admins_tokens WHERE waatID = $adminId"
        );

        if ((int)$tokenExists > 0) {
            db::SQL(
                "UPDATE webapp_admins_tokens 
                 SET waatSalt = '" . db::EscapeStr($salt) . "' 
                 WHERE waatID = $adminId"
            );
        } else {
            db::SQL(
                "INSERT INTO webapp_admins_tokens (waatID, waatSalt, waatToken) 
                 VALUES ($adminId, '" . db::EscapeStr($salt) . "', '')"
            );
        }
    }

    // Generate and store the token
    $token = cf::hashTokenByPassAndSalt($plainPassword, $salt);

    db::SQL(
        "UPDATE webapp_admins_tokens 
         SET waatToken = AES_ENCRYPT('" . db::EscapeStr($token) . "', '" . WEBAPP_SQL_SECRET_KEY . "')
         WHERE waatID = $adminId"
    );
}

/**
 * Creates or updates a webapp_admins record for an employee based on
 * the "Is HR Manager" / "Is Interviewer" checkboxes, and stores the
 * hashed password in webapp_admins_tokens.
 *
 * Role IDs (from webapp_admins.role_id):
 *   1 = admin
 *   2 = hr_manager
 *   3 = interviewer
 */
// private static function syncRoleMapping($employeeId, $isHR, $isIV, $email, $plainPass = '')
// {
//     if ($employeeId <= 0 || empty($email)) return;

//     // Decide which role to apply (HR Manager wins over Interviewer if both checked)
//     $wantRole   = null;
//     $wantRoleId = null;
//     if ($isHR === 'yes') {
//         $wantRole   = 'hr_manager';
//         $wantRoleId = 2;
//     } elseif ($isIV === 'yes') {
//         $wantRole   = 'interviewer';
//         $wantRoleId = 3;
//     }

//     // ------------------------------------------------------------------
//     // Look for an existing admin record linked to this employee
//     // ------------------------------------------------------------------
//     $existing = db::PrepareLoadSingleRow(
//         "SELECT wa.waaID, wa.role, wa.role_id, wa.waaEmail, wat.waatSalt
//          FROM webapp_admins wa
//          LEFT JOIN webapp_admins_tokens wat ON wat.waatID = wa.waaID
//          WHERE wa.waaAFID = " . (int)$employeeId . "
//          ORDER BY wa.waaID DESC
//          LIMIT 1"
//     );

//     // ------------------------------------------------------------------
//     // Case A: NEITHER role wanted → clear role link
//     // ------------------------------------------------------------------
//     if ($wantRole === null) {
//         if ($existing) {
//             db::SQL(
//                 "UPDATE webapp_admins
//                  SET role = NULL, role_id = NULL, waaRole = 'Admin'
//                  WHERE waaID = " . (int)$existing['waaID']
//             );
//         }
//         return;
//     }

//     // ------------------------------------------------------------------
//     // Case B: Role IS wanted
//     // ------------------------------------------------------------------
//     $escapedEmail = db::EscapeStr($email, true);
//     $escapedRole  = db::EscapeStr($wantRole, true);

//     if ($existing) {
//         // Update existing admin record
//         $waaID = (int)$existing['waaID'];
//         db::SQL(
//             "UPDATE webapp_admins
//              SET role = '{$escapedRole}', role_id = {$wantRoleId}, waaRole = 'Admin', waaEmail = '{$escapedEmail}'
//              WHERE waaID = {$waaID}"
//         );
//     } else {
//         // Insert a new admin record
//         $escapedUsername = db::EscapeStr(strtolower(str_replace('@', '_', $email)), true);
//         db::SQL(
//             "INSERT INTO webapp_admins
//               (waaUsername, waaEmail, waaRole, waaAFID, waaRegDate, role, role_id)
//              VALUES
//               ('{$escapedUsername}', '{$escapedEmail}', 'Admin', " . (int)$employeeId . ", NOW(), '{$escapedRole}', {$wantRoleId})"
//         );

//         // Re-fetch to get the new waaID
//         $existing = db::PrepareLoadSingleRow(
//             "SELECT waaID, waaEmail FROM webapp_admins WHERE waaAFID = " . (int)$employeeId . " ORDER BY waaID DESC LIMIT 1"
//         );
//         if (!$existing) return;

//         $waaID = (int)$existing['waaID'];

//         // Create token row so login works
//         db::SQL(
//             "INSERT IGNORE INTO webapp_admins_tokens (waatID, waatSalt, waatToken)
//              VALUES ({$waaID}, '', '')"
//         );
//     }

//     // ------------------------------------------------------------------
//     // Sync password to webapp_admins_tokens (only when a password was provided)
//     // ------------------------------------------------------------------
//     if (!empty($plainPass)) {
//         self::syncAdminToken($employeeId, $plainPass);
//     }
// }
private static function syncRoleMapping($employeeId, $isHR, $isIV, $email, $plainPass = '')
{
    if ($employeeId <= 0 || empty($email)) return;

    $wantRole   = null;
    $wantRoleId = null;
    if ($isHR === 'yes') {
        $wantRole   = 'hr_manager';
        $wantRoleId = 2;
    } elseif ($isIV === 'yes') {
        $wantRole   = 'interviewer';
        $wantRoleId = 3;
    }

    $existing = db::PrepareLoadSingleRow(
        "SELECT wa.waaID, wa.role, wa.role_id, wa.waaEmail, wat.waatSalt
         FROM webapp_admins wa
         LEFT JOIN webapp_admins_tokens wat ON wat.waatID = wa.waaID
         WHERE wa.waaAFID = " . (int)$employeeId . "
         ORDER BY wa.waaID DESC
         LIMIT 1"
    );

    if ($wantRole === null) {
        if ($existing) {
            db::SQL(
                "UPDATE webapp_admins
                 SET role = NULL, role_id = NULL, waaRole = 'Admin'
                 WHERE waaID = " . (int)$existing['waaID']
            );
        }
        return;
    }

    $escapedEmail = db::EscapeStr($email);
    $escapedRole  = db::EscapeStr($wantRole);

    if ($existing) {
        $waaID = (int)$existing['waaID'];
        db::SQL(
            "UPDATE webapp_admins
             SET role = '{$escapedRole}', role_id = {$wantRoleId}, waaRole = 'Admin', waaEmail = '{$escapedEmail}'
             WHERE waaID = {$waaID}"
        );
    } else {
        $escapedUsername = db::EscapeStr($email);
        db::SQL(
            "INSERT INTO webapp_admins
               (waaUsername, waaEmail, waaRole, waaAFID, waaRegDate, role, role_id)
             VALUES
               ('{$escapedUsername}', '{$escapedEmail}', 'Admin', " . (int)$employeeId . ", NOW(), '{$escapedRole}', {$wantRoleId})"
        );

        $existing = db::PrepareLoadSingleRow(
            "SELECT waaID, waaEmail FROM webapp_admins WHERE waaAFID = " . (int)$employeeId . " ORDER BY waaID DESC LIMIT 1"
        );
        if (!$existing) return;

        $waaID = (int)$existing['waaID'];

        db::SQL(
            "INSERT IGNORE INTO webapp_admins_tokens (waatID, waatSalt, waatToken)
             VALUES ({$waaID}, '', '')"
        );
    }

    if (!empty($plainPass)) {
        self::syncAdminToken($employeeId, $plainPass);
    }
}

// public static function SyncPasswordToAdminAccount(int $employeeId, string $newPass)
public static function SyncPasswordToAdminAccount($employeeId, $newPass)
{
    if (empty($newPass) || $employeeId <= 0) return;

    // Switch to admin database — same as UpdateAdminRecord
    db::SwitchToRealByName();

    $mysql = db::$mysqli;
    $empId = (int)$employeeId;

    // Find the latest linked admin account
    $result = $mysql->query(
        "SELECT wa.waaID, wat.waatSalt, wa.waaEmail
         FROM webapp_admins wa
         LEFT JOIN webapp_admins_tokens wat ON wat.waatID = wa.waaID
         WHERE wa.waaAFID = $empId
         ORDER BY wa.waaID DESC
         LIMIT 1"
    );

    if (!$result) return;
    $row = $result->fetch_assoc();
    if (!$row) return;

    $waaID = (int)$row['waaID'];
    $salt  = $row['waatSalt'];
    $email = $row['waaEmail'];

    // Generate salt from email if missing
    if (empty($salt)) {
        $salt = cf::saltAnWord($email);
        $esc = $mysql->real_escape_string($salt);
        $mysql->query(
            "INSERT INTO webapp_admins_tokens (waatID, waatSalt, waatToken)
             VALUES ($waaID, '$esc', '')
             ON DUPLICATE KEY UPDATE waatSalt = '$esc'"
        );
    }

    // Hash password — identical to how LoginProcess verifies it
    $token = cf::hashTokenByPassAndSalt($newPass, $salt);
    $escapedToken = $mysql->real_escape_string($token);
    $secretKey    = $mysql->real_escape_string(WEBAPP_SQL_SECRET_KEY);

    // Write token using direct query — same pattern as saveQueries() and UpdateAdminRecord
    $mysql->query(
        "INSERT INTO webapp_admins_tokens (waatID, waatSalt, waatToken)
         VALUES ($waaID, '" . $mysql->real_escape_string($salt) . "', AES_ENCRYPT('$escapedToken', '$secretKey'))
         ON DUPLICATE KEY UPDATE
         waatToken = AES_ENCRYPT('$escapedToken', '$secretKey')"
    );
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
		if (!isset(self::$id)) {
			RoleHelper::requireAdmin(); // ← ADD
		}
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
        if (!isset($_POST['afVacationDays']) || $_POST['afVacationDays'] == '' ) {
            $_POST['afVacationDaysLastChanged'] = '';
        } else {
            $_POST['afVacationDaysLastChanged'] = date('Y-m-d H:i:s');
        }

        db::Save(ApplyFormModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? msg::set(TXT_SAVE_OK, "success") : msg::set(TXT_SAVE_ERROR, "error");
        // ********   HERE UPDATE pastDaysPastYears begin **********/
        $year = date('Y');
        $AFID=self::$data['afID'];
        
        if ( !isset($_POST['afVacationDays']) || $_POST['afVacationDays'] == '') 
             $days = cf::getEmployeePaidDaysOff($year,$AFID);
        else $days = $_POST['afVacationDays'];
        
        $dateNow=date('Y-m-d H:i:s');
        $q="INSERT INTO paiddayspastyears 
              (pdpyYear, pdpyAFID,pdpyDays,pdpyDateChanged)
            VALUES
              ({$year},{$AFID},{$days},'{$dateNow}')
            ON DUPLICATE KEY UPDATE
              pdpyDays = VALUES(pdpyDays)";
         $r=db::SQL($q);
        
   
            
            
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
        
        // ===== SYNC HR MANAGER / INTERVIEWER ROLE MAPPING =====
$isHR   = filter_input(INPUT_POST, 'afIsHRManager',   FILTER_SANITIZE_STRING) === 'yes' ? 'yes' : 'no';
$isIV   = filter_input(INPUT_POST, 'afIsInterviewer',  FILTER_SANITIZE_STRING) === 'yes' ? 'yes' : 'no';
$empEmail = filter_input(INPUT_POST, 'afEmail', FILTER_SANITIZE_EMAIL);
$plainPass = filter_input(INPUT_POST, 'afPass',  FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
// NOTE: only pass $plainPass if your form has a "new password" input called afPass.
//       Leave it empty string if the password field is on a separate page (SetPassword).
self::syncRoleMapping((int)$id, $isHR, $isIV, $empEmail, (string)$plainPass);
// ===== END SYNC =====

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
	   $realFilePath="../add/customer".$_SESSION['customerID']."/uploads/files/".$rec['fcfbuFileSavedAs'];
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
            $filePath = "../add/customer".$_SESSION['customerID']."/uploads/files/".$rec['fcfbuFileSavedAs'];
			
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
			$newFile='../add/customer'.$_SESSION['customerID'].'/uploads/files/'.$newFileName;
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

    public static function SaveAdminDaysOff(){
		//cf::pr($_POST);exit;
		
		$startDate = val::convDate(USER_DATE,MYSQL_DATE,$_POST['startDate']);
		$endDate  = val::convDate(USER_DATE,MYSQL_DATE,$_POST['endDate']);
		//echo $startDate.' '.$endDate.'<br>';
		$daysBetween = DaysOffFrontModel::getWorkingDaysBetween($_POST['eID'],$startDate,$endDate);
		$year=date('Y',strtotime($startDate));
		
		if(isset($_POST['half_day']) && $_POST['half_day']=='half_day') {
				$_POST['days'] = 0.5;
				$msg = "Half day {$_POST['vacType']} ({$_POST['paid_type']})  on  {$_POST['startDate']}";
			}
		else {
			if($_POST['days']==1) {
				$msg = "1 day {$_POST['vacType']} ({$_POST['paid_type']}) on {$_POST['startDate']}";
			}
			else {
				$msg = "{$_POST['days']} days {$_POST['vacType']} ({$_POST['paid_type']}) from {$_POST['startDate']} to {$_POST['endDate']}";
			}
		}
		
		foreach ($daysBetween as $k=>$d){
				$date=str_replace('-','/',$d).'/'.$_POST['year'];
				$days[$k]=val::convDate(USER_DATE,MYSQL_DATE,$date);
			}
		$days_list=implode(',',$days);
		$q="SELECT * FROM apply_forms WHERE afID={$_POST['eID']}";
		$user=db::PrepareLoadSingleRow($q);
		$now=date('Y-m-d');
		$q="INSERT INTO vacation_requests set vrAFID={$_POST['eID']},
			vrYear={$year},
			vrDays={$_POST['days']},
			vrDateStart='{$startDate}',
			vrDateEnd='{$endDate}',
			vrState='approved',
			vrDeclineReason='',
			vrDateEntered='{$now}',
			vrType='{$_POST['vacType']}',
			vrPaydUnpaid='{$_POST['paid_type']}',
			vrRealDays='{$days_list}'
		";
		$result = db::SQL($q);
		if($result){
		    msg::set('Day(s) off were given to ' . $user['afFirstName'] . ' ' . $user['afLastName'] . ': ' . $msg . '.','success');
			$subject = "You've been given days off";
			$mail=$user['afEmail'];
			$msg=strtolower($msg);
			
			$message ="
			Dear {$user['afFirstName']} {$user['afLastName']},<br>
			You've been given days off: {$msg}.<br><br>
			".MAIL_FOOTER_HTML;
			
			cf::sendMail($mail, $subject, $message, $GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS']); //<<<<<<<
			
		}
		else msg::set('There was a problem to save days off. Please notify the administrator.','error');
		
		cf::Redirect(ADMIN.'employee-details?cmd=days-off&id='.$_POST['eID']);
	}
	
	public static function checkDates($reqDates,$vacDays){
		if (!isset($vacDays) || empty($vacDays) || $vacDays == false) $vacDays=array();
		foreach ($vacDays as $r){
			$dates=explode(',',$r['vrRealDays']);
			foreach ($reqDates as $d){
				if (in_array($d,$dates)) return false;
			}
		}
		return true;
	}
	
	public static function CheckAdminDaysOff(){//<<<<<< PV
		
		$startDate = val::convDate(USER_DATE,MYSQL_DATE,$_GET['date_start']);
		$endDate = val::convDate(USER_DATE,MYSQL_DATE,$_GET['date_end']);
		$userID=$_GET['eID'];
		$vacType=$_GET['vacType'];
		$typePaid=$_GET['paid_unpaid'];
		$daysBetween = DaysOffFrontModel::getWorkingDaysBetween($userID,$startDate,$endDate);
		$daysBetveen = count($daysBetween);
		$half_day=$_GET['half_day'];
		
		$year=date('Y',strtotime($startDate));
		$year1=date('Y',strtotime($endDate));
		
		$res=array();
		if ($year != $year1) {
			$res['result']='FAIL';
			$res['message']='Please select start date and end date in same year.';
			echo json_encode($res);
			exit;
		}
		
		else { // check for limitations
		
		    if ($startDate > date('Y-m-d', strtotime('+1 years'))){
				$res['result']='FAIL';
				$res['message']='Please select start date within 1 year from today.';
				echo json_encode($res);
				exit;
			}
			
			if ($half_day == 1 && ($startDate != $endDate)){
				$res['result']='FAIL';
				$res['message']='Please select only one day if you need half day.';
				echo json_encode($res);
				exit;
			}
		
			$vacDays=//cf::getEmployeePaidDaysOff($year,$userID);
                DaysOffFrontModel::getVacDates($userID,$year);
                
			$vac_days=DaysOffFrontModel::getWorkingDaysBetweenFull($userID,$startDate,$endDate);
			
			if (! self::checkDates($vac_days,$vacDays)){
			$res['result']='FAIL';
			$res['message']='The selected days overlap with already approved days off.';
			echo json_encode($res);exit;
			}
		
			/*************
			if ($vacType != 'Jury duty' && $vacType!='Sick leave' && !isset($_GET['type'])){
				$dateAfter3months = date("Y-m-d", strtotime(AppModel::$usr['afDateHired']."+ 3 months"));
				if($startDate < $dateAfter3months){
				$res['result']='FAIL';
				$res['message']='There must be at least 3 months from date when you was hired ('.
				val::convDate(MYSQL_DATE,USER_DATE,AppModel::$usr['afDateHired']).') ';
				
				echo json_encode($res);exit;
				}
			}
			
			if ($vacType != 'Jury duty' && $vacType!='Sick leave' && !isset($_GET['type'])){
				$dateAfter2weeks = date("Y-m-d", strtotime("+ ".$position['TwoWeekNoticeNmb']." ".$position['TwoWeekNoticeDim']));
				
				if($startDate < $dateAfter2weeks){
				$res['result']='FAIL';
				$res['message']='There must be a '
				.$position['TwoWeekNoticeNmb']."-".str_replace('s','',$position['TwoWeekNoticeDim'])
					.' notice for vacation or CE.';
				echo json_encode($res);exit;
				}
			}
			****************/
			if(true){
				$blockOutDays=DaysOffFrontModel::getBlockOutDates($userID,$year);
				
				if ($blockOutDays ===false) $blockOutDays=array();
				foreach ($blockOutDays as $k=>$boDay){
					if ($boDay['bodIsInterval']=='single') $boDay['bodDateEnd']=$boDay['bodDateStart'];
					if (($boDay['bodDateStart']>=$startDate && $boDay['bodDateStart']<=$endDate)
					|| ( $boDay['bodDateEnd']>=$startDate && $boDay['bodDateEnd']<=$endDate)) {
						
						$res['result']='FAIL';
						if (in_array($vacType,array('Jury duty','Sick leave','Vacation','CE'))) // <<< PV April 2019 
						{
							$res['result']='OK';
				$res['days']=$daysBetveen;
				$res['type']=$typePaid;
				$res['vacType']=$vacType;
				if ($half_day == 1) $days='Half day';
				else $days = $daysBetveen.' day(s)';
				$res['days_msg']=$days.' '.$vacType.'  <b>'.$typePaid.'</b>';
						}
						else {
						if ($boDay['bodIsInterval']=='single') {
							$bodate=val::convDate(MYSQL_DATE,USER_DATE,$boDay['bodDateStart']);
							$res['message']=
							"You've selected a period which includes a blockout date 
							- {$bodate}. Please send two requests - one before and one 
							after the blockout date.";
						}
						else{	
							$bodateStart=val::convDate(MYSQL_DATE,USER_DATE,$boDay['bodDateStart']);
							$bodateEnd=val::convDate(MYSQL_DATE,USER_DATE,$boDay['bodDateEnd']);
							$res['message']=
							"You've selected a period which includes a blockout dates
							: {$bodateStart}-{$bodateEnd}. Please send two requests - one before and one 
							after the blockout date.";
						}
						}
						echo json_encode($res);exit;
					}
					
				}
			}
			
			
			
			if ($daysBetveen==0){
				$res['result']='FAIL';
				$res['message']='You\'ve selected only non-working days. Your request must include at least one working day.';
				echo json_encode($res);exit;
			}
			
				$res['result']='OK';
				$res['days']=$daysBetveen;
				$res['type']=$typePaid;
				$res['vacType']=$vacType;
				if ($half_day == 1) $days='Half day';
				else $days = $daysBetveen.' day(s)';
				$res['days_msg']=$days.' '.$vacType.'  <b>'.$typePaid.'</b>';
			
			/**********
			elseif ($vacType == 'CE' && $remainPaidCE>=$daysBetveen) {
				
				$res['result']='OK';
				$res['days']=$daysBetveen;
				$res['type']='paid';
				$res['days_msg']=$daysBetveen.' day(s) <b>paid</b>';
			}
			elseif ($vacType == 'CE' && $remainPaidCE<=0) {
				$res['result']='OK';
				$res['days']=$daysBetveen;
				$res['type']='unpaid';
				$res['days_msg']=$daysBetveen.' day(s) <b>unpaid</b>';
			}
			elseif ($vacType == 'Jury duty' || $remainPaid<=0) {
				$res['result']='OK';
				$res['days']=$daysBetveen;
				$res['type']='unpaid';
				$res['days_msg']=$daysBetveen.' day(s) <b>unpaid</b>';
			}
			elseif (($daysPerYear-$daysUsed)>=$daysBetveen){
					$res['result']='OK';
					$res['days']=$daysBetveen;
					$res['type']='paid';
					$res['days_msg']=$daysBetveen.' day(s) <b>paid</b>';
				}
				else {
					if ($vacType='Sick leave' && $remainPaid > $daysBetveen){//$daysPerYear==0 ){
						$res['result']='OK';
						$res['days']=$daysBetveen;
						$res['type']='paid';
						$res['days_msg']=$daysBetveen.' days <b>paid</b>';	
					}
					elseif ($vacType='Sick leave' && $remainPaid < 0 ){//$daysPerYear==0 ){
						$res['result']='OK';
						$res['days']=$daysBetveen;
						$res['type']='unpaid';
						$res['days_msg']=$daysBetveen.' days <b>unpaid</b>';	
					}
					else {
					
					$res['result']='FAIL';
					$res['message']=
					"You don't have enough paid days. Please submit 2 requests - one for all paid days left and the rest as unpaid.";
					echo json_encode($res);exit;
					}
				}

			************/
		
		    /*******  POSITION LIMIT **************
		    if ($vacType != 'Sick leave' &&  $vacType!='Jury duty'){
				
				$userPosId=AppModel::$usr['afHiredForPID'];
				$userOfficeId=AppModel::$usr['afCOID'];
				
				
				foreach ($daysBetween as $k=>$d){
					$date=str_replace('-','/',$d).'/'.$year;
					$days[$k]=val::convDate(USER_DATE,MYSQL_DATE,$date);
				}
				$days_list=implode(',',$days);
				
				// get limit per office
				$q="SELECT * from positions WHERE pID={$userPosId}";
				$_res=db::PrepareLoadSingleRow($q);
				if ($_res !== false){
					$off_limits=json_decode($_res['pMinEmplPerPosArr'],true);
					if (isset($off_limits[$userOfficeId])) $limit=$off_limits[$userOfficeId];
					else $limit=MIN_EMP_PER_POSITION;
				}
				else $limit=MIN_EMP_PER_POSITION;
				
				// get all in same office with same position
				$q22="SELECT * FROM  apply_forms WHERE  afActive='Hired' AND afHiredForPID={$userPosId} AND afCOID={$userOfficeId}";
				$_res=db::PrepareLoad($q22);
				
				$colegues=array();
				
				foreach ($_res as $r){
					//if ($r['afID']!=$userID ) 
						$colegues[]=$r['afID'];
				}
				$colegues_count=count($colegues);
				// get vacations of all another
				$col_list=implode(',',$colegues);
				
				$q="SELECT * FROM vacation_requests WHERE vrState IN ('approved') AND vrAFID IN ({$col_list})  AND vrYear={$year}";
				$dates = db::PrepareLoad($q);
				if ($dates==false) $dates=array();
				
				$bad_days=array();
				foreach ($days as $day){
					$employees=$colegues_count;
					foreach($dates as $date){
						if ($day>=$date['vrDateStart'] && $day<=$date['vrDateEnd']){
							$employees--;
						}
					}
					if($employees-1 < $limit) $bad_days[]=$day;
				}
	
				if (!empty($bad_days)){//
					$res['result']='FAIL';
					foreach ($bad_days as $j=>$d) $bad_days[$j]=val::convDate(MYSQL_DATE,USER_DATE,$d);
					$bad_days_str=implode(',',$bad_days);
					$res['message']='Cannot take vacation/CE on the selected dates - not enough employees left for your position and office.';
					echo json_encode($res);exit;
				}
				
			}
			*************/
			
		}//end chek for limitations 
		
		echo json_encode($res);exit;
		}
	
}