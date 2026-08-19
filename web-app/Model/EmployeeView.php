<?php
class EmployeeView extends ApplicationView
{
	
	 public static function TerminateEmployeeView($data,$pg,$lbls){
		  $args = array ( 'fld-class' => 'form-group','lbl-class' => 'control-label col-sm-4 col-md-3',
		  'wrap-class' => 'col-sm-8 col-md-7 col-lg-5','class' => 'form-control');
		  
		  //'afDateLeaving','afLeavingReason','afTerminatedOrRetired'
		  form::openTag("form", array("method" => "post", "enctype" => "multipart/form-data", "validate" => true, "class" => "form-horizontal"));
		
		  $f = $pg["pfx"] . "DateLeaving";
		  if (isset($_POST[$f])) $_date = $_POST[$f];
		  else $_date = val::convDate(MYSQL_DATE,USER_DATE,$data[$f]);
        form::inpField(
            $lbls[$f] .":",
            $f,
            $_date,
            $args + ['required'=>'required','id'=>'terminateDate','hint'=>'Example: '.USER_DATE_EXAMPLE]
        );
		
		 form::inpField(
            $lbls[($f = $pg["pfx"] . "LeavingReason")] . ":",
            $f,
            htmlspecialchars($data[$f]),
            $args //+ array("type" => "textarea", "rows" => "6")
        );

       $ck2=''; $ck1='';$ck3='';
		if ($data['afTerminatedOrRetired'] == 'Terminated'){
			$ck1='checked'; $ck2='';$ck3='';
		}
		if ($data['afTerminatedOrRetired'] == 'Retired'){
			$ck2='checked'; $ck1='';$ck3='';
		}
		if ($data['afTerminatedOrRetired'] == 'Resigned'){
			$ck3='checked'; $ck1='';$ck2='';
		}

		form::html('
            <div class="fld form-group">
                <label class="control-label col-sm-4 col-md-3">'.$lbls[$f=$pg["pfx"]."TerminatedOrRetired"].':'.ASTERISK.'</label>
                <div class="col-sm-8 col-md-7 col-lg-5" style="position:relative;top:6px;">
				  <input required style="cursor:pointer" id="ck1" type="radio" name="'.$f.'" value="Terminated" '.$ck1.'> <label style="cursor:pointer" for="ck1">Terminated</label>
				  &nbsp;&nbsp;&nbsp;&nbsp;
				  <input recuired style="cursor:pointer" id="ck2" type="radio" name="'.$f.'" value="Retired" '.$ck2.'> <label style="cursor:pointer" for="ck2">Retired</label>
				  &nbsp;&nbsp;&nbsp;&nbsp;
				  <input recuired style="cursor:pointer" id="ck3" type="radio" name="'.$f.'" value="Resigned" '.$ck3.'> <label style="cursor:pointer" for="ck3">Resigned</label>
				   
				  </div>
            </div>');
		
		$_r=ProfileController::listProfileIAmManagerFor($_GET['id']);
		
		if(!empty($_r)) {
			if (!isset($data['afNewManager'])) $data['afNewManager']='';
			form::inpSelect(
            'Transfer employees to:'.ASTERISK,
            'afNewManager',
			  EmployeeModel::getManagersExceptThis($_GET['id']),
			  $data['afNewManager'],
            $args
        );
			
		}
		
		$_r = EmployeeModel::listOnboardingIAmResponsibleFor($_GET['id']);
		//cf::pr($_r);
		if (!empty ($_r)){
			if (!isset( $data['afNewResponsible']))  $data['afNewResponsible'] ='';
			form::inpSelect(
            'Transfer onboarding tasks to:'.ASTERISK,
            'afNewResponsible',
			  EmployeeModel::getEmplyeesExceptThis($_GET['id']),
			  $data['afNewResponsible'],
            $args
        );
			
			
		}
		
		
		$arg_base = array();//self::$saveCancelArgsBase;
		$arg_base['wrap-class'] = 'col-sm-8 col-md-8 col-lg-6 col-sm-offset-3 col-md-offset-3';
		
		form::inpSaveCancel('employee-details?id='.$_GET['id'], $arg_base + 
			["class"=>"btn btn-danger",
			"class-cancel"=>"btn btn-default","name"=>"btnSaveTerminateEmployee",
			"id" => "btnSaveTerminateEmployee",
			"onclick"=>"return check_date()"],
			"Terminate employee",
			"<i class='fa fa-arrow-left' ></i> Cancel");
			
			echo '<script>
			
			function get_date(){
				var ret;
				jQuery.ajax({
				url: "?cmd=get_date",
				success: function (result) {
				  ret=result;
				},
				async: false
			});
			return ret;
			}
			
			function check_date(){
				var dateStr=jQuery("#terminateDate").val();
				var todayStr = new Date(get_date());
				var today = new Date(todayStr);
				today.setHours(0,0,0,0);
				var date = new Date(dateStr);
				
				if ( today.getTime()  >= date.getTime()  ){
				return confirm("You\'ve entered the employee as leaving today or in past.\nThis will terminate him/her IMMEDIATELY. Are you sure?");
				} else {
				return true;
				}				
			}
			</script>';
		
		form::closeDiv();
	 }

    static protected function buildHistoryModal($forField, $dialogTitle)
    {

        $previous = FieldHistoryModel::getAllHistory($forField, EmployeeController::$id);
        if (!empty($previous)) {
            if (count($previous) == 1) {
                return false;
            }

            $previousHTML = "<table class='table'><tr><th>From date</th><th>Value</th></tr>";
            foreach ($previous as $previou) {
                $previousHTML .= "<tr><td>{$previou['fhAdded']}</td><td>{$previou['fhValue']}</td></tr>";
            }
            $previousHTML .= "</table>";

            $modal = '<div class="modal fade" id="' . $forField . 'Modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">' . $dialogTitle . '</h4>
      </div>
      <div class="modal-body" style="max-height: 400px; overflow-y: auto; ">
        ' . $previousHTML . ' 
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>';
            form::html($modal);
            return true;
        } else {
            return false;
        }
    }


    public static function AddHiredForPositionBlockAsManager($data, $pg, $lbls, $hasPrevious, $args, $args1, $args2)
    {
		 
	
        $f = $pg["pfx"] . "HiredForPID";
		
        form::inpInfo(
            $lbls[$f] . ":",
            $data["afHiredPosition"],
            ($hasPrevious ? $args1 + 
			array("hint" => "<a href='#' data-toggle='modal' data-target='#positionModal'>See previous >></a>") : $args) 
			+ array("readonly" => "readonly")+['onchange'=>'alert(1)']
        );
		

        if (($data[$pg["pfx"] . "HiredForPID"] === '9999') || (filter_input(INPUT_POST, $pg["pfx"] . "HiredForPID") === "9999")) {
            $infoDisplay = "";
        } else {
            $infoDisplay = ' style = "display: none;" ';
        }
		
			
        form::html('<div id="managerInfo"' . $infoDisplay . '>');
        $positions = implode(', ', array_column(PositionModel::data("pID in (" . implode(',', $data[$pg["pfx"] . "ResponsibleForPID"]) . ")"), "pName"));
        form::inpInfo(
            $lbls[($f = $pg["pfx"] . "ResponsibleForPID")] . ":",
            $positions,
            $args2 + array("readonly" => "readonly")
        );
        $offices = implode(', ', array_column(CompanyOfficeModel::data("coID in (" . implode(',', $data[$pg["pfx"] . "ResponsibleForCOID"]) . ")"), "coName"));
        form::inpInfo(
            $lbls[($f = $pg["pfx"] . "ResponsibleForCOID")] . ":",
            $offices,
            $args2 + array("readonly" => "readonly")
        );
        form::html('</div>');
    }

    public static function AddHiredForPositionBlockAsAdmin($data, $pg, $lbls, $hasPrevious, $args, $args1, $args2)
    {
        $f = $pg["pfx"] . "HiredForPID";
        $positions = PositionModel::AllActiveKeyName($data[$f]);

        $position = PositionModel::dataById($data[$f]);
        if (@$position["pCanRemove"] !== "No") {
            $manager = "regular";
        } else {
            $q = 'select count(afID) from apply_forms where afActive="Hired" and afRMID=' . $data["afID"];
            $emplCnt = db::PrepareLoadSingleCellValue($q);
            if ($emplCnt > 0) {
                $manager = "manager";
            } else {
                $manager = "regular";
            }
        }
		
		 $add='';
		 
		 if ( $data['afIsManager'] == 'yes') {
			  $d=EmployeeModel::GetActiveResponsibleForEmployees($data["afID"]);
			   $c=count($d);
			   if ($c==0) 
					$add.='<span style="font-style: italic">No assigned employees</span>';
			   else 
				   $add.='<span style="font-style: italic">'.$c.' employee(s)</span> '.
					form::linkOut('/admin/employees-of-manager?list=emp&id='. $data["afID"], 'View>>');
					
			   
		   }
		 
		 
		 if (empty($_POST)) $ast=ASTERISK; else $ast='';
        if (strpos($_SERVER['REQUEST_URI'],'employee-details') !== false ) $ast='';
		
        form::inpSelect(
            $lbls[$f] .":". $ast,
            $f,
            $positions,
            $data[$f],
            ($hasPrevious ? $args1 + array("data" => $manager,
			 "add"=>'',
			 "hint" => 
			 "<a href='#' data-toggle='modal' data-target='#positionModal'>See previous >></a>") : $args) + array("id" => "HiredForPID")
        );
		
		$days=PositionModel::getJSONDaysOFF();
		echo '
			<script>
			var daysPerPos;
			jQuery( document ).ready(function() {
				jQuery("#HiredForPID").bind("change",changePosition);
				daysPerPos=jQuery.parseJSON(\''.$days.'\');
				if (jQuery("#Hourly").is(":checked")) 
						jQuery("#cedays").val("0").attr("readonly","readonly");
			});
			
			function changePosition() { 
				setDefValueCE();
				setDefValueVac();
			}
			
			function setCEdays(that){
					if (jQuery(that).val()=="Hourly") {
						jQuery("#cedays").val("0").attr("readonly","readonly");
					}
					else {
						jQuery("#cedays").removeAttr("readonly");
						setDefValueCE();
						setDefValueVac();
					}
			}
			
			function setDefValueCE(){
				var v=jQuery("#HiredForPID").find(":selected").val();
				if (jQuery("#Hourly").is(":checked")) 
					{jQuery("#cedays").val("0").attr("readonly","readonly");}
				else
					{
						jQuery("#cedays").val(daysPerPos[v]["cedays"]);
						jQuery("#cedays").removeAttr("readonly");
					}
			}
			
			function setDefValueVac(){
				var v=jQuery("#HiredForPID").find(":selected").val();
				jQuery("#vacdays").val(daysPerPos[v]["vacdays"]);
			}
			</script>
		';
		
		if ($data['afIsManager']=="yes") $checked='checked'; else $checked='';
		
		$add='';
		
		if ($data['afIsManager']=="yes")  {
						   $_data=EmployeeModel::GetActiveResponsibleForEmployees($_GET['id']);
						   $c=count($_data);
						   if ($c==0) 
								$add='<span style="font-style: italic">No assigned employees</span>';
						   else {
							   $add='<span style="font-style: italic">'.$c.' employee(s)</span> '.
								form::linkOut('/admin/employees-of-manager?list=emp&id='. $_GET['id'], 'View>>');
						   }
			
		}
		if (isset($data['afDateLeaving'])) $dis='disabled';
		else $dis ='';
		form::html('
            <div class="fld form-group">
                <label style="padding-top:12px" class="control-label col-sm-4 col-md-3">Is Manager:</label>
                <div class="col-sm-8 col-md-7 col-lg-5" style="margin-top:7px">
				   <input type="hidden"   name="afIsManager" value="no" >
				   <input '.$dis.' style="width:20px;height:20px" type="checkbox" name="afIsManager" value="yes" '.$checked.'>
				   <br>'.$add.'</div>
            </div>');
		//if ($data['afIsManager']=="yes") echo '<input type="hidden"   name="afIsManager" value="yes" >';


        if (($data[$pg["pfx"] . "HiredForPID"] === '9999') || (filter_input(INPUT_POST, $pg["pfx"] . "HiredForPID") === "9999")) {
            $infoDisplay = "";
        } else {
            $infoDisplay = ' style = "display: none;" ';
        }
        form::html('<div id="managerInfo"' . $infoDisplay . '>');

        $args1 = $args;
        $args1["wrap-class"] .= " checklist-top-margin";
        form::inpCheckList(
            $lbls[($f = $pg["pfx"] . "ResponsibleForPID")] . ":",
            $f,
            PositionModel::AllActiveKeyName(null, false),
            empty($data[$f]) ? [] : $data[$f],
            $args1 + array("type" => "checkbox")
        );

        form::inpCheckList(
            $lbls[($f = $pg["pfx"] . "ResponsibleForCOID")] . ":",
            $f,
            CompanyOfficeModel::AllActiveKeyName($data[$f]),
            empty($data[$f]) ? [] : $data[$f],
            $args1 + array("type" => "checkbox")
        );

        form::html('</div>');
    }

    public static function AddHiredForPositionBlock($data, $pg, $lbls, $hasPrevious, $args, $args1, $args2)
    {
        if (intval(AppModel::$admin["waaAFID"]) > 0) {
            self::AddHiredForPositionBlockAsManager($data, $pg, $lbls, $hasPrevious, $args, $args1, $args2);
        } else {
            self::AddHiredForPositionBlockAsAdmin($data, $pg, $lbls, $hasPrevious, $args, $args1, $args2);
        }
    }

    static function Details($data, $pg, $lbls, $list)
    {
       // if ($data["afActive"] !== "Hired") {cf::redirect($pg["hired"]["url"]);}
		  
        $args = self::$fldArgsBase;
        if (isset(AppModel::$admin["waaAFID"])) {
            $args = self::$fldArgsBase + array("readonly" => "readonly", "skip-empty" => true);
            $isManager = true;
        } else {
            $isManager = false;
        }

        $args1 = $args;
        $args1["class"] = "form-control width-70 left";
        $args1["hint-class"] = "no-border left line-height-34 ";
        $args1["prefix-class"] = "form-control no-border left";

        $args1Normal = self::$fldArgsBase;
        $args1Normal["class"] = "form-control width-70 left";
        $args1Normal["hint-class"] = "no-border left line-height-34 ";
        $args1Normal["prefix-class"] = "form-control no-border left";

        $args1Right = $args;
        $args1Right["hint-class"] = "no-border right ";
        $args1Left = $args;
        $args1Left["hint-class"] = "no-border left ";
        $args1Left["class"] = "form-control auto-height";


        $args2 = $args;
        $args2["class"] .= " auto-height ";


        $tasks = EmployeeOnboardingModel::LoadEmployeeOnboardingTasks(EmployeeController::$id,'','','on');
		 $offboarding_tasks = 
		 EmployeeOnboardingModel::LoadEmployeeOnboardingTasks(EmployeeController::$id,'','','off');
		//echo "<pre>";print_r($offboarding_tasks);echo "</pre>";
        $isDone = true;
		 $isDoneOffboarding=true;
        foreach ($tasks as $item) {
            if (empty($item["eoCompletedOn"])) {
                $isDone = false;
                break;
            }
        }
		
		 foreach ($offboarding_tasks as $item) {
            if (empty($item["eoCompletedOn"])) {
                $isDoneOffboarding = false;
                break;
            }
        }

        $creds = EmployeeCredentialingModel::LoadEmployeeCredentialings(
				EmployeeController::$id,""," ecUploadedOn ASC, ecDueDate DESC",'','admin');
								
//echo '<pre>';print_r($creds);echo '</pre>';

        $onboardingActive = (filter_has_var(INPUT_GET, "cmd") && filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "undo");

        if ($onboardingActive) {
            form::Tag("Profile", "button", ["type" => "button", "id" => "employee_record_button", "class" => "btn btn-secondary"]);
        } else {
            form::Tag("Profile", "button", ["type" => "button", "id" => "employee_record_button", "class" => "btn btn-primary"]);
        }
        if ($isDone) {
            if ($onboardingActive) {
                form::Tag("Onboarding complete", "button", ["type" => "button", "id" => "employee_onboarding_button", "data" => "secondary", "class" => "btn btn-primary"]);
            } else {
                form::Tag("Onboarding complete", "button", ["type" => "button", "id" => "employee_onboarding_button", "data" => "secondary", "class" => "btn btn-secondary"]);
            }
        } else {
            form::Tag("Onboarding: Incomplete", "button", ["type" => "button", "id" => "employee_onboarding_button", "data" => "danger", "class" => "btn btn-danger"]);
        }
		
		
		$offboardingActive = (filter_has_var(INPUT_GET, "cmd") && filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "undo");

      
		if ($data["afActive"]=='Terminated' || !empty($data["afDateLeaving"])) {
		if ($isDoneOffboarding) {
            if ($offboardingActive) {
                form::Tag("Off-boarding complete", "button", ["type" => "button", "id" => "employee_offboarding_button", "data" => "secondary", "class" => "btn btn-primary"]);
            } else {
                form::Tag("Off-boarding complete", "button", ["type" => "button", "id" => "employee_offboarding_button", "data" => "secondary", "class" => "btn btn-secondary"]);
            }
        } else {
            form::Tag("Off-boarding: Incomplete", "button", ["type" => "button", "id" => "employee_offboarding_button", "data" => "danger", "class" => "btn btn-danger"]);
        }
		}
		
		
		
		
		
        form::Tag("Credentialing", "button", ["type" => "button", "id" => "employee_credentialing_button", "data" => "secondary", "class" => "btn btn-secondary"]);

		 form::Tag("Files", "button", ["type" => "button", "id" => "assigned_files_button", "class" => "btn btn btn-secondary"]);

        form::Tag("", "hr", ["style" => "margin-top:0px; border-width: 3px; border-color: #337ab7;"]);

        if ($onboardingActive) {
            form::openDiv(array("id" => "employee_record_content", "style" => "display:none;"));
        } else {
            form::openDiv(array("id" => "employee_record_content"));
        }
        self::RenderDetailsForm($data, $pg, $lbls, $isManager, $args, $args1, $args1Normal, $args1Left, $args2);
        form::closeDiv();

        
		if ($onboardingActive) {
            form::openDiv(array("id" => "employee_onboarding_content", "class" => "container-fluid"));
        } else {
            form::openDiv(array("id" => "employee_onboarding_content", "class" => "container-fluid", "style" => "display:none;"));
        }
		
        self::RenderOnboardingTasks($tasks);
        form::closeDiv();
		
		 if ($data["afActive"]=='Terminated' || !empty($data["afDateLeaving"])){
			 if ($offboardingActive ) {
				form::openDiv(array("id" => "employee_offboarding_content", "class" => "container-fluid","style" => "display:none;"));
			}
			else {
				form::openDiv(array("id" => "employee_offboarding_content", "class" => "container-fluid", "style" => "display:none;"));
			}
			
			self::RenderOnboardingTasks($offboarding_tasks);
			form::closeDiv();
		 }
		

		 
        form::openDiv(array("id" => "employee_credentialing_content", "class" => "container-fluid", "style" => "display:none;"));
        self::RenderCredentialings($creds);
        form::closeDiv();
		 
		 form::openDiv(array("id" => "assigned_files_content", "class" => "container-fluid","style" => "display:none;"));
		 self::RenderAssignedFiles();
		 form::closeDiv();
    }
	
	public static function RenderAssignedFiles()
	{
		$filedata = FileCategoriesController::getFilesForEmployee();
		FileCategoriesView::RenderAssignedFiles($filedata);
		if (isset($_GET['tab']) &&  $_GET['tab']=='ass_files'){
			echo '<script>showAssignedFilesContent();</script>';
		}
		
	}

    public static function RenderOnboardingTasks($data = [])
    {
        OnboardingView::RenderOnboardingTasks($data,true,'admin');
    }

    public static function RenderCredentialings($data = [])
    {
        CredentialingView::RenderAdminCredentialings($data);
    }

    public static function RenderDetailsForm($data, $pg, $lbls, $isManager, $args, $args1, $args1Normal, $args1Left, $args2)
    {
		  global $_US_STATES,$_RELS,$_GENDER,$_M_STATUS;
		  
        $imgFile = ApplyFormModel::IMG_PATH . str_replace("{---LiveID---}", $data[$pg["key"]], ApplyFormModel::IMG_FILENAME);
        $bioFile = ApplyFormModel::BIO_PATH . str_replace("{---LiveID---}", $data[$pg["key"]], ApplyFormModel::BIO_FILENAME);

        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/ ));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "enctype" => "multipart/form-data", "validate" => true, "class" => "form-horizontal"));
		
		 
		 
