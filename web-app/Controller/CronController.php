<?php
class CronController extends AppController
{
	
    const __DUMP__ = false;

    public static $id, $data, $pg;

    static function Run($request)
    { 
		set_time_limit(0);	
		$now=date('Y-m-d H:i:s');
        switch ($request) {
            case 'onboarding':echo "ONBOARDING";
				db::SwitchToMain();
				$q="SELECT * FROM customers WHERE cActive='yes'";
				$__res=db::PrepareLoad($q);
				if (!is_array($__res )) $__res=array();
				foreach ($__res as $__r){
					echo '<hr>Customer: '.$__r['cCustomerName'].'<br>';
					db::SwitchToRealUsingDBName($__r['cDBASE']);
					db::setSiteSettingsAsGlobalVars();
					self::Check('onboarding_weekly');
					self::CronOverdueOnboarding();
					$__q = "UPDATE cron SET crLastRun='{$now}' WHERE crName='onboarding_weekly'";
					db::SQL($__q);
					echo '<br>Customer: '.$__r['cCustomerName'].' DONE.<br>';
				}
                break;
			  case 'offboarding':echo "OFF-BOARDING";
				db::SwitchToMain();
				$q="SELECT * FROM customers WHERE cActive='yes'";
				$__res=db::PrepareLoad($q);
				if (!is_array($__res )) $__res=array();
				foreach ($__res as $__r){
					echo '<hr>Customer: '.$__r['cCustomerName'].'<br>';
					db::SwitchToRealUsingDBName($__r['cDBASE']);
					db::setSiteSettingsAsGlobalVars();
					self::Check('offboarding_weekly');
					self::CronOverdueOffboarding('offboarding_weekly');
					$__q = "UPDATE cron SET crLastRun='{$now}' WHERE crName='offboarding_weekly'";
					db::SQL($__q);
					echo '<br>Customer: '.$__r['cCustomerName'].' DONE.<br>';
				}
                break;
            case 'credentialing':echo "CREDENTIALING";
				db::SwitchToMain();
				$q="SELECT * FROM customers WHERE cActive='yes'";
				$__res=db::PrepareLoad($q);
				//cf::pr($__res);
				if (!is_array($__res )) $__res=array();
				foreach ($__res as $__r){
					echo '<hr>Customer: '.$__r['cCustomerName'].'<br>';
					db::SwitchToRealUsingDBName($__r['cDBASE']);
					db::setSiteSettingsAsGlobalVars();
					self::Check('credentialing_weekly');
					self::CronOverdueCredentialing();
					$__q = "UPDATE cron SET crLastRun='{$now}' WHERE crName='credentialing_weekly'";
					db::SQL($__q);
					echo '<br>Customer: '.$__r['cCustomerName'].' DONE.<br>';
				}
                break;
			 case 'benefits': echo "BENEFITS UPLOAD";
				db::SwitchToMain();
				$q="SELECT * FROM customers WHERE cActive='yes'";
				$__res=db::PrepareLoad($q);
				if (!is_array($__res )) $__res=array();
				foreach ($__res as $r){
					echo '<hr>Customer: '.$r['cCustomerName'].'<br>';
					db::SwitchToRealUsingDBName($r['cDBASE']);
					db::setSiteSettingsAsGlobalVars();
					self::_BenefitsUpload();
				}
                break;
			case 'benefits-download': echo "BENEFITS DOWNLOAD";
				db::SwitchToMain();
				$q="SELECT * FROM customers WHERE cActive='yes'";
				$__res=db::PrepareLoad($q);
				if (!is_array($__res )) $__res=array();
				foreach ($__res as $r){
					echo '<hr>DB: '.$r['cCustomerName'].'<br>';
					db::SwitchToRealUsingDBName($r['cDBASE']);
					db::setSiteSettingsAsGlobalVars();
					self::_BenefitsDownload();
				}
                break;
            
            default:;
        }

    }
	
	public static function Check($what) {
        $q = "SELECT * FROM cron where crName='{$what}'";
        $task = db::PrepareLoadSingleRow($q);
		$lastRun=$task['crLastRun'];
		$now=date('Y-m-d H:i:s'); 
		$nextRun=date('Y-m-d H:i:s', strtotime("+6 days", strtotime($lastRun)));
		//if ($nextRun > $now) {echo 'Too early to start cron';exit;}
    }
	
	public static function  _BenefitsUpload(){
		$r = self::uploadFile();
		if ($r['result'] == 'OK'){
				echo '<br>Demographics sheet file generated and uploaded.';
		}
		else {
			echo '<br>There was problem in uploading Demographics sheet file. Reason: '.$r['message'];
		}
		
	}
	
	public static function  _BenefitsDownload(){ 
		$res1=self::downloadFile();
		if($res1['result']=='OK'){
			echo '<br>Benefits deductions file was downloaded.';
			
			$subject="Weekly Deductions";
			$nl="\r\n";
			$mail_body= "{$nl}Hi,{$nl},Please find attached the Weekly Deductions data.{$nl}".MAIL_FOOTER;
			
			$q = "SELECT * FROM webapp_admin_settings  WHERE asKey = 'BENEFITS_SETTINGS'";
			$_r = db::PrepareLoadSingleRow($q);
			//$settings = json_decode($_r['asValue'], true);
			$mailto = $GLOABALS['EmailDeductionsSheetTo'];
			//$mailto='pbx@developer.bg';
			
			$r = self::SendMailWithAttchments($GLOBALS['EMAIL_MAILER_NAME'],$GLOBALS['EMAIL_MAILER_ADDRESS'],$mailto,$subject,$mail_body,$res1['file_full_path']);
			if ($r['result'] == 'OK'){
				echo '<br>Benefits deductions was sent.';
			}
			else {
				echo '<br>Benefits deductions was NOT sent. Reason: '.$r['message'];
			}
		}
		else {
			echo 'Not downloaded new file. Reason:'.$res1['message'];
		}
	}
	
	
	public static function SendMailWithAttchments($EMAIL_MAILER_NAME,$EMAIL_MAILER_ADDRESS,$mailto,$subject,$message,$path){
		$r=array();

		$content = file_get_contents($path);
		$content = chunk_split(base64_encode($content));

		$content = file_get_contents( $path);
		$content = chunk_split(base64_encode($content));
		$uid = md5(uniqid(time()));
		
		// header
		$header = "From: ".$EMAIL_MAILER_NAME." <".$EMAIL_MAILER_ADDRESS.">\r\n";
		$header .= "Reply-To: ".$EMAIL_MAILER_ADDRESS."\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";

		// message & attachment
		$nmessage = "--".$uid."\r\n";
		$nmessage .= "Content-type:text/plain; charset=iso-8859-1\r\n";
		$nmessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$nmessage .= $message."\r\n\r\n";
		$nmessage .= "--".$uid."\r\n";
		$nmessage .= "Content-Type: application/octet-stream; name=\"".basename($path)."\"\r\n";
		$nmessage .= "Content-Transfer-Encoding: base64\r\n";
		$nmessage .= "Content-Disposition: attachment; filename=\"".basename($path)."\"\r\n\r\n";
		$nmessage .= $content."\r\n\r\n";
		$nmessage .= "--".$uid."--";

		//SEND Mail
		if (mail($mailto, $subject, $nmessage, $header)) {
			$r['result']='OK';
		} else {
			echo "mail send ERROR!";
			print_r( error_get_last() );
			$r['result']='FAIL';
		}

		return $r;
	}
	
	

