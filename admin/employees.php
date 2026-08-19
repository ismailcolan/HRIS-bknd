<?php

/** The Offices (admin) class */
class Employees extends PageAdmin
{
    const CONTROLLER = "EmployeeController";

    public function __construct($id = 0, $C = self::CONTROLLER)
    {
        parent::__construct();

        $M = App_AdminModel::GetModel($C);

        $C::$id = !in_array($C::$rq, array("hired", "inactive-hired", "manager-hired")) && $id > 0 ? $id : null;

        $C::$data = isset($C::$id) ? $M::dataById($id) : null;

        AccessModel::GrantAccessAndActions($C);
    }

    protected function inHead($C = self::CONTROLLER)
    {
        msg::prepare();
        table::prepare();
    }

    protected function setPgRq($C = self::CONTROLLER, $pg = array("item" => "Employee", "items" => "Employees"))
    { 
        $M = App_AdminModel::GetModel($C);

        $pg["hired"] = array("url" => strtolower($pg["items"]), "title" => $pg["items"]);
		 $pg["employees-terminated"] = array("url" => "employees-terminated", "title" => "Terminated");
        $pg["inactive-hired"] = array("url" => "inactive-" . strtolower($pg["items"]), "title" => "Inactive " . strtolower($pg["items"]));
        $pg["manager-hired"] = array("url" => "manager-" . strtolower($pg["items"]), "title" => "Managers");
        $pg["details"] = array("url" => strtolower($pg["item"]) . "-details", "title" => $pg["item"]);
        $pg["summary"] = array("url" => strtolower($pg["item"]), "title" => $pg["item"]);
        $pg["password"] = array("url" => strtolower($pg["item"] . "-password"), "title" => "Set " . strtolower($pg["item"]) . " password");
        
		 $pg["delete-employee"] = array("url" => strtolower("delete-employee"), 
										"title" => "List All Applications");
		 $pg["admin-mass-task"] = array("url" => strtolower("admin-mass-task"), 
										"title" => "Admin Mass task");
										
		$pg["view-docs"] = array("url" => strtolower("view-docs"), 
										"title" => "View Document to sign");
        
		$pg["tbl"] = $M::$dt["tbl"];
        $pg["key"] = $M::$dt["key"];
        $pg["pfx"] = $M::$dt["pfx"];

        foreach ($C::$pg = $pg as $k => $v) {
			
            if (is_array($v) && isset($v["url"]) && $v["url"] === cf::$rq["url"]) {
				  
                return $C::$rq = self::$rq = $k;
            }
        }
    }

    public static function ImgsSetup()
    {
        img::$setup = array(); //Its better to reset the values
        img::$setup["path"] = ApplyFormModel::IMG_PATH;
        img::$setup["crop"] = "inline";
        img::$setup["width"] = "320";
        img::$setup["height"] = "320";
        img::$setup["disable-delete"] = true;
        img::$setup["allowed-extensions"] = array("jpg", "png");
        img::prepare();
    }

}

$adminSection = new Employees((int)filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT));


/******** ajax calls ***********/

if (isset($_GET['to_do']) && $_GET['to_do']=='tsr') {
	
if (isset($_GET['table-id']) && strpos($_GET['table-id'],'tsr__')!== false) {
			$state=str_replace('tsr__','',$_GET['table-id']);
			$state=str_replace('-table','',$state);
			$adminSection->RenderChangeRequests($state);
			exit;
}
				
if( isset($_GET['task']) && $_GET['task']=='update_request' ){
				$adminSection->UpdateRequest();
				exit;
}

}
/*******************/

if (isset($_GET['action']) && $_GET['action'] == 'upload_file') EmployeeController::uploadFile();




if ($adminSection::$rq == 'delete-employee' && isset($_GET['deid'] )){
	$adminSection->DeleteApplication();
}

if ($adminSection::$rq == 'admin-mass-task'){$adminSection->AdminMassTask();}


if (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "connect-profiles") {
        $adminSection->ConnectProfiles();
}

if (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "ask_no_more_connect") {
        $adminSection->AskNoMoreConnect();
}
if (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "check_admin_daysoff") {
        $adminSection->CheckAdminDaysOff();
		exit;
}




$adminSection->ImgsSetup();
$adminSection->Header();

if (isset($_GET['to_do']) && $_GET['to_do']=='tsr')
{ 
	$adminSection->ShowTSRequests();
	exit;
}






if (isset($_GET['action']) &&  $_GET['action']=='delete')  $adminSection->deleteFile();



if (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "save_admin_daysoff") {
        $adminSection->SaveAdminDaysOff();
}
if ($adminSection::$rq == 'delete-employee'){
	$adminSection->AllApplications();
}
else if (in_array($adminSection::$rq, array("hired", "inactive-hired", "manager-hired","employees-terminated"))) {
	
    $adminSection->All();
} else if ($adminSection::$rq === "summary") {
    $adminSection->Summary();
} else if ($adminSection::$rq === "password") {
    $adminSection->SetPassword();
} else if ($adminSection::$rq === "details") {
	if (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "terminate") {
        $adminSection->TerminateEmployee();
	}
	elseif (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "days-off") {
        $adminSection->DaysOffEmployee();
   
   }else if (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "undo-sign-doc") {
        $adminSection->UndoSignDocEmployee();
	}else 
	if (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "view-docs") {
        $adminSection->ViewDocsToSign();
    }
	else if (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "undo-terminate") {
        $adminSection->UndoTerminateEmployee();
    }
	
	else if (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "terminate-today") {
        $adminSection->TerminateTodayEmployees();
    }
    else if (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "trash") {
        $adminSection->Delete();
    } else if (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "restore") {
        $adminSection->Reactivate();
    } else if (!isset($_GET['action']) && (filter_input(INPUT_SERVER, "REQUEST_METHOD") === "POST" || $_SERVER['REQUEST_METHOD'] === 'POST')) {
       $adminSection->DetailsSave();
    } else {

        $adminSection->CheckForUndoOnboarding();

        $adminSection->Details();
    }
}


$adminSection->Footer();