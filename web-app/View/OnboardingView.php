<?php

/** The CompanyOffice view */
class OnboardingView extends AppView
{
	
	  /** Shows the controller list table - allowing the type filtering
     * @param array $pg - controller::$pg
     * @param string $query - model::query($cond)
     * @param array $lbls - model::$fldLbl
     */
    final static function Report($pg, $query, $lbls, $rq)
    {
        if(!isset(table::$export)) {
            table::$export = false;
        }
		 
		
		 
        //table::create($pg["tbl"], $pg["key"], "desc", self::ADMIN_LISTS_ROWS_LIMIT);
		 
		 table::create('employee_onboarding', 'eoID', "ASC", self::ADMIN_LISTS_ROWS_LIMIT,'onboarding-report');
		 
        table::$cols[] = Array('Description', 'otDescription',array("filtered" => false));
        table::$cols[] = Array('For Employee', "otEmployeer",array("filtered" => false));//, array("sort" => table::$export));
        table::$cols[] = Array('Responsible Employee', "otResponsibleEmployeer",array("filtered" => false));
		 table::$cols[] = Array('Due Date', "TimeFrame");//,array("filtered" => false));
		 table::$cols[] = Array('Status', "Status");
       // table::$cols[] = Array($lbls[($c = $pg["pfx"] . "TimeFrame")], $c, array("sort" => table::$export));
      //  table::$cols[] = Array("Keep status", $pg["pfx"] . "KeepStatus", array("sort" => table::$export));
       // table::$cols[] = Array("", $pg["pfx"] . "Active", array("class" => "link", "sort" => false));

        table::execute($query);
        
		  if (table::$export === false) {
			foreach (table::$data as $r => &$cells) {
				if(!isset($cells['otResponsibleEmployeer'])) $cells['otResponsibleEmployeer']='Not set';
				if (trim($cells['otEmployeer']) != EMPLOYEE_THEMSELF_NAME ) {
					$cells['otEmployeer'] =
					'<a target="blank" href="employee-details?id='.$cells['eoAFID'].'">'
					.$cells['otEmployeer'].'</a>';
				}
				 
				if (trim($cells['otResponsibleEmployeer']) != EMPLOYEE_THEMSELF_NAME ) {
					$cells['otResponsibleEmployeer'] = 
					'<a target="blank" href="employee-details?id='.$cells['otAFID'].'">'
					.$cells['otResponsibleEmployeer'].'</a>';
				}
		  
			}
		  }
        table::load();
    }

