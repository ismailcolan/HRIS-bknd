<?php
include("web-app/config/app-config.php");

ini_set('memory_limit', '-1');
//if (php_sapi_name() !='cli') exit;
//code to get current week start date and end date//
date_default_timezone_set('America/New_York');//initiate timezone//
ini_set('error_reporting', E_ALL);// & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
ini_set("error_log", "crons.error.log");

$con1 = mysqli_connect(SERVER_NAME, USER, PASS, DB_PREFIX.'main');
$q="SELECT * FROM customers WHERE cActive='yes'";
$__result = mysqli_query($con1,$q);
mysqli_close($con1);
//===========================================
while ( $r__ = mysqli_fetch_assoc($__result)) {

echo '<hr>DB:'.DB_PREFIX.$r__['cDBASE'].'<br>';
$mysqlcon = mysqli_connect(SERVER_NAME.":3306",USER,PASS,DB_PREFIX.$r__['cDBASE']);

$q = "SELECT * FROM webapp_admin_settings";
$result = mysqli_query($mysqlcon,$q);
while($__r=mysqli_fetch_assoc($result)){
	$GLOBALS[$__r['asKey']]= $__r['asValue'];
}


$minimumhours=0;
$maximumhours=20;
$tovar=implode(',',explode("\n",$GLOBALS['ADMIN_MAILS']));
$fromvar=$GLOBALS['EMAIL_MAILER_NAME'].'<'.$GLOBALS['EMAIL_MAILER_ADDRESS'].'>';
$currentdatevar="";

$TestEmailid="";
$previousdate=date('Y-m-d', strtotime(date("Y-m-d H:i:s").' -1 day'))."";//+1 day to current date//
$currentdatevar=$previousdate;
//intitalize function to calc weekstartdate and end date//
$getweekdatesdata=calculatestartdateandenddateofweek($currentdatevar);


$serverName = $GLOBALS['TCP_server']; //serverName\instanceName
$username=$GLOBALS['TCP_username'];
$password=$GLOBALS['TCP_password'];

$conn = mssql_connect( $serverName, $username, $password);
$selected = mssql_select_db($GLOBALS['TCP_database'], $conn);

if( $conn ) {
     echo "Connection established.<br />";
}else{
     echo "Connection could not be established.<br />";
}

//-------------------------//
//MYSQL DATABASE CONNECTION//


// Check connection
if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    else
        {
            echo "MYSQL Connection established.<hr />";
        }



//code to get date time of last update start here//
$currentdate=date('Y-m-d H:i:s');
$mysqllu="SELECT crLastRun FROM cron Where `crName`='timesclock_datasync_weekly'";
$resultlu = mysqli_query($mysqlcon, $mysqllu);
$rowlu = mysqli_fetch_array($resultlu);
$lastupdate=$rowlu["crLastRun"];
$datetime1 = new DateTime($rowlu["crLastRun"]);
$datetime2 = new DateTime($currentdate);
$interval = $datetime1->diff($datetime2);
$diftime=$interval->format('%d');
//code to get date time of last update end here//    

if($diftime>=6 ) //<<<<<<<<<<<<<
//if (true)
{

    $updatelu="UPDATE `cron` SET `crLastRun`='".date('Y-m-d H:i:s')."' WHERE `crName`='timesclock_datasync_weekly'";
    mysqli_query($mysqlcon, $updatelu);
    
//----------------------------------------------------------//
//code to get max timein and max timeout from hris database
if($currentdatevar!="")
  {
      $nextdate=date('Y-m-d', strtotime($currentdatevar.' +1 day'))." 23:59:59";//+1 day to current date//
  }
  else
      {
          $nextdate=date('Y-m-d', strtotime(' +1 day'))." 23:59:59";//+1 day to current date//
      }
$USHolidays=getFederalHolydays(date('Y'));
//print_r($USHolidays);
$now=date('Y-m-d');


$prev_week=start_end_date_prev_week();
$weekstartdate=$prev_week['start'];//week first date//
$weekenddate=$prev_week['end'];//week end date//
echo $weekstartdate.' '.$weekenddate;///exit;
//-----------------------------------------------------------------------------------------------//
//function to make string of valid user's employee numbers//
$cncnt=1;
//$mysqlcnquery="SELECT * FROM apply_forms Where `afSalaryType`='Hourly' and `afActive`='Hired' and `afNoPayrollJA`='no' 
$mysqlcnquery="SELECT * FROM apply_forms Where `afSalaryType`='Hourly' and ((`afActive`='Hired') OR (`afActive`='Terminated' AND `afDateLeaving`>='". $weekstartdate ."')) and `afNoPayrollJA`='no' 
and `afEmplNumber`!='' and `afStartDate`<'".date("Y-m-d")."'";

$resultcnquery = mysqli_query($mysqlcon, $mysqlcnquery);
$num_rowcnquery = mysqli_num_rows($resultcnquery);
$stingofvaliemployenumbers=array();
while($rowcnquery = mysqli_fetch_array($resultcnquery)){
        $stingofvaliemployenumbers[]=$rowcnquery["afEmplNumber"];

}
//----------------------------------------------------------------------------------------------//


            //copying data from timesclock database to hris database start here//
            if($nextdate<$weekenddate){ $fctimout=$nextdate; } else { $fctimout=$weekenddate;}

            
            $returnedrecordids="";
            $insertvalues="";
            $insertquerylvalues="";
            $emailmessage="";
            $emailmessagenotification="";

            $sql = "SELECT TimeIn, TimeOut, EmployeeRecordId, tcp_employeework.worksegment.RecordId as WorkRecordId, 
			tcp_employee.employee.RecordId as EmprecordId, tcp_employee.employee.EmployeeId as EmployeNumber FROM tcp_employeework.worksegment, 
			tcp_employee.employee Where (tcp_employee.employee.RecordId = tcp_employeework.worksegment.EmployeeRecordId) AND 
			(TimeIn >= '".$weekstartdate."') AND (TimeOut >= '".$weekstartdate."') AND 
			(TimeIn <= '".$weekenddate."') AND (TimeOut <= '".$weekenddate."') AND 
			(TimeIn <= TimeOut) AND (TimeOut - TimeIn < 20) AND 
			(tcp_employee.employee.EmployeeId IN (".implode(',',$stingofvaliemployenumbers)."))";

            $stmt = mssql_query($sql, $conn);
			
			$foundHolidays = array();
			$savedEmployees = array();
			$addMessage = '';
			
            while( $row = mssql_fetch_array( $stmt) )

                    {

                        $returnedrecordids.=$row['WorkRecordId'].", ";

                        $timein=date_create($row['TimeIn']);
						
						$date = date_format($timein, 'Y-m-d');
						
						$empNo = $row['EmployeNumber'];
						
                        $finaltimein=date_format($timein,"Y-m-d H:i:s");
						
						
                        $timeout=date_create($row['TimeOut']);

                        if($row['TimeOut']!=""){ $finaltimeout=date_format($timeout,"Y-m-d H:i:s"); }else { $finaltimeout="0"; }

                        if($finaltimein!="" and $finaltimeout!=""){ $hours=differenceInHours($finaltimein,$finaltimeout); }else{ $hours=0; }
						
						$gry="SELECT * FROM apply_forms Where `afEmplNumber`='".$row["EmployeNumber"]."'";
                        $resultgdata = mysqli_query($mysqlcon, $gry);
                        $emp = mysqli_fetch_array($resultgdata);
						
						$Type='Work';//Regular
						
						if ($row['TimeIn']==$row['TimeOut']){
							$addMessage.='<tr>'
							.'<td>'.$row["WorkRecordId"].'</td>'
							.'<td>'.$emp["afFirstName"].'</td>'
							.'<td>'.$emp["afLastName"].'</td>'
							.'<td>'.$row["EmployeNumber"].'</td>'
							.'<td>'.$finaltimein.'</td>'
							.'<td>'.$finaltimeout.'</td>'
							.'</tr>'."\r\n";
							continue;	
						}
						
						if (isInOfficeClosedDays($date,$empNo,date('Y'),$mysqlcon) ) {
							$hours = 0; $Type='Office closed';
							
							$addMessage.='<tr>'
							.'<td>'.$row["WorkRecordId"].'</td>'
							.'<td>'.$emp["afFirstName"].'</td>'
							.'<td>'.$emp["afLastName"].'</td>'
							.'<td>'.$row["EmployeNumber"].'</td>'
							.'<td>'.$finaltimein.'</td>'
							.'<td>'.$finaltimeout.'</td>'
							.'</tr>'."\r\n";
							
						}
						elseif (in_array($date,$USHolidays)) {
							//$foundHolidays[$date]=1;
							$hours = max(8,$hours);
							$Type='Holiday';
							if ($hours >8) $Type='Work';
							$savedEmployees[]=$row["EmployeNumber"];
						}
						else {
							$payd_days = getDates('paid',$mysqlcon,$row["EmployeNumber"],date('Y'));
							if (in_array($date,$payd_days)) {	
							$hours=8;$Type='Day off';
							$addMessage.='<tr>'
							.'<td>'.$row["WorkRecordId"].'</td>'
							.'<td>'.$emp["afFirstName"].'</td>'
							.'<td>'.$emp["afLastName"].'</td>'
							.'<td>'.$row["EmployeNumber"].'</td>'
							.'<td>'.$finaltimein.'</td>'
							.'<td>'.$finaltimeout.'</td>'
							.'</tr>'."\r\n";
							}
							
							$unpayd_days=getDates('unpaid',$mysqlcon,$row["EmployeNumber"],date('Y'));
							if (in_array($date,$unpayd_days)) {	
							$hours=0;$Type='Day off';
							$addMessage.='<tr>'
							.'<td>'.$row["WorkRecordId"].'</td>'
							.'<td>'.$emp["afFirstName"].'</td>'
							.'<td>'.$emp["afLastName"].'</td>'
							.'<td>'.$row["EmployeNumber"].'</td>'
							.'<td>'.$finaltimein.'</td>'
							.'<td>'.$finaltimeout.'</td>'
							.'</tr>'."\r\n";
							}
						}
                        $insertvalues.=" ('".$row['WorkRecordId']."', '".$row["EmployeNumber"]."', '".$finaltimein."', '".$finaltimeout."', '".round($hours,2)."', '".date("Y-m-d H:i:s")."','".$Type."'), ";
						


                        echo "Work Record ID:".$row["WorkRecordId"];echo ", ";
                        echo "Time In:".$row["TimeIn"];echo ", ";
                        echo "TimeOut:".$row["TimeOut"];echo ", ";
                        echo "Hours:".$hours;echo ", ";
						echo "Type:".$Type;
                        echo "<br>";

                    }


                    $deletequery="DELETE FROM `p_timeclock_worksegment` WHERE  (TimeIn >= '".$weekstartdate." 00:00:00') AND (TimeOut >= '".$weekstartdate." 00:00:00') 
					AND (TimeIn <= '".$weekenddate." 23:59:59') AND (TimeOut <= '".$weekenddate." 23:59:59')";
					
					//echo '<br>'.$deletequery.'<br>'; exit;
                    mysqli_query($mysqlcon, $deletequery);


                    //importdata//
                    $insertquery="INSERT INTO `p_timeclock_worksegment` (`RecordId`, `EmployeeNumber`, `TimeIn`, `TimeOut`, `WorkingHours`,`Updated_On`,`Type`) VALUES ".$insertvalues;
                    $finalquery=substr($insertquery, 0, -2);
					echo '<br>'.$finalquery.'<br>';
					if ($insertvalues !='') mysqli_query($mysqlcon, $finalquery);//insert working hours command//
					
					
					/************   GET UNSAVED HOURLY EMPLOYEES ****************/
					$foundHolidays=getHolidaysInInterval($weekstartdate,$weekenddate,$USHolidays);
					if (!empty($foundHolidays)) {
						if(!empty($savedEmployees)) $add=" AND afEmplNumber NOT IN (".implode(',',$savedEmployees).") ";
						else $add='';
						
						foreach ($foundHolidays as $now=>$v) {
							$q="SELECT * FROM apply_forms WHERE `afSalaryType`='Hourly' and `afActive`='Hired' and `afNoPayrollJA`='no' and 
							`afEmplNumber`!='' and `afStartDate`<'".date("Y-m-d")."' ".$add ;
							//echo $q;
							$insVal=array();
							$dayVal=array();
							$res = mysqli_query($mysqlcon, $q);
							while($row = mysqli_fetch_array($res)){
								//echo '<pre>';print_r($row);exit;
								$qry="SELECT * FROM p_listofweeks WHERE EMPLOYEE_NUMBER='{$row["afEmplNumber"]}' AND STARTDATE='{$weekstartdate}'";
								$rq = mysqli_query($mysqlcon, $qry);
								$cnt = mysqli_num_rows($rq);
								if ($cnt==0) $insVal[]="('".$row['afEmplNumber']."', '".$row["afID"]."', '".$weekstartdate."', '".$weekenddate."', 'No')";
								$dayVal[]="(0, '{$row['afEmplNumber']}', '{$now} 08:00:00', '{$now} 16:00:00', 8, '{$now} 23:55:59','Office closed')"; //Holiday
								 
							}
							
							$p_listofweeks = "INSERT INTO `p_listofweeks` (`EMPLOYEE_NUMBER`, `EMPLOYEE_ID`, `MANAGER_ID`, `STARTDATE`, `ENDDATE`, `SIGNED_STATUS`) 
							VALUES ".implode(', ', $insVal);                 
							mysqli_query($mysqlcon, $p_listofweeks);
							
							$insertquery ="INSERT INTO `p_timeclock_worksegment` (`RecordId`, `EmployeeNumber`, `TimeIn`, `TimeOut`, `WorkingHours`, `Updated_On`,`Type`) 
							VALUES ".implode(', ', $dayVal);  
							mysqli_query($mysqlcon, $insertquery);
						}
					}
					/************  END GET UNSAVED HOURLY EMPLOYEES ****************/
					
					/************   GET PAID/UNPAID DAYS IN INTERVAL  ****************/
					
					$dateNow = date('Y-m-d');
					$q="SELECT * FROM apply_forms WHERE `afSalaryType`='Hourly' AND `afActive`='Hired' AND `afNoPayrollJA`='no' AND 
							`afEmplNumber` != '' AND `afStartDate`<='{$dateNow}' " ;
					
					$_res = mysqli_query($mysqlcon,$q);
		
					$now = date('Y-m-d H:i:s');
					if($weekenddate > $dateNow) $weekenddate = $dateNow;
					
					while ( $_r = mssql_fetch_array( $_res) ) {
						$EmplNumber = $_r['afEmplNumber'];
						$EmpID = $_r['afID'];//<<<<<<<
						$payd_days = getDaysOfTypeFor('paid',$EmpID,$weekstartdate,$weekenddate,$mysqlcon);// $EmplNumber <-- $EmpID
						if(empty($payd_days)) continue;
						
						$insVal=array();
						$insertvalues='';
						$hours = 8;
						$Type = 'Day off';
						
						
						foreach ($payd_days as $pday){
							$finaltimein=$pday.' 08:00:00';
							$finaltimeout=$pday.' 16:00:00';
							$_day=$pday.' 22:00:00';
							$insertvalues.=" ('000001', '".$EmplNumber."', '".$finaltimein."', '".$finaltimeout."', '".round($hours,2)."', '{$now}','".$Type."'), ";
						}
						$insertquery ="INSERT INTO `p_timeclock_worksegment` (`RecordId`, `EmployeeNumber`, `TimeIn`, `TimeOut`, `WorkingHours`, `Updated_On`,`Type`) 
						VALUES ".$insertvalues;
						$finalquery=substr($insertquery, 0, -2);
						if ($insertvalues !='') mysqli_query($mysqlcon, $finalquery);
						
						
						
						/************  UNPAID DAYS IN INTERVAL *********************************/
						$unpayd_days = getDaysOfTypeFor('unpaid',$EmpID,$weekstartdate,$weekenddate,$mysqlcon); // <<<EmpID
						if(empty($unpayd_days)) continue;
						
						$insVal=array();
						$insertvalues='';
						$hours = 0;
						$Type = 'Day off';
						
						
						foreach ($unpayd_days as $pday){
							$finaltimein=$pday.' 08:00:00';
							$finaltimeout=$pday.' 16:00:00';
							$_day=$pday.' 22:00:00';
							$insertvalues.=" ('000001', '".$EmplNumber."', '".$finaltimein."', '".$finaltimeout."', '".round($hours,2)."', '{$now}','".$Type."'), ";
						}
						
						if ($insertvalues !=''){
							$insertquery ="INSERT INTO `p_timeclock_worksegment` (`RecordId`, `EmployeeNumber`, `TimeIn`, `TimeOut`, `WorkingHours`, `Updated_On`,`Type`) 
							VALUES ".$insertvalues;
							$finalquery=substr($insertquery, 0, -2);
							mysqli_query($mysqlcon, $finalquery);
						}
						
						$qry="SELECT * FROM p_listofweeks WHERE EMPLOYEE_NUMBER='{$EmplNumber}' AND STARTDATE='{$weekstartdate}'";
						$rq = mysqli_query($mysqlcon, $qry);
						$cnt = mysqli_num_rows($rq);
						if ($cnt==0) {
							$p_listofweeks = "INSERT INTO `p_listofweeks` (`EMPLOYEE_NUMBER`, `EMPLOYEE_ID`, `MANAGER_ID`, `STARTDATE`, `ENDDATE`, `SIGNED_STATUS`) 
							VALUES ('{$EmplNumber}', '".$_r["afID"]."', '{$weekstartdate}', '{$weekenddate}', 'No') ";                 
							mysqli_query($mysqlcon, $p_listofweeks);
						}
					
					}
					/************   END PAID/UNPAID DAYS IN INTERVAL  ****************/
					
					


//function to create and send wrong report//
$emailmessage.='<div class="container">';
$emailmessage.='';//'<h3>Hi, Admin</h3>';
$emailmessage.='<p>The following reports from TimeClockPlus for the last week were incorrect and were not imported into HRIS system:</p>';
$emailmessage.='<div class="table-responsive">';
$emailmessage.='<table class="table table-bordered table-hover" border="1" cellspacing="0">';
$emailmessage.='<thead>';
$emailmessage.='<tr>';
$emailmessage.='<th>Record ID</th>';
$emailmessage.='<th>First Name</th>';
$emailmessage.='<th>Last Name</th>';
$emailmessage.='<th>Employee Number</th>';
$emailmessage.='<th>Time In</th>';
$emailmessage.='<th>Time Out</th>';
$emailmessage.='</tr>';
$emailmessage.='</thead>';
$emailmessage.='<tbody>'."\r\n";


$SqlReports = "SELECT TimeIn, TimeOut, EmployeeRecordId, tcp_employeework.worksegment.RecordId as WorkRecordId, tcp_employee.employee.RecordId as EmprecordId, tcp_employee.employee.EmployeeId as EmployeNumber, FirstName,
	LastName FROM tcp_employeework.worksegment, tcp_employee.employee Where 
	(tcp_employee.employee.RecordId = tcp_employeework.worksegment.EmployeeRecordId) 
	AND ((TimeIn >= '".$weekstartdate."') OR (TimeOut >= '".$weekstartdate."')) 
	AND ((TimeIn <= '".$weekenddate."') OR ( TimeOut <= '".$weekenddate."')) 
	AND  (tcp_employee.employee.EmployeeId NOT IN (".implode(',',$stingofvaliemployenumbers)."))";
$StmtReports = mssql_query($SqlReports, $conn);


if (empty($StmtReports) && $addMessage=="") $emailmessage = '<br>No wrong reports.<br>';
else {
	$emailmessage.=$addMessage;
	while( $RowReports = mssql_fetch_array( $StmtReports) )
    {

        $timein=date_create($RowReports['TimeIn']);
        $finaltimein=date_format($timein,"m/d/Y g:i A");

        $timeout=date_create($RowReports['TimeOut']);
        $finaltimeout=date_format($timeout,"m/d/Y g:i A");

        $emailmessage.='<tr>';
        $emailmessage.='<td>'.$RowReports["WorkRecordId"].'</td>';
        $emailmessage.='<td>'.$RowReports["FirstName"].'</td>';
        $emailmessage.='<td>'.$RowReports["LastName"].'</td>';
        $emailmessage.='<td>'.$RowReports["EmployeNumber"].'</td>';
        $emailmessage.='<td>'.$finaltimein.'</td>';
        $emailmessage.='<td>'.$finaltimeout.'</td>';
        $emailmessage.='</tr>'."\r\n";
    }
	$emailmessage.='</tbody>';
	$emailmessage.='</table>';
}

$emailmessage.="<br>When a report is incorrect:<br>
- employee not existing or terminated<br>
- employee's Start Date in future<br>
- employee not hourly<br>
- no Employee Number assigned to employee<br>
- employee was away (vacation, sick leave, jury duty, CE)<br>
- report duration more than 20 hours<br>
- report in future<br>
- zero duration<br>
- time in and time out in different weeks<br>
- time out before time in<br>
- incorrect time in or out<br>
";
$emailmessage.='</div>';
$emailmessage.='<p>'.nl2br($GLOBALS['SYSTEM_MAILS_SIGNATURE']).'</p>';
echo $emailmessage;
sendemail($emailmessage,$tovar,"",$fromvar,"Wrong time report Last Week");






//function to send manager notification//
$LasWeekDate=$date = date("Y-m-d H:i:s", strtotime('-7 days', time()));//get last week date//
$GetLastWeekStartandEndDates=calculatestartdateandenddateofweek($LasWeekDate);//Function to calculate previous week start date and end date//
$PreviousWeekStartDate=$GetLastWeekStartandEndDates["weekstartdate"];//Previous Week Start Date//
$PreviousWeekEndDate=$GetLastWeekStartandEndDates["weekenddate"];//Previous Week End Date//

$cntGM=1;
$bccemails="";
$SqlQueryGM="SELECT DISTINCT MANAGER_ID, afFirstName, afLastName, afEmail FROM p_listofweeks, apply_forms WHERE (p_listofweeks.MANAGER_ID = apply_forms.afID) and (`STARTDATE`='".$PreviousWeekStartDate."' and `ENDDATE`='".$PreviousWeekEndDate."')";
$ResultGM = mysqli_query($mysqlcon, $SqlQueryGM);
$numGM = mysqli_num_rows($ResultGM);
while($RowGM = mysqli_fetch_array($ResultGM))
    {
        echo $RowGM["MANAGER_ID"];echo $RowGM["afFirstName"];echo ", ";
        if($TestEmailid!=""){ $bccemails.=$TestEmailid; }else { $bccemails.=$RowGM["afEmail"]; };
        if($cntGM<$numGM)
            {
                $bccemails.=", ";
            }

        $cntGM++;
    }

	
echo $tovarnav="mailer@humanroad.ferndaleroad.com";
echo "<br>";
echo $bccemails;
//code to send email Start Here//
$emailmessagenotification.='<div class="container">';
$emailmessagenotification.='Dear Sir/Madam,<br><br>';
$emailmessagenotification.='<p>Just a reminder to sign-off the weekly reports for the last week.</p>';
$emailmessagenotification.='</div>';
$emailmessagenotification.='<p>Sincerely,<br>Payroll Processing Team</p>';

sendemail($emailmessagenotification,$tovarnav,$bccemails,$fromvar,"Reminder to sign-off reports");
//code to send email Start Here//
//updateGeorgi($mysqlcon,$weekstartdate, $weekenddate);   
}//lastupdate condition end here//
}


