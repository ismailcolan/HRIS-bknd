<?php
class CronController extends AppController
{

    const __DUMP__ = false;

    public static $id, $data, $pg;

    static function Run($request)
    {
		 set_time_limit(0);
        switch ($request) {
            case 'onboarding':
                self::CronOverdueOnboarding();
                break;
			  case 'offboarding':
                self::CronOverdueOffboarding();
                break;
            case 'credentialing':
                self::CronOverdueCredentialing();
                break;
            case 'test':
                self::Test();
                break;
            default:;
        }

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
	(SELECT CONCAT(afFirstName, " ", afLastName) FROM apply_forms WHERE afID=otAFID ) respEmployee
FROM employee_onboarding
LEFT JOIN onboarding_tasks ON otID=eoOTID
WHERE otType="on" AND eoCompletedOn IS NULL AND eoTimeFrame < "'.$today->format(MYSQL_DATE_TIME).'"
HAVING (employee IS NOT NULL and respEmployee IS NOT NULL) 
ORDER BY otAFID';

        $tasks = db::PrepareLoad($q);
		
        if (empty($tasks)) {
            echo 'No overdue onboarding';return;
        }

        $email = $tasks[0]["respEmail"];
        $respName = $tasks[0]["respEmployee"];
        $respMsgTable = "<hr/>";
        $adminMsgTable = "<hr/>";

        foreach ($tasks as $task) {
			  sleep(0.5);
            if ($email != $task["respEmail"]) {
                $respMsgTable .= "<hr/>";
				//echo 'TO:'.$respName.'<br>'.$respMsgTable;
                MailController::OnboardingEmployeeOverdueTasks($respName, $email, $respMsgTable);
                $respMsgTable = "<hr/>";
				
                $email = $task["respEmail"];
                $respName = $task["respEmployee"];
            }

            $respMsgTable .= "{$task['employee']}, {$task['otDescription']}<br/>";
            $adminMsgTable .= "{$task['employee']}, {$task['otDescription']}, Responsible: {$task['respEmployee']}<br/>";
        }

        $respMsgTable .= "<hr/>";
		 //echo 'ONBOARDING TO:'.$respName.'<br>'.$respMsgTable;
        MailController::OnboardingEmployeeOverdueTasks($respName, $email, $respMsgTable);
        $adminMsgTable .= "<hr/>";
		 //echo 'ONBOARDING TO:ADMIN<br>'.$adminMsgTable;
        MailController::OnboardingAdminOverdueTasks($adminMsgTable);
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
            echo 'No overdue off-boarding';return;
        }

        $email = $tasks[0]["respEmail"];
        $respName = $tasks[0]["respEmployee"];
        $respMsgTable = "<hr/>";
        $adminMsgTable = "<hr/>";

        foreach ($tasks as $task) {
				sleep(0.5);
            if ($email != $task["respEmail"]) {
                $respMsgTable .= "<hr/>";
				//echo 'TO:'.$respName.'<br>'.$respMsgTable;
                MailController::OffboardingEmployeeOverdueTasks($respName, $email, $respMsgTable);
                $respMsgTable = "<hr/>";
                $email = $task["respEmail"];
                $respName = $task["respEmployee"];
            }

            $respMsgTable .= "{$task['employee']}, {$task['otDescription']}<br/>";
            $adminMsgTable .= "{$task['employee']}, {$task['otDescription']}, Responsible: {$task['respEmployee']}<br/>";
        }

        $respMsgTable .= "<hr/>";
		 echo 'OFF-BOARDING TO:'.$respName.'<br>'.$respMsgTable;
        MailController::OffboardingEmployeeOverdueTasks($respName, $email, $respMsgTable);
        $adminMsgTable .= "<hr/>";
		 echo 'OFF-BOARDING TO:ADMIN<br>'.$adminMsgTable;
        MailController::OffboardingAdminOverdueTasks($adminMsgTable);
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
		  if (empty($data)) {echo 'No overdue credentialings';return;}
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
        foreach ($employees as $empl) {
			  sleep(0.5);
            $email = $empl["email"];
            $name = $empl["name"];
			  $adminMsgTable .='<br>';
            if (!empty($empl["overdue"])) {
                $respOverdueMsgTable = "<hr/>";
				
                foreach ($empl["overdue"] as $task) {
					
                    $task["ecDueDate"] = val::convDate(MYSQL_DATE_TIME,USER_DATE,$task["ecDueDate"]);
					 
                    $respOverdueMsgTable .= "{$task['cName']}, Due Date: {$task['ecDueDate']}<br/>";
                    $adminMsgTable .= "{$name}, {$task['cName']}, Due Date: {$task['ecDueDate']}<br/>";
                }
                $respOverdueMsgTable .= "<hr/>";
            } else {
                $respOverdueMsgTable = "";
            }

            if (!empty($empl["soon"])) {
                $respMsgTable = "<hr/>";
                foreach ($empl["soon"] as $task) {
                    $task["ecDueDate"] = val::convDate(MYSQL_DATE_TIME,USER_DATE,$task["ecDueDate"]);
					   $respMsgTable .= "{$task['cName']}, Due Date: {$task['ecDueDate']}<br/>";
                }
                $respMsgTable .= "<hr/>";
            } else {
                $respMsgTable = "";
            }
			  //echo "<br> ".$count++." TO ".$name.' '.$email."<br>".$respOverdueMsgTable.'<br><br>'.$respMsgTable.'<hr>';
            MailController::CredentialingEmployeeOverdueTasks($name, $email, $respOverdueMsgTable, $respMsgTable);
        }


        if (!empty($adminMsgTable)) {
            $adminMsgTable = "<hr/>" . $adminMsgTable . "<hr/>";
			 // echo "<br>TO ADMIN<br>".$adminMsgTable;
            MailController::CredentialingAdminOverdueTasks($adminMsgTable);
        }
    }

    final static function TitleAndMetaTags()
    {

    }
}
