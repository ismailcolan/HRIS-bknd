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
	
	
	public static function isWeekdayNow($date){
        return (date('N', strtotime($date)) >= 6);
    }
	
	public static function  isEmplOnWorkToday($r,$what='normal'){
		$day = date('Y-m-d');
		$year = date('Y');
		
		
		
		$fedHolidays = cf::getFederalHolydays($year);
		
		$daysOff=DaysOffFrontModel::getDaysOffDates($r['afID'],$year);
		if (!is_array($daysOff)) $daysOff=array();
		
		$nonWorkingDays=array();
        foreach ($daysOff as $k=>$dO){
		   $days= cf::getWorkdaysBetween($dO['nwdDateStart'], $dO['nwdDateEnd']);
		   
		   foreach($days as $j=>$d){
				$nonWorkingDays[] = $year.'-'.$d;
		   }
	    }
		//cf::pr($nonWorkingDays);
		if (in_array($day,$nonWorkingDays)) {
			$circl = '<span title="Not at work: office closed today" class="gray-circle"></span>';
			$text="Not at work : office closed today";
		}
		elseif (in_array($day,$fedHolidays)) {
			$circl = '<span title="Not at work: US holiday" class="gray-circle"></span>';
			$text="Not at work: US holiday";
		}
		else if (self::isWeekdayNow($day)) {
			$circl = '<span style="cursor:help" title="Not at work: weekend" class="gray-circle"></span>';
			$text="Not at work: weekend";
		}
		else {
			$q="SELECT * FROM vacation_requests WHERE vrAFID={$r['afID']} AND vrRealDays LIKE '%{$day}%'";
			$res = db::PrepareLoadSingleRow($q);
			if ($res == false) {
				$reason='';
			}
			else {
				$reason=': '.$res['vrType'].', '.$res['vrPaydUnpaid'];
			}
			
			/* START COMMENTED BY George Zlatev 23.06.2026
			if ($r['afSalaryType'] =='Hourly' && !empty($r['afEmplNumber']) && $r['afEmplNumber'] != ''){
				if ($r['afIn'] == 0 ) {
					$circl = '<span style="cursor:help" title="Not at work '.$reason.'" class="gray-circle"></span>';
					$text= 'Not at work '.$reason;
				}
				else {
					$circl = '<span style="cursor:help" title="In office now" class="green-circle"></span>';
					$text = 'In office now';
				}
			}
			
			else {
				$away = MyEmployeesController::isEmployeeAwayToday($r["afID"]);
				if (!empty($away)) {
					$circl = '<span style="cursor:help" title="Not at work'.$reason.'"  class="gray-circle"></span>';
					$text = 'Not at work'.$reason;
				}
				else {
					$circl = '<span style="cursor:help" title="At work today"  class="green-circle"></span>';
					$text = 'At work today';
				}
			}
			// END COMMENTED BY George Zlatev 23.06.2026
			*/
			
			// START George Zlatev 23.06.2026
			// ------------------------------------------------------------------------------------
				$away = MyEmployeesController::isEmployeeAwayToday($r["afID"]);
				if (!empty($away)) {
					$circl = '<span style="cursor:help" title="Not at work'.$reason.'"  class="gray-circle"></span>';
					$text = 'Not at work'.$reason;
				}
				else {
					$circl = '<span style="cursor:help" title="At work today"  class="green-circle"></span>';
					$text = 'At work today';
				}
			// ------------------------------------------------------------------------------------
			// END George Zlatev 23.06.2026
		}
		
		if ($what=='normal'){
			
			$q="SELECT * FROM timeclock_change_requests WHERE 	tcrAFID={$r['afID']} AND tcrState='pending'";
			$res = db::PrepareLoad($q);
			if($res===false) $res=array();
			$link = "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
			
			if(count($res)>0 && strpos($link,'admin') !== false ) 
				$circl='<span class="red timeclock_icon" style="cursor:help" title="There are unverified timeclock requests" >
			<i class="fa fa-clock-o" style="font-size:1.2em;color:red" aria-hidden="true"></i>
			 </span>'
				.$circl;
				
			return $circl;	
		}
		else return $text;
	}
	/***********
	public static function  isEmplOnWorkToday($eID){
		$q="SELECT * FROM apply_forms WHERE afID=".$eID;
		$r=db::PrepareLoadSingleRow($q);
		if ($r['afSalaryType'] =='Annual') {
			$away = MyEmployeesController::isEmployeeAwayToday($r["afID"]);
			//cf::pr($away);
			if (!empty($away)) $circl = '<span title="Not on work today"  class="gray-circle"></span>';
			else $circl = '<span title="On work today"  class="green-circle"></span>';
		}
		else {
			if ($r['afIn'] == 0 ) $circl = '<span title="Not on work today" class="gray-circle"></span>';
			else
			  $circl = '<span title="On work today" class="green-circle"></span>';
		}
		return $circl;		
	}
	**********/
	
	public static function  MyEmployeesList(){
		include __DIR__ ."/../webapp-html/header-only.html";
		echo '<body style="background:#FFF;min-height:500px">';
		
		echo '<div style="width:100%;text-align:center">';
		msg::get();
		echo '
		<div style="padding:15px;color:#337ab7;font-size:22px;font-weight:bold">My Employees</div>';
		
		
		$manID=AppModel::$usr['afID'];
		$res=EmployeeModel::GeAllResponsibleForEmployees($manID);
		if(!is_array($res)) $res=array();
		usort($res, function($a, $b) {
			return strcmp($a['afFirstName'].' '.$a['afLastName'], $b['afFirstName'].' '.$b['afLastName']);
		});
		//<thead><tr><td >Name</td><td>Reports</td></thead></tr>
		if (empty($res)) echo 'No assigned employees';
		else {
		echo '<table  class="" style="border:2px;width:100%;margin:0 auto;">
		<tbody>';
		$day = date('w')-1;
		$week_start = date('Y-m-d', strtotime('-'.$day.' days'));
		$week_end = date('Y-m-d', strtotime('+'.(6-$day).' days'));
		echo '<tr>';$i=0;
		foreach ($res as $k=>$r){
			$circl = self::isEmplOnWorkToday($r);
			
			echo '<width="30%"><td class="left-align">'.$circl.' <a class="blue_link" target="__blank" href="employees?ID='.$r['afID'].'">'.$r['afFirstName'].' '.$r['afLastName'].'</a></td>
		
			
			<td width="20%"><a 
			target="" href="days-off?cmd=another-employee&id='.$r['afID'].'">days off  >></a>';
			if ($r['afSalaryType']=='Hourly' && $r['afEmplNumber']!='' && $r['afNoPayrollJA']=='no' )
				echo ' | <a onclick="top.jQuery(\'#unsigned_rep_link\').click()" target="unsigned_rep_iframe" 
				href="managertcureports?querytype=listattendence&empnumber='.$r['afEmplNumber'].'
				&startdate='.$week_start.'">time reports >></a>';
			
			echo '</td>';
			
			$i++;  
			if ($i % 2 == 0 && $i != count($res)) echo '</tr><tr>';

		}
		if ($i % 2 == 1)echo '<td width="30%">&nbsp;</td><td width="20%">&nbsp;</td>';
		echo '</tr></tbody></table>';
		}
		
		echo '</div><br></br></body>';
	}
	
	public static function  CheckNotSignedReportsFor($manID){
		$res = EmployeeModel::GeAllResponsibleForEmployees($manID);
		$emp=array();
		if(!is_array($res)) $res=array();
		foreach ($res as $r){
			$emp[]=$r['afID'];
		}
		$emp_list=implode(',',$emp);
		if ($emp_list !='')  $emp_list="EMPLOYEE_ID IN ({$emp_list})  AND ";
		$now=date('Y-m-d');
		$q="SELECT * FROM  p_listofweeks WHERE {$emp_list} ENDDATE<'{$now}' AND SIGNED_STATUS = 'No'";
		
		$emps=db::PrepareLoad($q);
		if ($emps===false) return false; else return true;
	}
	
	public static function DaysOffRequest(){	
		$q="SELECT afID FROM apply_forms WHERE afActive='Hired' AND afRMID=".AppModel::$usr['afID'];
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
		}
		
		MyEmployeesView::ShowDaysOffRequest($req);
	}
	
	public static function UpcommingDaysOff(){
		include __DIR__ ."/../webapp-html/header-only.html";
		echo '<body style="background:#FFF;min-height:auto">';
		
		echo '<div style="width:100%">';
		msg::get();
		
		echo '
		<div style="padding:15px;color:#337ab7;font-size:22px;font-weight:bold;text-align:center;">Upcoming Days Off</div>
		<table class="white_table" style="border:1px solid #CCC;width:100%;margin:0 auto;">
		<thead>
		<tr><td>From</td><td>To</td><td>Working Days</td><td>EmployeeName</td><td>Type</td><td>Paid / Unpaid</td></tr>
		</thead>
		<tbody>';
		
		$manID = AppModel::$usr['afID'];
		$res   = EmployeeModel::GeAllResponsibleForEmployees($manID);
		
		$empArray = array();
		if(!is_array($res)) $res=array();
		foreach ($res as $val) {
			$empArray[]=$val['afID'];
		};
		//cf::pr($empArray);
		
		$now = date('Y-m-d').' 00:00:00';
		
		if (!empty($empArray)) $IN_STAT=" afID IN (".implode(',',$empArray).") AND ";
			else $IN_STAT=' FALSE AND';
		
		$q="SELECT *,concat(afFirstName,' ',afLastName) as afFullName FROM apply_forms
		LEFT JOIN vacation_requests ON vrAFID=afID
		WHERE {$IN_STAT} vrDateStart>='{$now}'  AND vrState='approved'
		ORDER BY vrDateStart ASC";
		
		
		$data= db::PrepareLoad($q);
		
		if($data == false) $data=array();
		
		//cf::pr($data);
		
		
		if (empty($data)) echo '<tr><td colspan="7" style="text-align:center">No Upcoming Days Off found</td></tr>';
		else {
			foreach ($data as $k=>$d){	
			$start=val::convDate(MYSQL_DATE,USER_DATE,$d['vrDateStart']);
			$end=val::convDate(MYSQL_DATE,USER_DATE,$d['vrDateEnd']);
			echo "<tr>
				<td>{$start}</td><td>{$end}</td><td>{$d['vrDays']}</td>
				<td style=\"text-align:center\">{$d['afFullName']}</td>
				<td>{$d['vrType']}</td><td>{$d['vrPaydUnpaid']}</td>
				</tr>";
			}
		}
		
		echo '</tbody></table></div>';
		
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
		
		$isAllOK = true;
		/****************  START CHECK AGAIN ***********/
		$q ="SELECT * FROM vacation_requests  WHERE vrID =".$_GET['id'];
		$req=db::PrepareLoadSingleRow($q);
		$empID=$req['vrAFID'];
		$from = val::convDate(MYSQL_DATE,USER_DATE,$req['vrDateStart']);
		$to   = val::convDate(MYSQL_DATE,USER_DATE,$req['vrDateEnd']);
		$startDate = $req['vrDateStart'];
		$endDate = $req['vrDateEnd'];
		$year = $req['vrYear'];
		$vacType = $req['vrType'];
		$userID=$req['vrAFID'];
		
		$totalVac = DaysOffFrontModel::getDefaultDays($empID,$year,'Vacation');
		$totalCE = DaysOffFrontModel::getDefaultDays($empID,$year,'CE');
		$usedPaid = DaysOffFrontModel::getUsedDays($empID,$year,['Vacation','Sick leave'],'paid');
		$usedCE = DaysOffFrontModel::getUsedDays($empID,$year,['CE'],'paid');
		$remainPaid = $totalVac-$usedPaid;
		$remainPaidCE=$totalCE-$usedCE;
		
		
		$daysUsed = DaysOffFrontModel::getUsedDays($empID,$year,[$vacType]);
		$daysBetween = DaysOffFrontModel::getWorkingDaysBetween($empID,$startDate,$endDate);
		$daysBetveen = count($daysBetween);
		
		
		$daysPerYear=DaysOffFrontModel::getDefaultDays($empID,$year,$vacType);
		$d=new DateTime();
		$today=$d->format('Y-m-d');
		$res=array();
		
		$vacDays=DaysOffFrontModel::getVacDates($empID,$year);
		$vac_days=DaysOffFrontModel::getWorkingDaysBetweenFull($empID,$startDate,$endDate);
		
		$q="SELECT * FROM apply_forms WHERE afID=".$empID;
		$emp=db::PrepareLoadSingleRow($q);
		
		$q="SELECT * FROM positions WHERE pID = ".$emp['afHiredForPID'];
		$position=db::PrepareLoadSingleRow($q);
		
		
		
		/*************   REAL CHECK BEIN HERE ***************/
		
		
		
		
		
		if($startDate < date('Y-m-d')){
			$isAllOK = false;
			msg::set('Date already in the past.',"error");
		}
		
		elseif (! DaysOffFrontController::checkDates($vac_days,$vacDays)){
			$isAllOK = false;
			msg::set('The selected days overlap with already approved days off.','error');
			
		}
		else {
			if ($vacType != 'Jury duty' && $vacType!='Sick leave' && !isset($_GET['type'])){
				$dateAfter3months = date("Y-m-d", strtotime($emp['afDateHired']."+ 3 months"));
				if($startDate < $dateAfter3months){
					$isAllOK = false;
					msg::set('There must be at least 3 months from date when employee was hired ('.
					val::convDate(MYSQL_DATE,USER_DATE,$emp['afDateHired']).')',"error");
				}
			}
			
			/**********
			if ($vacType != 'Jury duty' && $vacType!='Sick leave' && !isset($_GET['type'])){
				$dateAfter2weeks = date("Y-m-d", strtotime("+ ".$position['TwoWeekNoticeNmb']." ".$position['TwoWeekNoticeDim']));
				
				if($startDate < $dateAfter2weeks){
					$isAllOK = false;
					msg::set('There must be a '
					.$position['TwoWeekNoticeNmb']."-".str_replace('s','',$position['TwoWeekNoticeDim']).' notice for vacation or CE.');
				}
			}
			**********/
			
			if($vacType != 'Sick leave' && $vacType != 'Jury duty'){ //echo $userID.' '.$year;
				$blockOutDays=DaysOffFrontModel::getBlockOutDates($userID,$year,true);
				//cf::pr($blockOutDays);echo $startDate.' '.$endDate;exit;
				if ($blockOutDays===false) $blockOutDays=array();
				foreach ($blockOutDays as $k=>$boDay){
					if ($boDay['bodIsInterval']=='single') $boDay['bodDateEnd']=$boDay['bodDateStart'];
					
					//echo $boDay['bodDateStart'].'++++'.$boDay['bodDateEnd'].' '.$startDate.'::'.$endDate.'<br>';
					
					if (($boDay['bodDateStart']>=$startDate && $boDay['bodDateStart']<=$endDate)
					|| ( $boDay['bodDateEnd']>=$startDate && $boDay['bodDateEnd']<=$endDate)) {
					//echo 'OPAA';
						$isAllOK = false;
						if ($boDay['bodIsInterval']=='single') {
							$bodate=val::convDate(MYSQL_DATE,USER_DATE,$boDay['bodDateStart']);
							msg::set(
							"You've selected a period which includes a blockout date 
							- {$bodate}. Please send two requests - one before and one 
							after the blockout date.","error");
						}
						else{	
							$bodateStart=val::convDate(MYSQL_DATE,USER_DATE,$boDay['bodDateStart']);
							$bodateEnd=val::convDate(MYSQL_DATE,USER_DATE,$boDay['bodDateEnd']);
							msg::set(
							"You've selected a period which includes a blockout dates
							: {$bodateStart}-{$bodateEnd}. Please send two requests - one before and one 
							after the blockout date.","error");;
						}
					}
					
				}
				//echo 8888888;exit;
			}
		}
			
			
			
			if ($daysBetveen==0){
				$isAllOK = false;
				msg::set('Employee have selected only non-working days. Request must include at least one working day.',"error");
			}
			
			elseif ($vacType == 'CE' && $remainPaidCE>=$daysBetveen) {

			}
			elseif ($vacType == 'CE' && $remainPaidCE<=0) {
				
			}
			elseif ($vacType == 'Jury duty' || $remainPaid<=0) {
				
			}
			elseif (($daysPerYear-$daysUsed)>=$daysBetveen){
					
				}
				else {
					if ($vacType='Sick leave' && $remainPaid > $daysBetveen){//$daysPerYear==0 ){
						
					}
					elseif ($vacType='Sick leave' && $remainPaid < 0 ){//$daysPerYear==0 ){
						
					}
					else {
					
					$isAllOK = false;
					msg::set(
					"Employee don't have enough paid days. He/she must submit 2 requests - one for all paid days left and the rest as unpaid.","error");
					
					}
				}

		
		
		
		
		
		/***************CHECK FOR MIN LIMIT ***********************/
		if ($isAllOK && $vacType != 'Sick leave' &&  $vacType != 'Jury duty'){
				
				$userPosId=$emp['afHiredForPID'];
				$userOfficeId=$emp['afCOID'];
				
				
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
					msg::set('Cannot take vacation/CE on the selected dates - not enough employees left for your position and office.'.$startDate,"error");
					$isAllOK=false;
				}
				
			}
		/**********  check another vac request ************************/
		
			/************************************************/
			
		if ($isAllOK) {
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
				msg::set('Request was approved.','popup');
				$mail_body="
				Dear {$emp['afFirstName']} {$emp['afLastName']},<br><br>
				Your request for unpaid {$req['vrType']} from {$from} to {$to} has been approved.<br>
				".MAIL_FOOTER_HTML;
				
				
				cf::sendMail($emp['afEmail'], 
							'Days off request approved', 
							$mail_body, 
							EMAIL_MAILER_NAME, 
							EMAIL_MAILER_ADDRESS 
				);
			}
			else msg::set('There was a problem to save approved request.','error');
		}
		else {
		}
		
		cf::Redirect('?cmd=days-off-request');
	}
	
	public static function DeclineRequest(){
		global $con;
		$reason=mysqli_real_escape_string($con, $_GET['reason']);//strip_tags()
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
			Dear {$emp['afFirstName']} {$emp['afLastName']},<br><br>
			Your request for unpaid {$req['vrType']} from {$from} to {$to} has been declined.<br>
			Decline reason:{$_GET['reason']}<br><br>
			".MAIL_FOOTER_HTML;
			
			//echo $mail_body;exit;
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