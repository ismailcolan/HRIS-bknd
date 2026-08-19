<?php

/** The CompanyOffice controller */
class OnboardingFrontController extends AppController
{

    const __DUMP__ = false;
    const __PAGESIZE__ = 20;

    public static $id, $data, $pg, $type='on';
	
	
	public static function SignOnBoardingTasks(){
		global $host;
		
		if(AccessModel::$usr === null ) {
			echo '<h4>You have no rights to do that operation</h4>';
			return;
		}
		
		
		$user_id = AccessModel::$usr['afID'];
		$q="SELECT * FROM employee_onboarding WHERE eoID={$_GET['id']} AND eoAFID={$user_id} ORDER BY eoID";
        // print_r ($q); break;
		$r1=db::PrepareLoadSingleRow($q);
		if($r1== false){
			echo '<h4>No permission to complete the request</h4>';
			return;
		}
		$taskID=$r1['eoOTID'];;
		$docs_to_sign=explode('|',$r1['eoDocsToSign']);
		
	
		$q="SELECT * FROM onboarding_tasks WHERE otID={$taskID}";
		
		$r2=db::PrepareLoadSingleRow($q);
		$task_name=$r2['otDescription'];
		//print_r($r1);
		
		$docs=explode('|', $r1['eoDocsToSign']);
		$docs_signed=explode('|', $r1['eoSignedDocs']);
		$docs_remain=explode('|', $r1['eoDocsRemainToSign']);
			
		if($docs_remain[0]=='') $docs_remain=array();
		$doc_nmb_remain=count($docs_remain);
		if ($doc_nmb_remain==0) $doc_remain_txt='All documents signed';
		else if ($doc_nmb_remain==1)	$doc_remain_txt=$doc_nmb_remain.' document to sign';		
		else 	$doc_remain_txt=$doc_nmb_remain.' documents to sign';
		
		echo '
		<div class="col-xs-12 col-right">
						
			<div class="content bg-white">
				<br>	
				<p style="font-size:1.2em;text-align:center">Document to be signed for task: <b>'.$task_name.'</b><br>
				'.$doc_remain_txt.'</p>
				<hr>
				'.msg::get().'
				<br>
				<span style="color:#000"><b>IMPORTANT:</b> Signing the document means you\'ve read it and you agree to it.</span>
				<br><br>
				<table >
				<thead ><tr><th style="text-align:center">Document</th>
				<th style="text-align:center">Date created</th><th></th><th></th></tr></thead>
				';
			
		//cf::pr($docs)	;cf::pr($docs_signed);
		foreach ($docs as $docID){ //cf::pr($doc)	;exit;
			$q="SELECT * FROM onboarding_files WHERE ofID={$docID}";
			$doc=db::PrepareLoadSingleRow($q);
			/***********
			//echo '>>>'.$doc['otURLName'];
			//$doc['otURLName'] = str_replace('add','add/customer'.$_SESSION['customerID'],$doc['otURLName']);
			//echo '<br>>>'.$doc['otURLName'];
			//$doc['otURLName'] = PROTOCOL. $host.'/'.strstr($doc['otURLName'], 'add');
			//echo '<br>>>'.$doc['otURLName'];
			
			//<a style="float:right" href="'.$doc['otURLName'].'">Download</a>
			**********/
			$doc['otURLName']=str_replace('hris.maidenlanemedical.com',$host,$doc['otURLName']);// due bad routinf
			echo '<tr><td>
			     <a style="color:#000" title="Click to view" href="'.$doc['otURLName'].'">'.$doc['ofTitle'].'</a>
				
				 <a style="float:right;margin-right:10px" target="blank" href="'.$doc['otURLName'].'">View</a>
			     </td>'.
				'<td style="text-align:center">'.val::convDate(MYSQL_DATE_TIME, USER_DATE_TIME_SHORT,$doc['ofDateTimeSaved']).'</td>';
			
			//print_r ($docs_signed);exit;
			
			if(in_array($docID,$docs_signed)){
				$q="SELECT * FROM docs_signed WHERE dsDocID={$docID} AND dsAFID={$user_id}";
				$signData=db::PrepareLoadSingleRow($q);
				echo '<td style="text-align:center"><span style="color:green">SIGNED</span></a>
				</td><td style="text-align:center">
				<a class="bstooltip" data-html="true" data-trigger="click" href="javascript:void"  data-toggle="tooltip" data-placement="top" 
				title="Date : '.val::convDate(MYSQL_DATE_TIME, USER_DATE_TIME_SHORT,$signData['dsDateTime']).'<br>From IP :'.$signData['dsIP']
				.'<br>(click link again to close)" >
				details >> </a>
				</td>';
			}
			else {
				if (isset($_SESSION['webapp-admin-token'])){
					echo '<td style="text-align:center"><span style="color:red;">not signed</span></td><td></td>';
				}
				else {
				echo '<td style="text-align:center">
				<span style="color:red;">not signed</span></td>
				<td style="text-align:center"><a href="javascript:void(0)" 
				onclick="if (confirm(\'Are you sssure you have read and you agree to the document?\')) 
					location.href=\'?cmd=sign-doc&emp='.$user_id.'&doc='.$docID.'&task='.$_GET['id'].'\'">Sign >></a>
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
		</script>	
		';
	}
	
	
	public static function signDoc(){
	    
	 if(!isset($_SESSION['webapp-login-token']) || isset($_SESSION['webapp-admin-token'])
		 || AppModel::$usr["afID"] != $_GET['emp'] ){
		echo '<h3>No permission to sign this document</h3>';
	 }
	 else {
		 if (isset($_GET['emp']))$emp=$_GET['emp'];
		 if (isset($_GET['doc']))$doc=$_GET['doc'];
		 if (isset($doc) && isset($emp)){
			 $res=array();
			 $now=new DateTime();
			 $today=$now->format(MYSQL_DATE_TIME);
			 $today_resp = $now->format(USER_DATE_TIME_FLAT);
			 $ip=cf::get_ip_address();
			 $q="INSERT INTO docs_signed SET dsAFID={$emp}, dsDocID={$doc},dsDateTime='{$today}',dsIP='{$ip}'";
			 //echo $q; exit;
			 $r=db::SQL($q);
			// if ($r) echo 'OK'; else echo mysql_error(); exit;
			 
			 
			 $q="SELECT * FROM employee_onboarding WHERE eoAFID={$emp} AND eoDocsToSign LIKE '%{$doc}%'";
			 $res=db::PrepareLoadSingleRow($q);
			 $eoID=$res['eoID'];
			 if(is_null($res['eoSignedDocs']) or $res['eoSignedDocs']=='') 
				$signedDocs=array();
			 else
				$signedDocs=explode('|',$res['eoSignedDocs']);
		 
			 $signedDocs[]=$doc;
			 $signedDocsStr=implode('|',$signedDocs);
			 
			 $docsToSign=explode('|',$res['eoDocsToSign']);
			 foreach ($signedDocs as $k=>$v){
				if (($key = array_search($v, $docsToSign)) !== false) {
					unset($docsToSign[$key]);
				}
			 }
			 $docsRemainToSignStr=implode('|',$docsToSign);
			 
			 $q="UPDATE employee_onboarding SET 	eoSignedDocs='{$signedDocsStr}', 
			 eoDocsRemainToSign='{$docsRemainToSignStr}'
			 WHERE eoID={$eoID} ";		 
			 //print ($q);break;
			 $r2=db::SQL($q);
			 
			 
			 
			 if (empty($docsToSign)){
				 $date=new DateTime();
				 $now=$date->format(MYSQL_DATE_TIME);
				 
				 $q="UPDATE employee_onboarding SET eoCompletedOn='{$now}',eoCompletedBy=NULL
					WHERE 	eoID={$eoID}";
				 $r3=db::SQL($q);
			 }

			 msg::set('Document was signed','success');
			 
			 if ($r && $r2) msg::set('Document was signed','success');
			 else msg::set('There was a problem to sign this doc','error');
		 }
		 else msg::set('There was a problem to sign this doc','error');
	 }
	 
	 cf::redirect('?cmd=sign&id='.$_GET['task']);
 }
 


    public static function OnBoardingTasks()
    {
		 $first = true;
        if (!isset(AppModel::$usr["afID"])) {
            self::MustSigned();
            return;
        }

        $id = AppModel::$usr["afID"];

        if(filter_has_var(INPUT_GET, "cmd") && 
		     (filter_input(INPUT_GET, "cmd") === "undo" || filter_input(INPUT_GET, "cmd") === "mark"))
		 {
            EmployeeController::CheckForUndoOnboarding(false);
            cf::Redirect(WP . "onboarding-tasks");
        }

        EmployeeController::CheckForOnboardingTasksComplete($id);

        msg::get();
		 OnboardingModel::$type=self::$type;
        $respList = OnboardingFrontModel::data("otActive='Yes' and otAFID <> ".EMPLOYEE_THEMSELF_ID." and (otAFID=".$id.' OR otAFID='.EMPLOYEE_THEMSELF_ID.')');
        if($respList) {
            $respIDs = implode(", ", array_column($respList, "otID"));
        } else {
            $respIDs = '-1';
        }

        $activePage = "about-me";
        $cond = [];
        if(filter_has_var(INPUT_GET, "task0")) {
            $taskId = filter_input(INPUT_GET, "task0", FILTER_SANITIZE_NUMBER_INT);
            if($taskId > 0) {
                $cond[] = " eoOTID={$taskId}";
				   $first = false;
            }
        }
		
		 $having='HAVING otEmployeer IS NOT NULL';
        if(filter_has_var(INPUT_GET, "name0")) {
			  $activePage = "pending";
            $name = db::EscapeStr(filter_input(INPUT_GET, "name0", FILTER_SANITIZE_STRING));
            if(!empty($name)) {
                //$cond[] = " (SELECT CONCAT(afFirstName, ' ', afLastName) FROM apply_forms WHERE afID=eoAFID) like '%{$name}%' ";
				   $having = "  AND otEmployeer like '%{$name}%' ";
				   $first = false;
            }
        }
        if(!empty($cond)) {
            $condsStr = ' and '.implode(' and ', $cond);
        } else {
            $condsStr = '';
        }
		
        $pending = EmployeeOnboardingModel::LoadResponsibleOnboardingTasks
			($id, "eoCompletedOn is null  {$condsStr}",'','pending',
			$having,self::$type	);//and eoOTID in ({$respIDs})
			
			

        $cond = [];
        if(filter_has_var(INPUT_GET, "task1")) {
            $activePage = "completed";
            $taskId = filter_input(INPUT_GET, "task1", FILTER_SANITIZE_NUMBER_INT);
            if($taskId > 0) {
                $cond[] = " eoOTID={$taskId}";
				   $first = false;
            }
        }
		 $having='HAVING otEmployeer IS NOT NULL AND otResponsibleEmployeer IS NOT NULL ';
        if(filter_has_var(INPUT_GET, "name1")) {
            $activePage = "completed";
            $name = db::EscapeStr(filter_input(INPUT_GET, "name1", FILTER_SANITIZE_STRING));
            if(!empty($name)) {
                //$cond[] = " (SELECT CONCAT(afFirstName, ' ', afLastName) FROM apply_forms WHERE afID=eoAFID) like '%{$name}%' ";
				   $having = " AND otEmployeer  like '%{$name}%' ";
				   $first = false;
            }
        }
        if(!empty($cond)) {
            $condsStr = ' and '.implode(' and ', $cond);
        } else {
            $condsStr = '';
        }
		
        $completed = EmployeeOnboardingModel::LoadResponsibleOnboardingTasks
		($id,   "eoCompletedOn is NOT NULL  {$condsStr}",
		 " eoCompletedOn ASC, eoTimeFrame ASC ",'completed',$having,self::$type);
		 //<<<<< removed and eoOTID in ({$respIDs}) BEFORE {$condsStr}
		 //print_r($completed);
		
		
        $cond = [];
        if(filter_has_var(INPUT_GET, "task9")) {
			  $activePage = "about-me";
            $taskId = filter_input(INPUT_GET, "task9", FILTER_SANITIZE_NUMBER_INT);
            if($taskId > 0) {
                $cond[] = " eoOTID={$taskId}";
				   $first = false;
            }
        }
		  $having='';
        if(filter_has_var(INPUT_GET, "name9")) {
			  $activePage = "about-me";
            $name = db::EscapeStr(filter_input(INPUT_GET, "name9", FILTER_SANITIZE_STRING));
            if(!empty($name)) {
                $having = "  HAVING otEmployeer like '%{$name}%' ";
				" //(SELECT CONCAT(afFirstName, ' ', afLastName) FROM apply_forms WHERE afID=eoAFID) like '%{$name}%' ";
				  $first = false;
            }
        }
        if(!empty($cond)) {
            $condsStr = ' and '.implode(' and ', $cond);
        } else {
            $condsStr = '';
        }
       
		  $about_me = EmployeeOnboardingModel::LoadResponsibleOnboardingTasks
           ($id, "eoCompletedOn is null  {$condsStr}",'','about_me',$having,self::$type);
		   
		  if ( $first ){ 
			  $_SESSION['about_me'][ AppModel::$usr["afID"] ]=$about_me;
			  $_SESSION['pending'][ AppModel::$usr["afID"] ]=$pending;
			  $_SESSION['completed'][ AppModel::$usr["afID"] ]=$completed;
			  $_SESSION['responsible'][ AppModel::$usr["afID"] ]=$respList;
			  if (!empty($pending)) $activePage = "pending"; else  $activePage = "about-me";
		  }
//echo '<pre>';print_r($completed);echo '</pre>';
        OnboardingView::RenderFrontOnboardingTasks([
		     "about-me" => $about_me,
            "pending" => $pending,
            "completed" => $completed,
            "responsible" => $respList
        ], $activePage);

    }


    public static function MustSigned()
    {
        EmployeeView::RenderMustSigned();
    }

}