<?php
/** The Orders model */
class ApplyFormModel extends AppModel {
    
    
    static $dataAddons = array(), $dt = array("tbl"=>"apply_forms", "key"=>"afID", "pfx"=>"af", "controller"=>"ApplyFormController");
    static $mainTblFields = array("afJID", "afPID", "afApplyAsEmployeeID", 
		 "afFirstName", "afLastName", "afCellPhone", "afEmcContact", "afHomePhone", 
		 "afEmail", "afSkills", "afBirthday", "afDesiredSalary", "afDesiredSalaryType",
        "afIP", "afRID", "afComments",  "afActive", "afHiredForPID", "afDID", "afCOID", "afRMID", 
		 "afStartDate", "afDateHired","afSalary", "afSalaryType", "afCompany", "afToken", "afKey","afIsManager",
        "afShortResume", "afSpecialSkills", "afWorkPhone", "afWorkPhoneExt", "afEmplNumber",
		 "afGender","afEmergencyPhone","afEmergencyRelationship","afZip","afMaritalStatus",
		 "afAddressLine1","afAddressLine2","afCity","afState",
		 "afSSNmb","afNoPayrollJA",
		 "afQuestionnaire",
		 'afNYSMedLicenseNum','afNYSMedicalLicenseExpDate','afNPINum','afCAQHNum',
		 'afDateLeaving','afLeavingReason','afTerminatedOrRetired',
		 "afVacationDays","afCEDays","afVacationDaysLastChanged",
		 "afIsHRManager","afIsInterviewer"
		 );

    static $fldLbl = array("afJID"=>"Desired Job", "afPID"=>"Desired Position", "afHiredForPID"=>"Position", "afFirstName"=>"First name", "afLastName"=>"Last name", "afCellPhone"=>"Cell phone", "afEmcContact"=>"Emergency Contact",
        "afHomePhone"=>"Home phone", "afEmail"=>"E-mail", "afSkills"=>"Skills", "afBirthday"=>"Date of birth", "afDesiredSalary"=>"Desired salary", "afDesiredSalaryType"=>"Desired salary type", "afActive"=>"Status", 
		  "afAdded"=>"Applied",
        "afRating"=>"Rating", "afRID"=>"Rating", "afComments"=>"Notes", "afIP"=>"Applied from IP", "afBio"=>"Application CV", "afCurrentCV"=>"Current CV", "afDID"=>"Department", "afCOID"=>"Company office", "afRMID"=>"Manager",
        "afStartDate"=>"Start Date","afDateHired"=>"Date Hired","afSalary"=>"Salary", "afSalaryType"=>"Salary type", "afCompany"=>"Company", "afHiredForPID"=>"Position", "afResponsibleForPID"=>"Position responsible for",
        "afResponsibleForCOID"=>"Offices responsible for", "afWorkPhone"=>"Work phone", "afWorkPhoneExt"=>"Work phone ext.", "afEmplNumber"=>"Employee number",
        "afSpecialSkills"=>"Skills and Hobbies", "afShortResume"=>"Short resume",
		 "afIsManager"=>"IsManager", 'afGender'=>'Gender',
		 "afMaritalStatus"=>"Marital Status",
		 "afAddressLine1"=>"Addres Line 1",
		 "afAddressLine2"=>"Addres Line 2",
		 "afZip"=>"Zip Code",
		 "afCity"=>"City",
		 "afState"=>"State",
		 "afEmergencyPhone"=>"Emergency Phone","afEmergencyRelationship"=>"Emergency Relationship",
		 "afSSNmb"=>'SSN',

		'afNYSMedLicenseNum' => 'NYS Medical License Number',
		'afNYSMedicalLicenseExpDate' => 'NYS Medical license exp. date',
		'afNPINum' => 'NPI Number',
		'afCAQHNum' => 'CAQH Number',
		'afDateLeaving'=>'Date Leaving',
		'afLeavingReason'=>'Reason',
		'afTerminatedOrRetired'=>'Terminated or Retired',
		"afVacationDays"=>"Annual paid days Vacation",
		"afCEDays"=>"Annual paid days CE"
		 );
		 