		 if (!empty($data['afConnectedToProfile']) && $data['afConnectedToProfile']!=' '){
		    EmployeeModel::showConnected($data);
		 }
		 
		 else {
		  if ($data['afConnectedToProfile']!=' '  && $data['afActive']=='Hired'){
			
			$former_id=array();
			$former_id = ApplicationModel::checkIfSuchExist($data["afFirstName"],
				$data["afLastName"],$data["afBirthday"]);
			$id_of_former_empl=$former_id['afID'];
			if (! empty($former_id)) {
			  $former_ids = EmployeeModel::getFormerIds($id_of_former_empl);
			  $txt = $data["afFirstName"].' '.$data["afLastName"]." 
			  <a style=\"text-decoration:underline\" target=\"_blank\" href=\"employee-details?id={$former_id['afID']}&type=terminated\">View >>></a><br>";

			   form::html('<div class="fld form-group">
				<label class="control-label col-sm-4 col-md-3"></label>
				<div class="col-sm-7 col-md-5 col-lg-5">
			   <div class="alert alert-info">
			   <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				
					&nbsp;There is a former employees with the same name and birthday:<br>
					<p style="text-align:center">
					'.$txt.'
					</p>
					Would you like to "connect" the two profiles as the same person?
				<br><br><p style="text-align:center">
				<a href="javascript:void(0)" onclick="connect_profiles('.$id_of_former_empl.','.$data["afID"].
				',\''.$former_ids.'\')" class="btn btn-primary">Connect</a>
				<a href="javascript:void(0)" onclick="ask_no_more_connect('.$data["afID"].')" class="btn btn-default">No, it is different person</a>
				
				</p>
				</div>
		   
			  </div></div>
			  <script>
			   function connect_profiles(former_id,id,former_ids){
				   jQuery.get( "employee-details?cmd=connect-profiles&id="+id+
				   "&former_id="+former_id+"&former_ids="+former_ids
				   ,
				   function( data ) { 
					  if (data=="OK") {
						  jQuery(".alert-info").alert("close");
						  alert("The profiles were connected.");
						  location.reload();
					  }
					  else myalert("Alert","There was a problem to connect profiles.");
					});
			   }
			   
			   function ask_no_more_connect(id){
				if (confirm(
"If you continue you can not anymore connect this profile with existing ones.Are you sure to continue?")){
				jQuery.get( "employee-details?cmd=ask_no_more_connect&id="+id
				   ,
				   function( data ) { 
					  if (data=="OK") {
						  jQuery(".alert-info").alert("close");
					  }
					  else myalert("Alert","There was a problem to rehect connecting.");
					});
				}
			   }


				</script>
			  ');
			 }
		 }
		 } 
		 
		
		
        form::inpField(
            $lbls[($f = $pg["pfx"] . "EmplNumber")] . ":",
            $f,
            htmlspecialchars($data[$f]),
            $args
        );
        form::inpField(
            $lbls[($f = $pg["pfx"] . "FirstName")] . ":",
            $f,
            htmlspecialchars($data[$f]),
            $args
        );
        form::inpField(
            $lbls[($f = $pg["pfx"] . "LastName")] . ":",
            $f,
            htmlspecialchars($data[$f]),
            $args
        );
        form::inpField(
            $lbls[($f = $pg["pfx"] . "Email")] . ":",
            $f,
            htmlspecialchars($data[$f]),
            $args
        );
		
				
        if (!$isManager) {
            form::html('
            <div class="fld form-group">
                <label class="control-label col-sm-4 col-md-3">Password:</label>
                <div class="col-sm-8 col-md-7 col-lg-5"><a class="line-height-34" href="' . $pg["password"]["url"] . '?id=' . $data[$pg["key"]] . '" title="' . $pg["password"]["title"] . '">Change Password</a></div>
            </div>');
        }
		



        $hasPrevious = self::buildHistoryModal('position', 'Position history');
        self::AddHiredForPositionBlock($data, $pg, $lbls, $hasPrevious, self::$fldArgsBase, $args1Normal, $args2);
		
		
		//cf::pr($data["onboarding_tasks"]);
        if (!empty($data["onboarding_tasks"])) {
            if (count($data["onboarding_tasks"]) === 1) {
                $data["onboarding_tasks"][] = "&nbsp;";
            }
            form::inpInfo(
                "Onboarding Tasks Responsible For",
                implode("<br/>", $data["onboarding_tasks"]),
                $args2 + array("readonly" => "readonly")
            );
        }
		
		 if (!empty($data["offboarding_tasks"])) {
            if (count($data["offboarding_tasks"]) === 1) {
                $data["offboarding_tasks"][] = "&nbsp;";
            }
            form::inpInfo(
                "Off-boarding Tasks Responsible For",
                implode("<br/>", $data["offboarding_tasks"]),
                $args2 + array("readonly" => "readonly")
            );
        }

        $hasPrevious = self::buildHistoryModal('office', 'Company office history');
        if (intval(AppModel::$admin["waaAFID"]) > 0) {
            form::inpInfo(
                $lbls[($f = $pg["pfx"] . "COID")] . ":",
                $data["afOffice"],
                ($hasPrevious ? $args1Normal + array("hint" => "<a href='#' data-toggle='modal' data-target='#officeModal'>See previous >></a>") : self::$fldArgsBase) + array("readonly" => "readonly")
            );

        } else {
            form::inpSelect(
                $lbls[($f = $pg["pfx"] . "COID")] . ":",
                $f,
                CompanyOfficeModel::AllActiveKeyName(),
                $data[$f],
                ($hasPrevious ? $args1Normal + array("hint" => "<a href='#' data-toggle='modal' data-target='#officeModal'>See previous >></a>") : self::$fldArgsBase)
            );
        }
		
		
		
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
		
		$f = $pg["pfx"] . "Birthday";
		
	   if (isset($_POST['comming_from']) && $_POST['comming_from'] == 'edit'  )
			$_date = $_POST[$f];
		else $_date = val::convDate(MYSQL_DATE_TIME,USER_DATE,$data[$f]); 
		 form::inpField(
            $lbls[$f] . ":",
            $f,
            $_date, 
			  
            $args + array("hint" => "Example: ".USER_DATE_EXAMPLE) + ($readonly ? ["readonly" => "readonly"] : [])
        );
		
		
		
		form::inpField(
            $lbls[($f = $pg["pfx"] . "SSNmb")] . ":",
            $f,
            empty($data[$f]) ? "" : $data[$f],
            $args + array("hint" => "Format:  xxx-xx-xxxx") + ($readonly ? ["readonly" => "readonly"] : [])
        );
		
		/*******************************************/
		
		//echo '<input type="hidden" name="afHiredForPID" value="'.$data['afHiredForPID'].'">';
		if (val::positionRequirePhysicianField($data['afHiredForPID'])) {
		form::inpField(
            $lbls[($f = $pg["pfx"] . "NYSMedLicenseNum")] . ":",
            $f,
            $data[$f],
            $args 
        );
		
		/*********
		if ($data["afNYSMedicalLicenseExpDate"] == '0000-00-00') $data["afNYSMedicalLicenseExpDate"]='';
			form::inpField(
				$lbls[($f = $pg["pfx"] . "NYSMedicalLicenseExpDate")] . ":",
				$f,
				empty($data[$f]) ? "" :  val::convDate(MYSQL_DATE,USER_DATE,$data[$f]),
				//date(USER_DATE, strtotime($data[$f])),<<<
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
		
		
		form::inpSelect(
            $lbls[($f = $pg["pfx"] . "Gender")] . ":",
            $f,
			  $_GENDER,
			  empty($data[$f]) ? "" :$data[$f],
            $args + ($readonly ? ["readonly" => "readonly"] : [])
        );
		
		form::inpSelect(
            $lbls[($f = $pg["pfx"] . "MaritalStatus")] . ":",
            $f,
			  $_M_STATUS,
			  empty($data[$f]) ? "" :$data[$f],
            $args + ($readonly ? ["readonly" => "readonly"] : [])
        );
		

        form::inpField(
            $lbls[($f = $pg["pfx"] . "CellPhone")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args + ($readonly ? ["readonly" => "readonly"] : []) + ["hint" => "Format: xxx-xxx-xxxx"]
        );

        form::inpField(
            $lbls[($f = $pg["pfx"] . "HomePhone")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args + ($readonly ? ["readonly" => "readonly"] : []) + ["hint" => "Format: xxx-xxx-xxxx"]
        );
		
		echo '<hr>';
		
		 form::inpField(
            $lbls[($f = $pg["pfx"] . "EmcContact")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args + ($readonly ? ["readonly" => "readonly"] : [])
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
		echo '<hr>';

		
      
        form::html('<div class="fld form-group">
        <label class="control-label col-sm-4 col-md-3"></label>
        <div class="col-sm-7 col-md-5 col-lg-5"><div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
        <small>This information will appear on the internal employee directory and can be viewed by all other employees</small></div></div></div>');
        
        form::inpField(
            $lbls[($f = $pg["pfx"] . "WorkPhone")] . ":",
            $f,
            htmlspecialchars($data[$f]),
            $args
        );
        form::inpField(
            $lbls[($f = $pg["pfx"] . "WorkPhoneExt")] . ":",
            $f,
            htmlspecialchars($data[$f]),
            $args
        );
        
        $hasPrevious = self::buildHistoryModal('department', 'Department history');
		
        form::inpSelect(
            $lbls[($f = $pg["pfx"] . "DID")] . ":",
            $f,
            ($isManager ? [$data[$f] => DepartmentModel::AllIdNameArr()[$data[$f]]] : DepartmentModel::AllIdNameArr()),
            $data[$f],
            ($hasPrevious ? $args1 + array("hint" => "<a href='#' data-toggle='modal' data-target='#departmentModal'>See previous >></a>") : $args)
        );

        $f = $pg["pfx"] . "RMID";
        if ($isManager) {
            $managers = EmployeeModel::getManagersExceptThis($data["afID"]);
            if (isset($managers[$data[$f]])) {
                $managers = [$data[$f] => $managers[$data[$f]]];
            } else {
                $managers = [];
            }
        } else {
            $managers = EmployeeModel::getManagersExceptThis($data["afID"]);
            if (isset($managers[$data["afID"]])) {
                unset($managers[$data["afID"]]);
            }
        }
        form::inpSelect(
            $lbls[($f = $pg["pfx"] . "RMID")] . ":",
            $f,
            $managers,
            $data[$f],
            $args
        );
        form::inpSelect(
            $lbls[($f = $pg["pfx"] . "Company")] . ":",
            $f,
            ($isManager ? [$data[$f] => $data[$f]] : ["{!--Empty--!}" => "Please select"] + CompanyModel::AllActiveNameName()),
            $data[$f],
            $args
        );

		 
        $hasPrevious = self::buildHistoryModal('salary', 'Salary history');
		
		 
        form::inpField(
            $lbls[($f = $pg["pfx"] . "Salary")] . ":",
            $f,
            $data[$f],
            ($hasPrevious ? $args1 + array("hint" => "<a href='#' data-toggle='modal' data-target='#salaryModal'>See previous >></a>") : $args1) + array("prefix" => GLOBAL_MONEY_SIGN)
        );

/***************   SALARY TYPE AND JA **********************/
		  form::openDiv();
        form::openDiv(array("class" => "fld form-group"));
        form::tag("Salary Type:" . ASTERISK, "label", ["class" => "control-label  col-sm-4 col-md-3"]);

        form::openDiv(["class" => "col-sm-8 col-md-7 col-lg-5"]);

       
		 $f = $pg["pfx"] . "SalaryType";
		
		  if (!isset($data[$f])) {$_ck1='checked'; $_ck2='';}
		  else {
			if ($data[$f]=='Hourly') {$_ck1='checked'; $_ck2='';}
			if ($data[$f]=='Annual') {$_ck1=''; $_ck2='checked';}
		  }
		 
		 echo '<div class = "row">
				&nbsp;&nbsp;
				<input id="Hourly" style="cursor:pointer" type="radio" 
				name="'.$f.'" '.$_ck1.' value="Hourly"  onchange="setCEdays(this)"> 
				<label  for="Hourly" style="font-weight:normal;cursor:pointer">Hourly</label>
				&nbsp;&nbsp;
				<input id="Annual" style="cursor:pointer" type="radio" 
				 name="'.$f.'" '.$_ck2.' value="Annual" onchange="setCEdays(this)"> 
				<label for="Annual" style="font-weight:normal;cursor:pointer">Annual</label>
				</div>';
					
        if (isset(form::$error["pSalaryTypeErr"])) {
            form::html('<span class="err">' . form::$error["pSalaryTypeErr"][0] . '</span>');
        }
		
		form::closeDiv();
		form::closeDiv();
		
/****************** JA *********************/		
		
		if ($data['afNoPayrollJA']=="yes") $_checked='checked'; else $_checked='';
		
		$add='';
		//print_r(form::$error);
		form::html('
            <div class="fld form-group">
                <label style="padding-top:12px" class="control-label col-sm-4 col-md-3">
				   No Payroll (Joinder Agreement):</label>
                <div class="col-sm-8 col-md-7 col-lg-5" style="margin-top:7px">
				   <input type="hidden"   name="afNoPayrollJA" value="no" >
				   <input style="width:20px;height:20px" type="checkbox" id="afNoPayrollJA"
					onchange="set_salary()"
				   name="afNoPayrollJA" value="yes" '.$_checked.'>
				   <br>'.$add.'</div>
            </div>
			<script>
			var oldSalaryType = jQuery(\'input[name="afSalaryType"]:checked\').val();
			var oldSalary = jQuery(\'input[name="afSalary"]\').val();
			
			set_salary();
			function set_salary(){
				if (jQuery("#afNoPayrollJA").is(":checked")) {
					oldSalaryType = jQuery(\'input[name="afSalaryType"]:checked\').val();
					oldSalary = jQuery(\'input[name="afSalary"]\').val();
					jQuery("#Hourly").prop("checked",true)
					jQuery(\'input[name="afSalaryType"]\').attr("disabled",true);
					jQuery(\'input[name="afSalary"]\').val("0.00").attr("readonly",true);
				}
				else {				
					if (oldSalaryType=="Hourly") var id="#Hourly"; else var id="#Annual";
					jQuery(id).prop("checked",true);
					jQuery(\'input[name="afSalaryType"]\').attr("disabled",false);
					jQuery(\'input[name="afSalary"]\').val(oldSalary).attr("readonly",false);
				}
				
				setDefValueCE();
			}
			</script>');
/*******************   END SALART TYPE AND JA ****************/
			$d=date('Y',strtotime($data['afStartDate']));
			/********
			if (date('Y')==$d){
				$cf=cf::getDaysToAsPercent($data['afStartDate']);
				$days=round($data['afVacationDays']*$cf);
				$hintArr=array('hint'=>
				"<span style=\"color:blue\">INFO: For year {$d} paid days are {$days} for set {$data['afVacationDays']} days.</span>");
			}
			else $hintArr=array();
			***********/	
			$hintArr=array();
			
		 	form::inpField(
            $lbls[($f = $pg["pfx"] . "VacationDays")] . ":",
            $f,
            $data[$f],
            $args +['id'=>'vacdays','style'=>'width:5em']+$hintArr
        );
		
			form::inpField(
            $lbls[($f = $pg["pfx"] . "CEDays")] . ":",
            $f,
            $data[$f],
            $args +['id'=>'cedays','style'=>'width:5em']
         );
		 
        if (file_exists("../" . $imgFile)) {
            $photo = "<img src='" . WP . $imgFile .'?r='.rand(0,9999). "' class='admin-applicant-photo' /><br/><small>Move over the picture to zoom in</small>";
          echo '<div id="image_begin"></div>';  
			form::inpInfo("Photo:", $photo, $args2);

            if (!$isManager) {
                $bio = "<label><input type='radio' value='download' name='photoCmd' checked> <a href='" . WP . "{$imgFile}' id='downloadPhoto' target='_blank'><i class='fa fa-download'></i>download</a></label> | 
                    <label><input type='radio' value='remove' name='photoCmd'> <i class='fa fa-trash-o'></i>delete</input></label>";

                form::inpInfo(
                    " ",
                    $bio,
                    $args
                );

                form::inpImg(
                    "Replace photo:",
                    "afPhoto",
                    "",
                    $args + array("hint" => "Please upload only .jpg or .png file")
                );
				  
            }
        } else {
            if (!$isManager) {
				   echo '<div id="image_begin"></div>';
                form::inpImg(
                    "Photo:",
                    "afPhoto",
                    "",
                    $args1Left + array("hint" => "<strong>No photo uploaded.</strong> Please upload only .jpg or .png file")
                );
				  
            }
        }

        $fldargs = $args1Left;
        if (!empty(form::$error['afBio'])) {
            $err = '<br/><span class="err">' . form::$error['afBio'][0] . '</span>';
            $fldargs["class"] = $fldargs["class"] . " error";
        } else {
            $err = '';
        }
        if (!empty($data["cvs"][CVModel::APPLICATION_CV])) {
            $file = $data["cvs"][CVModel::APPLICATION_CV];
            if (!$isManager) {
                $bio = "<label><input type='radio' value='download' name='bioCmd' checked> <a href='" . $file['fileURL'] . "' id='downloadBio'><i class='fa fa-download'></i> " . $file["cvFileName"] . "</a></label> |
                    <label><input type='radio' value='remove' name='bioCmd'> <i class='fa fa-trash-o'></i>delete</input></label> |
                    <label><input type='radio' value='upload' name='bioCmd'> <i class='fa fa-upload'></i>replace</input></label>
                    <span id='selectBioFile' style='display: none;'>&nbsp;&nbsp;&nbsp;<input type='file' name='afBio' style='display: inline-block;'></span>";
             //print_r($fldargs)   ;
				form::inpInfo(
                    "Application CV:",
                    $bio,
                    $fldargs + array("hint" => "This is the <strong>Application CV</strong>. Please don't update it except in case of technical problem.<br/>
                Please upload only .doc, .docx or .pdf" . $err)
                );
            } else {
                $bio = "<label><a href='" . $file['fileURL'] . "' id='downloadBio'><i class='fa fa-download'></i> " . $file["cvFileName"] . "</a></label>";
                form::inpInfo(
                    "Application CV:",
                    $bio,
                    $fldargs
                );
            }
        } else {
            if (!$isManager) {
                form::inpField(
                    $lbls[($f = $pg["pfx"] . "Bio")] . ":",
                    $f,
                    empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
                    $args + array("type" => "file", "hint" => "<strong>No file uploaded.</strong> Please don't update it except in case of technical problem.<br/>
                Please upload only .doc, .docx or .pdf")
                );
            }
        }

        form::html("<hr/>");
		 $argsOld = $args;
        $args = $args + array("readonly" => "readonly");
        $args1 = $args1 + array("readonly" => "readonly");
        $args2 = $args2 + array("readonly" => "readonly");
		echo '<input type="hidden" value="edit"  name="comming_from" >';
	   
	   $f = $pg["pfx"] . "DateHired";
	   if (isset($_POST['comming_from']) && $_POST['comming_from'] = 'edit'  )
			$_date = $_POST[$f];
		else $_date = val::convDate(MYSQL_DATE,USER_DATE,$data[$f]); 
		form::inpField(
            $lbls[$f] . ":",
            $f,
			  $_date,
			  
            $argsOld+array("hint" => "Example: ".USER_DATE_EXAMPLE)
        );
		
		  $f = $pg["pfx"] . "StartDate";
		  if (isset($_POST['comming_from']) && $_POST['comming_from'] = 'edit'  )
		  $_date = $_POST[$f];
		  else $_date = val::convDate(MYSQL_DATE,USER_DATE,$data[$f]); 
		
        form::inpField(
            $lbls[$f] . ":",
            $f,
            $_date,
			    //date(USER_DATE, strtotime($data[$f])),
            $argsOld+array("hint" => "Example: ".USER_DATE_EXAMPLE)
        );

        form::inpField(
            $lbls[($f = $pg["pfx"] . "Added")] . ":",
            $f,
			  val::convDate(MYSQL_DATE_TIME,GLOBAL_PHP_DATETIME,$data[$f]),
            //date("n/d/Y h:i:s A", strtotime($data[$f])),<<<
            $args
        );

        $f = $pg["pfx"] . "JID";
        $readOnly = [];
        if (!empty($data[$f])) {
            if (isset($list["jobs"][$data[$f]])) {
                $list["jobs"] = [$data[$f] => $list["jobs"][$data[$f]]];
            } else {
                $list["jobs"] = [];
            }
            $readOnly["readonly"] = "readonly";

            if (!empty($readOnly)) {
                form::inpInfo(
                    $lbls[$f] . ":",
                    isset($list["jobs"][$data[$f]]) ? htmlspecialchars($list["jobs"][$data[$f]]) : "not set",
                    $args2
                );
            }
        }
        $f = $pg["pfx"] . "PID";
        $position = PositionModel::dataById($data[$f]);
        $readOnly = [];
        if (isset($data[$f])) {
            if (!isset($list["positions"][$data[$f]])) {
                if ($data[$f] == 0) {
                    $list["positions"][0] = "Not sure";
                } else {
                    $missed = PositionModel::dataById($data[$f]);
                    $list["positions"][$data[$f]] = $missed["pName"];
                }
            }
            $list["positions"] = [$data[$f] => $list["positions"][$data[$f]]];
            $readOnly["readonly"] = "readonly";
            $readOnly["skip-empty"] = "skip-empty";
        }

        if (!empty($readOnly)) {
            form::inpInfo(
                $lbls[$f] . ":",
                $list["positions"][$data[$f]],
                $args + array("readonly" => "readonly")
            );
        }


        form::inpInfo(
            $lbls[($f = $pg["pfx"] . "DesiredSalary")] . ":",
            PositionModel::formatSalary($data[$f]) . " " . strtolower($data[$pg["pfx"] . "DesiredSalaryType"]),
            $args + array("readonly" => "readonly")
        );

        form::tag("", "input", array("name" => $f, "type" => "hidden", "value" => $data[$f]));

        form::inpField(
            $lbls[($f = $pg["pfx"] . "Skills")] . ":",
            $f,
            str_replace("|", ", ", $data[$f]),
            $args + array("readonly" => "readonly")
        );

        form::inpField(
            $lbls[($f = $pg["pfx"] . "SpecialSkills")] . ":",
            $f,            
            //nl2br($data[$f]) . "&nbsp;",
			  $data[$f],
            $argsOld + array("type" => "textarea")
        );
		  $args21=$args2;
		  unset($args21['readonly']);
        form::inpField(
            $lbls[($f = $pg["pfx"] . "ShortResume")] . ":", 
			  $f,
            $data[$f],
            $args21 + array("type" => "textarea")
        );

        $questions = json_decode($data["afQuestionnaire"]);
        $questHtml = self::RenderQuestionnarieInfo($questions);
        if (!empty($questHtml)) {
            form::inpInfo("Questionnaire:", $questHtml, $args2);
        }

        form::inpField(
            $lbls[($f = $pg["pfx"] . "IP")] . ":",
            $f,
            htmlspecialchars($data[$f]),
            $args
        );

        $q = "SELECT * FROM logs where lafID=%d order by lID desc";
        $q = sprintf($q, $data[$pg["key"]]);
        $logs = db::PrepareLoad($q);
        if (($logs !== false) && (!empty($logs))) {
            $log = $logs[0];
            $logData = json_decode($log["lData"]);
            form::inpInfo("Last Login:", "<strong>IP address:</strong> " . $logData->IP . "<br/>
                                            <strong>Date/Time: </strong>" 
											//. date(GLOBAL_PHP_DATETIME, strtotime($log["lTimestamp"])) <<<
											.val::convDate(MYSQL_DATE,USER_DATE,$log["lTimestamp"])
											. "<div class='row' style='padding-right: 10px;'><a class='btn btn-primary right' data-toggle='modal' data-target='#logsModal'>View all logins >></a></div>", $args2);


            $logsCnt = count($logs);
            $logsHTML = "<table class='table'>
                            <thead>
                                <tr><th width='10%'>IP Address</th><th width='20%'>Date/Time</th><th width='20%'></th></tr>         
                            </thead>
                            <tbody>";
            for ($i = 0; $i < $logsCnt; $i++) {
                $log = $logs[$i];
					//print_r($log);echo "<hr>";
					if ( $log["lLoggedAs"] == 0 ) $logged = 'employee himself/herself'; else $logged = 'admin';
                $logsHTML .= "<tr>
                                <td>" . $logData->IP . "</td>
                                <td>" 
								     . val::convDate(MYSQL_DATE,USER_DATE,$log["lTimestamp"])
								     //. date(GLOBAL_PHP_DATETIME, strtotime($log["lTimestamp"])) <<<
									  . "</td>
									  <td>" .  $logged . "</td>
                              </tr>";
            }

            $logsHTML .= "</tbody></table>";


            $modal = '
<div class="modal fade" id="logsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">' . $data["afFirstName"] . ' ' . $data["afLastName"] . ' Log</h4>
      </div>
      <div class="modal-body" style="max-height: 400px; overflow-y: auto; ">
        ' . $logsHTML . ' 
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>';
            form::html($modal);
        }

       

        self::RenderApplicationsAfterHired($data["afID"], $args2);
		unset($args['readonly']);
		 form::inpField(
            $lbls[($f = $pg["pfx"] . "Comments")] . ":",
            $f,
            htmlspecialchars($data[$f]),
            $args + array("type" => "textarea", "rows" => "6")
        );
		
		/**********   TERMINATION DATA ***************/
		  if (isset($data['afDateLeaving']) ){
			$today = new DateTime();
			//echo '>>>>>>>>>>>>>>>'.$data['afDateLeaving'];
			if ($data['afActive']== 'Terminated' || 
			   ($data['afDateLeaving'] !='' && $data['afDateLeaving'] <= $today->format(MYSQL_DATE))
			) {
				$addarr=array('readonly'=>'readonly');
				$addstr ='disabled';
			}
			else {
				$addarr=array();
				$addstr = '';
			}
			
		  form::html('<hr/>');
		  echo '<h4 style="margin-left:100px"><b>Termination data</b></h4>';
		  
		  $f = $pg["pfx"] . "DateLeaving";
		  
		  if (isset($_POST[$f])) $_date = $_POST[$f];
		  else $_date = val::convDate(MYSQL_DATE,USER_DATE,$data[$f]); 
		  
		  form::inpField(
            $lbls[$f] . ":",
            $f,
            $_date,
            $args + array('id'=>'terminateDate')
        );
		
		 form::inpField(
            $lbls[($f = $pg["pfx"] . "LeavingReason")] . ":",
            $f,
            htmlspecialchars($data[$f]),
            $args + $addarr
        );
		
		
		if ($data['afTerminatedOrRetired'] == 'Terminated'){
			$ck1='checked'; $ck2='';$ck3='';
		}
		
		if ($data['afTerminatedOrRetired'] == 'Retired'){
			$ck1=''; $ck2='checked';$ck3='';
		}
		
		if ($data['afTerminatedOrRetired'] == 'Resigned'){
			$ck1=''; $ck2='';$ck3='checked';
		}
		
		form::html('
            <div class="fld form-group">
                <label class="control-label col-sm-4 col-md-3">'.$lbls[$f=$pg["pfx"]."TerminatedOrRetired"].':'.'</label>
                <div class="col-sm-8 col-md-7 col-lg-5" style="position:relative;top:6px;">
				  <input '.$addstr.' style="cursor:pointer" id="ck1" type="radio" name="'.$f.'" value="Terminated" '.$ck1.'> <label style="cursor:pointer" for="ck1">Terminated</label>
				  &nbsp;&nbsp;&nbsp;&nbsp;
				  <input '.$addstr.' style="cursor:pointer" id="ck2" type="radio" name="'.$f.'" value="Retired" '.$ck2.'> <label style="cursor:pointer" for="ck2">Retired</label>
				  &nbsp;&nbsp;&nbsp;&nbsp;
				  <input recuired style="cursor:pointer" id="ck3" type="radio" name="'.$f.'" value="Resigned" '.$ck3.'> <label style="cursor:pointer" for="ck3">Resigned</label>
				 
				  </div>
            </div>');
		
		  
		  }

        form::closeDiv();

        if (intval(AppModel::$admin["waaAFID"]) > 0) {
            form::html('
                <div class="fld form-group">
                    <div class="col-sm-8 col-md-7 col-lg-5 col-sm-offset-3 col-md-offset-2">
                    <button type="button" onclick="location.assign(\'' . EmployeeController::getBackUrl()["url"] . '\')" class="btn btn-default">Back</button>
                    </div>
                </div>
            ');
        } else {
            form::inpSaveCancel(EmployeeController::getBackUrl()["url"], 
			self::$saveCancelArgsBase + ((!in_array($data["afActive"], ["Inactive"])) 
			? 
			array("id" => "btnSaveApplication",'onclick'=>"return check_date()")
			: 
			array()));
			
			echo '<script>
			function get_date(){
				var ret;
				jQuery.ajax({
				url: "?cmd=get_date",
				success: function (result) {
				  ret=result;
				},
				async: false
			});
			return ret;
			}
			
			function check_date(){
				var dateStr=jQuery("#terminateDate").val();
				var today = new Date(get_date());
				today.setHours(0,0,0,0);
				var date = new Date(dateStr);
				date.setHours(0,0,0,0);
				if ( today.getTime()  >= date.getTime()  ){
				return confirm("You\'ve entered the employee as leaving today or past date.\nThis will terminate him/her IMMEDIATELY. Are you sure?");
				} else {
				return true;
				}				
			}
			</script>';;
        }
        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }

    public static function RenderQuestionnarieInfo($qestions)
    {
        $questHtml = "";
        if (!empty($qestions)) {
            foreach ($qestions as $q => $a) {
                $rPos = strpos($a, "{--rejected--}");
                if ($rPos) {
                    $a = str_replace("{--rejected--}", "", $a);
                    $questHtml .= "<strong class='red-bold'><strong>" . $q . "</strong><br/>" . $a . " <i class='fa fa-arrow-right'></i> App Rejected</strong><br/><br/>";
                } else {
                    $questHtml .= "<strong>" . $q . "</strong><br/>" . $a . "<br/><br/>";
                }
            }
        }
        return $questHtml;
    }

    public static function RenderApplicationsAfterHired($afID, $args)
    {
        $afID = intval($afID);
        $q = sprintf('select * from apply_forms where afID <> %d and afApplyAsEmployeeID = %d', $afID, $afID);
        $data = db::PrepareLoad($q);

        if (!empty($data)) {
            form::tag("Applications after hired", "h1");        
            foreach ($data as $item) {

                form::openDiv(["class" => "alert alert-info"]);

                form::inpInfo("Status:", $item["afActive"] === "Change Position" ? "Employee position changed" : $item["afActive"], $args);

				
                if (empty($item["afStartDate"])) {
                    $startdate = "n/a";
                }
				  else {
                    $startdate = val::convDate(MYSQL_DATE,USER_DATE,$item["afStartDate"]);
					   //date(USER_DATE, strtotime($item["afStartDate"]));<<<
                }
				
                form::inpInfo("Hired:", $startdate, $args);
                
					
				  form::inpInfo("Applied:", 
				   date(GLOBAL_PHP_DATETIME, strtotime($item["afAdded"])) , $args);
                
					form::inpInfo("Desired Job:", $item["afActive"], $args);
                form::inpInfo("Desired Position:", $item["afActive"], $args);
                form::inpInfo("Desired Salary:", PositionModel::formatSalary($item["afDesiredSalary"]) . " " . strtolower($item["afDesiredSalaryType"]), $args);
                if (!empty($item["afSkills"])) {
                    form::inpInfo("Skills:", str_replace("|", ", ", $item["afSkills"]), $args);
                }
                $qestions = json_decode($item["afQuestionnaire"]);
                $questHtml = self::RenderQuestionnarieInfo($qestions);
                if (!empty($questHtml)) {
                    form::inpInfo("Questionnaire:", $questHtml, $args);
                }
                form::inpInfo("IP address:", $item["afIP"], $args);

                form::closeDiv();
            }
        }


    }

    public static function SetPassword($data, $pg, $lbls, $list)
    {
        $args = self::$fldArgsBase;
        $args["type"] = "password";

        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/ ));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "enctype" => "multipart/form-data", "validate" => true, "class" => "form-horizontal"));

        form::inpField(
            "Password:",
            "pass",
            "",
            $args
        );
        form::inpField(
            "Re password:",
            "passRe",
            "",
            $args
        );

        form::inpSaveCancel(ApplicationController::getBackUrl()["url"], self::$saveCancelArgsBase + ((!in_array($data["afActive"], ["Hired", "Inactive"])) ? array("id" => "btnSaveApplication") : array()));
        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }


    public static function GetEmployeePhoto($id)
    {
        $imgFile = ApplyFormModel::IMG_PATH . str_replace("{---LiveID---}", $id, ApplyFormModel::IMG_FILENAME);
        if (file_exists($imgFile)) {
            $imgSrc = WP . $imgFile . '?r=' . rand(1000, 9999);
        } else {
            $imgSrc = ADD_IMG . 'employee-photo.png';
        }
        return $imgSrc;
    }

    public static function RenderFrontEmployeeList($emplList = [], $totalCnt = 0, $currentPage = 1, $pageSize)
    {
        form::openDiv(array('class' => "content bg-white"));
        form::openDiv(array('class' => "row"));


        form::openDiv(array("class" => "col-sm-12"));
        // form::tag("Employee Directory", "h3");

        self::RenderFilter();

        if (empty($emplList)) {
            form::html('<strong>No employees found</strong>');
        } else {
            foreach ($emplList as $empl) {

                $imgSrc = self::GetEmployeePhoto($empl["afID"]);

                form::html('
                    <div class="col-sm-6" >
                        <div class="card">
                        <div class="card-header"><a href="/employees?ID=' . $empl["afID"] . '">' . $empl["afFirstName"] . ' ' . $empl["afLastName"] . '</a></div>
                        <div class="card-body text-primary">
                        <img src="' . $imgSrc . '" class="card-photo">
                          Position: <span class="card-title">' . htmlspecialchars($empl["afHiredPosition"]) . '</span><br/>
                          Office: <span class="card-title">' . htmlspecialchars($empl["afOffice"]) . '</span><br/><br/>
                          <a href="/employees?ID=' . $empl["afID"] . '">Details >></a>                    
                        </div>
                      </div>
                    </div>');
            }
        }


        self::RenderPager($totalCnt, $currentPage, $pageSize);


        form::closeDiv(array("class" => "col-sm-12 col-md-6 col-lg-6"));


        form::openDiv(array('class' => "row"));
        form::closeDiv(array('class' => "content bg-white"));


    }

    public static function RenderFilter()
    {   
	     echo '<div id="search"><br><a class="btn btn-info" href="javascript:void(0)"  
		    onclick="jQuery(\'#search_form\').show();jQuery(\'#search\').hide();">Click here to search >></a></div>';
		 		 
		 
        $args = //array("fld-class" => "filter_group", "lbl-class" => "horizontal-label", "wrap-class" => "", "class" => "form-control");
       $args = array ( 'fld-class' => 'form-group',
	    'lbl-class' => 'control-label col-sm-4 col-md-3',
		'wrap-class' => 'col-sm-8 col-md-7 col-lg-5', 'class' => 'form-control');
		
		$btnargs = array("fld-class" => "filter_group auto-width ", 
		    "wrap-class" => "", 
			"class" => "btn btn-primary");
			
		$btnargs = array("fld-class" => "form-group ", 
		    "wrap-class" => "col-sm-8 col-md-7 col-lg-5 col-sm-offset-4 col-md-offset-3", 
			"class" => "btn btn-primary");
			
		
        
        form::tag("&nbsp;", "div");

        form::openDiv(array("class" => "row",'id'=>'search_form','style'=>'display:none'));
		echo '<div id="search"><a class="btn btn-info" href="javascript:void(0)"  
		    onclick="jQuery(\'#search_form\').hide();jQuery(\'#search\').show();">Hide search >></a></div>';
		 
        form::openDiv(array("class" => "col-sm-12"));

        form::openTag("form", array("method" => "GET", "enctype" => "multipart/form-data", "validate" => true, 
		"class" => "form-horizontal"));

        form::inpField("First Name", "firstname", filter_input(INPUT_GET, "firstname", FILTER_SANITIZE_STRING), $args);

        form::inpField("Last Name", "lastname", filter_input(INPUT_GET, "lastname", FILTER_SANITIZE_STRING), $args);

        form::inpSelect("Position", "position", array("-1" => "Any") + PositionModel::AllActiveKeyName(), filter_input(INPUT_GET, "position", FILTER_SANITIZE_NUMBER_INT), $args + array("skip-empty" => 1));

        form::inpSelect("Office", "office", array("-1" => "Any") + CompanyOfficeModel::AllIdNameArr(), filter_input(INPUT_GET, "office", FILTER_SANITIZE_NUMBER_INT), $args + array("skip-empty" => 1));

        form::inpSaveCancel(null, $btnargs, "Search");

        form::closeTag("form");

        form::closeDiv(array("class" => "col-sm-12"));
		 
        form::closeDiv(array("class" => "row"));
    }

    public static function RenderPager($totalCnt = 1, $currentPage = 1, $pageSize)
    {
        $query = [];
        $fields = ["firstname", "lastname", "position", "office"];
        foreach ($fields as $field) {
            if (filter_has_var(INPUT_GET, $field)) {
                $query[] = $field . "=" . filter_input(INPUT_GET, $field, FILTER_SANITIZE_STRING);
            }
        }
        $paging = new paging($totalCnt, $currentPage, $pageSize);
        echo $paging->render(WP . "employees", "?" . implode("&", $query));
    }

    public static function RenderEmployee($dt, $lbls, $pg)
    {

        form::openDiv(array("class" => "content bg-white"));
        form::openDiv(array("class" => "row"));
        form::openDiv(array("class" => "col-sm-12 col-md-12 col-lg-12"));
       
		 $today = new DateTime();
		
		$add_txt ='';
		if (isset($dt['afDateLeaving']) &&  ($today->format('Y-m-d')< $dt['afDateLeaving'])) {
						$add_txt='<br><span style="color:red;text-transform: none;font-size:0.8em;">In leaving notice, leaving '.
						val::convDate(MYSQL_DATE,USER_DATE,$dt['afDateLeaving'])
						.'</span>';
		 }
		 
		$add_txt ='';// no more show leaving
		 form::tag($dt["afFirstName"] . ' ' . $dt["afLastName"].$add_txt, "h3");
		 
        form::tag("", 'hr');

        form::openDiv(array("class" => "row"));
        form::openDiv(array("class" => "col-sm-12 col-md-3 col-lg-3"));
        $imgSrc = self::GetEmployeePhoto($dt["afID"]);
        form::tag("", "img", array("src" => $imgSrc));
        form::closeDiv(array("class" => "col-sm-12 col-md-6 col-lg-6"));

        // style part added by me PV to avoid overhead of image
        form::openDiv(array("class" => "col-sm-12 col-md-8 col-lg-8","style"=>"position:relative;left:90px"));

        form::openTag("dl", array("class" => "dl-horizontal extra-space"));

        form::tag("First name:", "dt");
        form::tag(htmlspecialchars($dt["afFirstName"]), "dd");
        form::tag("Last name:", "dt");
        form::tag(htmlspecialchars($dt["afLastName"]), "dd");
        form::tag("Cell phone:", "dt");
        form::tag(htmlspecialchars($dt["afCellPhone"]), "dd");
        form::tag("Home phone:", "dt");
        form::tag(htmlspecialchars($dt["afHomePhone"]), "dd");
        form::tag("Work phone:", "dt");
        form::tag(htmlspecialchars($dt["afWorkPhone"]), "dd");
        form::tag("Work phone ext.:", "dt");
        form::tag(htmlspecialchars($dt["afWorkPhoneExt"]), "dd");
        //form::tag("Employee number:", "dt");
        //form::tag(htmlspecialchars($dt["afEmplNumber"]), "dd");
        form::tag("E-mail:", "dt");
        form::tag(htmlspecialchars($dt["afEmail"]), "dd");
        form::tag("Birth date:", "dt");
        form::tag(
		   val::convDate(MYSQL_DATE_TIME,'M d',$dt["afBirthday"]),
		   //date("M d", strtotime($dt["afBirthday"])), <<<
		   "dd");
        form::tag("Position:", "dt");
        form::tag(htmlspecialchars($dt["afHiredPosition"]), "dd");
        form::tag("Office:", "dt");
        form::tag(htmlspecialchars($dt["afOffice"]), "dd");
        form::tag("Department:", "dt");
        form::tag(htmlspecialchars($dt["afDepartment"]), "dd");
        form::tag("Company:", "dt");
        form::tag(htmlspecialchars($dt["afCompany"]), "dd");
        form::tag("Employee since:", "dt");
       
		form::tag(
			 val::convDate(MYSQL_DATE,'m/Y',$dt["afStartDate"]),
		     //date("Y", strtotime($dt["afStartDate"])),<<<
			 "dd");
        form::tag("Short Resume:", "dt", array("class" => "dt-last-child"));
        form::tag(nl2br(htmlspecialchars($dt["afShortResume"])), "dd", array("class" => "dt-last-child"));
        form::tag("Skills and Hobbies:", "dt", array("class" => "dt-last-child"));
        form::tag(nl2br(htmlspecialchars($dt["afSpecialSkills"])), "dd", array("class" => "dt-last-child"));

        form::closeTag("dl");

        form::closeDiv(array("class" => "col-sm-12 col-md-6 col-lg-6"));
        form::closeDiv(array("class" => "row"));


        form::closeDiv(array("class" => "col-sm-12 col-md-12 col-lg-12"));
        form::closeDiv(array("class" => "row"));
        form::closeDiv(array("class" => "content bg-white"));
    }

    public static function RenderMustSigned()
    {
        form::openDiv(array("class" => "content bg-white"));
        form::openDiv(array("class" => "row"));
        form::openDiv(array("class" => "col-sm-12 col-md-12 col-lg-12"));
        form::tag("EMPLOYEE DIRECTORY", "h3");
        form::tag("", 'hr');

        form::openDiv(array("class" => "row"));
        form::openDiv(array("class" => "col-sm-12 col-md-12 col-lg-12"));

        form::html('<div class="alert alert-danger">You need to be logged in as an employee or a manager to view the Employee Directory.</div><br/>
                    <a href="' . WP . 'profile" class="btn btn-primary">Login now >></a>');

        form::closeDiv(array("class" => "col-sm-12 col-md-12 col-lg-12"));
        form::closeDiv(array("class" => "row"));


        form::closeDiv(array("class" => "col-sm-12 col-md-12 col-lg-12"));
        form::closeDiv(array("class" => "row"));
        form::closeDiv(array("class" => "content bg-white"));

    }

}
