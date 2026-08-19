<?php
/** The Orders model */
class QuestionModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"questions", "key"=>"qID", "pfx"=>"q", "controller"=>"QuestionController");
    static $mainTblFields = array("qName", "qActive");
    static $fldLbl = array( "qID"=>"ID", "qName"=>"Question");
    static $addonFieldsByTables;
    static $postOverrided = array();


    private static function validateName($name = "qName"){
        val::required($name,"Please enter question.");
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::unique($name, self::$dt["tbl"], self::$dt["key"], QuestionController::$id, "Question with current name is already registered. Please use unique name.");
        val::maxLength($name, 30000);
    }

    final static function DetailsValidate($type, &$lists){
        $lists["active"] = self::$active;
        val::init($type);
        self::validateName();
        if(!isset(QuestionController::$id) && val::$runat==="server"){ //Fix the MySql problem with more than 1 timestamp
            self::$mainTblFields[] = self::$dt["pfx"]."Added";
            self::$postOverrided[self::$dt["pfx"]."Added"] = "NOW()";
            self::$postOverridedSkipEscape[] = self::$dt["pfx"]."Added";

            self::$mainTblFields[] = self::$dt["pfx"]."Updated";
            self::$postOverrided[self::$dt["pfx"]."Updated"] = "NOW()";
            self::$postOverridedSkipEscape[] = self::$dt["pfx"]."Updated";

            if(filter_has_var(INPUT_POST, "positions")) {
                self::$postOverrided["positions"] = filter_input(INPUT_POST, "positions", FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            } else {
                self::$postOverrided["positions"] = [];
            }

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


    static function getAllActiveQuestionsForPosition($pID, $loadAns = true) {
        $q = "SELECT * FROM questions
              LEFT JOIN question_positions ON qpQID = qID
              LEFT JOIN positions on pID = qpPID
              WHERE qpPID = $pID AND qActive = 'Yes' AND pActive = 'Yes'";

        $result = db::PrepareLoad($q);
        if($result === false) $result = [];

        foreach ($result as $key => &$item) {
            $item["answers"] = AnswerModel::GetQuestionAnswers($item["qID"], true);
            if($item["answers"] === false) {
                $item["answers"] = [];
            }
            if(count($item["answers"]) < 2) unset($result[$key]);
        }

        return $result;
    }

}
