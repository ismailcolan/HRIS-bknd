<?php
/** The Orders model */
class AnswerModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"answers", "key"=>"aID", "pfx"=>"a", "controller"=>"QuestionController");
    static $mainTblFields = array("aName");
    static $fldLbl = array( "aID"=>"ID", "aQID"=>"Question", "aName"=>"Answer");
    static $addonFieldsByTables;
    static $postOverrided = array();


    private static function validateName($name = "aName"){
        val::required($name,"Please enter answer.");
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::maxLength($name, 500);
    }

    final static function DetailsValidate($type, &$lists){
        $lists["active"] = self::$active;
        val::init($type);

        $i = 1;
        while(filter_has_var(INPUT_POST, "ansName".$i)) {
            self::validateName("ansName".$i);
            $i++;
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
        } else if($C::$rq==="details" && filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING)=== "trash"){
            $title = "<span class=\"text-danger\">Delete</span> Answer #".$C::$id;
            $links = null;
        } else {
            if($C::$data) {
                $title = "Edit ".$C::$data[self::$dt["pfx"]."Name"];
            } else {
                $title = "Add new answer";
            }
        }
        return array($title, $links);
    }

    static function ItemDeleteAllowed(){
        return false;
    }

    public static function QuestionAnswersQuery($qID){
        $qid = intval($qID);
        return 'select * from `answers` where `aQID` = '.$qid.' and `aActive`="Yes"';
    }

    public static function GetQuestionAnswers($qID, $addRejectedFor = false){
        $q = self::QuestionAnswersQuery($qID);
        $data = db::PrepareLoad($q);
        if($data === false) {
            $data = [];
        } else {

            foreach ($data as &$item) {
                $item[self::$dt["pfx"]."Name"] = htmlspecialchars($item[self::$dt["pfx"]."Name"]);
            }

            if($addRejectedFor) {
                foreach ($data as &$item) {
                    $rpForList = AnswerRejectedPositionsModel::GetAnswerRejectedPositions($item["aID"]);
                    $item["rejectedFor"] = ($rpForList === false ? [] : $rpForList);
                }
            }
        }
        return $data;
    }

    static function saveAnswers($qID){
        $i = 1;
        db::StartTransaction();
        while(filter_has_var(INPUT_POST, "ansName".$i)) {
            $name =  db::FastEscStr(filter_input(INPUT_POST, "ansName".$i));
            $q = "INSERT INTO `answers` (`aQID`, `aName`, `aActive`, `aAdded`, `aUpdated`) values (%s, '%s', 'Yes', NOW(), NOW())";
            $result = db::SQL(sprintf($q, $qID, $name));
            if($result) {
                $aID = db::LastID();
                if(filter_has_var(INPUT_POST, "ansPositions".$i)) {
                    $arPos = filter_input(INPUT_POST, "ansPositions".$i, FILTER_DEFAULT, FILTER_FORCE_ARRAY);
                    if($arPos && (count($arPos) > 0)) {
                        AnswerRejectedPositionsModel::saveRejectedFor($aID, $arPos);
                    }
                }
            } else {
                db::Rollback();
                return false;
            }
            $i++;
        }
        db::Commit();
        return true;
    }
}
