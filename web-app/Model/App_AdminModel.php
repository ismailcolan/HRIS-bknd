<?php
/** The APP_Admin model */
class App_AdminModel extends AppModel {
    
    static function NavArrays(){
        $main = $sub = array();
        $trigger  = array("main"=>array(),"sub"=>array());
        
        $main["home"] = array("Home", array("title"=>"Admin home"));
        if(in_array(cf::$rq["class"], array("home","webapp-admins", "webapp-settings", "webapp-change-password"))){
            $sub["home"] = array("Dashboard", array("title"=>"Admin dashboard"));
            //$sub["settings"] = array("Settings", array("title"=>"Admin settings"));
            $sub["password"] = array("<span class=\"fa fa-key\"></span> Password", array("title"=>"Change password"));
            $trigger["main"]["webapp-change-password"]="home";
            $trigger["main"]["webapp-settings"]="home";
            if(in_array(AppModel::$admin["waaRole"], WebappAdminModel::$AdminRolesAccessible)){
                //$sub["admins"] = array("Admin users", array("title"=>"Admins users list"));
                    $trigger["main"]["webapp-admins"]="home";
                        $trigger["sub"]["admin"]="admins";
                        $trigger["sub"]["admin-password"]="admins";
                        $trigger["sub"]["settings"]="home";
            }
        }


        $main["applications"] = array("Applications", array("title"=>"Applications"));
        if(in_array(cf::$rq["class"], array("applications"))){
            $sub["applications"] = array("Pending", array("title"=>"Pending applications list"));
            $trigger["sub"]["application"]="applications";
            $trigger["sub"]["application-details"]="applications";
            $sub["for-interview-applications"] = array("For interview", array("title"=>"Applications for interview list"));
            $sub["for-decision-applications"] = array("For decision", array("title"=>"Applications for decision list"));
            $sub["manually-rejected-applications"] = array("Manually rejected", array("title"=>"Manually rejected list"));
            $sub["auto-rejected-applications"] = array("Automatically rejected", array("title"=>"Automatically rejected list"));
        }

        $main["employees"] = array("Employees", array("title"=>"Employees"));
        if(in_array(cf::$rq["class"], array("employees")) || (isset($_GET['list']) && $_GET['list']=='emp')){
            if(EmployeeController::$data["afActive"] == 'Inactive'
			||
			 (EmployeeController::$data["afActive"] == 'Terminated' )
			)
			 {
                $trigger["sub"]["employee-details"]="inactive-employees";
            } else {
                //if(EmployeeController::$data["afHiredForPID"] == '9999') {
					//if(EmployeeController::$data["afIsManager"] == 'yes') {
                    //$trigger["sub"]["employee-details"]="manager-employees";
                //} else {
                    $trigger["sub"]["employee-details"]="employees";
                //}
            }
			/*****
			  if (isset($_GET['list'])) { 
				   $trigger["sub"]["employee-details"]="employees";
				  
				}
			******/
            $trigger["sub"]["employee-password"]="employees";
			if(cf::$rq['url'] =='delete-employee'){
            $sub["employees"] = array("Employees", array("title"=>"Active employees list"));
            $sub["manager-employees"] = array("L", array("title"=>"Active employees list"));
			//array("Managers", array("title"=>"Managers list"));
			}
			
			/*****/
			if(cf::$rq['url'] !='admin-mass-task'){
            $sub["employees"] = array("Employees", array("title"=>"Employee list"));
            //$sub["manager-employees"] = array("List employees of manager", array("title"=>"Managers list"));
			  $sub["employees-terminated"] = array("Terminated", array("title"=>"Terminated employee list"));
			}
			/*****/
			
           // $sub["inactive-employees"] = array("Inactive", array("title"=>"Inactive employees list"));
        }


        $main["questions"] = array("Questions", array("title"=>"Questions"));
        if(in_array(cf::$rq["class"], array("questions"))){
            $sub["questions"] = array("Active", array("title"=>"Active questions list"));
            $trigger["sub"]["question"]="questions";
            $trigger["sub"]["question-details"]="questions";
            $trigger["sub"]["question-answers"]="questions";
            $sub["inactive-questions"] = array("Inactive", array("title"=>"Inactive questions list"));
        }

        $main["positions"] = array("Positions", array("title"=>"Positions"));
        if(in_array(cf::$rq["class"], array("positions"))){
            $sub["positions"] = array("Active", array("title"=>"Active positions list"));
            $trigger["sub"]["position"]="positions";
            $trigger["sub"]["position-details"]="positions";
            $trigger["sub"]["position-assignments"]="positions";
            $sub["inactive-positions"] = array("Inactive", array("title"=>"Inactive positions list"));
        }

        $main["skills"] = array("Skills", array("title"=>"Skills"));
        if(in_array(cf::$rq["class"], array("skills"))){
            $sub["skills"] = array("Active", array("title"=>"Active skills list"));
            $trigger["sub"]["skill"]="skills";
            $trigger["sub"]["skill-details"]="skills";
            $sub["inactive-skills"] = array("Inactive", array("title"=>"Inactive skills list"));
        }

        $main["offices"] = array("Offices", array("title"=>"Company offices"));
        if(in_array(cf::$rq["class"], array("offices"))){
//            $sub["offices"] = array("Active", array("title"=>"Active offices list"));
//            $trigger["sub"]["office"]="offices";
//            $trigger["sub"]["office-details"]="offices";
//            $sub["inactive-offices"] = array("Inactive", array("title"=>"Inactive offices list"));
        }

        $main["departments"] = array("Departments", array("title"=>"Company departments"));
        if(in_array(cf::$rq["class"], array("departments"))){
            $sub["departments"] = array("Active", array("title"=>"Active departments list"));
            $trigger["sub"]["department"]="departments";
            $trigger["sub"]["department-details"]="departments";
            $sub["inactive-departments"] = array("Inactive", array("title"=>"Inactive departments list"));
        }
		
		$main["companies"] = array("Companies", array("title"=>"Companies"));
        if(in_array(cf::$rq["class"], array("companies"))){
            $sub["companies"] = array("Active", array("title"=>"Active companies list"));
            $trigger["sub"]["companies"]="companies";
            $trigger["sub"]["companies-details"]="companies";
            $sub["inactive-companies"] = array("Inactive", array("title"=>"Inactive companies list"));
        }
/********
        $main["managers"] = array("Managers", array("title"=>"Managers"));
        if(in_array(cf::$rq["class"], array("managers"))){
            $sub["managers"] = array("Active", array("title"=>"Active managers list"));
            $trigger["sub"]["manager"]="managers";
            $trigger["sub"]["manager-details"]="managers";
            $sub["inactive-managers"] = array("Inactive", array("title"=>"Inactive managers list"));
        }
**********/
        $main["jobs"] = array("Jobs", array("title"=>"Open Jobs"));
        if(in_array(cf::$rq["class"], array("jobs"))){
            $sub["jobs"] = array("Active", array("title"=>"Active open jobs list"));
            $trigger["sub"]["job"]="jobs";
            $trigger["sub"]["job-details"]="jobs";
            $sub["inactive-jobs"] = array("Inactive", array("title"=>"Inactive open jobs list"));
        }
		
		/***********
		 $main["payroll"] = array("Payroll", array("title"=>"Payroll"));
        if(in_array(cf::$rq["class"], array("payroll"))){
           // $sub["payroll"] = array("Active", array("title"=>"Payroll"));
           
        }
	    ************/

        $main["onboarding"] = array("Onboarding", array("title"=>"Onboarding"));
        if(in_array(cf::$rq["class"], array("onboarding"))){
            $sub["onboarding"] = array("Active", array("title"=>"Active onboarding list"));
		     $sub["inactive-onboarding"] = array("Inactive", array("title"=>"Inactive onboarding list"));
  
            $trigger["sub"]["onboarding"]="onboarding";
            $trigger["sub"]["onboarding-details"]="onboarding";
			  
			  $trigger["sub"]["onboarding-report"]="onboarding";
			  
			  if (strpos($_SERVER['REQUEST_URI'],'onboarding-report') !== false ) {
				  unset($sub);
				  $sub = array();
			  }
         }
		 
		 $main["offboarding"] = array("Offboarding", array("title"=>"Offboarding"));
        if(in_array(cf::$rq["class"], array("offboarding"))){
            $sub["offboarding"] = array("Active", array("title"=>"Active offboarding list"));
		     $sub["inactive-offboarding"] = array("Inactive", array("title"=>"Inactive offboarding list"));
  
            $trigger["sub"]["offboarding"]="offboarding";
            $trigger["sub"]["offboarding-details"]="offboarding";
			  
			  $trigger["sub"]["offboarding-report"]="offboarding";
			  
			  if (strpos($_SERVER['REQUEST_URI'],'offboarding-report') !== false ) {
				  unset($sub);
				  $sub = array();
			  }
         }

        $main["credentialing"] = array("Credentialing", array("title"=>"Credentialing"));
        if(in_array(cf::$rq["class"], array("credentialing"))){
            $sub["credentialing"] = array("Active", array("title"=>"Active credentialing list"));
            $trigger["sub"]["credentialing"]="credentialing";
            $trigger["sub"]["credentialing-details"]="credentialing";
			$trigger["sub"]["credentialing-report"]="credentialing";
            $sub["inactive-credentialing"] = array("Inactive", array("title"=>"Inactive credentialing list"));
			if (strpos($_SERVER['REQUEST_URI'],'credentialing-report') !== false ) {
				  //unset($sub);
				  $sub = array();
			  }
        }

        $main["announcements"] = array("Announcements", array("title"=>"Announcements"));
        if(in_array(cf::$rq["class"], array("announcements"))){
            $sub["announcements"] = array("Active", array("title"=>"Active announcements list"));
            $trigger["sub"]["announcements"]="announcements";
            $trigger["sub"]["announcement-details"]="announcements";
            $sub["inactive-announcements"] = array("Expired", array("title"=>"Expired announcements list"));
        }

        $main["calendar"] = array("Calendar", array("title"=>"Calendar"));
        if(in_array(cf::$rq["class"], array("calendar"))){
            $sub["calendar"] = array("Active", array("title"=>"Active events list"));
            $trigger["sub"]["calendar"]="calendar";
            $trigger["sub"]["calendar-details"]="calendar";
            $sub["inactive-calendar"] = array("Expired", array("title"=>"Expired events list"));
        }

        $main["resources"] = array("Resources", array("title"=>"Resources"));
        if(in_array(cf::$rq["class"], array("resources"))){ 
            // $sub["resources"] = array("Active", array("title"=>"Active resources list"));
            $trigger["sub"]["resources"]="resources";
            $trigger["sub"]["resource-details"]="resources";
            // $sub["inactive-resources"] = array("Inactive", array("title"=>"Inactive resources list"));

            $trigger["sub"]["rcategories"]="rcategories";
            $trigger["sub"]["rcategory-details"]="rcategories";
        }
		
		$main["filecategories"] = array("File Catiegories", array("title"=>"File Categories"));
        if(in_array(cf::$rq["class"], array("filecatiegories"))){
            // $sub["resources"] = array("Active", array("title"=>"Active resources list"));
            $trigger["sub"]["filecategories"]="filecategories";
            $trigger["sub"]["filecategories-details"]="filecatiegories";
			$trigger["sub"]["filecategories-getfile"]="filecatiegories";
           
        }
		
		/******
		$main["benefits_deductions_data"] = array("Benefits Deductions Data", array("title"=>"Benefits Deductions Data"));
        if(in_array(cf::$rq["class"], array("benefits_deductions_data"))){
            // $sub["resources"] = array("Active", array("title"=>"Active resources list"));
            $trigger["sub"]["benefits_deductions_data"]="benefits_deductions_data";
            $trigger["sub"]["benefits_deductions_data-details"]="benefits_deductions_data";
			 $trigger["sub"]["benefits_deductions_data-getfile"]="benefits_deductions_data";
        }
		****/
		
		$main["welcomeemail"] = array("Welcome Emails", array("title"=>"Welcome Emails"));
		
        if(in_array(cf::$rq["class"], array("welcomeemail"))){
            //$sub["welcomeemail"] = array("Active", array("title"=>"Welcome Emails editing"));
            //$trigger["sub"]["welcomeemail"]="welcomeemail";
            //$trigger["sub"]["welcomeemail-details"]="welcomeemail";
			  //$trigger["sub"]["welcomeemail-save"]="welcomeemail";
           
        }
		
		$main["nonworking-days"] = array("Nonworking Days", array("title"=>"Nonworking Days"));
		
        if(in_array(cf::$rq["class"], array("nonworking-days"))){
            //$sub["welcomeemail"] = array("Active", array("title"=>"Welcome Emails editing"));
            //$trigger["sub"]["welcomeemail"]="welcomeemail";
            //$trigger["sub"]["welcomeemail-details"]="welcomeemail";
			  //$trigger["sub"]["welcomeemail-save"]="welcomeemail";
           
        }
		
		
		$main["general"] = array("Administrator Emails", array("title"=>"Administrator Emails"));
		
        if(in_array(cf::$rq["class"], array("general"))){		
            //$sub["general"] = array("Active", array("title"=>"Admin Emails editing"));
            $trigger["sub"]["general"]="general";
            
        }
		
		$main["benefits"] = array("benefits", array("title"=>"Benefits"));
		
        if(in_array(cf::$rq["class"], array("benefits"))){		
            //$sub["general"] = array("Active", array("title"=>"Benefits"));
            $trigger["sub"]["benefits"]="benefits";
            
        }
	


        $main["positions-offices"] = array("Positions & Offices", array("title"=>"Positions & Offices"));

        $main["manager-applications"] = array("Applications", array("title"=>"Applications"));
        if(in_array(cf::$rq["class"], array("manager-applications"))){
            $sub["manager-applications"] = array("Pending", array("title"=>"Pending applications list"));
            $trigger["sub"]["manager-application"]="manager-applications";
            $trigger["sub"]["manager-application-details"]="manager-applications";
            $sub["manager-for-interview-applications"] = array("For interview", array("title"=>"Applications for interview list"));
            $sub["manager-for-decision-applications"] = array("For decision", array("title"=>"Applications for decision list"));
            $sub["manager-manually-rejected-applications"] = array("Manually rejected", array("title"=>"Manually rejected list"));
        }

        //$main["employees-manager"] = array("Employees", array("title"=>"Employees"));

        return array($main, $sub, $trigger);
    }
    
