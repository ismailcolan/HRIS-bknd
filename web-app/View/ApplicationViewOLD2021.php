<?php
class ApplicationView extends AppView
{

    final static function AllOther($pg, $query, $lbls, $rq)
    { 
		$query.=' and afID >0';
		
		$qry='SELECT * FROM companies WHERE comActive = "Yes" ';
		$cmp=db::PrepareLoad($qry);
		if(!is_array($cmp)) $cmp = array();
		$companies=array();
		foreach ($cmp as $k=>$r){
			$companies[$r['comID']]=$r['comShortName'];
		}
		
	
		//if ($rq == 'manager-hired') echo 88888888;
        table::create($pg["tbl"], $pg["key"], "desc", self::ADMIN_LISTS_ROWS_LIMIT);
        table::$cols[] = Array("Name", "afName");
		 table::$cols[] = Array("SSN", "afSSNmb");
        table::$cols[] = Array("E-mail", "afEmail");
		
		 if (in_array(ApplicationController::$rq, ["employees-terminated"])) {
            table::$cols[] = Array("Position", "afHiredPosition");
            table::$cols[] = Array("Company", "afCompany");
            
        }
        else if (in_array(ApplicationController::$rq, ["hired"/*, "manager-hired"*/])) {
			  
            table::$cols[] = Array("Position", "afHiredPosition");
            table::$cols[] = Array("Company", "afCompany");
            table::$cols[] = Array("Department", "afDepartment");
            table::$cols[] = Array("Company office", "afOffice");
            if (!isset(AppModel::$admin["waaAFID"])) {
                table::$cols[] = Array("Manager", "afManager");
            }
        } else {
            if (!isset(AppModel::$admin["waaAFID"])) {
                table::$cols[] = Array("Job", "afJob");
                table::$cols[] = Array("Position", "afPosition");
            }
        }

        if (in_array(ApplicationController::$rq, ["for-interview", "for-decision"])) {
            table::$cols[] = Array("Rating", "afRating");
        }


        if (in_array(ApplicationController::$rq, ["hired",/*"manager-hired",*/"employees-terminated"])) {
            table::$cols[] = Array($lbls[($c = $pg["pfx"] . "StartDate")], $c, array("style"=>"width:10em","class" => "link", "filtered" => false));
        } else {
            table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Added")], $c, array("class" => "link", "filtered" => false));
        }
		
		 if (in_array(ApplicationController::$rq, ["employees-terminated"])){
		   table::$cols[] = Array('Date Left', $pg["pfx"] . "DateLeaving", array("style"=>"width:10em","class" => "link", "filtered" => false));	 
		 }

        if (in_array(ApplicationController::$rq, ["hired"])) {
            if (!isset(AppModel::$admin["waaAFID"])) {
                table::$cols[] = Array("", "Pass", array("class" => "link", "sort" => false));
            }
        }
		
		 if (in_array(ApplicationController::$rq, ["employees-terminated"])){
		   table::$cols[] = Array('Terminated Or Retired', $pg["pfx"] . "TerminatedOrRetired", array("style"=>"width:15em","filtered" =>true));	 
		 }
	//echo $query;exit;
        table::execute($query);
        if ($rq === "list") {
            list($view, $edit, $deactivate) = self::AllLnksCode($pg, array("view", "edit", "deactivate"));
        } else {
            list($view, $edit, $reactivate) = self::AllLnksCode($pg, array("view", "edit", "reactivate"));
        }

        foreach (table::$data as $r => &$cells) {
			  if (in_array(ApplicationController::$rq, ["hired","employees-terminated"])) {
        		   $cells[$pg["pfx"] . "DateLeaving"] = val::convDate(MYSQL_DATE,USER_DATE,$cells[$pg["pfx"] . "DateLeaving"]);
            }
			
			
            if (table::$export === false) {
                $cells["lnks"] = sprintf($view, $cells[$pg["key"]]);
                if ($rq === "list") {
                    $cells[$pg["pfx"] . "active"] = sprintf($deactivate, $cells[$pg["key"]]);
                } else {
                    $cells[$pg["pfx"] . "active"] = sprintf($reactivate, $cells[$pg["key"]]);
                }

                $cells[$pg["key"] . "edit"] = sprintf($edit, $cells[$pg["key"]]);

				
                $cells["afName"] = $cells[$pg["pfx"] . "FirstName"] . ' ' . $cells[$pg["pfx"] . "LastName"];
				$cells["afSSNmb"]='<span style="white-space:nowrap">'.$cells["afSSNmb"].'</span>';
				$add='';
				$addArr=array();
				$today = new DateTime();
				$addText='';
				//echo $cells['afDateLeaving'].' === ' .$today->format(MYSQL_DATE).'<br>';
				if (strpos($_SERVER['REQUEST_URI'],'manager-employees')!== false){
					$date_leaving = $cells['afDateLeaving'];
				}
				else {
					$date_leaving = val::convDate(USER_DATE,MYSQL_DATE,$cells['afDateLeaving']);
				} 
				//echo '>>>>>>>>>>>>>>>>>'.$date_leaving ;
				if ($cells["afActive"] == 'Hired'
					&&
					(isset($cells['afDateLeaving']) &&  
					 $date_leaving > $today->format(MYSQL_DATE))
					 ){  
					$add='';
					if (strpos($cells['afDateLeaving'],'/')!== false)
						$the_day = $cells['afDateLeaving'];
					else
						$the_day = val::convDate('Y-m-d',USER_DATE,$cells['afDateLeaving']);
					$addArr=array('style'=>'color:red');
					$addText='<br><span style="color:red;margin-top:0;padding-top:0">( in leave notice, leaving '
					.$the_day.' )</span>';
					
				}
				
				//echo '>>>>>>>>>>'.$cells['afDateLeaving'];
				if (strpos($_SERVER['REQUEST_URI'],'manager-employees') !== false )
					$date_leaving = $cells['afDateLeaving'];
				else
					$date_leaving = val::convDate(USER_DATE,MYSQL_DATE,$cells['afDateLeaving']);
				//if(!empty($cells['afDateLeaving'])) echo '<br>>>---NOTSET'; else echo '<br>'.$cells['afDateLeaving'];	
				if (
					   $cells["afActive"] == 'Terminated'
						||
						(
						 $cells["afActive"] == 'Hired' && isset($cells['afDateLeaving']) 
						 && $date_leaving !='' && $date_leaving <= $today->format('Y-m-d')
						)
					 )
				{
					$add='&type=terminated';
					$addArr=array();
					$addText='';
				}
				
				
                $cells["afName"] = 
				   form::linkOut($pg["details"]["url"] . "?id=" . $cells[$pg["key"]].$add, 
				   $cells["afName"],
				   $addArr);
				   if ($addText != '') $cells["afName"] .= $addText;
				$crcl='';
				if(strpos($_SERVER['REQUEST_URI'],'admin/employees')!==false && strpos($_SERVER['REQUEST_URI'],'employees-terminated') == false ) 
							$crcl=MyEmployeesController::isEmplOnWorkToday($cells);
				$cells["afName"] = '<span style="white-space:nowrap">'.$crcl.$cells["afName"].'</span>';
				   
                $emplID = intval($cells["afApplyAsEmployeeID"]);
                if($emplID > 0) {
                    $cells["afName"] .= '<br/>(employee)';
                }
                $cells["afEmail"] = form::linkOut("mailto:" . $cells[$pg["pfx"] . "Email"], $cells[$pg["pfx"] . "Email"]);
                
				
				if (in_array(ApplicationController::$rq, ["hired","employees-terminated"])) {
                    $cells[$pg["pfx"] . "StartDate"] = val::convDate(MYSQL_DATE,USER_DATE,$cells[$pg["pfx"] . "StartDate"]);
					   //$cells[$pg["pfx"] . "DateLeaving"] = val::convDate(MYSQL_DATE,USER_DATE,$cells[$pg["pfx"] . "DateLeaving"]);
						//date(USER_DATE, strtotime($cells[$pg["pfx"] . "StartDate"]));//<<<
                } else {
					  
                    $cells[$pg["pfx"] . "Added"] = 
						  val::convDate(MYSQL_DATE_TIME,GLOBAL_PHP_DATETIME,$cells[$pg["pfx"] . "Added"]);
					   //date(USER_DATE, strtotime($cells[$pg["pfx"] . "Added"]));
                }
				
                if (in_array(ApplicationController::$rq, ["hired", "manager-hired"])) {
                    $cells["afHiredPosition"] = htmlspecialchars($cells["afHiredPosition"]);
					   
					   if ($cells["afIsManager"]=='yes') {
						  
						   $data=EmployeeModel::GetActiveResponsibleForEmployees($cells["afID"]);
						   $c=count($data);
						   if ($c==0) 
								$add='manager: No assigned employees';
						   else {
							   if ($c==1) $txt='1';
							   else $txt=$c;
							   $add='manager: '.
								form::linkOut('/admin/employees-of-manager?list=emp&id='. $cells[$pg["key"]], 'view employees ('.$txt.') >>');
								
						   }
								
						   $cells["afHiredPosition"].='<br><span style="white-space: nowrap;">'.$add.'</span>';
   
					  }
					   
                    $cells["afDepartment"] = htmlspecialchars($cells["afDepartment"]);
                    $cells["afOffice"] = htmlspecialchars($cells["afOffice"]);
//                    if($cells["afRMID"] == MANAGER_ID) { $cells["afManager"] = MANAGER_NAME; }
//                    else { $cells["afManager"] = htmlspecialchars($cells["afManager"]); };
                    $cells["afManager"] = htmlspecialchars($cells["afManager"]);
					$cells["afCompany"] = htmlspecialchars($companies[$cells["afCompany"]]);
								//htmlspecialchars(CompanyModel::GetShortName($cells["afCompany"]));
                } else {
                    $cells["afJob"] = htmlspecialchars($cells["afJob"]);
					   if ($cells["afPosition"]=='HR Manager') $cells["afPosition"]='Not sure';
                    $cells["afPosition"] = htmlspecialchars($cells["afPosition"]);
                }

                $imgFile = ApplyFormModel::IMG_PATH . str_replace("{---LiveID---}", $cells[$pg["key"]], ApplyFormModel::IMG_FILENAME);
                if (file_exists("../" . $imgFile)) {
                    $cells["afName"] = "<img src='" . WP . $imgFile .'?_r='.rand(0,9999). "' style='height: 50px; margin: 5px;' />" . $cells["afName"];
                }

                if (in_array(ApplicationController::$rq, ["hired"])) {
                    $cells["Pass"] = form::linkOut($pg["password"]["url"] . "?id=" . $cells[$pg["key"]], "<span class='fa fa-key'></span>", ["class" => "tbl-ico", "title" => "Change employee password"]);
                }

            }
			
			//print_r($cells);
        }
        table::load();
    }