    static $addonFieldsByTables;
    static $postOverrided = array();
	
	
	const IMG_PATH = _IMG_PATH;
	const BIO_PATH = _BIO_PATH;
	
    const IMG_FILENAME = "{---LiveID---}.jpg";
    const BIO_FILENAME = "Application_CV_{---LiveID---}.doc";
    const CURRENTCV_FILENAME = "Current_CV_{---LiveID---}.doc";

    public static function validateAddressLine1($name = "afAddressLine1"){
        val::required($name,"Please enter ".self::$fldLbl[$name]);
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::maxLength($name, 100);
    }
	
	public static function validateSSN($name = "afSSNmb"){
        val::required($name,"Please enter ".self::$fldLbl[$name]);
		 val::regExp($name, "JSVAL_RX_SSN", "Please follow the format.");
        val::maxLength($name, 11);
      
    }
	
	public static function validateZIP($name = "afZip"){
        val::required($name,"Please enter ".self::$fldLbl[$name]);
		 val::minLength($name, 5, "Please enter exactly 5 digits.");
        val::maxLength($name, 5);
      
    }
	
	public static function validateRequiredField($name = "afCity"){
        val::required($name,"Please enter ".self::$fldLbl[$name]);
	
    }
	
	
	public static function validateVacationDays($name = "afVacationDays"){
		
		if(
			!isset($_POST[$name]) || ( empty($_POST[$name]) && $_POST['afHiredForPID'] == '{!--Empty--!}' )
		) return true;
		
		//val::required($name,"Please enter ".self::$fldLbl[$name]);
		if (val::$runat=='server'){
			if ($name == "afVacationDays") $max=60; else $max=100;
			$min=0;
			if ($name == "afCEDays"){
				if ($_POST['afNoPayrollJA']=='yes' || $_POST['afSalaryType']=='Hourly') $min=0;
				else $min=0;
			}
		
        if (!empty($_POST[$name]) && $name == "afVacationDays"){ 
            val::isInt($name);
            val::numMax($name,$max);
            val::numMin($name,$min);
		}
        }
    }
	
	
	
	public static function validateCity($name = "afCity"){
        val::required($name,"Please enter ".self::$fldLbl[$name]);
      
    }
	
	public static function validateEmplNmb($name ="afEmplNumber"){
		if (!isset($_POST['afEmplNumber'] ) || trim($_POST['afEmplNumber'] == '') ) return true;
		else {
			$q="SELECT * FROM apply_forms WHERE afID<>{$_GET['id']} AND afEmplNumber ='{$_POST['afEmplNumber']}'";
			$res=db::PrepareLoad($q);
			
			if ($res == false) return true;
			else {
				val::setError($name, 'Employee number must be unique. There is a employee with number '.$_POST['afEmplNumber'].' .');
				
				return false;
			}
		}
	}
	
    public static function validateName($name = "afFirstName"){
        val::required($name,"Please enter ".self::$fldLbl[$name]);
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::maxLength($name, 50);
    }
	
	 public static function validateNameNoReq($name = "afFirstName"){
       
		if(filter_has_var(INPUT_POST, $name)) {
            $val = filter_input(INPUT_POST, $name);
            if(!empty($val)) {
                val::minLength($name, 2, "Please add at least 2 characters.");
				   val::maxLength($name, 50);
            }
        }
    }
	
	

    public static function validateCellPhone($name = "afCellPhone"){
        val::required($name,"Please enter a valid ".self::$fldLbl[$name]." number");
        val::regExp($name, "JSVAL_RX_TEL", "Please enter the phone number in format 'xxx-xxx-xxxx'");
    }
	
	

    public static function validateHomePhone($name = "afHomePhone"){
	
        //val::required($name,"Please enter ".self::$fldLbl[$name]." number");
        if(filter_has_var(INPUT_POST, $name)) {
            $val = filter_input(INPUT_POST, $name);
            if(!empty($val)) {
                val::regExp($name, "JSVAL_RX_TEL", "Please enter the phone number in format 'xxx-xxx-xxxx'");
            }
        }
    }

    protected static function validateMail($name = "afEmail")
    {
		
        val::required($name,"Please enter a valid ".self::$fldLbl[$name]." address");
        val::regExp($name, "JSVAL_RX_EMAIL", "Please enter a valid e-mail address.");
        val::maxLength($name, 100);
    }

