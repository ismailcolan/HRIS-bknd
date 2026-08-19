<?php
/** The EmployeeOffice model */
class ManagerPositionsModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"manager_positions", "key"=>"mpID", "pfx"=>"mp", "controller"=>"");
    static $mainTblFields = array("mpAFID", "mpPID");
    static $fldLbl = array();
    static $postOverrided = array();


    public static function LoadManagerPositions($afID) {
        $afid = intval($afID);
        $q = "select * from `".self::$dt['tbl']."`
              left join `positions` on `pID` = `mpPID` 
              where `pActive`= 'Yes' and `mpAFID` = ".$afid;
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

    public static function LoadAsKeyValueArray($afID) {
        $data = self::LoadManagerPositions($afID);
        $result = [];
        foreach ($data as $item) {
            $result[$item["mpPID"]] = $item["pName"];
        }
        return $result;
    }

    public static function SaveManagerPositions($afID, $pIDs = []) {
        $afid = intval($afID);
        $q = "delete from `".self::$dt['tbl']."` where `mpAFID` = ".$afid;
        db::SQL($q);

        $q = [];
        if(empty($pIDs)) { $pIDs = []; }
        foreach ($pIDs as $pid) {
            $pid = intval($pid);
            $q[] = "insert into `".self::$dt['tbl']."` (`mpAFID`, `mpPID`) values($afid, $pid)";
        }
        return db::Save($q);
    }

}
