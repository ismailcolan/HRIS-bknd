<?php
/** The EmployeeOffice model */
class ManagerOfficesModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"manager_offices", "key"=>"moID", "pfx"=>"mo", "controller"=>"");
    static $mainTblFields = array("moAFID", "moCOID");
    static $fldLbl = array();
    static $postOverrided = array();


    public static function LoadManagerOffices($afID) {
        $afid = intval($afID);
        $q = "select * from `".self::$dt['tbl']."`
              left join `company_offices` on `coID` = `moCOID` 
              where `coActive`= 'Yes' and `moAFID` = ".$afid;
        $data = db::PrepareLoad($q);
        if($data === false) {
            $data = [];
        } else {
            foreach ($data as &$item) {
                $item["coName"] = htmlspecialchars($item["coName"]);
            }
        }
        return $data;
    }

    public static function LoadAsKeyValueArray($afID) {
        $data = self::LoadManagerOffices($afID);
        $result = [];
        foreach ($data as $item) {
            $result[$item["moCOID"]] = $item["coName"];
        }
        return $result;
    }

    public static function SaveManagerOffices($afID, $coIDs = []) {
        $afid = intval($afID);
        $q = "delete from `".self::$dt['tbl']."` where `moAFID` = ".$afid;
        db::SQL($q);

        $q = [];
        if(empty($coIDs)) { $coIDs = []; }
        foreach ($coIDs as $coid) {
            $coid = intval($coid);
            $q[] = "insert into `".self::$dt['tbl']."` (`moAFID`, `moCOID`) values($afid, $coid)";
        }
        return db::Save($q);
    }

}