    public static function validateBio($name = "afBio", $isRequired = true)
    {
        if(isset($_FILES[$name])) {
            if(!empty($_FILES[$name]) and (!empty($_FILES[$name]["name"]))) {
                $allowed =  array('doc', 'docx', 'pdf');
                $filename = $_FILES[$name]['name'];
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if(!in_array($ext,$allowed) ) {
                    val::setError($name, 'Invalid file format.');
                    return false;
                }
            } else {
                if($isRequired) {
                    if($name !== "afCurrentCV") {
                        if($_SERVER['REQUEST_METHOD'] !== 'GET') {
                            // var_dump($_FILES);
                            val::setError($name, "Please upload a Resume.");
                            return false;
                        }
                    }
                }
            }    
        }
        return true;
    }

    private static function validateSkills($name = "afSkills")
    {
        if(filter_has_var(INPUT_POST, $name)) {
            $userSkills = filter_input(INPUT_POST, $name, FILTER_DEFAULT, FILTER_FORCE_ARRAY);
            $userSkillnames = [];
            $skills = SkillModel::AllActiveKeyName();
            foreach ($skills as $sID => $skill) {
                if(in_array($sID, $userSkills)) {
                    $userSkillnames[] = $skill;
                }
            }
            self::$postOverrided["afSkills"] = implode("|", $userSkillnames);
        } else {
            //val::setError($name, "Select at least one skill");
        }
        return true;
    }
	
	public static function validateNYSMedicalLicenseExpDate($name = "afNYSMedicalLicenseExpDate", $validateAsEmployee = true)
	{
	   //if (filter_input(INPUT_POST, $name) == '') return true;
		$lbl = self::$fldLbl[$name];
	   $mydate=filter_input(INPUT_POST, $name);
	  
	   if($mydate !=''){
		   
		   if(val::validDate($name)){
			   
				$date = DateTime::createFromFormat(USER_DATE, filter_input(INPUT_POST, $name));
				
				if($date === false) {
					$date = DateTime::createFromFormat(GLOBAL_PHP_DATE_ALT, filter_input(INPUT_POST, $name));
				}
				
				if($name === "afNYSMedicalLicenseExpDate" && $validateAsEmployee) {
					$today = new DateTime();
					$today->setTime(0, 0, 0);
					if($date < $today) {
						val::setError($name, "The date can not be in the past.");
					}
				}
				
				self::$postOverrided[$name] = $date->format(MYSQL_DATE);
				
			}
						
	   }
	   
        
    }
	
	
	public static function validateStartDate($name = "afStartDate", $validateAsEmployee = false)
    {  
	    if (isset($_POST['comming_from']) && $_POST['comming_from']== 'my_profile') return true;
		 		 
        $lbl = strtolower(self::$fldLbl[$name]);
        if(val::required($name,"Please enter a valid ".self::$fldLbl[$name]." in format [".USER_DATE."]")
			&&
		    val::required('afDateHired',"Please enter a valid ".self::$fldLbl['afDateHired']." in format [".USER_DATE."]")
		 ) {
          
			if(
			   val::validDate($name, "Invalid Start Date. Valid Start Date example: 12/30/1990")
				&& 
				val::validDate('afDateHired', "Invalid Date Hired. Valid Date Hired example: 12/30/1990") 	
			) {
                $date = DateTime::createFromFormat(USER_DATE, filter_input(INPUT_POST, $name));
				   
                if($date === false) {
                    $date = DateTime::createFromFormat(GLOBAL_PHP_DATE_ALT, filter_input(INPUT_POST, $name));
                }
               
                
                    $today = new DateTime();
                    if (!(isset($_POST['comming_from']) && $_POST['comming_from']=='edit')) {
							if($date->format('Y-m-d') < $today->format('Y-m-d')) {
								val::setError($name, "The date can not be in the past.");
							}
						}
						
						/*********  Start and Hired validation ******/
						 $dateHired = DateTime::createFromFormat(USER_DATE, filter_input(INPUT_POST, 'afDateHired'));
					    $dateStart = DateTime::createFromFormat(USER_DATE, filter_input(INPUT_POST, 'afStartDate'));
						
					   if ($dateHired->format('Y-m-d') > $dateStart->format('Y-m-d')){
						  
						   val::setError($name, "Start Date cannot be before Date Hired.");
					   }
                
				  
						self::$postOverrided[$name] = $date->format('Y-m-d');
                				  
                self::$postOverrided[$name] = $date->format('Y-m-d');
            }
			   else 
				if (!val::validDate($name, "Invalid {$lbl}.Valid {$lbl} example: 12/30/1990"))    
			   val::setError($name, "Invalid format");
			 
        }
    }
	