//----------------------------REQUIRED FUNCTIONS FOR THE DATA SYNC CRONE----------------------------//
//function to get hours//

function differenceInHours($startdate,$enddate){
  $datetime1 = new DateTime($startdate);
  $datetime2 = new DateTime($enddate);
  $interval = $datetime1->diff($datetime2);
  $hour = $interval->format('%h');
  $minute = $interval->format('%i');
  if($minute<10){ $fminute="0".$minute; } else { $fminute=$minute; }

  $difference=$hour.".".$fminute;

	return $difference;
}



function sendemail_short($messagevar,$tovar,$fromvar,$subjectvar) {
            $to = $tovar;
            $subject = $subjectvar;
            $from = $fromvar;

            // To send HTML mail, the Content-type header must be set
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

            // Create email headers
            $headers .= 'From: '.$from."\r\n".
            'Reply-To: '.$from."\r\n" .
            'X-Mailer: PHP/' . phpversion();

            // Compose a simple HTML email message
            $message = '<html><body>';
            $message .= $messagevar;
            $message .= '</body></html>';

            mail($to , $subject, $message, $headers);
			echo "<hr><br>FROM:{$fromvar}<br>TO:{$to}<br>SUBJECT:{$subject}<br>MESSAGE:{$message}<hr>END<hr><br>";

}