    /** Shows the controller list table - allowing the type filtering
     * @param array $pg - controller::$pg
     * @param string $query - model::query($cond)
     * @param array $lbls - model::$fldLbl
     */
    final static function All($pg, $query, $lbls, $rq)
    {
        if(!isset(table::$export)) {
            table::$export = false;
        }
		 
        table::create($pg["tbl"], $pg["key"]/*"coAdded"*/, "desc", self::ADMIN_LISTS_ROWS_LIMIT);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Description")], $c);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Positions")], $c, array("sort" => table::$export));
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Employee")], $c);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "TimeFrame")], $c, array("sort" => table::$export));
        table::$cols[] = Array("Keep status", $pg["pfx"] . "KeepStatus", array("sort" => table::$export));
        table::$cols[] = Array("", $pg["pfx"] . "Active", array("class" => "link", "sort" => false));

        table::execute($query);
        if ($rq === "list") {
            list($view, $edit, $deactivate) = self::AllLnksCode($pg, array("view", "edit", "deactivate"));
        } else {
            list($view, $edit, $reactivate) = self::AllLnksCode($pg, array("view", "edit", "reactivate"));
        }

        foreach (table::$data as $r => &$cells) { //cf::pr($cells);exit;
				//if (table::$export === false) {}
				// if ($cells["otAFID"] == EMPLOYEE_THEMSELF_ID ) {
				//	   $cells[$pg["pfx"] . "Employee"] = EMPLOYEE_THEMSELF_NAME;
				// } 
      			//else if ($cells["otAFID"] == MANAGER_ID) {
             //   $cells[$pg["pfx"] . "Employee"] = MANAGER_NAME;
			   //} 
			
			  if (!($cells[$pg["pfx"] . "AFID"]==EMPLOYEE_THEMSELF_ID
			  || $cells[$pg["pfx"] . "AFID"]==MANAGER_ID))

            $cells[$pg["pfx"] . "Employee"] = form::linkOut(ADMIN.'employee-details?id='.$cells[$pg["pfx"] . "AFID"], htmlspecialchars($cells[$pg["pfx"] . "Employee"]), ["target"=>"blank"]);
            
			
            if(table::$export === false) {
                $cells[$pg["pfx"] . "Description"] = form::linkOut($pg["details"]["url"] . "?id=" . $cells[$pg["key"]], htmlspecialchars($cells[$pg["pfx"] . "Description"]));
            }
            $cells[$pg["pfx"] . "Positions"] = htmlspecialchars($cells[$pg["pfx"] . "Positions"]);
            $cells[$pg["pfx"] . "TimeFrame"] = $cells[$pg["pfx"] . "TimeFrameNum"] . " " . $cells[$pg["pfx"] . "TimeFrameDim"];
			
			if (!isset($cells['otEmployee'])) $cells['otEmployee']='Not set';

            if (table::$export === false) {
                $cells["lnks"] = sprintf($view, $cells[$pg["key"]]);
                if ($rq === "list") {
                    $cells[$pg["pfx"] . "Active"] = sprintf($deactivate, $cells[$pg["key"]]);
                } else {
                    $cells[$pg["pfx"] . "Active"] = sprintf($reactivate, $cells[$pg["key"]]);
                }
            }
        }
        table::load();
    }

    /**Show an model item summary page
     * @param type $dt - controller::$data
     * @param type $pg - controller::$pg
     * @param type $lbls - model::$fldLbl
     */
    final static function Summary($dt, $pg, $lbls)
    {
        $flds4panel = array();
        $flds4panel[$lbls[($c = $pg["pfx"] . "Description")]] = $dt[$c];
        self::DataPanel($flds4panel, $pg["item"] . " summary", "panel-default");
    }

    /** Shows a controller details view
     * @param type $data - controller::$data
     * @param type $lbls - model::$fldLbl
     * @param type $lists - lists generated in the model
     */
    final static function Details($data, $pg, $lbls, $list)
    {
         if (OnboardingController::$id > 0) {
    $url = (OnboardingController::$type == 'off')
        ? ADMIN . "offboarding-details?copy=" . OnboardingController::$id
        : ADMIN . "onboarding-details?copy=" . OnboardingController::$id;
?>
<!--<div style="text-align:right; margin-bottom:15px;">-->
<!--    <a href="<?= $url; ?>" class="btn btn-info">-->
<!--        Copy this <?= (OnboardingController::$type == 'off') ? 'Offboarding' : 'Onboarding'; ?>-->
<!--    </a>-->
<!--</div>-->
<?php }
        $args = self::$fldArgsBase;
       
        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel","onsubmit"=>"return check_submit()"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("enctype" => "multipart/form-data","method" => "post", 
					"validate" => true, "class" => "form-horizontal","id"=>"onboarding-form"));
        // form::inpField(
        //     $lbls[($f = $pg["pfx"] . "Description")] . ":",
        //     $f,
        //     $data[$f],
        //     $args + (isset(OnboardingController::$id) ? array("readonly" => "readonly") : array("hint" => "The description cannot be changed later."))
        // );
        
		 $f = $pg["pfx"] . "Description";
		 if(isset($_POST[$f])) $data[$f]=$_POST[$f]  ;
		 if(!isset($data[$f])) $data[$f] ='';
        form::inpField(
            $lbls[$f] . ":",
            $f,
            $data[$f],
            $args +
            (OnboardingController::$id > 0 ? array("hint" => 'Be careful: changing the name will affect all existing onboarding tasks. If a task was "create an email" 
            and you changed it to "print cards", the employee responsible for creating an email will see he/she needs to print cards instead.<br/><br/>
            Please make only minor changes to the name. If a bigger change is needed, better disable the onboarding and create a new one.') : [])
        );
		
		 if (OnboardingController::$type =='off'){
			 /******
			 form::inpSelect(
            $lbls[($f = $pg["pfx"] . "ApplayTo")] . ":",
            $f,
            array('both'=>'Both','terminated'=>'Terminated','retired'=>'Retired'),
            isset($data[$f]) ? $data[$f] : filter_input(INPUT_POST, $f, FILTER_SANITIZE_STRING),
            $args
			); 
			**********/
			$args1 = $args;
			$args1["wrap-class"] .= " checklist-top-margin applTo";
			$f = $pg["pfx"] . "ApplayTo";
			if (!isset($data[$f])) $data[$f]='terminated|retired|resigned';
			$data[$f]=explode('|',$data[$f]);
        form::inpCheckList(
            $lbls[$f] . ":",
            $f,
            array('terminated'=>'Terminated','retired'=>'Retired','resigned'=>'Resigned'),
            empty($data[$f]) ? [] : $data[$f],
            $args1 + array("type" => "checkbox", "id"=>"leaving_reason")
        );
			 
		 } else {
		
		echo "<br>";
		form::openDiv();
		
		form::openDiv(array("class" => "fld form-group"));
		
		form::tag("Type:" . ASTERISK, "label", ["class" => "control-label  col-sm-4 col-md-3"]);
		
       form::openDiv(["class" => "col-sm-8 col-md-7 col-lg-5","style"=>"position:relative;top:6px;"]);

       
		 $f = $pg["pfx"] . "TaskOrDocsToSign";
		
		  if (!isset($data[$f])) {$_ck1=''; $_ck2='';$display='none';$addScript=false;}
		  else {
			if ($data[$f]=='task') {$_ck1='checked'; $_ck2='';$display='none';$addScript=false;}
			if ($data[$f]=='docstosign') {$_ck1=''; $_ck2='checked';$display='initial';$addScript=true;}
		  }
		  
		 echo '<script>there_is_old_files=true; </script>';
		 
		 if ($_ck2=='checked'){
		 $uploaded_files='';
		 $options_select_upl_files = '';
		 
		 if(isset($_GET['id'])){
		 $op_upl_files=OnboardingModel::getLastOnboardings($_GET['id']);
		 $uploaded_files_array = OnboardingModel::getAllLastOnboardings($_GET['id']);
		 
			 if (! empty($op_upl_files)){
				 foreach ($op_upl_files as $k=>$d){
						$options_select_upl_files.="<option value='".$d['ofID']."'>Replace: '".$d['ofTitle']."'</option>"; 
				 }
			 }
		 }
		 
		 echo '<script>';
		 if (!empty($uploaded_files_array)) echo 'there_is_old_files=true;';
		 else echo 'there_is_old_files=false;';
		 echo '</script>';
		 
		 if (!empty($uploaded_files_array)){
			 $uploaded_files='<h5>Uploaded documents:</h5>
			 <table class="upl_files_table"><tbody>';
			 //<thead><tr><th>Title</th><th>State</th></tr></thead>;
			 foreach ($uploaded_files_array as $k=>$d){
				
				$old_files= OnboardingModel::getHistoryOf($d['ofID']);
				
				if (!empty($old_files)){
					$see_more="&nbsp;&nbsp;&nbsp;&nbsp;<span id=\"show{$d['ofID']}\" style=\"margin-bottom:5px;margin-right:25px;float:right;cursor:pointer;color:#337ab7;;text-decoration:underline\" 
					onclick=\"$('#show{$d['ofID']}').hide();$('#old{$d['ofID']}').slideDown();\">view old versions >></span>
					
					<div style=\"display:none;width:100%;\" id=\"old{$d['ofID']}\">
					
					<span style=\"margin:10px;float:right;cursor:pointer;color:#337ab7;;text-decoration:underline\" 
					onclick=\"$('#old{$d['ofID']}').slideUp();$('#show{$d['ofID']}').show();\">hide old versions >></span>
					<div style=\"background-color:#EEE;width:100%;padding:10px\">
					<br><h5 style=\"float:left;\">Old versions:</h5><br><br><br>";
					foreach ($old_files as $k=>$fl){
					$_date=val::convDate(MYSQL_DATE_TIME,USER_DATE_TIME_SHORT,$fl['ofDateTimeSaved']);
					/*******
					<a style=\"color:#337ab7;;float:left\" title=\"Click to download document\" 
					download href=\"{$fl['otURLName']}\"><i class=\"fa fa-1x fa-download\" aria-hidden=\"true\">
					</i></a>
					&nbsp;&nbsp;
					***********/
					
					$fl['otURLName']=str_replace('hris.maidenlanemedical.com','humanroad.ferndaleroad.com',$fl['otURLName']);
					$see_more.="<a style=\"float:left;color:#337ab7;;text-decoration:underline\" 
					title=\"Click to view document\" target='_blank' href=\"{$fl['otURLName']}\">{$fl['ofTitle']}</a>
					<br><span style=\"float:left;\">Version: {$_date}</span><hr>";
					}
					$see_more.='</div></div>';
				}
				else {
					$see_more='';
				}
				
				$date = val::convDate(MYSQL_DATE_TIME,USER_DATE_TIME_SHORT,$d['ofDateTimeSaved']);
				$date = str_replace('','&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',$date);
				if ($d['ofReplaceFile'] == 0 ) $replace='New document';
				else {
					$q='SELECT * from onboarding_files WHERE ofID='.$d['ofReplaceFile'];
					$res=db::PrepareLoadSingleRow($q);
					$replace='<b>'.$res['ofTitle'].'</b><br><span style="font-size:12px">'.nl2br(val::convDate(MYSQL_DATE_TIME,USER_DATE_TIME_SHORT,$res['ofDateTimeSaved'])).'</span>';
				}
				$q="SELECT * FROM  employee_onboarding WHERE eoDocsToSign LIKE '%{$d['ofID']}%' ";
				$result = db::SQL($q);
				$assigned_to=$result->num_rows;
				
				
				$q="SELECT * FROM  employee_onboarding WHERE eoSignedDocs LIKE '%{$d['ofID']}%' ";
				$result = db::SQL($q);
				$signed_by=$result->num_rows;
				
				
				if ($d['ofActive']=='active'){
					$state="<span class='state_txt'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$d['ofActive']} </span>
					| <a data-state=\"disabled\" href=\"javascript:void(0)\"
					style=\"float:right;color:#337ab7;;text-decoration:underline;\" title=\"Changes states of document\" 
					onclick=\"change_doc_state( this, {$d['ofID']})\">disable</a>";
					$color="#337ab7;";
				}
				else {
					$state="<span class='state_txt'>{$d['ofActive']}</span>
					| <a href=\"javascript:void(0)\" 
					data-state=\"active\"
					style=\"float:right;color:#337ab7;;text-decoration:underline;\" title=\"Changes states of document\" 
					onclick=\"change_doc_state( this,{$d['ofID']})\">enable</a>";
					$color="red";
				}
				/*****
				<a style=\"color:#337ab7;;float:left\" title=\"Click to download document\" download href=\"{$d['otURLName']}\"><i class=\"fa fa-1x fa-download\" aria-hidden=\"true\"></i></a>
				&nbsp;&nbsp;
				********/
				$d['otURLName']=str_replace('hris.maidenlanemedical.com','humanroad.ferndaleroad.com',$d['otURLName']);
				
				$uploaded_files.="<tr><td>
				<a style=\"float:left;color:{$color};text-decoration:underline\" title=\"Click to view document\" target='_blank' href=\"{$d['otURLName']}\">{$d['ofTitle']}</a>
				<br><span style=\"float:left;\">Version: {$date}</span>
					{$see_more}
				</td>
				<td>{$state}</td>
				</tr>"; 
			 }
			 $uploaded_files.='</tbody></table><hr>';
		 }
		 if($addScript)
		 echo '<script>
			jQuery( document ).ready(function() {
				jQuery("#sel_resp").val(1).attr("disabled", "disabled");
			});
			</script>';
		 }
		 else {
			 $op_upl_files='';
			 $uploaded_files='';
			 $options_select_upl_files='';
		 }
		 
		 //&nbsp;<input class="upl_files_description" title="Please enter the description of document here" type="text" style="width:300px;padding:3px; border:1px solid #CCC;"  value="" placeholder="Description" name=upl_desc[]>
		 
		 echo '<div class = "row" >
				&nbsp;&nbsp;
				<input required id="task" style="cursor:pointer" type="radio" 
				name="'.$f.'" '.$_ck1.' value="task" onchange="showHideUploads(this)"> 
				<label for="task" style="font-weight:normal;cursor:pointer">Task</label>
				&nbsp;&nbsp;
				<input required id="docstosign" style="cursor:pointer" type="radio" 
				 name="'.$f.'" '.$_ck2.' value="docstosign" onchange="showHideUploads(this)"> 
				<label for="docstosign" style="font-weight:normal;cursor:pointer">Documents To Sign</label>
				</div>';
				
		echo 		
				'<div id="div_holder" style="display:none">
				<div id="upl_dir_NMB" class="my_upload" style="width:100%;padding-bottom:20px">
				<label style="font-weight:normal;width:130px;padding-top:5px;padding-top:6px;padding-bottom:8px;">New or Replace:'.ASTERISK.'</label>
				<select class="form-control" title="Please select what document is comming this to replace or is new one" 
				id="upl_select_NMB" class ="upl_select" name="upl_type[]" 
				style="float:right;width:75%;padding-bottom:10px;padding:2px">
				<option value="0">New document</option>'
				.$options_select_upl_files.
				'</select>
				<br>
				<label style="font-weight:normal;width:130px;padding-top:6px;padding-bottom:5px;">File to upload:'.ASTERISK.'</label>
				<input  type="file" id="uplNMB"  class="form-control upl_files" name="upl[]" onchange="proc(this)" 
				accept="application/pdf" placeholder="File" 
				style="padding-bottom:5px;padding-left:0;border:0;display:inline-block;float:right;width:75%;">
				<label style="font-weight:bold;padding-top:7px;padding-bottom:5px;">No empty spaces allowed in filename.</label><br>
				<label style="font-weight:normal;width:130px;padding-top:7px;padding-bottom:5px;">Title:'.ASTERISK.'</label>
				<input class="form-control upl_files_title" title="Please enter the title of document here" 
				type="text" style="float:right;width:75%;" name="upl_title[]" 
				onblur="check_me(this)">
				<br>
				<a style="float:right;text-decoration:underline;color:#337ab7;;cursor:pointer" title="Remove this document" hfer="javascript:void()" 
				onclick="del_div(NMB)">Delete</a></a>
				<br><br>
				</div>
				
				</div>
				
				<div id="uploadBlock" style="float:left;width:100%;display:'.$display.'">
				<div id="uploaded_files">'
				.$uploaded_files.
				'</div>
				<h5 style="margin-bottom:2em;">Upload new documents:  <a style="float:right;position:relative;top:-7px;" hfer="javascript:void()" class="btn btn-primary" onclick="add_div(this)"><i class="fa fa-plus-circle" aria-hidden="true"></i> Add / update document</a></h5>
				
				
				</div>
				
				</div>
				<script>
				var ind=0;
				$( document ).ready(function() {
					initTitle();
				});
				
				
				function check_me(that){
				   var txt =jQuery(that).val();
				   if (txt.match(/[\\/:*?<>|]/))   myalert("Warning","Symbols <b>\\\  /  :  *  ?  \"  <  >  |</b> are not allowed in title as title will be used as file name.");
				}
				
				

				function initTitle(){
					jQuery(".upl_files_title").keypress( function(e) {
							var chr = String.fromCharCode(e.which);
							if ("\\\/:*?\"<>|".indexOf(chr) >= 0){
								myalert("Warning","Symbols <b>\\\  /  :  *  ?  \"  <  >  |</b> are not allowed in title as title will be used as file name.");
								return false;
							}
					});
					
					
				}
					
				function proc(that){
					var id = jQuery(that).attr("id");
					var type = document.getElementById(id).files[0].type;
					var size = document.getElementById(id).files[0].size;
					if (type !="application/pdf" || size>10485760)
					{myalert("Alert","Allowed are only PDF files with max size 10MB");
					jQuery("#"+id).val(null);
					return;}
				}
				
				function showHideUploads(that){
					if(jQuery(that).val()=="docstosign") {
						jQuery("#uploadBlock").show();
						jQuery("#sel_resp").val(1).attr("disabled", "disabled");
						if	(jQuery("#uploaded_files").lenght==1 && 
							jQuery("#uploaded_files").innerHTML !="") there_is_old_files=true;
						else there_is_old_files=false;
					}
					else {
						jQuery("#uploadBlock").hide();
						jQuery("#sel_resp").removeAttr("disabled");
						there_is_old_files=true;
					}
				}
				function add_div(){
					ind++;
					var templ=jQuery("#div_holder").html();
					var tt = templ.replace(/NMB/g,ind);
					var old = document.getElementById("uploadBlock").innerHTML;
					jQuery(tt).appendTo("#uploadBlock");
					initTitle();
				}
				
				function del_div(that){
					if(confirm("Are you sure to delete this document?"))
					jQuery("#upl_dir_"+that).remove();
				}
				
				function check_submit(){ 
					if (jQuery("#task").is(":checked")) {
						jQuery(".my_upload").remove();
						return true;
					}
					
					
					var ret=true;
					
					if (jQuery(".upl_files:visible").length == 0 && !there_is_old_files) 
					{
						myalert("Alert","Please select at least one document for upload.");
						return false;
					}
					
					var count=0;
					var found=false;
					var double_selected=false;
					
					jQuery(".upl_files:visible").each(function( index,el ) { 
						
						if (jQuery(el).val() != "") { 
							if(jQuery(el).next().next(".upl_files_title").val()=="" ){
								found=true;
								return false;
							}
							else {count++;}
						}

						jQuery(".upl_select:visible").each(function( index ) {
							var myval=jQuery(this).val();
							var selcount=0;
							jQuery(".upl_select:visible").each(function( index ) {
								var cr_val = jQuery(this).val();
								if (cr_val == myval && cr_val != 0 ) selcount++;
								if(selcount > 1) {
									double_selected=true;
									return false;
								}
							});
							if (double_selected) return false;
						});
						if (double_selected) return false;
					});
					
					if (found) {
						myalert("Alert","Please provide title for every document for upload.");
						return false;
					}
					
					if (double_selected){
						myalert("Alert","It is not allowed to replace the same file with two and more new ones at once.");
						return false;
					}
					
					if (count == 0 && !there_is_old_files){
						myalert("Alert","You need to select at least one file for upload.");
						return false;
					}
					
					jQuery(".upl_files_title:visible").each(function( index,el ) { 
						
						var txt = jQuery(el).val();
						
						if (txt.match(/[\\/:*?<>|]/)) {
							myalert("Warning","There is title with not-allowed symbols.<br>Symbols <b>\\\  /  :  *  ?  \"  <  >  |</b> are not allowed in title as title will be used as file name.");
							ret=false;
							return false;
						}
					});
					
					if (ret) jQuery("#sel_resp").removeAttr("disabled");
					return ret;
				}
			</script>';
				
        if (isset(form::$error["otTaskOrDocsToSign"])) {
            form::html('<span class="err">' . form::$error["pSalaryTypeErr"][0] . '</span>');
        }
		
		
		form::closeDiv();
		
		form::closeDiv();
		
		 }
		 

        $args1 = $args;
        $args1["wrap-class"] .= " checklist-top-margin";
        form::inpCheckList(
            $lbls[($f = $pg["pfx"] . "Positions")] . ":".ASTERISK,
            $f,
            PositionModel::AllActiveKeyName(null, true),
            empty($data[$f]) ? [] : $data[$f],
            $args1 + array("type" => "checkbox", "id"=>"onboarding_positions")
        );

		 $arr=array(MANAGER_ID=>MANAGER_NAME);
		 if (OnboardingController::$type =='on')
			$arr= $arr+array(EMPLOYEE_THEMSELF_ID=>'--- '.EMPLOYEE_THEMSELF_NAME.' ---')
			    + 
				OnboardingModel::getEmplyeesNotLeavingOnly();
		 else
			$arr=$arr+OnboardingModel::getEmplyeesNotLeavingOnly();
		
        form::inpSelect(
            $lbls[($f = $pg["pfx"] . "AFID")] . ":",
            $f,
            $arr,
            isset($data[$f]) ? $data[$f] : filter_input(INPUT_POST, $f, FILTER_SANITIZE_NUMBER_INT),
            $args+array('id'=>'sel_resp')
        );

		  echo "<br>";
        form::openDiv(["class" => "row"]);
        form::html('<label class="'.$args['lbl-class'].'">Time frame:<span title="Required field" style="color: red;">*</span></label>');
        form::openDiv(["class" => "col-sm-3 col-md-2 col-lg-2"]);
        $tfNums = [];
        for ($i = 1; $i < 31; $i++) {
            $tfNums[$i] = $i;
        }
        $f = $pg["pfx"] . "TimeFrameNum";
        form::inpSelect(
            "",
            $f,
            $tfNums,
            isset($data[$f]) ? $data[$f] : filter_input(INPUT_POST, $f, FILTER_SANITIZE_NUMBER_INT),
            ["class" => "form-control"]
        );
        form::closeDiv(["class" => "col-sm-2 col-md-1 col-lg-1"]);


        form::openDiv(["class" => "col-sm-3 col-md-2 col-lg-2"]);
        $tfDims = ["day(s)" => "day(s)", "week(s)" => "week(s)", "month(s)" => "month(s)"];
        $f = $pg["pfx"] . "TimeFrameDim";
        form::inpSelect(
            "",
            $f,
            $tfDims,
            isset($data[$f]) ? $data[$f] : filter_input(INPUT_POST, $f, FILTER_SANITIZE_STRING),
            ["class" => "form-control"]
        );
        form::closeDiv(["class" => "col-sm-3 col-md-2 col-lg-2"]);
        form::closeDiv(["class" => "row"]);

        
		 if (OnboardingController::$type =='off') $ar2=array('disabled'=>'disabled');
		 else $ar2=array();

        form::inpSelect(
            $lbls[($f = $pg["pfx"] . "KeepStatus")] . ":",
            $f,
            $list["active"],
            isset($data[$f]) ? $data[$f] : "Yes",
            (array("skip-empty" => true) + $args+$ar2)
        );


        if (CompanyOfficeController::$id) {
            $args = self::$saveCancelArgsBase + array("id" => "saveOfficeBtn");
        } else {
            $args = self::$saveCancelArgsBase;
        }
        $args = self::$saveCancelArgsBase;

        form::inpSaveCancel(OnboardingController::getBackUrl()["url"], $args);


        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }


    public static function RenderOnboardingTasks($data = [], $inRespMode = true,$tab='any',$responsible=false)
    { 
	//cf::pr($data);
	//if ($tab=='about-me') cf::pr($data);
	if (OnboardingModel::$type=='off' && $tab=='about-me') return;
        if ($inRespMode) {
            $fieldName = 'otRespEmpl';
            $colTitle = 'Responsible now';
        } else {
            $fieldName = 'otEmployeer';
            $colTitle = 'Employee';
        }
		  
		 if ($tab == 'about-me') $colTitle = 'Responsible now';

        $renderButton = false;
        if (!empty($data)) {
            foreach ($data as $item) {
                if (empty($item["eoCompletedOn"])) {
                    $renderButton = true;
                    break;
                }
            }
        }
		
		  if ($tab == 'about-me') $renderButton = false;

        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "enctype" => "multipart/form-data", "validate" => true, "class" => "form-horizontal"));


        form::openTag("table");
        form::openTag("thead");
        form::openTag("tr");
        if ($renderButton) {
            form::Tag("", "th");
        }
        form::Tag("Task", "th",['style'=>"text-align:center"]);
        if (isset($data)) form::Tag($colTitle, "th",['style'=>"text-align:center"]);
		  
		 //if ($responsible)
		 if (!in_array($tab,array('admin','about-me')))			 
			form::Tag('Responsible now', "th",['style'=>"text-align:center"]);
        form::Tag("Time Frame", "th",['style'=>"text-align:center"]);
        form::Tag("Deadline", "th",['style'=>"text-align:center"]);
        form::Tag("Status", "th",['style'=>"text-align:center"]);
        form::closeTag("tr");
        form::closeTag("thead");

        form::openTag("tbody");

        if (empty($data)) {
            form::openTag("tr");
            form::Tag('No tasks assigned.', "td", array("class" => "text-center", "colspan" => "6"));
            form::closeTag("tr");
        } else {
            foreach ($data as $item) {
				
                if (!empty($item["eoCompletedOn"])) {
                    $status = '<span class="text-green">Done</span>';
					//echo AppModel::$usr["afID"].' '.$item['eoAFID'].' '.$item['eoOTID'];
                    if ($inRespMode  ) {
						   //if ($item['eoAFID'] == AppModel::$usr["afID"] )
							if ($item["otTaskOrDocsToSign"] != 'docstosign')
                       $status .= ' | <a href="'.ADMIN.'employee-details?id='.EmployeeController::$id.'&cmd=undo&eoid='.$item["eoID"].'" title="Undo"><i class="fa fa-undo"></i></a>';
                    } 
					   else {
						    
						    if ($tab != 'about-me'){
							
							if (($item['otAFID'] == AppModel::$usr["afID"] 
								||  $item['otAFID'] == EMPLOYEE_THEMSELF_ID )
								&& is_null($item['eoDocsToSign']))
							   $status .= ' | <a href="'.WP.'onboarding-tasks?cmd=undo&eoid='.$item["eoID"].'" title="Undo"><i class="fa fa-undo"></i></a>';
							
							
							}
                    }
                    $isDone = true;
                } else {
                    $isDone = false;
                    if (strtotime($item["eoTimeFrame"]) < time()) {
                        $status = '<span class="text-red">Overdue</span>';
                    } else {
                        $status = 'Incomplete';
                    }
                }

                form::openTag("tr");
                if ($renderButton) {
                    $addCheckBox = true;
                    if ($inRespMode) {
                        if (isset(AppModel::$admin["waaAFID"])) {
                            if (AppModel::$admin["waaAFID"] != $item["otAFID"]) {
                                $addCheckBox = false;
                            }
                        }
                    }
                 
				   
					if ($item['otTaskOrDocsToSign']=='docstosign')
					form::Tag('<input type="checkbox" disabled>' , "td", array("class" => "text-center"));
					else {
					if (AppModel::$usr['afID'] !=$item["otAFID"] && $item["otAFID"] !=1 )
					form::Tag('<input type="checkbox" disabled>' , "td", array("class" => "text-center"));
					else				
					form::Tag(($isDone || !$addCheckBox ? '' : 
				     '<input type="checkbox" name="onboarding_tasks[]" value="' . $item["eoID"] . '"'), "td", array("class" => "text-center"));
					}
					
                }
				
				   if ($item['otTaskOrDocsToSign']=='docstosign'){
					   $docs=explode('|',$item['eoDocsToSign']);
					   if($docs[0]=='') $docs=array();
					   $total_to_sign =count($docs);
					   $currentID=$tab.$item['eoID'];
					   $nmb_docs=count($docs);
					   if (empty($item['eoDocsRemainToSign']) || $item['eoDocsRemainToSign']=='')
							$nmb_to_sign = 0;
						else { 
							$_docs=explode('|',$item['eoDocsRemainToSign']);
							$nmb_to_sign=count($_docs);
						}
					   
					   $title='';//'Remain22 to sign '.$nmb_to_sign.' from '.$nmb_docs.' document(s).';
					   
					   $_add='';
					  
					   if (isset($_SESSION['webapp-admin-token']) && strpos($_SERVER['REQUEST_URI'],'admin') )
						   {
							   if ($total_to_sign==0 ) $_add='<span  style="float:right;margin-right:10px">No docs to sign</span>';
							   else {
							   if ($nmb_to_sign == 0 ){
								$_add='
								<a style="float:right;margin-right:10px" href="'.ADMIN.'employee-details?id='.$_GET['id'].'&cmd=view-docs&onbID='.$item['eoID'].'" title="'.$title.'" >
								<span style="color:#337ab7;">view >></span></a>
								&nbsp;&nbsp;<span style="color:green;float:right;margin-right:10px">All doc signed</span>&nbsp;&nbsp;';
							   }
								else { 
								$_add='
								<a style="float:right;margin-right:10px" href="'.ADMIN.'employee-details?id='.$_GET['id'].'&cmd=view-docs&onbID='.$item['eoID'].'" title="'.$title.'" >
								<span style="color:#337ab7;">view >></span></a>
								&nbsp;&nbsp;<span style="font-style:italic">'.$title.'</span>&nbsp;&nbsp;
								<span style="color:red;float:right;margin-right:10px">Signed '.($nmb_docs-$nmb_to_sign).' of '.$nmb_docs.' doc(s)</span>';
							   }
							   }
							}
						else 
						{ 					
					       if ($total_to_sign==0 ) $_add='<span  style="float:right">No docs to sign</span>';
						   else {
							if ($tab=='completed') 
							$_add='&nbsp;&nbsp;<a style="float:right" href="?cmd=sign&id='.$item['eoID'].'" title="'.$title.'" >
							View docs >>&nbsp;&nbsp;&nbsp;&nbsp;</a>';
						    else
							$_add='&nbsp;&nbsp;<a style="float:right;margin-right:10px" href="?cmd=sign&id='.$item['eoID'].'" title="'.$title.'" >
							Sign docs >></a>';
						   }
						}
				   }
				   else $_add='';
				   
				   
                form::Tag(nl2br(htmlspecialchars($item["otDescription"])).$_add, "td");
				  
                if($item[$fieldName] === "HR Manager") {
                    $link = "";
                } 
				  else {
                    if ($inRespMode) {
							//otAFID <-eoAFID
							if (in_array($item["otAFID"],array(0,1)))
							 $link = htmlspecialchars($item[$fieldName]);
						 else
                        $link = form::linkOut(ADMIN.'employee-details?id='.$item["otAFID"], htmlspecialchars($item[$fieldName]), ["target"=>"blank", "class"=>"text-#337ab7;"]);
                    } else {
						    if ($tab=='about-me') $_item = $item['otAFID'];  else $_item=$item["eoAFID"];
							
							 if (
							   ($tab != 'admin' && $_item == EMPLOYEE_THEMSELF_ID )
								||
								($tab != 'admin' && $_item == AppModel::$usr["afID"])
							 )
							 {
								 $link = EMPLOYEE_THEMSELF_NAME_FRONT;
							 } else if ( $_item == MANAGER_ID)
								 $link = MANAGER_NAME;
							 else
							 if ( $_item == MANAGER_ID)
								 $link = MANAGER_NAME;
							 else
                        $link = form::linkOut(WP.'employees?ID='.$_item, 
						    htmlspecialchars($item[$fieldName]), 
							["target"=>"blank", "class"=>"text-#337ab7;"]);
                    }
                }
				   $enable_check = false;
				   
				   if ($tab=='admin'){
					   $l=EMPLOYEE_THEMSELF_NAME;
				   }else {
						if (in_array($tab,array('any')))
							{$l=EMPLOYEE_THEMSELF_NAME_FRONT_SHORT; }
						else  
							{$l = EMPLOYEE_THEMSELF_NAME_FRONT;}
				   }
				  /*****/
				  if ($tab =='admin' && $item["otAFID"]==EMPLOYEE_THEMSELF_ID)
				   {
					
					   $link=$l;
					   $enable_check = true;
				   }
				   /******/
                form::Tag($link, "td");
				
					if (!in_array($tab,array('admin','about-me'))){
					   //if ($responsible) {
						   if (//trim($item["otResponsibleEmployeer"]) == EMPLOYEE_THEMSELF_NAME
							
								$item["otAFID"]==AppModel::$usr["afID"]
								|| 
								$item["otAFID"]==EMPLOYEE_THEMSELF_ID
						   ){
							   form::Tag(EMPLOYEE_THEMSELF_NAME_FRONT,"td");  
						   }						   
						   else {
							    if (MANAGER_ID == $item['otAFID'])
									$link =MANAGER_NAME;
								else
								$link = form::linkOut(WP.'employees?ID='.$item['otAFID'], 
								htmlspecialchars($item["otResponsibleEmployeer"]), 
								["target"=>"blank", "class"=>"text-#337ab7;"]);
							
								form::Tag($link, "td");
						   }	  
						//}
					}
					
                form::Tag($item["otTimeFrameNum"] . ' ' . $item["otTimeFrameDim"], "td");
                form::Tag(
				        val::convDate(MYSQL_DATE_TIME,USER_DATE,$item["eoTimeFrame"]), "td");
					
				   
                form::Tag($status, "td");
                form::closeTag("tr");
            }
        }

        form::closeTag("tbody");

        form::closeTag("table");

        if ($renderButton) {
            form::tag("", "hr");
            form::tag("Mark selected as Complete", "button", array("class" => "btn btn-primary", "name" => "complete-onboarding-tasks"));
        }

        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }

    public static function RenderResponsibleOnboardingTasks($data = [])
    {
        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "enctype" => "multipart/form-data", "validate" => true, "class" => "form-horizontal"));


        form::openTag("table");
        form::openTag("thead");
        form::openTag("tr");
        form::Tag("Task", "th");
        form::Tag("Time Frame", "th");
        form::closeTag("tr");
        form::closeTag("thead");

        form::openTag("tbody");


        $renderButton = false;
        if (empty($data)) {
            form::openTag("tr");
            form::Tag('No tasks assigned.', "td", array("class" => "text-center", "colspan" => "2"));
            form::closeTag("tr");
        } else {
            foreach ($data as $item) {
                form::openTag("tr");
                form::Tag(nl2br($item["otDescription"]), "td");
                form::Tag($item["otTimeFrameNum"] . ' ' . $item["otTimeFrameDim"], "td");
                form::closeTag("tr");
            }
        }

        form::closeTag("tbody");

        form::closeTag("table");

        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }
	
	
	
	 public static function getSelectTask($data){
		 $ret=array();
		 foreach ($data as $d){
			 $ret[$d['otID']]= $d['otDescription'];
		 }
		 return $ret;
	 }

    public static function RenderFilter($idx,$data=array())
    {
        $args = array("fld-class" => "filter_group", "lbl-class" => "horizontal-label", "wrap-class" => "", "class" => "form-control ");
        $btnargs = array("fld-class" => "filter_group auto-width ", "wrap-class" => "", "class" => "btn btn-primary");


        form::openDiv(array("class" => "filter_block"));

        form::openDiv(array("class" => "row"));

        form::openDiv(array("class" => "col-sm-12"));

        form::openTag("form", array("method" => "GET", "enctype" => "multipart/form-data", "validate" => true, "class" => "form-horizontal"));

        form::inpSelect("Task:", "task" . $idx, array("-1" => "Any task") +
                      //OnboardingModel::AllActiveKeyName(isset(AppModel::$usr["afID"]) ? AppModel::$usr["afID"] : null),
					       self::getSelectTask($data),
                        filter_input(INPUT_GET, "task{$idx}", FILTER_SANITIZE_NUMBER_INT), 
						    $args + array("skip-empty" => 1));
        form::inpField("Employee:", "name{$idx}", filter_input(INPUT_GET, "name{$idx}", FILTER_SANITIZE_STRING), $args);

        form::inpSaveCancel(null, $btnargs, "Filter");

        form::closeTag("form");

        form::closeDiv(array("class" => "col-sm-12"));

        form::closeDiv(array("class" => "row"));

        form::closeDiv(array("class" => "filter_block"));
    }

    final static function RenderFrontOnboardingTasks($data, $activePage = "pending") {
	
        form::openDiv(array('class' => "content bg-white"));
        form::openDiv(array('class' => "row"));
        form::openDiv(array("class" => "col-sm-12"));
        form::tag("", "h3");
		
		$responsible = 
		EmployeeOnboardingModel::LoadResponsibleOnboardingTasks(AppModel::$usr["afID"], 
		"eoCompletedOn is null",'','pending','',OnboardingModel::$type	);
						
		  if (! empty($responsible)) $show_all=true; else $show_all=false;
		 
		  if (!isset($data['responsible']) or empty($data['responsible']))
			  $i_am_resp_for_empty = true; 
		  else
			  $i_am_resp_for_empty = false;
		 
		 //if ($i_am_resp_for_empty) echo 'EMPTY'; else echo 'NOT EMPTY';
		 //empty(OnboardingFrontModel::data(" otActive='Yes' and otAFID <> ".EMPLOYEE_THEMSELF_ID." and (otAFID=".AppModel::$usr["afID"].' OR otAFID='.EMPLOYEE_THEMSELF_ID.')'));
		 
		 if ($show_all ){// && !$i_am_resp_for_empty){ 
		 
		  if ($i_am_resp_for_empty){
				
				form::html('
            <ul class="nav nav-tabs text-default">
				<li '.($activePage == "pending" ? 'class="active"' : '').'><a data-toggle="tab" href="#pending-tasks">Tasks to do</a></li>
				<li '.($activePage == "about-me" ? 'class="active"' : '').'><a data-toggle="tab" href="#about-me-tasks">Tasks about me</a></li>
				<li '.($activePage == "completed" ? 'class="active"' : '').'><a data-toggle="tab" href="#completed-tasks">Completed Items</a></li>
            </ul>
        ');
		  }
		  else {
			if(OnboardingModel::$type=='off'){
				$activePage = "pending";
				form::html('
				<ul class="nav nav-tabs text-default">
				   <li '.($activePage == "pending" ? 'class="active"' : '').'><a data-toggle="tab" href="#pending-tasks">Tasks to do</a></li>					
					<li '.($activePage == "completed" ? 'class="active"' : '').'><a data-toggle="tab" href="#completed-tasks">Completed Items</a></li>
					<li><a data-toggle="tab" href="#responsible-tasks">Tasks I\'m responsible for</a></li>
				</ul>
			');
			}
			else
			form::html('
				<ul class="nav nav-tabs text-default">
				   <li '.($activePage == "pending" ? 'class="active"' : '').'><a data-toggle="tab" href="#pending-tasks">Tasks to do</a></li>					
					<li '.($activePage == "about-me" ? 'class="active"' : '').'><a data-toggle="tab" href="#about-me-tasks">Tasks about me</a></li>
					<li '.($activePage == "completed" ? 'class="active"' : '').'><a data-toggle="tab" href="#completed-tasks">Completed Items</a></li>
					<li><a data-toggle="tab" href="#responsible-tasks">Tasks I\'m responsible for</a></li>
				</ul>
			');
			  }
		 }
		  else {
			
			form::html('
            <ul class="nav nav-tabs text-default">
			      <li '.($activePage == "about-me" ? 'class="active"' : '').'><a data-toggle="tab" href="#about-me-tasks">Tasks about me</a></li>
                <li '.($activePage == "completed" ? 'class="active"' : '').'><a data-toggle="tab" href="#completed-tasks">Completed Items</a></li>
               
            </ul>
			');  
			
		  }
		
		 

        form::openDiv(array("class" => "tab-content"));

		 form::openDiv(array("id" => "about-me-tasks", "class" => "tab-pane fade".($activePage == "about-me" ? ' in active' : '')));
        self::RenderFilter(9,$_SESSION['about_me'][ AppModel::$usr["afID"] ]);
        self::RenderOnboardingTasks($data["about-me"], false,'about-me');
        form::closeDiv(array("id" => "about-me-tasks"));
		 
		
        if ($show_all){
        form::openDiv(array("id" => "pending-tasks", "class" => "tab-pane fade".($activePage == "pending" ? ' in active' : '')));
        self::RenderFilter(0,$_SESSION['pending'][ AppModel::$usr["afID"] ]);
        self::RenderOnboardingTasks($data["pending"], false );
        form::closeDiv(array("id" => "pending-tasks"));
        }
        form::openDiv(array("id" => "completed-tasks", "class" => "tab-pane fade".($activePage == "completed" ? ' in active' : '')));
        self::RenderFilter(1,$_SESSION['completed'][ AppModel::$usr["afID"] ]);
        self::RenderOnboardingTasks($data["completed"], false,'completed',$responsible);
        form::closeDiv(array("id" => "completed-tasks"));

		  if ($show_all && !$i_am_resp_for_empty){
        form::openDiv(array("id" => "responsible-tasks", "class" => "tab-pane fade"));
        self::RenderResponsibleOnboardingTasks($_SESSION['responsible'][ AppModel::$usr["afID"] ]);
        form::closeDiv(array("id" => "responsible-tasks"));
		  }

        form::closeDiv(array("class" => "tab-content"));

        form::closeDiv(array("class" => "col-sm-12"));
        form::closeDiv(array('class' => "row"));
        form::closeDiv(array('class' => "content bg-white"));
    }
}
