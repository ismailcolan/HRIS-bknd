<?php

/** The webpage assets admin view class */
class WebpageAssetsAdminView extends AppView
{

    static function pageTitle($title, $C, $btnsUrlText = null,$add='')
    { 
        if (isset(cf::$rq["ext"]) && in_array(cf::$rq["ext"], array("json"))) {
            return;
        }
		  if ($C::$rq == 'report')  {
			  if ($C=='OnboardingController'){
			  if (OnboardingController::$type =='on') $title='Onboarding report';
			  else $title='Off-boarding report';
			  }
		  }
		  
        form::openDiv(array("class" => "page-header row no-gutter"));
        form::openDiv(array("class" => "col-sm-6"));
		 if ($title == 'Employees' && isset($_GET['to_do'])) $title='Requests for corrections of timeclock';
		 if ($add !='') $title .='<span style="font-size:0.8em">'.$add.'</span>';
		 
		 if ($title=='Add Offboarding') $title='Add Off-boarding';
		 if ($title=='Offboarding') $title='Off-boarding';
		
        form::tag($title, "h3");
        form::closeDiv();
        form::openDiv(array("class" => "col-sm-6 text-right"));
				if ($C === "EmployeeController") {
			  $today = new DateTime();
			  
            if ($C::$rq === "details"
			   && 
					!(
					$C::$data['afActive'] =='Terminated' || 
					($C::$data['afActive'] =='Hired'  &&  isset($C::$data['afDateLeaving']) && $C::$data['afDateLeaving'] <= $today->format('Y-m-d'))
				   )
			   )
			  
			   {
					form::link(ADMIN . 'employee-details?id=' . $C::$id . '&cmd=autologin', "<i class='fa fa-user'></i>&nbsp;&nbsp;Login as this employee", ["target" => "_blank", "class" => "btn btn-primary"]);
					
					if (strpos($_SERVER['REQUEST_URI'],'cmd=terminate') === false && $add ==''){
					
							form::link(ADMIN . 'employee-details?cmd=terminate&id=' . $C::$id  , "Terminate employee", ["class" => "btn btn-danger"]);
							
							form::link(ADMIN . 'employee-details?cmd=days-off&id=' . $C::$id  , "Give Days Off", ["class" => "btn btn-success"]);
						}
					
					  if ($C::$data["afActive"] == 'Hired'
							&&
							(isset($C::$data['afDateLeaving']) &&  $C::$data['afDateLeaving'] > $today->format('Y-m-d'))
							 )
							form::link(ADMIN . 'employee-details?cmd=undo-terminate&id=' . $C::$id  , "Cancel termination",
							["onclick"=>"return confirm('Are you sure you want to un-do the employee termination?')","class" => "btn btn-success"]);

				}
			
			
			}

        if ($C::$rq === "summary") {
            if (method_exists($C, "getBackUrl")) {
                $backUrl = $C::getBackUrl();
                if (!empty($backUrl)) {
                    $name = $backUrl["name"];
                    $url = $backUrl["url"];
                    $icon = $backUrl["icon"];
                    $args = $backUrl["args"];
                    $name = '<i class="' . $icon . '"></i> ' . $name;
                    $urlPre = ADMIN;
                    form::link($urlPre . $url, $name, $args);
                }
            }
        }

        if (is_array($btnsUrlText)) {
            foreach ($btnsUrlText as $url => $name) {

                if ($C === "PositionController") {
                    if ($C::$data["pCanRemove"] === "No") {
                        if (in_array(substr($name, 0, 11), ["Deactivate ", "Reactivate "])) {
                            continue;
                        }
                    }
                }

                $args = array("class" => "btn btn-primary");
				
				if ($name=='Add filecategories') $name = 'Add Category';
				if ($name=='Add welcomeemail') $name = 'Add Welcome Email Template';
			
                if (substr($name, 0, 4) === "Add ") {
					
                    if($name === "Add calendar") {
                        $name = "Add event";
                    }
					   if ($name=='Add offboarding') $name='Add off-boarding';
                    $name = "<i class=\"fa fa-plus\"></i> " . $name;
				
					   
                } else if (substr($name, 0, 5) === "Edit ") {
                    $name = "<i class=\"fa fa-pencil-square-o\"></i> " . $name;
                } else if (substr($name, 0, 11) === "Deactivate ") {
                    $name = "<i class=\"fa fa-ban\"></i> " . $name;
                    $args = array("class" => "btn btn-danger", "onclick" => "return confirm('" . str_replace("row", strtolower($C::$pg["item"]), TXT_DEACTIVATE_CONFIRM) . "');");
                } else if (substr($name, 0, 11) === "Reactivate ") {
                    $name = "<i class=\"fa fa-undo\"></i> " . $name;
                    $args = array("class" => "btn btn-success");
                } else if (substr($name, 0, 13) === "Submit order ") {
                    $name = "<i class=\"fa fa-cart-arrow-down fa-lg\"></i> " . $name;
                    $args = array("class" => "btn btn-success");
                } else if ($name === "Change employee password") {
                    $name = "<i class=\"fa fa-key fa-lg\"></i> " . $name . " <i class=\"fa fa-external-link\"></i>";
                    $args = array("class" => "btn btn-primary", "target" => "_blank");
                } else if (substr($name, 0, 7) === "Delete ") {
                    $delConfirm = str_replace("row", strtolower(str_replace("Delete ", "", $name)), TXT_DELETE_CONFIRM);
                    $name = "<i class=\"fa fa-trash-o\"></i> " . $name;
                    $args = $C !== "OrderController" ?
                        array("class" => "btn btn-danger", "onclick" => "return confirm('" . $delConfirm . "');") :
                        array("class" => "btn btn-danger");
                }
                $urlPre = ADMIN;
                //form::tag($name,"button",array("class"=>"btn btn-primary", "onclick"=>"location.assign('".$urlPre.$url."')"));
                
				 form::link($urlPre . $url, $name, $args);
				 
				 if ($name == '<i class="fa fa-plus"></i> Add onboarding') 
						 echo 
						'<a href="onboarding-report" class="btn btn-primary"><span class="fa fa-list-alt"></span> Onboarding Report</a>';
					
				if ($name == '<i class="fa fa-plus"></i> Add off-boarding') 
						 echo 
						'<a href="offboarding-report" class="btn btn-primary"><span class="fa fa-list-alt"></span> Off-boarding Report</a>';
						
				
				
				if ($name == '<i class="fa fa-plus"></i> Add credentialing') 
						 echo 
						'<a href="credentialing-report" class="btn btn-primary"><span class="fa fa-list-alt"></span> Credentialing Report</a>';
					
				
            }
            //"<button class=\"btn primary pull-right\">Add ".strtolower($pg["item"])."</button>"
        }
		
		 if ($C::$rq == 'report') {
			if ($C=='CredentialingController') echo '<a href="credentialing" style="float:right" class="btn btn-primary" ><span class="fa fa-arrow-left"></span>'
				.'&nbsp;&nbsp;&nbsp;Back to Credentialings Lists</a>';
				
			elseif (OnboardingController::$type =='on')
				echo '<a href="onboarding" style="float:right" class="btn btn-primary" ><span class="fa fa-arrow-left"></span>'
				.'&nbsp;&nbsp;&nbsp;Back to Onboarding Lists</a>';
			else
				echo '<a href="offboarding" style="float:right" class="btn btn-primary" ><span class="fa fa-arrow-left"></span>'
				.'&nbsp;&nbsp;&nbsp;Back to Off-boarding Lists</a>';
		 }
		
        form::closeDiv();
        form::closeDiv();
    }