    public static function Test() {
        $q = 'insert into test(testDate) values(CURRENT_TIMESTAMP)';
        db::SQL($q);
    }

    public static function CronOverdueOnboarding()
    {//and afActive = "Hired"
$today = new DateTime();	
        $q = ' SELECT otDescription,
	(SELECT CONCAT(afFirstName, " ", afLastName) FROM apply_forms WHERE afID=eoAFID and afActive = "Hired" ) employee,
	(SELECT afEmail FROM apply_forms WHERE afID=otAFID) respEmail,
	(SELECT afEmail FROM apply_forms af WHERE afID=eoAFID) empEmail,
	(SELECT CONCAT(afFirstName, " ", afLastName) FROM apply_forms WHERE afID=otAFID ) respEmployee
FROM employee_onboarding
LEFT JOIN onboarding_tasks ON otID=eoOTID
WHERE            otType="on" AND eoCompletedOn IS NULL AND eoTimeFrame < "'.$today->format(MYSQL_DATE_TIME).'"
HAVING (employee IS NOT NULL and respEmployee IS NOT NULL) 
ORDER BY otAFID';
//otActive = "Yes" AND
        $tasks = db::PrepareLoad($q);
	//cf::pr($tasks);
        if (empty($tasks)) {
			echo 'No overdue onboarding';
			MailController::OnboardingAdminOverdueTasks('<br>No onboarding tasks overdue.');
			return;
        }
		 
		 $Emails=array();
        $email = $tasks[0]["respEmail"];
		 
        $respName = $tasks[0]["respEmployee"];
       // $respMsgTable = "<hr/>";
        $adminMsgTable = "<hr/>";
		$__i=0;
        foreach ($tasks as $task) {
		
			  if (trim($task['respEmployee'])=='Employees themselves') $Emails[]=$task['empEmail'];
			   else $Emails[]=$task['respEmail'];
			   
            if ($email != $task["respEmail"]) {
              //  $respMsgTable .= "<hr/>";
				//echo 'TO:'.$respName.'<br>'.$respMsgTable;
             //   MailController::OnboardingEmployeeOverdueTasks($respName, $email, $respMsgTable);
             //   $respMsgTable = "<hr/>\n";
				  
                //$email = $task["respEmail"];
				
					
                $respName = $task["respEmployee"];
            }

           // $respMsgTable .= "{$task['employee']}, {$task['otDescription']}<br/>";
		      
            $adminMsgTable .= "{$task['employee']}, {$task['otDescription']}, Responsible: {$task['respEmployee']}<br/> \r\n";
			$__i++;
        }
		
		//$adminMsgTable .='<br>TORAL:'.$__i;
		
$msg="Dear Sir/Madam,<br><br>

There are overdue onboarding task(s) you are responsible for. Please login to your
Employee area and check the tasks.<br><br> \r\n

P.S. Please do not reply to this email, your email won't be received. If
you've got questions, please email or call your manager.<br> \r\n
<br>
".nl2br($GLOBALS['SYSTEM_MAILS_SIGNATURE']);

		$bcc=self::formatHeaders($Emails);
	/****/	
		cf::sendMail(
		'no-reply@humanroad.ferndaleroad.com', 
		"Onboarding task(s) overdue", 
		$msg, 
		$GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS'],
		'', '', $bcc);
		/****/


        //MailController::OnboardingEmployeeOverdueTasks($respName, $email, $respMsgTable);
        $adminMsgTable .= "<hr/>";
		echo 'ONBOARDING TO:ADMIN<br>'.$adminMsgTable;
        MailController::OnboardingAdminOverdueTasks($adminMsgTable);
		echo "DONE ONBOARDING".date('Y-m-d H:i:s');
    }
	
	 public static function CronOverdueOffboarding()
    {
		$today = new DateTime();
        $q = ' SELECT otDescription,
	(SELECT CONCAT(afFirstName, " ", afLastName) FROM apply_forms WHERE afID=eoAFID ) employee,
	(SELECT afEmail FROM apply_forms WHERE afID=otAFID) respEmail,
	(SELECT CONCAT(afFirstName, " ", afLastName) FROM apply_forms WHERE afID=otAFID  ) respEmployee
FROM employee_onboarding
LEFT JOIN onboarding_tasks ON otID=eoOTID
WHERE otType="off" AND eoCompletedOn IS NULL AND eoTimeFrame < "'.$today->format(MYSQL_DATE_TIME).'"
HAVING (employee IS NOT NULL and respEmployee IS NOT NULL) 
ORDER BY otAFID';

        $tasks = db::PrepareLoad($q);
        if (empty($tasks)) {
			  MailController::OffboardingAdminOverdueTasks('<br>No off-boarding tasks overdue.');
            echo 'No overdue off-boarding';return;
        }
        $email = $tasks[0]["respEmail"];
        $respName = $tasks[0]["respEmployee"];
        //$respMsgTable = "<hr/>";
        $adminMsgTable = "<hr/>";
		 $Emails=array();
		 $respMsgTable ='';
        foreach ($tasks as $task) {
			  $Emails[]=$task['respEmail'];
            if ($email != $task["respEmail"]) {
                $respMsgTable .= "<hr/>";
				//echo 'TO:'.$respName.'<br>'.$respMsgTable;
                MailController::OffboardingEmployeeOverdueTasks($respName, $email, $respMsgTable);
			      
               // $respMsgTable = "<hr/>";
                $email = $task["respEmail"];
                $respName = $task["respEmployee"];
            }

           // $respMsgTable .= "{$task['employee']}, {$task['otDescription']}<br/>\r\nn";
            $adminMsgTable .= "{$task['employee']}, {$task['otDescription']}, Responsible: {$task['respEmployee']}<br/> \r\n";
        }
		/*************  MAIL TO RESP EMPLOYEES *************/
		$msg="Dear Sir/Madam,<br><br>

There are overdue offboarding task(s) you are responsible for. Please login to your
Employee area and check the tasks.<br><br>\r\n

P.S. Please do not reply to this email, your email won't be received. If
you've got questions, please email or call your manager.<br>\r\n
<br>".nl2br($GLOBALS['SYSTEM_MAILS_SIGNATURE']);

		$bcc=self::formatHeaders($Emails);
		
		cf::sendMail(
		'no-reply@humanroad.ferndaleroad.com', 
		"Offboarding task(s) overdue", 
		$msg, 
		$GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS'],
		'', '', $bcc);
		
		

        $respMsgTable .= "<hr/>";
		//echo 'OFF-BOARDING TO:'.$respName.'<br>'.$respMsgTable;
        MailController::OffboardingEmployeeOverdueTasks($respName, $email, $respMsgTable);
        $adminMsgTable .= "<hr/>";
		 echo 'OFF-BOARDING TO:ADMIN<br>'.$adminMsgTable;
        MailController::OffboardingAdminOverdueTasks($adminMsgTable);
		 echo "DONE OFFBOARDING ".date('Y-m-d H:i:s');
    }


