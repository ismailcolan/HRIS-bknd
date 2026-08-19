<?php
/** The Orders model */
class EmployeeModel extends ApplyFormModel {

    static $dataAddons = array(), $dt = array("tbl"=>"apply_forms", "key"=>"afID", "pfx"=>"af", "controller"=>"EmployeeController");

    static function adminPageTitleWithLinks($links = null){
		
        $C = self::$dt["controller"];
	
		 if(in_array($C::$rq, array("view-docs","delete-employee","hired",'employees-terminated', "inactive-hired", "manager-hired"))){
            $title = $C::$pg[$C::$rq]["title"];
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else if($C::$rq==="summary"){
            $title = (filter_has_var(INPUT_GET, "id") ? $C::$data[self::$dt["pfx"]."FirstName"]. " " . $C::$data[self::$dt["pfx"]."LastName"] : cf::end(__METHOD__));
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else if($C::$rq==="password"){
            $title = (filter_has_var(INPUT_GET, "id") ? "Set password for " . $C::$data[self::$dt["pfx"]."FirstName"]. " " . $C::$data[self::$dt["pfx"]."LastName"] : cf::end(__METHOD__));
            $links = AccessModel::AdminPageHeaderLnk($C);
			  }
		  else if(isset($_GET['list']) && $_GET['list']='emp' ){
            $title = (filter_has_var(INPUT_GET, "id") ? "List Employess of Manager: " . $C::$data[self::$dt["pfx"]."FirstName"]. " " . $C::$data[self::$dt["pfx"]."LastName"] : "Add ".$C::$pg[$C::$rq]["title"]);
			  $_SESSION['manager-of-employees']= $C::$data[self::$dt["pfx"]."FirstName"]. " " . $C::$data[self::$dt["pfx"]."LastName"];
        } else {
			if (filter_has_var(INPUT_GET, "id")) $crcl='<span style="position:relative;top:-4px">'.MyEmployeesController::isEmplOnWorkToday($C::$data).'</span>'; else $crcl='';
			
            $title = (filter_has_var(INPUT_GET, "id") ? "Editing: " .$crcl. '<a style="font-weight:bold" href="admin/employee-details?id='.$_GET['id'].'">'.$C::$data[self::$dt["pfx"]."FirstName"]. " " . $C::$data[self::$dt["pfx"]."LastName"].'</a>' : "Add ".$C::$pg[$C::$rq]["title"]);
        }
		 
        return array($title, $links);
    }
	
	
	public static function showConnected($data){
	
		if ($data['afConnectedToProfile']==' ') return '';
			$profiles=explode('|',$data['afConnectedToProfile']);
			$txt='';
			$nmb = count($profiles);
			$i=0;
			foreach ($profiles as $profile){
				$i++;
				if (!is_numeric($profile)) continue;
				if ($profile == $data['afID']) {
					if ($i == $nmb) $txt.='.';
					continue;
				}
				
				$currently=EmployeeModel::whatIs($profile);
				
				$txt1='';
				if($currently == 'Terminated'){
				$txt1 ='<a target="_blank" 
				 href="employee-details?id='.$profile.'&type=terminated">'
				.$data["afFirstName"].' '.$data["afLastName"]." (ID{$profile})"
				.', former employee</a>';
				
				}
				
			  if($currently == 'Hired'){
				$txt1 ='<a target="_blank" 
				 href="employee-details?id='.$profile.'">'
				.$data["afFirstName"].' '.$data["afLastName"]." (ID{$profile})"
				.', current employee</a>';
				
			  }
			  
			  if ($txt1 !=''){
			  if ($txt =='') $txt.=$txt1;
			  else $txt.=', '.$txt1;
			  }
			  if ($i == $nmb) $txt.='.';
			}
			
			if ($data['afActive']=='Terminated') $head='Connected profiles: ';
			else $head='Connected profiles: ';

			return form::html('<div class="fld form-group">
				<label class="control-label col-sm-4 col-md-3"></label>
				<div class="col-sm-7 col-md-5 col-lg-5">'
				.$head
				.$txt.
				'</div></div>');

	}
	
	public static function getFormerIds($id){
		$q='SELECT * FROM apply_forms WHERE afID='.$id;
		$res=db::PrepareLoadSingleRow($q);
		return $res['afConnectedToProfile'];
	}
	
	public static function whatIs($id) {
		$q='SELECT * FROM apply_forms WHERE afID='.$id;
		$res=db::PrepareLoadSingleRow($q);
		return $res['afActive'];
	}
	
	public static function isHired($id) {
		$q='SELECT * FROM apply_forms WHERE afID='.$id;
		$res=db::PrepareLoadSingleRow($q);
		if (empty($res) || $res['afActive'] != 'Hired') return false;
		else return true;
	}
	
	
	public static function getManagersExceptThis($id){
		$today = new DateTime();
		$q = 'SELECT *,concat(afFirstName," ",afLastName) as afFullName FROM 
		     apply_forms WHERE afIsManager = "yes"  AND afActive = "Hired" AND  afId <> '.$id.' 
			  AND afDateLeaving IS NULL  
			  ORDER by afFirstName ASC,afLastName ASC';
			  // OR afDateLeaving > "'.$today->format('Y-m-d').'" )
		  
		$res = db::PrepareLoad($q);
		//cf::pr($res);
		$ret = array();
		$ret[MANAGER_ID]= MANAGER_NAME;
		if (! empty($res)){
			foreach ($res as $k=>$r){
				$ret[$r['afID']]=trim($r['afFullName']);
			}
		}
		return $ret;
	}
	
	public static function getEmplyeesExceptThis($id){
		$today = new DateTime();
		$q = 'SELECT *,concat(afFirstName," ",afLastName) as afFullName FROM apply_forms WHERE 
			  afActive = "Hired" and afId <> '.$id.' 
			  AND afDateLeaving IS NULL 
			  ORDER by afLastName ASC';
			  //OR afDateLeaving > "'.$today->format('Y-m-d').'"
		$res = db::PrepareLoad($q);
		$ret = array();
		$ret[EMPLOYEE_THEMSELF_ID]='---'. EMPLOYEE_THEMSELF_NAME.'---';
		$ret[MANAGER_ID]= MANAGER_NAME;
		foreach ($res as $k=>$r){
			$ret[$r['afID']]=trim($r['afFullName']);
		}
		return $ret;
	}

    public static function AllActiveKeyName() {
        $data = db::LoadKeyValueArrFromTable(self::query(" afActive='Hired' "), self::$dt["key"], self::$dt["pfx"]."Name");
        if($data === false) { $data = []; }
        return $data;
    }

    public static function StatusCond($orderType){
		  $d=new DateTime();
		  $today = $d->format('Y-m-d');
        switch ($orderType) {
            case "hired" :
                return  " afID>1 and afActive='Hired'
				AND (afDateLeaving IS NULL OR (afDateLeaving IS NOT NULL and afDateLeaving > '{$today}')) ";//afHiredForPID<>9999";
			   case "employees-terminated" :
                return "`" . self::$dt["pfx"] . "Active`='Terminated' 
				    or (afDateLeaving IS NOT NULL AND afDateLeaving <='".$today."' ) ";
            case "inactive-hired" :
                return "`" . self::$dt["pfx"] . "Active`='Inactive'";
			/**********
            case "manager-hired" :
                return "`" . self::$dt["pfx"] . "Active`='Hired' and afID>1 and afIsManager='yes' 
				   AND (afDateLeaving IS NULL OR (afDateLeaving IS NOT NULL and afDateLeaving > '{$today}')) ";
			*******/
            default :
                return "`" . self::$dt["pfx"] . "Active`='hired'";
        }
    }
	
	public static function GeAllResponsibleForEmployees($managerID) {
        $data = self::data('afRMID='.intval($managerID));
        return $data;
    }

    public static function GetActiveResponsibleForEmployees($managerID) {
        $data = self::data('afActive="Hired" and afRMID='.intval($managerID));
        return $data;
    }
	
	public static function listOnboardingIAmResponsibleFor($id){
		$q='SELECT * FROM onboarding_tasks WHERE otAFID='.$id;
		$res = db::PrepareLoad($q);
		return $res;
	}
	
	public static function IncompleateOnboardingIAmResponsibleFor($id){
		$emp_themself=EMPLOYEE_THEMSELF_ID;
		$now=date('Y-m-d').' 00:00:00';
		/******
		$q="SELECT * FROM onboarding_tasks 
		LEFT JOIN  employee_onboarding ON otID = eoOTID 
		LEFT JOIN  apply_forms ON afID = eoOTID 
		WHERE afActive = 'Hired' AND (otAFID={$id} AND  eoTimeFrame < '{$now}' AND eoCompletedOn IS NULL)
		OR (eoAFID ={$id} AND otAFID={$emp_themself}  AND eoTimeFrame < '{$now}' AND eoCompletedOn IS NULL)";
		*********/
		
		$q="SELECT * FROM onboarding_tasks 
		LEFT JOIN  employee_onboarding ON otID = eoOTID 
		LEFT JOIN  apply_forms ON afID = eoOTID 
		WHERE afActive = 'Hired'
		AND (eoAFID ={$id} AND otAFID={$emp_themself}  AND eoTimeFrame < '{$now}' AND eoCompletedOn IS NULL)";
		
		$res = db::PrepareLoad($q);
		if($res==false) $res=array();
		return $res;
	}
	
	public static function validateTerminateEmployee($where,$imediatelyThrow = false){
		val::$runat=$where;
       if ($where == 'client'){
			val::required('afDateLeaving', "Please fill out the Date Leaving");
			val::required('afTerminatedOrRetired', "Please fill out the Terminated or Retired");
		}
		else {
			if (val::validDate('afDateLeaving', "Invalid Date Leaving format. Valid example: 12/30/1990"))
			{
			
				$date = DateTime::createFromFormat(USER_DATE,$_POST['afDateLeaving']);
				$today = new DateTime();
				$after3monts = new DateTime(); 
				$after3monts->add(date_interval_create_from_date_string('3 months'));
				if (isset(EmployeeController::$data['afActive']) && EmployeeController::$data['afActive']=='Terminated' ){
					if ($date->format('Y-m-d') > $today->format('Y-m-d') ){
					val::setError('afDateLeaving','The date of terminated employee can\'t be in the future.');
				}
				}
				else{
				if ($date->format('Y-m-d') > $after3monts->format('Y-m-d') ){
					val::setError('afDateLeaving','The date may not be later than 3 monts.');
				}
				
				if ($date->format('Y-m-d') < EmployeeController::$data['afDateHired']){
				///$today->format('Y-m-d') 
					val::setError('afDateLeaving','The date can\'t be in before date hired.');
				}
				}
				
				
				
				self::$postOverrided['afDateLeaving'] = $date->format('Y-m-d');
			}
			if (isset($_POST['afNewManager']) && $_POST['afNewManager'] == '{!--Empty--!}') {
				val::setError('afNewManager','Please select new manager.');
			}
			
			if (isset($_POST['afNewResponsible']) && $_POST['afNewResponsible'] =='{!--Empty--!}' ){
				val::setError('afNewResponsible','Please select new responsilble employee.');
			}
			
			
			if ($imediatelyThrow) val::throwServerRunError();
			
			
		}
    }

}
