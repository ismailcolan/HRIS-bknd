<?php
/** The EmployeeOffice model */
class QuestionPositionsModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"question_positions", "key"=>"qpID", "pfx"=>"qp", "controller"=>"QuestionController");
    static $mainTblFields = array("qpQID", "qpPID");
    static $fldLbl = array();
    static $postOverrided = array();


    public static function LoadQuestionPositions($qID) {
        $qid = intval($qID);
        $q = "select * from `question_positions` 
              left join `positions` on `pID` = `qpPID` 
              where `pActive`= 'Yes' and `qpQID` = ".$qid;
        $data = db::PrepareLoad($q);
        if($data === false) {
            $data = [];
        } else {
            foreach ($data as &$item) {
                $item["pName"] = htmlspecialchars($item["pName"]);
            }
        }
        return $data;
    }

    public static function LoadAsKeyValueArray($qID) {
        $data = self::LoadQuestionPositions($qID);
        $result = [];
        foreach ($data as $item) {
            $result[$item["pID"]] = $item["pName"];
        }
        return $result;
    }

    public static function SaveQuestionPositions($qID, $pIDs = []) {
        $qid = intval($qID);
        $q = "delete from `question_positions` where `qpQID` = ".$qid;
        $data = db::SQL($q);

        $q = [];
        if(empty($pIDs)) { $pIDs = []; }
        foreach ($pIDs as $pid) {
            $q[] = "insert into `question_positions` (`qpQID`, `qpPID`) values($qid, $pid)";
        }
        return db::Save($q);
    }

}
