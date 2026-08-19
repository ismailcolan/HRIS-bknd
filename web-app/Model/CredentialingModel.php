<?php

/** The CompanyOffice model */
class CredentialingModel extends AppModel
{

   static $dataAddons = array(), $dt = array("tbl" => "credentialings", "key" => "cID", "pfx" => "c", "controller" => "CredentialingController");
   static $mainTblFields = array(
		"cName","cNotApplForSomeEmployees","cOldType","cType", 
		"cTimeFrameNum", "cTimeFrameDim", "cDueDate", "cKeepFiles", "cActive",
		"cRenewalTimeFrameNum","cRenewalTimeFrameDim",
		"cHasExpirationDate","cImportantWhenDocCompleated"
	);
   static $fldLbl = array("cID" => "ID","cNotApplForSomeEmployees"=>"cNotApplForSomeEmployees",
		"cName" => "Name", "cType" => "Credentialing type", "cKeepFiles" => "Keep files if position changed",
       "cTimeFrameNum" => "Initial Time frame", "cTimeFrameDim" => "Initial Time frame", "cTimeFrame" => "Initial Time frame", 
	    "cRenewalTimeFrameNum"=>"Renewal Time Frame ","cRenewalTimeFrameDim"=>"Renewal Time Frame",
		"cDueDate" => "Due Date", "cActive" => "Status","cHasExpirationDate"=>"Has Expiration Date",
       "cPositions" => "Positions required for","cOldType" => "Credentialing old type",
       "cActive" => "Active", "cAdded" => "Added", "cUpdated" => "Last changed",
	    "cImportantWhenDocCompleated"=>'Is important when document was completed'
	   );
   static $postOverrided = array();

    private static function validateName($name = "cName")
    {
        val::required($name, "Please fill out the " . strtolower(self::$fldLbl[$name]) . ".");
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::maxLength($name, 100);
        $C = self::$dt["controller"];
        val::unique($name, self::$dt["tbl"], self::$dt["key"], $C::$id);
    }
    
	 public static function hasUploadedFile($credID,$userID){
		$q="SELECT * FROM employee_credentialing WHERE ecFileName IS NOT NULL AND ecAFID={$userID} 
		AND ecCID={$credID}";
		$res = db::PrepareLoad($q);
		if (empty($res)) return false; else return true;
		
	}
    private static function validateTimeFrame()
    {
        val::required("cTimeFrameNum", "Please select time frame", "select");
        val::required("cTimeFrameDim", "Please select time frame", "select");
    }
	
	private static function validateRenewalTimeFrame($type)
    {
		if (isset($_POST['cType']) && $_POST['cType']=='recurring'  ){
        val::required("cRenewalTimeFrameNum", "Please select time frame", "select");
        val::required("cRenewalTimeFrameDim", "Please select time frame", "select");
		}
    }

    final static function DetailsValidate($type, &$lists)
    {
        $lists["active"] = self::$active;
        val::init($type);
        self::validateName();
        self::validateTimeFrame();
		 self::validateRenewalTimeFrame($type);
        if (!isset(CredentialingController::$id) && val::$runat === "server") { //Fix the MySql problem with more than 1 timestamp
            self::$mainTblFields[] = self::$dt["pfx"] . "Added";
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

    static function query($conditions = '', array $orderby = array(), $limit = 0)
    {
        $query = "SELECT *, 
                        (select GROUP_CONCAT(pName) from positions where pID in (select cpPID from credentialing_positions where cpCID=cID)) as cPositions
                  FROM " . self::$dt["tbl"];
        $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);

        $addons = array();

        return str_replace("*", (empty($addons) ? "*" : ("*,\n" . implode(",\n", $addons))), $q);
    }

    public static function AllActiveKeyName($afID = null) {
        if(empty($afID)) {
            $data = db::LoadKeyValueArrFromTable(self::query("`" . self::$dt["pfx"] . "Active`='Yes' "), self::$dt["key"], self::$dt["pfx"] . "Name");
        } else {
            $data = db::LoadKeyValueArrFromTable(self::query("`" . self::$dt["pfx"] . "Active`='Yes' and exists(select ecId from employee_credentialing where ecAFID=".$afID." and ecCID=cID)"), self::$dt["key"], self::$dt["pfx"] . "Name");
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

        $data[self::$dt["pfx"] . "Name"] = htmlspecialchars($data[self::$dt["pfx"] . "Name"]);
        $data[self::$dt["pfx"] . "Positions"] = array_keys(CredentialingPositionsModel::LoadAsKeyValueArray($id));
        return $data;
    }

    static function adminPageTitleWithLinks($links = null)
    { 
        $C = self::$dt["controller"];

        if (in_array($C::$rq, array("list", "inactive-list"))) {
            $title = $C::$pg[$C::$rq]["title"];
            $links = AccessModel::AdminPageHeaderLnk($C);
        } elseif ($C::$rq=="report") {
            $title = 'Overdue credentialings report';
            $links = AccessModel::AdminPageHeaderLnk($C);
        } 
		else if ($C::$rq === "summary") {
            $title = (filter_has_var(INPUT_GET, "id") ? $C::$data[self::$dt["pfx"] . "Name"] : cf::end(__METHOD__));
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else {
            $title = (filter_has_var(INPUT_GET, "id") ? 
			"Editing: " . $C::$data[self::$dt["pfx"] . "Name"] 
			: "Add " . $C::$pg[$C::$rq]["title"]);
        }
        return array($title, $links);
    }

    static function ItemDeleteAllowed()
    {
        return false;
    }
}
