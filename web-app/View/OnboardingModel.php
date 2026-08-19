<?php

/** The CompanyOffice model */
class OnboardingModel extends AppModel
{
    static 
	 $type='on',
	 $dataAddons = array(), 
	 $dt = array("tbl" => "onboarding_tasks", "key" => "otID", "pfx" => "ot", 
	 "controller" => "OnboardingController");
    static $mainTblFields = array("otTaskOrDocsToSign","otApplayTo","otType","otDescription", "otAFID", "otKeepStatus", "otTimeFrameNum", "otTimeFrameDim", "otTimeFrame", "otActive"); /*"coID", , "coAdded", "coUpdated"*/
    static $fldLbl = array("otID" => "ID", "otDescription" => "Description", "otAFID" => "Responsible employee", "otKeepStatus" => "Keep status if position changed",
        "otTimeFrameNum" => "Time frame", "otTimeFrameDim" => "Time frame", "otTimeFrame" => "Time frame", "otEmployee" => "Responsible employee",
        "otPositions" => "Positions required for","otApplayTo"=>"Apply to",
		 "otTaskOrDocsToSign" =>"Type of document",
        "otActive" => "Active", "otAdded" => "Added", "otUpdated" => "Last changed");
    static $postOverrided = array();
	
	public static function getAllOffboardingTasks(){
		$q='select * from onboarding_tasks WHERE otType="off"';
		$res = db::PrepareLoad($q);
		$ret=array();
		foreach ($res as $k=>$r){
			$ret[]=$r['otID'];
		}
		return $ret;
	}
	
	public static function getEmplyeesNotLeavingOnly(){
		$today = new DateTime();
		$q = 'SELECT *,concat(afFirstName," ",afLastName) as afFullName FROM apply_forms WHERE 
			  afActive = "Hired" AND (afDateLeaving IS NULL )
			  ORDER by afLastName ASC,afFirstName ASC';
			  
		$res = db::PrepareLoad($q);
		$ret = array();
		
		foreach ($res as $k=>$r){
			$ret[$r['afID']]=trim($r['afFullName']);
		}
		return $ret;
	}

    private static function validateDescription($name = "otDescription")
    {
        val::required($name, "Please fill out the " . strtolower(self::$fldLbl[$name]) . ".");
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::maxLength($name, 100);
        val::unique($name, self::$dt["tbl"], self::$dt["key"], OnboardingController::$id);
    }

    private static function validateEmployee($name = "otAFID")
    {
        val::required($name, "Please select responsible employee", "select");
    }
	
	 private static function validateApplayTo()
    {
		  if (val::$runat=='server'){
			  if(empty($_POST['otApplayTo'])) val::setError('otApplayTo','Please select to whum this offboarding task apply');
		  }
		  else
        val::required("otApplayTo", "Please select to whum this off-boarding task apply", "check-list");
    }

    private static function validateTimeFrame()
    {
        val::required("otTimeFrameNum", "Please select time frame", "select");
        val::required("otTimeFrameDim", "Please select time frame", "select");
    }

    final static function DetailsValidate($type, &$lists)
    {
        $lists["active"] = self::$active;
		 
        val::init($type);
		 if (OnboardingController::$type =='off')  self::validateApplayTo();
        self::validateDescription();
        self::validateEmployee();
        self::validateTimeFrame();
        if (!isset(OnboardingController::$id) && val::$runat === "server") { //Fix the MySql problem with more than 1 timestamp
            self::$mainTblFields[] = "otAdded";
            self::$postOverrided[self::$dt["pfx"] . "Added"] = "NOW()";
            self::$postOverridedSkipEscape[] = self::$dt["pfx"] . "Added";

            self::$mainTblFields[] = self::$dt["pfx"] . "Updated";
            self::$postOverrided[self::$dt["pfx"] . "Updated"] = "NOW()";
            self::$postOverridedSkipEscape[] = self::$dt["pfx"] . "Updated";
        }
        val::throwServerRunError();
    }

    static function delete($data, $dump = false)
    {
        return false;
    }

    static function query($conditions = '', $orderby = array(), $limit = 0,$type)
    {
        $query = "SELECT *, 
                        (select concat(afFirstName, ' ', afLastName) from apply_forms where afID=otAFID) otEmployee,
                        (select GROUP_CONCAT(pName) from positions where pID in (select otpPID from onboarding_positions where otpOTID=otID)) as otPositions
                  FROM " . self::$dt["tbl"];
				     if ($conditions=='') $conditions.=' otType="'.$type.'" ';
					 else $conditions.=' AND otType="'.$type.'" ';
					
        $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);

        $optionalAddons = array();
        $addons = array();