    final static function AllAutoRejected($pg, $query, $lbls, $rq)
    {
        table::create($pg["tbl"], $pg["key"], "desc", self::ADMIN_LISTS_ROWS_LIMIT);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Added")], $c);
        table::$cols[] = Array("Job", "afJob");
        table::$cols[] = Array("Position", "afPosition");
        table::$cols[] = Array("Name", "afName");
        table::$cols[] = Array("E-mail", "afEmail");
//        table::$cols[] = Array("E-mail", "afEmail");

        table::execute($query);
        if ($rq === "list") {
            list($view, $edit, $deactivate) = self::AllLnksCode($pg, array("view", "edit", "deactivate"));
        } else {
            list($view, $edit, $reactivate) = self::AllLnksCode($pg, array("view", "edit", "reactivate"));
        }

        foreach (table::$data as $r => &$cells) {
            if (table::$export === false) {
                $cells["lnks"] = sprintf($view, $cells[$pg["key"]]);
                if ($rq === "list") {
                    $cells[$pg["pfx"] . "active"] = sprintf($deactivate, $cells[$pg["key"]]);
                } else {
                    $cells[$pg["pfx"] . "active"] = sprintf($reactivate, $cells[$pg["key"]]);
                }

                $cells[$pg["pfx"] . "Added"] = form::linkOut($pg["summary"]["url"] . "?id=" . $cells[$pg["key"]], $cells[$pg["pfx"] . "Added"]);
                if(!empty($cells[$pg["pfx"] . "Name"])) {
                    $cells[$pg["pfx"] . "Name"] = form::linkOut(ADMIN . "application-details?id=" . $cells["afApplyAsEmployeeID"], $cells[$pg["pfx"] . "Name"]);
                }
            }
        }
        table::load();
    }

