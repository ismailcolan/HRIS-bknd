<?php

/** The Orders model */
class ProfileModel extends ApplyFormModel
{
    static $fldLbl = array(
			"afID"=>"ID","afJID" => "Job", "afPID" => "Position", "afFirstName" => "First name",
			"afLastName" => "Last name", "afCellPhone" => "Cell phone", 
			"afEmcContact" => "Emergency Contact",
			"afHomePhone" => "Home phone", "afEmail" => "E-mail", "afSkills" => "Skills", "afBirthday" => "Date of birth", "afDesiredSalary" => "Desired salary", "afActive" => "Status", "afAdded" => "Applied",
			"afRating" => "Rating", "afRID" => "Rating", "afComments" => "Notes", "afIP" => "IP Address", "afBio" => "Application CV", "afCurrentCV"=>"Current CV", "afDID" => "Department", "afCOID" => "Company office", "afRMID" => "Manager",
			"afStartDate" => "Start date","afDateHired" => "Date hired", "afSalary" => "Salary", "afSalaryType" => "Salary type", "afCompany" => "Company", "afHiredForPID" => "Position",
			"afDepartment" => "Department", "afOffice" => "Company office", "afManager" => "Manager", "afShortResume"=>"Short resume", "afSpecialSkills"=>"Skills and Hobbies",
			"afEmplNumber"=>"Employee Number",
			'afGender'=>'Gender',
			"afMaritalStatus"=>"Marital Status",
			"afAddressLine1"=>"Addres Line 1",
			"afAddressLine2"=>"Addres Line 2",
			"afZip"=>"Zip Code",
			"afCity"=>"City","afNoPayrollJA"=>"No Payroll (Joinder Agreement)",
			"afState"=>"State",
			"afEmergencyPhone"=>"Emergency Phone","afEmergencyRelationship"=>"Emergency Relationship",
			"afSSNmb"=>'SSN',
			'afNYSMedLicenseNum' => 'NYS Medical License Number',
			'afNYSMedicalLicenseExpDate' => 'NYS Medical license exp. date',
			'afNPINum' => 'NPI Number',
			'afCAQHNum' => 'CAQH Number',
			'afWorkPhone'=>'Work phone', 'afWorkPhoneExt'=>'Work phone ext.'
		);
    public static $sessionVarName = "webapp-login-token";

    private static function validatePassword($name = "pass")
    {
        val::required($name, "Please fill out the password.");
        val::minLength($name, 8, "The password must be at least 8 characters long and must include a number.");
        val::maxLength($name, 30);
        val::regExp($name, "JSVAL_RX_PASSWORD", "The password must be at least 8 characters long and must include a number.");
    }

    private static function validateRePassword($name = "passRe")
    {
        val::equals($name, substr($name, 0, -2), "Password confirmation does not match.");
    }

    private static function validateOldPassword($name = "passOld")
    {
        if (!filter_has_var(INPUT_POST, $name)) return true;
        if (val::required($name, "Please fill out the password.")) {
            $pass = filter_input(INPUT_POST, $name, FILTER_SANITIZE_STRING);
            $token = cf::decrypt(AccessModel::$usr["afToken"], $pass);
            if (empty($token)) {
                val::setError("passOld", "Invalid password!");
            }
        }
    }

    static function ValidateChangePassword($oldPass = null, $checkConfirm = true)
    {
        val::init("server");
        if (!empty($oldPass)) {
            self::validateOldPassword($oldPass);
        }
        self::validatePassword();
        self::validateRePassword();

        if($checkConfirm) {
            if (!filter_has_var(INPUT_POST, "confirm")) {
                val::sestError("confirm", "Please confirm");
            }
        }

        return empty(form::$error);
    }

