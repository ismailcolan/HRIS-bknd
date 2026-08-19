<?php
class CronController extends AppController
{
	
    const __DUMP__ = false;

    public static $id, $data, $pg;

    public static function Run($request)
    { 
		set_time_limit(0);
		$now=date('Y-m-d H:i:s');
        switch ($request) {
            case 'onboarding':
			    self::Check('onboarding_weekly');
                self::CronOverdueOnboarding();
				$q = "UPDATE cron SET crLastRun='{$now}' WHERE crName='onboarding_weekly'";
				db::SQL($q);
                break;
			  case 'offboarding':
				self::Check('offboarding_weekly');
                self::CronOverdueOffboarding('offboarding_weekly');
				$q = "UPDATE cron SET crLastRun='{$now}' WHERE crName='offboarding_weekly'";
				db::SQL($q);
                break;
            case 'credentialing':
				self::Check('credentialing_weekly');
                self::CronOverdueCredentialing();
				$q = "UPDATE cron SET crLastRun='{$now}' WHERE crName='credentialing_weekly'";
				db::SQL($q);
                break;
			 case 'benefits': 
				//self::Check('benefits_weekly');
                self::BenefitsUpload();
				$q = "UPDATE cron SET crLastRun='{$now}' WHERE crName='benefits_weekly'";
				db::SQL($q);
                break;
            case 'test':
                self::Test();
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
		if ($nextRun > $now) {echo 'Too early to start cron';exit;}
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
WHERE otType="on" AND eoCompletedOn IS NULL AND eoTimeFrame < "'.$today->format(MYSQL_DATE_TIME).'"
HAVING (employee IS NOT NULL and respEmployee IS NOT NULL) 
ORDER BY otAFID';

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
        }
		
$msg="Dear Sir/Madam,<br><br>

There are overdue onboarding task(s) you are responsible for. Please login to your
Employee area and check the tasks.<br><br> \r\n

P.S. Please do not reply to this email, your email won't be received. If
you've got questions, please email or call your manager.<br> \r\n
<br>
".MAIL_FOOTER_HTML;

		$bcc=self::formatHeaders($Emails);
	/****/	
		cf::sendMail(
		'no-reply@hris.maidenlanemedical.com', 
		"Onboarding task(s) overdue", 
		$msg, 
		EMAIL_MAILER_NAME, EMAIL_MAILER_ADDRESS,
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
<br>".MAIL_FOOTER_HTML;

		$bcc=self::formatHeaders($Emails);
		
		cf::sendMail(
		'no-reply@hris.maidenlanemedical.com', 
		"Offboarding task(s) overdue", 
		$msg, 
		EMAIL_MAILER_NAME, EMAIL_MAILER_ADDRESS,
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
		 $today = new DateTime();
        $q = "SELECT 
        concat(afFirstName, ' ', afLastName) as afName, afID, cID, cName, ecDueDate, afEmail
    FROM `employee_credentialing` 
    LEFT JOIN apply_forms on afID = ecAFID
    LEFT JOIN credentialings on cID = ecCID
    WHERE ecDueDate <'".$today->format(MYSQL_DATE_TIME)."'  and afActive = 'Hired' and cActive = 'Yes' and ecUploadedOn is null
    order by afID, ecDueDate";

        $data = db::PrepareLoad($q);
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
            //MailController::CredentialingEmployeeOverdueTasks($name, $email, $respOverdueMsgTable, $respMsgTable);
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
".MAIL_FOOTER_HTML;

		$bcc = self::formatHeaders($Emails);
		
		cf::sendMail(
		'no-reply@hris.maidenlanemedical.com', 
		"Credentialing overdue or required soon", 
		$msg, 
		EMAIL_MAILER_NAME, EMAIL_MAILER_ADDRESS,
		'', '',$bcc);
		
		echo 'BCC>>>: '.$bcc;
        if (!empty($adminMsgTable)) {
            $adminMsgTable = "<hr/>" . $adminMsgTable . "<hr/>";
			echo "<br>TO ADMIN<br>".$adminMsgTable;
            MailController::CredentialingAdminOverdueTasks($adminMsgTable);
        }
		echo "DONE CREDENTIALING ".date('Y-m-d H:i:s');
    }
	
	
	
	public static function BenefitsUpload(){ 
	
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		
		//define('NET_SSH2_LOGGING', 3);

		global $_US_STATES;

		require_once "lib/xlsxwriter.class.php";
		
		$q="SELECT * FROM webapp_admin_settings WHERE asKey='BENEFITS_SETTINGS' ";
		$res=db::PrepareLoadSingleRow($q);
		$data=json_decode($res['asValue'],true);
		
		$ExcelName=$data['excelSheedPrefix'].'_'.date('dmY').'.xlsx';
		echo $ExcelName;
		$dir =FP . 'add/uploads/for_upload/benefits';
		$ExcelFileName=$dir.'/'.$ExcelName;
		$monthBefore =  date( "Y-m-d", strtotime( date("Y-m-d") . " -1 month" )) ;
		
		
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
			
		
			foreach ($res as $k => $r){
			
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
				$Departments[$r['afDID']],//19
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
		
		$writer->writeToFile($ExcelFileName);
		//echo $writer->writeToString();
	echo '<br>FILE OK';

		
		$strServer = FTP_server;
		$strServerPort = FTP_port;
		$strServerUsername =FTP_username;
		$strServerPassword = FTP_password;
		$resFile = FTP_upload.'/'.$ExcelName;
		$srcFile= $ExcelFileName;
		/*******
		//connect to server
		$resConnection = ssh2_connect($strServer, $strServerPort);

		if(ssh2_auth_password($resConnection, $strServerUsername, $strServerPassword)){
			//Initialize SFTP subsystem
			echo "connected";
			$resSFTP = ssh2_sftp($resConnection);    
			$resFile = fopen("ssh2.sftp://{$resSFTP}/".$resFile, 'w');
			$srcFile = fopen($csv_filename, 'r');
			$writtenBytes = stream_copy_to_stream($srcFile, $resFile);
			fclose($resFile);
			fclose($srcFile);                  

		}else{
			echo "Unable to authenticate on server";
		}
		*******/
		
		//$res = Sftp::upload($strServer, $strServerUsername, $strServerPassword, $srcFile, $resFile, $strServerPort);
		//if($res) echo 'UPLOADED OK';
		
		include "lib/Net/SFTP.php";
		
		//define('NET_SFTP_LOGGING', NET_SFTP_LOG_COMPLEX);
		//echo FTP_server.' '.FTP_port.' >>> '.FTP_username.' '.FTP_password;
		$sftp = new Net_SFTP(FTP_server,FTP_port,1000);
		
		//echo $sftp->getLog();
		//echo '--->'.$sftp->login(FTP_username, FTP_password);
		if (!$sftp->login(FTP_username, FTP_password)) { 
			exit('Login Failed');
		} 
		else echo 'Connected';
		
		//unlink($ExcelFileName);
		
		echo '<br>DONE';
	
	}
	
	
    final static function TitleAndMetaTags()
    {

    }
	
	final static function formatHeaders($Emails){
		return implode(',',$Emails);
	
	}
}