    public static function CronOverdueCredentialing()
    {
        
        $ecIDString = "";
        
        $Query = "SELECT afID FROM apply_forms WHERE afActive = 'Hired'";
        $QueryArray = db::PrepareLoad($Query);
        
        foreach ($QueryArray as $single)
        {
            foreach ($single as $individual)
            {
                
                $CQuery = "select * from employee_credentialing LEFT join credentialings ON ecCID=cID where ( cType ='recurring' and cActive='Yes' and ecAFID = $individual) XOR ( cType = 'one-time' and cActive='Yes' and ecAFID = $individual)";
                
                $CQueryArray = db::PrepareLoad($CQuery);
                $CQueryArray = SkipUloadedCredentials($CQueryArray);
                
                foreach ($CQueryArray as $Csingle)
                {
                        
                    foreach ($Csingle as $Cindividual)
                    {
                        //file_put_contents("credentialings-data-after-processing.txt", $Cindividual . "\r\n", FILE_APPEND);
                        $ecIDString .= $Cindividual . ",";
                        break;
                    }
                }
                
                
            }
        }
        
        //file_put_contents("credentialings-data-after-processing.txt", $ecIDString, FILE_APPEND);
        $ecIDString = substr($ecIDString, 0, strlen($ecIDString)-1);
        //file_put_contents("credentialings-data-after-processing.txt", "\r\n", FILE_APPEND);
        //file_put_contents("credentialings-data-after-processing.txt", $ecIDString, FILE_APPEND);
        
        
        
        //$Query = "select * from employee_credentialing LEFT join credentialings ON ecCID=cID where ( cType ='recurring' and cActive='Yes' and ecAFID = 3089) XOR ( cType = 'one-time' and cActive='Yes' and ecAFID = 3089)";
       
        
         $today = new DateTime();
        $q = "SELECT 
        concat(afFirstName, ' ', afLastName) as afName, afID, cID, cName, ecDueDate, afEmail, ecID
    FROM `employee_credentialing` 
    LEFT JOIN apply_forms on afID = ecAFID
    LEFT JOIN credentialings on cID = ecCID
    WHERE ecDueDate <'".$today->format(MYSQL_DATE_TIME)."'  and afActive = 'Hired' and cActive = 'Yes' and ecUploadedOn is null and ecID IN ($ecIDString)
    order by afID, ecDueDate";
    file_put_contents("credentialings-data-after-processing.txt", $q, FILE_APPEND);
        $data = db::PrepareLoad($q);
        
        
        foreach ($data as $Csingle)
        {
                
            foreach ($Csingle as $Cindividual)
            {
                file_put_contents("credentialings-data-after-processing.txt", $Cindividual . "\r\n", FILE_APPEND);
                $ecIDString .= $Cindividual . ",";
                break;
            }
        }
        
        //return;
        $today = time();
        $after3Months = strtotime("+3 months");
        $employees = [];
		  if (empty($data)) {
			echo 'No overdue credentialings';
			MailController::CredentialingAdminOverdueTasks('<br>No credentialing overdue.');
			return;
			}
        foreach ($data as $item) {
            $afID = $item["afID"];
            $dueDate = strtotime($item["ecDueDate"]);

            if ($dueDate < $today) {
                if (!isset($employees[$afID])) {
                    $employees[$afID]["name"] = $item["afName"];
                    $employees[$afID]["email"] = $item["afEmail"];
                    $employees[$afID]["credentialing"] = $item["cName"];

                    $employees[$afID]["overdue"] = [$item];
                    $employees[$afID]["soon"] = [];
                } else {
                    $employees[$afID]["overdue"][] = $item;
                }
            } elseif ($dueDate < $after3Months) {
                if (!isset($employees[$afID])) {
                    $employees[$afID]["name"] = $item["afName"];
                    $employees[$afID]["email"] = $item["afEmail"];
                    $employees[$afID]["credentialing"] = $item["cName"];

                    $employees[$afID]["overdue"] = [];
                    $employees[$afID]["soon"] = [$item];
                } else {
                    $employees[$afID]["soon"][] = $item;
                }
            }

        }

        $adminMsgTable = "";
		 
		 $count=1;
		 $Emails=array();
        foreach ($employees as $empl) {
            $email = $empl["email"];
            $name = $empl["name"];
			  $adminMsgTable .='<br>';
            if (!empty($empl["overdue"])) {
                $respOverdueMsgTable = "<hr/>";
				
                foreach ($empl["overdue"] as $task) {
					
                    $task["ecDueDate"] = val::convDate(MYSQL_DATE_TIME,USER_DATE,$task["ecDueDate"]);
					 
                   // $respOverdueMsgTable .= "{$task['cName']}, Due Date: {$task['ecDueDate']}<br/>";
                    $adminMsgTable .= "{$name}, {$task['cName']}, Due Date: {$task['ecDueDate']}<br/>\r\n";
                }
               // $respOverdueMsgTable .= "<hr/>";
            } else {
               // $respOverdueMsgTable = "";
            }

            if (!empty($empl["soon"])) {
                //$respMsgTable = "<hr/>";
                foreach ($empl["soon"] as $task) {
                    $task["ecDueDate"] = val::convDate(MYSQL_DATE_TIME,USER_DATE,$task["ecDueDate"]);
					  // $respMsgTable .= "{$task['cName']}, Due Date: {$task['ecDueDate']}<br/>";
                }
               // $respMsgTable .= "<hr/>";
            } else {
               // $respMsgTable = "";
            }
			  //echo "<br> ".$count++." TO ".$name.' '.$email."<br>".$respOverdueMsgTable.'<br><br>'.$respMsgTable.'<hr>';
            
            // ZLATEV
            MailController::CredentialingEmployeeOverdueTasks($name, $email, $respOverdueMsgTable, $respMsgTable);
			$Emails[]=$email;
        }
		/***** SEND mail at once ****************/
		
		$msg="Dear Sir/Madam,<br><br>

There are credentialing files you need to upload. Please login to your
Employee area, click CREDENTIALING on the left menu and upload the
required files.<br><br>

P.S. Please do not reply to this email, your email won't be received. If
you've got questions, please email or call your manager.<br>
<br>
".nl2br($GLOBALS['SYSTEM_MAILS_SIGNATURE']);

		$bcc = self::formatHeaders($Emails);
		
		cf::sendMail(
		'no-reply@humanroad.ferndaleroad.com', 
		"Credentialing overdue or required soon", 
		$msg, 
		$GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS'],
		'', '',$bcc);
		
		echo 'BCC>>>: '.$bcc;
        if (!empty($adminMsgTable)) {
            $adminMsgTable = "<hr/>" . $adminMsgTable . "<hr/>";
			echo "<hr>TO ADMIN:<br>".$adminMsgTable;
			//file_put_contents("maillog.log", $adminMsgTable);
            //ZLATEV:
            MailController::CredentialingAdminOverdueTasks($adminMsgTable);			
        }
		echo "DONE CREDENTIALING ".date('Y-m-d H:i:s');
    }
	
	
	
