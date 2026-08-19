<?php

/** The Page Controller */
class PageController extends AppController
{

    /*const __DUMP__ = false;*/
    public static $data, $pg, $rq = null;

    final static function PagesStart($C)
    {
        $v = array();
        $v["nav-litags"] = self::NavLis($C);
        $v["usr-name"] = "Username";
        $v["usr-pending-orders"] = "Order count";
        $v["breadcrumb"] = self::Breadcrumbs($C);

        require_once(APP_HTML . "pages-start.html");
    }

    private static function NavLis($C)
    { 
        if (!isset(AppModel::$usr["afID"])) {
            $navArr = array("home" => "home");
        } else {
            $navArr = array(
			  "profile" => "Employee Home", "my-profile" => "My Profile",
			 // "payroll"=>'Payroll',
			  "change-password"=>"Change password",);
			  //, );
			  $navArr += array("my_files" => "My Files");
            
			
				$today  = val::currentDate(MYSQL_DATE_TIME);
			  /**********
			  $credentialings = 
			     EmployeeCredentialingModel::CheckEmployeeCredentialings(AppModel::$usr["afID"]);
				 echo '<pre>';print_r($credentialings);echo '</pre>';
            if (!empty($credentialings)) {
                $notDone = false;
                foreach ($credentialings as $credentialing) {
                    if (empty($credentialing["ecUploadedOn"])) {
                        if (strtotime($credentialing["ecDueDate"]) < time()) {
                            $notDone = true;
                            break;
                        }
                    }
                }
			   *********/
				   $notDone = EmployeeCredentialingModel::CheckEmployeeCredentialings(AppModel::$usr["afID"]);
				   $OneTimeToRecurringWithFile = !empty(CredentialingController::CheckOneTimeToRecurringWithFile(AppModel::$usr["afID"]));
                if ($notDone || $OneTimeToRecurringWithFile) {
                    //$navArr = $navArr + array("credentialings" => "<span class='text-red'>Credentialing</span>");
                    $navArr = $navArr + array("credentialings" => "<span>Credentialing</span>");
                    if (in_array($C, ["ProfileController", "ResourcesController", "AnnouncementsController", 
					       "CalendarController","OffboardingFrontController",
					       "OnboardingFrontController", "JobFrontController", "HomeFrontController", 
						    "EmployeeFrontController", "ApplyFormController"])) {
                        //msg::set("You have incomplete credentialing.&nbsp;&nbsp;<a href='" . WP . "credentialings'>View >></a>", "error");
                    }
                } else {
                    $navArr = $navArr + array("credentialings" => "Credentialing");
                }
           // }
			  $navArr += array('Training' => 'link:<a target="_blank" href="https://mlm.trainualapp.com/users/sign_in">');
			  $navArr += array("CE" => "--comingsoon--");
			  
            if (AppModel::$usr["afHiredForPID"] == 9999) {
                $navArr = $navArr + array("admin/employees-manager" => "My Employees");
            }

            //$onbTasks = OnboardingModel::AllActiveKeyName(AppModel::$usr["afID"]);
			
			 $onbTasks = EmployeeOnboardingModel::AmIResponsiveFor(AppModel::$usr["afID"]);
						//LoadResponsibleOnboardingTasks($myid, "eoCompletedOn is null ",'','check','HAVING otEmployeer IS NOT NULL');
            if ( $onbTasks === true) {
				
				
                    $navArr = $navArr + array("onboarding-tasks" => "<span class='text-red'>Onboarding</span>");
                   
			   }
  				else {
					
                    $navArr = $navArr + array("onboarding-tasks" => "Onboarding");
                }
				
				
			$offbTasks = EmployeeOnboardingModel::LoadResponsibleOnboardingTasks(
				  AppModel::$usr["afID"], " eoCompletedOn IS NULL",'', 'pending','','off');
		
            if ( !empty($offbTasks)) {

                    $navArr = $navArr + array("offboarding-tasks" => "<span class='text-red'>Off-boarding</span>");

			   }
			   else {
				   $offbTasks = EmployeeOnboardingModel::LoadResponsibleOnboardingTasks(
					AppModel::$usr["afID"], " 1 ",'', 'pending','','off');
					if ( !empty($offbTasks)) {
						$navArr = $navArr + array("offboarding-tasks" => "<span >Off-boarding</span>");
					}			   
			   }
			   
			   if (AppModel::$usr["afIsManager"]=='yes') {
					$nmb_pending_reqs=count(MyEmployeesController::getPendingRequestsEmployeesOf(AppModel::$usr["afID"]));
				   
				    if (MyEmployeesController::CheckNotSignedReportsFor(AppModel::$usr["afID"])) 
						$navArr += array("my-employees" => "<span class='text-red'>My Employees</span>");
					elseif ($nmb_pending_reqs==0)   $navArr += array("my-employees" => "My Employees");
					else $navArr += array("my-employees" => "<span class='text-red'>My Employees</span>");
				   
			   }
            
			$navArr += array("empty4" => "--empty--");
            $navArr += array("payroll" => "payroll");
            
            $navArr += array("timeclock" => "timeclock");
			  
            $navArr += array( "Benefits" =>'link:<a target="_blank" href="https://boonchapman.benselect.com/nypelvicpain">' );
			$navArr += array("days-off" => "Days off");
			//$navArr += array("Days off" => "--comingsoon--"); //<<<<<< to be changed

            $navArr += array("empty2" => "--empty--");
            $navArr += array("employees" => "Directory");
            $navArr += array("resources" => "Resources");
            //$navArr += array("notifications" => "Notifications");
            $navArr += array("announcements" => "Announcements");
				
            $navArr += array("calendar" => "Calendar");
            $navArr += array("jobs" => "jobs"); //, "application-form"=>"application form");
            
            $navArr += array('FINANCIAL WELLNESS' => 
            'link:<a target="_blank" href="https://mso.morganstanleyclientserv.com/publiccontent/finwell/welcome.html?c=maidenlanemedical">');


            $navArr += array("empty1" => "--empty--");
            $navArr += array("sign-out" => "Logout");//, "change-password"=>"Change password");
			
			
			 //$navArr += array("getheader?cmd=/sub/frontend-example.php" => "Test External");
        }

        $navArr += array("jobs" => "jobs"); //, "application-form"=>"application form");
		 $url =$_SERVER['REQUEST_URI'];
		
		
		 if(strpos($url,'frontend-example.php')!==false){
			 $active = "getheader?cmd=/sub/frontend-example.php";
		 }
		 else if ($C === "HomeFrontController") {
            $active = "home";
        }
		else if ($C === "BenefitsFrontController") {
            $active = "benefits";
        }
        else if ($C === "DaysOffFrontController") {
            $active = "days-off";
        } else if ($C === "ApplyFormController") {
            $active = "application-form";
        }  else if ($C === "EmployeeFrontController" && $url=='/my_files') {
            $active = "my_files";		
		 }
		 else if ($C === "JobFrontController") {
            $active = "jobs";
        } else if ($C === "EmployeeFrontController"  && $url=='/employees') {
            $active = "employees";
        } else if ($C === "OnboardingFrontController") {
            $active = "onboarding-tasks";
		  } else if ($C === "OffboardingFrontController") {
            $active = "offboarding-tasks";
        } else if ($C === "CredentialingFrontController") {
            $active = "credentialings";
		  } else if ($C === "PayrollController") {
            $active = "payroll";
		  } else if ($C === "MyEmployeesController") {
            $active = "my-employees";
        } else if ($C === "AnnouncementsController") {
            $active = "announcements";
        } else if ($C === "CalendarController") {
            $active = "calendar";
        } else if ($C === "ResourcesController") {
            $active = "resources";
        } else if ($C === "ProfileController") {
            if (!empty($_GET["mode"]) && 
			      ($_GET["mode"] === "myprofile" ||$_GET["mode"] === "view-emp-iammanager" )) {
                $active = "my-profile";
            } elseif ($C::$rq === "change-password") {
                $active = "change-password";
            } 
			  else {
                $active = "profile";
            }
        } else {
            $active = null;
        }
        return PageView::NavLisHtml($navArr, $active, 0);
    }

