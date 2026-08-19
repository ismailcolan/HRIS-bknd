<?php
/** The Orders model */
class AnswerRejectedPositionsModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"answer_rejected_positions", "key"=>"arpID", "pfx"=>"arp", "controller"=>"AnswerRejectedPositionsController");
    static $mainTblFields = array("arpAID", "arpPID");
    static $fldLbl = array( "arpID"=>"ID", "arpAID"=>"Answer", "arpPID"=>"Position");
    static $addonFieldsByTables;
    static $postOverrided = array();


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

    static function ItemDeleteAllowed(){
        return true;
    }

    public static function AnswerRejectedPositionsQuery($aID){
        $qid = intval($aID);
        return 'select `'.self::$dt['tbl'].'`.*, p.pName from `'.self::$dt['tbl'].'` left join positions p on p.pID=arpPID where `'.self::$dt['pfx'].'AID` = '.$aID.' and p.pActive="Yes"';
    }

    public static function GetAnswerRejectedPositions($aID){
        $q = self::AnswerRejectedPositionsQuery($aID);
        $data = db::PrepareLoad($q);
        if($data === false) {
            $data = [];
        } else {
            foreach($data as &$item) {
                $item["pName"] = htmlspecialchars($item["pName"]);
            }
        }
        return $data;
    }

    public static function saveRejectedFor($aID, $arPos) {
        $q = "INSERT INTO `answer_rejected_positions` (`arpAID`, `arpPID`) values (%d, %d)";
        foreach ($arPos as $arPo) {
            db::SQL(sprintf($q, $aID, $arPo));
        }
    }

}
