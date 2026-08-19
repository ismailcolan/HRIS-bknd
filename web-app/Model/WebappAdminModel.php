<?php



/** The webapp admin model */

class WebappAdminModel extends AppModel

{



    static $dataAddons = array(), $dt = array("tbl" => "webapp_admins", "key" => "waaID", "pfx" => "waa", "controller" => "WebappAdminController");

    static $mainTblFields = array("waaUsername", "waaEmail", "waaRole");

    static $postOverrided = array();

    static $AdminRolesAccessible = array("Editor", "Admin");

    protected static $sessionVarName = "webapp-admin-token";

    const SIGN_LOG_KEEP_MAX = 20;



    private static function validateUsername($name = "waaUsername")

    {

        val::required($name, "Enter your username.");

        val::minLength($name, 4, "Username is too short.");

        val::regExp($name, "/^[A-Za-z0-9\-_@.\s]{4,30}$/");

//        val::maxLength($name, 30);

        if ($name !== "user") {

            val::unique($name, self::$dt["tbl"], self::$dt["key"], WebappAdminController::$id, "The selected username is already used. Please use unique value.");

        }

    }



    private static function validateEmail($name = "waaEmail")

    {

        val::required($name, "Please fill out the email.");

        val::minLength($name, 2, "Please add at least 2 characters.");

        val::maxLength($name, 150);

        val::regExp($name, "JSVAL_RX_EMAIL", "Please enter a valid e-mail address.");

    }



    private static function validateRole($roles, $name = "waaRole")

    {

        val::required($name, "Please select an role.", "select");

        val::validValue($name, $roles);

    }



    private static function validateOldPassword($type, $name = "OldPass")

    {

        val::required($name, "Please fill out the password.");

        val::minLength($name, 6, "Please add at least 6 characters.");

        val::maxLength($name, 30);

        if ($type === "server") {

            $salt = db::PrepareLoadSingleCellValue("SELECT waatSalt FROM webapp_admins_tokens WHERE waatID=" . (int)AppModel::$admin["waaID"]);

            $token = cf::hashTokenByPassAndSalt(filter_input(INPUT_POST, $name), $salt);

            $cond = "(SELECT waatToken FROM webapp_admins_tokens WHERE waatID = waaID) = AES_ENCRYPT(\"" . $token . "\", \"" . WEBAPP_SQL_SECRET_KEY . "\")";

            $adminData = self::data($cond);

            //WebappAdminController::$id = $adminData[0]["waaID"];

            WebappAdminController::$data["token"] = cf::hashTokenByPassAndSalt(filter_input(INPUT_POST, "waaPass"), $salt);

            if ((int)$adminData[0]["waaID"] === 0) {

                val::setError($name, "The old password is incorrect.");

            }

        }

    }



    private static function validatePassword($name = "waaPass")

    {

        val::required($name, "Please fill out the password.");

        val::minLength($name, 6, "Password is too short.");

        val::maxLength($name, 30);

    }



    private static function validateRePassword($name = "waaPassRe")

    {

        val::required($name, "Confirm your password.");

        val::minLength($name, 6, "Password confirmation is too short.");

        val::equals($name, substr($name, 0, -2), "Password confirmation does not match.");

        val::maxLength($name, 30);

    }



    static function ValidateChangePassword($type, &$oldPass = false)

    {

        val::init($type);

		

        $oldPass = (bool)((int)WebappAdminController::$id === (int)AppModel::$admin["waaID"]);

		if(isset($_SESSION['NO_ASK_OLD_PASS'])) $oldPass = false;

        //cf::vd($oldPass, WebappAdminController::$id);

        if ($oldPass) {

            self::validateOldPassword($type);

        } else {

            if (isset(WebappAdminController::$id)) {

                $salt = db::PrepareLoadSingleCellValue("SELECT waatSalt FROM webapp_admins_tokens WHERE waatID=" . (int)WebappAdminController::$id);

                WebappAdminController::$data["token"] = cf::hashTokenByPassAndSalt(filter_input(INPUT_POST, "waaPass"), $salt);

            }



        }



        self::validatePassword();

        self::validateRePassword();

        val::throwServerRunError();

    }



    static function ValidateDetails($type, &$lists)

