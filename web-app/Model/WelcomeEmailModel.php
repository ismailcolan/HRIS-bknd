<?php

/** The General model */
class WelcomeEmailModel extends AppModel
{

    static $dataAddons = array(), $dt = array("tbl" => "email_template", "key" => "emtID", 
	"pfx" => "emt", "controller" => "WelcomeEmailController");
    static $mainTblFields = array("emtName", "emtSubject", "emtMessage");
    static $fldLbl = array("emtID" => "ID", "emtName" => "Name", "emtSubject" => "Subject", "emtMessage" => "Message");
    static $addonFieldsByTables;
    static $postOverrided = array();


    public static function validateName($name='emtName'){
		 val::required($name,"Please enter ".self::$fldLbl[$name]);
		 val::minLength($name, 2, "Please add at least 2 characters.");
        val::unique($name, self::$dt["tbl"], self::$dt["key"], 
		 WelcomeEmailController::$id, "Template with current name is already registered. Please use unique name.");
		 
        val::maxLength($name, 500);
	}
	
	public static function validateSubject($name='emtSubject'){
		 val::required($name,"Please enter ".self::$fldLbl[$name]);
		 val::minLength($name, 2, "Please add at least 2 characters.");

		 val::maxLength($name, 500);
		 
	}
	
 	public static function validateMessage($name='emtMessage'){
	
		 val::required($name,"Please enter message."); 
		 
		 val::minLength($name, 2, "Please add at least 2 characters.");
		 if (!val::maxLength($name, 65000,'Email Message too long.')  &&  !empty($_POST))
	    {
			if (isset(form::$error['emtMessage'])  &&  in_array('Email Message too long.',form::$error['emtMessage']))
			{msg::clear(); msg::set('Email Message too long.','error');}
		}
		 self::validateMsg($name);
		
	}
	
	public static function checkVars($name){
		global $_TEMPL_ALLOWED;
		if (isset($_POST[$name])) {
			$s= getAllStriingsBetween($_POST[$name]);
			
		}
	}
	
	public static function validateMsg($name='emtMessage'){
		global $_TEMPL_ALLOWED,$_TEMPL_ALLOWED_ADDON;
		if (! isset($_POST[$name])) return;
		$s = $_POST[$name];
		$s = str_replace($_TEMPL_ALLOWED,'',$s);

		$count = substr_count($s,'[CreatePaswordLink]');
		if ( $count == 0 ) val::setError($name,'There must be one [CreatePaswordLink] in message.');
		if ( $count > 1 ) val::setError($name,'There must be exactly one [CreatePaswordLink] in message.');
	}
	
    final static function DetailsValidate($type, &$lists)
    {
		$lists["active"] = self::$active;
		val::init($type);
		self::validateName();
		self::validateSubject();
		self::validateMessage();

       val::throwServerRunError();
    }


    static function query($conditions = '', array $orderby = array(), $limit = 0)
    {
        $query = "SELECT * FROM " . self::$dt["tbl"];

        $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);

        $optionalAddons = array();
        $addons = array();
        foreach (self::$dataAddons as $k) {
            $addons[] = (in_array($k, array_keys($optionalAddons)) ? $optionalAddons[$k] : cf::end(__METHOD__ . ": Incorrect addon request call:" . $k));
        }

        $addonsQry = str_replace("*", (empty($addons) ? "*" : ("*,\n" . implode(",\n", $addons))), $q);

        return $addonsQry;
    }

    static function dataById($id = 0, $allLangs = false)
    {
        $data = parent::dataById($id, $allLangs);
        if ($data === null) {
            $data = [self::$dt["pfx"] . "Name" => "--- Not set ---"];
        } else {
            $data[self::$dt["pfx"] . "Name"] = htmlspecialchars($data[self::$dt["pfx"] . "Name"]);
        }
        return $data;
    }

    public static function StatusCond($orderType)
    {
        if ($orderType === "inactive-list") {
            return "`" . self::$dt["pfx"] . "Active`='No' ";
        } else {
            return "`" . self::$dt["pfx"] . "Active`='Yes' ";
        }

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
            $title = (filter_has_var(INPUT_GET, "id") ? "Editing: " . $C::$data[self::$dt["pfx"] . "Name"] : "Add " . $C::$pg[$C::$rq]["title"]);
        }
        return array($title, $links);
    }


    public static function AllActiveKeyName($currentValue = null, $includeManager = true)
    {
        if($includeManager) {
            $data = db::LoadKeyValueArrFromTable(self::query("`" . self::$dt["pfx"] . "Active`='Yes' "), self::$dt["key"], self::$dt["pfx"] . "Name");
        } else {
            $data = db::LoadKeyValueArrFromTable(self::query("`" . self::$dt["pfx"] . "Active`='Yes' and pID <> 9999"), self::$dt["key"], self::$dt["pfx"] . "Name");
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

    static function ItemDeleteAllowed()
    {
        return false;
    }


    static function getAllActivePositions()
    {
        $q = "SELECT * FROM positions
              WHERE pActive = 'Yes' ORDER BY pName";

        $data = db::PrepareLoad($q);

        if ($data === false) {
            $data = [];
        } else {
            foreach ($data as &$item) {
                $item[self::$dt["pfx"] . "Name"] = htmlspecialchars($item[self::$dt["pfx"] . "Name"]);
            }
        }
        return $data;
    }


    public static function formatSalary($salary)
    {
        $salary = str_replace(["$", "annual", "hourly", ","],"",$salary);
        if(empty($salary)) {
            return '';
        }
        try {
            $sInt = intval($salary);
            if ((string)$sInt == $salary) {
                $result = "$" . number_format($sInt);
            } else {

                $result = "$" . number_format($salary, 2, ".", ",");
            }
        } catch (Exception $e) {
            $result = $salary;
        }

        return $result;
    }

    public static function requiredSkillsAsString($skillIDs = "")
    {
        $allSkills = SkillModel::AllActiveKeyName();
        $skillNames = [];
        $list = explode("|", $skillIDs);
        foreach ($list as $id) {
            if (isset($allSkills[$id])) {
                $skillNames[] = $allSkills[$id];
            }
        }
        return implode(", ", $skillNames);
    }
}