	public static function validateDateHired($name = "afDateHired", $validateAsEmployee = false)
    {  
	    if (isset($_POST['comming_from']) && $_POST['comming_from']== 'my_profile') return true;
        $lbl = strtolower(self::$fldLbl[$name]);
        if(val::required($name,"Please enter a valid ".self::$fldLbl[$name]." in format [".USER_DATE."]")
			&&
		    val::required('afStartDate',"Please enter a valid ".self::$fldLbl['afStartDate']." in format [".USER_DATE."]")
		 )
		{
          
			if(val::validDate($name, "Invalid {$lbl}.Valid {$lbl} example: 12/30/1990")
				&& 
				val::validDate('afStartDate', "Invalid {$lbl}.Valid {$lbl} example: 12/30/1990")
			) {
                $date = DateTime::createFromFormat(USER_DATE, filter_input(INPUT_POST, $name));
				
				   $startDate = DateTime::createFromFormat(USER_DATE, filter_input(INPUT_POST, 'afStartDate'));
                if($date === false) {
                    $date = DateTime::createFromFormat(GLOBAL_PHP_DATE_ALT, filter_input(INPUT_POST, $name));
                }
		 
				   if ($date->format('Y-m-d') 
					   > $startDate->format('Y-m-d'))
					   val::setError($name, "Date hired can't be after start date.");
                
				  
                self::$postOverrided[$name] = $date->format('Y-m-d');
          }
			else 
				if (!val::validDate($name, "Invalid {$lbl}.Valid {$lbl} example: 12/30/1990")) 
					val::setError($name, "Invalid format");
			
        }
    }
	

    public static function validateBirthday($name = "afBirthday", $validateAsEmployee = false)
    {  
	
	    //if (isset($_POST['comming_from']) && $_POST['comming_from']== 'my_profile') return true;
        $lbl = strtolower(self::$fldLbl[$name]);
        if(val::required($name,"Please enter a valid ".self::$fldLbl[$name]." in format [".USER_DATE."]")) {
          
			if(val::validDate($name, "Invalid {$lbl}.Valid {$lbl} example: 12/30/1990")) {
				   
                $date = DateTime::createFromFormat(USER_DATE, filter_input(INPUT_POST, $name));
				   
                if($date === false) {
                    $date = DateTime::createFromFormat(GLOBAL_PHP_DATE_ALT, filter_input(INPUT_POST, $name));
                }
                if($name === "afBirthday") {
                    $today     = new DateTime();
                    $interval  = $today->diff($date);
					   if ($date->format('Y-m-d') > $today->format('Y-m-d')){
						   val::setError($name, "Birth date can not be in future.");
					   }
                    if($interval->format("%y") < 18) {
                        val::setError($name, "Employee must be at least 18 years of age.");
                    }
                }
                if($name === "afStartDate" && !$validateAsEmployee) {
                    $today = new DateTime();
                    $today->setTime(0, 0, 0);
                    if($date < $today) {
                        val::setError($name, "The date can not be in the past.");
                    }
                }
				   
                self::$postOverrided[$name] = $date->format('Y-m-d');
            }
			 else val::setError($name, "Invalid format");
			 //<<<self::$postOverrided['afBirthday'] = $_POST['afBirthday'];
        }
    }
	
	


