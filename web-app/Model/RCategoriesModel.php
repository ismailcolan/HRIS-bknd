<?php
/** The RCategories model */
class RCategoriesModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"rcategories", "key"=>"rcID", "pfx"=>"rc", "controller"=>"RCategoriesController");
    static $mainTblFields = array("rcName", "rcImportant", "rcActive");
    static $fldLbl = array( "rcID"=>"ID", "rcName"=>"Name", "rcImportant" => "Is important", "rcActive"=>"Active", "rcResCount"=>"Resources count",
                            "rcAdded"=>"Added", "rcUpdated"=>"Last changed");
    static $postOverrided = array();
    
    private static function validateName($name = "rcName"){
        val::required($name,"Please fill out the ". strtolower(self::$fldLbl[$name]).".");
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::maxLength($name, 150);
        val::unique($name, self::$dt["tbl"], self::$dt["key"], RCategoriesController::$id);
    }

    final static function DetailsValidate($type, &$lists){
        $lists["active"] = self::$active;
        val::init($type);
        self::validateName();
        if(!isset(RCategoriesController::$id) && val::$runat==="server"){ //Fix the MySql problem with more than 1 timestamp
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
        $query = "SELECT *, 
                        (select count(rclID) from res_cats_link
                        left join resources
                        on resID = rclRID
                        where rclRCID=rcID and resActive='Yes') as rcResCount 
                  FROM " . self::$dt["tbl"];
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
        $data = self::data("rcActive='Yes'", ["rcImportant" => "ASC", "rcName" => "ASC"]);
        $result = [];
        foreach ($data as $item) {
            $result[$item["rcID"]] = htmlspecialchars($item["rcName"]);
        }
        return $result;
    }

    public static function Resources($rcID) 
    {
        $data = ResourcesModel::data("resID in (select rclRID from res_cats_link where rclRCID=".$rcID.") and resActive='Yes'", ["resImportant" => "ASC", "resTitle" => "ASC"]);        
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
        $data = self::data("rcActive='Yes'", ["rcImportant" => "ASC", "rcName" => "ASC"]);        
        if($data === false) {
            $data = [];
        }
        foreach ($data as &$item) {
            $item["rcName"] = htmlspecialchars($item["rcName"]);
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
			
            $title = (filter_has_var(INPUT_GET, "id") ? "Editing: " . $C::$data[self::$dt["pfx"]."Name"] : "Add ".$C::$pg[$C::$rq]["title"]);
        }
        return array($title, $links);
    }

    static function ItemDeleteAllowed(){        
        return true;
    }
}
