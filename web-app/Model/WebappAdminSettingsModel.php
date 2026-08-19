<?php
class WebappAdminSettingsModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"webapp_admin_settings", "key"=>"asKey", "pfx"=>"as", "controller"=>"WebappAdminSettingsController");
    static $mainTblFields = array("asValue");
    static $postOverrided = array();
    static $AdminRolesAccessible = array("Editor", "Admin");
    
    private static function validateSetting($name){
        switch($name){
            case "ORDER_SUBMISSION_NOTIFY_EMAILS":
                val::required($name,"Please fill out the password.");
                if(val::$runat==="server" && empty(form::$error)){ //On server validation and no errors before
                    $emailsString = trim(filter_input(INPUT_POST, $name));
                    $emailsWithSpacesOny = str_replace(array("\r\n", "\n", "\r", "\t", ",")," ", $emailsString);
                    //$emailsWithSpaceSeparator = preg_replace("/\s{2,}/", " ", $string);
                    $emailsWithComaSeparator = preg_replace("/\s{1,}/", ",", $emailsWithSpacesOny);
                    
                    $re = "/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,5})+$/"; //as per val::regExp($name, "JSVAL_RX_EMAIL", "Please enter valid email.");
                    foreach(($emails = array_unique(explode(",",$emailsWithComaSeparator))) as $email){
                        if (preg_match($re, $email) == 0) {
                            val::setError($name, "Invalid email: <strong>".$email."</strong>");
                            break;
                        }
                    }
                    if(empty(form::$error)){
                        WebappAdminSettingsController::$data["processed"][$name] = implode(",",$emails);
                    }
                }
                break;
            /*default: val::required($name);*/
        }
    }
    static function ValidateSettings($type){
        val::init($type);
        foreach(array_keys(WebappAdminSettingsController::$data) as $key){
            self::validateSetting($key);
        }
        val::throwServerRunError();
    }
    static function saveQueries($qList = array()) {
        $processed = WebappAdminSettingsController::$data["processed"];
        unset(WebappAdminSettingsController::$data["processed"]);
        
        foreach(array_keys(WebappAdminSettingsController::$data) as $key){
            $qList = "UPDATE `".self::$dt["tbl"]."` SET `asValue`=".self::EscapeStr($processed[$key], true)." WHERE `".self::$dt["key"]."`=".self::EscapeStr($key, true).";";
        }
        return $qList;
    }
    
    static function query($conditions = '', array $orderby = array(), $limit = 0){
        $query = "SELECT * FROM " . self::$dt["tbl"];
        $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);

        $optionalAddons = array();
            //$optionalAddons["waaRegDateFormatted"] = "DATE_FORMAT(waaRegDate, '".GLOBAL_MYSQL_DATETIME."') AS `waaRegDateFormatted`";
        $addons = array();
        
        foreach(self::$dataAddons as $k){
            $addons[] = (in_array($k, array_keys($optionalAddons)) ? $optionalAddons[$k] : die(__METHOD__.": Incorrect addon request call: ".$k));
        }
		
        return str_replace("*", (empty($addons) ? "*" : ("*,\n".implode(",\n",$addons))), $q);
    }
    /**Gets a row value (`asValue`) from the main table (`webapp_admin_settings`) by the key (`asKey`)
     * @param string $key 
     * @return string|null */
    static function dataByKey($key=null){
		$res = static::data(static::$dt["key"]."=".self::EscapeStr($key, true));
        
        if(!is_null($data = (count($res) !== 1 ? null : $res[0]))){
            $data = $data["asValue"];
        }
        
		return $data;
    }
    
    static function adminPageTitleWithLinks($links = null){
        $ctr = self::$dt["controller"];
        if($ctr::$rq==="settings"){
            $title = $ctr::$pg[$ctr::$rq]["title"]; 
        } else {
            $title = null;
        }
        return array($title, $links);
    }
}