    {

        val::init($type);

        $lists["roles"] = self::getEnumFieldValues(self::$dt["tbl"], "waaRole");

        self::validateUsername();

        self::validateEmail();

        self::validateRole(array_keys($lists["roles"]));

        if (!isset(WebappAdminController::$id)) {

            self::ValidateChangePassword($type);

        }

        val::throwServerRunError();

    }



    // static function saveQueries($qListSubmitted = array())

    // {

    //     $qList = parent::saveQueries($qListSubmitted);

    //     if (!isset(WebappAdminController::$id)) {

    //         $emailForSalt = filter_input(INPUT_POST, "waaEmail", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

    //         $salt = cf::saltAnWord($emailForSalt);

    //         $token = cf::hashTokenByPassAndSalt(filter_input(INPUT_POST, "waaPass"), $salt);

    //         $qList["token"] = "INSERT INTO `webapp_admins_tokens` (`waatID`, `waatSalt`, `waatToken`) VALUES ({---MainID---}, " . db::EscapeStr($salt, true) . ",AES_ENCRYPT(\"" . $token . "\", \"" . WEBAPP_SQL_SECRET_KEY . "\"));";

    //     }

    //     return $qList;

    // }

    static function saveQueries($qListSubmitted = array())
{
    $qList = parent::saveQueries($qListSubmitted);

    if (!isset(WebappAdminController::$id)) {
        $emailForSalt = filter_input(INPUT_POST, "waaEmail", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        $salt  = cf::saltAnWord($emailForSalt);
        $token = cf::hashTokenByPassAndSalt(filter_input(INPUT_POST, "waaPass"), $salt);
        $qList["token"] = "INSERT INTO `webapp_admins_tokens` (`waatID`, `waatSalt`, `waatToken`) 
                           VALUES ({---MainID---}, " . db::EscapeStr($salt) . ", 
                           AES_ENCRYPT(\"" . $token . "\", \"" . WEBAPP_SQL_SECRET_KEY . "\"));";
    }
    return $qList;
}



    static function ValidateLogin($type)

    {

        val::init($type);

        self::validateUsername("user");

        self::validatePassword("pass");

        val::throwServerRunError();

    }



    static function query($conditions = '', array $orderby = array(), $limit = 0)

    {

        // $query = "SELECT * FROM " . self::$dt["tbl"];

        // $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);

        $query = "SELECT wa.*, r.role_name FROM " . self::$dt["tbl"] . " wa
              LEFT JOIN roles r ON r.id = wa.role_id";
    $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);


        $optionalAddons = array();

        $optionalAddons["waaRegDateFormatted"] = "DATE_FORMAT(waaRegDate, '" . GLOBAL_MYSQL_DATETIME . "') AS `waaRegDateFormatted`";

        $addons = array();



        foreach (self::$dataAddons as $k) {

            $addons[] = (in_array($k, array_keys($optionalAddons)) ? $optionalAddons[$k] : die(__METHOD__ . ": Incorrect addon request call: " . $k));

        }



        // return str_replace("*", (empty($addons) ? "*" : ("*,\n" . implode(",\n", $addons))), $q);

        return $q;

    }



    /** Description: The method checks do the reuest is submitted from our server - a session var

     *  is used for validation. Redirect trigerred on validation failure */

    /*static function SetOrValidateRequestSrc(){

        if(!isset($_SESSION["login-src-check"])){

            $_SESSION["login-src-check"] = rand();

        } else {

            $checkString = cf::hashTokenByPassAndSalt($_SESSION["login-src-check"], "login-page-check");

            unset($_SESSION["login-src-check"]);

            

            $validateSource = filter_input(INPUT_GET, "q");



            if(empty($validateSource) || !cf::timingSafeCompare($checkString, $validateSource)){

                cf::Redirect(ADMIN."login");

                die();

            } else {

                //resetting the session

                self::SetOrValidateRequestSrc();

            }

        }

    }*/

    static function LoginProcess()

    {

        $user = filter_input(INPUT_POST, "user", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

        $pass = filter_input(INPUT_POST, "pass", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);



        // $salt = db::PrepareLoadSingleCellValue("SELECT waatSalt FROM webapp_admins_tokens WHERE waatID=(SELECT waaID FROM webapp_admins WHERE waaAFID is null and waaUsername=" . self::EscapeStr($user, true) . ")");
        $salt = db::PrepareLoadSingleCellValue("SELECT waatSalt FROM webapp_admins_tokens WHERE waatID=(SELECT waaID FROM webapp_admins WHERE waaUsername=" . self::EscapeStr($user, true) . ")");



//        if($salt !== false) {

//            $sql = "SELECT *, NOW() as waaLastSignInDate FROM webapp_admins LEFT JOIN apply_forms ON afID=waaAFID WHERE waaUsername = '" . $user . "'";

//            $usr = self::PrepareLoadSingleRow($sql);

//            if($usr) {

//                $decrypt = cf::decrypt($usr["afToken"], $pass);

//                if (empty($decrypt)) {

//                    $usr = false;

//                } else {

//                    AppModel::$usr = $usr;

//                    token::SetIdAndDate(ProfileModel::$sessionVarName, "session", $usr["afID"], date("Y-m-d H:i:s"));

//                }

//            }

//        } else {

        $token = cf::hashTokenByPassAndSalt($pass, $salt);

        $sql = "SELECT *, NOW() as waaLastSignInDate FROM webapp_admins

                    WHERE waaUsername = \"" . $user . "\"

                    AND (SELECT waatToken FROM webapp_admins_tokens WHERE waatID = waaID) = AES_ENCRYPT(\"" . $token . "\", \"" . WEBAPP_SQL_SECRET_KEY . "\")";

        $usr = self::PrepareLoadSingleRow($sql);

		//echo $sql;exit;

		//cf::pr($usr);exit;

//        }





        if ($usr) {

            self::setLoginToken($usr);

            return true;

        } else {

            return false;

        }

    }



    static function ManagerLoginProcess()

    {

        $user = db::EscapeStr(filter_input(INPUT_POST, "email", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));

        $pass = filter_input(INPUT_POST, "pass", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);



        $salt = db::PrepareLoadSingleCellValue("SELECT waatSalt FROM webapp_admins_tokens WHERE waatID=(SELECT waaID FROM webapp_admins WHERE waaUsername=" . self::EscapeStr($user, true) . ")");



        if ($salt !== false) {

            $sql = "SELECT *, NOW() as waaLastSignInDate FROM webapp_admins LEFT JOIN apply_forms ON afID=waaAFID WHERE waaUsername = '" . $user . "'";

            $usr = self::PrepareLoadSingleRow($sql);

            if ($usr) {

                $decrypt = cf::decrypt($usr["afToken"], $pass);

                if (empty($decrypt)) {

                    $usr = false;

                } else {

                    AppModel::$usr = $usr;

					    $d=new DateTime();$_d = $d->format("Y-m-d H:i:s");

                    token::SetIdAndDate(ProfileModel::$sessionVarName, "session", $usr["afID"], $_d);

                }

            }

        } else {

            $token = cf::hashTokenByPassAndSalt($pass, $salt);

            $sql = "SELECT *, NOW() as waaLastSignInDate FROM webapp_admins

                    WHERE waaUsername = \"" . $user . "\"

                    AND (SELECT waatToken FROM webapp_admins_tokens WHERE waatID = waaID) = AES_ENCRYPT(\"" . $token . "\", \"" . WEBAPP_SQL_SECRET_KEY . "\")";

            $usr = self::PrepareLoadSingleRow($sql);

			

        }





        if ($usr) {

            self::setLoginToken($usr);

            return true;

        } else {

            return false;

        }

    }




//replace colan
    // public static function setLoginToken($usr = false)

    // {

    //     self::SQL("UPDATE webapp_admins SET waaLastSignInDate = NOW() WHERE waaID = " . $usr["waaID"]);

	// 	  $d=new DateTime();$_d = $d->format("Y-m-d H:i:s");

    //     token::SetIdAndDate(self::$sessionVarName, "session", $usr["waaID"], $_d);



    //     self::setLoginLog((int)$usr["waaID"]);

    //     if (isset($usr['role']) && !empty($usr['role'])) {
    //         $_SESSION['role'] = $usr['role'];
    //     } elseif (isset($usr['waaRole']) && !empty($usr['waaRole'])) {
    //         $_SESSION['role'] = strtolower($usr['waaRole']);
    //     } else {
    //         $_SESSION['role'] = 'admin';
    //     }

        
    //     /*$loginSessionData = array("u"=>abs($usr["waaID"]));



    //     $date = new DateTime();

    //     $loginSessionData["time"] = $date->format("Y-m-d H:i:s");

        

    //     if (version_compare(phpversion(), '5.5.0', '>=')) {

    //         $loginSessionData["token"] = password_hash($usr["waaID"].$loginSessionData["time"], PASSWORD_DEFAULT);

    //     } else {

    //         $ivSize = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);

    //         $token = mcrypt_create_iv($ivSize, MCRYPT_DEV_RANDOM);

    //         $tokenStr = hash("sha512",$token);

    //         //$token = $usr["wauID"] . ':' . $tokenStr;

    //         $mac = hash_hmac('sha256', ($usr["waaID"] . $loginSessionData["time"] . ':' . $tokenStr), WEBAPP_PHP_SECRET_KEY);

    //         $loginSessionData["token"] = ($tokenStr . ':' . $mac);

    //     }

        

    //     $_SESSION[self::$sessionVarName] = $loginSessionData;

    //     */

    // }


    //new colan
    public static function setLoginToken($usr = false)
{
    self::SQL("UPDATE webapp_admins SET waaLastSignInDate = NOW() WHERE waaID = " . $usr["waaID"]);
    $d = new DateTime(); $_d = $d->format("Y-m-d H:i:s");
    token::SetIdAndDate(self::$sessionVarName, "session", $usr["waaID"], $_d);
    self::setLoginLog((int)$usr["waaID"]);

    $roleRow = db::PrepareLoadSingleRow(
        "SELECT r.id, r.role_name 
         FROM roles r 
         INNER JOIN webapp_admins wa ON wa.role_id = r.id 
         WHERE wa.waaID = " . (int)$usr["waaID"]
    );

    $_SESSION['role']           = $roleRow ? strtolower($roleRow['role_name']) : 'admin';
    $_SESSION['role_id']        = $roleRow ? (int)$roleRow['id'] : 1;
    $_SESSION['role_name']      = $roleRow ? $roleRow['role_name'] : 'Admin';
    $_SESSION['admin_id']       = (int)$usr["waaID"];
    $_SESSION['admin_username'] = $usr["waaUsername"];

    // Store linked employee ID if this admin is an employee
    if (!empty($usr['waaAFID'])) {
        $_SESSION['employee_id'] = (int)$usr['waaAFID'];
    }
}


    private static function setLoginLog($adminID)

    {

        $data["`waaslAdminID`"] = (int)$adminID;

        $data["`waaslInfo`"] = self::EscapeStr(cf::visitorBrowserData(cf::$rq["complete"]), true);



        $q["signlog"] = "INSERT INTO `" . self::$dt["tbl"] . "_signin_log`(" . implode(", ", array_keys($data)) . ") VALUES (" . implode(", ", $data) . ");";



        /*if(!is_null(AttorneyController::$id)){*/

        $q["signlog-limit"] = "DELETE FROM `" . self::$dt["tbl"] . "_signin_log` WHERE `waaslAdminID`=" . $data["`waaslAdminID`"] . " AND `waaslSignTime` < (SELECT MIN(`waaslSignTime`) FROM (SELECT `waaslSignTime` FROM `" . self::$dt["tbl"] . "_signin_log` WHERE `waaslAdminID`=" . $data["`waaslAdminID`"] . " ORDER BY `waaslSignTime` DESC limit 0," . self::SIGN_LOG_KEEP_MAX . ") LastProtected);";

        /*}*/

        self::Save($q);

    }



    static function checkLoginTokens($userRole = false)

    {   

        $adminId = token::GetIdAndReset(self::$sessionVarName, "session", "PT5M");

		

		//cf::pr($_SESSION);

        if ($adminId != false && isset($_SESSION['DBNAME'])) {

			

			$ret=WebappAdminModel::dataById($adminId);

		}

		else { 

			$ret=null;

		}

	

	   return $ret;

	   //********/

        /*

        if(isset($_SESSION[self::$sessionVarName]) && !empty($_SESSION[self::$sessionVarName])) {



            

            $revalidateIntervalMinutes = 1;



            $datetime_current = new DateTime();

            $datetime_session = new DateTime($_SESSION[self::$sessionVarName]["time"]);

            //$datetime_session->add(new DateInterval("PT".$revalidateIntervalMinutes."M"));

            $datetime_session->add(new DateInterval("PT10S"));

var_dump($datetime_current > $datetime_session);

            if($datetime_current > $datetime_session) {

                $time_to_check = $_SESSION[self::$sessionVarName]["time"];

                $sessionData = array("u"=>abs($_SESSION[self::$sessionVarName]["u"]));

                $sessionData["time"] = $datetime_current->format("Y-m-d H:i:s");            

                

                if (version_compare(phpversion(), '5.5.0', '>=')) {

                    $validate_res = (password_verify( $sessionData["u"].$time_to_check , $_SESSION[self::$sessionVarName]["token"] ) ? $_SESSION[self::$sessionVarName]["u"] : false );

                    $sessionData["token"] = password_hash($validate_res.$datetime_current->format("Y-m-d H:i:s"), PASSWORD_DEFAULT);

                } else {

                    $tokenStr = $_SESSION[self::$sessionVarName]["token"] . ":" .$_SESSION[self::$sessionVarName]["u"];

                    list ($token, $mac, $uid) = explode(':', $tokenStr);

                    

                    $checkMac = hash_hmac('sha256', $uid . $time_to_check. ':' . $token, WEBAPP_PHP_SECRET_KEY);

                    

                    $validate_res = cf::timingSafeCompare($checkMac, $mac) ?  $uid : false;

                    if($validate_res){

                        $ivSize = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);

                        $token = mcrypt_create_iv($ivSize, MCRYPT_DEV_RANDOM);

                        $tokenStr = hash("sha512",$token);

                        //$token = $usr["wauID"] . ':' . $tokenStr;

                        $mac = hash_hmac('sha256', ($sessionData["u"] . $datetime_current->format("Y-m-d H:i:s") . ':' . $tokenStr), WEBAPP_PHP_SECRET_KEY);

                        $sessionData["token"] = ($tokenStr . ':' . $mac);

                    }

                }

                if($validate_res){

                    $_SESSION[self::$sessionVarName]["token"] = $sessionData;

                    return WebAppAdminModel::dataAdminById($uid);

                } else {

                    unset($_SESSION[self::$sessionVarName]);

                }

            } else {

                return  WebAppAdminModel::dataAdminById($_SESSION[self::$sessionVarName]["u"]);

            }

        }

        return false;

         */

    }



    static function unsetLoginToken($userRole = false)

    {

        if (isset($_SESSION[self::$sessionVarName])) {

            unset($_SESSION[self::$sessionVarName]);

        }

    }



    /*static function BreadcrumbsAndTitle(){

        $ctr = self::$dt["controller"];

        if(is_null($ctr::$id)){

            $ctr::$pg["breadcrumbs-ending"][] = $ctr::$pg["title"] = ucfirst($ctr::$pg["items"]);

        } else {

            $ctr::$pg["breadcrumbs-ending"][$ctr::$pg["list"]["url"]] = ucfirst($ctr::$pg["items"]);

            $ctr::$pg["breadcrumbs-ending"][] = $ctr::$pg["title"] =  filter_has_var(INPUT_GET, "id")? WebappAdminController::$data["waaUsername"] : ("Create ".$ctr::$pg["item"]);

        }

    }*/



    static function adminPageTitleWithLinks($links = null)

    {

        $ctr = self::$dt["controller"];

        if ($ctr::$rq === "list") {

            $links[$ctr::$pg["details"]["url"]] = "Add " . strtolower($ctr::$pg["item"]);

            $title = $ctr::$pg[$ctr::$rq]["title"];

        } else if ($ctr::$rq === "details") {

            $title = (filter_has_var(INPUT_GET, "id") ? $ctr::$data["waaUsername"] : "Add " . $ctr::$pg[$ctr::$rq]["title"]);

        } else if ($ctr::$rq === "password") {

            $title = $ctr::$pg[$ctr::$rq]["title"];

        } else {

            $title = null;

        }

        return array($title, $links);

    }

}

