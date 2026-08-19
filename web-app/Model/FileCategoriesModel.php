<?php

/** The General model */
class FileCategoriesModel extends AppModel
{

    static $dataAddons = array(), 
	 $dt = array("tbl" => "fcategories", "key" => "fcID", "pfx" => "fc", "controller" => "FileCategoriesController");
    static $mainTblFields = array("fcName", "fcImportant", "fcActive");
    static $fldLbl = array( "fcID"=>"ID", "fcName"=>"Name", "fcImportant" => "Important", "fcActive"=>"Active", "fcResCount"=>"Files",
                            "fcAdded"=>"Added", "fcUpdated"=>"Last changed");
    static $postOverrided = array();
	
	static function getAllCatsExceptCurrent()
    {
		 $id=$_GET['id'];
        $query = "SELECT * FROM fcategories WHERE fcID<>$id AND fcActive='Yes' ORDER BY fcName ASC";
        $res_arr = self::PrepareLoad($query);
        return $res_arr;
    }
	
	static function getFilesInCategory()
    {
		 $id=$_GET['id'];
        $query = "SELECT * FROM fcfilesbyuser WHERE fcfbuFileCatID=$id";
        $res_arr = self::PrepareLoad($query);
        return $res_arr;
    }
      
    private static function validateName($name = "fcName"){
        val::required($name,"Please fill out the ". strtolower(self::$fldLbl[$name]).".");
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::maxLength($name, 150);

        val::unique($name, self::$dt["tbl"], self::$dt["key"], FileCategoriesController::$id);
    }

    final static function DetailsValidate($type, &$lists){
        $lists["active"] = self::$active;
        val::init($type);
        self::validateName();
        if(!isset(FileCategoriesController::$id) && val::$runat==="server"){ 
		//Fix the MySql problem with more than 1 timestamp
            self::$mainTblFields[] = self::$dt["pfx"]."Added";
            self::$postOverrided[self::$dt["pfx"]."Added"] = "NOW()";
            self::$postOverridedSkipEscape[] = self::$dt["pfx"]."Added";
            
            self::$mainTblFields[] = self::$dt["pfx"]."Updated";
            self::$postOverrided[self::$dt["pfx"]."Updated"] = "NOW()";
            self::$postOverridedSkipEscape[] = self::$dt["pfx"]."Updated";
        }
        val::throwServerRunError();
    }


    static function query($conditions = '', array $orderby = array('fcAdded DESC'), $limit = 0){
       /**** 
		$query = "SELECT *, 
                        (select count(fclID) from files_cats_link
                        left join resources
                        on resID = fclRID
                        where fclRCID=fcID and rcActive='Yes') as fcResCount 
                  FROM " . self::$dt["tbl"];
		*****/		  
		  $query = "SELECT *, 
                        (select count(fcfbuID) from fcfilesbyuser WHERE fcfbuFileCatID=fcID ) as fcResCount 
                  FROM " . self::$dt["tbl"] ." WHERE fcActive='Yes' ";

        $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);
      
        return $q;
    }


    static function AllIdNameArr(){
        $data = self::data();
        $result = [];
        foreach ($data as $item) {
            $result[$item["rcID"]] = htmlspecialchars($item["rcName"]);
        }
        return $result;
    }


    public static function AllActiveKeyName()
    {
        $data = self::data("fcActive='Yes'", ["fcImportant" => "ASC", "fcName" => "ASC"]);
        $result = [];
        foreach ($data as $item) {
            $result[$item["rcID"]] = htmlspecialchars($item["rcName"]);
        }
        return $result;
    }

    public static function Resources($fcID) 
    {
        $data = ResourcesModel::data("fesID in (select fclRID from files_cats_link where fclRCID=".$fcID.") and resActive='Yes'", ["resImportant" => "ASC", "resTitle" => "ASC"]);        
        if(($data === false) || (empty($data))) {
            $data = [];
        }        

        foreach ($data as &$item) {
            $item["resTitle"] = htmlspecialchars($item["resTitle"]);
            $item["resDescription"] = nl2br(htmlspecialchars($item["resDescription"]));
        }

        return $data;
    }

    public static function AllActiveData()
    {
        $data = self::data("fcActive='Yes'", ["fcImportant" => "ASC", "fcName" => "ASC"]);        
        if($data === false) {
            $data = [];
        }
        foreach ($data as &$item) {
            $item["fcName"] = htmlspecialchars($item["fcName"]);
            // $item["resources"] = self::Resources($item["rcID"]);
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
		
            $title = (filter_has_var(INPUT_GET, "id") ? 
			    "Editing File Category: " . $C::$data[self::$dt["pfx"]."Name"] 
				: 
				 "Add New File Category");//.$C::$pg[$C::$rq]["title"]);
        }
        return array($title, $links);
    }

    static function ItemDeleteAllowed(){        
        return true;
    }
}