<?php

/** The CompanyOffice view */
class CredentialingView extends AppView
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
		 
		 table::create('employee_credentialing', 'ecID', "ASC", self::ADMIN_LISTS_ROWS_LIMIT,'credentialing-report');
		 
        table::$cols[] = Array('Credentialing', 'cName',array("filtered" => false));
        table::$cols[] = Array('Employee', "afName",array("filtered" => false));//, array("sort" => table::$export));
     //   table::$cols[] = Array('Responsible Employee', "otResponsibleEmployeer",array("filtered" => false));
		 table::$cols[] = Array('Due Date', "ecDueDate");//,array("filtered" => false));
		// table::$cols[] = Array('Status', "Status");
       // table::$cols[] = Array($lbls[($c = $pg["pfx"] . "TimeFrame")], $c, array("sort" => table::$export));
      //  table::$cols[] = Array("Keep status", $pg["pfx"] . "KeepStatus", array("sort" => table::$export));
       // table::$cols[] = Array("", $pg["pfx"] . "Active", array("class" => "link", "sort" => false));

        table::execute($query);
        
		  
		foreach (table::$data as $r => &$cells) {
			if (table::$export === false) {
				$cells['afName']='<a target="__blank" href="employee-details?id='.$cells['afID'].'">'.$cells['afName'].'</a>';
			}
			$cells['ecDueDate']=val::convDate(MYSQL_DATE_TIME,USER_DATE,$cells['ecDueDate']);
		}
		  
        table::load();
    }
	
	
	
	
    final static function All($pg, $query, $lbls, $rq)
    {
        if (!isset(table::$export)) {
            table::$export = false;
        }
        table::create($pg["tbl"], $pg["key"], "desc", self::ADMIN_LISTS_ROWS_LIMIT);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Name")], $c);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Type")], $c, array("sort" => false));
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Positions")], $c, array("sort" => false));
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "TimeFrame")], $c, array("sort" => false));
        table::$cols[] = Array("Keep files", $pg["pfx"] . "KeepFiles", array("sort" => false));
        table::$cols[] = Array("", $pg["pfx"] . "Active", array("class" => "link", "sort" => false));

        table::execute($query);
        if ($rq === "list") {
            list($view, $edit, $deactivate) = self::AllLnksCode($pg, array("view", "edit", "deactivate"));
        } else {
            list($view, $edit, $reactivate) = self::AllLnksCode($pg, array("view", "edit", "reactivate"));
        }

        foreach (table::$data as $r => &$cells) {
            if (table::$export === false) {
                $cells[$pg["pfx"] . "Name"] = form::linkOut($pg["details"]["url"] . "?id=" . $cells[$pg["key"]], htmlspecialchars($cells[$pg["pfx"] . "Name"]));
            }
            $cells[$pg["pfx"] . "Positions"] = htmlspecialchars($cells[$pg["pfx"] . "Positions"]);
            $cells[$pg["pfx"] . "TimeFrame"] = $cells[$pg["pfx"] . "TimeFrameNum"] . " " . $cells[$pg["pfx"] . "TimeFrameDim"];

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
        $flds4panel[$lbls[($c = $pg["pfx"] . "Name")]] = $dt[$c];
        self::DataPanel($flds4panel, $pg["item"] . " summary", "panel-default");
    }

    /** Shows a controller details view
     * @param type $data - controller::$data
     * @param type $lbls - model::$fldLbl
     * @param type $lists - lists generated in the model
     */
    final static function Details($data, $pg, $lbls, $list)
    { 
       
        
         if (CredentialingController::$id > 0) {
    echo '<a class="btn btn-primary"
        href="'.ADMIN.'credentialing-details?copy='
        .CredentialingController::$id.'"
        style="float:right;">
        <i class="fa fa-copy"></i> Copy this Credentialing
    </a>';
}
$args = self::$fldArgsBase;
        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "validate" => true, "class" => "form-horizontal", "id" => "credentialing-form"));
     
        if (strpos($_SERVER['REQUEST_URI'],'id')) {$addarr=array('readonly'=>'readonly');}
        else {$addarr=array();}
        form::inpField(
            $lbls[($f = $pg["pfx"] . "Name")] . ":",
            $f,
            $data[$f],
            $args +
            $addarr +
            (CredentialingController::$id > 0 ? array("hint" => "Be careful: Changing the name will affect all existing credentials. If an employee has uploaded a driving license and you change the credential name to \"medical exam\", the driving license file will be shown as \"medical exam\".
            <br/><br/>Please make only minor changes to the name. If a bigger change is needed, better disable the credential and create a new one.") : [])
        );

        $args1 = $args;
        $args1["wrap-class"] .= " checklist-top-margin";
        form::inpCheckList(
            $lbls[($f = $pg["pfx"] . "Positions")] . ":",
            $f,
            PositionModel::AllActiveKeyName(null, true),
            empty($data[$f]) ? [] : $data[$f],
            $args1 + array("type" => "checkbox", "id" > "onboarding_positions")
        );
		
		if ($data['cNotApplForSomeEmployees']=="yes") $checked='checked'; else $checked='';
		form::html('
            <div class="fld form-group">
                <label style="padding-top:12px" class="control-label col-sm-4 col-md-3">Not applicable to some employees:</label>
                <div class="col-sm-8 col-md-7 col-lg-5" style="margin-top:7px">
				   <input type="hidden"   name="cNotApplForSomeEmployees" value="no" >
				   <input style="width:20px;height:20px" type="checkbox" 
				   name="cNotApplForSomeEmployees" value="yes" '.$checked.'>
				   <br></div>
            </div>');

		/****
        if (CredentialingController::$id > 0) {
            form::inpInfo(
                $lbls[($f = $pg["pfx"] . "Type")] . ":",
                isset($data[$f]) ? $data[$f] : "one-time",
                $args + ["readonly" => "readonly"]
            );
        } else {
			/****/
            form::inpSelect(
                $lbls[($f = $pg["pfx"] . "Type")] . ":",
                $f,
                ["one-time" => "One-time", "recurring" => "Recurring"],
                isset($data[$f]) ? $data[$f] : "one-time",
                (array("skip-empty" => true,"onchange"=>"showHideRenewal(this)") + $args)
            );
       //}

//        $tfNums = [];
//        for ($i = 1; $i < 21; $i++) {
//            $tfNums[$i] = $i;
//        }
//        form::inpSelect(
//            $lbls[($f = $pg["pfx"] . "TimeFrameNum")] . ":",
//            $f,
//            $tfNums,
//            isset($data[$f]) ? $data[$f] : filter_input(INPUT_POST, $f, FILTER_SANITIZE_NUMBER_INT),
//            $args
//        );
//        $tfDims = ["day(s)" => "day(s)", "week(s)" => "week(s)", "month(s)" => "month(s)", "year(s)" => "year(s)"];
//        $f = $pg["pfx"] . "TimeFrameDim";
//        form::inpSelect(
//            " ",
//            $f,
//            $tfDims,
//            isset($data[$f]) ? $data[$f] : filter_input(INPUT_POST, $f, FILTER_SANITIZE_STRING),
//            $args
//        );


        form::openDiv(["class" => "row"]);
        form::html('<label class="control-label col-sm-3 col-md-3">Initial Time frame:<span title="Required field" style="color: red;">*</span></label>');
        form::openDiv(["class" => "col-sm-3 col-md-2 col-lg-2"]);
        $tfNums = [];
        for ($i = 1; $i < 21; $i++) {
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
        form::closeDiv(["class" => "col-sm-3 col-md-2 col-lg-2"]);


        form::openDiv(["class" => "col-sm-3 col-md-2 col-lg-2"]);
        $tfDims = ["day(s)" => "day(s)", "week(s)" => "week(s)", "month(s)" => "month(s)", "year(s)" => "year(s)"];
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
		
		/**********************  RENEWAL TIME FRAME ******************************/
		 echo "<script>
		function showHideRenewal(that){
				var state = $(that).val();
				if (state == 'recurring') $('#renewal-div').css('display','');
				else $('#renewal-div').css('display','none');
		}	
		</script>";
		
		$arr=$arr=["id"=>"renewal-div"];
		if (! isset($data["cType"]) || ( isset($data["cType"]) && $data["cType"] != 'recurring') )
			$arr=["id"=>"renewal-div","style"=>"display:none"];
		form::openDiv($arr);
		form::openDiv(["class" => "row"]);
		
		form::html('<label class="control-label col-sm-3 col-md-3">Renewal Time frame:<span title="Required field" style="color: red;">*</span></label>');
		form::openDiv(["class" => "col-sm-3 col-md-2 col-lg-2"]);
		$tfNums = [];
		for ($i = 1; $i < 21; $i++) {
			$tfNums[$i] = $i;
		}
		$f = $pg["pfx"] . "RenewalTimeFrameNum";
		form::inpSelect(
			"",
			$f,
			$tfNums,
			isset($data[$f]) ? $data[$f] : filter_input(INPUT_POST, $f, FILTER_SANITIZE_NUMBER_INT),
			["class" => "form-control"]
		);
		form::closeDiv(["class" => "col-sm-3 col-md-2 col-lg-2"]);


        form::openDiv(["class" => "col-sm-3 col-md-2 col-lg-2"]);
        $tfDims = ["day(s)" => "day(s)", "week(s)" => "week(s)", "month(s)" => "month(s)", "year(s)" => "year(s)"];
        $f = $pg["pfx"] . "RenewalTimeFrameDim";
        form::inpSelect(
            "",
            $f,
            $tfDims,
            isset($data[$f]) ? $data[$f] : filter_input(INPUT_POST, $f, FILTER_SANITIZE_STRING),
            ["class" => "form-control"]
        );
        form::closeDiv(["class" => "col-sm-3 col-md-2 col-lg-2"]);
		
		
       form::closeDiv(["class" => "row"]);
		
		if (isset ($data['cHasExpirationDate']) && $data['cHasExpirationDate']=="yes") $checked='checked'; else $checked='';
		form::html('
            <div class="fld form-group">
                <label style="padding-top:12px" class="control-label col-sm-4 col-md-3">Has Expiration Date:</label>
                <div class="col-sm-8 col-md-7 col-lg-5" style="margin-top:7px">
				   <input type="hidden"   name="cHasExpirationDate" value="no" >
				   <input id ="cHasExpirationDate" style="width:20px;height:20px" type="checkbox" 
				   name="cHasExpirationDate" onclick="showHideWhenDocCompleated()" value="yes" '.$checked.'>
				   <br></div>
            </div>
		<script>	
			$( document ).ready(function() {
				showHideWhenDocCompleated();
			});
			
			function showHideWhenDocCompleated(){
				var st=$("#cHasExpirationDate").prop("checked");
				if (!st) $("#ImportantWhenDocCompleated").css("display","");
				else $("#ImportantWhenDocCompleated").css("display","none");
			}
		</script>	
		');
			
		form::openDiv(["class" => "row"]);
		form::html('
            <div class="fld form-group">
                    <label class="control-label col-sm-3 col-md-3"></label>
                    <div class="col-sm-8 col-md-7 col-lg-5">
                    <div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
		If <b>checked</b>, the document is due to renew on that date. 
		It must be <b>no later than "Renewal Time Frame" from now</b>. 
		In this case "Renewal Time Frame" is a limit to prevent users 
		from entering too distant expiration date.
		<br><br>
		If <b>un-checked</b> then we automatically set<br> 
		<b>Expiration Date = today + Renewal Time Frame</b>.

		 </div>
       </div>');
		form::closeDiv();
			
		echo '<div id="ImportantWhenDocCompleated" style="display:none">';	
		if (isset ($data['cImportantWhenDocCompleated']) && $data['cImportantWhenDocCompleated']=="yes") $checked='checked'; else $checked='';
		form::html('
            <div class="fld form-group">
                <label style="padding-top:12px" class="control-label col-sm-4 col-md-3">
				  Is important when document was completed:</label>
                <div class="col-sm-8 col-md-7 col-lg-5" style="margin-top:7px">
				   <input type="hidden"   name="cImportantWhenDocCompleated" value="no" >
				   <input style="width:20px;height:20px" type="checkbox" 
				   name="cImportantWhenDocCompleated" value="yes" '.$checked.'>
				   <br></div>
            </div>');
        
		
		
		
		form::openDiv(["class" => "row"]);
		form::html('
            <div class="fld form-group">
                    <label class="control-label col-sm-3 col-md-3"></label>
                    <div class="col-sm-8 col-md-7 col-lg-5">
                    <div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
					<b>"Checked"</b> means the renewal date will be based on the document 
					completion date rather than today.<br> 
					Documents older than (today) - (renewal time frame) won\'t be accepted.
		 </div>
       </div>');
	   echo '</div>';
		form::closeDiv();
		
		
		form::closeDiv();
		
		form::closeDiv();
		
		
		form::closeDiv();
		/*******************   END RECURRING HIDDEN PART *********************************/




        form::inpSelect(
            $lbls[($f = $pg["pfx"] . "KeepFiles")] . ":",
            $f,
            $list["active"],
            isset($data[$f]) ? $data[$f] : "Yes",
            (array("skip-empty" => true, "hint" => "if 'Yes', expiration dates for recurring docs will be kept too") + $args)
        );


        if (CredentialingController::$id) {
            $args = self::$saveCancelArgsBase + array("id" => "saveOfficeBtn");
        } else {
            $args = self::$saveCancelArgsBase;
        }
        $args = self::$saveCancelArgsBase;

        form::inpSaveCancel(CredentialingController::getBackUrl()["url"], $args);
        
        


        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }


    public static function RenderCredentialings($data = [], $isDone = false, $isAdmin = false, $isPreviousPositions = false)
    {
        
        $tblColCnt = 4;
        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "id" => "formCredentials", "enctype" => "multipart/form-data", "validate" => true, "class" => "form-horizontal"));
        form::openTag("table");
        form::openTag("thead");
        form::openTag("tr");
        form::Tag("Name", "th",['style'=>"text-align:center"]);
        form::Tag("Expiration/renewal", "th",['style'=>"text-align:center"]);
        form::Tag("Due Date", "th",['style'=>"text-align:center"]);
        if ($isDone) {
            form::Tag("Date uploaded", "th",['style'=>"text-align:center"]);
            $tblColCnt++;
        }

        if($isPreviousPositions) {
            form::Tag("Position", "th");
            $tblColCnt++;
        }

        form::Tag("", "th");
        form::closeTag("tr");
        form::closeTag("thead");

        form::openTag("tbody");

        if (empty($data)) {
            form::openTag("tr");
            form::Tag('No records.', "td", array("class" => "text-center", "colspan" => $tblColCnt));
            form::closeTag("tr");
        } else {
            foreach ($data as $item) {
				$today = new DateTime();
             if (!isset($item["ecDueDate"]) ) $item["ecDueDate"]=$today->format(MYSQL_DATE_TIME);
			   $due_date = DateTime::createFromFormat(MYSQL_DATE_TIME,$item["ecDueDate"]);
				
			     
                if(
					  (!$isDone && $due_date->getTimestamp() < $today->getTimestamp())
					|| 
					  (
						$item["cType"]=='recurring' && $item["cOldType"]=='one-time' &&
					    $item["ecUploadedOn"]== NULL && $item["ecExpiredDate"] == NULL 
					  )
				   ) {
                    form::openTag("tr", ["style"=>"color: red;"]);
                } else {
                    form::openTag("tr");
                }

                form::Tag(nl2br(htmlspecialchars($item["cName"])), "td");
				   
				  $add='';
				  $expdate = val::convDate(MYSQL_DATE_TIME,USER_DATE,$item["ecExpiredDate"]);
				  if ($isDone && $item['ecIsApplicableForMe']=='no') form::Tag("n.a.", "td");
				   else {
						if ($item["cType"] === "one-time") {
							$expdate ="one-time";
						} else {
							if (!empty($item["ecExpiredDate"])) {
								
								$expdate = val::convDate(MYSQL_DATE_TIME,USER_DATE,$item["ecExpiredDate"]);
								   //date(USER_DATE, strtotime($item["ecExpiredDate"]));<<<
							} else {
								$expdate = "not set";
							}
							
							
							if ($isDone) {
								//form::Tag($expdate, "td");
								
							} else {
								
								if (
								$item["cType"]=='recurring' && $item["cOldType"]=='one-time' &&
								$item["ecUploadedOn"]== NULL  && $item["ecExpiredDate"] == NULL 
								)
								$add =
								" [<a href='' onclick='return editCredentialingExpDate({$item["ecID"]}, \"\")'>enter date</a>]";
								else {
								
								if ($item["cType"]=='recurring' && $item["cHasExpirationDate"]=='no')
								$add='';
							   else
								 $add= " [<a href='' onclick='return editCredentialingExpDate({$item["ecID"]}, \"{$expdate}\")'>edit</a>]";
							    }
								
							}
						}
				   }
				   if ($item['ecIsApplicableForMe']=='yes') form::Tag($expdate.$add, "td");
				 
				   $tg='';
				   if ($isDone && $item['ecIsApplicableForMe']=='no') $tg='n.a';
				   else 
					 
					 if (
							($item["cType"]=='recurring' && $item["cOldType"]=='' &&
							  ! CredentialingModel::hasUploadedFile($item['ecCID'],$item['ecAFID'] ) )
							||
							$item["cType"]=='one-time'
						) {
					
				       $tg= val::convDate(MYSQL_DATE_TIME,USER_DATE,$item["ecDueDate"]);
						}
				   
				   if (
							$item["cType"]=='recurring' && $item["cOldType"]=='one-time' &&
							$item["ecUploadedOn"]== NULL && $item["ecExpiredDate"] == NULL 
						) $tg = "not set";
					if ($tg=='')	$tg= val::convDate(MYSQL_DATE_TIME,USER_DATE,$item["ecDueDate"]);
					if ($item['ecIsApplicableForMe']=='no') $tg='n.a.';
					
					form::Tag($tg,"td"); //  DUE DATE
				   

                if ($isDone) {
					   if ($item['ecIsApplicableForMe']=='no') $tg='n.a';
					   else $tg= val::convDate(MYSQL_DATE_TIME,USER_DATE,$item["ecUploadedOn"]);
                    form::Tag($tg,"td");

                    if($isPreviousPositions) {
                        $posStr = '';
                        if(!empty($item["position"])) {
                            $posStr = '<strong>'.$item["position"].'</strong> from date: '.$item["onPosFrom"];
                        }
                        form::Tag($posStr, "td");
                    }
					
					  if ($item['ecIsApplicableForMe']=='no')
						form::Tag('not applicable to me','td');
					  else
                    form::Tag("<a href='credentialings?download={$item["ecID"]}'>Download file >></a>", "td");
                } else {
					  if(
						$item["cType"]=='recurring' && $item["cOldType"]=='one-time' &&
					    $item["ecUploadedOn"]== NULL && $item["ecExpiredDate"] == NULL 
					  )   $t='n.a.';
					  else {
						if ($item['cType'] === "recurring" && $item['cHasExpirationDate']=='yes'){
						
							$withExpDate=1; 
							$complDate=0;
							$ti=  $item['cRenewalTimeFrameNum'].' '.$item['cRenewalTimeFrameDim'];
							
							$date = new DateTime();//$item["ecExpiredDate"]);
							$date->add(date_interval_create_from_date_string($ti));
							$expDate = $date->format(USER_DATE);
							
					   }
						else {
							//echo '>>>>'.$item['cImportantWhenDocCompleated'];
							$withExpDate=0;
							$expDate=0;
							$complDate=0;
							if (($item['cType'] === "recurring" && $item['cImportantWhenDocCompleated']=='yes')){
								$ti=  $item['cRenewalTimeFrameNum'].' '.$item['cRenewalTimeFrameDim'];
								$date = new DateTime();
								$date->sub( date_interval_create_from_date_string($ti));
								$complDate = $date->format(USER_DATE);
							
							}
						}
						//print_r($item);echo '<hr>';
						//{$withExpDate},{$expDate},{$complDate}
					   $t="<a href='' 
					   onclick='return uploadCredentialingFile({$item["ecID"]}, {$withExpDate},\"{$expDate}\",\"{$complDate}\");'>
					   Upload file >></a>";
					  }
						if ($item['ecNotApplicForMe']=='yes') 
						 $t.='<span style="color:gray"> | </span><a href="javascript:void()"
						 onclick="if (confirm(\'Are you sure this document is not applicable to you?\'))
							 location.href=\'credentialings?skip='.$item["ecID"].'\'">Skip >> (not applicable to me)</a>';
                    
					
					  form::Tag($t, "td");
                }

                form::closeTag("tr");
            }
        }

        form::closeTag("tbody");

        form::closeTag("table");

        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }

    public static function RenderCredentialingsForAdmin($data = [])
    {
//echo "<pre>";print_r($data);echo "</pre>";
        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"));
        form::$ofsetIndentsCount = 2;
        form::openTag("table");
        form::openTag("thead");
        form::openTag("tr");
        form::Tag("Name", "th");
        form::Tag("Expiration/renewal", "th");
        form::Tag("Due Date", "th");
        form::Tag("Date uploaded", "th");
        form::Tag("Status", "th");
        form::Tag("", "th");
        form::closeTag("tr");
        form::closeTag("thead");

        form::openTag("tbody");

        if (empty($data)) {
            form::openTag("tr");
            form::Tag('No records.', "td", array("class" => "text-center", "colspan" => "6"));
            form::closeTag("tr");
        } else {
            foreach ($data as $item) {
                if (!empty($item["ecUploadedOn"])) {
                    $status = '<span class="text-green">Done</span>';
                } else {
                    if (strtotime($item["ecDueDate"]) < time()) {
                        $status = '<span class="text-red">Overdue</span>';
                        form::openTag("tr", ["style"=>"color:red;"]);
                    } else {
                        $status = 'Incomplete';
                        form::openTag("tr");
                    }
                }

                form::Tag(nl2br(htmlspecialchars($item["cName"])), "td");
				   if ($item['ecIsApplicableForMe']=='no')
					 form::Tag("n.a.", "td");
				
                else if ($item["cType"] === "one-time") {
                    form::Tag("one-time", "td");
                } else {
                    if (!empty($item["ecExpiredDate"])) {
                        $expdate = val::convDate(MYSQL_DATE_TIME,USER_DATE,$item["ecExpiredDate"]);
						     //date(USER_DATE, strtotime($item["ecExpiredDate"]));<<<
                    } else {
                        $expdate = "not set";
                    }
                    form::Tag($expdate, "td");
                }
				
				   if ($item['ecIsApplicableForMe']=='no')
					   $tg ='n.a.';
				   else
					   $tg= val::convDate(MYSQL_DATE_TIME,USER_DATE,$item["ecDueDate"]);
                form::Tag($tg,"td");
				   
                if (empty($item["ecUploadedOn"]) && $item['ecIsApplicableForMe']!='no') {
					
                    form::Tag("", "td");
                    form::Tag($status, "td");
                    form::Tag("", "td");
                } else {
					    if ($item['ecIsApplicableForMe']=='no') 
							$tg='n.a.';
						else
							$tg = val::convDate(MYSQL_DATE_TIME,USER_DATE,$item["ecUploadedOn"]);
                    
					form::Tag($tg,"td");
					    // date(USER_DATE, strtotime($item["ecUploadedOn"]))
					
						if ($item['ecIsApplicableForMe']=='no') $status='NA';
                    form::Tag($status, "td");
					
					    if ($item['ecIsApplicableForMe']=='no')// && $item['	ecNotApplicForMe']=='yes') 
							$tg="<a class=\"text-blue\" href='" . ADMIN . "employee-details?id={$item["ecAFID"]}&cmd=undo_applicable&ecID={$item["ecID"]}' onclick='return confirm(\"Please confirm un-do not applicable status.\")'>un-do \"not applicable\" status</a>";
						else
							$tg="<a class=\"text-blue\" href='" . WP . "credentialings?download={$item["ecID"]}'>Download file</a> | 
                            <a class=\"text-blue\" href='" . ADMIN . "employee-details?id={$item["ecAFID"]}&cmd=remove&ecID={$item["ecID"]}' onclick='return confirm(\"Please confirm the removal of the file.\")'>Remove file</a>";
							
                    form::Tag($tg, "td");
                }

                form::closeTag("tr");
            }
        }

        form::closeTag("tbody");

        form::closeTag("table");

        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }


    public static function RenderFilter($idx)
    {
        $args = array("fld-class" => "filter_group", "lbl-class" => "horizontal-label", "wrap-class" => "", "class" => "form-control ");
        $btnargs = array("fld-class" => "filter_group auto-width ", "wrap-class" => "", "class" => "btn btn-primary");


        form::openDiv(array("class" => "filter_block"));

        form::openDiv(array("class" => "row"));

        form::openDiv(array("class" => "col-sm-12"));

        form::openTag("form", array("method" => "GET", "enctype" => "multipart/form-data", "validate" => true, "class" => "form-horizontal"));

        form::inpSelect("Name:", "credentialing" . $idx, array("-1" => "Any credentialing") +
            CredentialingModel::AllActiveKeyName(isset(AppModel::$usr["afID"]) ? AppModel::$usr["afID"] : null),
            filter_input(INPUT_GET, "credentialing{$idx}", FILTER_SANITIZE_NUMBER_INT), $args + array("skip-empty" => 1));

        form::inpSaveCancel(null, $btnargs, "Filter");

        form::closeTag("form");

        form::closeDiv(array("class" => "col-sm-12"));

        form::closeDiv(array("class" => "row"));

        form::closeDiv(array("class" => "filter_block"));
    }

    final static function RenderFrontCredentialings($data, $activePage = "pending")
    {
        
        $AllCredentialings = SkipUloadedCredentials(array_merge($data["pending"], $data["completed"], $data["previous"]));
        
        global $CREDENTIALINGS_TO_SKIP_BECAUSE_UPLOADED;
        
        $ProcessedPending = array();
        
        foreach ($data["pending"] as $SingleItem)
        {
            //print ("-" . $SingleItem["ecID"] . "-");
            if (strpos($CREDENTIALINGS_TO_SKIP_BECAUSE_UPLOADED, "-" . $SingleItem["ecID"] . "-") === false)
            {
                array_push($ProcessedPending, $SingleItem);
            }
        }
        //echo "SE:";
        //print ($CREDENTIALINGS_TO_SKIP_BECAUSE_UPLOADED);
        
        $ProcessedCompleted = array();
        
        foreach ($data["completed"] as $SingleItem)
        {
            //print ("-" . $SingleItem["ecID"] . "-");
            if (strpos($CREDENTIALINGS_TO_SKIP_BECAUSE_UPLOADED, "-" . $SingleItem["ecID"] . "-") === false)
            {
                array_push($ProcessedCompleted, $SingleItem);
            }
        }
        
        
        
        form::openDiv(array('class' => "content bg-white"));
        form::openDiv(array('class' => "row"));
        form::openDiv(array("class" => "col-sm-12"));
        form::tag("", "h3");

        form::html('
            <ul class="nav nav-tabs text-default">
                <li ' . ($activePage == "pending" ? 'class="active"' : '') . '><a data-toggle="tab" href="#pending-tasks">Required</a></li>
                <li ' . ($activePage == "completed" ? 'class="active"' : '') . '><a data-toggle="tab" href="#completed-tasks">Credentialing files</a></li>                
                '. (!empty($data["previous"]) ? '<li ' . ($activePage == "previous" ? 'class="active"' : '') . '><a data-toggle="tab" href="#previous-positions">Previous Positions</a></li>' : '') .'                
            </ul>
        ');

        form::openDiv(array("class" => "tab-content"));

        form::openDiv(array("id" => "pending-tasks", "class" => "tab-pane fade" . ($activePage == "pending" ? ' in active' : '')));
        //print_r($ProcessedPending);exit;
        self::RenderCredentialings($ProcessedPending, false);
        form::closeDiv(array("id" => "pending-tasks"));

        form::openDiv(array("id" => "completed-tasks", "class" => "tab-pane fade" . ($activePage == "completed" ? ' in active' : '')));
//        self::RenderFilter(1);
        self::RenderCredentialings($ProcessedCompleted, true);
        form::closeDiv(array("id" => "completed-tasks"));

        if(!empty($data["previous"])) {
            form::openDiv(array("id" => "previous-positions", "class" => "tab-pane fade" . ($activePage == "previous" ? ' in active' : '')));
//            self::RenderFilter(2);
            self::RenderCredentialings($data["previous"], true, false, true);
            form::closeDiv(array("id" => "completed-tasks"));
        }

        form::closeDiv(array("class" => "tab-content"));

        form::closeDiv(array("class" => "col-sm-12"));
        form::closeDiv(array('class' => "row"));
        form::closeDiv(array('class' => "content bg-white"));


        form::html('
<div class="modal fade" id="credentialingUploadModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Upload credentialing</h4>
      </div>
      <div class="modal-body" style="max-height: 400px; overflow-y: auto; ">');

        form::openTag("form", array("method" => "post", "id" => "formCredentials", "onsubmit" => "return validateCredentialingUpload(".CREDENTIALING_MAX_FILE_SIZE.")", "enctype" => "multipart/form-data", "validate" => false, "class" => "form-horizontal"));
        form::html('<input type="hidden" name="ecID" />');
        form::inpField(
            "Select file:",
            "credentialingFile",
            "",
            array("hint" => "Allowed file formats: .pdf, .doc, .docx, .xls, .xlsx, .jpg, .png.", 
			
			 "fld-class" => "form-group", "lbl-class" => "control-label col-md-12 text-left", "wrap-class" => "col-md-12", "class" => "form-control", "type" => "file")
        );
		 echo '<div id="exp_date" >';
        form::inpField(
            "Expiration/renewal:",
            "credentialingExpDate",
            "",
            array("hint" => "Example: ".USER_DATE_EXAMPLE, "fld-id" => "credentialingExpDate", "fld-class" => "form-group", "lbl-class" => "control-label col-md-12 text-left", "wrap-class" => "col-md-12", "class" => "form-control")
        );
		 echo '<input type="hidden" id="exp-date-field" value="0">';
	     echo '</div>';
		 
		 echo '<div id="complBlock" style="display:none">';
        form::inpField(
            "Date completed/created:",
            "credentialingComplDate",
            "",
            array("hint" => "Example: ".USER_DATE_EXAMPLE, "fld-id" => "credentialingComplDate", "fld-class" => "form-group", "lbl-class" => "control-label col-md-12 text-left", "wrap-class" => "col-md-12", "class" => "form-control")
        );
		 echo '<input type="hidden" id="compl-date-field" value="0">';
	     echo '</div>';
		 
		 
        form::html("<div class='alert alert-warning'><strong>IMPORTANT:</strong> You cannot update/delete a file once it's uploaded so please make sure you're uploading the correct file. If you've uploaded a file by mistake - please contact the HR manager.</div>");

        form::html('</div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Upload</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      </div>');
        form::closeTag("form");
        form::html('</div></div></div>');

        form::html('<div><form method="POST" id="editCredentialRenewalDate">
		 <input type="hidden" name="ecID" />
		 <input type="hidden" name="newRenewalDate"/>
		 <input type="hidden" name="emptyecFileName" id="emptyecFileName" value="no" />
		 </form></div>');
    }

    final static function RenderAdminCredentialings($data, $activePage = "pending")
    {
        form::openDiv(array('class' => "content bg-white"));
        form::openDiv(array('class' => "row"));
        form::openDiv(array("class" => "col-sm-12"));

        self::RenderCredentialingsForAdmin($data);

        form::closeDiv(array("class" => "col-sm-12"));
        form::closeDiv(array('class' => "row"));
        form::closeDiv(array('class' => "content bg-white"));

    }


}

