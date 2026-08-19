<?php
/** The CompanyOffice model */
class CompaniesModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"companies", "key"=>"comID", "pfx"=>"com", "controller"=>"CompaniesController");
    static $mainTblFields = array("comName", "comShortName","comActive","comCompanyIDAnual","comCompanyIDHourly");
    static $fldLbl = array( "comCompanyIDAnual"=>"Company ID Anual","comCompanyIDHourly"=>"Company ID Hourly",
	"comID"=>"ID", "comName"=>"Company Name", "comShortName"=>"Company Short Name", "comActive"=>"Active", "comAdded"=>"Added", "comUpdated"=>"Last changed");
    static $postOverrided = array();
	
	public static function GetActiveCompanies(){
		 $q = "SELECT * FROM  companies WHERE comActive='Yes' ORDER BY comShortName ASC";
		 $res = db::PrepareLoad($q);
		 if($res === false) $res=array();
		 $data=array();
		 foreach ($res as $r) {
            $data[$r['comID']]= htmlspecialchars($r['comShortName']);
        }
		return $data; 
	}
	
	
	public static function GetActiveCompaniesLong(){
		$qry='SELECT * FROM companies WHERE comActive = "Yes" ORDER BY comName ASC';
		$res = db::PrepareLoad($qry);
		if($res === false) $res=array();
		$companies=array();
		foreach ($res as $k=>$r){
			$companies[$r['comID']]=$r['comName'];
		}
		return $companies;
	}
	/**********/
    
    private static function validateName($name = "comName"){
        val::required($name,"Please fill out the ". strtolower(self::$fldLbl[$name]).".");
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::maxLength($name, 150);
        val::unique($name, self::$dt["tbl"], self::$dt["key"], CompaniesController::$id);
    }
	
	 private static function validateShortName($name = "comShortName"){
        val::required($name,"Please fill out the ". strtolower(self::$fldLbl[$name]).".");
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::maxLength($name, 150);
        val::unique($name, self::$dt["tbl"], self::$dt["key"], CompaniesController::$id);
    }
	
    private static function validateActive($active ,$name = "comActive"){
        val::required($name,"Please select ". strtolower(self::$fldLbl[$name])." status.", "select");
        val::validValue($name, $active);
    }
    final static function DetailsValidate($type, &$lists){
        $lists["active"] = self::$active;
        val::init($type);
        self::validateName();
		self::validateShortName();
		self::validateName('comCompanyIDAnual');
		self::validateName('comCompanyIDHourly');
		
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
           // $optionalAddons[self::$dt["key"]."edit"] = self::$dt["key"]." AS `".self::$dt["key"]."edit`";
			$optionalAddons[self::$dt["key"]."edit"] = self::$dt["key"]." AS `".self::$dt["key"]."`";
            $optionalAddons[self::$dt["pfx"]."AddedFormatted"] = "DATE_FORMAT(`".self::$dt["pfx"]."Added`,'".GLOBAL_MYSQL_DATETIME."') AS `".self::$dt["pfx"]."AddedFormatted`";
            $optionalAddons[self::$dt["pfx"]."UpdatedFormatted"] = "DATE_FORMAT(`".self::$dt["pfx"]."Updated`,'".GLOBAL_MYSQL_DATETIME."') AS `".self::$dt["pfx"]."UpdatedFormatted`";
        $addons = array();
        
        foreach(self::$dataAddons as $k){
            $addons[] = (in_array($k, array_keys($optionalAddons)) ? $optionalAddons[$k] : die(__METHOD__.": Incorrect addon request call (".$k.")"));
        }
		
        $str = str_replace("*", (empty($addons) ? "*" : ("*,\n".implode(",\n",$addons))), $q);
		
		return $str;
    }
    
    static function AllIdNameArr(){
		
        $data = self::LoadKeyValueArrFromTable(self::$dt["tbl"], self::$dt["key"], self::$dt["pfx"]."Name");
		$data= val::sortArray($data);
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