    public static function changePassword($checkOldPass = true, $checkConfirm = true, $usrID = null)
    {
        if($checkOldPass) {
            $check = self::ValidateChangePassword("passOld", $checkConfirm);
        } else {
            $check = self::ValidateChangePassword(null, $checkConfirm);
        }

        if ($check) {
            $new = filter_input(INPUT_POST, "pass", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
            if($usrID === null) {
                $usrID = AppModel::$usr["afID"];
            }
            self::savePassword($usrID, $new);
            msg::set("The password has been successfully changed!", "success");
            return true;
        }
        return false;
    }

    public static function signOut()
    {
        if (array_key_exists(self::$sessionVarName, $_SESSION)) {
            unset($_SESSION[self::$sessionVarName]);
        }
    }

    public static function SetUserSession($user) {
		
        AppModel::$usr = $user;
		 $d = new DateTime();
		 $_d = $d->format("Y-m-d H:i:s");

        token::SetIdAndDate(self::$sessionVarName, "session", $user[self::$dt["key"]], $_d);
		  $_d= new DateTime();
        $q = "INSERT INTO logs (`lafID`, `lData`,`lTimestamp`,`lLoggedAs`) VALUES (%d,'%s','%s',%d)";
		 if (isset($_SESSION['webapp-admin-token'])) $admin=1; else $admin=0;
        $q = sprintf($q, APPMODEL::$usr["afID"], cf::visitorBrowserData(null),
				$_d->format(MYSQL_DATE_TIME), $admin);
        
		
		
		
		  db::SQL($q);

        WebappAdminModel::ManagerLoginProcess();
    }

    public static function checkUserCredentials()
    {
		if (!db::SwitchToReal()){
			msg::put("Not valid customer, username or password.", "error");
			return false;
		}
        val::init("server");
        val::required("email", "Please enter valid email.");
        val::regExp("email", "JSVAL_RX_EMAIL", "Please enter a valid e-mail address.");
        val::required("pass", "Please fill out the password.");
        val::minLength("pass", 8, "Password is too short");

        val::throwServerRunError();

        $email = db::EscapeStr(filter_input(INPUT_POST, "email", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
        $pass = filter_input(INPUT_POST, "pass", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		
		 $today = new DateTime();
         $q = 'select * from apply_forms where afEmail="' . $email . '" and afActive="Hired"
		  AND (afDateLeaving IS NULL  OR afDateLeaving > "'.$today->format('Y-m-d').'")';
	    
        $user = db::PrepareLoadSingleRow($q);
		
        if ($user) {
            $decrypt = cf::decrypt($user["afToken"], $pass);

            if (!empty($decrypt)) {
                self::SetUserSession($user);
                return true;
            }
        }

        msg::put("The login data you entered is incorrect.", "error");
        return false;
    }

    private static function setLoginToken($usr = false)
    {
		 $d=new DateTime();
		 $_d = $d->format("Y-m-d H:i:s");
		 
        token::SetIdAndDate(self::$sessionVarName, "session", $usr[self::$dt["key"]], $_d);
        self::setLoginLog((int)$usr[self::$dt["key"]]);
    }

    private static function setLoginLog($eID)
    {
        /*
        $data["`eslEID`"] = (int)$eID;
        $data["`eslInfo`"] = self::EscapeStr(cf::visitorBrowserData(cf::$rq["complete"]), true);

        $q["signlog"] = "INSERT INTO `" . self::$dt["tbl"] . "_signin_log`(" . implode(", ", array_keys($data)) . ") VALUES (" . implode(", ", $data) . ");";

        self::Save($q);
        */
    }

    public static function checkPasswordKey()
    {
        if (filter_has_var(INPUT_GET, "key")) {
            $key = filter_input(INPUT_GET, "key", FILTER_SANITIZE_STRING);

            if (!LOCAL and strlen($key) != 50) {
                return false;
            }

            $q = 'select afID from apply_forms where afActive="Hired" and afKey=' . db::EscapeStr($key, true);
            return db::PrepareLoadSingleCellValue($q);
        } else {
            return false;
        }
    }

    public static function setNewPassword()
    {
        $afID = self::checkPasswordKey();
        if ($afID) {
            if (self::ValidateChangePassword()) {
                $key = filter_input(INPUT_GET, "key", FILTER_SANITIZE_STRING);
                $pass = filter_input(INPUT_POST, "pass", FILTER_SANITIZE_STRING);
                self::savePassword($afID, $pass);
                return true;
            }
        } else {
            msg::put("Invalid key", "error");
        }

        return false;
    }

    public static function savePassword($afID, $pass)
    {
        $mysql = db::$mysqli;
        $token = cf::encrypt(WEBAPP_PHP_SECRET_KEY, $pass);


        $q = 'update apply_forms set afToken=?, afKey=null where afID=?';
        $slmt = $mysql->prepare($q);
        $slmt->bind_param("ss", $token, $afID);
        $slmt->send_long_data(0, $token);
        $slmt->execute();
    }

    public static function DataIfLoggedIn()
    {
        $usrId = token::GetIdAndReset(self::$sessionVarName, "session", "PT5M");
        if($usrId !== false) {
            $user = ProfileModel::dataById($usrId);
        } else {
            $user = null;
        }

        return $user;
    }
	
	
    public static function sendRecoverPasswordMail()
    {	
		
		if (! db::SwitchToReal()){
			msg::set("Error: Invalid customer name: {$_POST['customer']}.", "error");
			return false;	
			
		}


        val::init("server");
        if (!val::required("email", "Please fill out the email address")) {
            return false;
        }
        if (!val::regExp("email", "JSVAL_RX_EMAIL", "Invalid e-mail address")) {
            return false;
        }

        $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_STRING);
        if (empty($email)) {
            $email = filter_input(INPUT_POST, "afEmail", FILTER_SANITIZE_STRING);
        }
		
		
        $q = 'select * from apply_forms where afEmail="' . $email . '" and afActive="Hired"';
        $user = db::PrepareLoadSingleRow($q);
		 
        if ($user) {
            $key = self::generateKey();
            $url = WP . "login-recover?key=" . $key.'&db='.$_SESSION['DBNAME'];

            $msg = str_replace("{--Recovery-lnk--}", form::linkOut($url, ltrim($url, "//")), nl2br(MAIL_PASSWORD_MSG));

            if (cf::sendMail($user["afEmail"], MAIL_PASSWORD_SUBJ, $msg, $GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS'])) {
                msg::set("An email has been sent to <strong>" . $user["afEmail"] . "</strong> with a link to set a new password.", "success");

                $q = 'update apply_forms set afKey="' . $key . '" where afID=' . $user["afID"];
                db::SQL($q);
				
                return true;
				
            } else {
                msg::set(("Error: The mail message with recovery link cannot be sent to: <strong>" . $user["afEmail"] . "</strong>."), "error");
				
            }
        } else {

            msg::put("Invalid e-mail addres (" . $email . ").", "error");
        }
        return false;
    }

    public static function generateKey()
    {
        $key = cf::getRandomString(50);
        return $key;
    }

    public static function SaveProfile()
    {
        val::init("server");

        ApplyFormModel::validateMail();
       // ApplyFormModel::validateCellPhone();
       // ApplyFormModel::validateHomePhone();
        ApplyFormModel::validateNameNoReq("afEmcContact");
        ApplyFormModel::validateBio("afCurrentCV", false);
		
		
		ApplyFormModel::validateSSN("afSSNmb");
		
		 ApplyFormModel::validateAddressLine1("afAddressLine1");
		 ApplyFormModel::validateCity("afCity");
		 
		 ApplyFormModel::validateSelect("afMaritalStatus"); 
		 ApplyFormModel::validateSelect("afGender");
		 ApplyFormModel::validateSelect("afState");
		 //ApplyFormModel::validateSelect("afEmergencyRelationship");
		 
		 ApplyFormModel::validateZIP("afZip");
		
        ApplyFormModel::validateHomePhone("afCellPhone");
		 ApplyFormModel::validateHomePhone("afEmergencyPhone");
		 ApplyFormModel::validateHomePhone("afWorkPhone");

        ApplyFormModel::validateHomePhone("afHomePhone");
		
		 
        val::throwServerRunError();

        $q = "select count(afID) cnt from apply_forms where afEmail='%s' and afID<>%d and afActive='Hired'";
        $q = sprintf($q, filter_input(INPUT_POST, "afEmail", FILTER_SANITIZE_EMAIL), AccessModel::$usr["afID"]);
        $res = db::PrepareLoadSingleCellValue($q);

        if ($res > 0) {
            val::setError("afEmail", "This email address is already in use. Please use another email address.");
            val::throwServerRunError();
        }

       self::$mainTblFields[] = self::$dt["pfx"] . "Updated";
       self::$postOverrided[self::$dt["pfx"] . "Updated"] = "NOW()";
	   
		self::$postOverrided['afBirthday']=val::convDate(USER_DATE,MYSQL_DATE_TIME,$_POST['afBirthday']);
		self::$postOverrided['afStartDate']=val::convDate(USER_DATE,MYSQL_DATE,$_POST['afStartDate']);
		self::$postOverrided['afDateHired']=val::convDate(USER_DATE,MYSQL_DATE,$_POST['afDateHired']);
		if (isset($_POST['afDateLeaving']))
		self::$postOverrided['afDateLeaving']=val::convDate(USER_DATE,MYSQL_DATE,$_POST['afDateLeaving']);				
		//<<<<<PV27.7
		
		
		
        self::$postOverridedSkipEscape[] = self::$dt["pfx"] . "Updated";

		
        db::Save(ApplyFormModel::saveQueries(), AccessModel::$usr["afID"], "apply_forms", false);

        $id = AccessModel::$usr["afID"];

        CVModel::processUploadedFiles($id);

       
		$fileName = str_replace("{---LiveID---}", $id, ApplyFormModel::IMG_FILENAME);
        if (filter_has_var(INPUT_POST, "photoCmd") && 
			(filter_input(INPUT_POST, "photoCmd", FILTER_SANITIZE_STRING) === "remove")) {
           
			$photoFile = "" . ApplyFormModel::IMG_PATH . $fileName;
            if (file_exists($photoFile)) {
                unlink($photoFile);
            }
        }
		img::move_images_from_temp("afPhoto", true, $fileName);
    }
}