    static function AdminMainMenuHtmlCode($navArray)
    {

        if (isset($_GET['list']) && $_GET['list']=='emp' ){
		   $navArray['employees']=array('Employees', Array( 'title' => 'Employees','class' => 'active'));
		}
         
        $active = "";
		//echo '<pre>';print_r($navArray);echo '<pre>';
        foreach ($navArray as $url => $nav) {
			//print_r($nav); echo '<hr>';
            if (isset($nav[1]["class"])) {
                if (($nav[1]["class"]) === "active") {
					   $active = $url;
                    break;
                }
            }
        }
		 if (! isset($active) || $active=='') 
			 
		 $active=explode('?',str_replace('/admin/','',$_SERVER['REQUEST_URI']))[0];

        if (!empty(AppModel::$admin["waaAFID"])) {
            $url = cf::$rq["class"];
            if (!in_array($url, ["positions-offices", "manager-applications", "employees-manager"])) {
                cf::Redirect(ADMIN . "positions-offices");
            }

            $code = "<li class='". (in_array($active, ["profile"]) ? "active" : "inactive") . "'>
                    <a href='" . WP . "profile' ><< Back to My Employee Account</a>
                  </li>";

            $code .= "<li class='". (in_array($active, ["positions-offices"]) ? "active" : "inactive") . "'>
                    <a href='" . ADMIN . "positions-offices'>Positions & Offices</a>
                  </li>";

            $code .= "<li class='". (in_array($active, ["employees-manager"]) ? "active" : "inactive") . "'>
                    <a href='" . ADMIN . "employees-manager'>Employees</a>
                  </li>";

            $code .= "<li class='". (in_array($active, ["manager-applications"]) ? "active" : "inactive") . "'>
                    <a href='" . ADMIN . "manager-applications'>Applications</a>
                  </li>";
        } else {
            $code = "<li class='" . (in_array($active, ["home"]) ? "active" : "") . "'>
                    <a href='" . ADMIN . "home'>Home</a>
                  </li>";
				  
				  
			  $code .="<li class='". (in_array($active, ["applications"]) ? "active" : "inactive") . "'>
                    <a href='" . ADMIN . "applications'>Applications</a>
						</li>";
			
			 $code .= "<li class='" . (in_array($active, ["employees-of-manager","employees"]) &&!isset($_GET['to_do']) ? "active" : "") . "'>
                  <a href='" . ADMIN . "employees'>Employees</a>
                </li>";
		/******/
            $code .= "<li class='". (in_array($active, ["request_for_correction","credentialing", "onboarding", 
			"offboarding", "filecategories","departments", "offices","payroll-report","unpaid-days-off-report","benefits_deductions_data"])
		||  (strpos($_SERVER['REQUEST_URI'],'admin_example')!==false) 
		||  (isset($_GET['to_do']) && $_GET['to_do'] == 'tsr')  			
		? "active" : "inactive") . "'>
                    <a href='javascript:void()'>Employee Data</a>
                    <ul class='second-level-menu' >
					
					  <li class='". ( isset($_GET['to_do']) &&  $_GET['to_do'] == 'tsr'  ? "active" : "inactive") ."'>
					     <a class='dropdown-item' href='" . ADMIN . "employees?to_do=tsr'>Requests for corrections</a></li>
						 
                     <li class='". ( in_array($active, ["credentialing"])  ? "active" : "inactive") ."'>
					     <a class='dropdown-item' href='" . ADMIN . "credentialing'>Credentialing</a></li>
						 					      
						  <li class='". (in_array($active, ["onboarding"]) ? "active" : "inactive") ."'>
					     <a  href='" . ADMIN . "onboarding'>Onboarding</a></li>
						 
						 <li class='". (in_array($active, ["offboarding"]) ? "active" : "inactive") ."'>
					     <a  href='" . ADMIN . "offboarding'>Off-boarding</a></li>
						 
						 <li class='". (in_array($active, ["filecategories"]) ? "active" : "inactive") ."'>
						  <a class='dropdown-item' href='" . ADMIN . "filecategories'>File Categories</a></li>
						  
						  <li class='". (in_array($active, ["departments"]) ? "active" : "inactive") ."'>
					     <a href='" . ADMIN . "departments'>Departments</a></li>
						 
                     <li class='". (in_array($active, ["offices"]) ? "active" : "inactive") ."'>
						  <a href='" . ADMIN . "offices'>Offices</a></li>
						  
						 <li class='". ((strpos($_SERVER['REQUEST_URI'],'payroll-report')!==false) ? "active" : "inactive") ."'>
						  <a href='" . ADMIN . "payroll-report'>Payroll Report</a></li>
						  
						 <li class='". ((strpos($_SERVER['REQUEST_URI'],'unpaid-days-off-report')!==false) ? "active" : "inactive") ."'>
						  <a href='" . ADMIN . "unpaid-days-off-report'>Unpaid days off</a></li>
						
						<li class='". ((strpos($_SERVER['REQUEST_URI'],'not-assigned')!==false) ? "active" : "inactive") ."'>
						  <a href=\"javascript:void(0)\" onclick=\"alert('Coming soon..')\">Employee of the month</a></li>
						  
						<li class='". (in_array($active, ["benefits_deductions_data"]) ? "active" : "inactive") ."'>
						  <a class='dropdown-item' href='" . ADMIN . "benefits_deductions_data'>Benefits</a></li>
						
                    </ul>
                  </li>";
				/***********
				  <li class='". ((strpos($_SERVER['REQUEST_URI'],'admin_example')!==false) ? "active" : "inactive") ."'>
						  <a href='" . ADMIN."getheader?cmd=/sub/admin/admin_example.php'>Test external</a></li>
				***********/	 
				  				  
			  $code .= "<li class='". (in_array($active, ["positions", "jobs", 
							"skills", "questions"]) ? "active" : "inactive") . "'>
                    <a href='javascript:void()'>Positions</a>
                    <ul class='second-level-menu' >
							<li class='". (in_array($active, ["positions"]) ? "active" : "inactive") ."'>
							<a  href='" . ADMIN . "positions'>Positions</a></li>
						  
							<li class='". (in_array($active, ["skills"]) ? "active" : "inactive") ."'>
							<a  href='" . ADMIN . "skills'>Skills</a></li>
							
						   <li class='". (in_array($active, ["questions"]) ? "active" : "inactive") ."'>
							<a  href='" . ADMIN . "questions'>Questions</a></li>
                      
							<li class='". (in_array($active, ["jobs"]) ? "active" : "inactive") ."'>
							<a  href='" . ADMIN . "jobs'>Open Jobs</a></li>
                    </ul>
                  </li>";
				  
			 //<<<<
			 $code .= "<li class='". (in_array($active, ["announcements", "calendar", 
							"rcategories","rcategory-details", "resources"]) ? "active" : "inactive") . "'>
                    <a href='javascript:void()'>Information</a>
							<ul class='second-level-menu' >
								<li class='" . (in_array($active, ["announcements"]) ? "active" : "inactive") . "'>
								<a href='" . ADMIN . "announcements'>Announcements</a>
								</li>

								<li class='" . (in_array($active, ["calendar"]) ? "active" : "inactive") . "'>
								<a href='" . ADMIN . "calendar'>Calendar</a>
								</li>

								<li class='" . (in_array($active, ["resources"]) ? "active" : "inactive") . "'>
								<a href='" . ADMIN . "resources'>Resources</a>
								</li>
								
								<li class='" . (in_array($active, ["rcategories","rcategory-details"]) ? "active" : "inactive") . "'>
								<a href='" . ADMIN . "rcategories'>Resource Categories</a>
								</li>					
							</ul>
                  </li>";
				
          
		  			  
            $code .= "<li class='" . 
			(in_array($active, ["companies","company_logo","welcomeemail","general","nonworking-days","block-out-days","unsign_limit","benefits"]) ? "active" : "inactive") . "'>
                    <a href='javascript:void()' >
                        Settings
                    </a>
                    <ul class='second-level-menu'>
					     <li class='". (in_array($active, ["general"]) ? "active" : "inactive") ."'>
						  <a  href='" . ADMIN . "general'>Site settings</a></li>
						 	
						  <li class='". (in_array($active, ["welcomeemail"]) ? "active" : "inactive") ."'>
						  <a  href='" . ADMIN . "welcomeemail'>Welcome Email</a></li>
						  
						  <li class='". (in_array($active, ["nonworking-days"]) ? "active" : "inactive") ."'>
						  <a  href='" . ADMIN . "nonworking-days'>Non-working days</a></li>
						  
						  <li class='". (in_array($active, ["block-out-days"]) ? "active" : "inactive") ."'>
						  <a  href='" . ADMIN . "block-out-days'>Block-out dates</a></li>
						  
						  <li class='". (in_array($active, ["unsign_limit"]) ? "active" : "inactive") ."'>
						  <a  href='" . ADMIN . "unsign_limit'>General</a></li>
						  
						 <li class='". (in_array($active, ["companies"]) ? "active" : "inactive") ."'>
					     <a href='" . ADMIN . "companies'>Companies</a></li>
						  
						  <li class='". (in_array($active, ["company_logo"]) ? "active" : "inactive") ."'>
						  <a  href='" . ADMIN . "company_logo'>Customer logo</a></li>
						  
                    </ul>
                  </li>
				     </ul>";   

						 /*******
						  <li class='". (in_array($active, ["benefits"]) ? "active" : "inactive") ."'>
						  <a  href='" . ADMIN . "benefits'>Benefits</a></li>
						  *******/					 
			
        }

        return $code;


        foreach ($navArray as $url => $data) {
            if (count($data) === 2) {
                list($lnkTitle, $liArgs) = $data;
            } else {
                $liArgs = null;
                $lnkTitle = (is_array($data) ? $data[0] : $data);
            }

            if (isset($liArgs["class"])) {
                $liArgs["class"] .= " nav-item ";
            } else {
                $liArgs["class"] = " nav-item ";
            }

            $code .= form::openLiOut($liArgs);
            if (is_numeric($url)) {
                $code .= form::htmlOut($lnkTitle);
            } else {
                $code .= form::tagOut($lnkTitle, "a", array("href" => $url));
            }
            $code .= form::closeLiOut();
        }

        return $code;

    }