    /** Generates the page navigation Array
     * @param arr|null $usr (PageController::$usr)
     * @param arr $pg (PageController::$pg)
     * @return array("url1"=>arry("linkTitle1", $liArgsArray)...) */
    static function PageNavigationArrays($C, $arr = array()){ 
        list($main, $sub, $trigger) = self::NavArrays();
		
		self::CheckForInactiveItemAndSwitchTriggerSubs($trigger, $C);
		
        if(self::setNavArrayChildCssClassByUrl($main, cf::$rq["class"], $trigger["main"])){
            self::setNavArrayChildCssClassByUrl($sub, cf::$rq["url"], $trigger["sub"]);
        }
		
        return array($main, $sub);
    }
    private static function CheckForInactiveItemAndSwitchTriggerSubs(&$trigger, $C){ 
	
	 /*******
        if(isset($trigger["sub"]) && isset($C::$id)){ 
            $M = self::GetModel($C);
            $activeFld = $M::$dt["pfx"]."Active";
            if(in_array($activeFld, $M::$mainTblFields) && $C::$data[$activeFld]==="No"){
                foreach($trigger["sub"] as &$v){
                    $v = "inactive-".$v;
                }
            }
        }
	********/
		
    }
    
    
    static function BreadcrumbsArray($pg){
        if(cf::$rq["class"]!="home"){
            $breadcrumbs[ADMIN] = "Home";
            if(isset($pg["breadcrumbs-ending"])){
                $breadcrumbs += (array)$pg["breadcrumbs-ending"];
            } else if(!is_null(cf::$rq["class"])) {
                $breadcrumbs[] = ucfirst(cf::$rq["class"]);
            }
            return  $breadcrumbs;
        } else {
            return null;
        }
    }
    