    private static function Breadcrumbs($C)
    {
        $request = strlen($url = filter_input(INPUT_GET, "rq")) > 0 ? $url : "profile";

        $breadcrumbs = array();
        if ($C === "HomeFrontController") {
            $breadcrumbs[null] = "Home";
        } else if ($C === "ApplyFormController") {
            $breadcrumbs[null] = "Application form";
        } else if ($C === "ProfileController") {            
            $breadcrumbs[null] = "Employee Home";
            
            if(filter_has_var(INPUT_GET, "mode")) {
                $breadcrumbs[] = "My Profile";
                if(filter_has_var(INPUT_POST, "save-btn")) {
                    $breadcrumbs[] = "Edit Profile";
                }
            }

        } else if ($C === "CredentialingFrontController") {
            $breadcrumbs[null] = "Credentialing";
        } else if ($C === "OnboardingFrontController") {
            $breadcrumbs[null] = "Onboarding Tasks";
        } else if ($C === "OffboardingFrontController") {
            $breadcrumbs[null] = "Off-boarding Tasks";
		  } else if ($C === "DaysOffFrontController") {
            $breadcrumbs[null] = "Days Off";
		} else if ($C === "BenefitsFrontController") {
            $breadcrumbs[null] = "Benefits";
        } else if ($C === "JobFrontController") {
            $breadcrumbs[null] = "Jobs";
        } else if ($C === "EmployeeFrontController" && $_SERVER['REQUEST_URI']=='/employees') {
            $breadcrumbs[null] = "Employee directory";
			
		  } else if ($C === "EmployeeFrontController" && $_SERVER['REQUEST_URI']=='/my_files') {
            $breadcrumbs[null] = "MY FILES";
		  } else if ($C === "MyEmployeesController" ) {
            $breadcrumbs[null] = "My Employees";
			
        } else if ($C === "AnnouncementsController") {
            $breadcrumbs[null] = "Announcements";
		  } else if ($C === "DaysOffController") {
            $breadcrumbs[null] = "Days off";
        } else if ($C === "CalendarController") {
            $breadcrumbs[null] = "Calendar";
        } else if ($C === "ResourcesController") {
            $breadcrumbs[null] = "Resources";
            $breadcrumbs["resources"] = "Categories";
            if(filter_has_var(INPUT_GET, "cat")) {
                $breadcrumbs["resources?cat=".filter_input(INPUT_GET, "cat", FILTER_SANITIZE_NUMBER_INT)] = "Resources";
            }
        }
        return PageView::BreadcrumbsLisHtml($breadcrumbs);
    }


    final
        static function PagesStop()
    {
        if (isset(AppModel::$usr)) {
            require_once(APP_HTML . "pages-stop.html");
        } else {
            require_once(APP_HTML . "pages-stop.html");
        }
    }

}
