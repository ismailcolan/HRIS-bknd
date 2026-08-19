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
		      
            $adminMsgTable .= "{$task['employee']}, {$task['otDescription']}, Responsible: {$task['respEmployee']}<br/>\r\n";
        }
		
$msg="Dear Sir/Madam,<br><br>

There are overdue onboarding task(s) you are responsible for. Please login to your
Employee area and check the tasks.<br><br>\r\n

P.S. Please do not reply to this email, your email won't be received. If
you've got questions, please email or call your manager.<br>\r\n
<br>
".MAIL_FOOTER;

		$bcc=self::formatHeaders($Emails);
	/****/	
		cf::sendMail(
		'no-reply@hris.maidenlanemedical.com', 
		"Onboarding task(s) overdue", 
		$msg, 
		EMAIL_MAILER_NAME, EMAIL_MAILER_ADDRESS,
		'', '', $bcc);
		/****/
//echo '======>>>'.$bcc;

        //MailController::OnboardingEmployeeOverdueTasks($respName, $email, $respMsgTable);
        $adminMsgTable .= "<hr/>";
		 //echo 'ONBOARDING TO:ADMIN<br>'.$adminMsgTable;
        MailController::OnboardingAdminOverdueTasks($adminMsgTable);
		 echo "DONE ".date('Y-m-d H:i:s');
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
             //  MailController::OffboardingEmployeeOverdueTasks($respName, $email, $respMsgTable);
			      
               // $respMsgTable = "<hr/>";
                $email = $task["respEmail"];
                $respName = $task["respEmployee"];
            }

           // $respMsgTable .= "{$task['employee']}, {$task['otDescription']}<br/>\n";
            $adminMsgTable .= "{$task['employee']}, {$task['otDescription']}, Responsible: {$task['respEmployee']}<br/>\r\n";
        }
		/*************  MAIL TO RESP EMPLOYEES *************/
		$msg="Dear Sir/Madam,<br><br>

There are overdue offboarding task(s) you are responsible for. Please login to your
Employee area and check the tasks.<br><br>\n

P.S. Please do not reply to this email, your email won't be received. If
you've got questions, please email or call your manager.<br>\n
<br>".MAIL_FOOTER;

		$bcc=self::formatHeaders($Emails);
		
		cf::sendMail(
		'no-reply@hris.maidenlanemedical.com', 
		"Offboarding task(s) overdue", 
		$msg, 
		EMAIL_MAILER_NAME, EMAIL_MAILER_ADDRESS,
		'', '', $bcc);
		
		

       // $respMsgTable .= "<hr/>";
		// echo 'OFF-BOARDING TO:'.$respName.'<br>'.$respMsgTable;
       // MailController::OffboardingEmployeeOverdueTasks($respName, $email, $respMsgTable);
        $adminMsgTable .= "<hr/>";
		 echo 'OFF-BOARDING TO:ADMIN<br>'.$adminMsgTable;
        MailController::OffboardingAdminOverdueTasks($adminMsgTable);
		 echo "DONE ".date('Y-m-d H:i:s');
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
".MAIL_FOOTER;

		$bcc = self::formatHeaders($Emails);
		
		cf::sendMail(
		'no-reply@hris.maidenlanemedical.com', 
		"Credentialing overdue or required soon", 
		$msg, 
		EMAIL_MAILER_NAME, EMAIL_MAILER_ADDRESS,
		'', '',$bcc);
		
		//echo 'BCC>>>: '.$bcc;
        if (!empty($adminMsgTable)) {
            $adminMsgTable = "<hr/>" . $adminMsgTable . "<hr/>";
			 // echo "<br>TO ADMIN<br>".$adminMsgTable;
            MailController::CredentialingAdminOverdueTasks($adminMsgTable);
        }
		echo "DONE ".date('Y-m-d H:i:s');
    }

    final static function TitleAndMetaTags()
    {

    }
	
	final static function formatHeaders($Emails){
		return implode(',',$Emails);
	
	}
}