//function to send emails//

function sendemail($messagevar,$tovar,$bcc,$fromvar,$subjectvar)
   {
      $to = $tovar;
      $subject = $subjectvar;
      $from = $fromvar;

      // To send HTML mail, the Content-type header must be set
      $headers  = 'MIME-Version: 1.0' . "\r\n";
      $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

      // Create email headers
      $headers .= 'From: '.$from."\r\n".
      'Reply-To: '.$from."\r\n" .
      'Bcc: '.$bcc."\r\n" .
      'X-Mailer: PHP/' . phpversion();

      // Compose a simple HTML email message
      $message = '<html><body>';
      $message.='<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">';
      $message.='<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:100,100i,300,300i,400,400i,500,500i,700,700i,900,900i">';
      $message.='<style type="text/css">';
      $message.='body {';
      $message.='font-family: Roboto, sans-serif !important;';
      $message.='font-weight: 300;';
      $message.='}';
      $message.='.table > tbody > tr > td,';
      $message.='.table > tbody > tr > th,';
      $message.='.table > tfoot > tr > td,';
      $message.='.table > tfoot > tr > th,';
      $message.='.table > thead > tr > td,';
      $message.='.table > thead > tr > th {';
      $message.='padding: 8px;';
      $message.='line-height: 1.42857143;';
      $message.='vertical-align: top;';
      $message.='border-top: 1px solid #ddd;';
      $message.='text-align: center;';
      $message.='}';
      $message.='.table > thead > tr > th {';
      $message.='font-weight: 900;';
      $message.='}';
      $message.='</style>';
      $message .= $messagevar;
      $message .= '</body></html>';
    
      mail($to , $subject, $message, $headers);
	  
	 
	  echo "<hr><br>FROM:{$from}<br>TO:{$to}<br>{$subject}<br>MESSAGE:{$message}<hr>END<hr><br>";
	  
    }

