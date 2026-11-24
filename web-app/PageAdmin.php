<?php 
/** The page (Admin) class */
class PageAdmin extends Page {

    protected static $timeStart = null, 
    $pageToControllerMethods = array("All","AllForManager","DetailsForManager","DetailsSaveForManager","Summary","ItemToBin","Delete","DetailsSave",
            "Details","Reactivate", "Answers", "Assignments", "AssignmentsSave", "AssignmentsCopy", "SetPassword", "PasswordSave", "CheckForUndoOnboarding", "uploadResourceFile", "addResourceLink", 
            "deleteFile","uploadFile","AllApplications","DeleteApplication","AdminMassTask",
			  "deleteFilesFromCategory","moveFilesToCategory",
			  "SetFile","Report","TerminateEmployee","DaysOffEmployee","UndoSignDocEmployee","UndoTerminateEmployee","TerminateTodayEmployees",
			  "ConnectProfiles","AskNoMoreConnect","ChangeDocState",
			  "downloadFile","removeResourceFile", "removeResourceLink", "RemoveResources","EditAdminMails","SaveAdminMails",
			  "Init","Edit","Save","ViewDocsToSign","ListUnpaidRequests",
			  "MainPayrollReport","ListReport","CheckReport",
			  "SaveAdminUNSIGN_SETTINGS","EditAdmin_UNSIGN_SETTINGS",
			  "SaveAdminBenefits","EditAdminBenefits",
			  "MainUnpaidDaysOffReport",
			  "BenefitsDeductionsDataList","CheckAdminDaysOff","SaveAdminDaysOff",
			  "delDemographics","delDeductions","NewDownload","createNewUpload",
			  
			  "isActive","EditLogo","SaveLogo",
			  "ForgotPasswordForm","SendLinkForgotPassword","ForceLogin",
			  
			  "RenderChangeRequests","UpdateRequest","ShowTSRequests"
			  );
    private static $accessFreeUrls = array("logout", "login");
    
		
    public function __construct() {
		
        parent::__construct();
		
        if(empty(AppModel::$admin)){
            if(!in_array(cf::$rq["url"], self::$accessFreeUrls)){
                cf::Redirect( ADMIN . "login" . (!empty(cf::$rq["url"]) ? "?go=".urlencode(cf::$rq["url"]) : "") );
            }
        } else {
            if(in_array(cf::$rq["url"], array("login"))){
                cf::Redirect( ADMIN . ( filter_has_var(INPUT_GET, "go") ? filter_input(INPUT_GET,"go",FILTER_SANITIZE_URL) : "home" ) );
            }
        }
		
    }
	
	public function HeaderOnly($templates=null, $v=null) {
		  $class=cf::$rq["class"];
		  cf::$rq["class"]='';
        if(self::$error404===true){
            cf::$rq["class"]=null;
        }
        App_AdminController::$rq = self::$rq;


        if(is_null($templates)){
           App_AdminController::Headers(static::CONTROLLER, $templates, $v);
        }
        parent::Header($templates, $v);
		 cf::$rq["class"]=$class;
	}
	 	 

    public function Header($templates=null, $v=null) { 
        if(self::$error404===true){
            cf::$rq["class"]=null;
        }
		
        App_AdminController::$rq = self::$rq;
		
        if(is_null($templates)){  
            App_AdminController::Headers(static::CONTROLLER, $templates, $v);
			
        }

        parent::Header($templates, $v);

        if(!is_null(($ctr=static::CONTROLLER)) && in_array($ctr::$rq, array("details","summary","answers","assignments")) && isset($ctr::$id) && empty($ctr::$data)){
            //Summary and details pages cannot be loaded with inaccurate ID.
			
            $templates[] = "admin/app-panel-wrong-id";
            App_AdminController::Footer($templates);
            die(parent::Footer($templates));
        } else if(!is_null(($ctr=static::CONTROLLER)) && ($ctr::$rq==="summary") && empty($ctr::$data)){
            //Summary pages cannot be loaded without ID.
            $templates[] = "admin/app-panel-wrong-id";
            App_AdminController::Footer($templates);
            die(parent::Footer($templates));
        } else {
			  $C = static::CONTROLLER;
			  $add ='';
			  $today = new DateTime();
			  if (@$C::$data["afActive"] == 'Hired'
					&&
				 (isset($C::$data['afDateLeaving']) &&  $C::$data['afDateLeaving'] > $today->format('Y-m-d'))
				)					
					$add = '<br><span style="color:red">(in leave notice, leaving '.val::convDate('Y-m-d',USER_DATE,$C::$data['afDateLeaving']).')</span>';
			  
			   App_AdminController::PageTitle(static::CONTROLLER,$add);
        }
		
    }
    
    public function Footer($templates=null, $v=null) {
        if(is_null($templates)){
            App_AdminController::Footer($templates);
        }

        parent::Footer($templates);
    }
    
}
