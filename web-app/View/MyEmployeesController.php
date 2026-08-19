<?php

/** The CompanyOffice controller */
class MyEmployeesController extends AppController
{

    const __DUMP__ = false;

    public static $id, $data, $pg;
	
	public static function MyEmployessInit(){
		MyEmployeesView::Init();
	}
	
	public static function Test(){
		
		include __DIR__ ."/../webapp-html/header-only.html";
		echo '<body style="background:#FFF">
		coming soon
		<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
		<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
		<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
		<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
		<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
		END
		</body></html>';
		
	}
	
	public static function DaysOffRequest(){	
		$q="SELECT afID FROM apply_forms WHERE afActive='Hired' AND afRMID=".AppModel::$usr['afID'];
		$emps=db::PrepareLoad($q);
		if ($emps==false) $emps=array();
		if (! empty($emps)){
			$emp=array();
			foreach ($emps as $k=>$v){
				$emp[]=$v['afID'];
			}
			$emps_str = implode(',',$emp);
			
			$q="SELECT * FROM  vacation_requests  
			LEFT JOIN apply_forms ON afID=vrAFID
			WHERE vrAFID IN ({$emps_str}) AND vrState='pending'";
			$req=db::PrepareLoad($q);
		}
		
		MyEmployeesView::ShowDaysOffRequest($req);
	}
	
	public static function getPendingRequestsEmployeesOf($manID){
		$q="SELECT afID FROM apply_forms WHERE afActive='Hired' AND afRMID=".$manID;
		$emps=db::PrepareLoad($q);
		if ($emps==false) $emps=array();
		
		$req=array();
		if (! empty($emps)){
			$emp=array();
			foreach ($emps as $k=>$v){
				$emp[]=$v['afID'];
			}
			$emps_str = implode(',',$emp);
			
			$q="SELECT * FROM  vacation_requests  
			LEFT JOIN apply_forms ON afID=vrAFID
			WHERE vrAFID IN ({$emps_str}) AND vrState='pending'";
			$req=db::PrepareLoad($q);
			if($req==false) $req=array();
		}
		return $req;
	}
	
	public static function isEmployeeAwayToday($empID){
		$day=new DateTime();
		$today=$day->format('Y-m-d');
		$q="SELECT * FROM  vacation_requests  
			WHERE vrAFID={$empID} AND ('{$today}'>=vrDateStart AND '{$today}'<=vrDateEnd )  AND vrState='approved'";
			
		$req=db::PrepareLoadSingleRow($q);
		if ($req==false) $req=array();
		return $req;
	}
	
	public static function ApproveRequest(){
		
		$q="UPDATE  vacation_requests  
			SET 	vrState='approved'
			WHERE vrID =".$_GET['id'];
			$res=db::SQL($q);
		if ($res) {
			$q ="SELECT * FROM vacation_requests  WHERE vrID =".$_GET['id'];
			$req=db::PrepareLoadSingleRow($q);
			$empID=$req['vrAFID'];
			$from = val::convDate(MYSQL_DATE,USER_DATE,$req['vrDateStart']);
			$to   = val::convDate(MYSQL_DATE,USER_DATE,$req['vrDateEnd']);
			
			$q="SELECT * FROM apply_forms WHERE afID=".$empID;
			$emp=db::PrepareLoadSingleRow($q);
			
			
			msg::set('Request was approved.','success');
			$mail_body="
			Dear {$emp['afFirstName']} {$emp['afLastName']},<br>
			Your request for unpaid days off from {$from} to {$to} has been approved.<br>
			".MAIL_FOOTER_HTML;
			
			
			cf::sendMail($emp['afEmail'], 
						'Days off request approved', 
						$mail_body, 
						EMAIL_MAILER_NAME, 
						EMAIL_MAILER_ADDRESS 
			);
		}
		else msg::set('There was a problem to approve request.','error');
		
		cf::Redirect('?cmd=days-off-request');
	}
	
	public static function DeclineRequest(){
		$reason=mysql_real_escape_string(strip_tags($_GET['reason']));
		$q="UPDATE  vacation_requests  
			SET 	vrState='declined',
			vrDeclineReason='{$reason}'
			WHERE vrID =".$_GET['id'];
			$res=db::SQL($q);
		
		if ($res) {
			
			$q ="SELECT * FROM vacation_requests  WHERE vrID =".$_GET['id'];
			$req=db::PrepareLoadSingleRow($q);
			$empID=$req['vrAFID'];
			$from = val::convDate(MYSQL_DATE,USER_DATE,$req['vrDateStart']);
			$to   = val::convDate(MYSQL_DATE,USER_DATE,$req['vrDateEnd']);
			
			$q="SELECT * FROM apply_forms WHERE afID=".$empID;
			$emp=db::PrepareLoadSingleRow($q);
			
			msg::set('Request was declined.','success');
			$mail_body="
			Dear {$emp['afFirstName']} {$emp['afLastName']},<br>
			Your request for unpaid days off from {$from} to {$to} has been declined.<br>
			Decline reason:{$reason}<br><br>
			".MAIL_FOOTER_HTML;
			
			cf::sendMail($emp['afEmail'], 
						'Unpaid days off request declined', 
						$mail_body, 
						EMAIL_MAILER_NAME, 
						EMAIL_MAILER_ADDRESS 
			);
		}
		else msg::set('There was a problem to decline request.'.$_GET['reason'],'error');
		cf::Redirect('?cmd=days-off-request');
	}
	
	public static function RenderEmployeeAway($emp,$manID){
		if ($emp['afRMID']==$manID) $yearStart=date('Y',strtotime($emp['afStartDate']));
		else $yearStart=date('Y');
		$yearEnd=date('Y')+1;
		echo '<div style="text-align:center;font-weight:bold">Year: 
		<select style="border:0" id="current_year" title="Click to select year" onchange="changeCurrentYear()">';
		for($year=$yearStart;$year<=$yearEnd;$year++){
			if ($year==date('Y')) $sel='selected'; else $sel='';
			echo '<option '.$sel.' value="'.$year.'">'.$year.'</option>';
		}
		echo '<select></div>
		<script>
		function changeCurrentYear(){
			var year=jQuery("#current_year").val();
			jQuery(".yearVac").hide();
			jQuery("#year"+year).fadeIn();
		}
		</script>';
		for ($year=$yearStart;$year<=$yearEnd;$year++){
			if ($year==date('Y')) $display='block'; else $display='none';
			echo '<div class="yearVac" id="year'.$year.'" style="text-align:center;display:'.$display.'">
			<span style="display:inline-block;text-align:left;margin:0 auto;">';
			$q="SELECT * FROM vacation_requests WHERE vrYear='{$year}' AND vrAFID={$emp['afID']} 
			AND vrState='approved' ORDER BY vrDateStart ASC";
			$res=db::prepareLoad($q);
			if ($res==false) echo 'No days off this year.';
			else
			foreach ($res as $k=>$r){
				echo val::convDate(MYSQL_DATE,USER_DATE,$r['vrDateStart']).' - '.
				val::convDate(MYSQL_DATE,USER_DATE,$r['vrDateEnd']).' '.$r['vrType'].'<br>';
			}
			echo '</span></div>';
		}
	}	
		
}