        foreach (self::$dataAddons as $k) {
            $addons[] = (in_array($k, array_keys($optionalAddons)) ? $optionalAddons[$k] : die(__METHOD__ . ": Incorrect addon request call (" . $k . ")"));
        }

        $ret = str_replace("*", (empty($addons) ? "*" : ("*,\n" . implode(",\n", $addons))), $q);
		
		 return $ret;
    }


    public static function AllActiveKeyNameEmpl($afID = null,$type='on') {
		
		//query($conditions = '', $orderby = array(), $limit = 0,$type='on')
        if(empty($afID)) {
            $data = db::LoadKeyValueArrFromTable(self::query("`" . self::$dt["pfx"] . "Active`='Yes' ",
			 array(),0,$type), 
			 self::$dt["key"], self::$dt["pfx"] . "Description");
        } else {
			//OR otAFID=0
			
            $data = db::LoadKeyValueArrFromTable(self::query("`" . self::$dt["pfx"] . "Active`='Yes' and otAFID=".$afID.' ',
			  array(),0,$type), self::$dt["key"], self::$dt["pfx"] . "Description");
			  // <<<<PV  1.7.2018 added OR otAFID=0
        }
        if ($data === false) {
            $data = [];
        } else {
            foreach ($data as &$item) {
                $item = htmlspecialchars($item);
            }
        }
        return $data;
    }
	
	


    static function dataById($id = 0, $allLangs = false)
    {
        $data = parent::dataById($id, $allLangs);
        if (empty($data)) {
            return false;
        }

        $data[self::$dt["pfx"] . "Description"] = htmlspecialchars($data[self::$dt["pfx"] . "Description"]);
        $data[self::$dt["pfx"] . "Employee"] = htmlspecialchars($data[self::$dt["pfx"] . "Employee"]);
        $data[self::$dt["pfx"] . "Positions"] = array_keys(OnboardingPositionsModel::LoadAsKeyValueArray($id));

        return $data;
    }

    static function adminPageTitleWithLinks($links = null)
    {
        $C = self::$dt["controller"];
        if (in_array($C::$rq, array("list", "inactive-list"))) {
            $title = $C::$pg[$C::$rq]["title"];
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else if ($C::$rq === "summary") {
            $title = (filter_has_var(INPUT_GET, "id") ? $C::$data[self::$dt["pfx"] . "Name"] : cf::end(__METHOD__));
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else {
			  $title = (filter_has_var(INPUT_GET, "id") ? "Editing: " . $C::$data[self::$dt["pfx"] . "Description"] : "Add " . 
			  $C::$pg[$C::$rq]["title"]);
        }
        return array($title, $links);
    }

    static function ItemDeleteAllowed()
    {
        return false;
    }
	
	public static function getLastOnboardings($obID){
		
		$q='SELECT * FROM onboarding_files  WHERE ofOBID='.$obID.' AND ofActive="active"';// AND ofReplaceFile=0';
		////AND ! EXISTS (SELECT * FROM onboarding_files of2 WHERE of2.ofReplaceFile=of1.ofID AND ofActive="disabled")';
		$res=db::PrepareLoad($q);
		/****
		foreach ($res as $k=>$r){
			while (!  empty($z=db::PrepareLoadSingleRow('SELECT * FROM onboarding_files WHERE  ofReplaceFile='.$r['ofID']))){
				$r = $z;
				if($z['ofActive']=='active') $res[$k] = $r;
			}
		}
		****/
		// order them by alphabet!!!
		return $res;
	}
	
	public static function getAllLastOnboardings($obID){
		
		$q='SELECT * FROM onboarding_files WHERE ofOBID='.$obID.' AND ofReplaceFile=0 ORDER BY ofActive ASC';
		$res=db::PrepareLoad($q);
		
		if ($res === false ) $res=array();
		foreach ($res as $k=>$r){
			while (!  empty($z=db::PrepareLoadSingleRow('SELECT * FROM onboarding_files WHERE ofReplaceFile='.$r['ofID']))){
				$r = $z;
				$res[$k] = $r;
				
			}
		}
		
		
		return $res;
	}
	
	public static function getHistoryOf($ofID){
		
		$q='SELECT * FROM onboarding_files WHERE ofID='.$ofID;
		$res=db::PrepareLoadSingleRow($q);
		//cf::pr($res);exit;
		$repl_file=$res['ofReplaceFile'];
		
		$ret=array();
		while ($repl_file != 0){
			$q='SELECT * FROM onboarding_files WHERE ofID='.$repl_file;
			
			$r1=db::PrepareLoadSingleRow($q);
			$ret[]=$r1;
			
			$repl_file=$r1['ofReplaceFile'];
		}
		
		return $ret;
	
	}
}
