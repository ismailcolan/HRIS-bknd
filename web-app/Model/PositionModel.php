<?php

/** The Orders model */
class PositionModel extends AppModel
{

    static $dataAddons = array(), $dt = array("tbl" => "positions", "key" => "pID", "pfx" => "p", "controller" => "PositionController");
    static $mainTblFields = array("pName", "pSkills", "pSalaryType", "pSalaryFrom", "pSalaryTo",
	"pActive","pWelcomeEmail","pPhisician","pVacationDays","pCEDays");
    static $fldLbl = array("pID" => "ID", "pName" => "Position", "pSkills" => "Required skills", "pSalaryType" => "Salary type",
	"pSalaryFrom" => "Salary from", "pSalaryTo" => "Salary to","pWelcomeEmail"=>"Email Template",
	"pVacationDays"=>"Annual paid days Vacation","pCEDays"=>"Annual paid days CE");
    static $addonFieldsByTables;
    static $postOverrided = array();
    
	 public static function getActiveOffices(){
		$q="SELECT * FROM company_offices where coActive='Yes' ORDER BY coName ASC";
		$res=db::PrepareLoad($q);
		if ($res==false) $res=array();
		return $res;
	}

    private static function validateName($name = "pName")
    {
		val::required($name, "Please enter position name.");
		val::minLength($name, 2, "Please add at least 2 characters.");
		if(! isset($_GET['id'])) val::unique($name, self::$dt["tbl"], self::$dt["key"], PositionController::$id,
		"Position with current name is already registered. Please use unique name.");
		val::maxLength($name, 150);
    }

    private static function validateSkills()
    {
        $skills = filter_input(INPUT_POST, "pSkills", FILTER_DEFAULT, FILTER_FORCE_ARRAY);
        if ($skills !== null) {
            $filtered = [];
            foreach ($skills as $item) {
                $item = intval($item);
                if ($item !== 0) $filtered[] = $item;
            }
            static::$postOverrided["pSkills"] = implode("|", $_POST["pSkills"]);
        } else {
            static::$postOverrided["pSkills"] = "";
        }
    }

    private static function validateSalary()
    {
        if(val::required("pSalaryType", "Please, select salary type.")) {
            if (filter_input(INPUT_POST, "pSalaryType") === "Hourly") {
                $minSalary = 0;
				   $maxSalary = 10000;
				   $rate='Hourly rate ';
            } else {
				   $minSalary = 1000;
                $maxSalary = 1000000;
				   $rate='Anual rate ';
            }
			  $rate = '<br>'.$rate;
            val::required("pSalaryFrom", "Please fill out the field.");
            val::required("pSalaryTo", "Please fill out the field.");
            val::regExp("pSalaryFrom", "JSVAL_RX_MONEY_COMMAS", "Please use valid number format (1,234.56)");
            val::regExp("pSalaryTo", "JSVAL_RX_MONEY_COMMAS", "Please use valid number format (1,234.56)");

            $_POST["pSalaryFrom"] = str_replace([",", " "], "", filter_input(INPUT_POST, "pSalaryFrom"));
            $_POST["pSalaryTo"] = str_replace([",", " "], "", filter_input(INPUT_POST, "pSalaryTo"));

            val::numMin("pSalaryFrom", $minSalary, "Invalid salary range.".$rate.'FROM is from '.$minSalary.'.' );
            val::numMax("pSalaryFrom", $maxSalary, "Invalid salary range.".$rate.'FROM is to '.$maxSalary.'.');

            val::numMin("pSalaryTo", $minSalary, "Invalid salary range.".$rate.'TO is from '.$minSalary.'.');
            val::numMax("pSalaryTo", $maxSalary, "Invalid salary range.".$rate.'TO is to '.$maxSalary.'.');
			
			  if ($_POST["pSalaryFrom"] >= $_POST["pSalaryTo"] ) val::setError('pSalaryFrom', 
			  'Salary From can not be greater or equivalent to Salary To.');
				
			  static::$postOverrided["pSalaryType"] = $_POST["pSalaryType"];
            static::$postOverrided["pSalaryFrom"] = $_POST["pSalaryFrom"];
            static::$postOverrided["pSalaryTo"] = $_POST["pSalaryTo"];
        }
    }

    final static function DetailsValidate($type, &$lists)
    {
        $lists["active"] = self::$active;
        val::init($type);
        self::validateName();
        self::validateSkills();
        self::validateSalary();
		 self::validateVacationDays();
		 self::validateCEDays();

        if (!isset(PositionController::$id) && val::$runat === "server") { //Fix the MySql problem with more than 1 timestamp
            self::$mainTblFields[] = self::$dt["pfx"] . "Added";
            self::$postOverrided[self::$dt["pfx"] . "Added"] = "NOW()";
            self::$postOverridedSkipEscape[] = self::$dt["pfx"] . "Added";

            self::$mainTblFields[] = self::$dt["pfx"] . "Updated";
            self::$postOverrided[self::$dt["pfx"] . "Updated"] = "NOW()";
            self::$postOverridedSkipEscape[] = self::$dt["pfx"] . "Updated";
        }
		  
        val::throwServerRunError();
		 
		 
    }
	
	public static function getJSONDaysOFF(){
		$q="SELECT * FROM positions ";
		$pos=db::PrepareLoad($q);
		$res=array();
		if (!empty($pos)){
			foreach ($pos as $k=>$r){
				$res[$r['pID']]=array('vacdays'=>$r['pVacationDays'],'cedays'=>$r['pCEDays']);
			}
		}
		
		return json_encode ($res);
	}
	
	private static function validateVacationDays()
    {
		val::required("pVacationDays", "Please, enter vacation days.");
		val::numMax("pVacationDays",60,"Please enter a valid number between 1 and 60 ");
		val::numMin("pVacationDays",0,"Please enter a valid number between 0 and 60 ");
		val::isInt("pVacationDays");
	}
	
	
	private static function validateCEDays()
    {
		val::required("pCEDays", "Please, enter Continuing Education (CE) days.");
		val::numMax("pCEDays",100,"Please enter a valid number between 1 and 100 ");
		val::numMin("pCEDays",0,"Please enter a valid number between 0 and 100 ");
		val::isInt("pCEDays");
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
		$data = val::sortArray($data);
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