    final static function All($pg, $query, $lbls, $rq)
    { 
        if ($rq === "auto-rejected") {
            self::AllAutoRejected($pg, $query, $lbls, $rq);
        } else {
			
            self::AllOther($pg, $query, $lbls, $rq);
        }
    }
	
	 final static function AllApplications($pg, $query, $lbls, $rq)
    { 
     
        table::create($pg["tbl"], $pg["key"], "desc", self::ADMIN_LISTS_ROWS_LIMIT);
		 table::$cols[] = Array("ID", "afID");
        table::$cols[] = Array("First Name", "afFirstName");
		 table::$cols[] = Array("Last Name", "afLastName");
        table::$cols[] = Array("Status", "afActive");
		 table::$cols[] = Array("", $pg["key"] . "delete", array("class" => "link", "sort" => false));
        table::execute($query);
       

        foreach (table::$data as $r => &$cells) {
 
                
                $emplID = intval($cells["afApplyAsEmployeeID"]);
                if($emplID > 0) {
                    $cells["afFirstName"] .= ' (employee)';
                }
				  if ($cells["afActive"] == 'Auto-rejected'){
					  $cells["afFirstName"] = $cells["afAdded"];
					  $cells["afLastName"] = '&nbsp;';
				  }
				   
				   $cells[$pg["key"] . "delete"]=
				   '<a onclick="return confirm(\'Are you sure to delete '.$cells["afFirstName"].' '.$cells["afLastName"].
				   ' ?\')" href="delete-employee?deid='
				   .$cells[$pg["pfx"] . "ID"].'"><i class="fa fa-trash-o"></i></a>';
        }
        table::load();
    }


