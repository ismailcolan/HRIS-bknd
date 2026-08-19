<?php
/** The CompanyOffice model */
class ManagerModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"reporting_managers", "key"=>"rmID", "pfx"=>"rm", "controller"=>"ManagerController");
    static $mainTblFields = array("rmName", "rmEmail", "rmActive");
    static $fldLbl = array( "rmID"=>"ID", "rmName"=>"Name", "rmEmail"=>"E-mail", "rmActive"=>"Active", "rmAdded"=>"Added", "rmUpdated"=>"Last changed");
    static $postOverrided = array();

    private static function validateName($name = "rmName"){
        val::required($name,"Please fill out the ". strtolower(self::$fldLbl[$name]).".");
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::maxLength($name, 150);
        val::unique($name, self::$dt["tbl"], self::$dt["key"], ManagerController::$id);
    }
    private static function validateEmail($name = "rmEmail"){
        val::required($name,"Please fill out the ". strtolower(self::$fldLbl[$name]).".");
        val::regExp($name, "JSVAL_RX_EMAIL", "Please enter a valid e-mail address.");
        val::unique($name, self::$dt["tbl"], self::$dt["key"], ManagerController::$id);
    }
    private static function validateActive($active ,$name = "rmActive"){
        val::required($name,"Please select ". strtolower(self::$fldLbl[$name])." status.", "select");
        val::validValue($name, $active);
    }
    final static function DetailsValidate($type, &$lists){
        $lists["active"] = self::$active;
        val::init($type);
        self::validateName();
        self::validateEmail();
        self::validateActive(array_keys($lists["active"]));
        if(!isset(CompanyOfficeController::$id) && val::$runat==="server"){ //Fix the MySql problem with more than 1 timestamp
            self::$mainTblFields[] = self::$dt["pfx"]."Added";
            self::$postOverrided[self::$dt["pfx"]."Added"] = "NOW()";
            self::$postOverridedSkipEscape[] = self::$dt["pfx"]."Added";
            
            self::$mainTblFields[] = self::$dt["pfx"]."Updated";
            self::$postOverrided[self::$dt["pfx"]."Updated"] = "NOW()";
            self::$postOverridedSkipEscape[] = self::$dt["pfx"]."Updated";
        }
        val::throwServerRunError();
    }


    static function query($conditions = '', array $orderby = array(), $limit = 0){
        $query = "SELECT * FROM " . self::$dt["tbl"];
        $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);

        $optionalAddons = array();
            $optionalAddons[self::$dt["key"]."edit"] = self::$dt["key"]." AS `".self::$dt["key"]."edit`";
            $optionalAddons[self::$dt["pfx"]."AddedFormatted"] = "DATE_FORMAT(`".self::$dt["pfx"]."Added`,'".GLOBAL_MYSQL_DATETIME."') AS `".self::$dt["pfx"]."AddedFormatted`";
            $optionalAddons[self::$dt["pfx"]."UpdatedFormatted"] = "DATE_FORMAT(`".self::$dt["pfx"]."Updated`,'".GLOBAL_MYSQL_DATETIME."') AS `".self::$dt["pfx"]."UpdatedFormatted`";
        $addons = array();
        
        foreach(self::$dataAddons as $k){
            $addons[] = (in_array($k, array_keys($optionalAddons)) ? $optionalAddons[$k] : die(__METHOD__.": Incorrect addon request call (".$k.")"));
        }
		
        return str_replace("*", (empty($addons) ? "*" : ("*,\n".implode(",\n",$addons))), $q);
    }
    
    static function AllIdNameArr(){
        $data = self::LoadKeyValueArrFromTable(self::$dt["tbl"], self::$dt["key"], self::$dt["pfx"]."Name");
        foreach ($data as &$item) {
            $item = htmlspecialchars($item);
        }
        return $data;
    }

    static function dataById($id=0, $allLangs=false){
        $data = parent::dataById($id, $allLangs);
        if(empty($data)) {
            return false;
        }

        $data[self::$dt["pfx"]."Name"] = htmlspecialchars($data[self::$dt["pfx"]."Name"]);

        return $data;
    }


    static function adminPageTitleWithLinks($links = null){
        $C = self::$dt["controller"];

        if(in_array($C::$rq, array("list", "inactive-list"))){
            $title = $C::$pg[$C::$rq]["title"];
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else if($C::$rq==="summary"){
            $title = (filter_has_var(INPUT_GET, "id") ? $C::$data[self::$dt["pfx"]."Name"] : cf::end(__METHOD__));
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else {
            $title = (filter_has_var(INPUT_GET, "id") ? "Editing: " . $C::$data[self::$dt["pfx"]."Name"] : "Add ".$C::$pg[$C::$rq]["title"]);
        }
        return array($title, $links);
    }

    static function ItemDeleteAllowed(){
        return false;
    }
}
