<?php
/** The EmployeeOffice model */
class OnboardingPositionsModel extends AppModel {

    static $dataAddons = array(), $dt = array("tbl"=>"onboarding_positions", "key"=>"otpID", "pfx"=>"otp", "controller"=>"");
    static $mainTblFields = array("otpOTID", "otpPID");
    static $fldLbl = array();
    static $postOverrided = array();


    public static function LoadOnboardingPositions($otID) {
        $otid = intval($otID);
        $q = "select * from `".self::$dt['tbl']."`
              left join `positions` on `pID` = `otpPID` 
              where `pActive`= 'Yes' and `otpOTID` = ".$otid;
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
	
	public static function LoadPositionOnboardingsONBOnly($pID) {
        $pid = intval($pID);
        $q = "select * from `".self::$dt['tbl']."`
              left join `onboarding_tasks` on `otID` = `otpOTID` 
              where otType='on' AND `otActive`= 'Yes' and `otpPID` = ".$pid;
        $data = db::PrepareLoad($q);
        if($data === false) {
            $data = [];
        } else {
            foreach ($data as &$item) {
                $item["otDescription"] = htmlspecialchars($item["otDescription"]);
            }
        }
        return $data;
    }

    public static function LoadPositionOnboardings($pID) {
        $pid = intval($pID);
        $q = "select * from `".self::$dt['tbl']."`
              left join `onboarding_tasks` on `otID` = `otpOTID` 
              where `otActive`= 'Yes' and `otpPID` = ".$pid;
        $data = db::PrepareLoad($q);
        if($data === false) {
            $data = [];
        } else {
            foreach ($data as &$item) {
                $item["otDescription"] = htmlspecialchars($item["otDescription"]);
            }
        }
        return $data;
    }

    public static function LoadAsKeyValueArray($afID) {
        $data = self::LoadOnboardingPositions($afID);
        $result = [];
        foreach ($data as $item) {
            $result[$item["otpPID"]] = $item["pName"];
        }
        return $result;
    }

    public static function SaveOnboardingPositions($otID, $pIDs = []) {
        $otid = intval($otID);
        $q = "delete from `".self::$dt['tbl']."` where `otpOTID` = ".$otid;
        db::SQL($q);

        $q = [];
        if(empty($pIDs)) { $pIDs = []; }
        foreach ($pIDs as $pid) {
            $pid = intval($pid);
            $q[] = "insert into `".self::$dt['tbl']."` (`otpOTID`, `otpPID`) values($otid, $pid)";
        }
        return db::Save($q);
    }

}
