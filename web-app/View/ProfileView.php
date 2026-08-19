<?php
/** The Vendor view */
class ProfileView extends AppView
{
    protected static
        $fldArgsBase = array("fld-class" => "form-group", "lbl-class" => "control-label col-sm-4 col-md-3", "wrap-class" => "col-sm-12 col-md-9 col-lg-9", "class" => "form-control"),
        $saveCancelArgsBase = array("fld-class" => "form-group", "wrap-class" => "col-sm-8 col-md-7 col-lg-5 col-sm-offset-4 col-md-offset-3", "class" => "btn btn-primary", "class-cancel" => "btn btn-default");


    final public static function renderForm($title = null, $method = "post",$action="profile")
    {
        if (isset($title)) {
            form::codeStyle(true, 1, false);
            form::openTag("div", array("class" => "form-panel"));
            form::$ofsetIndentsCount = 2;

            if (!empty($title)) {
                form::html('<h3>' . $title . '</h3>');
            }
            form::openTag("form", array("method" => $method, "action" => WP . $action, "enctype" => "multipart/form-data", "validate" => true, "class" => "form-horizontal"));
        } else {
            form::closeTag("form");
            form::closeDiv();
        }
    }

    final static function Profile($data, $pg, $rq)
    { 
        form::html('<div class="content bg-white">');
        form::html('<div class="row">');
        form::openDiv(array("class" => "col-sm-12"));

        if (!empty($_GET["mode"]) && ($_GET["mode"] === "myprofile")) {
        // form::html('<h3>Employee Profile</h3>');
            form::tag("&nbsp;", "div");
            self::Summary($data, $pg, ProfileModel::$fldLbl);

        } else {
            self::EmployeeHome();
        }

        form::html('</div></div></div>');
    }
	