//function to get week start date and end date//

function updateGeorgi($mysqlcon,$weekstartdate, $weekenddate){
	// Georgi afID=1325 Nmb 850000000000000   |   Gathers ID=1404 Nmb 85

	$q="SELECT * FROM p_timeclock_worksegment WHERE EMPLOYEE_NUMBER='85' AND STARTDATE='{$weekstartdate}'";
	$res = mysqli_query($mysqlcon, $q);
	
	$insVal=array();
	$dayVal=array();
	
	if($res !== false) 
	{
		while($row = mysqli_fetch_array($res)){
			$dayVal[]="('{$row['RecordId']}', '850000000000000', '{$row['TimeIn']}', '{$row['TimeOut']}','{$row['WorkingHours']}', '{$row['Updated_On']}','{$row['Type']}')"; 	 
		}
	}
	 
	 
	 $qry= "SELECT * FROM p_listofweeks WHERE EMPLOYEE_NUMBER='85' AND STARTDATE='{$weekstartdate}'";
	 $rq = mysqli_query($mysqlcon, $qry);
	 $cnt = mysqli_num_rows($rq);
		
		if ($cnt==0) {
			$insVal[]="('850000000000000', '1325', '".$weekstartdate."', '".$weekenddate."', 'No')";
			$p_listofweeks = "INSERT INTO `p_listofweeks` (`EMPLOYEE_NUMBER`, `EMPLOYEE_ID`, `MANAGER_ID`, `STARTDATE`, `ENDDATE`, `SIGNED_STATUS`) 
			VALUES ".implode(', ', $insVal);                 
			mysqli_query($mysqlcon, $p_listofweeks);
		}
	
	if($res !== false) {
		$insertquery ="INSERT INTO `p_timeclock_worksegment` (`RecordId`, `EmployeeNumber`, `TimeIn`, `TimeOut`, `WorkingHours`, `Updated_On`,`Type`) 
		VALUES ".implode(', ', $dayVal);
		mysqli_query($mysqlcon, $insertquery);
	}
							
	echo '<br>GEORGE UPDATED<br>';
}