	public static function BenefitsUpload($ExcelName){ 
	
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		//echo "START";exit;
		//define('NET_SSH2_LOGGING', 3);

		global $_US_STATES;

		require_once "lib/xlsxwriter.class.php";
		
		$q="SELECT * FROM webapp_admin_settings WHERE asKey='BENEFITS_SETTINGS' ";
		$res=db::PrepareLoadSingleRow($q);
		$data=json_decode($res['asValue'],true);
		
		$ExcelName=$data['excelSheedPrefix'].'_'.date('dmY').'.xlsx';
		//echo $ExcelName;
		$dir ='/home/developmv/public_html/add/uploads/for_upload/benefits_uploaded/';
		$ExcelFileName=$dir.''.$ExcelName;
		//echo $ExcelFileName;exit;
		$monthBefore =  date( "Y-m-d", strtotime( date("Y-m-d") . " -1 month" )) ;
		
		/*************/
		$qry='SELECT * FROM companies WHERE comActive = "Yes" ORDER BY comName ASC';
		$res = db::PrepareLoad($qry);
		if($res === false) $res=array();
		$companies=array();
		foreach ($res as $k=>$r){
			$companies[$r['comID']]=$r['comName'];
		}
		/************/
		$writer = new XLSXWriter();
		$format =array(
		'freeze_rows'=>1, 
		'freeze_columns'=>2,
		
		'widths'=>
				[
				15,//1
				5,//2
				6,//3
				20,//4
				6, //5
				20,//6
				6, //7
				10, //8
				7, //9
				10, //10
				15, //11
				25,//12
				20,//13
				15,//14
				15,//15
				7,//16
				15,//16a
				15,//19
				15,//20
				35,//21
				35,//22
				10,//23
				10,//24
				15,//25
				15,//26
				15,//27
				15,//28
				15,//29
				20, //30
				25,//30a
				15,//30b
				15,//30c
				30,//31
				10, //32
				10, //33
				25, //34
				20, //35
				15, //36
				13, //37
				13, //38
				13, //39
				13, //40
				13, //41
				13, //42
				13, //43
				13, //44
				13, //45
				15, //46
				20, //47
				25, //48
				25, //49
				15, //50
				10, //51
				10, //52
				10, //53
				10, //54
				20, //55
				10, //56
				12 //57
				]
		,
		['font-style'=>'bold','wrap_text'=>true],//1
		['font-style'=>'bold','wrap_text'=>true],//2
		['font-style'=>'normal','wrap_text'=>true],//3
		['font-style'=>'bold','wrap_text'=>true],	//4		
		['font-style'=>'normal','wrap_text'=>true],//5		
		['font-style'=>'bold','wrap_text'=>true],	//6
		['font-style'=>'normal','wrap_text'=>true],//7
		['font-style'=>'bold','wrap_text'=>true],	//8
		['font-style'=>'bold','wrap_text'=>true],	//9
		['font-style'=>'normal','wrap_text'=>true],//10
		['font-style'=>'bold','wrap_text'=>true],//11
		['font-style'=>'normal','wrap_text'=>true],//12
		['font-style'=>'normal','wrap_text'=>true],//13
		['font-style'=>'normal','wrap_text'=>true],//14
		['font-style'=>'normal','wrap_text'=>true],//15
		['font-style'=>'normal','wrap_text'=>true],//16
		['font-style'=>'normal','wrap_text'=>true],//16a
		['font-style'=>'normal','wrap_text'=>true],//18
		['font-style'=>'normal','wrap_text'=>true],//19
		['font-style'=>'bold','wrap_text'=>true],//20
		['font-style'=>'bold','wrap_text'=>true],//21
		['font-style'=>'bold','wrap_text'=>true],	//22		
		['font-style'=>'bold','wrap_text'=>true],	//23
		['font-style'=>'bold','wrap_text'=>true],//24
		['font-style'=>'bold','wrap_text'=>true],//25
		['font-style'=>'normal','wrap_text'=>true],//26
		['font-style'=>'bold','wrap_text'=>true],//27
		['font-style'=>'normal','wrap_text'=>true],//28
		['font-style'=>'normal','wrap_text'=>true],//29
		['font-style'=>'bold','wrap_text'=>true],//30
		['font-style'=>'normal','wrap_text'=>true],//30a
		['font-style'=>'normal','wrap_text'=>true],//30b
		['font-style'=>'normal','wrap_text'=>true],//30c
		['font-style'=>'bold','wrap_text'=>true],//31
		['font-style'=>'normal','wrap_text'=>true],//32
		['font-style'=>'normal','wrap_text'=>true],//33				
		['font-style'=>'normal','wrap_text'=>true],//34		
		['font-style'=>'normal','wrap_text'=>true],//35	
		['font-style'=>'normal','wrap_text'=>true],//36
		['font-style'=>'normal','wrap_text'=>true],//37
		['font-style'=>'normal','wrap_text'=>true],//38
		['font-style'=>'normal','wrap_text'=>true],//39
		['font-style'=>'normal','wrap_text'=>true],//40
		['font-style'=>'normal','wrap_text'=>true],//41
		['font-style'=>'bold','wrap_text'=>true],//42
		['font-style'=>'bold','wrap_text'=>true],//43
		['font-style'=>'normal','wrap_text'=>true],//44
		['font-style'=>'normal','wrap_text'=>true],//45				
		['font-style'=>'normal','wrap_text'=>true],//46		
		['font-style'=>'normal','wrap_text'=>true],//47		
		['font-style'=>'normal','wrap_text'=>true],//48
		['font-style'=>'normal','wrap_text'=>true],//49
		['font-style'=>'normal','wrap_text'=>true],//50
		['font-style'=>'normal','wrap_text'=>true],//51
		['font-style'=>'normal','wrap_text'=>true],//52
		['font-style'=>'normal','wrap_text'=>true],//53
		['font-style'=>'normal','wrap_text'=>true],//54
		['font-style'=>'normal','wrap_text'=>true],//55
		['font-style'=>'normal','wrap_text'=>true],//56
		['font-style'=>'normal','wrap_text'=>true]//57
		);
		
		
		$writer->writeSheetHeader(
			'Employees', 
			array(
				'Employee SSN'=>'string',//1
				'EID'=>'string',//2
				'Prefix'=>'',//3
				'First'=>'string',//4
				'MI'=>'string',//5
				'Last'=>'string',//6
				'Sufix'=>'string',//7
				'DOB'=>'string',//8
				'Sex'=>'string',//9
				"Marital status"=>'string',//10
				'Country'=>'string',//11
				'Address1'=>'string',//12
				'Address2'=>'string',//13
				'City'=>'string', //14
				'State'=>'string',//15
				'Zip'=>'string',//16
				'Home Phone'=>'string', //16a
				'Mobile Phone'=>'string',//18	
				'Work Phone'=>'string',//19
				'Email'=>'string',//20
				'Personal Email'=>'string',//21
				'State'=>'string',//22			
				"Payroll frequency"=>'integer',//22
				"Deduction frequency"=>'integer',//23
				'Gross Salary'=>'price',//'integer',//24
				"Location Number"=>'string',//25
				'Location'=>'string',//26
				'Job Class'=>'string', //27
				'Pay Group'=>'string',//28
				'Department Number'=>'string',//29
				'Department'=>'string',//30
				'Title'=>'string',//30a
				'FTE' =>'string',//30b
				'Hours Per Week'=>'string',//30c
				'Hire Date'=>'string',//31
				"Eligibility Date"=>'string',//32
				'Staus'=>'string',//33
				'Enrollment Status'=>'string',//34
				'Termination date'=>'string',//34	
				'Event date'=>'string',//36
				'PIN'=>'string',//37
				"Require PIN Change"=>'string',//38
				'As Of'=>'string',//39
				"Session UserID"=>'string',//40
				'Session City'=>'string',//41
				"Session State"=>'string',//42
				"Hourly Wage"=>'string',//43	
				"PTO Balance"=>'string',//44
				'PTO Cost'=>'string',//45
				'Mailing Country'=>'string',//45	
				'Mailing Address 1'=>'string',//47
				'Mailing Address 2'=>'string',//48
				'Mailing City'=>'string',//49	
				"Mailing State"=>'string',//50
				'Mailing Zip'=>'string',//51	
				"Country of Citizenship"=>'string',//52
				"Event Code"=>'string',//53
				'Event Description'=>'string',//54	
				'User ID'=>'string',//55
				"Birth Country"=>'string',//56
				"CompanyID"=>"string"//57
			), $format 
			
			);
			
			$now = date('m/d/Y');
				
			$q="SELECT * FROM company_offices WHERE coActive='Yes'";
			$results=db::PrepareLoad($q);
			$Ofices=array();
			foreach ($results as $r){
				$Ofices[$r['coID']] = $r['coName'];
			}

			$q="SELECT * FROM departments WHERE dActive='Yes'";
			$results=db::PrepareLoad($q);
			$Departments=array();
			foreach ($results as $r){
				$Departments[$r['dID']] = $r['dName'];
			}
			
			$q="SELECT * FROM apply_forms WHERE (afActive = 'Hired' OR afChangedToTerminated >'{$monthBefore}')";
			$res=db::PrepareLoad($q);
			if($res==false) $res=array();
			//cf::pr($Departments);
			//echo $ExcelFileName;
		
			foreach ($res as $k => $r){
			//echo '<br>'.$r['afCompany'];
			if($r['afSalaryType']=='Annual'){
				$PayrollFrequency = $data['PayrollFrequencyAnualEmployees'];
				$DeductionFrequency = $data['DeductionFrequencyAnualEmployees'];
				$GrossSalary =$r['afSalary'];
				if($r['afCompany']=='KAL Health') $companyID='19058176';
				elseif ($r['afCompany']=='Maiden Lane Medical') $companyID='19058160';
				else $companyID='undefined';
			}
			else {
				$PayrollFrequency = $data['PayrollFrequencyHourlyEmployees'];
				$DeductionFrequency = $data['DeductionFrequencyHourlyEmployees'];
				
				$GrossSalary = $r['afSalary']*1960;
				if($r['afCompany']=='KAL Health') $companyID='19058143';
				elseif ($r['afCompany']=='Maiden Lane Medical') $companyID='19058145';
				else $companyID='undefined';
			}
			
				
			if( $r['afNoPayrollJA']=='yes' ) $JobClass='owner'; else $JobClass ='employee';
		    if (!isset($Departments[$r['afDID']])) $_Department ='not defined'; else $_Department = $Departments[$r['afDID']];
			
			$tbl=array(
				$r['afSSNmb'],//1
				$r['afEmplNumber'],//2
				'',//3
				$r['afFirstName'],//4
				'',//5
				$r['afLastName'],//6
				'',//7
				val::convDate(MYSQL_DATE_TIME,USER_DATE,$r['afBirthday']),//8
				$r['afGender'],//9
				$r['afMaritalStatus'],
				'',//Country
				$r['afAddressLine1'],
				$r['afAddressLine2'],//10
				$r['afCity'], //11
				$_US_STATES[$r['afState']],//12
				$r['afZip'],//13
				$r['afHomePhone'],
				$r['afCellPhone'],
				$r['afWorkPhone'],
				$r['afEmail'],
				'',//Personal Email
				$PayrollFrequency,//int 14
				$DeductionFrequency,//int 15
				$GrossSalary,//int 16
				'',
				$Ofices[$r['afCOID']],//17
				$JobClass,
				'',
				'',
				 //18
				$_Department,//$Departments[$r['afDID']],//19
				'',//21
				'',
				'',
				val::convDate(MYSQL_DATE,USER_DATE,$r['afDateHired']),//20
				'',
				$r['afPaymentStatus'],//22
				'',//27
				'',
				'',
				'',
				'',
				$now,//28
				'',
				'',//29
				'',//30
				'',//30a
				'',//30b
				'',//30c
				'',//31
				'',//32
				'',//33
				'',//34
				'',//35
				'',//36
				'',//37
				'',//38
				'',//39
				'',//40
				'',//41
				$companyID
			);		
			$writer->writeSheetRow('Employees', $tbl);		
		};
		
		//$writer->writeToFile($ExcelFileName);
		$filedata = $writer->writeToString();
		echo  $ExcelFileName.' DONE.<br>';
		/***********  SFTP begin **************/
		
		
		define('SITE','sftp://'.FTP_username.':'.FTP_password.'@'.FTP_server.':'.FTP_port);

		$remote_file = FTP_download.FTP_file_download;
		$local_file =  LOCAL_DOWNLOAD_DIR.date('Y-m-d').FTP_file_download;
		
        $ch = curl_init();
         curl_setopt($ch, CURLOPT_VERBOSE, 1);
         curl_setopt($ch, CURLOPT_URL, SITE.REMOUTE_UPLOAD_DIR.$ExcelName);
		 curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,30);
		 
