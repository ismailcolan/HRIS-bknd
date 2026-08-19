<?php
/** The Orders model */
class SkillModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"skills", "key"=>"sID", "pfx"=>"s", "controller"=>"SkillController");
    static $mainTblFields = array("sName", "sActive");
    static $fldLbl = array( "sID"=>"ID", "sName"=>"Skill");
    static $addonFieldsByTables;
    static $postOverrided = array();


    private static function validateName($name = "sName"){
        val::required($name,"Please enter skill.");
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::unique($name, self::$dt["tbl"], self::$dt["key"], SkillController::$id, "Skill with current name is already registered. Please use unique name.");
        val::maxLength($name, 150);
    }

    final static function DetailsValidate($type, &$lists){
        $lists["active"] = self::$active;
        val::init($type);
        self::validateName();
        if(!isset(SkillController::$id) && val::$runat==="server"){ //Fix the MySql problem with more than 1 timestamp
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
        $addons = array();
        foreach(self::$dataAddons as $k){
            $addons[] = (in_array($k, array_keys($optionalAddons)) ? $optionalAddons[$k] : cf::end(__METHOD__.": Incorrect addon request call:".$k));
        }

        $addonsQry = str_replace("*", (empty($addons) ? "*" : ("*,\n".implode(",\n",$addons))), $q);

        return $addonsQry;
    }


    public static function StatusCond($orderType){
        if($orderType==="inactive-list"){
            return "`".self::$dt["pfx"]."Active`='No'";
        } else {
            return "`".self::$dt["pfx"]."Active`='Yes'";
        }
        
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
        $data[self::$dt["pfx"]."Name"] = htmlspecialchars($data[self::$dt["pfx"]."Name"]);
        return $data;
    }


    public static function AllActiveKeyName() {
        $data = db::LoadKeyValueArrFromTable(self::query(self::StatusCond("list")), self::$dt["key"], self::$dt["pfx"]."Name");
        if($data === false) { $data = []; }
        else {
            foreach ($data as &$item) {
                $item = htmlspecialchars($item);
            }
        }
        return $data;
    }

    public static function RequiredSkillsForPosition($position) 
    {
		 if (!isset($position["pSkills"])) return '';
        $pSkills = explode("|", $position["pSkills"]);
        foreach($pSkills as $key=>&$pSkill) {
            if(intval($pSkill) === 0) {
                unset($pSkills[$key]);
            }
        }
        if(empty($pSkills)) {
            return [];
        }

        $q = 'select sID, sName from skills where sID in ('.implode(',', $pSkills).') and sActive="Yes"';
        $skills = db::PrepareLoad($q);

        $result = [];
        if(is_array($skills)) {
            foreach($skills as $skill) {
                $result[$skill["sID"]] = htmlspecialchars($skill["sName"]);
            }
        }
        
        return $result;
    }

}
