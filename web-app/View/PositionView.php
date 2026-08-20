<?php

/** The Vendor view */
class PositionView extends AppView
{

    /** Shows the controller list table - allowing the type filtering
     * @param array $pg - controller::$pg
     * @param string $query - model::query($cond)
     * @param array $lbls - model::$fldLbl
     */
    final static function All($pg, $query, $lbls, $rq)
    {
		 
        table::create($pg["tbl"], 'pName'/*$pg["key"]/*"coAdded"*/, "ASC", self::ADMIN_LISTS_ROWS_LIMIT);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Name")], $c);
        table::$cols[] = Array("Salary", "Salary", ["sort" => false]);
        if ($rq === "list") {
            table::$cols[] = Array("", $pg["key"] . "deactivate", array("class" => "link", "sort" => false));
        }
        if ($rq === "inactive-list") {
            table::$cols[] = Array("", $pg["key"] . "reactivate", array("class" => "link", "sort" => false));
        }


        table::execute($query);
        list($view, $edit, $reactivate, $deactivate, $delete) = self::AllLnksCode($pg, array("view", "edit", "reactivate", "deactivate", "delete"));
        foreach (table::$data as $r => &$cells) {
            if (table::$export === false) {
                $name = htmlspecialchars($cells[$pg["pfx"] . "Name"]);
                $cells[$pg["pfx"] . "Name"] = '<a href="/admin/position-details?id=' . $cells[$pg["key"]] . '" title="Edit ' . $name . '">' . $name . '</a>';

                if ($cells["pCanRemove"] === "Yes") {
                    $cells["lnks"] = sprintf($view, $cells[$pg["key"]]);
                    $cells[$pg["key"] . "deactivate"] = sprintf($deactivate, $cells[$pg["key"]]);
                    $cells[$pg["key"] . "reactivate"] = sprintf($reactivate, $cells[$pg["key"]]);
                }
            }
            $cells[$pg["pfx"] . "SalaryFrom"] = PositionModel::formatSalary($cells[$pg["pfx"] . "SalaryFrom"]);
            $cells[$pg["pfx"] . "SalaryTo"] = PositionModel::formatSalary($cells[$pg["pfx"] . "SalaryTo"]);
            $cells["Salary"] = $cells[$pg["pfx"] . "SalaryFrom"] . ' - ' . $cells[$pg["pfx"] . "SalaryTo"] . ' ' . strtolower($cells[$pg["pfx"] . "SalaryType"]);
        }
        table::load();
    }


