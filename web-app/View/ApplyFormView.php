<?php

/** The Vendor view */
class ApplyFormView extends AppView
{
    protected static $fldArgsBase = array("fld-class" => "form-group", "lbl-class" => "control-label col-sm-4 col-md-3", "wrap-class" => "col-sm-12 col-md-12 col-lg-12", "class" => "form-control"),
        $saveCancelArgsBase = array("fld-class" => "form-group", "wrap-class" => "col-sm-8 col-md-7 col-lg-5 col-sm-offset-4 col-md-offset-3", "class" => "btn btn-primary", "class-cancel" => "btn btn-default");

    final static function Positions($withManager=false)
    {
        $positions = PositionModel::AllActiveKeyName(null, true);
		
		
        form::html('<div class="row"><div class="col-sm-12"><label>Apply directly:</label>');

        $args = self::$fldArgsBase;
        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "get", "class" => "form-horizontal", "action" => WP . "application-form"));

        form::inpSelect(
            "",
            "pID",
            array("{!--Empty--!}" => "Select desired position", "0" => "--- Not sure ---") + $positions,
            "{!--Empty--!}",
            $args + array("id" => "activePositions")
        );


        form::html('<div class="fld form-group">
                        <div class="col-sm-8 col-md-7 col-lg-5">
                            <button type="submit" id="btnApplyPosition" value="Save" class="btn btn-primary" disabled>Proceed</button><br/>&nbsp;
                        </div>
                    </div>');


        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));

        form::html('</div></div>');
    }

    final static function PositionsInOneRow()
    {
        $positions = PositionModel::AllActiveKeyName(null, true);

        form::html('<div class="row"><div class="col-sm-12"><br/>');
        // form::html('<div class="row"><div class="col-sm-12"><hr/>');

        $args = array("lbl-class" => "control-label", "lbl-style" => "flex:none;", "class" => "form-control");
        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"));
        form::$ofsetIndentsCount = 2;
        form::$inpFieldContainer = false;
        form::openTag("form", array("method" => "get", "class" => "form-horizontal", "action" => WP . "application-form"));


        form::inpSelect(
            "Apply directly:",
            "pID",
            array("{!--Empty--!}" => "Select desired position", "0" => "--- Not sure ---") + $positions,
            "{!--Empty--!}",
            $args + array("id" => "activePositions", "style" => "width:auto;display:inline-block;margin:0px 10px 0px 10px;")
        );

        form::html('<button type="submit" id="btnApplyPosition" value="Save" class="btn btn-primary" style="vertical-align: baseline;" disabled>Proceed</button><hr/>');


        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));

        form::html('</div></div>');
    }

    final static function Questinnarie($pID, $pg, $rq, $data)
    {

        $questions = isset($data["questions"]) ? $data["questions"] : [];

        form::html('<div class="content bg-white">');
        form::html('<div class="row"><div class="col-sm-12">');
        form::html('<div class="content" id="questionnaire"><h3 style="margin-top: -10px;">Questionnaire</h3><div class="row">');

        if (filter_has_var(INPUT_GET, "questionnaire")) {
            form::html('<div class="col-sm-12 col-md-12 col-lg-12"><br/><div class="alert alert-danger">Sorry, your application is automatically rejected based on your answers to the questionnaire and required skills.</div><br/><a href="' . WP . '" class="btn btn-primary">Continue</a></div></div></div></div></div>');
        } else {
            if (!empty($questions)) {
                foreach ($questions as $q) {
                    $answers = '';
                    foreach ($q["answers"] as $ans) {
                        $reject = "";
                        $rps = $ans["rejectedFor"];
                        foreach ($rps as $rp) {
                            if ($rp["arpPID"] === $pID) {
                                $reject = " reject='true' ";
                                break;
                            }
                        }
                        $answers .= '<div class="col-sm-12 col-md-4 col-lg-3"><label><input type="radio" name="question' . $q["qID"] . '"' . $reject . ' value="' . $ans['aID'] . '"> ' . $ans["aName"] . '</label></div>';
                    }

                    form::html('
                <div class="card-question">
                    <div class="question-title"><i class="fa fa-question-circle"></i> ' . htmlspecialchars($q["qName"]) . '</div>
                    <div class="question-body"><hr/>' . $answers . '</div>
                    <div class="question-body err col-sm-12 col-md-12 col-lg-12"></div>                    
                </div>
            ');
                }
                form::html('<div class="col-sm-12 col-md-12 col-lg-12" id="qresult"><a href="javascript:checkAnswers();" class="btn btn-primary">Continue</a></div></div></div></div></div>');
            }
        }
    }

    final static function SuccessMessage($pID, $pg, $rq, $data)
    {

        form::html('<div class="content bg-white"><div class="row"><div class="col-sm-12" id="questionnaire"><h3>Successful application</h3><div class="row">');

        form::html('<div class="col-sm-12 col-md-12 col-lg-12"><br/>
                    <div class="alert alert-success">Thank you, we\'ll process your application and we\'ll contact you if you\'re selected for an interview.</div><br/>
                    <a href="' . WP . '" class="btn btn-primary">Continue</a>
                    </div>
                    </div>
                    </div></div></div>');
    }

    public static function ShowErrornMessage($msg, $buttons = '')
    {
        form::html('<div class="content bg-white">');
        form::html('<div class="row">');
        form::html('<div class="col-sm-12 col-md-12 col-lg-12">
                        <br/>
                        <div class="alert alert-danger">' . $msg . '</div>
                        <br/>' . $buttons . '
                    </div>
                    </div>
                    </div>');

    }

    final static function Form($pID, $pg, $rq, $lbls, $data, $readonly = false)
    {
	//echo '>>>>';cf::pr(form::$error);
		 global $_US_STATES,$_RELS,$_GENDER,$_M_STATUS;
		 
        $isUser = isset(AppModel::$usr["afID"]);
        $position = $data["position"];
        $job = !empty($data["job"]) ? $data["job"] : [];

        form::html('<div class="content bg-white"><div class="row"><div class="col-sm-12"><h3>Application Form</h3>');

        $args = array("fld-class" => "form-group", "lbl-class" => "control-label col-sm-4 col-md-3", "wrap-class" => "col-sm-12 col-md-6 col-lg-6", "class" => "form-control");

        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"));
        form::$ofsetIndentsCount = 2;

        form::openTag("form", array("method" => "post", "enctype" => "multipart/form-data", "validate" => true, "class" => "form-horizontal"));
        
        if (!$isUser) {
            form::openDiv([]);
            form::html('
                    <div class="alert alert-warning">
                        Note: If you\'re already an employee, please <strong>do not</strong> submit the form below but <a href="' . WP . '">login to your account</a> and then apply.
                    </div>');
            form::closeDiv();
        }

        if (!empty($job)) {
            form::html('<div class="fld form-group">
                        <label class="control-label col-sm-4 col-md-3">Desired Job:</label>
                        <div class="col-sm-8 col-md-6 col-lg-6"><h4 class="form-control auto-height"><strong>' . $job['jName'] . '</strong></h4></div>
                    </div>');
        }
        form::html('<div class="fld form-group">
                        <label class="control-label col-sm-4 col-md-3">Desired Position:</label>
                        <div class="col-sm-8 col-md-6 col-lg-6"><h4 class="form-control auto-height"><strong>' . $position['pName'] . '</strong></h4></div>
                    </div>');
        form::closeTag("form");

        form::html('<hr/>');

        form::openTag("form", array("method" => "post", "enctype" => "multipart/form-data", "validate" => true, "class" => "form-horizontal", "id" => "dataForm"));
		 
        form::openDiv(["id" => "form-data"]);


        if (($pID > 0) && ($isUser)) {
            $min = $data["position"]["pSalaryFrom"];
            $max = $data["position"]["pSalaryTo"];
            $avg = round(($min + $max) / 2);

            $salary = empty($data['afDesiredSalary]']) ? $avg : round($data['afDesiredSalary]']);
            if (($salary < $min) || ($salary > $max)) {
                $salary = $avg;
            }

            form::inpField(
                $lbls[($f = $pg["pfx"] . "DesiredSalary")] . ":",
                $f,
                $salary,
                $args + array("type" => "range", "min" => $min, "max" => $max, "step" => "1", "oninput" => "showVal(this.value)", "onchange" => "showVal(this.value)")
            );

            form::html('<div class="fld form-group" style="margin-top: -10px;"><label class="control-label col-sm-4 col-md-3"></label><div class="col-sm-8 col-md-6 col-lg-4">
                    <div><label id="desiredSalary">' . PositionModel::formatSalary($avg) . '</label> <label>' . $data["position"]["pSalaryType"] . '</label></div></div></div>');



            $args1 = $args;
            $args1["wrap-class"] .= " checklist-top-margin";
            $skills = SkillModel::AllActiveKeyName();
            $selected = [];
            $f = $pg["pfx"] . "Skills";
            if (!empty($data[$f])) {
                $userSkills = explode(",", $data[$f]);
                foreach ($skills as $id => $skill) {
                    if (in_array($skill, $userSkills)) {
                        $selected[] = $id;
                    }
                }
            }
            form::inpCheckList(
                $lbls[$f] . ":",
                $f,
                $skills,
                $selected,
                $args1 + array("type" => "checkbox")
            );

        }

        form::inpField(
            $lbls[($f = $pg["pfx"] . "FirstName")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args + ($readonly ? ["readonly" => "readonly"] : [])
        );

        form::inpField(
            $lbls[($f = $pg["pfx"] . "LastName")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args + ($readonly ? ["readonly" => "readonly"] : [])
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
            $lbls[($f = $pg["pfx"] . "Birthday")] . ":",
            $f,
            empty($data[$f]) ? "" : val::convDate(MYSQL_DATE_TIME,USER_DATE,$data[$f]),
			  //date(USER_DATE, strtotime($data[$f]))<<<
            $args + array("hint" => "Example: ".USER_DATE_EXAMPLE) + 
						($readonly ? ["readonly" => "readonly"] : [])
        );
		
		form::inpField(
            $lbls[($f = $pg["pfx"] . "SSNmb")] . ":",
            $f,
            empty($data[$f]) ? "" : $data[$f],
            $args + array("hint" => "Format:  xxx-xx-xxxx") + ($readonly ? ["readonly" => "readonly"] : [])
        );
		
		
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

        form::html('<div class="fld form-group">
        <label class="control-label col-sm-4 col-md-3"></label>
        <div class="col-sm-8 col-md-6 col-lg-6"><div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
        <small>This information will appear on the internal employee directory and can be viewed by all other employees</small></div></div></div>');
		/************
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
       ***********/
        form::inpField(
            $lbls[($f = $pg["pfx"] . "Email")] . ":",
            $f,
            empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
            $args + ($readonly ? ["readonly" => "readonly"] : [])
        );
		
		


        if (!$isUser) {
            $args1 = $args;
            $args1["wrap-class"] .= " checklist-top-margin";
            // $skills = SkillModel::AllActiveKeyName();
            $skills = SkillModel::RequiredSkillsForPosition($position);
            $selected = [];
            $f = $pg["pfx"] . "Skills";
            if (!empty($data[$f])) {
                $userSkills = explode(",", $data[$f]);
                foreach ($skills as $id => $skill) {
                    if (in_array($skill, $userSkills)) {
                        $selected[] = $id;
                    }
                }
            }
            if(!empty($skills)) {
                form::inpCheckList(
                    $lbls[$f] . ":",
                    $f,
                    $skills,
                    $selected,
                    $args1 + array("type" => "checkbox")
                );
    
            }

            if ($pID > 0) {
                $min = $data["position"]["pSalaryFrom"];
                $max = $data["position"]["pSalaryTo"];
                $avg = round(($min + $max) / 2);
                $salary = empty($data[$f]) ? $avg : round($data[$f]);
                if (($salary < $min) || ($salary > $max)) {
                    $salary = $avg;
                }

                form::inpField(
                    $lbls[($f = $pg["pfx"] . "DesiredSalary")] . ":",
                    $f,
                    $salary,
                    $args + array("type" => "range", "min" => $min, "max" => $max, "step" => "1", "oninput" => "showVal(this.value)", "onchange" => "showVal(this.value)")
                );

                form::html('<div class="fld form-group" style="margin-top: -10px;"><label class="control-label col-sm-4 col-md-3"></label><div class="col-sm-8 col-md-6 col-lg-4">
                    <div><label id="desiredSalary">' . PositionModel::formatSalary($avg) . '</label> <label>' . $data["position"]["pSalaryType"] . '</label></div></div></div>');

            }


            form::inpField(
                "Upload Resume:" . ASTERISK,
                $pg["pfx"] . "Bio",
                empty($data[$f]) ? "" : htmlspecialchars($data[$f]),
                $args + array("type" => "file")
            );

            form::html('<div class="fld form-group">
                    <label class="control-label col-sm-4 col-md-3"></label>
                    <div class="col-sm-8 col-md-6 col-lg-6">
                    <div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
                    <small>Please upload only .doc, .docx or .pdf</small></div></div></div>');

            // form::inpImg(
            //     "Photo:",
            //     "photo",
            //     htmlspecialchars(""),
            //     $args
            // );

            // form::html('<div class="fld form-group">
            //         <label class="control-label col-sm-4 col-md-3"></label>
            //         <div class="col-sm-8 col-md-6 col-lg-6"><div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
            //         <small>Please upload only .jpg or .png file</small></div></div></div>');
			
		

        }

        form::inpSaveCancel(WP, self::$saveCancelArgsBase, "Send Application");

        form::closeDiv();

        form::openDiv(["id" => "confirm-panel", "style" => "display:none;"]);
        form::html('
                    <div class="alert alert-danger">
                        There\'s already an employee with the same first and last name. If you\'re already an employee, please login and then apply again.
                    </div>');
        form::inpSaveCancel(WP, array("fld-class" => "", "wrap-class" => "", "class" => "btn btn-primary", "class-cancel" => "btn btn-default"), "I'm not an employee, proceed with application >>", "I'm an employee, log me in >>");
        form::closeDiv();

        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));

        form::html('</div></div>');


    }


}