    public static function validateSalary($position, $name = "afDesiredSalary"){
//<<<<<<<<????
        if($name === "afSalary") {
            $type = filter_input(INPUT_POST, "afSalaryType", FILTER_SANITIZE_STRING);
            if ($type === "Hourly") {
                $minSalary = 1;
                $maxSalary = 999;
            } else {
                $minSalary = 1000;
                $maxSalary = 1000000;
            }
        } else {
            if(!isset($position["pID"])) {
                $position["pID"] = -1;
            }
            if($position["pID"] < 1) {
                return true;
            }
            $minSalary = $position["pSalaryFrom"];
            $maxSalary = $position["pSalaryTo"];
        }
		
		 if (isset($_POST['afNoPayrollJA']) && $_POST['afNoPayrollJA']=='yes' ) $minSalary = 0;

        val::required($name, "Please fill out the field.");
        val::regExp($name, "JSVAL_RX_MONEY_COMMAS", "Please use valid number format (1,234.56)");

        $_POST[$name] = str_replace([",", " "], "", filter_input(INPUT_POST, $name));

        val::numMin($name, $minSalary, "Invalid salary. Must be greater or equal to ".
		 number_format($minSalary, 2, ".", ",").".");
        val::numMax($name, $maxSalary, "Invalid salary. Must be lower or equal to ".
		 number_format($maxSalary, 2, ".", ",").".");;

        static::$postOverrided[$name] = $_POST[$name];
        if($name === "afDesiredSalary") {
            static::$postOverrided["afDesiredSalaryType"] = $position["pSalaryType"];
        }
    }

    public static function validateHiredSalary(){
		/***********
        if(!filter_has_var(INPUT_POST, "afSalaryType")) {
		     val::setError("afSalaryTypeErr", "Please, select salary type.");
            return false;
        }
		*********/
        if(filter_has_var(INPUT_POST, "afHiredForPID")) {
            $position = PositionModel::dataById(filter_input(INPUT_POST, "afHiredForPID", FILTER_SANITIZE_NUMBER_INT));
        } else {
            $position = PositionModel::dataById(filter_input(INPUT_POST, "afPID", FILTER_SANITIZE_NUMBER_INT));
        }
        $position["pSalaryType"] = filter_input(INPUT_POST, "afSalaryType", FILTER_SANITIZE_STRING);
        return self::validateSalary($position, "afSalary");
    }

    public static function validateSelect($name)
    {
        val::required($name, "Please make your selection.", "select");
    }

    final static public function VisitorIP(){
        if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif(!empty($_SERVER['REMOTE_ADDR'])){
            return $_SERVER['REMOTE_ADDR'];
        } else {
            return 'NULL';
        }
    }
	


	
	
