<?php

class MailController
{

    public static function OnboardingTaskNewResponsibleEmployee($otID)
    {

        $task = OnboardingModel::dataById($otID);
        $empl = EmployeeModel::dataById($task["otAFID"]);
        if (empty($empl)) {
            return;
        }

        $subject = "New onboarding task assigned to you";
        $msg_text = "
Dear {$empl['afFirstName']},

A new onboarding task has been assigned to you:
\"{$task['otDescription']}\", Time frame:  {$task['otTimeFrameNum']} {$task['otTimeFrameDim']}." . $GLOBALS['SYSTEM_MAILS_SIGNATURE'];

        $mail = $empl['afEmail'];
        $msg = nl2br($msg_text);
        cf::sendMail($mail, $subject, $msg, $GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS']);
    }

    public static function OnboardingTaskRemoveResponsibleEmployee($oldAFID, $otID)
    {

        $task = OnboardingModel::dataById($otID);
        $empl = EmployeeModel::dataById($oldAFID);
        if (empty($empl)) {
            return;
        }

        $subject = "Onboarding task no longer assigned to you";
        $msg_text = "
Dear {$empl['afFirstName']},

The onboarding task \"{$task['otDescription']}\" with time frame: {$task['otTimeFrameNum']} {$task['otTimeFrameDim']} is no longer assigned to you." . $GLOBALS['SYSTEM_MAILS_SIGNATURE'];

        $mail = $empl['afEmail'];
        $msg = nl2br($msg_text);
        cf::sendMail($mail, $subject, $msg, $GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS']);
    }

    public static function OnboardingTaskNewTimeFrame($otID)
    {

        $task = OnboardingModel::dataById($otID);
        $empl = EmployeeModel::dataById($task["otAFID"]);
        if (empty($empl)) {
            return;
        }

        $subject = "Onboarding task's time frame modified";
        $msg_text = "
Dear {$empl['afFirstName']},

The time frame for the onboarding task \"{$task['otDescription']}\" has been changed to: {$task['otTimeFrameNum']} {$task['otTimeFrameDim']}." 
. $GLOBALS['SYSTEM_MAILS_SIGNATURE'];

        $mail = $empl['afEmail'];
        $msg = nl2br($msg_text);
        cf::sendMail($mail, $subject, $msg, $GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS']);
    }

    public static function OnboardingEmployeePositionChanged($afID, $respAFID)
    {
        $empl = EmployeeModel::dataById($afID);
        $respEmpl = EmployeeModel::dataById($respAFID);


        if (empty($empl)) {
            return;
        }
        if (empty($respEmpl)) {
            return;
        }

        $subject = "Employee position changed, new onboarding task needed";
        $msg_text = "
Dear {$respEmpl['afFirstName']},

An employee's position has been changed - {$empl['afFirstName']} {$empl['afLastName']}, new position: {$empl['afHiredPosition']}. 
Some onboarding tasks are needed for the new one. Please <a href='" . WP . "profile'>sign in to your profile</a> to complete them. 
Thank you!" . $GLOBALS['SYSTEM_MAILS_SIGNATURE'];

        $mail = $respEmpl['afEmail'];
        $msg = nl2br($msg_text);
        cf::sendMail($mail, $subject, $msg, $GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS']);

    }

    public static function OnboardingNewEmployee($afID, $respAFID)
    {
        $empl = EmployeeModel::dataById($afID);
        $respEmpl = EmployeeModel::dataById($respAFID);


        if (empty($empl)) {
            return;
        }
        if (empty($respEmpl)) {
            return;
        }

        $subject = "Onboarding Task Required";
        $msg_text = "
Dear {$respEmpl['afFirstName']},

There are onboarding tasks for you to complete. 
Please login to your Employee Area, click Onboarding on the left menu and go to \"Tasks to do\".

Thank you!
" . $GLOBALS['SYSTEM_MAILS_SIGNATURE'];



        $mail = $respEmpl['afEmail'];
        $msg = nl2br($msg_text);
        cf::sendMail($mail, $subject, $msg, $GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS']);

    }
	
	
	public static function OffboardingNewEmployee($afID, $respAFID)
    {
        $empl = EmployeeModel::dataById($afID);
        $respEmpl = EmployeeModel::dataById($respAFID);


        if (empty($empl)) {
            return;
        }
        if (empty($respEmpl)) {
            return;
        }

        $subject = "Offboarding Task Required";
        $msg_text = "
Dear {$respEmpl['afFirstName']},

There are offboarding tasks for you to complete. 
Please login to your Employee Area, click Offboarding on the left menu and go to \"Tasks to do\".

Thank you!
" . $GLOBALS['SYSTEM_MAILS_SIGNATURE'];



        $mail = $respEmpl['afEmail'];
        $msg = nl2br($msg_text);
        cf::sendMail($mail, $subject, $msg, $GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS']);

    }


    public static function OnboardingEmployeeOverdueTasks($name, $email, $tasksTable)
    {

        $subject = "Overdue onboarding tasks";
        $msg_text = "
Dear {$name},

The following onboarding tasks you're responsible for are OVERDUE. Please <a href='" . WP . "profile'>sign in to your profile</a> to complete them ASAP. 
Thank you!" . trim($tasksTable) . $GLOBALS['SYSTEM_MAILS_SIGNATURE'];

        $mail = $email;
        $msg = nl2br($msg_text);
        
        // print $mail.'<br/>';
        // print $subject.'<br/>';
        // print $msg.'<hr/>';

        cf::sendMail($mail, $subject, $msg, $GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS']);
    }
	
	
	 public static function OffboardingEmployeeOverdueTasks($name, $email, $tasksTable)
    {

        $subject = "Overdue offboarding tasks";
        $msg_text = "
Dear {$name},

The following offboarding tasks you're responsible for are OVERDUE. Please <a href='" . WP . "profile'>sign in to your profile</a> to complete them ASAP. 
Thank you!" . trim($tasksTable) . $GLOBALS['SYSTEM_MAILS_SIGNATURE'];

        $mail = $email;
        $msg = nl2br($msg_text);
        
        // print $mail.'<br/>';
        // print $subject.'<br/>';
        // print $msg.'<hr/>';

        cf::sendMail($mail, $subject, $msg, $GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS']);
    }

    public static function OnboardingAdminOverdueTasks($tasksTable)
    {
        $subject = "Overdue onboarding tasks";
        $msg_text = "The following onboarding tasks are overdue:" . trim($tasksTable) . $GLOBALS['SYSTEM_MAILS_SIGNATURE'];
        $msg = nl2br($msg_text);
        
        // print $mail.'<br/>';
        // print $subject.'<br/>';
        // print $msg.'<hr/>';
        
        cf::sendMail(implode(',',explode("\n",$GLOBALS['ADMIN_MAILS'])), $subject, $msg, $GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS']);
        //cf::sendMail(DEV_EMAIL_ADMIN, $subject, $msg, DEV_EMAIL_MAILER_NAME, DEV_EMAIL_MAILER_ADDRESS);
    }
	
	 public static function OffboardingAdminOverdueTasks($tasksTable)
    {
        $subject = "Overdue offboarding tasks";
        $msg_text = "The following offboarding tasks are overdue:" . trim($tasksTable) . $GLOBALS['SYSTEM_MAILS_SIGNATURE'];
        $msg = nl2br($msg_text);
        
        // print $mail.'<br/>';
        // print $subject.'<br/>';
        // print $msg.'<hr/>';
        
        cf::sendMail(implode(',',explode("\n",$GLOBALS['ADMIN_MAILS'])), $subject, $msg, $GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS']);
        //cf::sendMail(DEV_EMAIL_ADMIN, $subject, $msg, DEV_EMAIL_MAILER_NAME, DEV_EMAIL_MAILER_ADDRESS);
    }

    public static function CredentialingEmployeeOverdueTasks($name, $email, $overdueTasksTable, $tasksTable)
    {
        
        $subject = "Credentials overdue or required soon";
        $msg_text = "Dear {$name},";
        /*
        if (!empty($overdueTasksTable)) {
            
            $msg_text .= "
    
            The following credentialing is OVERDUE: " . trim($overdueTasksTable);
            
            
        }
        if (!empty($tasksTable)) {
            $msg_text .= "
    
            The following credential file(s) are required within the next 3 months at latest: " . trim($tasksTable);
        }
        */

        $msg_text .= "\r\n\r\n";
        $msg_text .= "There are credentialing files you need to upload. Please login to your account and click CREDENTIALING menu to view your credentialing files.";
        $msg_text .= "\r\n\r\n";
        $msg_text .= $GLOBALS['SYSTEM_MAILS_SIGNATURE'];
        file_put_contents("test.txt", $msg_text);
        
        // $msg_text .= "\nPlease login to upload those files.".$GLOBALS['SYSTEM_MAILS_SIGNATURE'];

        $mail = $email;
        $msg = nl2br($msg_text);

        // print $mail.'<br/>';
        // print $subject.'<br/>';
        // print $msg.'<hr/>';
        
        // ZLATEV:
        
        //if ($mail == "info@metavisia.com")
        //{
            cf::sendMail($mail, $subject, $msg, $GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS']);
        //}
    }

    public static function CredentialingAdminOverdueTasks($tasksTable) {
        $subject = " Credentialing overdue ";
        $msg_text = " The following credentialing is overdue: ".trim($tasksTable).$GLOBALS['SYSTEM_MAILS_SIGNATURE'];
        $msg = nl2br($msg_text);

        // print EMAIL_ADMIN .', '.DEV_EMAIL_ADMIN.'<br/>';
        // print $subject.'<br/>';
        // print $msg.'<hr/>';        
echo '<br>>>>>>>>>>>'.$GLOBALS['EMAIL_MAILER_NAME'].' =========== '.$GLOBALS['EMAIL_MAILER_ADDRESS'].'<br>'.implode(',',explode("\n",$GLOBALS['ADMIN_MAILS'])).'<hr>';

        // ZLATEV:
        
        cf::sendMail(str_replace("\r", "", implode(',',explode("\n",$GLOBALS['ADMIN_MAILS']))), $subject, $msg, $GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS']);
        
        //file_put_contents("test.txt", $recipients);
        //cf::sendMail('emails@metavisia.com', $subject, $msg, $GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS']);
        //cf::sendMail(DEV_EMAIL_ADMIN, $subject, $msg, DEV_EMAIL_MAILER_NAME, DEV_EMAIL_MAILER_ADDRESS);
    }
	
	public static function SendMailWithAttchments($from,$from_mail,$mailto,$subject,$message,$file){
		
		$content = file_get_contents($file);
		$content = chunk_split(base64_encode($content));

		// a random hash will be necessary to send mixed content
		$separator = md5(time());

		// carriage return type (RFC)
		$eol = "\r\n";

		// main header (multipart mandatory)
		$headers = "From: {$from} <{$from_mail}>" . $eol;
		$headers .= "MIME-Version: 1.0" . $eol;
		$headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
		$headers .= "Content-Transfer-Encoding: 7bit" . $eol;
		$headers .= "This is a MIME encoded message." . $eol;

		// message
		$body = "--" . $separator . $eol;
		$body .= "Content-Type: text/plain; charset=\"UTF-8\"" . $eol;
		$body .= "Content-Transfer-Encoding: 8bit" . $eol;
		$body .= $message . $eol;

		// attachment
		$body .= "--" . $separator . $eol;
		$body .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
		$body .= "Content-Transfer-Encoding: base64" . $eol;
		$body .= "Content-Disposition: attachment" . $eol;
		$body .= $content . $eol;
		$body .= "--" . $separator . "--";

		//SEND Mail
		$res=array();
		if (mail($mailto, $subject, $body, $headers)) {
			$res['result']="OK"; // or use booleans here
		} else {
			$res['result']="FAIL"; 
			$res['message'] = error_get_last();
		}
	}
}