    final static function Summary($dt, $pg, $lbls)
    {
        if ($dt[$pg["pfx"] . "PID"] < 1) {
            $position = "Not sure";
        } else {
            $position = PositionModel::dataById($dt[$pg["pfx"] . "PID"])[PositionModel::$dt["pfx"] . "Name"];
        }

        $c = $pg["pfx"] . "Birthday";
        $dt[$c] = val::convDate(MYSQL_DATE,USER_DATE,$dt[$c]);
		 //date(USER_DATE, strtotime($dt[$c]));<<<

        $c = $pg["pfx"] . "StartDate";
		
        $dt[$c] = val::convDate(MYSQL_DATE,USER_DATE,$dt[$c]);
		 //date(USER_DATE, strtotime($dt[$c]));<<

        $imgFile = ApplyFormModel::IMG_PATH . str_replace("{---LiveID---}", $dt[$pg["key"]], ApplyFormModel::IMG_FILENAME);
        $bioFile = WP . ApplyFormModel::BIO_PATH . str_replace("{---LiveID---}", $dt[$pg["key"]], ApplyFormModel::BIO_FILENAME);

        $questHtml = "";
        $qestions = json_decode($dt["afQuestionnaire"]);

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

        $flds4panel = array();
        $c = $pg["pfx"] . "JID";
        if (!empty($dt[$c])) {
            $flds4panel[$lbls[$c]] = $dt["afJob"];
        }

        $flds4panel[$lbls[($c = $pg["pfx"] . "PID")]] = $position;

        if ($dt["afActive"] != "Auto-rejected") {
            if (file_exists("../" . $imgFile)) {
                $flds4panel["Photo"] = "<img src='" . WP . $imgFile . "' class='admin-applicant-photo' /><br/><small>Move over the picture to zoom in</small>";
            }
            $flds4panel[$lbls[($c = $pg["pfx"] . "FirstName")]] = $dt[$c];
            $flds4panel[$lbls[($c = $pg["pfx"] . "LastName")]] = $dt[$c];
            $flds4panel[$lbls[($c = $pg["pfx"] . "Birthday")]] = $dt[$c];
//        $flds4panel['Desired '.strtolower($lbls[($c = $pg["pfx"]."PID")])] = form::linkOut(ADMIN."position-details?id=".$dt[$c], $position." <i class=\"fa fa-pencil-square-o\" aria-hidden=\"true\"></i>");
            $flds4panel[$lbls[($c = $pg["pfx"] . "CellPhone")]] = $dt[$c];
            $flds4panel[$lbls[($c = $pg["pfx"] . "HomePhone")]] = $dt[$c];
            $flds4panel[$lbls[($c = $pg["pfx"] . "EmcContact")]] = $dt[$c];
            $flds4panel[$lbls[($c = $pg["pfx"] . "Email")]] = form::linkOut("mailto:" . $dt[$c], $dt[$c]);
            $flds4panel[$lbls[($c = $pg["pfx"] . "Skills")]] = $dt[$c];
            $flds4panel[$lbls[($c = $pg["pfx"] . "DesiredSalary")]] = $dt[$c];
            $flds4panel[$lbls[($c = $pg["pfx"] . "IP")]] = $dt[$c];
            $flds4panel[$lbls[($c = $pg["pfx"] . "Active")]] = $dt[$c];
            $flds4panel[$lbls[($c = $pg["pfx"] . "Added")]] = $dt[$c];
            $flds4panel[$lbls[($c = $pg["pfx"] . "Bio")]] = "<a href='" . $bioFile . "'>Download <i class='fa fa-download' aria-hidden='true'></i></a>";

            if (in_array($dt["afActive"], ["For Interview", "For Decision"])) {
                $flds4panel[$lbls[($c = $pg["pfx"] . "Rating")]] = $dt[$c];
                $flds4panel[$lbls[($c = $pg["pfx"] . "Comments")]] = $dt[$c];
            }
        } else {
            $flds4panel[$lbls[($c = $pg["pfx"] . "IP")]] = $dt[$c];
            $flds4panel[$lbls[($c = $pg["pfx"] . "Active")]] = $dt[$c];
            $flds4panel[$lbls[($c = $pg["pfx"] . "Added")]] = $dt[$c];
        }


        if (!empty($questHtml)) {
            $flds4panel["Questionnaire"] = $questHtml;
        }

        self::DataPanel($flds4panel, (!empty($pg["item"]) ? $pg["item"] . " summary" : null), "panel-default");
    }

