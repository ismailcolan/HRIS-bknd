<?php
/** The Page model */
class PageModel extends AppModel {
    
    /*static $dataAddons = array(), $dt = array("tbl"=>"llist_categories", "key"=>"lcID", "controller"=>"CategoryController");
    static $mainTblFields = array("lcName", "lcUrl", "lcRootCID", "lcNaviTitle", "lcProfileTitle", "lcProfileInListTitle", "lcRightSide", "lcPublic", "mainListShow");
    static $postOverrided = array();*/
    
    static  $navL = array("home"=>"Home"/*, "contact"=>"Contact"*/), 
            $navR = array(),
            $navFooter2Add = array(),
            $noNavPages = array(),
            
            $contactDataName2Lbl = array(   "name"=>"Your Name", "gender"=>"Male/Female", "email"=>"Your Email Address", 
                                            "age-category"=>"Age Category","nature-enquiry"=>"Nature of your Enquiry",
                                            "captcha"=>"Please enter the code", "comments"=>"Comments");
    
    private static function validateName($name = "name"){
        val::required($name,"Please fill out the name.");
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::maxLength($name, 255);
    }
    private static function validateGender($genders,$name = "gender"){
        val::required($name,"Please select gender.", "select");
        val::validValue($name, $genders);
    }
    private static function validateEmail($name = "email"){
        val::required($name,"Please fill out the name.");
        val::regExp($name, self::EMAIL_REGEXP, "Please use valid email.");
        val::maxLength($name, 255);
    }
    private static function validateAge($ages,$name = "age-category"){
        val::required($name,"Please select age category.", "select");
        val::validValue($name, $ages);
    }
    private static function validateNature($natures,$name = "nature-enquiry"){
        val::required($name,"Please select nature enquiry".$name.".", "select");
        val::validValue($name, $natures);
    }
    private static function validateCaptcha($name = "captcha"){
        val::required($name,"Please fill out the code.");
        val::minLength($name, 5);
        val::maxLength($name, 5);
        if(val::$runat==="server" && filter_input(INPUT_POST, $name)!==$_SESSION[captcha::SESSION_KEY]){
            val::setError($name, "Wrong protection code. Please type the new one.");
        }
    }


    final static function ContactFormValidate($type, &$lists){
        $lists["gender"] = array_combine(($g = array("Male","Female")),$g);
        $lists["age-category"] = array_combine(($a = array("20 or under","21-29","30-39","40-49","50-59","60 +")),$a);
        $lists["nature-enquiry"] = array_combine(($n = array("Anxiety Issue","Anger Issue","Youth Issue","Other Issue","Enquiry")),$n);
        val::init($type);
        self::validateName();
        self::validateGender($g);
        self::validateEmail();
        self::validateAge($a);
        self::validateNature($n);
        self::validateCaptcha();
        val::throwServerRunError();
    }
    final static function ContactFormSend($msg = null){
        $v = array("label"=>self::$contactDataName2Lbl);
        foreach(array_keys(self::$contactDataName2Lbl) as $f){
            if($f==="captcha"){ continue;}
            $v[$f] = filter_input(INPUT_POST, $f);
        }
        
        cf::buffer();
        require APP_HTML."process-contact-email--message-content.html";
        cf::buffer($msg);
        return cf::sendMail(EMAIL_CONTACTUS, ("Contact form message from ".SITE_NAME), $msg, $v["name"], $v["email"]);
    }

    final static function TransferAttorneysValidate($type){
        val::init($type);
        self::validateTransferCategory();
        self::validateTransferCategory("destination");
        if($type=="server"){
            if(filter_input(INPUT_POST,"source")==filter_input(INPUT_POST,"destination")){
                val::setError("destination", "Categories cannot be equal.");
            }
        }
        val::throwServerRunError();
    }


    static function query($conditions = '', array $orderby = array(), $limit = 0){
        $query = "SELECT * FROM " . self::$dt["tbl"];
        $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);

