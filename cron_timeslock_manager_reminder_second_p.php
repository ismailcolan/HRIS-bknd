<?php

ini_set('memory_limit', '-1');
include("web-app/config/app-config.php");
date_default_timezone_set('America/New_York');//initiate timezone//

$con1 = mysqli_connect(SERVER_NAME, USER, PASS, DB_PREFIX.'main');
$q="SELECT * FROM customers WHERE cActive='yes'";
$__result = mysqli_query($con1,$q);
mysqli_close($con1);
//===========================================
while ( $r__ = mysqli_fetch_assoc($__result)) {

echo '<hr>DB:'.DB_PREFIX.$r__['cDBASE'].'<br>';
$mysqlcon = mysqli_connect(SERVER_NAME.":3306",USER,PASS,DB_PREFIX.$r__['cDBASE']);

// Check connection
if (mysqli_connect_errno())
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();exit;
	}
else
	{
		echo "MYSQL Connection established.<br />";
	}

$q = "SELECT * FROM webapp_admin_settings";
$result = mysqli_query($mysqlcon,$q);
while($__r=mysqli_fetch_assoc($result)){
	$GLOBALS[$__r['asKey']]= $__r['asValue'];
}

//code to get date time of last update start here//
echo $currentdate=date('Y-m-d H:i:s');
$mysqllu="SELECT crLastRun FROM cron Where `crName`='timesclock_manager_emp_reminder'";
$resultlu = mysqli_query($mysqlcon, $mysqllu);
$rowlu = mysqli_fetch_array($resultlu);
$lastupdate=$rowlu["crLastRun"];
$datetime1 = new DateTime($rowlu["crLastRun"]);
$datetime2 = new DateTime($currentdate);
$interval = $datetime1->diff($datetime2);
echo "<br>";
echo 'DIFF TIME: '.$diftime=$interval->format('%d');
  
if($diftime>=4)//<<<<<<<<<<<  ////lastupdate condition starts here//
{

    $updatelu="UPDATE `cron` SET `crLastRun`='".date('Y-m-d H:i:s')."' WHERE `crName`='timesclock_manager_emp_reminder'";
    mysqli_query($mysqlcon, $updatelu);
    
    

//logical code start from here//
$TestEmailid="";
$LasWeekDate=$date = date("Y-m-d H:i:s", strtotime('-7 days', time()));//get last week date//
$GetLastWeekStartandEndDates=calculatestartdateandenddateofweek($LasWeekDate);//Function to calculate previous week start date and end date//
$PreviousWeekStartDate=$GetLastWeekStartandEndDates["weekstartdate"];//Previous Week Start Date//
$PreviousWeekEndDate=$GetLastWeekStartandEndDates["weekenddate"];//Previous Week End Date//


//function to create composite data//
$cntCD=1;
$bccemailsCD="";
$SqlQueryCD="SELECT MANAGER_ID, EMPLOYEE_ID, STARTDATE, ENDDATE, afFirstName, afLastName, afEmail FROM p_listofweeks, apply_forms WHERE (p_listofweeks.EMPLOYEE_ID = apply_forms.afID) and (`STARTDATE`='".$PreviousWeekStartDate."' and `ENDDATE`='".$PreviousWeekEndDate."') and `SIGNED_STATUS`='No'";
$ResultCD = mysqli_query($mysqlcon, $SqlQueryCD);
$numCD = mysqli_num_rows($ResultCD);
while($RowCD = mysqli_fetch_array($ResultCD))
    {
      if($TestEmailid!=""){ $bccemailsCD.=$TestEmailid; }else { $bccemailsCD.=$RowCD["afEmail"]; };
      if($cntCD<$numCD)
          {
              $bccemailsCD.=", ";
          }

      $cntCD++;
    }

//function to send notifications//
$cntGM=1;
$bccemails="";
$empemailmessage="";
$emailmessage="";

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

  echo "<br>";
  echo $tovar="mailer@humanroad.ferndaleroad.com";
  echo "<br>";
  echo $bccemails;
  echo "<br>";
  echo $bccemailsCD;

  $empemailmessage='<div class="container">';
  $empemailmessage.='Dear Sir/Madam,<br>';
  $empemailmessage.='<p>As of 3 PM today, your manager has not signed off on your hours for the current pay period. This is required in order to process your payroll and pay you this Friday.</p>';
  $empemailmessage.='<p>Unfortunately, your manager is the only person who can do this task. 
  If this is not completed by 10 AM Tuesday then your payroll will not be processed. 
  Please work with your manager by helping to remind your manager that this task must be completed immediately.</p>';
  $empemailmessage.='</div>';
  $empemailmessage.='<p>Sincerely,<br>Payroll Processing Team</p>';
  
  sendemail($empemailmessage,$tovar,$bccemailsCD,"mailer@humanroad.ferndaleroad.com","Weekly report not signed off");
  echo '<hr><hr>';
  echo '<hr>SUBJECT: Weekly report not signed off';
  echo '<br>SEND TO:'.$tovar;
  echo '<br>BCC: '.$bccemailsCD;
  echo '<br>MESSAGE: '.$empemailmessage;

  $emailmessage='<div class="container">';
  $emailmessage.='Dear Sir/Madam,<br>';
  $emailmessage.='<p>As of 3 PM today you still have unsigned time reports. Please login to your account to sign them off.</p>';
  $emailmessage.='<p>We rely on you to be our eyes and ears onsite. 
  One of the critical tasks in achieving this is confirming the actual hours worked by employees under your charge. 
  This confirmation is done electronically through HRIS as you have been trained.</p>';
  $emailmessage.='<p>Please understand, if the hours are not signed off then the listed employees cannot be paid. 
  The employee  has also received an email that the hours were not signed and thus the employee will not be paid.</p>';
  $emailmessage.='</div>';
  $emailmessage.='<p>Sincerely,<br>Payroll Processing Team</p>';
  
  sendemail($emailmessage,$tovar,$bccemails,"mailer@humanroad.ferndaleroad.com","IMPORTANT: Please sign off weekly reports");
  echo '<hr>SUBJECT: IMPORTANT: Please sign-off weekly reports';
  echo '<br>SEND TO:'.$tovar;
  echo '<br>BCC:'.$bccemails;
  echo '<br>MESSAGE: '.$emailmessage;
    
  }//lastupdate condition end here//
}

//function to get week start date and end date//
function calculatestartdateandenddateofweek($curendatevariable=NULL)
    {
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

    }

?>