		 echo '<br>Trying to connect to: '.SITE.REMOUTE_UPLOAD_DIR.$ExcelName.'<br>';
         $fp = tmpfile();
         fwrite($fp, $filedata);
         fflush($fp);
         rewind($fp);

         curl_setopt($ch, CURLOPT_UPLOAD, 1);
         curl_setopt($ch, CURLOPT_INFILE, $fp);
         curl_setopt($ch, CURLOPT_INFILESIZE, strlen($filedata));
         
		 $resultUpload = curl_exec($ch);
		 $error_msg = curl_error($ch);
		 
         fclose($fp);
		 if($resultUpload) echo 'UPLOAD OK'; 
		 else {
			 echo 'ERROR: UPLOAD FILED<br>';
			 echo 'Error message: '.$error_msg;
		 }
		
		/**************  OK start **************
		$resConnection = ssh2_connect(FTP_server,FTP_port);

		if(ssh2_auth_password($resConnection, FTP_username, FTP_password)){
			
			$UPLFile = FTP_upload.$ExcelName;
			//echo 'UPLOAD'.$UPLFile;
			
			$remote_file = FTP_download.FTP_file_download;
			$local_file =  LOCAL_DOWNLOAD_DIR.date('Y-m-d').FTP_file_download;
			
			
			try {
				//************   UPLOAD OK *************
				$sftp = ssh2_sftp($resConnection);
				// 
				$sftpStream = fopen("ssh2.sftp://".intval($sftp) . $UPLFile, "w");
				if (!$sftpStream) {
					throw new Exception("Could not open remote file: {$UPLFile}");
				}
				$data_to_send = file_get_contents($ExcelFileName);
				if ($data_to_send === false) {
					throw new Exception("Could not open local file: {$srcFile}.");
				}
				if (fwrite($sftpStream, $data_to_send) === false) {
					throw new Exception("Could not send data from file: {$ExcelFileName}.");
				}
				fclose($sftpStream);
			    echo '<br>Uploaded';
				
				//*******  DOWNLOAD  OK *********
				//echo '<br>Remote:'.$remote_file.'<br>'.$local_file;
				
				if(copy( $remote_file, $local_file)) {
				//if(ssh2_scp_recv($resConnection, $remote_file, $local_file)) {
					echo "<br>Downloaded";
				} else {
					echo "<br>File Download Failed";
				}
			} catch (Exception $e) {
				error_log('Exception: ' . $e->getMessage());
				fclose($sftpStream);
			}
			
		}else{
			echo "Unable to authenticate on server";
		}

		***********  OK end **************/
	
	}
	
	public static function generateExcel($ExcelName){ 
		global $_US_STATES;

		require_once "lib/xlsxwriter.class.php";
		
		$q="SELECT * FROM webapp_admin_settings WHERE asKey='BENEFITS_SETTINGS' ";
		$res=db::PrepareLoadSingleRow($q);
		$data=json_decode($res['asValue'],true);
		
		$LOCAL_UPLOAD_DIR = FS_ROOT."add/customer".$_SESSION['customerID']."/uploads/for_upload/benefits_uploaded/";
		$ExcelFileName=$LOCAL_UPLOAD_DIR.''.$ExcelName;
		
		$monthBefore =  date( "Y-m-d", strtotime( date("Y-m-d") . " -1 month" )) ;
		
		
		$qry='SELECT * FROM companies WHERE comActive = "Yes" ORDER BY comName ASC';
		$res = db::PrepareLoad($qry);
		if($res === false) $res=array();
		$companies=array();
		foreach ($res as $k=>$r){
			$companies[$r['comID']]['Name']=$r['comName'];
			$companies[$r['comID']]['Anual']=$r['comCompanyIDAnual'];
			$companies[$r['comID']]['Hourly']=$r['comCompanyIDHourly'];
		}
		
		$writer = new XLSXWriter();
		$format =array(
		'freeze_rows'=>1, 
		'freeze_columns'=>2,
		
		'widths'=>
				[
				15,//1
				5,//2
				6,//3
				20,//4
				6, //5
				20,//6
				6, //7
				10, //8
				7, //9
				10, //10
				15, //11
				25,//12
				20,//13
				15,//14
				15,//15
				7,//16
				15,//16a
				15,//19
				15,//20
				35,//21
				35,//22
				10,//23
				10,//24
				15,//25
				15,//26
				15,//27
				15,//28
				15,//29
				20, //30
				25,//30a
				15,//30b
				15,//30c
				30,//31
				10, //32
				10, //33
				25, //34
				20, //35
				15, //36
				13, //37
				13, //38
				13, //39
				13, //40
				13, //41
				13, //42
				13, //43
				13, //44
				13, //45
				15, //46
				20, //47
				25, //48
				25, //49
				15, //50
				10, //51
				10, //52
				10, //53
				10, //54
				20, //55
				10, //56
				12 //57
				]
		,
		['font-style'=>'bold','wrap_text'=>true],//1
		['font-style'=>'bold','wrap_text'=>true],//2
		['font-style'=>'normal','wrap_text'=>true],//3
		['font-style'=>'bold','wrap_text'=>true],	//4		
		['font-style'=>'normal','wrap_text'=>true],//5		
		['font-style'=>'bold','wrap_text'=>true],	//6
		['font-style'=>'normal','wrap_text'=>true],//7
		['font-style'=>'bold','wrap_text'=>true],	//8
		['font-style'=>'bold','wrap_text'=>true],	//9
		['font-style'=>'normal','wrap_text'=>true],//10
		['font-style'=>'bold','wrap_text'=>true],//11
		['font-style'=>'normal','wrap_text'=>true],//12
		['font-style'=>'normal','wrap_text'=>true],//13
		['font-style'=>'normal','wrap_text'=>true],//14
		['font-style'=>'normal','wrap_text'=>true],//15
		['font-style'=>'normal','wrap_text'=>true],//16
		['font-style'=>'normal','wrap_text'=>true],//16a
		['font-style'=>'normal','wrap_text'=>true],//18
		['font-style'=>'normal','wrap_text'=>true],//19
		['font-style'=>'bold','wrap_text'=>true],//20
		['font-style'=>'bold','wrap_text'=>true],//21
		['font-style'=>'bold','wrap_text'=>true],	//22		
		['font-style'=>'bold','wrap_text'=>true],	//23
		['font-style'=>'bold','wrap_text'=>true],//24
		['font-style'=>'bold','wrap_text'=>true],//25
		['font-style'=>'normal','wrap_text'=>true],//26
		['font-style'=>'bold','wrap_text'=>true],//27
		['font-style'=>'normal','wrap_text'=>true],//28
		['font-style'=>'normal','wrap_text'=>true],//29
		['font-style'=>'bold','wrap_text'=>true],//30
		['font-style'=>'normal','wrap_text'=>true],//30a
		['font-style'=>'normal','wrap_text'=>true],//30b
		['font-style'=>'normal','wrap_text'=>true],//30c
		['font-style'=>'bold','wrap_text'=>true],//31
		['font-style'=>'normal','wrap_text'=>true],//32
		['font-style'=>'normal','wrap_text'=>true],//33				
		['font-style'=>'normal','wrap_text'=>true],//34		
		['font-style'=>'normal','wrap_text'=>true],//35	
		['font-style'=>'normal','wrap_text'=>true],//36
		['font-style'=>'normal','wrap_text'=>true],//37
		['font-style'=>'normal','wrap_text'=>true],//38
		['font-style'=>'normal','wrap_text'=>true],//39
		['font-style'=>'normal','wrap_text'=>true],//40
		['font-style'=>'normal','wrap_text'=>true],//41
		['font-style'=>'bold','wrap_text'=>true],//42
		['font-style'=>'bold','wrap_text'=>true],//43
		['font-style'=>'normal','wrap_text'=>true],//44
		['font-style'=>'normal','wrap_text'=>true],//45				
		['font-style'=>'normal','wrap_text'=>true],//46		
		['font-style'=>'normal','wrap_text'=>true],//47		
		['font-style'=>'normal','wrap_text'=>true],//48
		['font-style'=>'normal','wrap_text'=>true],//49
		['font-style'=>'normal','wrap_text'=>true],//50
		['font-style'=>'normal','wrap_text'=>true],//51
		['font-style'=>'normal','wrap_text'=>true],//52
		['font-style'=>'normal','wrap_text'=>true],//53
		['font-style'=>'normal','wrap_text'=>true],//54
		['font-style'=>'normal','wrap_text'=>true],//55
		['font-style'=>'normal','wrap_text'=>true],//56
		['font-style'=>'normal','wrap_text'=>true]//57
		);
		
		
		$writer->writeSheetHeader(
			'Employees', 
			array(
				'Employee SSN'=>'string',//1
				'EID'=>'string',//2
				'Prefix'=>'',//3
				'First'=>'string',//4
				'MI'=>'string',//5
				'Last'=>'string',//6
				'Sufix'=>'string',//7
				'DOB'=>'string',//8
				'Sex'=>'string',//9
				"Marital status"=>'string',//10
				'Country'=>'string',//11
				'Address1'=>'string',//12
				'Address2'=>'string',//13
				'City'=>'string', //14
				'State'=>'string',//15
				'Zip'=>'string',//16
				'Home Phone'=>'string', //16a
				'Mobile Phone'=>'string',//18	
				'Work Phone'=>'string',//19
				'Email'=>'string',//20
				'Personal Email'=>'string',//21
				'State'=>'string',//22			
				"Payroll frequency"=>'integer',//22
				"Deduction frequency"=>'integer',//23
				'Gross Salary'=>'price',//'integer',//24
				"Location Number"=>'string',//25
				'Location'=>'string',//26
				'Job Class'=>'string', //27
				'Pay Group'=>'string',//28
				'Department Number'=>'string',//29
				'Department'=>'string',//30
				'Title'=>'string',//30a
				'FTE' =>'string',//30b
				'Hours Per Week'=>'string',//30c
				'Hire Date'=>'string',//31
				"Eligibility Date"=>'string',//32
				'Staus'=>'string',//33
				'Enrollment Status'=>'string',//34
				'Termination date'=>'string',//34	
				'Event date'=>'string',//36
				'PIN'=>'string',//37
				"Require PIN Change"=>'string',//38
				'As Of'=>'string',//39
				"Session UserID"=>'string',//40
				'Session City'=>'string',//41
				"Session State"=>'string',//42
				"Hourly Wage"=>'string',//43	
				"PTO Balance"=>'string',//44
				'PTO Cost'=>'string',//45
				'Mailing Country'=>'string',//45	
				'Mailing Address 1'=>'string',//47
				'Mailing Address 2'=>'string',//48
				'Mailing City'=>'string',//49	
				"Mailing State"=>'string',//50
				'Mailing Zip'=>'string',//51	
				"Country of Citizenship"=>'string',//52
				"Event Code"=>'string',//53
				'Event Description'=>'string',//54	
				'User ID'=>'string',//55
				"Birth Country"=>'string',//56
				"CompanyID"=>"string"//57
			), $format 
			
			);
			
			$now = date('m/d/Y');
				
			$q="SELECT * FROM company_offices WHERE coActive='Yes'";
			$results=db::PrepareLoad($q);
			$Ofices=array();
			foreach ($results as $r){
				$Ofices[$r['coID']] = $r['coName'];
			}

			$q="SELECT * FROM departments WHERE dActive='Yes'";
			$results=db::PrepareLoad($q);
			$Departments=array();
			foreach ($results as $r){
				$Departments[$r['dID']] = $r['dName'];
			}
			
			$q="SELECT * FROM apply_forms WHERE (afActive = 'Hired' OR afChangedToTerminated >'{$monthBefore}') AND afID > 1";
			$res=db::PrepareLoad($q);
			if($res==false) $res=array();
			
			foreach ($res as $k => $r){
			
			if($r['afSalaryType']=='Annual'){
				$PayrollFrequency = $data['PayrollFrequencyAnualEmployees'];
				$DeductionFrequency = $data['DeductionFrequencyAnualEmployees'];
				$GrossSalary = $r['afSalary'];
				
				if(isset($companies[$r['afCompany']]['Anual'])) $companyID=$companies[$r['afCompany']]['Anual'];
				else $companyID='undefined';
			}
			else {
				$PayrollFrequency = $data['PayrollFrequencyHourlyEmployees'];
				$DeductionFrequency = $data['DeductionFrequencyHourlyEmployees'];
				
				$GrossSalary = $r['afSalary']*1960;
				
				if(isset($companies[$r['afCompany']]['Hourly'])) $companyID=$companies[$r['afCompany']]['Hourly'];
				else $companyID='undefined';
			}
			
				
			if( $r['afNoPayrollJA']=='yes' ) $JobClass='owner'; else $JobClass ='employee';
		    if (!isset($Departments[$r['afDID']])) $_Department ='not defined'; else $_Department = $Departments[$r['afDID']];
			
			$tbl=array(
				$r['afSSNmb'],//1
				$r['afEmplNumber'],//2
				'',//3
				$r['afFirstName'],//4
				'',//5
				$r['afLastName'],//6
				'',//7
				val::convDate(MYSQL_DATE_TIME,USER_DATE,$r['afBirthday']),//8
				$r['afGender'],//9
				$r['afMaritalStatus'],
				'',//Country
				$r['afAddressLine1'],
				$r['afAddressLine2'],//10
				$r['afCity'], //11
				$_US_STATES[$r['afState']],//12
				$r['afZip'],//13
				$r['afHomePhone'],
				$r['afCellPhone'],
				$r['afWorkPhone'],
				$r['afEmail'],
				'',//Personal Email
				$PayrollFrequency,//int 14
				$DeductionFrequency,//int 15
				$GrossSalary,//int 16
				'',
				$Ofices[$r['afCOID']],//17
				$JobClass,
				'',
				'',
				 //18
				$_Department,//$Departments[$r['afDID']],//19
				'',//21
				'',
				'',
				val::convDate(MYSQL_DATE,USER_DATE,$r['afDateHired']),//20
				'',
				$r['afPaymentStatus'],//22
				'',//27
				'',
				'',
				'',
				'',
				'',//$now,//28
				'',
				'',//29
				'',//30
				'',//30a
				'',//30b
				'',//30c
				'',//31
				'',//32
				'',//33
				'',//34
				'',//35
				'',//36
				'',//37
				'',//38
				'',//39
				'',//40
				'',//41
				$companyID
			);		
			$writer->writeSheetRow('Employees', $tbl);		
		};
		
		$writer->writeToFile($ExcelFileName);
		//$filedata = $writer->writeToString();
		//echo  $ExcelFileName.' GENERATED.<br>';
		
	}
	
	public static function doUploadFile($ExcelName){
		$result=array();
		$result['message']='';
		
		$resConnection = @ssh2_connect($GLOBALS['FTP_server'],$GLOBALS['FTP_port']);
		$UPLFile = $GLOBALS['FTP_upload'].$ExcelName;
		$LOCAL_UPLOAD_DIR = FS_ROOT."add/customer".$_SESSION['customerID']."/uploads/for_upload/benefits_uploaded/";
		$ExcelFileName=$LOCAL_UPLOAD_DIR.$ExcelName;
	/*******
	echo 	'SERVER:'.$GLOBALS['FTP_server'].'<br> PORT:'.$GLOBALS['FTP_port'].'<br> User:'.$GLOBALS['FTP_username']
	.'<br> PASS:'.$GLOBALS['FTP_password']
	.'<BR>FILE:'.$ExcelFileName
	.'<BR>FILE TO UPLOAD:'.$UPLFile;
	exit;
	********/

		if(@ssh2_auth_password($resConnection, $GLOBALS['FTP_username'], $GLOBALS['FTP_password'])){
			
			
			
			try {
				//************   UPLOAD *************
				$sftp = ssh2_sftp($resConnection);
				$sftpStream = fopen("ssh2.sftp://".intval($sftp) . $UPLFile, "w");
				if (!$sftpStream) {
					throw new Exception("Could not open remote file: {$UPLFile}");
				}
				$data_to_send = file_get_contents($ExcelFileName);
				if ($data_to_send === false) {
					throw new Exception("Could not open local file: {$srcFile}.");
				}
				if (fwrite($sftpStream, $data_to_send) === false) {
					throw new Exception("Could not send data from file: {$ExcelFileName}.");
				}
				fclose($sftpStream);
			    $result['result']='OK';
				$result['file'] = $ExcelName;
				
			} catch (Exception $e) {
				$msg=$e->getMessage();
				fclose($sftpStream);
				//echo $msg;exit;
				error_log('Exception: ' . $msg);
				
				//unlink($ExcelFileName );
				$result['result']='FAIL';
				$result['message']=$msg;
			}
			
		}else{
			$result['result']='FAIL';
			$result['message']="Unable to authenticate on server";
			unlink($ExcelFileName);
		}

		
		return $result;
	}
	
	public static function uploadFile(){
		
		$q="SELECT * FROM webapp_admin_settings WHERE asKey='BENEFITS_SETTINGS' ";
		$res=db::PrepareLoadSingleRow($q);
		$data=json_decode($res['asValue'],true);
		
		$ExcelName=$data['excelSheedPrefix'].'_'.date('dmY').'.xlsx';
		self::generateExcel($ExcelName);
		
		$_result = self::doUploadFile($ExcelName);
		
		
		$result = array();
		$result['result'] = $_result['result'];
		$result['file'] = $ExcelName;
		$result['message']=$_result['message'];
		return $result;
		
		
	}
	
	
	function my_ssh_disconnect($reason, $message, $language='eng') {
			printf("Server disconnected with reason code [%d] and message: %s\n",$reason, $message);exit;
	}
	
	public static function downloadFile(){

		$result=array();
		$remote_file = $GLOBALS['FTP_upload'].$GLOBALS['FTP_file_download'];
		
		$local_file_short = date('Y-m-d').''.$GLOBALS['FTP_file_download'];
		$LOCAL_DOWNLOAD_DIR = FS_ROOT."add/customer".$_SESSION['customerID']."/uploads/for_upload/benefits_uploaded/";
		$local_file =  $LOCAL_DOWNLOAD_DIR.$local_file_short;
		
	

		$methods = array(
		  'kex' => 'diffie-hellman-group1-sha1',
		  'client_to_server' => array(
			'crypt' => '3des-cbc',
			'comp' => 'none'),
		  'server_to_client' => array(
			'crypt' => 'aes256-cbc,aes192-cbc,aes128-cbc',
			'comp' => 'none'));

		$callbacks = array('disconnect' => 'my_ssh_disconnect');

		
		$resConnection = @ssh2_connect(FTP_server,FTP_port,$methods, $callbacks);
		if (!$resConnection) {
			$result['message']='Connection failed.';
			$result['result']='FAIL';
			return $result;
			
		}
		
		
		if( @ssh2_auth_password($resConnection, FTP_username, FTP_password)){
			try {
				
				$sftp = @ssh2_sftp($resConnection);
				
				if(@copy( $remote_file, $local_file)) {
				
					$result['result']='OK';
					$result['file']=$local_file_short;
					$result['file_full_path']=$local_file;
				} else {
					
					$result['result']='FAIL';
					
				}
			} catch (Exception $e) {
				$msg=$e->getMessage();
				error_log('Exception: ' .$msg );
				$result['message']=$msg;
				fclose($sftpStream);
				$result['result']='FAIL';
			}
			
		}else{
			$result['result']='FAIL';
			$result['message'] = "Unable to authenticate on server";
		}
		
		return $result;
	}
	

	
	final static function formatHeaders($Emails){
		return implode(',',$Emails);
	
	}
}
