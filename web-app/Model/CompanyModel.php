<?php
/** The CompanyOffice model */
class CompanyModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"companies", "key"=>"comID", "pfx"=>"com");
    static $mainTblFields = array("comShortName", "comName", "comActive");
    static $fldLbl = array( "comID"=>"ID", "comName"=>"Name", "comShortName"=>"Short name", "coActive"=>"Active");
    static $postOverrided = array();
    static $companies = null;


    static function query($conditions = '', array $orderby = array(), $limit = 0){
        $query = "SELECT *                         
                  FROM " . self::$dt["tbl"];
        $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);

        $optionalAddons = array();
        $addons = array();
        
        foreach(self::$dataAddons as $k){
            $addons[] = (in_array($k, array_keys($optionalAddons)) ? $optionalAddons[$k] : die(__METHOD__.": Incorrect addon request call (".$k.")"));
        }

        $q = str_replace("*", (empty($addons) ? "*" : ("*,\n".implode(",\n",$addons))), $q);
        return $q;
    }

    public static function AllActiveKeyName($includeID = null)
    {
        if(is_array($includeID)) {
            $includeID = implode(",", $includeID);
        }
        $data = db::LoadKeyValueArrFromTable(self::query((!empty($includeID) ? " comID in (".$includeID.")" : "")), self::$dt["key"], self::$dt["pfx"] . "Name");
        if ($data === false) {
            $data = [];
        } else {
            foreach ($data as &$item) {
                $item = htmlspecialchars($item);
            }
        }
        return $data;
    }

    public static function AllActiveNameName($includeID = null)
    {
        $final = [];
        if(is_array($includeID)) {
            $includeID = implode(",", $includeID);
        }
        $data = db::LoadKeyValueArrFromTable(self::query((!empty($includeID) ? " comID in (".$includeID.")" : "")), self::$dt["pfx"] . "Name", self::$dt["pfx"] . "Name");
       		
		if ($data === false) {
        } else {
            foreach ($data as $item) {
                $final[$item] = htmlspecialchars($item);
            }
        }
        return $final;
    }
	
	public static function AllActiveIDName($includeID = null)
    {
        $final = [];
        if(is_array($includeID)) {
            $includeID = implode(",", $includeID);
        }
        $data = db::LoadKeyValueArrFromTable(self::query((!empty($includeID) ? " comID in (".$includeID.")" : "")), self::$dt["pfx"] . "ID", self::$dt["pfx"] . "Name");
		
       
		if ($data === false) {
        } else {
            foreach ($data as $k=>$item) {
                $final[$k] = htmlspecialchars($item);
            }
        }
        return $final;
    }



    static function dataById($id=0, $allLangs=false){
        $data = parent::dataById($id, $allLangs);
        if(empty($data)) {
            return false;
        }

        $data[self::$dt["pfx"]."Name"] = htmlspecialchars($data[self::$dt["pfx"]."Name"]);
        $data[self::$dt["pfx"]."ShortName"] = htmlspecialchars($data[self::$dt["pfx"]."ShortName"]);

        return $data;
    }

    static function GetShortName($name) {
        if(empty(self::$companies)) {
            self::$companies = self::data('comActive="Yes"');
        }

        $result = '';
        foreach (self::$companies as $item) {
            if($item["comName"] === $name) {
                $result = $item["comShortName"];
                break;
            }
        }
        return $result;
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
	
	public static function GetActiveCompanies(){
		 $q = "SELECT * FROM  companies WHERE comActive='Yes' ORDER BY comShortName ASC";
		 $res = db::PrepareLoad($q);
		 if($res === false) $res=array();
		 $data=array();
		 foreach ($res as $r) {
            $data[$r['dID']]= htmlspecialchars($r['comShortName']);
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
}