	public static function WhoIsAwayFull(){
		$d=new DateTime();
		$today=$d->format('Y-m-d');
		$q="SELECT * FROM vacation_requests
		LEFT JOIN apply_forms ON afID=vrAFID
		WHERE '{$today}'>=vrDateStart AND '{$today}'<=vrDateEnd AND vrState='approved'";
		$emps=db::PrepareLoad($q);
		
		form::html('<br>
                <div class="card" style="border:0;">
                
                <div class="card-body text-primary">
					<h4>Who\'s away today FULL LIST </h4>
				   <a href="profile" style="float:right;margin-right:10px;font-weight:normal;text-transform:none">Back >></a>
				   <br>
				  ');
					
					if($emps==false) echo 'No employees away today.';
					else {
						
						foreach ($emps as $k=>$sl){
							$from=val::convDate(MYSQL_DATE,USER_DATE,$sl['vrDateStart']);
							$to= val::convDate(MYSQL_DATE,USER_DATE,$sl['vrDateEnd']);
							echo "<span>{$sl['afFirstName']} {$sl['afLastName']}&nbsp;&nbsp;&nbsp;&nbsp;
							from {$from} to {$to}&nbsp;&nbsp;&nbsp;&nbsp;{$sl['vrType']}</span><br>";
							
						}
					}
                    
         echo '</div>
              </div>';
		
	}

    public static function EmployeeHome()
    {

        form::openDiv(["class" => "row"]);
        form::openDiv(["class" => "col-sm-12 col-md-6"]);

        form::html('
                <div class="card">
                <div class="card-header">Quick links</div>
                <div class="card-body text-primary">                    
                    <div><a href="' . WP . 'my-profile">My Profile</a></div>
                    <div><a href="' . WP . 'credentialings">Credentialing</a></div>
                    <div><a href="' . WP . 'employees">Employee Directory</a></div>
                    <div><a href="' . WP . 'resources">Resources</a></div>
                    <div><a href="' . WP . 'jobs">Jobs</a></div>
                </div>
              </div>
			');
			
		  $d=new DateTime();
		  $today=$d->format('Y-m-d');
		  $q="SELECT * FROM vacation_requests
		  LEFT JOIN apply_forms ON afID=vrAFID
		  WHERE '{$today}'>=vrDateStart AND '{$today}'<=vrDateEnd AND vrState='approved'";
		  $emps=db::PrepareLoad($q);

		  form::html('
                <div class="card">
                <div class="card-header">Who\'s away today</div>
                <div class="card-body text-primary">');

					if($emps==false) echo 'No employees away today.';
					else {
						$sliced= array_slice($emps,0,9);
						foreach ($sliced as $k=>$sl){
							$from=val::convDate(MYSQL_DATE,USER_DATE,$sl['vrDateStart']);
							$to= val::convDate(MYSQL_DATE,USER_DATE,$sl['vrDateEnd']);
							echo "<span>{$sl['afFirstName']} {$sl['afLastName']}
							until {$to} ({$sl['vrType']})</span><br>";
							
						}
						if ( count($sliced)<count($emps))
							echo '<a style="float:right" href="?cmd=who-is-away">view all away today >></a>';
					}
                    
         echo '</div>
              </div>';


        form::tag("<hr/><strong>Please welcome our newest employees:</strong>", "div");

        HomeView::RenderLast5Employees();

        form::closeDiv(["class" => "col-sm-12 col-md-6"]);



        form::openDiv(["class" => "col-sm-12 col-md-6"]);
        
		 //print_r(msg::returnVal());
		    $r = db::PrepareLoadSingleRow('select * from apply_forms WHERE afID='.AppModel::$usr["afID"]) ;
			$val =  preg_replace('/\s+/', '', $r['afSpecialSkills']);
			
			$s='';
		   if ($val == '') {
			 $s = '<div style="margin:0;padding:0;margin-top:10px;">Your profile is incomplete - please go to <a href="my-profile">"My Profile"</a> and enter Skills And Hobbies.</div>';
			 
		   }
		   
		   $val =  preg_replace('/\s+/', '', $r['afShortResume']);
			
			
		   if ($val == '') {
			 $s .= '<div style="margin:0;padding:0;margin-top:10px;">
			 Your profile is incomplete - please go to <a href="my-profile">"My Profile"</a> and enter Short Resume.</div>';
			 
		   }
		   
		   $res = db::PrepareLoad('SELECT * FROM `apply_forms` WHERE afID ='.AppModel::$usr["afID"]);
		   
		   if (val::positionRequirePhysicianField($res[0]['afHiredForPID'])) {
			   $e=array();
			   if($res[0]['afNYSMedLicenseNum']=='')$e[]='NYS Medical License Number';
			   //if(empty($res[0]['afNYSMedicalLicenseExpDate']) ||
			   //$res[0]['afNYSMedicalLicenseExpDate']=='0000-00-00')$e[]='NYS Medical license exp. date';
			   if($res[0]['afNPINum']=='')$e[]='NPI Number';
			   if($res[0]['afCAQHNum']=='')$e[]='CAQH Number';
			   if (!empty($e)) {
				   //if ($s!='') $s.='<br>';
				   $s .= '<div style="margin:0;padding:0;margin-top:10px;">Your profile is incomplete - please go to <a href="my-profile">"My Profile"</a> and enter '
				   .implode(', ',$e).'.</div>';
			   }
		   }
		 $z	 = msg::returnVal();
		 
		 $onb = EmployeeOnboardingModel::AmIResponsiveFor(AppModel::$usr["afID"]);
		 //OnboardingModel::AllActiveKeyName(AppModel::$usr["afID"]);
		 if (!empty($onb)) {
			$s.='<div style="">' 
			."There are incomplete onboarding tasks. <a href='" . WP . "onboarding-tasks'>View >></a>"
			.'</div>';
		 }
		 $s=str_replace('div style="','div style="margin-bottom:10px;line-height:1.1;"',$s);
		 
		 
		 $s1='';
		 $notDone = EmployeeCredentialingModel::CheckEmployeeCredentialings(AppModel::$usr["afID"]);
		 if (!empty($notDone)) {
			$s1.='<div style="">' 
			."You have incomplete credentialing.&nbsp;&nbsp;<a href='" . WP . "credentialings'>View >></a>"
			.'</div>';
		 }
		 
		 
		 $offbTasks = EmployeeOnboardingModel::LoadResponsibleOnboardingTasks(
		AppModel::$usr["afID"], " eoCompletedOn IS NULL",'', 'pending','','off');
		if ( !empty($offbTasks)) {
			$s1.='<div style="">' 
			."You have incomplete off-boarding tasks.&nbsp;&nbsp;<a href='" . WP . "offboarding-tasks'>View >></a>"
			.'</div>';
		}
		$s1=str_replace('div style="','div style="margin-bottom:10px;line-height:1.1;"',$s1);
		 
		 $s2='';
		 $there_onetime=CredentialingController::CheckOneTimeToRecurringWithFile(AppModel::$usr["afID"]);
		 
		 if ($there_onetime !== false ) {
			$s2.='<div style="">' 
			."Credential requires expiration date. <a href='" . WP . "credentialings'>View >></a>"
			.'</div>';
		 }
		 $s2=str_replace('div style="','div style="margin-bottom:10px;line-height:1.1;"',$s2);
		
               
		 $s=$s.$s1.$s2;
		 
		 if (AppModel::$usr["afIsManager"]=='yes'){ 
			 $nmb_pending_requests=count(MyEmployeesController::getPendingRequestsEmployeesOf(AppModel::$usr["afID"]));
			 if ($nmb_pending_requests>0) 
				 $s=$s.'<div style="">There are CE/vacation requests. <a href="' . WP . 'my-employees">View >></a></div>';
			 
			if (MyEmployeesController::CheckNotSignedReportsFor(AppModel::$usr["afID"])) {
				$s=$s.'<div style="">There are reports to sign off. <a href="' . WP . 'my-employees">View >></a></div>';
			}
		 }
		 
		
		 if ($z == 'No new notifications.' && $s !='') $z='';
        form::html('
                <div class="card"  style="height:auto;min-height:200px">
                <div class="card-header-dark">Notifications</div>
                <div class="card-body text-primary" >
                    <div class="notifications">'.$z.$s.'</div>                   
                </div>
              </div>');

        $anns = AnnouncementsModel::AllActiveKeyName();
        if ($anns === false) {
            $anns = [];
        }
        if (empty($anns)) {
            $annsHTML = "No announcements";
        } else {
            $annsHTML = "";
			  $i=count($anns);
			  $z=1;
            foreach ($anns as $id => $headline) {
                $annsHTML .= '<a href="' . WP . 'announcements#collapse' . $id . '">' . $headline . '</a>';
				   if($i != $z) $annsHTML .='<br/>';
				   $z++;
				   
            }
        }
		 
		  $annsHTML=str_replace('<a','<a style="line-height:1.1em;display:inline-block"',$annsHTML);
		  
        form::html('
                <div class="card">
                <div class="card-header">Announcements</div>
                <div class="card-body text-primary">
                    <div class="notifications">' . $annsHTML . '</div>                   
                </div>
              </div>
');

        $events = CalendarModel::data('calActive = "Yes" and calDate >= CURRENT_DATE', ["calDate" => "ASC"]);
        if ($events === false) {
            $events = [];
        }
        if (empty($events)) {
            $eventsHTML = "No events";
        } else {
            $eventsHTML = "";
			  $i=count($events);
			  $z=1;
            foreach ($events as $event) {
                $eventsHTML .= 
                    '<a href="' . WP . 'calendar#collapse' . $event[CalendarModel::$dt["key"]] . '">' . 
                    '<span style="color:#337ab7">'.
					   val::convDate(MYSQL_DATE,USER_DATE,$event[CalendarModel::$dt["pfx"] . "Date"]).': '.
					   '</span>'.
					   nl2br(htmlspecialchars($event[CalendarModel::$dt["pfx"] . 'Headline'])) . '</a>';
				   if($i != $z) $eventsHTML .='<br/>';
				   $z++;
            }
        }
		 
		 
		 
		 $eventsHTML=str_replace('<a','<a style="line-height:1.1em;display:inline-block;"',$eventsHTML);
		 
        form::html('
                <div class="card">
                <div class="card-header">Calendar</div>
                <div class="card-body text-primary">
                    <div class="notifications lineheight1">'.$eventsHTML.'</div>                   
                </div>
              </div>
');

        form::closeDiv(["class" => "col-sm-12 col-md-6"]);

        form::closeDiv(["class" => "row"]);
    }



    final static function SignIn()
    {
//        form::html('<div class="content bg-white">');
        form::html('<div class="row">');
        form::html('<div class="col-sm-12 col-md-12 col-lg-12">');

        self::renderForm("");
        $args = self::$fldArgsBase;

        if (isset(AppModel::$usr["afID"])) {
            $args["wrap-class"] = "col-sm-12";
            form::html('<div class="row"><div class="col-sm-12"><label>Logged in as:</label>');
            form::inpInfo(
                "",
                AppModel::$usr["afFirstName"] . ' ' . AppModel::$usr["afLastName"],
                $args + array("readonly" => "readonly")
            );
            form::html('<div class="fld form-group">
                        <div class="col-sm-8 col-md-7 col-lg-5">
                            <br/><a href="' . WP . 'sign-out" value="Save" class="btn btn-primary">Logout</a><br/><br/>&nbsp;
                        </div>
                    </div>');
            form::html('</div></div>');
        } else {
			
			if(isset($_SESSION['company'])) $customer = $_SESSION['company'];  else $customer='';
			if(isset($_POST['customer'])) $customer = strtoupper($_POST['customer']);
			$data['customer']=$customer;
			form::inpField("Customer:",
			"customer", 
			empty($data["customer"]) ? "" : $data["customer"],
			$args
			//array("autofocus"=>true, "id"=>"inputCustomer", 
			//"fld-class"=>"form-group", "lbl-class"=>"control-label col-sm-4", "wrap-class"=>"col-sm-8", "class"=>"form-control")
			);
										
            form::inpField(
                "E-mail:",
                "email",
                empty($data["email"]) ? "" : $data["email"],
                $args
            );

            form::inpField(
                "Password:",
                "pass",
                empty($data["pass"]) ? "" : $data["pass"],
                $args + array("type" => "password")
            );
			

            form::inpSaveCancel(null, array("fld-class" => "form-group", "wrap-class" => "col-sm-9 col-sm-offset-3", "class" => "btn btn-primary"), "Log in");

            form::openTag("div", array("class" => "fld form-group"));
            form::openTag("div", array("class" => "col-sm-9 col-sm-offset-3"));
            form::link(WP . "login-recover", "Forgotten password");
            form::closeDiv();
            form::closeDiv();
        }

        self::renderForm();
        form::closeDiv();
        form::closeDiv();
//        form::closeDiv();
    }

    final public static function setPassword()
    {
        form::html('<div class="content bg-white">');
        form::html('<div class="row">');
        form::html('<div class="col-sm-12 col-md-12 col-lg-12">');
		 
		 $data=ProfileController::$data;
        self::renderForm("Set new password",'post','login-recover');
        $args = self::$fldArgsBase;
		  $args['wrap-class'] ='col-sm-12 col-md-9 col-lg-4';
		  echo '<input type="hidden" name="key" value="'.$_GET['key'].'">';
        form::inpField(
            "New password:",
            "pass",
            empty($data["pass"]) ? "" : $data["pass"],
            $args + array("type" => "password")
        );
        form::inpField(
            "Re password:",
            "passRe",
            empty($data["passRe"]) ? "" : $data["passRe"],
            $args + array("type" => "password")
        );

        form::html('<div class="fld form-group">
                        <label class="control-label col-sm-3 col-md-2"></label>
                        <div class="col-sm-8 col-md-7 col-lg-5">
                        <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
                        <small>The password must be at least 8 characters long and must include a number. Please make sure to choose a hard to guess password. Don\'t use your name or anything else that is easy to guess.</small>
                        </div>
                        </div>
                    </div>');
       if (isset($data['confirm'])) $check='checked'; else $check='';
        form::html('
        <div class="fld form-group">
            <label class="control-label col-sm-3 col-md-2"> </label>
            <div class="col-sm-8 col-md-7 col-lg-5">
            <label><input type="checkbox" '.$check.' name="confirm"> I confirm I will not give access to my account to anybody else and only I will sign documents in my account</label>
            ' . (!empty(form::$error["confirm"]) ? '<span class="err">' . form::$error["confirm"][0] . '</span>' : "") . '
            </div>
        </div>');

        form::inpSaveCancel(null, array("fld-class" => "form-group", "wrap-class" => "col-sm-9 col-sm-offset-2", "class" => "btn btn-primary"),
   		 "Set Password",'Cancel','save-set-new-password');

        self::renderForm();
        form::closeDiv();
        form::closeDiv();
        form::closeDiv();
    }

    final public static function sendSetPasswordKey()
    {
        form::html('<div class="content bg-white">');
        form::html('<div class="row">');
        form::html('<div class="col-sm-12 col-md-12 col-lg-12"><br>');
		msg::get();
        self::renderForm("Send new password e-mail",'post','login-recover');
        $args = array("fld-class" => "form-group", "lbl-class" => "control-label col-sm-4 col-md-3", "wrap-class" => "col-sm-12 col-md-5 col-lg-5", "class" => "form-control");
		
		if(isset($_COOKIE['customer'])) $customer = $_COOKIE['customer'];  else $customer='';
			if(isset($_POST['customer'])) $customer = strtoupper($_POST['customer']);
			$data['customer']=$customer;
			form::inpField("Customer:",
			"customer", 
			empty($data["customer"]) ? "" : $data["customer"],
			$args
			//array("autofocus"=>true, "id"=>"inputCustomer", 
			//"fld-class"=>"form-group", "lbl-class"=>"control-label col-sm-4", "wrap-class"=>"col-sm-8", "class"=>"form-control")
			);

        form::inpField(
            "E-mail:",
            "email",
            empty($data["email"]) ? "" : $data["email"],
            $args
        );

        form::inpSaveCancel(null, array("fld-class" => "form-group", "wrap-class" => "col-sm-9 col-sm-offset-2", "class" => "btn btn-primary"), "Send");

        self::renderForm();
        form::closeDiv();
        form::closeDiv();
        form::closeDiv();
    }

    final public static function InvalidSetPasswordKey($key = "")
    {
        form::html('<div class="content bg-white">');
        form::html('<div class="row">');
        form::html('<div class="col-sm-12 col-md-12 col-lg-12">');

        self::renderForm("Set new password", "get");
        $args = array("fld-class" => "form-group", "lbl-class" => "control-label col-sm-4 col-md-3", "wrap-class" => "col-sm-12 col-md-5 col-lg-5", "class" => "form-control");

        form::html("<div class='alert alert-danger'>
		You have already set a password using this link. If you have forgotten it - please go to the login form and click \"Forgotten password\" to send a new link.
		</div>");

        self::renderForm();
        form::closeDiv();
        form::closeDiv();
        form::closeDiv();
    }

    final public static function CreateAccountSuccessfully()
    {
        form::html('<div class="content bg-white">');
        form::html('<div class="row">');
        form::html('<div class="col-sm-12 col-md-12 col-lg-12">');

        form::html("<h3>Welcome to our team</h3><div>&nbsp;</div><div class='alert alert-success'><strong>Account created successfully.</strong><div>Welcome to our system. Please keep your password safe and don't disclose it to anyone.</div></div>");

        form::closeDiv();
        form::closeDiv();
        form::closeDiv();
    }

    final public static function ChangePasswordSuccessfully()
    {
        form::html('<div class="content bg-white">');
        form::html('<div class="row">');
        form::html('<div class="col-sm-12 col-md-12 col-lg-12">');

        form::html("<div>&nbsp;</div><div class='alert alert-success'><strong>PASSWORD CHANGED</strong><div><br/><a href='/profile'>Click here to sign in >></a></div></div>");

        form::closeDiv();
    }

    final public static function changePassword()
    {
        form::html('<div class="content bg-white">');
        form::html('<div class="row">');
        form::html('<div class="col-sm-12 col-md-12 col-lg-12">');

        self::renderForm("Change password",'post','change-password');
        $args = array("fld-class" => "form-group", "lbl-class" => "control-label col-sm-4 col-md-3", "wrap-class" => "col-sm-12 col-md-5 col-lg-5", "class" => "form-control");

        form::inpField(
            "Old password:",
            "passOld",
            empty($data["passOld"]) ? "" : $data["passOld"],
            $args + array("type" => "password")
        );

        form::inpField(
            "New password:",
            "pass",
            empty($data["pass"]) ? "" : $data["pass"],
            $args + array("type" => "password")
        );

        form::inpField(
            "Re new password:",
            "passRe",
            empty($data["passRe"]) ? "" : $data["passRe"],
            $args + array("type" => "password")
        );

        form::html('<div class="fld form-group">
                        <label class="control-label col-sm-4 col-md-3"></label>
                        <div class="' . $args["wrap-class"] . '">
                        <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
                        <small>The password must be at least 8 characters long and must include both letters and numbers. Please make sure to choose a hard to guess password. Don\'t use your name or anything else that is easy to guess.</small>
                        </div>
                        </div>
                    </div>');

        form::html('
        <div class="fld form-group">
            <label class="control-label col-sm-4 col-md-3"> </label>
            <div class="' . $args["wrap-class"] . '">
            <label><input type="checkbox" name="confirm"> I confirm I will not give access to my account to anybody else and only I will sign documents in my account</label>
            ' . (!empty(form::$error["confirm"]) ? '<span class="err">' . form::$error["confirm"][0] . '</span>' : "") . '
            </div>
        </div>');

        form::inpSaveCancel(null, array("fld-class" => "form-group", "wrap-class" => "col-sm-9 col-sm-offset-3", "class" => "btn btn-primary"), "Change");

        self::renderForm();
        form::closeDiv();
        form::closeDiv();
        form::closeDiv();
    }

    final static function Summary($dt, $pg, $lbls)
    { 
	    global $_US_STATES;
		
        $extLnk = " <i class=\"fa fa-external-link\" aria-hidden=\"true\"></i>";
		//print_r(PositionModel::dataById($dt[$pg["pfx"] . "HiredForPID"]));
        if ($dt[$pg["pfx"] . "PID"] == -1) {
            $position = "Not sure";
        } else {
            $position = PositionModel::dataById($dt[$pg["pfx"] . "HiredForPID"])[PositionModel::$dt["pfx"] . "Name"];
        }
		
		 

        
        $imgFile = ApplyFormModel::IMG_PATH . str_replace("{---LiveID---}", $dt[$pg["key"]], ApplyFormModel::IMG_FILENAME);
        $flds4panel = array();
		
        if (file_exists($imgFile)) {
            $flds4panel["Photo"] = "<img src='" . WP . $imgFile . "?r=" . rand(0, 9999) . "' class='applicant-photo' /><br/><small>Move over the picture to zoom in</small>";
        }
		
		 $add_txt = '';
		 $today = new DateTime();
		 if (isset($dt['afDateLeaving']) &&  ($today->format('Y-m-d')< $dt['afDateLeaving'])) {
						$add_txt='<span style="color:red;font-size:1.5em;">In leaving notice, leaving '.
						val::convDate(MYSQL_DATE,USER_DATE,$dt['afDateLeaving'])
						.'</span>';
		 }
		 $add_txt='';// no more show leaving
		 if ($add_txt != '') echo ''.$add_txt.'';
		 
        $flds4panel[$lbls[($c = $pg["pfx"] . "FirstName")]] = $dt[$c];
        $flds4panel[$lbls[($c = $pg["pfx"] . "LastName")]] = $dt[$c];
		$flds4panel[$lbls[($c = $pg["pfx"] . "SSNmb")]] = $dt[$c];
		
		//$flds4panel[$lbls[($c = $pg["pfx"] . "NoPayrollJA")]] = $dt[$c];
		$flds4panel[$lbls[($c = $pg["pfx"] . "EmplNumber")]] = $dt[$c];

		$flds4panel[$lbls[($c = $pg["pfx"] . "WorkPhone")]] = $dt[$c];
		$flds4panel[$lbls[($c = $pg["pfx"] . "WorkPhoneExt")]] = $dt[$c];
		
		if (val::positionRequirePhysicianField($dt['afHiredForPID'])) {
			
			//if ($dt['afNYSMedicalLicenseExpDate'] == '12/31/1969')  $dt['afNYSMedicalLicenseExpDate']='';
			$flds4panel[$lbls[($c = $pg["pfx"] . "NYSMedLicenseNum")]] = $dt[$c];
			
			//$flds4panel[$lbls[($c = $pg["pfx"] . "NYSMedicalLicenseExpDate")]] = val::convDate(MYSQL_DATE,USER_DATE,$dt[$c]);
			$flds4panel[$lbls[($c = $pg["pfx"] . "NPINum")]] = $dt[$c];
			$flds4panel[$lbls[($c = $pg["pfx"] . "CAQHNum")]] = $dt[$c];
		}
		
		$flds4panel[$lbls[($c = $pg["pfx"] . "MaritalStatus")]] = $dt[$c];
		
		$flds4panel[$lbls[($c = $pg["pfx"] . "Gender")]] = $dt[$c];
		//$flds4panel[$lbls[($c = $pg["pfx"] . "SSNmb")]] = $dt[$c];
		$flds4panel[$lbls[($c = $pg["pfx"] . "AddressLine1")]] = $dt[$c];
		$flds4panel[$lbls[($c = $pg["pfx"] . "AddressLine2")]] = $dt[$c];
		$flds4panel[$lbls[($c = $pg["pfx"] . "City")]] = $dt[$c];
		
		if (isset($_US_STATES[$dt[$c]])) $v=$_US_STATES[$dt[$c]]; else $v='';
		$flds4panel[$lbls[($c = $pg["pfx"] . "State")]] = $_US_STATES[$dt[$c]];
		
		
		
		
		
		
        $flds4panel[$lbls[($c = $pg["pfx"] . "Birthday")]] =val::convDate(MYSQL_DATE_TIME,USER_DATE,$dt[$c]);
        $flds4panel[$lbls[($c = $pg["pfx"] . "CellPhone")]] = $dt[$c];
        $flds4panel[$lbls[($c = $pg["pfx"] . "HomePhone")]] = htmlspecialchars($dt[$c]);
        
		
		 $flds4panel[$lbls[($c = $pg["pfx"] . "EmcContact")]] = htmlspecialchars($dt[$c]);
		 $flds4panel[$lbls[($c = $pg["pfx"] . "EmergencyRelationship")]] = htmlspecialchars($dt[$c]);
        $flds4panel[$lbls[($c = $pg["pfx"] . "EmergencyPhone")]] = $dt[$c];
        $flds4panel[$lbls[($c = $pg["pfx"] . "CellPhone")]] = $dt[$c];
		
		
		
        $flds4panel[$lbls[($c = $pg["pfx"] . "Email")]] = form::linkOut("mailto:" . $dt[$c], $dt[$c]);
        $flds4panel["Password"] = form::linkOut(WP . "change-password", "Change password >>");
        $flds4panel[$lbls[($c = $pg["pfx"] . "HiredForPID")]] = $position;
		
        $flds4panel[$lbls[($c = $pg["pfx"] . "Department")]] = $dt[$c];
        $flds4panel[$lbls[($c = $pg["pfx"] . "Office")]] = $dt[$c];
        $flds4panel[$lbls[($c = $pg["pfx"] . "Manager")]] = $dt[$c];
		
		$companies = CompanyModel::AllActiveIDName();
        $flds4panel[$lbls[($c = $pg["pfx"] . "Company")]] = $companies[$dt[$c]];
        $flds4panel[$lbls[($c = $pg["pfx"] . "Salary")]] = (PositionModel::formatSalary($dt[$c]))  . ' ' . strtolower($dt["afSalaryType"]);
        
		
		 $flds4panel[$lbls[($c = $pg["pfx"] . "HiredForPID")]] = $position;
		 $flds4panel[$lbls[($c = $pg["pfx"] . "DateHired")]] = val::convDate(MYSQL_DATE,USER_DATE,$dt[$c]);
		
        $flds4panel[$lbls[($c = $pg["pfx"] . "StartDate")]] = val::convDate(MYSQL_DATE,USER_DATE,$dt[$c]);
        

        $cvs = $dt['cvs'][CVModel::APPLICATION_CV];
        if (!empty($cvs)) {
            $flds4panel[$lbls[($c = $pg["pfx"] . "Bio")]] = form::linkOut($cvs["fileURL"], $cvs["cvFileName"] . " <i class='fa fa-download'></i>");
        }
        /*
        $cvs = $dt['cvs'][CVModel::CURRENT_CV];
        if (!empty($cvs)) {
            $flds4panel[$lbls[($c = $pg["pfx"] . "CurrentCV")]] = form::linkOut($cvs["fileURL"], $cvs["cvFileName"] . " <i class='fa fa-download'></i>");
        }
         */

        $flds4panel[$lbls[($c = $pg["pfx"] . "ShortResume")]] = nl2br(htmlspecialchars($dt[$c]));
        $flds4panel[$lbls[($c = $pg["pfx"] . "SpecialSkills")]] = nl2br(htmlspecialchars($dt[$c]));

        self::DataPanel($flds4panel, (!empty($pg["item"]) ? $pg["item"] . " summary" : null), "panel-default"
		 ,null,'col-sm-3 lbl' );

		
        form::openTag("form", array("method" => "post", "enctype" => "multipart/form-data", "validate" => true, "class" => "form-horizontal"));
        $args = self::$fldArgsBase;
        form::inpSaveCancel(null, array("fld-class" => "form-group", "wrap-class" => "col-sm-9", "class" => "btn btn-primary"), "Edit");
        form::closeTag("form");
    }

    final public static function EditProfile($data, $pg, $lbls)
    {	global $_US_STATES,$_RELS,$_GENDER,$_M_STATUS;
        
        
		//print_r(form::$error);
        form::html('<div class="content bg-white">');
        form::html('<div class="row">');
        form::html('<div class="col-sm-12 col-md-12 col-lg-12">');

        // form::html('<h3>Edit profile</h3>');
        form::tag("&nbsp;", "div");

        $args = self::$fldArgsBase;
        $args["wrap-class"] = "col-sm-12 col-md-6 col-lg-6";
        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"));
        form::$ofsetIndentsCount = 2;

        form::openTag("form", array("method" => "post", "enctype" => "multipart/form-data", "validate" => true, "class" => "form-horizontal"));
		
		
		/************************>>>>>>>>>>>>>************/
		echo '<input type="hidden" name="comming_from" value="my_profile">';
		echo '<input type="hidden" name="afID" value="'.$data['afID'].'">';
		$imgFile = ApplyFormModel::IMG_PATH . str_replace("{---LiveID---}", 
		   $data[$pg["key"]], ApplyFormModel::IMG_FILENAME);
      echo '<div id="image_begin"></div>';
	   $args = array('fld-class'=>'form-group','lbl-class'=>'control-label col-sm-4 col-md-3',
				'wrap-class'=>'col-sm-8 col-md-7 col-lg-5','class'=>'form-control');
	   
	   $args2=array('fld-class'=>'form-group','lbl-class'=>'control-label col-sm-4 col-md-3',
				'wrap-class'=>'col-sm-8 col-md-7 col-lg-5','class'=>'form-control auto-height');
	
	     $text = "Upload photo:";
		 
        if (file_exists($imgFile)) {
            $photo = "<img src='" . WP . $imgFile . "?r=".rand(0,9999)."' class='applicant-photo' /><br/><small>Move over the picture to zoom in</small>";
            form::inpInfo("Photo:", $photo, $args2);
		      $text = "Replace photo:";
           // if (!$isManager) {
                $bio = "<label><input type='radio' value='download' name='photoCmd' checked> <a href='" . WP . "{$imgFile}' id='downloadPhoto' target='_blank'><i class='fa fa-download'></i>download</a></label> | 
                    <label><input type='radio' value='remove' name='photoCmd'> <i class='fa fa-trash-o'></i>delete</input></label>";
			}
			
			form::inpImg(
                    $text,
                    "afPhoto",
                    "",
                    $args + array("hint" => "Please upload only .jpg or .png file")
                );
          
		    if (file_exists($imgFile)) {
			form::inpInfo(
                    " ",
                    $bio,
                    $args
                );
			}
			 
			 form::html('<div class="fld form-group">
                    <label class="control-label col-sm-4 col-md-3"></label>
                    <div class="col-sm-8 col-md-6 col-lg-5"><div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
                    <small>Please upload only .jpg or .png file</small></div></div></div>');
       
			
                
		/*******************************************/
		
		$f = $pg["pfx"] . "Birthday";
        if (!empty($data[$f]) && ($data[$f] != "0000-00-00") && 
		  !(isset($_POST['comming_from']) && $_POST['comming_from']=='my_profile')) {
            $data[$f] = val::convDate(MYSQL_DATE_TIME,USER_DATE,$data[$f]);
        }
		
		form::inpField(
            $lbls[($f = $pg["pfx"] . "Birthday")] . ":",
            $f,
            $data[$f],
            $args +array("hint" => "example: ".USER_DATE_EXAMPLE)
        );
		
		if ($data[$pg["pfx"] . "SSNmb"] !='') $read_only=true;
		else	$read_only=false;
		form::inpField(
            $lbls[($f = $pg["pfx"] . "SSNmb")] . ":",
            $f,
            empty($data[$f]) ? "" : $data[$f],
            $args + array("hint" => "Format:  xxx-xx-xxxx") + ($read_only ? ["readonly" => "readonly"] : [])
        );
		
		/*******************************************/
		
		echo '<input type="hidden" name="afHiredForPID" value="'.$data['afHiredForPID'].'">';
		if (val::positionRequirePhysicianField($data['afHiredForPID'])) {
		form::inpField(
            $lbls[($f = $pg["pfx"] . "NYSMedLicenseNum")] . ":",
            $f,
            $data[$f],
            $args 
        );
		
		/************
		if ($data["afNYSMedicalLicenseExpDate"] == '0000-00-00') $data["afNYSMedicalLicenseExpDate"]='';
			form::inpField(
				$lbls[($f = $pg["pfx"] . "NYSMedicalLicenseExpDate")] . ":",
				$f,
				empty($data[$f]) ? "" :  val::convDate(MYSQL_DATE,USER_DATE,$data[$f]),
				
				$args + array("hint" => "Example: ".USER_DATE_EXAMPLE) 
			);
		**********/
			form::inpField(
				$lbls[($f = $pg["pfx"] . "NPINum")] . ":",
				$f,
				$data[$f],
				$args 
			);
			
			form::inpField(
				$lbls[($f = $pg["pfx"] . "CAQHNum")] . ":",
				$f,
				$data[$f],
				$args 
			);
		}
		
		
		
		/******************************************/
		
		$readonly=true;
		
		form::inpField(
            $lbls[($f = $pg["pfx"] . "StartDate")] . ":",
            $f,
            empty($data[$f]) ? "" :  val::convDate(MYSQL_DATE,USER_DATE,$data[$f]),
            $args + array("hint" => "example: ".USER_DATE_EXAMPLE) + ($read_only ? ["readonly" => "readonly"] : [])
        );
		
		form::inpField(
            $lbls[($f = $pg["pfx"] . "DateHired")] . ":",
            $f,
            empty($data[$f]) ? "" :  val::convDate(MYSQL_DATE,USER_DATE,$data[$f]),
            $args +  array("hint" => "example: ".USER_DATE_EXAMPLE) + ($read_only ? ["readonly" => "readonly"] : [])
        );
		form::inpField(
            $lbls[($f = $pg["pfx"] . "EmplNumber")] . ":",
            $f,
            empty($data[$f]) ? "" : $data[$f],
            $args + ($read_only ? ["readonly" => "readonly"] : [])
        );
		
		if ($readonly == true) {
			$_name = $pg["pfx"] . "Gender";
			echo '<input type="hidden" name = "'.$_name.'" value="'.$data[$_name].'">';
		}
		form::inpSelect(
            $lbls[($f = $pg["pfx"] . "Gender")] . ":",
            $f,
			  $_GENDER,
			  empty($data[$f]) ? "" :$data[$f],
            $args
			 + ($readonly ? ["disabled" => "true"]  : [])
        );
		
		$readonly=false;
		form::inpSelect(
            $lbls[($f = $pg["pfx"] . "MaritalStatus")] . ":",
            $f,
			  $_M_STATUS,
			  empty($data[$f]) ? "" :$data[$f],
            $args + ($readonly ? ["readonly" => "readonly"] : [])
        );
		
		echo '<hr>';
		echo '<h4 style="margin-left:100px"><b>Home Address</b></h4>';
		$readonly=false;
		
		form::inpField(
            $lbls[($f = $pg["pfx"] . "AddressLine1")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args + ($readonly ? ["readonly" => "readonly"] : [])
        );
		
		form::inpField(
            $lbls[($f = $pg["pfx"] . "AddressLine2")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args + ($readonly ? ["readonly" => "readonly"] : [])
        );
		
		
		form::inpField(
            $lbls[($f = $pg["pfx"] . "City")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args + ($readonly ? ["readonly" => "readonly"] : [])
        );
		
		
		form::inpSelect(
            $lbls[($f = $pg["pfx"] . "State")] . ":",
            $f,
			  $_US_STATES,
			  empty($data[$f]) ? "" :$data[$f],
            $args + ($readonly ? ["readonly" => "readonly"] : [])
        );
		
		form::inpField(
            $lbls[($f = $pg["pfx"] . "Zip")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args + ($readonly ? ["readonly" => "readonly"] : [])
        );
        
		echo '<hr>';
        form::inpField(
            $lbls[($f = $pg["pfx"] . "Email")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args
        );

        form::html('
            <div class="fld form-group">
                <label class="control-label col-sm-4 col-md-3">Password:</label>
                <div class="col-sm-12 col-md-9 col-lg-9"><a class="line-height-34" href="' . WP . 'change-password">Change Password >></a></div>
            </div>');

        form::inpField(
            $lbls[($f = $pg["pfx"] . "CellPhone")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args + ["hint" => "Format: xxx-xxx-xxxx"]
        );

        form::inpField(
            $lbls[($f = $pg["pfx"] . "HomePhone")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args + ["hint" => "Format: xxx-xxx-xxxx"]
        );
		
		form::inpField(
            $lbls[($f = $pg["pfx"] . "WorkPhone")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args + ["hint" => "Format: xxx-xxx-xxxx"]
        );
		
		form::inpField(
            $lbls[($f = $pg["pfx"] . "WorkPhoneExt")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args 
        );

        form::html('<div class="fld form-group">
        <label class="control-label col-sm-4 col-md-3"></label>
        <div class="col-sm-8 col-md-6 col-lg-6"><div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
        <small>This information will appear on the internal employee directory and can be viewed by all other employees</small></div></div></div>');

        form::inpField(
            $lbls[($f = $pg["pfx"] . "EmcContact")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args
        );
		
		form::inpField(
            $lbls[($f = $pg["pfx"] . "EmergencyPhone")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args + ($readonly ? ["readonly" => "readonly"] : []) + ["hint" => "Format: xxx-xxx-xxxx"]
        );
		
		form::inpSelect(
            $lbls[($f = $pg["pfx"] . "EmergencyRelationship")] . ":",
            $f, 
			  $_RELS,
			  empty($data[$f]) ? "" :$data[$f],
            $args + ($readonly ? ["readonly" => "readonly"] : [])
        );
			
		
		
		
		
		
		

        form::inpField(
            $lbls[($f = $pg["pfx"] . "ShortResume")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args + array("type" => "textarea", "rows" => 5)
        );

        form::inpField(
            $lbls[($f = $pg["pfx"] . "SpecialSkills")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args 
            + array("type" => "textarea", "rows" => 5
//             , "hint" => 'Your special skills are things that you can do and help people with inside the company that not everyone can do. For example, you can write:
// "I am an expert at helping with Big Ant set up" or "I have a great understanding of Blue Cross coding an I am happy to help any of the providers"'
)
        );
        form::html('<div class="fld form-group">
                <label class="control-label col-sm-4 col-md-3"></label>
                <div class="col-sm-8 col-md-6 col-lg-6"><div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
                <small>Your skills and hobbies are things that you can do and help people with inside the company that not everyone can do. For example, you can write:
                "I am an expert at helping with Big Ant set up" or "I have a great understanding of Blue Cross coding an I am happy to help any of the providers"</small></div></div></div>');
        /*
        form::inpField(
            $lbls[($f = $pg["pfx"]."CurrentCV")].":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args + array("type" => "file")
        );
         */
/**
        form::inpImg(
            "Photo:",
            "afPhoto",
            htmlspecialchars(""),
            $args
        );
**/
        
        form::inpSaveCancel(WP . "my-profile", self::$saveCancelArgsBase);
        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));

        form::html('</div></div></div></div>');

    }
	
	final public static function listEmployeesIAmManagerFor($query,$rq,$pg,$lbls)
    { 
		
		table::create($pg["tbl"], $pg["key"], "desc", self::ADMIN_LISTS_ROWS_LIMIT);
		table::$cols[] = Array("Name", "afName");
		table::$cols[] = Array("E-mail", "afEmail");
		
		table::$cols[] = Array("Position", "afHiredPosition");
		//table::$cols[] = Array("Job", "afJob");
		table::$cols[] = Array("Company", "afCompanyName");
		table::$cols[] = Array("Department", "afDepartment");
		table::$cols[] = Array("Company office", "afOffice");
		//echo $query;
       table::execute($query);
	    if (table::$export === false)
		 foreach (table::$data as $r => &$cells){
			 $cells['afName']='<a href="/admin/employee-details?id='.$cells['afID'].'">'
			 .$cells['afName'].'<a>';
		 }

       table::load();
		
    }
	
	final public static function listEmployeesIAmManagerForJSON($query,$rq,$pg,$lbls)
    { 
		table::create($pg["tbl"], $pg["key"], "desc", self::ADMIN_LISTS_ROWS_LIMIT);
		table::$cols[] = Array("Name", "afName");
		table::$cols[] = Array("E-mail", "afEmail");
		
		table::$cols[] = Array("Position", "afHiredPosition");
		//table::$cols[] = Array("Job", "afJob");
		table::$cols[] = Array("Company", "afCompanyName");
		table::$cols[] = Array("Department", "afDepartment");
		table::$cols[] = Array("Company office", "afOffice");
//echo $query;

        table::execute($query);
		 foreach (table::$data as $r => &$cells){
			 $cells['afName']='<a href="/admin/employee-details?id='.$cells['afID'].'">'
			 .$cells['afName'].'<a>';
		 }

        table::load();
		
    }
}
