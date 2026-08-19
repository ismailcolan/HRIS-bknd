<?php
/** The CompanyOffice view */
class JobView extends AppView {
    
    /** Shows the controller list table - allowing the type filtering
     * @param array $pg - controller::$pg 
     * @param string $query - model::query($cond)
     * @param array $lbls - model::$fldLbl */
    final static function All($pg, $query, $lbls, $rq){
        table::create($pg["tbl"], $pg["key"], "desc", self::ADMIN_LISTS_ROWS_LIMIT);
        table::$cols[] = Array($lbls[($c = $pg["pfx"]."Name")], $c);
        table::$cols[] = Array($lbls[($c = $pg["pfx"]."Position")], $c);
        table::$cols[] = Array($lbls[($c = $pg["pfx"]."Description")], $c);
        table::$cols[] = Array($lbls[($c = $pg["pfx"]."EndDate")], $c);


            //table::$cols[] = Array("", $pg["key"]."edit", array("class"=>"link","sort"=>false));
            table::$cols[] = Array("", $pg["pfx"]."active", array("class"=>"link","sort"=>false));

            table::execute($query);
            if($rq === "list"){
                list($view, $edit, $deactivate) = self::AllLnksCode($pg, array("view","edit","deactivate"));
            } else {
                list($view, $edit, $reactivate) = self::AllLnksCode($pg, array("view","edit","reactivate"));
            }

            foreach(table::$data as $r => &$cells){
                if(table::$export === false){
                    $cells["lnks"] = sprintf($view, $cells[$pg["key"]]);
                    if($rq === "list") {
                        $cells[$pg["pfx"] . "active"] = sprintf($deactivate, $cells[$pg["key"] . "edit"]);
                    } else {
                        $cells[$pg["pfx"] . "active"] = sprintf($reactivate, $cells[$pg["key"] . "edit"]);
                    }

                    $cells[$pg["key"]."edit"] = sprintf($edit, $cells[$pg["key"]."edit"]);
                    $cells[$pg["pfx"]."Name"] = form::linkOut($pg["details"]["url"]."?id=".$cells[$pg["key"]], htmlspecialchars($cells[$pg["pfx"]."Name"]));
                    $cells[$pg["pfx"]."Position"] = htmlspecialchars($cells[$pg["pfx"]."Position"]);
//                    $cells[$pg["pfx"]."Description"] = htmlspecialchars($cells[$pg["pfx"]."Description"]);
                    $cells[$pg["pfx"]."EndDate"] = $cells[$pg["pfx"]."EndDate"] != "0000-00-00" ?  
							val::convDate(MYSQL_DATE,USER_DATE,$cells[$pg["pfx"]."EndDate"]): "";
					      // date(USER_DATE, strtotime($cells[$pg["pfx"]."EndDate"])) : "";<<<

                    if(!empty($cells[$pg["pfx"]."EndDate"])) {
                        $endDate = DateTime::createFromFormat(USER_DATE, $cells[$pg["pfx"]."EndDate"]);
                        if($endDate < new DateTime()) {
                            $cells[$pg["pfx"]."Name"] = form::tagOut($cells[$pg["pfx"]."Name"], "span", ["class"=>"red-bold"]);
                            $cells[$pg["pfx"]."Position"] = form::tagOut($cells[$pg["pfx"]."Position"], "span", ["class"=>"red-bold"]);
                            $cells[$pg["pfx"]."Description"] = form::tagOut($cells[$pg["pfx"]."Description"], "span", ["class"=>"red-bold"]);
                            $cells[$pg["pfx"]."EndDate"] = form::tagOut($cells[$pg["pfx"]."EndDate"], "span", ["class"=>"red-bold"]);
                            $cells[$pg["pfx"]."active"] = form::tagOut($cells[$pg["pfx"]."active"], "span", ["class"=>"red-bold"]);
                        }
                    }

                }
            }
        table::load();
    }

    final static function CheckIsExpired($date, $putMsg = true) {
        if(!empty($date) && ($date != "0000-00-00")) {
            $date = DateTime::createFromFormat(USER_DATE, $date);
            if($date < new DateTime()) {
                if($putMsg) {
                    msg::put("Expired - please change end date or disable", "error");
                }
                return true;
            }
        }
        return false;
    }


    /**Show an model item summary page
     * @param type $dt - controller::$data
     * @param type $pg - controller::$pg 
     * @param type $lbls - model::$fldLbl */
    final static function Summary($dt, $pg, $lbls){
        $extLnk = " <i class=\"fa fa-external-link\" aria-hidden=\"true\"></i>";
        $position = PositionModel::dataById($dt[$pg["pfx"]."PID"])[PositionModel::$dt["pfx"]."Name"];

        $flds4panel = array();
        $flds4panel[$lbls[($c = $pg["pfx"]."Name")]] = $dt[$c];
        $flds4panel[$lbls[($c = $pg["pfx"]."PID")]] = form::linkOut(ADMIN."position-details?id=".$dt[$c], $position." <i class=\"fa fa-pencil-square-o\" aria-hidden=\"true\"></i>");
        $flds4panel[$lbls[($c = $pg["pfx"]."EndDate")]] = $dt[$c]; self::CheckIsExpired($dt[$c]);
        $flds4panel[$lbls[($c = $pg["pfx"]."Description")]] = nl2br(htmlspecialchars($dt[$c]));

        self::DataPanel($flds4panel, $pg["item"]." summary", "panel-default");
    }

