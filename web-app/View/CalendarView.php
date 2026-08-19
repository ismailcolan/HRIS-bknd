<?php

/** The CompanyOffice view */
class CalendarView extends AppView
{

    /** Shows the controller list table - allowing the type filtering
     * @param array $pg - controller::$pg
     * @param string $query - model::query($cond)
     * @param array $lbls - model::$fldLbl
     * @param array $rq - controller::$rq
     */
    final static function All($pg, $query, $lbls, $rq)
    {
        if (!isset(table::$export)) {
            table::$export = false;
        }
        table::create($pg["tbl"], "", "", self::ADMIN_LISTS_ROWS_LIMIT);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Headline")], $c);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Description")], $c);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Date")], $c);
        table::$cols[] = Array("", $pg["pfx"] . "Active", array("class" => "link", "sort" => false));

        table::execute($query);
        list($view, $edit, $delete) = self::AllLnksCode($pg, array("view", "edit", "delete"));

        foreach (table::$data as $r => &$cells) {
            if (table::$export === false) {
                $cells[$pg["pfx"] . "Headline"] = form::linkOut($pg["details"]["url"] . "?id=" . $cells[$pg["key"]], htmlspecialchars($cells[$pg["pfx"] . "Headline"]));
            }
            $cells[$pg["pfx"] . "Description"] = strip_tags($cells[$pg["pfx"] . "Description"]);
          //  $cells[$pg["pfx"] . "Description"] = htmlspecialchars($cells[$pg["pfx"] . "Description"]);
            $cells[$pg["pfx"] . "Date"] = val::convDate(MYSQL_DATE,USER_DATE,$cells[$pg["pfx"] . "Date"]);
			   //date(USER_DATE, strtotime($cells[$pg["pfx"] . "Date"]));<<<

            if (table::$export === false) {
                $cells["lnks"] = sprintf($view, $cells[$pg["key"]]);
                $cells[$pg["pfx"] . "Active"] = sprintf($delete, $cells[$pg["key"]]);                
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
        $flds4panel[$lbls[($c = $pg["pfx"] . "Headline")]] = $dt[$c];
        self::DataPanel($flds4panel, $pg["item"] . " summary", "panel-default");
    }

    /** Shows a controller details view
     * @param type $data - controller::$data
     * @param array $pg - controller::$pg
     * @param type $lbls - model::$fldLbl
     * @param type $list - lists generated in the model
     */
    final static function Details($data, $pg, $lbls, $list)
    {
        $args = self::$fldArgsBase;
		
		echo "<div class=\"alert alert-info\" style=\"text-align:center\"> 
					You don't need to enter non-working days or blockout dates here. Set them only in Settings menu.
		They will be automatically displayed on the Calendar page for the employees.
		 </div>";
		  form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "validate" => true, "class" => "form-horizontal"));
        
        form::inpField(
            $lbls[($f = $pg["pfx"] . "Headline")] . ":",
            $f,
            $data[$f],
            $args
        );
        
        form::inpField(
            $lbls[($f = $pg["pfx"] . "Description")] . ":",
            $f,
            $data[$f],
            $args + ["type" => "textarea", "rows" => "5", "id"=>"description"]
        );

        form::html('<script>CKEDITOR.replace("description");</script>');

        form::inpField(
            $lbls[($f = $pg["pfx"] . "Date")] . ":",
            $f,
            $data[$f],
            $args + array("hint" => "example:".USER_DATE_EXAMPLE)
        );
        
        // $args = self::$saveCancelArgsBase;
        // form::inpSaveCancel(CalendarController::getBackUrl()["url"], $args);


        $btnsHTML = '
            <div class="fld form-group">
                <div class="col-sm-8 col-md-7 col-lg-5 col-sm-offset-3 col-md-offset-2">
                    <button type="submit" name="save-btn" value="Save" class="btn btn-primary">Save</button> 
                    <button type="button" onclick="location.assign(\'calendar\')" class="btn btn-default">Cancel</button>'.
                  ((intval($data['calID']) > 0) ? '<button type="button" onclick="if(confirm(\'Do you really want to delete this event and connected data?\')) { location.assign(\''.ADMIN.'calendar-details?id='.$data["calID"].'&cmd=trash\'); } " class="btn btn-danger right">Delete Event</button>' : '')
                .'</div>
            </div>';
        form::html($btnsHTML);

        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }

    
    public static function Calendar($data) {
        form::openDiv(["class" => "content bg-white"]);
        form::openDiv(["class" => "row"]);
        form::openDiv(["class" => "col-sm-6"]);
        
		if(empty($data)) {
		form::html('<div style="margin-top: 20px;"><strong>No events</strong></div>');
		}
		else {
			form::openDiv(["class"=>"_accordion", "id" => "accordion","style"=>"background-color:#FFF;padding-top:10px"]);
            foreach($data as $item) {
                form::openDiv(["class"=>"card","style"=>"padding-top:0"]);
    
                form::openDiv(["class"=>"card-header", "style" => "height: 60px;",  "id" => "heading".$item[CalendarModel::$dt["key"]]]);
                form::openTag("h5", ["class" => "mb-0"]);
    
                form::html('<a class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse'.$item[CalendarModel::$dt["key"]].'" aria-expanded="true" aria-controls="collapseOne">
                    <i class="fa fa-calendar"></i>&nbsp;&nbsp;'.htmlspecialchars($item[CalendarModel::$dt["pfx"] . "Headline"]).'</a><br/>'.
                    "<span style='margin-left: 35px;'>"
					   .val::convDate(MYSQL_DATE,USER_DATE,$item[CalendarModel::$dt["pfx"] . "Date"])
					  //.date(USER_DATE, strtotime($item[CalendarModel::$dt["pfx"] . "Date"])) <<<
					  ."</span>");
                form::closeTag("h5", ["class" => "mb-0"]);
                form::closeDiv(["class"=>"card-header",  "id" => "heading".$item[CalendarModel::$dt["key"]]]);
            
                form::html('
                <div id="collapse'.$item[CalendarModel::$dt["key"]].'" class="collapse in" aria-labelledby="heading'.$item[CalendarModel::$dt["key"]].'" data-parent="#accordion">
                    <div class="card-body">'.$item[CalendarModel::$dt["pfx"] . "Description"].'</div>
                </div>');
    
                form::closeDiv(["class"=>"card"]);
            } 
			form::closeDiv(["class"=>"_accordion", "id" => "accordion"]);
        }

        
		
		 form::closeDiv(["class" => "col-sm-6"]);
		 
		 
		 form::openDiv(["class" => "col-sm-6","style"=>"padding:10px;padding-top:15px"]);
		 $boDaysStr=DaysOffFrontModel::getBODatesAsJS(AppModel::$usr['afID'],date('Y'));
		$boDaysNextStr=DaysOffFrontModel::getBODatesAsJS(AppModel::$usr['afID'],(date('Y')+1));
		
		$doDaysStr=DaysOffFrontModel::getDaysOffDatesAsJS(AppModel::$usr['afID'],date('Y'));
		$doDaysNextStr=DaysOffFrontModel::getDaysOffDatesAsJS(AppModel::$usr['afID'],(date('Y')+1));
		
		$vacDaysStr=DaysOffFrontModel::getVacDatesAsJSFuture(AppModel::$usr['afID'],date('Y'));
		$vacDaysNextStr=DaysOffFrontModel::getVacDatesAsJSFuture(AppModel::$usr['afID'],date('Y')+1);
		
		 echo '<div style="width:100%;text-align:justify">';
		     $year=date('Y');
		   
		 echo '<h4>'.$year.'</h4>';
		    echo '<b>US holidays: </b>';
			echo '<span >'.
			DaysOffFrontView::listHolidays(cf::getFederalHolydays($year))
			.'</span>'; 
		   echo '<br><b>Block-out dates:</b>';
		   //$z=DaysOffFrontModel::getBlockOutDates(AppModel::$usr['afID'],($year+1));cf::pr($z);
		   
			if ($year==date('Y')) 
				echo '<span >'.DaysOffFrontView::listDays(DaysOffFrontModel::getBlockOutDates(AppModel::$usr['afID'],$year),'bod').'</span>'; 
			else 
				echo '<span >'.DaysOffFrontView::listDays(DaysOffFrontModel::getBlockOutDates(AppModel::$usr['afID'],$year+1),'bod').'</span>';
			
			echo '<br><b>Non-working days: </b>';
			if ($year==date('Y')) echo 
			'<span >'.DaysOffFrontView::listDays(DaysOffFrontModel::getDaysOffDates(AppModel::$usr['afID'],$year),'nwd').'</span>'; 
			else 
			echo '<span >'.DaysOffFrontView::listDays(DaysOffFrontModel::getDaysOffDates(AppModel::$usr['afID'],$year+1),'nwd').'</span>';
		   echo '<br><b>Approved days off: </b>';
		   if ($year==date('Y')) echo 
			'<span >'.DaysOffFrontView::listDays(DaysOffFrontModel::getVacDatesFuture(AppModel::$usr['afID'],$year),'vr').'</span>'; 
			else 
			echo '<span >'.DaysOffFrontView::listDays(DaysOffFrontModel::getVacDatesFuture(AppModel::$usr['afID'],$year),'vr').'</span>';
			
		
		 $year=date('Y')+1;
		
		 echo '<br><br><h4>'.$year.'</h4>';
		   echo '<b>US holidays: </b>';
			echo '<span >'.
			DaysOffFrontView::listHolidays(cf::getFederalHolydays($year))
			.'</span>'; 
		   echo '<br><b>Block-out dates:</b>';
			//if ($year==date('Y')) echo '<span >'.DaysOffFrontView::listDays(DaysOffFrontModel::getBlockOutDates(AppModel::$usr['afID'],$year),'bod').'</span>'; 
			//else 
			echo '<span >'.DaysOffFrontView::listDays(DaysOffFrontModel::getBlockOutDates(AppModel::$usr['afID'],$year),'bod').'</span>';
			echo '<br><b>Non-working days: </b>';
			//if ($year==date('Y')) echo 
			//'<span >'.DaysOffFrontView::listDays(DaysOffFrontModel::getDaysOffDates(AppModel::$usr['afID'],$year),'nwd').'</span>'; 
			//else 
			echo '<span >'.DaysOffFrontView::listDays(DaysOffFrontModel::getDaysOffDates(AppModel::$usr['afID'],$year),'nwd').'</span>';
		   echo '<br><b>Approved days off: </b>';
		   if ($year==date('Y')) echo 
			'<span >'.DaysOffFrontView::listDays(DaysOffFrontModel::getVacDatesFuture(AppModel::$usr['afID'],$year),'vr').'</span>'; 
			else 
			echo '<span >'.DaysOffFrontView::listDays(DaysOffFrontModel::getVacDatesFuture(AppModel::$usr['afID'],$year),'vr').'</span>';;
			
			echo '</div>';
			
			
			
		 form::closeDiv(["class" => "col-sm-6"]);
        
		 form::closeDiv(["class" => "row"]);
		 

		 
        form::closeDiv(["class" => "row"]);
        //form::closeDiv(["class" => "col-sm-12"]);
        form::closeDiv(["class" => "row"]);
        form::closeDiv(["class" => "content bg-white"]);

        form::html('
            <script>
                var url = location.href;
                var idx = url.indexOf("#");
                if(idx > -1) {                    
                    var hash = url.substring(url.indexOf("#")+1);                
                    jQuery("#"+hash).collapse("show");
                }
            </script>
        ');
    }

}