        $optionalAddons = array();
            $optionalAddons["lcPath"] = "IF(ISNULL(`lcRootCID`) ,`lcName`, CONCAT((SELECT `lcName` FROM `llist_categories` `a` WHERE `a`.`lcID`=`llist_categories`.`lcRootCID`), \" &nbsp; &raquo; &nbsp; \", `lcName`)) AS `lcPath`";
            $optionalAddons["lcRoot"] = "IF(ISNULL(`lcRootCID`) ,null, (SELECT `lcName` FROM `llist_categories` `a` WHERE `a`.`lcID`=`llist_categories`.`lcRootCID`)) AS `lcRoot`";
            $optionalAddons["lcUpdatedFormatted"] = "DATE_FORMAT(`lcUpdated`,'".GLOBAL_MYSQL_DATETIME."') AS `lcUpdatedFormatted`";
            $optionalAddons["lcAddedFormatted"] = "DATE_FORMAT(`lcAdded`,'".GLOBAL_MYSQL_DATETIME."') AS `lcAddedFormatted`";
            $optionalAddons["lcMetaDescrAttysCount"] = "(SELECT COUNT(*) FROM `llist_lawyers_categories` WHERE `llcCID`=`".self::$dt["key"]."`) AS `lcMetaDescrAttysCount`";
            $optionalAddons["lcAssignedCitiesCount"] = "(SELECT COUNT(*) FROM `llist_cities_categories` WHERE `lccCID`=`".self::$dt["key"]."`) AS `lcAssignedCitiesCount`";
            $optionalAddons["lcMainListShow"] = "IF(`lcRootCID` IS NULL OR `mainListShow`=1,\"Yes\",\"No\") AS `lcMainListShow`";
        $addons = array();
        
        foreach(self::$dataAddons as $k){
            $addons[] = (in_array($k, array_keys($optionalAddons)) ? $optionalAddons[$k] : die(__METHOD__.": Incorrect addon request call"));
        }
		
        return str_replace("*", (empty($addons) ? "*" : ("*,\n".implode(",\n",$addons))), $q);
    }
    
    static function DirectorySearchCategoriesJson(){
        $catsLimits = array();
        $catsLimits[] = "`lcPublic`=\"Yes\"";
        if(filter_has_var(INPUT_GET, "loc")){
            if(filter_has_var(INPUT_GET, "state")){
                $city = (int)trim(filter_input(INPUT_GET, "loc", FILTER_SANITIZE_NUMBER_INT));
                $catsLimits[] = "`".self::$dt["key"]."` IN (SELECT DISTINCT `lccCID` FROM `llist_cities_categories` WHERE `lccCTID`=".$city.")";
            } else {
                $state = (int)trim(filter_input(INPUT_GET, "loc", FILTER_SANITIZE_NUMBER_INT));
                $catsLimits[] = "`".self::$dt["key"]."` IN (SELECT DISTINCT `lccCID` FROM `llist_cities_categories` WHERE `lccCTID` IN (SELECT DISTINCT `ctID` FROM `llist_cities` WHERE `ctSID`=".$state."))";
            }
        }
        //$catsQ = str_replace("*", "`lcName` AS cat", self::query(implode(" AND ",$catsLimits)));
        $catsQ = str_replace("*", "`".self::$dt["key"]."` AS id, `lcName` AS cat, `lcUrl` AS slug", self::query(implode(" AND ",$catsLimits)));
        //cf::vdd($catsQ);
        
        if(($categories = self::PrepareLoad($catsQ))){
            die(json_encode($categories));
        } else { die(); };
        /*cf::vdd($categories, json_encode($categories));
        $cats=array();
        if(!empty($categories)){
            foreach($categories as $cat){
                $cats[]=$cat["cat"];
            }
        }
        die(json_encode($cats));*/
    }
    
    static function adminPageTitleWithLinks($links = null){
        $C = self::$dt["controller"];
        if($C::$rq==="list"){
            $title = $C::$pg[$C::$rq]["title"]; 
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else if($C::$rq==="summary"){
            $title = (filter_has_var(INPUT_GET, "id") ? $C::$data["lcName"] : cf::end(__METHOD__));
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else {
            $title = (filter_has_var(INPUT_GET, "id") ? $C::$data["lcName"] : "Add ".$C::$pg[$C::$rq]["title"]);
        }
        return array($title, $links);
    }
    static function ItemDeleteAllowed($SummaryRequestOrlcMetaDescrAttysCount){
        $lcMetaDescrAttysCount = $SummaryRequestOrlcMetaDescrAttysCount===true ?
                                 PageController::$data["lcMetaDescrAttysCount"] :
                                 $SummaryRequestOrlcMetaDescrAttysCount;
        return (bool)($lcMetaDescrAttysCount==="0");
    }
}