    /** Shows a controller details view
     * @param type $data <- controller::$data
     * @param type $lbls <- model::$fldLbl
     * @param type $lists <- lists generated in the model
     */
    final static function Details($data, $pg, $lbls, $list)
    {
        $args = self::$fldArgsBase;
        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "validate" => true, "class" => "form-horizontal"));

		
        form::inpField(
            $lbls[($f = $pg["pfx"] . "Name")] . ":",
            $f,
            $data[$f],
            $args + array("data" => $data[$f]) 
        );
		
		 form::html('<div class="fld form-group">
                    <label class="control-label col-sm-4 col-md-3"></label>
                    <div class="col-sm-8 col-md-7 col-lg-5">
                    <div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
                    Important: Editing a position name will change all existing employees and applicants.</div></div></div>');
					
		
		 if ($data['pPhisician'] == 'yes')  $_ch='checked'; else $_ch='';
		 
		 form::html('<div class="fld form-group">
                    <label class="control-label col-sm-4 col-md-3">Require physician fields:</label>
                    <div class="col-sm-8 col-md-7 col-lg-5" style="padding-top:6px">
                    <input type="hidden" name="pPhisician" value="no">
					   <input type="checkbox" value="yes" id="pPhisician" name="pPhisician" '.$_ch.'>
					    </div></div>');

        $f = $pg["pfx"] . "Skills";
        if (is_array($data[$f])) $data[$f] = implode('|',$data[$f]);
        form::inpCheckList(
            $lbls[$f] . ":",
            $f,
            SkillModel::AllActiveKeyName(),
            explode("|", $data[$f]),
            $args + array("check-lbl-class" => "check-lbl small")
        );
		
		
		  form::inpSelect(
            $lbls[($f = $pg["pfx"] . "WelcomeEmail")] . ":",
            $f,
			  WelcomeEmailController::getWelcomeEmailsTemplate(),
			  empty($data[$f]) ? "" :$data[$f],
            $args 
        );

        form::openDiv();
        form::openDiv(array("class" => "fld form-group"));
        form::tag("Salary:" . ASTERISK, "label", ["class" => "control-label  col-sm-4 col-md-3"]);

        form::openDiv(["class" => "col-sm-8 col-md-7 col-lg-5"]);

       
		 $f = $pg["pfx"] . "SalaryType";
		  if (!isset($data[$f])) {$_ck1='checked'; $_ck2='';}
		  else {
			if ($data[$f]=='Hourly') {$_ck1='checked'; $_ck2='';}
			if ($data[$f]=='Annual') {$_ck1=''; $_ck2='checked';}
		  }
		 
		 echo '<div class = "row">
				&nbsp;&nbsp;
				<input style="cursor:pointer" type="radio" name="'.$f.'" '.$_ck1.' value="Hourly" id="hourly"> 
				<label for="hourly" style="font-weight:normal;cursor:pointer">Hourly</label>
				&nbsp;&nbsp;
				<input style="cursor:pointer" type="radio" name="'.$f.'" '.$_ck2.' value="Annual" id="annual"> 
				<label for="annual" style="font-weight:normal;cursor:pointer">Annual</label>
				</div>';
					
        if (isset(form::$error["pSalaryTypeErr"])) {
            form::html('<span class="err">' . form::$error["pSalaryTypeErr"][0] . '</span>');
        }

        form::openDiv(["class" => "row"]);
        form::openDiv(["class" => "col-sm-3 col-md-3 col-lg-3"]);
        form::tag("From:<span title='Required field' style='color: red;'>*</span>", "span");
		
		 $f = $pg["pfx"] . "SalaryFrom";
		 if (floatval($data[$f])>=0)  $data[$f]= number_format($data[$f], 2, ".", ",");
        
        form::inpField(
            "",
            $f,
            $data[$f] === 0 ? "0.0" : $data[$f],
            ["class" => "form-control"]
        );
        form::closeDiv(["class" => "col-sm-3 col-md-3 col-lg-3"]);
		
        form::openDiv(["class" => "col-sm-3 col-md-3 col-lg-3"]);
        form::tag("To:<span title='Required field' style='color: red;'>*</span>", "span");
        $f = $pg["pfx"] . "SalaryTo";
		 if (floatval(@$data[$f])>=0)  $data[$f]= number_format(@$data[$f], 2, ".", ",");
        form::inpField(
            "",
            $f,
            $data[$f] === 0 ? "0.0" : $data[$f],
            ["class" => "form-control"]
        );
        form::closeDiv(["class" => "col-sm-3 col-md-3 col-lg-3"]);
        form::closeDiv(["class" => "row"]);

        form::closeDiv(["class" => "col-sm-8 col-md-7 col-lg-5"]);
        form::closeDiv(array("class" => "fld form-group"));
		
		form::inpField(
            $lbls[($f = $pg["pfx"] . "VacationDays")] . ":",
            $f,
            @$data[$f] == 0 ? 0 : @$data[$f],
            $args +['style'=>'width:5em','value'=>@$data[$f] == 0 ? 0 : @$data[$f]]
        );
		
		form::inpField(
            $lbls[($f = $pg["pfx"] . "CEDays")] . ":",
            $f,
            @$data[$f] == 0 ? 0 : @$data[$f],
            $args +['style'=>'width:5em','value'=>@$data[$f] == 0 ? 0 : @$data[$f]]
        );
		
        form::closeDiv();

        if (PositionController::$id) {
            form::html('<div class="fld form-group">
                    <label class="control-label col-sm-4 col-md-3"></label>
                    <div class="col-sm-8 col-md-7 col-lg-5">
                    <div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
                    Note: Changing paid days affects only employees hired from now on.</div></div></div>');
					
            $args = self::$saveCancelArgsBase + array("id" => "savePositionBtn");
        } else {
            $args = self::$saveCancelArgsBase;
        }
		
		 $all_offices=PositionModel::getActiveOffices();
		 if (isset($data['pMinEmplPerPosArr'])) $off_values=json_decode($data['pMinEmplPerPosArr'],true);
	    else $off_values=array();

        form::html('<div class="fld form-group">
                    <label class="control-label col-sm-4 col-md-3">Minimum employees per office:</label>
                    <div class="col-sm-8 col-md-7 col-lg-5">');
		  foreach($all_offices as $k=>$off){
			$id=$off['coID'];
			if(isset($off_values[$id])) $val=$off_values[$id];
			else $val= MIN_EMP_PER_POSITION;
			echo '<span style="width:50%;display:inline-block">
			<input type="text" name="min_emp['.$id.']"  style="width:50px;" value="'.$val.'">
			<label 
			  style="position:relative;top:0.8em;font-weight:normal;display:inline-block;width:200px;white-space: nowrap; 
				overflow: hidden;text-overflow: ellipsis;" title="'.$off['coName'].'">'.$off['coName'].'</label>
			</span>';
		  }
		 echo '</div></div>';
		 
		 
		 form::html('<br><div class="fld form-group">
                    <label class="control-label col-sm-4 col-md-3">Vacation/CE notice:</label>
                    <div class="col-sm-8 col-md-7 col-lg-5">');
		  
			echo '<select style="width:5em;display:inline-block"  class="form-control" name="TwoWeekNoticeNmb">';
			if (isset($data['TwoWeekNoticeNmb'])) $sel=$data['TwoWeekNoticeNmb'];
			else $sel=2;
			for($i=1;$i<=30;$i++) {
				if ($i==$sel) $selStr='selected'; else $selStr='';
				echo '<option '.$selStr.' value="'.$i.'">'.$i.'</option>';
			}
			echo '</select> ';
			
			echo '<select style="width:10em;display:inline-block"  class="form-control" name="TwoWeekNoticeDim">';
			if (isset($data['TwoWeekNoticeDim'])) $sel=$data['TwoWeekNoticeDim'];
			else $sel='weeks';
			foreach(array('days','weeks') as $k=>$d) {
				if ($d==$sel) $selStr='selected'; else $selStr='';
				echo '<option '.$selStr.' value="'.$d.'">'.$d.'</option>';
			}
			echo '</select>';
		  
		 echo '</div></div><br>';

        // =====================================================
        // INTERVIEW QUESTIONS SECTION
        // Matches existing form-horizontal layout:
        //   - label: col-sm-4 col-md-3  (right-aligned)
        //   - content: col-sm-8 col-md-7 col-lg-5
        // Each round rendered as a labelled row, matching the
        // style visible in the screenshots (border card, textarea
        // rows, red × remove button, green + Add Question button).
        // =====================================================
        $existingQuestions = array(1 => array(), 2 => array(), 3 => array());
        if (PositionController::$id) {
            $existingQuestions = InterviewQuestionModel::getByPosition(PositionController::$id);
        }

        echo '<hr>';

        // Section heading — aligned to the content column, matching
        // how "Home Address" heading is done elsewhere in the project.
        echo '<h4 style="margin-left:0;padding-left:0;margin-bottom:15px;">
                  <b>Interview Questions</b>
                  <small style="font-size:0.7em;color:#888;">(optional)</small>
              </h4>';

        foreach (array(1, 2, 3) as $round) {

            $questions = $existingQuestions[$round];

            // Outer Bootstrap form-group row — keeps label/content alignment
            echo '<div class="fld form-group">';

            // Label column — right-aligned label, same as all other fields
            echo '<label class="control-label col-sm-4 col-md-3">
                      <strong>Round ' . $round . ' Questions:</strong>
                  </label>';

            // Content column — same width as every other field in the form
            echo '<div class="col-sm-8 col-md-7 col-lg-5">';

            // Round card — border, background, rounded corners matching screenshot
            echo '<div style="padding:12px;border:1px solid #ddd;border-radius:4px;
                              background:#fafafa;margin-bottom:4px;">';

            // Questions container
            echo '<div id="iq_round_' . $round . '_container">';

            if (!empty($questions)) {
                foreach ($questions as $q) {
                    $escapedQ = htmlspecialchars($q['iqQuestion']);
                    $iqID     = (int)$q['iqID'];
                    echo '
                    <div class="iq-row" style="display:flex;align-items:flex-start;
                                               margin-bottom:8px;gap:6px;">
                        <input type="hidden"
                               name="iq_ids[' . $round . '][]"
                               value="' . $iqID . '">
                        <textarea name="iq_questions[' . $round . '][]"
                                  rows="2"
                                  class="form-control"
                                  style="flex:1;resize:vertical;font-size:14px; font-weight:bold;"
                                  placeholder="Enter question...">' . $escapedQ . '</textarea>
                        <button type="button"
                                onclick="iqRemoveRow(this)"
                                class="btn btn-danger btn-sm"
                                style="flex-shrink:0;margin-top:2px;"
                                title="Remove">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>';
                }
            } else {
                // One empty starter row
                echo '
                <div class="iq-row" style="display:flex;align-items:flex-start;
                                           margin-bottom:8px;gap:6px;">
                    <input type="hidden"
                           name="iq_ids[' . $round . '][]"
                           value="0">
                    <textarea name="iq_questions[' . $round . '][]"
                              rows="2"
                              class="form-control"
                              style="flex:1;resize:vertical;font-size:13px;"
                              placeholder="Enter question..."></textarea>
                    <button type="button"
                            onclick="iqRemoveRow(this)"
                            class="btn btn-danger btn-sm"
                            style="flex-shrink:0;margin-top:2px;"
                            title="Remove">
                        <i class="fa fa-times"></i>
                    </button>
                </div>';
            }

            echo '</div>'; // end #iq_round_N_container

            // "+ Add Question" button — green, matching screenshot
            echo '<button type="button"
                          onclick="iqAddRow(' . $round . ')"
                          class="btn btn-success btn-sm"
                          style="margin-top:4px;">
                      <i class="fa fa-plus"></i> Add Question
                  </button>';

            echo '</div>'; // end round card div
            echo '</div>'; // end content column
            echo '</div>'; // end .fld.form-group
        }

        // JS helpers — identical logic to original, no changes needed
        echo '
        <script>
        function iqAddRow(round) {
            var container = document.getElementById("iq_round_" + round + "_container");
            var div = document.createElement("div");
            div.className = "iq-row";
            div.style.cssText = "display:flex;align-items:flex-start;margin-bottom:8px;gap:6px;";
            div.innerHTML =
                \'<input type="hidden" name="iq_ids[\' + round + \'][]" value="0">\' +
                \'<textarea name="iq_questions[\' + round + \'][]" rows="2" \' +
                    \'class="form-control" \' +
                    \'style="flex:1;resize:vertical;font-size:13px;" \' +
                    \'placeholder="Enter question..."></textarea>\' +
                \'<button type="button" onclick="iqRemoveRow(this)" \' +
                    \'class="btn btn-danger btn-sm" \' +
                    \'style="flex-shrink:0;margin-top:2px;" \' +
                    \'title="Remove"><i class="fa fa-times"></i></button>\';
            container.appendChild(div);
            div.querySelector("textarea").focus();
        }

        function iqRemoveRow(btn) {
            var row       = btn.parentNode;
            var container = row.parentNode;
            // Keep at least one empty row
            if (container.querySelectorAll(".iq-row").length <= 1) {
                row.querySelector("textarea").value           = "";
                row.querySelector("input[type=hidden]").value = "0";
                return;
            }
            container.removeChild(row);
        }
        </script>';
        // =====================================================
        // END INTERVIEW QUESTIONS SECTION
        // =====================================================

        form::inpSaveCancel(PositionController::getBackUrl()["url"], $args);

        form::closeTag("form");
        form::codeStyle(true, 1, true);

        form::closeTag("div", array("class" => "form-panel"));
    }

    final static function Summary($dt, $pg, $lbls)
    {
        msg::get();
        $salary = PositionModel::formatSalary($dt[$pg["pfx"] . "SalaryFrom"]) . ' - ' . PositionModel::formatSalary($dt[$pg["pfx"] . "SalaryTo"]) . ' ' . strtolower($dt[$pg["pfx"] . "SalaryType"]);

        $flds4panel = array();
        $flds4panel[$lbls[($c = $pg["pfx"] . "Name")]] = $dt[$c];

        $lbl = $lbls[($c = $pg["pfx"] . "Skills")];
        $skills = PositionModel::requiredSkillsAsString($dt[$c]);
        $flds4panel[$lbl] = empty($skills) ? "none" : $skills;
        $flds4panel["Salary"] = $salary;
		 $temps=WelcomeEmailController::getWelcomeEmailsTemplate();
		 if ($dt['pWelcomeEmail'] == 0 ) $flds4panel["WelcomeEmail"] = 'Not selected template';
		 else $flds4panel["WelcomeEmail"] = $temps[$dt['pWelcomeEmail']];
        self::DataPanel($flds4panel, $pg["item"] . " summary", "panel-default");
    }

}