//----------------------------REQUIRED FUNCTIONS FOR THE DATA SYNC CRONE----------------------------//
//function to get hours//


	function calculatestartdateandenddateofweek($curendatevariable=NULL) {
	  if($curendatevariable!="") { $currentdateinf=$curendatevariable; } else { $currentdateinf=date("Y-m-d");}
	  $date=date_create($currentdateinf);
	  $Current=date_format($date,"N");

	  //$Current = Date('N');
	  $DaysToSunday = 7 - $Current;
	  $DaysFromMonday = $Current - 1;
	  $Sunday = Date('Y-m-d', StrToTime($currentdateinf. "+ {$DaysToSunday} Days"));
	  $Monday = Date('Y-m-d', StrToTime($currentdateinf. "- {$DaysFromMonday} Days"));

	  $resultwsed=array('weekstartdate'=>$Monday, 'weekenddate'=>$Sunday);

	  return $resultwsed;

	}
	
	function start_end_date_prev_week(){
		$previous_week = strtotime("-1 week +1 day");

		$start_week = strtotime("last Monday midnight",$previous_week);
		$end_week = strtotime("next Sunday",$start_week);
		
		$ret=array();
		$ret['start'] = date("Y-m-d",$start_week);//.' 00:00:00';
		$ret['end'] = date("Y-m-d",$end_week);//.' 23:59:59';
		

		return $ret;
	}
	
	/*************  HELPER FUNCTIONS ********************/
    function getDates($is_paid,$mysqlcon,$empNo,$year){

		$q="SELECT * FROM apply_forms  WHERE 	afEmplNumber='{$empNo}'";
		$res=mysqli_query($mysqlcon, $q);
		$user=mysqli_fetch_array($res,MYSQLI_ASSOC);

		//print_r($user);exit;
		
		$q="SELECT * FROM vacation_requests WHERE vrAFID={$user['afID']} AND (vrState='approved' OR vrState='used')
			AND vrPaydUnpaid='{$is_paid}' AND vrYear='{$year}'";
		
		$res=mysqli_query($mysqlcon, $q);
		
		$ret=array();
        foreach ($res as $k=>$r){
		   $unp=explode(',',$r['vrRealDays']);
		   foreach($unp as $j=>$u){
				$ret[]=$u;
		   }
	    }
		return $ret;
	}
	
	function isInOfficeClosedDays($date,$empNo,$year,$mysqlcon){
		$q="SELECT * FROM apply_forms  WHERE afEmplNumber='{$empNo}'";
		$res=mysqli_query($mysqlcon, $q);
		$user=mysqli_fetch_array($res,MYSQLI_ASSOC);
		
		$q="SELECT * FROM nonworking_days WHERE nwdOffices LIKE '%{$user['afCOID']}%'  AND nwdYear='{$year}'";
		
		$_res=mysqli_query($mysqlcon, $q);
		
		while ($row = mysqli_fetch_array($_res,MYSQLI_ASSOC)){
			
			//echo '<hr>>>>>'.$row['nwdDateStart'].' '.$row['nwdDateEnd'].'<hr>';
			if ($date>=$row['nwdDateStart'] && $date<=$row['nwdDateEnd']) return true;
		}
		return false;
	}
	
	function getFederalHolydays($year) {
		$holidays = array(
		$year.'-01-01',
		date("Y-m-d", strtotime("Last Monday of May ". $year)),
		$year.'-07-04',
		date("Y-m-d", strtotime("First Monday of September ".$year)),
		date("Y-m-d", strtotime("fourth Thursday of November ". $year)),
		$year.'-12-25');
		return $holidays;
	}

	function getHolidaysInInterval($weekstartdate,$weekenddate,$USHolidays){
		$date=$weekstartdate;
		$ret=array();
		while (strtotime($date) <= strtotime($weekenddate)) {
					//echo $date.'<br>';
					if(in_array($date,$USHolidays)) $ret[$date]=1;
					$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
		}
		return $ret;
	}
	
	function getDaysOfTypeFor($type,$EmpID,$weekstartdate,$weekenddate,$mysqlcon){ //vrAFID ={$EmpID}
		$q="SELECT * FROM vacation_requests WHERE vrAFID ={$EmpID}  AND vrState='approved' AND vrDateStart>='{$weekstartdate}' AND vrDateEnd<='{$weekenddate}' AND vrPaydUnpaid = '{$type}'";
		// added vrAFID ={$EmpID}
		$res=mysqli_query($mysqlcon, $q);
		$days=array();
		while ($r = mysqli_fetch_array($res,MYSQLI_ASSOC)){
			$_thisDays=explode(',',$r['vrRealDays']);
			foreach($_thisDays as $d){
				if($d >= $weekstartdate && $d <= $weekenddate) $days[]=$d;	
			}	
		}
		
		return $days;
	}