    /** Generates <li> items from array (to be placed inside <ul>|<ol> tags)
     * @param array $navArray
     * @param boolean $returnCode (true) Return html code OR to execute inline
     * @return string
     */
    static function NavigationArrayToListNodes($navArray, $returnCode = true, $pathPre = null)
    {
		 if (isset($_GET['list']) && $_GET['list']=='emp' ){
	
			$navArray[''] = Array('Employees of manager',Array('title' =>'Employees list','class' => 'active'));
			$navArray['employees-terminated']= Array('Terminated',Array('title' =>'Terminated employees list'));
		 }
        if ((bool)$returnCode) {
            ob_start();
        }
//cf::pr($navArray);
		  
         if (strpos($_SERVER['REQUEST_URI'],'terminated') !== false){
			 
			 foreach ($navArray as $k=>$ar){
				 if (isset( $ar[1]['class'])) unset($navArray[$k][1]['class']);
			 }
		    $navArray['employees-terminated'][1]['class']='active';
		  }
		 
        foreach ($navArray as $url => $data) {
            if (count($data) === 2) {
                list($lnkTitle, $liArgs) = $data;
            } else { 
                $liArgs = null;
                $lnkTitle = (is_array($data) ? $data[0] : $data);
            }
            form::openLi($liArgs);
            if (is_numeric($url)) {
                form::html($lnkTitle);
            } else {
                //$linkUrl = substr($url,0,1)!=="#" ? $pathPre.ltrim($url,"/") : $url;
                //form::tag($lnkTitle, "a", array("href"=>$linkUrl));
                form::tag($lnkTitle, "a", array("href" => $pathPre . $url));
            }
            form::closeLi();
        }

        if ((bool)$returnCode) {
            return ob_get_clean();
        }
    }

}