    final static function DetailsValidate($type, &$lists, $validateFromAdmin = false, $validateAsEmployee = false){

        $position = isset($lists["position"]) ? $lists["position"] : null;
        $lists["active"] = self::$active;
        val::init($type);

        if (!isset($_POST['comming_from'])) self::validateName("afFirstName");
        if (!isset($_POST['comming_from'])) self::validateName("afLastName");
		if (isset($_POST['comming_from'])) self::validateEmplNmb("afEmplNumber");
		
        self::validateNameNoReq("afEmcContact");
		 self::validateSSN("afSSNmb");
		
		 self::validateAddressLine1("afAddressLine1");
		 self::validateCity("afCity");
		 
		 self::validateSelect("afMaritalStatus"); 
		 self::validateSelect("afGender");
		 self::validateSelect("afState");
		 //self::validateSelectNoReq("afEmergencyRelationship");
		 
		 self::validateZIP("afZip");
		
        self::validateCellPhone("afCellPhone");
		 self::validateHomePhone("afEmergencyPhone");
        self::validateHomePhone("afHomePhone");
		
		self::validateVacationDays("afVacationDays");
		self::validateVacationDays("afCEDays");
		
		 if (isset(EmployeeController::$data['afDateLeaving'])){
			 EmployeeModel::validateTerminateEmployee($type) ;
		 }
		
		
        self::validateMail();

        if(!$validateFromAdmin) {
            self::validateSkills();
            self::validateBio();
            self::validateBio("afCurrentCV", false);
        }

		 self::validateBirthday('afBirthday',$validateAsEmployee);
		 val::required('afBirthday',"Please enter a valid ".self::$fldLbl['afBirthday']." in format [".USER_DATE."]");
       
	   if (isset($_POST['comming_from'])) self::validateStartDate("afStartDate", $validateAsEmployee);
		if (isset($_POST['comming_from'])) self::validateDateHired("afDateHired", $validateAsEmployee);
		//self::validateNYSMedicalLicenseExpDate('afNYSMedicalLicenseExpDate',$validateAsEmployee);
		
			
        if(!$validateFromAdmin) { self::validateSalary($position, 'afDesiredSalary'); }
        else {
            if(isset($lists['data'])) {
                static::$postOverrided['afDesiredSalary'] = $lists['data']['afDesiredSalary'];
            }
            if($validateAsEmployee) {
                self::validateHiredSalary();
            }
        }

        if(!isset(PositionController::$id) && val::$runat==="server") { //Fix the MySql problem with more than 1 timestamp
            self::$mainTblFields[] = self::$dt["pfx"] . "Added";
            self::$postOverrided[self::$dt["pfx"] . "Added"] = "".val::currentDate('Y-m-d H:i:s').""; //"NOW()";
           // self::$postOverridedSkipEscape[] = self::$dt["pfx"] . "Added";
        } else {
            self::$mainTblFields[] = self::$dt["pfx"]."Updated";
            self::$postOverrided[self::$dt["pfx"]."Updated"] = "".val::currentDate('Y-m-d H:i:s').""; //"NOW()";
           // self::$postOverridedSkipEscape[] = self::$dt["pfx"]."Updated";
        }
        if(!$validateFromAdmin) { self::$postOverrided["afIP"] = self::VisitorIP(); }

        $active = filter_input(INPUT_POST, self::$dt["pfx"] . "Active");
        if($active === null) {
            if(isset($lists['data'])) {
                $active = $lists['data'][self::$dt["pfx"] . "Active"];
            }
        }
		
			
        
        if($active === "Hired") {
			
            self::validateHiredSalary();
            self::validateBirthday("afBirthday", $validateAsEmployee);
            self::validateSelect("afHiredForPID");
            self::validateSelect("afDID");
            self::validateSelect("afCOID");
            self::validateSelect("afRMID");
            self::validateSelect("afCompany");
			  self::validateDateHired("afDateHired",true);
			  self::validateDateHired("afStartDate",true);
			  
        }
		
		 
		
        val::throwServerRunError();
		
    }
    

    static function dataById($id = 0, $allLangs = false)
    {
        $data = parent::dataById($id, $allLangs);
        if($data !== null) {
            $data["afResponsibleForPID"] = array_keys(ManagerPositionsModel::LoadAsKeyValueArray($id));
            $data["afResponsibleForCOID"] = array_keys(ManagerOfficesModel::LoadAsKeyValueArray($id));
        }
        return $data;
    }

    static function query($conditions = '', array $orderby = array(), $limit = 0){
		
        $query = "SELECT *, concat(afFirstName, ' ', afLastName) as afName,
                        (select pName from positions where pID=afPID) afPosition,
                        (select pName from positions where pID=afHiredForPID) afHiredPosition,
                        (select jName from jobs where jID=afJID) afJob, 
                        (select dName from departments where dID=afDID) as afDepartment,
                        (select coName from company_offices where coID=afCOID) as afOffice, 
                        (SELECT CONCAT(af1.afFirstName, ' ', af1.afLastName) FROM apply_forms af1 where af1.afID=apply_forms.afRMID) AS afManager,                      
                        (select rName from ratings where rID=afRID) afRating FROM " . self::$dt["tbl"];

        $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);

        $optionalAddons = array();
        $addons = array();
        foreach(self::$dataAddons as $k){
            $addons[] = (in_array($k, array_keys($optionalAddons)) ? $optionalAddons[$k] : cf::end(__METHOD__.": Incorrect addon request call:".$k));
        }

        $addonsQry = str_replace("*", (empty($addons) ? "*" : ("*,\n".implode(",\n",$addons))), $q);

