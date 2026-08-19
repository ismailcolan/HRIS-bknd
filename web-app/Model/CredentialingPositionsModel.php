<?php
/** The EmployeeOffice model */
class CredentialingPositionsModel extends AppModel {

    static $dataAddons = array(), $dt = array("tbl"=>"credentialing_positions", "key"=>"cpID", "pfx"=>"cp", "controller"=>"");
    static $mainTblFields = array("cpCID", "cpPID");
    static $fldLbl = array();
    static $postOverrided = array();


    public static function LoadCredentialingPositions($cID) {
        $cid = intval($cID);
        $q = "select * from `".self::$dt['tbl']."`
              left join `positions` on `pID` = `cpPID` 
              where `pActive`= 'Yes' and `cpCID` = ".$cid;
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

    public static function LoadPositionCredentialings($pID) {
        $pid = intval($pID);
        $q = "select * from `".self::$dt['tbl']."`
              left join `credentialings` on `cID` = `cpCID`
              where `cActive`= 'Yes' and `cpPID` = ".$pid;
        $data = db::PrepareLoad($q);
        if($data === false) {
            $data = [];
        } else {
            foreach ($data as &$item) {
                $item["cName"] = htmlspecialchars($item["cName"]);
            }
        }
        return $data;
    }

    public static function LoadAsKeyValueArray($cID) {
        $data = self::LoadCredentialingPositions($cID);
        $result = [];
        foreach ($data as $item) {
            $result[$item["cpPID"]] = $item["pName"];
        }
        return $result;
    }

    public static function SaveCredentialingPositions($cID, $pIDs = []) {
        $cid = intval($cID);
        $q = "delete from `".self::$dt['tbl']."` where `cpCID` = ".$cid;
        db::SQL($q);

        $q = [];
        if(empty($pIDs)) { $pIDs = []; }
        foreach ($pIDs as $pid) {
            $pid = intval($pid);
            $q[] = "insert into `".self::$dt['tbl']."` (`cpCID`, `cpPID`) values($cid, $pid)";
        }
        return db::Save($q);
    }

}