    /** Shows a controller details view
     * @param type $data - controller::$data
     * @param type $lbls - model::$fldLbl 
     * @param type $lists - lists generated in the model */
    final static function Details($data, $pg, $lbls, $list){

        self::CheckIsExpired($data["jEndDate"]);

        $args = self::$fldArgsBase;
        form::codeStyle(true, 1, false);
        form::openTag("div",array("class"=>"form-panel"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/));
            form::$ofsetIndentsCount = 2;
            form::openTag("form",array("method"=>"post","validate"=>true, "class"=>"form-horizontal"));
                form::inpSelect(
                    $lbls[($f = $pg["pfx"]."PID")].":",
                    $f,
                    $list["positions"],
                    isset($data[$f]) ? $data[$f] : null,
                    (array("id"=>"jobPositionSelect")+$args)
                );
                form::inpField(
                    $lbls[($f = $pg["pfx"]."Name")].":",
                    $f,
                    $data[$f],
                    (array("id"=>"jobTitleEdit")+$args)
                );
                form::inpField(
                    $lbls[($f = $pg["pfx"]."EndDate")].":",
                    $f,
                    $data[$f],
                    $args
                );
                form::inpField(
                    $lbls[($f = $pg["pfx"]."Description")].":",
                    $f,
                    $data[$f],
                    (array("type"=>"textarea", "rows"=>"5")+$args)
                );
                form::inpSelect(
                    $lbls[($f = $pg["pfx"]."Active")].":",
                    $f,
                    $list["active"],
                    isset($data[$f]) ? $data[$f] : "Yes",
                    (array("skip-empty"=>true)+$args)
                );

                form::inpSaveCancel(ADMIN.self::DetailsCancelUrl($data, $pg) , self::$saveCancelArgsBase);
            form::closeTag("form");
            form::codeStyle(true, 1, true);
        form::closeTag("div",array("class"=>"form-panel"));
    }


    public static function FrontListAll($pg, $rq)
    {
		if(!isset($_SESSION['customerID']) || $_SESSION['customerID'] == 0 ) {
		
			echo '<br><div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
				The company you entered <b></b> is not registered on this site.
				No jobs to show.</div>';
		}
		else {
        form::openDiv(array('class'=>"content bg-white"));
        form::openDiv(array('class'=>"row"));

        form::openDiv(array("class"=>"col-sm-12"));
        // form::tag("Job openings", "h3");

        ApplyFormView::PositionsInOneRow();


        $jobs = JobModel::data('jActive="Yes"', ["jAdded" => "DESC"]);
        if(empty($jobs)) {
            form::html('<strong>There are currently no open positions</strong>');
        } else {
            form::openDiv(array('class'=>"row"));
            foreach ($jobs as $job) {
				/**********
                form::html('
<div class=" col-sm-6" >
                <div class="card">
                <div class="card-header">'.$job["jName"].'</div>
                <div class="card-body text-primary">
                  Position: <span class="card-title">'.htmlspecialchars($job["jPosition"]).'</span>
                  <div class="card-text">'.nl2br(htmlspecialchars($job["jDescription"])).'</div>
                  <div class="card-footer right"><a href="/application-form?jID='.$job["jID"].'&pID='.$job["jPID"].'">Apply >></a></div>
                </div>
              </div>
</div>');
*************/
		form::html('
					<div class=" col-sm-6" >
                <div class="card">
                <div class="card-header">'.$job["jPosition"].'</div>
                <div class="card-body text-primary">
                  <span class="card-title">'.htmlspecialchars($job["jName"]).'</span>
                  <div class="card-footer right"><a href="/job-details?jID='.$job["jID"].'&pID='.$job["jPID"].'">View Details >></a></div>
                 </div>
              </div>
</div>');



            }
            form::closeDiv(array('class'=>"row"));
        }
		}
	}	
		
		public static function JobDetails($job)
    {
        //form::openDiv(array('class'=>"content bg-white"));
        //form::openDiv(array('class'=>"row"));

        //form::openDiv(array("class"=>"col-sm-12"));
      
        if(empty($job)) {
            form::html('<strong>No such job position</strong>');
        } else {
            form::openDiv(array('class'=>"row1"));
            				
                form::html('
						<!--<div class=" col-sm-6" >-->
                <div class="card" style="height:auto">
                <div class="card-header card-header-new">'.$job["jName"].'</div>
                <div class="card-body1 text-primary1">
                  <br>
					<b>Position:</b> <span class="card-title"><b>'.htmlspecialchars($job["jPosition"]).'</b></span>
                  <br><br>
				    <div class="card-text44">'.nl2br(htmlspecialchars($job["jDescription"])).'</div>
                  <div class="card-footer-new">
				  <br>
				  <a class="btn btn-primary" href="/application-form?jID='.$job["jID"].'&pID='.$job["jPID"].'">Apply >></a>
				  <br><br>
				   </div>
                </div>
             <!-- </div> -->
				</div><br>');

		

            }
            form::closeDiv(array('class'=>"row"));
        }





     //   form::openDiv(array('class'=>"row"));
     //   form::closeDiv(array('class'=>"content bg-white"));
    //}


}
