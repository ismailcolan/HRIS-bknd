<?php
/** The CompanyOffice model */
class CompanyOfficeModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"company_offices", "key"=>"coID", "pfx"=>"co", "controller"=>"CompanyOfficeController");
    static $mainTblFields = array("coName", "coAddress", "coActive"); /*"coID", , "coAdded", "coUpdated"*/
    static $fldLbl = array( "coID"=>"ID", "coName"=>"Name", "coAddress"=>"Address", "coActive"=>"Active", "coEmplCount"=>"Employees",
                            "coAdded"=>"Added", "coUpdated"=>"Last changed");
    static $postOverrided = array();
    
    private static function validateName($name = "coName"){
        val::required($name,"Please fill out the ". strtolower(self::$fldLbl[$name]).".");
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::maxLength($name, 150);
        val::unique($name, self::$dt["tbl"], self::$dt["key"], CompanyOfficeController::$id);
    }
    private static function validateAddress($name = "coAddress"){
        val::required($name,"Please fill out the ". strtolower(self::$fldLbl[$name]).".");
        val::maxLength($name, 300);
        if(val::$runat==="server" && strlen(filter_input(INPUT_POST, $name))===0){
            self::$postOverrided[$name] = "Null";
            self::$postOverridedSkipEscape[] = $name;
        }
    }
    private static function validateActive($active ,$name = "coActive"){
        val::required($name,"Please select ". strtolower(self::$fldLbl[$name])." status.", "select");
        val::validValue($name, $active);
    }
    final static function DetailsValidate($type, &$lists){
        $lists["active"] = self::$active;
        val::init($type);
        self::validateName();
        self::validateAddress();
//        self::validateActive(array_keys($lists["active"]));
        if(!isset(CompanyOfficeController::$id) && val::$runat==="server"){ //Fix the MySql problem with more than 1 timestamp
            self::$mainTblFields[] = "coAdded";
            self::$postOverrided[self::$dt["pfx"]."Added"] = "NOW()";
            self::$postOverridedSkipEscape[] = self::$dt["pfx"]."Added";
            
            self::$mainTblFields[] = self::$dt["pfx"]."Updated";
            self::$postOverrided[self::$dt["pfx"]."Updated"] = "NOW()";
            self::$postOverridedSkipEscape[] = self::$dt["pfx"]."Updated";
        }
        val::throwServerRunError();
    }


    static function query($conditions = '', array $orderby = array(), $limit = 0){
        $query = "SELECT *, 
                        (select count(afID) from apply_forms where afActive='Hired' and afCOID=coID) as coEmplCount 
                  FROM " . self::$dt["tbl"];
        $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);

        $optionalAddons = array();
            $optionalAddons[self::$dt["pfx"]."EmployeesIdNameJson"] = "(SELECT CONCAT(\"{\",GROUP_CONCAT((SELECT CONCAT(\"\\\"\",`eID`,\"\\\"\",\":\\\"\", `eName`,\"\\\"\") FROM `employees` WHERE FIND_IN_SET(`".self::$dt["key"]."`, `eAssignedCOIDs`)) SEPARATOR \",\"),\"}\")) AS `".self::$dt["pfx"]."EmployeesIdNameJson`";
            $optionalAddons[self::$dt["key"]."edit"] = self::$dt["key"]." AS `".self::$dt["key"]."edit`";
            $optionalAddons[self::$dt["pfx"]."AddedFormatted"] = "DATE_FORMAT(`".self::$dt["pfx"]."Added`,'".GLOBAL_MYSQL_DATETIME."') AS `".self::$dt["pfx"]."AddedFormatted`";
            $optionalAddons[self::$dt["pfx"]."UpdatedFormatted"] = "DATE_FORMAT(`".self::$dt["pfx"]."Updated`,'".GLOBAL_MYSQL_DATETIME."') AS `".self::$dt["pfx"]."UpdatedFormatted`";
        $addons = array();
        
        foreach(self::$dataAddons as $k){
            $addons[] = (in_array($k, array_keys($optionalAddons)) ? $optionalAddons[$k] : die(__METHOD__.": Incorrect addon request call (".$k.")"));
        }

        $q = str_replace("*", (empty($addons) ? "*" : ("*,\n".implode(",\n",$addons))), $q);
        return $q;
    }


    static function AllIdNameArr(){
//        $data = self::LoadKeyValueArrFromTable(self::$dt["tbl"], self::$dt["key"], self::$dt["pfx"]."Name");
        $data = self::data();
        $result = [];
        foreach ($data as $item) {
            $result[$item["coID"]] = htmlspecialchars($item["coName"]).($item['coActive'] == "No" ? ' (inactive)' : '') ;
        }
        return $result;
    }


    public static function AllActiveKeyName($includeID = null)
    {
        if(is_array($includeID)) {
            $includeID = implode(",", $includeID);
        }
        $data = db::LoadKeyValueArrFromTable(self::query((!empty($includeID) ? " coID in (".$includeID.")" : "")), self::$dt["key"], self::$dt["pfx"] . "Name");
        if ($data === false) {
            $data = [];
        } else {
            foreach ($data as &$item) {
                $item = htmlspecialchars($item);
            }
        }
		$data = val::sortArray($data);
        return $data;
    }


    static function dataById($id=0, $allLangs=false){
        $data = parent::dataById($id, $allLangs);
        if(empty($data)) {
            return false;
        }

        $data[self::$dt["pfx"]."Name"] = htmlspecialchars($data[self::$dt["pfx"]."Name"]);
        $data[self::$dt["pfx"]."Address"] = htmlspecialchars($data[self::$dt["pfx"]."Address"]);

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
        return true;
    }
}
