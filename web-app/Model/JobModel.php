<?php
/** The CompanyOffice model */
class JobModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"jobs", "key"=>"jID", "pfx"=>"j", "controller"=>"JobController");
    static $mainTblFields = array("jName", "jPID", "jEndDate", "jDescription", "jActive");
    static $fldLbl = array( "jID"=>"ID", "jName"=>"Name", "jPID"=>"Position", "jPosition"=>"Position", "jEndDate"=>"End date", "jDescription"=>"Description",
                            "jActive"=>"Active", "jAdded"=>"Added", "jUpdated"=>"Last changed");
    static $postOverrided = array();
    
    private static function validateName($name = "jName"){
        val::required($name,"Please fill out the ". strtolower(self::$fldLbl[$name]).".");
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::maxLength($name, 150);
    }
    private static function validatePosition($positions ,$name = "jPID"){
        val::required($name,"Please select position.", "select");
        val::validValue($name, $positions);
    }
    private static function validateEndDate($name = "jEndDate"){
        if(val::validDate($name)) {
            $test_date = filter_input(INPUT_POST, $name);
            if(!empty($test_date)) {
                $date = DateTime::createFromFormat(USER_DATE, $test_date);
                if($date < new DateTime()) {
                    val::setError($name, "End date must be equal or greater than the current date");
                    return false;
                }
            }
        }
        return true;
    }
    private static function validateActive($active ,$name = "jActive"){
        val::required($name,"Please select ". strtolower(self::$fldLbl[$name])." status.", "select");
        val::validValue($name, $active);
    }
    final static function DetailsValidate($type, &$lists){
        $lists["active"] = self::$active;
        val::init($type);
        self::validateName();
        self::validateEndDate();
        self::validatePosition(array_keys(PositionModel::AllActiveKeyName()));
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
        $query = "SELECT *, (select pName from positions where pID = jPID) jPosition FROM " . self::$dt["tbl"];
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
        return self::LoadKeyValueArrFromTable(self::$dt["tbl"], self::$dt["key"], self::$dt["pfx"]."Name");
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

    static function dataById($id=0, $allLangs=false){
        $data = parent::dataById($id, $allLangs);
        if(empty($data)) {
            return false;
        }

        if(empty($data[self::$dt["pfx"]."EndDate"])) {
            $data[self::$dt["pfx"]."EndDate"] = "";
        } else {
            if($data[self::$dt["pfx"]."EndDate"] === "0000-00-00") {
                $data[self::$dt["pfx"]."EndDate"] = "";
            } else {
                $data[self::$dt["pfx"]."EndDate"] = 
						val::convDate(MYSQL_DATE,USER_DATE,$data[self::$dt["pfx"]."EndDate"]);
				  //date(USER_DATE, strtotime($data[self::$dt["pfx"]."EndDate"]));<<<
            }
        }

        $data[self::$dt["pfx"]."Name"] = htmlspecialchars($data[self::$dt["pfx"]."Name"]);

        return $data;
    }

    static function postVarsOverride()
    {
        $endDate = filter_input(INPUT_POST, self::$dt["pfx"]."EndDate");
		
        if(!empty($endDate)) {
            self::$postOverrided[self::$dt["pfx"]."EndDate"] = 
			      val::convDate(USER_DATE,MYSQL_DATE,$endDate);
			    //date("Y-m-d", strtotime($endDate));<<<
        } else {
            self::$postOverrided[self::$dt["pfx"]."EndDate"] = "0000-00-00";
        }
    }

    static function getAllActiveJobs() {
        $q = "SELECT * FROM jobs 
              LEFT JOIN positions ON pID = jPID
              WHERE jActive = 'Yes' AND (jEndDate IS NULL OR jEndDate >= CURRENT_DATE OR jEndDate = '0000-00-00') AND pActive = 'Yes' ORDER BY jID DESC";

        $data = db::PrepareLoad($q);

        if($data === false) { $data = []; }
        else {
            foreach ($data as &$item) {
                $item[self::$dt["pfx"]."Name"] = htmlspecialchars($item[self::$dt["pfx"]."Name"]);
                $item["pName"] = htmlspecialchars($item["pName"]);
            }
        }

        return $data;
    }
}