        return $addonsQry;
    }

    public static function StatusCond($orderType){
        switch ($orderType) {
            case "all" :
                return '('. self::StatusCond("manually-rejected") . ' or ' . self::StatusCond("for-interview") . ' or ' .
                    self::StatusCond("for-decision") . ' or ' . self::StatusCond("pending") . ')';

            case "manually-rejected" :
                return "`" . self::$dt["pfx"] . "Active`='Manually Rejected'";
            case "for-interview" :
                return "`" . self::$dt["pfx"] . "Active`='For interview'";
            case "for-decision" :
                return "`" . self::$dt["pfx"] . "Active`='For decision'";
            case "auto-rejected" :
                return "`" . self::$dt["pfx"] . "Active`='Auto-rejected'";

            default :
                return "`" . self::$dt["pfx"] . "Active`='Pending'";
        }
    }

    public static function AllActiveKeyName() {
        $data = db::LoadKeyValueArrFromTable(self::query(self::StatusCond("list")), self::$dt["key"], self::$dt["pfx"]."Name");
        if($data === false) { $data = []; }
        return $data;
    }

    static function ItemDeleteAllowed(){
        return false;
    }

    public static function QuestionnarieQueryToArray($pID)
    {
        $qestions = QuestionModel::getAllActiveQuestionsForPosition($pID);
        $result = [];
        if(!empty($qestions)) {
            foreach ($qestions as $q) {
                $qKey = "question".$q["qID"];
                if(filter_has_var(INPUT_GET, $qKey)) {
                    $qaKey = filter_input(INPUT_GET, $qKey, FILTER_SANITIZE_NUMBER_INT);
                    $qans = $q["answers"];

                    foreach ($qans as $ans) {
                        if($ans["aID"] === $qaKey) {
                            $rfs = $ans["rejectedFor"];
                            $rejected = false;
                            foreach($rfs as $rf) {
                                if($rf["arpPID"] === $pID) {
                                    $rejected = true;
                                    break;
                                }
                            }
                            $result[$q["qName"]] = $ans["aName"].($rejected ? "{--rejected--}":"");
                            break;
                        }
                    }
                }
            }
        }
        return $result;
    }

    public static function getActiveManagersKeyName() {
        $q = "SELECT afID, CONCAT(afFirstName, ' ', afLastName) AS afName FROM `apply_forms` where afActive='Hired' and afIsManager='yes' order by afName";
        $data = db::PrepareLoad($q);
        if($data === false) { $data = []; }
        $result = [MANAGER_ID=>MANAGER_NAME];
        foreach ($data as $item) {
            $result[$item[self::$dt["key"]]] = $item["afName"];
        }
        return $result;
    }

    public static function getActiveEmployeesKeyName($includeManagers = true, $includeAdmin = true) {
        $q = "SELECT afID, CONCAT(afFirstName, ' ', afLastName) AS afName FROM `apply_forms` where afActive='Hired' ".(!$includeManagers ? " and afHiredForPID <> 9999 " : " ")."order by afName";
     
		 $data = db::PrepareLoad($q);
        if($data === false) { $data = []; }
        if($includeAdmin) {
            $result = [MANAGER_ID=>MANAGER_NAME];
        }
		 $result=array();
        foreach ($data as $item) {
            $result[$item[self::$dt["key"]]] = $item["afName"];
        }
        return $result;
    }

    public static function UpdateAdminRecord($afID, $afPID, $afEmail, $afToken) {
        db::SwitchToRealByName();
    
        // Get position info to check if admin role is needed
        $position = db::PrepareLoadSingleRow(
            "SELECT pID, pName, pAdminRole FROM positions WHERE pID = " . (int)$afPID
        );
    
        $adminRole = $position ? $position['pAdminRole'] : null;
    
        // pID=9999 is Manager - keep existing logic
        if ($afPID == 9999) {
            $q = sprintf('SELECT * FROM webapp_admins WHERE waaAFID=%d', $afID);
            $usr = db::PrepareLoadSingleRow($q);
    
            if (isset($usr["waaAFID"])) {
                $q = sprintf(
                    'UPDATE webapp_admins SET waaUsername="%s", waaEmail="%s", waaAFID=%d WHERE waaID=%d',
                    $afEmail, $afEmail, $afID, $usr["waaID"]
                );
                db::SQL($q);
                $waaID = $usr["waaID"];
            } else {
                $q = sprintf(
                    'INSERT INTO webapp_admins (waaUsername, waaEmail, waaAFID) VALUES ("%s", "%s", %d)',
                    $afEmail, $afEmail, $afID
                );
                db::SQL($q);
                $waaID = db::LastID();
            }
    
            $mysql = db::$mysqli;
            $q = 'INSERT INTO webapp_admins_tokens (waatID, waatToken) VALUES(?, ?) 
                  ON DUPLICATE KEY UPDATE waatToken=?';
            $slmt = $mysql->prepare($q);
            $slmt->bind_param("iss", $waaID, $afToken, $afToken);
            $slmt->send_long_data(1, $afToken);
            $slmt->send_long_data(2, $afToken);
            $slmt->execute();
    
        } elseif (!empty($adminRole)) {
            // HR Manager or Interviewer - create/update admin account
    
            // Get role_id from roles table
            $roleRow = db::PrepareLoadSingleRow(
                "SELECT id FROM roles WHERE role_name = '" . db::EscapeStr($adminRole) . "'"
            );
            $roleId = $roleRow ? (int)$roleRow['id'] : 1;
    
            // Check if admin account already exists for this employee
            // $existingAdmin = db::PrepareLoadSingleRow(
            //     "SELECT * FROM webapp_admins WHERE waaAFID = " . (int)$afID
            // );

            $existingAdmin = db::PrepareLoadSingleRow(
                "SELECT * FROM webapp_admins WHERE waaAFID = " . (int)$afID . " ORDER BY waaID DESC LIMIT 1"
            );
    
            if ($existingAdmin) {
                // Update existing admin account role
                db::SQL(
                    "UPDATE webapp_admins 
                     SET waaEmail = '" . db::EscapeStr($afEmail) . "',
                         waaUsername = '" . db::EscapeStr($afEmail) . "',
                         waaRole = 'Admin',
                         role = '" . db::EscapeStr($adminRole) . "',
                         role_id = $roleId
                     WHERE waaAFID = " . (int)$afID
                );
            } else {
                // Create new admin account - username = email
                $username = $afEmail;
    
                // Ensure username is unique
                $usernameCount = (int)db::PrepareLoadSingleCellValue(
                    "SELECT COUNT(*) FROM webapp_admins 
                     WHERE waaUsername = '" . db::EscapeStr($username) . "'"
                );
                if ($usernameCount > 0) {
                    $username = $afEmail . '_' . $afID;
                }
    
                db::SQL(
                    "INSERT INTO webapp_admins 
                     (waaUsername, waaEmail, waaAFID, waaRole, role, role_id, waaRegDate) 
                     VALUES (
                        '" . db::EscapeStr($username) . "',
                        '" . db::EscapeStr($afEmail) . "',
                        " . (int)$afID . ",
                        'Admin',
                        '" . db::EscapeStr($adminRole) . "',
                        $roleId,
                        NOW()
                     )"
                );
                $waaID = db::LastID();
    
                // Create token placeholder - will be filled when admin sets password
                $salt = cf::saltAnWord($afEmail);
                db::SQL(
                    "INSERT INTO webapp_admins_tokens (waatID, waatSalt, waatToken) 
                     VALUES ($waaID, '" . db::EscapeStr($salt) . "', '')"
                );
            }
    
        } else {
            // Any other position - remove admin access if it exists
            // (but only if not the main admin - waaID=1 or role='admin')
            $existingAdmin = db::PrepareLoadSingleRow(
                "SELECT * FROM webapp_admins 
                 WHERE waaAFID = " . (int)$afID . " AND role != 'admin'"
            );
            if ($existingAdmin) {
                db::SQL(
                    "DELETE FROM webapp_admins_tokens 
                     WHERE waatID = " . (int)$existingAdmin['waaID']
                );
                db::SQL(
                    "DELETE FROM webapp_admins WHERE waaAFID = " . (int)$afID . " AND role != 'admin'"
                );
            }
        }
    }

    public static function getActiveEmployeesHiredFor($pID) {

        $data = self::data('afActive = "Hired" and afHiredForPID in ('.$pID.')');
        if(empty($data)) {
            $data = [];
        }
        return $data;
    }
}