    static function Details($data, $pg, $lbls, $list)
    {
		 global $_US_STATES;
        $args = self::$fldArgsBase;
		
		$readonly=false;
		
		
		
 
        if (isset(AppModel::$admin["waaAFID"])) {
            $args = self::$fldArgsBase + array("readonly" => "readonly");
            $isManager = true;
        } else {
            $isManager = false;
        }

        $args1 = $args;
        $args1["class"] = "form-control width-70 left";
        $args1["hint-class"] = "no-border left line-height-34 ";
        $args1["prefix-class"] = "form-control no-border left";

        $args1Right = $args;
        $args1Right["hint-class"] = "no-border right ";
        $args1Left = $args;
        $args1Left["hint-class"] = "no-border left ";
        $args1Left["class"] = "form-control auto-height";

		

        $args2 = $args;
        $args2["class"] .= " auto-height ";

        $imgFile = ApplyFormModel::IMG_PATH . str_replace("{---LiveID---}", $data[$pg["key"]], ApplyFormModel::IMG_FILENAME);
        $bioFile = ApplyFormModel::BIO_PATH . str_replace("{---LiveID---}", $data[$pg["key"]], ApplyFormModel::BIO_FILENAME);
        $currentFile = ApplyFormModel::BIO_PATH . str_replace("{---LiveID---}", $data[$pg["key"]], ApplyFormModel::CURRENTCV_FILENAME);

        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "enctype" => "multipart/form-data", "validate" => true, "class" => "form-horizontal"));
		 
		 /*********************************
		 if (!empty($data['afConnectedToProfile']) && $data['afConnectedToProfile']!=' '){
		   echo EmployeeModel::showConnected($data);
		 }
		 
		 else {
		  if ($data['afConnectedToProfile']!=' '){
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
				<a href="javascript:void(0)" onclick="ask_no_more_connect('.$data["afID"].')" class="btn btn-info">No, it is different person</a>
				
				</p>
				</div>
		   
			  </div></div>
			  <script>
			   function connect_profiles(former_id,id,former_ids){
				   $.get( "employee-details?cmd=connect-profiles&id="+id+
				   "&former_id="+former_id+"&former_ids="+former_ids
				   ,
				   function( data ) { 
					  if (data=="OK") {
						  $(".alert-info").alert("close");
						  alert("The profiles were connected.");
						  location.reload();
					  }
					  else myalert("Alert","There was a problem to connect profiles.");
					});
			   }
			   
			   function ask_no_more_connect(id){
				if (confirm(
"If you continue you can not anymore connect this profile with existing ones.Are you sure to continue?")){
				$.get( "employee-details?cmd=ask_no_more_connect&id="+id
				   ,
				   function( data ) { 
					  if (data=="OK") {
						  $(".alert-info").alert("close");
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
/***************/	

        if(isset($data["check-name-failed"]) &&  ! isset($data['afApplyAsEmployeeID'])) {
			  //echo '<div class="fld form-group">
				//<label class="control-label col-sm-4 col-md-3"></label>
				//<div class="col-sm-7 col-md-5 col-lg-5">';
				
            form::openDiv(["class"=>"alert alert-info"]);
            form::html("There's already an employee with the same first and last name. Please hire the applicant only if he/she is a different person. If he/she wants to change position, do it from their employee profile.<br><br>");
            form::html("<input type='hidden' value=1 name='skip-name-check' />");
            echo '<div style="text-align:center;">
			<button type="submit" name="save-btn" value="Different person, hire now" class="btn btn-primary">Different person, hire now</button>
			<button type="button" onclick="location.assign(\'employees\')" class="btn btn-default">Already hired, cancel hiring</button>
			';
			  //form::inpSaveCancel(ApplicationController::getBackUrl()["url"], self::$saveCancelArgsBase,
  			  //"Different person, hire now", "Already hired, cancel hiring");
			  
			  form::closeDiv();
			  form::closeDiv();
			 // form::closeDiv();
        }
   
        if(intval($data["afApplyAsEmployeeID"]) > 0) {
            $isEmployee = true;
            $args3 = $args2;
            $args3["class"] .= " alert alert-danger ";

            $emplData = EmployeeModel::dataById($data["afApplyAsEmployeeID"]);
            $f = $pg["pfx"] . "PID";
            $position = PositionModel::dataById($data[$f]);

            form::inpInfo(
                " ",
                "This person is already hired as <strong>{$emplData["afHiredPosition"]}</strong>. He/she is now applying as: <strong>{$position["pName"]}</strong>.<br/><a href='".ADMIN."employee-details?id={$emplData["afID"]}' target='_blank'>View profile >></a>",
                $args3
            );
        } else {
            $isEmployee = false;
        }

        if($isEmployee) {
            form::openDiv(["style" => "display:none;"]);
        }
		
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
		
		form::inpSelect(
            $lbls[($f = $pg["pfx"] . "Gender")] . ":",
            $f,
			  array('male'=>'Male','female'=>'Female'),
			  empty($data[$f]) ? "" :$data[$f],
            $args + ($readonly ? ["readonly" => "readonly"] : [])
        );
		
		
		form::inpSelect(
            $lbls[($f = $pg["pfx"] . "MaritalStatus")] . ":",
            $f,
			  array("Single"=>'Single', "Married"=>'Married',"Divorced"=>'Divorced',"Widowed"=>'Widowed'),
			  empty($data[$f]) ? "" :$data[$f],
            $args + ($readonly ? ["readonly" => "readonly"] : [])
        );
		
        form::inpField(
            $lbls[($f = $pg["pfx"] . "Email")] . ":",
            $f,
            htmlspecialchars($data[$f]),
            $args
        );
		
		 form::inpField(
            $lbls[($f = $pg["pfx"] . "SSNmb")] . ":",
            $f,
            empty($data[$f]) ? "" : $data[$f],
            $args + array("hint" => "Format:  xxx-xx-xxxx") + ($readonly ? ["readonly" => "readonly"] : [])
        );
		
		
		
		
		
        form::inpField(
            $lbls[($f = $pg["pfx"] . "CellPhone")] . ":",
            $f,
            htmlspecialchars($data[$f]),
            $args + ["hint" => "Format: xxx-xxx-xxxx"]
        );
        form::inpField(
            $lbls[($f = $pg["pfx"] . "HomePhone")] . ":",
            $f,
            htmlspecialchars($data[$f]),
            $args + ["hint" => "Format: xxx-xxx-xxxx"]
        );
		
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
			  array("spouse"=>'spouse', "parent"=>'parent',
			  "child"=>'child',"friend"=>'friend','other'=>'other'),
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

        form::html('<div class="fld form-group">
        <label class="control-label col-sm-4 col-md-3"></label>
        <div class="col-sm-7 col-md-5 col-lg-5"><div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
        <small>This information will appear on the internal employee directory and can be viewed by all other employees</small></div></div></div>');
        
       
        if($isEmployee) {
            form::closeDiv(["style" => "display:none;"]);
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
                htmlspecialchars($list["positions"][$data[$f]]),
                $args + array("readonly" => "readonly")
            );
        } else {
            form::inpSelect(
                $lbls[$f] . ":",
                $f,
                $list["positions"],
                isset($data[$f]) ? $data[$f] : null,
                (array("id" => "jobPositionSelect") + $readOnly + $args)
            );
        }
        
		  if ($list["positions"][$data[$f]] == 'Not sure' ) $sal='Not specified';
        $f = $pg["pfx"] . "DesiredSalary";	  
		  $sal = PositionModel::formatSalary($data[$f]) . " " . strtolower($data[$pg["pfx"] . "DesiredSalaryType"]);
		
		 form::inpInfo(
            $lbls[($f = $pg["pfx"] . "DesiredSalary")] . ":",
            $sal,
            $args + array("readonly" => "readonly")
        );

        form::tag("", "input", array("name" => $f, "type" => "hidden", "value" => $data[$f]));

        form::inpField(
            $lbls[($f = $pg["pfx"] . "Skills")] . ":",
            $f,
            str_replace("|", ", ", $data[$f]),
            $args + array("readonly" => "readonly")
        );

        if($isEmployee) {
            form::openDiv(["style" => "display:none;"]);
        }

        // if (file_exists("../" . $imgFile)) {
        //     $photo = "<img src='" . WP . $imgFile . "' class='admin-applicant-photo' /><br/><small>Move over the picture to zoom in</small>";
        //     form::inpInfo("Photo", $photo, $args2);
        //     $bio = "<label><input type='radio' value='download' name='photoCmd' checked> <a href='" . WP . "{$imgFile}' id='downloadPhoto' target='_blank'><i class='fa fa-download'></i>download</a></label> | 
        //             <label><input type='radio' value='remove' name='photoCmd'> <i class='fa fa-trash-o'></i>delete</input></label>";

        //     form::inpInfo(
        //         " ",
        //         $bio,
        //         $args);

        //     if (!$isManager) {
        //         form::inpImg(
        //             "Replace photo:",
        //             "afPhoto",
        //             "",
        //             $args + array("hint" => "Please upload only .jpg or .png file")
        //         );
        //     }
        // } else {
        //     if (!$isManager) {
        //         form::inpImg(
        //             "Photo:",
        //             "afPhoto",
        //             "",
        //             $args + array("hint" => "<strong>No photo uploaded.</strong> Please upload only .jpg or .png file")
        //         );
        //     }
        // }

        $fldargs = $args1Left;
        if (!empty(form::$error['afBio'])) {
            $err = '<br/><span class="err">' . form::$error['afBio'][0] . '</span>';
            $fldargs["class"] = $fldargs["class"] . " error";
        } else {
            $err = '';
        }

        if (!empty($data["cvs"][CVModel::APPLICATION_CV])) {
            $file = $data["cvs"][CVModel::APPLICATION_CV];

            if ($isManager) {
                $bio = "<label><a href='" . $file['fileURL'] . "' id='downloadBio'><i class='fa fa-download'></i> " . $file["cvFileName"] . "</a></label>";
                form::inpInfo(
                    "Application CV",
                    $bio,
                    $fldargs
                );
            } else {
                $bio = "<label><input type='radio' value='download' name='bioCmd' checked> <a href=\"" . $file['fileURL'] . "\" id='downloadBio'><i class='fa fa-download'></i> " . $file["cvFileName"] . "</a></label> |
                    <label><input type='radio' value='remove' name='bioCmd'> <i class='fa fa-trash-o'></i>delete</input></label> |
                    <label><input type='radio' value='upload' name='bioCmd'> <i class='fa fa-upload'></i>replace</input></label>
                    <span id='selectBioFile' style='display: none;'>&nbsp;&nbsp;&nbsp;<input type='file' name='afBio' style='display: inline-block;'></span>";

                form::inpInfo(
                    "Application CV",
                    $bio,
                    $fldargs + array("hint" => "This is the <strong>Application CV</strong>. Please don't update it except in case of technical problem.<br/>
                Please upload only .doc, .docx or .pdf" . $err));
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

        $f = $pg["pfx"] . "JID";
        $readOnly = [];
        if (!empty($data[$f])) {
            if (isset($list["jobs"][$data[$f]])) {
                $list["jobs"] = [$data[$f] => $list["jobs"][$data[$f]]];
            } else {
                $list["jobs"] = [];
            }
            $readOnly["readonly"] = "readonly";
            $readOnly["skip-empty"] = "skip-empty";

       if (!isset($list["jobs"][$data[$f]])) $dj='&nbsp;';
	    else $dj=htmlspecialchars($list["jobs"][$data[$f]]);
            if (!empty($readOnly)) {
                form::inpInfo(
                    $lbls[$f] . ":",
                    $dj,
                    $args2 + array("readonly" => "readonly")
                );
            } else {
                form::inpSelect(
                    $lbls[$f] . ":",
                    $f,
                    $list["jobs"],
                    isset($data[$f]) ? $data[$f] : null,
                    (array("id" => "jobPositionSelect") + $readOnly + $args)
                );
            }
        }


        form::inpField(
            $lbls[($f = $pg["pfx"] . "Birthday")] . ":",
            $f,
            htmlspecialchars($data[$f]),
            $args1 + array("hint" => "example: ".USER_DATE_EXAMPLE)
        );

        if($isEmployee) {
            form::closeDiv(["style" => "display:none;"]);
        }

        form::inpField(
            $lbls[($f = $pg["pfx"] . "Added")] . ":",
            $f,
            val::convDate(MYSQL_DATE_TIME,GLOBAL_PHP_DATETIME,$data[$f]),
			 
            $args + array("readonly" => "readonly")
			
        );


        $questHtml = "";
        $qestions = json_decode($data["afQuestionnaire"]);

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
        if (!empty($questHtml)) {
            form::inpInfo("Questionnaire:", $questHtml, $args2 + array("readonly" => "readonly"));
        }

        form::inpField(
            $lbls[($f = $pg["pfx"] . "IP")] . ":",
            $f,
            htmlspecialchars($data[$f]),
            $args + array("readonly" => "readonly")
        );

        $q = "SELECT * FROM logs where lafID=%d order by lID desc";
        $q = sprintf($q, $data[$pg["key"]]);
		
        $logs = db::PrepareLoad($q);
        if (($logs !== false) && (!empty($logs))) {
            $log = $logs[0];
			  //$d=new 
            $logData = json_decode($log["lData"]);
            form::inpInfo("Last log in:", "<strong>IP address:</strong> " . $logData->IP . "<br/>
                                            <strong>Timestamp: </strong>" . 
											
											date(USER_DATE, strtotime($log["lTimestamp"])) . "<br/>
                                            <strong>Browser:</strong> " . $logData->USER_AGENT . "
                                            <div class='row' style='padding-right: 10px;'><a class='btn btn-primary right' data-toggle='modal' data-target='#logsModal'>View all</a></div>", $args2);


            $logsCnt = count($logs);
            $logsHTML = "<table class='table'>
                            <thead>
                                <tr><th width='10%'>IP Address</th><th width='20%'>Date/Time</th></tr>         
                            </thead>
                            <tbody>";
            for ($i = 0; $i < $logsCnt; $i++) {
                $log = $logs[$i];
                $logsHTML .= "<tr>
                                <td>" . $logData->IP . "</td>
                                <td>" . date(GLOBAL_PHP_DATETIME, strtotime($log["lTimestamp"])) . "</td>
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


        form::html("<hr/>");

        if ($isManager) {
            $options = [$data["afActive"] => $data["afActive"]];
            if ($data["afActive"] != "Manually Rejected") {
                $options["Manually Rejected"] = "Manually Rejected";
            }
        } else {
            if (in_array($data["afActive"], ["Hired" => "Hired", "Inactive" => "Inactive"])) {
                $options = ["Hired" => "Hired", "Inactive" => "Inactive"];
            } else {
                $options = ["Pending" => "Pending", "For Interview" => "For Interview", "For Decision" => "For Decision", "Manually Rejected" => "Manually Rejected", "Hired" => "Hired"];
            }
        }

        if($isEmployee) {
            $options["Hired"] = "Change employee position"; //"Change position to: <strong>{$position["pName"]}</strong>";
        }

        form::inpSelect(
            $lbls[($f = $pg["pfx"] . "Active")] . ":",
            $f,
            $options,
            htmlspecialchars($data[$f]),
            self::$fldArgsBase + Array("skip-empty" => "skip-empty", "id" => "applicationStatus")
        );

		$infoDisplay = "";
		/****
        if (in_array($data[$pg["pfx"] . "Active"], ["For Interview", "For Decision"])) {
            $infoDisplay = "";
        } else {
            $infoDisplay = ' style = "display: none;" ';
        }
		****/
        form::html('<div id="ratingInfo"' . $infoDisplay . '>');
        form::inpSelect(
            $lbls[($f = $pg["pfx"] . "RID")] . ":",
            $f,
            RatingModel::AllActiveKeyName(),
            $data[$f],
            $args
        );
        form::inpField(
            $lbls[($f = $pg["pfx"] . "Comments")] . ":",
            $f,
            htmlspecialchars($data[$f]),
            $args + array("type" => "textarea", "rows" => "6")
        );
        form::html('<hr/></div>');

		$infoDisplay = "";
		/***********
        if (($data[$pg["pfx"] . "Active"] === 'Hired') ||
            (filter_input(INPUT_POST, $pg["pfx"] . "Active") === "Hired")
        ) {
            $infoDisplay = "";
        } else {
            $infoDisplay = ' style = "display: none;" ';
        }
		***********/
        form::html('<div id="hiredInfo"' . $infoDisplay . '>');


        EmployeeView::AddHiredForPositionBlock($data, $pg, $lbls, false, $args, $args1, $args2);
		 
		 if (empty($_POST)) $ast=ASTERISK; else $ast='';
        form::inpSelect(
            $lbls[($f = $pg["pfx"] . "DID")] .":". $ast,
            $f,
            DepartmentModel::AllIdNameArr(),
            $data[$f],
            $args
        );

        
        form::inpSelect(
            $lbls[($f = $pg["pfx"] . "COID")] . ":". $ast,
            $f,
            CompanyOfficeModel::AllActiveKeyName($data[$f] != '{!--Empty--!}' ? $data[$f] : null),
            $data[$f],
            $args
        );

        form::inpSelect(
            $lbls[($f = $pg["pfx"] . "RMID")] . ":". $ast,
            $f,
            EmployeeModel::getManagersExceptThis(EMPLOYEE_THEMSELF_ID),
            $data[$f],
            $args
        );

        form::inpSelect(
            $lbls[($f = $pg["pfx"] . "Company")] . ":". $ast,
            $f,
            ["{!--Empty--!}" => "Please select"] + CompaniesModel::GetActiveCompanies(),// CompanyModel::AllActiveNameName(),
            $data[$f],
            $args
        );
		
		 if (isset($_POST[$pg["pfx"] . "Salary"])){
			    $data[$pg["pfx"] . "Salary"] = $_POST[$pg["pfx"] . "Salary"];
				
		  }

        form::inpField(
            $lbls[($f = $pg["pfx"] . "Salary")] . ":". $ast,
            $f,
            $data[$f],
            $args1 + array("prefix"=>GLOBAL_MONEY_SIGN, "hint-class" => "right", "hint" => "Desired salary: " . ($data["afDesiredSalary"] > 0 ? PositionModel::formatSalary($data["afDesiredSalary"]) : "not specified") . " " .
                strtolower($data["afDesiredSalaryType"]))
        );
/***************
        form::inpCheckList(
            $lbls[($f = $pg["pfx"] . "SalaryType")],
            $f,
            ["Hourly" => "Hourly", "Annual" => "Annual"],
            array($data[$f]),
            $args + array("type" => "radio")
        );
*********************/
/***************   SALARY TYPE **********************/
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
				name="'.$f.'" '.$_ck1.' value="Hourly" onchange="setCEdays(this)"> 
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
			form::closeDiv();
/**************  END SALARY TYPE AND JA ***************************/

			form::inpField(
            $lbls[($f = $pg["pfx"] . "VacationDays")] . ":",
            $f,
            $data[$f],
            $args +['id'=>'vacdays','style'=>'width:5em']
        );
		
			form::inpField(
            $lbls[($f = $pg["pfx"] . "CEDays")] . ":",
            $f,
            $data[$f],
            $args +['id'=>'cedays','style'=>'width:5em']
         );

		//cf::pr(form::$error);

		 $d = new DateTime();
		 $_d = $d->format(USER_DATE);
		 
		 echo '<input type="hidden" name="afDateHired" value="'.$_d.'">';
        
		form::inpField(
            $lbls[($f = $pg["pfx"] . "StartDate")] .":". $ast,
            $f,
            $data[$f],
            $args
        );


        form::html('<hr/></div>');


        if(isset($data["check-name-failed"]) && ! isset($data['afApplyAsEmployeeID'])) {
            
        } else {
            form::inpSaveCancel(ApplicationController::getBackUrl()["url"], self::$saveCancelArgsBase + ((!in_array($data["afActive"], ["Hired", "Inactive"])) ? 
            array("id" => "btnSaveApplication") : array()));
        }

        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }
}