    static function BreadcrumbsAndTitle($C){ 
        if($C::$rq==="list"){
            if(isset($C::$pg["inactive-list"])){ 
                $C::$pg["breadcrumbs-ending"][$C::$pg["list"]["url"]] = ucfirst($C::$pg["items"]);
                $C::$pg["breadcrumbs-ending"][] = "Active ".ucfirst($C::$pg["items"]);
            } else {
                $C::$pg["breadcrumbs-ending"][] = ucfirst($C::$pg["items"]);
            }
        } else if($C::$rq==="inactive-list"){
            $C::$pg["breadcrumbs-ending"][$C::$pg["list"]["url"]] = ucfirst($C::$pg["items"]);
            $C::$pg["breadcrumbs-ending"][] = $C::$pg["inactive-list"]["title"];
        } else {
            if(isset($C::$pg["list"]["url"])){
                $C::$pg["breadcrumbs-ending"][$C::$pg["list"]["url"]] = ucfirst($C::$pg["items"]);
                $C::$pg["breadcrumbs-ending"][]= filter_has_var(INPUT_GET, "id")? WebappAdminController::$data["waaUsername"] : "Create ".$C::$pg["item"];
            } else {
                $C::$pg["breadcrumbs-ending"][]= isset($C::$pg[$C::$rq]["title"]) ? $C::$pg[$C::$rq]["title"] : SITE_NAME;
            }
        }
        //cf::vd($ctr::$pg["breadcrumbs-ending"]);
		
        $C::$pg["title"] = implode(" - ",$C::$pg["breadcrumbs-ending"]).(isset(cf::$rq["title"])?cf::$rq["title"]:"");
		
    }
    
    /**Returns the coresponding Model to the current Controller class.
     * Check do local (self::CONTROLLER) constant is defined and dies with a proper message if not.
     * @param type $C = self::CONTROLLER
     * @return |string - the model class name */
    public static function GetModel($C){
        return ($C!=="AppController" ? str_replace("Controller","Model",$C) : cf::end("The class not have self::CONTROLER definition.",2,1));
    }
    
}
