<?php
ini_set('memory_limit', '-1');
date_default_timezone_set('America/New_York');//initiate timezone//
include("web-app/config/app-config.php");


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

//logical code start from here//
$TestEmailid="";
$LasWeekDate=$date = date("Y-m-d H:i:s", strtotime('-7 days', time()));//get last week date//
$GetLastWeekStartandEndDates=calculatestartdateandenddateofweek($LasWeekDate);//Function to calculate previous week start date and end date//
$PreviousWeekStartDate=$GetLastWeekStartandEndDates["weekstartdate"];//Previous Week Start Date//
$PreviousWeekEndDate=$GetLastWeekStartandEndDates["weekenddate"];//Previous Week End Date//


//function to create and send wrong report//
$cntGM=1;
$fromvar = 'hris@maidenlinemedical.com';
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

echo $tovar="managers@humanroad.com";
echo "<br>";

//code to send email Start Here//
    $emailmessage='<div class="container">';
    $emailmessage.='Dear Sir/Madam,<br>';
    $emailmessage.='<p>Just a reminder to sign-off the weekly reports for the last week.</p>';
    $emailmessage.='</div>';
    $emailmessage.='<p>Sincerely,<br>Payroll Processing Team</p>';

    sendemail($emailmessage,$tovar,$bccemails,"info@metavisia.com","Reminder to sign-off reports");
//code to send email Start Here//
echo '<hr>FROM:'.$fromvar;
echo '<br><br>BCC:'.$bccemails;
echo '<br>subject:Reminder to sign-off reports';
echo '<br><br>MESSAGE:'.$emailmessage;

echo '<hr>DONE';

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
