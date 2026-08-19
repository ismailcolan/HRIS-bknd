<?php

class DaysOffFrontView extends AppView{
	
		public static function ViewDaysOffOfUser($go_to_year=null){ 
		
		include __DIR__ ."/../webapp-html/header-only.html";
		echo '<body style="background:#FFF">';
		
		$userID=$_GET['id'];
		$q="SELECT * FROM apply_forms WHERE afID={$userID}";
		$_r=db::prepareLoadSingleRow($q);
		
		$endYear=date('Y',strtotime($_r['afStartDate']));
		$data=array();
		$data['years']=array();
		
		for($i=date('Y')+1;$i>=$endYear;$i--){
			$data['years'][]= $i;
		}
		//cf::pr($data['years']);
		if(isset($_GET['go_to_year'])) $go_to_year=$_GET['go_to_year'];
		//AppModel::$usr['afStartDate'])
		msg::get();
		echo '<div class="content bg-white" style="text-align:center"><br>';
		echo '<a class="btn btn-success" href="my-employees?cmd=my-employees-list" style="float:left"> << Back to My Employees</a><br><br>';
		
		echo '<div class="from-to-date"><span style="color: #000;font-size:18px;font-weight:bold;">Employee: </span><span style="color: #327ab7;font-weight: 900;">'
		.$_r['afFirstName'].' '.$_r['afLastName'].'</span>
		</div><br><br>';
		
		echo '<div id="years_select" style="width:100%;margin:0 auto;text-align:center;
		font-size:20px;font-weight: bold;position:relative;top:-10px;">
		Year:
		<select id="year_select" title="Click here to change year"  onchange="yearChange()">';
		
		foreach($data['years'] as $k=>$year){
			//if ($year=='2018') continue;
			if ($year==date('Y')) $sel='selected '; else $sel='';
			if (!($year==date('Y') || $year==date('Y')+1)) $sel.='data-has-request="1"'; 
			else $sel.='data-has-request="0"';
			echo '<option '.$sel.' value="'.$year.'">'.$year.'</option>';
		}
		echo '</select>
		<br>
		
		</div>	';
		
		foreach($data['years'] as $k=>$year){
		$total1 =cf::getEmployeePaidDaysOff($year, $_r['afID']);
        //<<< DaysOffFrontModel::getDefaultDays($_r['afID'],$year,'Vacation');
		
		$used1 = DaysOffFrontModel::getUsedDays($_r['afID'],$year,['Vacation','Sick leave'],'paid');
		$remain1 = $total1-$used1;
		if ($remain1<0) $remain1=0;
		
		$total2 = DaysOffFrontModel::getDefaultDays($_r['afID'],$year,'CE');
		$used2 = DaysOffFrontModel::getUsedDays($_r['afID'],$year,['CE'],'paid');
		$remain2 = $total2-$used2;
		if ($remain2<0) $remain2=0;
	
		$used3 = DaysOffFrontModel::getUsedDays($_r['afID'],$year,[],'unpaid');
		
		$usedSL=DaysOffFrontModel::getUsedDays($_r['afID'],$year,['Sick leave'],'unpaid');
		$usedJD=DaysOffFrontModel::getUsedDays($_r['afID'],$year,['Jury duty'],'unpaid');
		$usedCE=DaysOffFrontModel::getUsedDays($_r['afID'],$year,['CE'],'unpaid');
		
		$unpaidStr=DaysOffFrontModel::getUnpaidDatesAsJS($_r['afID'],$year,['Unpaid']);
		$unpaidJDStr=DaysOffFrontModel::getUnpaidDatesAsJS($_r['afID'],$year,['Jury duty']);
		$unpaidCEStr=DaysOffFrontModel::getUnpaidDatesAsJS($_r['afID'],$year,['CE']);
		$unpaidSLStr=DaysOffFrontModel::getUnpaidDatesAsJS($_r['afID'],$year,['Sick leave']);
		
		$boDaysStr=DaysOffFrontModel::getBODatesAsJS($_r['afID'],date('Y'));
		$boDaysNextStr=DaysOffFrontModel::getBODatesAsJS($_r['afID'],(date('Y')+1));
		
		$doDaysStr=DaysOffFrontModel::getDaysOffDatesAsJS($_r['afID'],date('Y'));
		$doDaysNextStr=DaysOffFrontModel::getDaysOffDatesAsJS($_r['afID'],(date('Y')+1));
		
		$vacDaysStr=DaysOffFrontModel::getVacDatesAsJS($_r['afID'],date('Y'));
		$vacDaysNextStr=DaysOffFrontModel::getVacDatesAsJS($_r['afID'],date('Y')+1);
		
		$holidaysStr=cf::getFederalHolidaysAsJS(date('Y'));
		$holidaysNextStr=cf::getFederalHolidaysAsJS(date('Y')+1);

		echo '<div class="years" style="display:none;" id="yeardiv'.$year.'">';

		echo '<div style="margin:0 auto;width:100%;text-align:left">';
		echo '<table class="white_table" style="width:100%">';
		echo '<thead><tr><th style="text-align:center">Year</th><th></th><th style="text-align:center">Allowed</th>
		<th style="text-align:center">Used</th><th style="text-align:center">Remaining</th></tr></thead>';
		echo '<tbody><tr><td style="text-align:center">'.$year.'</td><td>Paid days Vacation / sick leave</td>
		<td style="text-align:right">'.$total1.'</td>
		<td style="text-align:right">'.$used1.'</td>
		<td style="text-align:right">'.$remain1.'</td></tr>';
		echo '<tr><td style="text-align:center">'.$year.'</td><td>Paid days CE</td><td style="text-align:right">'.$total2.'</td>
		<td style="text-align:right">'.$used2.'</td><td style="text-align:right">'.$remain2.'</td></tr>';
		
		echo '<tr><td style="text-align:center">'.$year.'</td>
		<td>Unpaid days used <td>
		</td><td style="text-align:right">'.$used3.'</td><td></td></tr>';
		/******************
		echo '<script>
		jQuery( document ).ready(function() {
			var datesArray'.$year.'=['.$unpaidStr.'];
			jQuery(function(){
				jQuery("#permonth-unpaid'.$year.'").datepicker({
					 startDate:"1/1/'.$year.'",
					 endDate:"12/31/'.$year.'",
					 inline: true,
					 toggleActive:true,
					 title:"Unpaid days per '.$year.'",
					 beforeShowDay: function (date) {
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									
									if(jQuery.inArray(theday, datesArray'.$year.')>=0)
									return  "unpaidDate";
								   else
									return "";
						}
				});
			});
		});
		</script>';
		
		echo '<tr><td style="text-align:center">'.$year.'</td><td>Sick leave unpaid days used <a style="float:right" href="javascript:void(0)" 
		onclick="showhide_calendar(this)" data-id="permonthSL'.$year.'">View by month >>></a><br>
		<div style="width:280px;float:right;display:none;background-color:#FFF" id="permonthSL'.$year.'" class="permonth" data-date="1/1/'.$year.'"></div>
		<td>
		</td><td style="text-align:right">'.$usedSL.'</td><td></td></tr>';
		echo '<script>
		jQuery( document ).ready(function() {
			var datesArraySL'.$year.'=['.$unpaidSLStr.'];
			jQuery(function(){
				jQuery("#permonthSL'.$year.'").datepicker({
					 startDate:"1/1/'.$year.'",
					 endDate:"12/31/'.$year.'",
					 inline: true,
					 toggleActive:true,
					 title:"Sick leave unpaid '.$year.'",
					 beforeShowDay: function (date) {
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									if(jQuery.inArray(theday, datesArraySL'.$year.')>=0)
									return  "unpaidDate";
								   else
									return "";
						}
				});
			});
		});
		</script>';
		
		echo '<tr><td style="text-align:center">'.$year.'</td>
		<td>CE unpaid days used <a style="float:right" href="javascript:void(0)" 
		onclick="showhide_calendar(this)" data-id="permonthCE'.$year.'">View by month >>></a><br>
		<div style="width:280px;float:right;display:none;background-color:#FFF" id="permonthCE'.$year.'" class="permonth" data-date="1/1/'.$year.'"></div>
		<td>
		</td><td style="text-align:right">'.$usedCE.'</td><td></td></tr>';
		echo '<script>
		jQuery( document ).ready(function() {
			var datesArrayCE'.$year.'=['.$unpaidCEStr.'];
			jQuery(function(){
				jQuery("#permonthCE'.$year.'").datepicker({
					 startDate:"1/1/'.$year.'",
					 endDate:"12/31/'.$year.'",
					 inline: true,
					 toggleActive:true,
					 title:"CE unpaid '.$year.'",
					 beforeShowDay: function (date) {
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									if(jQuery.inArray(theday, datesArrayCE'.$year.')>=0)
									return  "unpaidDate";
								   else
									return "";
						}
				});
			});
		});
		</script>';
		
		echo '<tr><td style="text-align:center">'.$year.'</td><td>Jury duty  unpaid days used <a style="float:right" href="javascript:void(0)" 
		onclick="showhide_calendar(this)" data-id="permonthJD'.$year.'">View by month >>></a><br>
		<div style="width:280px;float:right;display:none;background-color:#FFF" id="permonthJD'.$year.'" class="permonth" data-date="1/1/'.$year.'"></div>
		<td>
		</td><td style="text-align:right">'.$usedJD.'</td><td></td></tr>';
		echo '<script>
		jQuery( document ).ready(function() {
			var datesArrayJD'.$year.'=['.$unpaidJDStr.'];
			jQuery(function(){
				jQuery("#permonthJD'.$year.'").datepicker({
					 startDate:"1/1/'.$year.'",
					 endDate:"12/31/'.$year.'",
					 inline: true,
					 toggleActive:true,
					 title:"Jury duty unpaid '.$year.'",
					 beforeShowDay: function (date) {
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									if(jQuery.inArray(theday, datesArrayJD'.$year.')>=0)
									return  "unpaidDate";
								   else
									return "";
						}
				});
			});
		});
		</script>';
		*****************/
		echo '</tbody></table><br><a  class="blue_link" href="javascript:void(0)" 
		onclick="showhide_calendar(this)" data-id="permonth-unpaid'.$year.'">View by month >></a><br>
		<div style="text-align:initial;width:100%;padding:10px;display:none;background-color:#FFF" id="permonth-unpaid'.$year.'" class="permonth" data-date="1/1/'.$year.'">
		<h4>Year: '.$year.'</h4>';
		DaysOffFrontView::showSummary($year);
		echo '</div>';
		echo '</div>
		<br><br>
		<div style="margin:0 auto;width:100%;text-align:left">';
		$user=$_r['afID'];
		echo '<h4>Approved days:</h4>';
		$q="SELECT * FROM vacation_requests WHERE vrAFID={$user} AND vrYear={$year} AND vrState='approved'
			ORDER BY	vrDateStart DESC";
		$reqs=db::PrepareLoad($q);
		if ($reqs==false) echo '&nbsp;&nbsp;&nbsp;&nbsp;None<br>';
		else {
			$nmb=0;
			$totalReq=count($reqs);
			foreach ($reqs as $rq){
				$color="#999";
				$add='';
				$today = val::currentDate('Y-m-d');
				
				$pipe=false;
			
				if ($rq['vrDateStart'] > $today) {
					$color='#000';
					$add.='<a class="blue_link" style="float:left;margin-right:5px;margin-left:10px"  href="?cmd=cancel_request&req_id='.$rq['vrID'].'"
					onclick="return confirm(\'Are you sure to cancel this request?\')">cancel days off</a>
					';
					$pipe=true;
				}
	
				if ($rq['vrDateEnd'] > $today ){
					if ($pipe) $add.='<span style="float:left;display:inline-block">|</span>';
					
					if ($rq['vrDays']>1)
					$add.='<a class="blue_link" style="float:left;margin-left:5px;"  href="javascript:void(0)"
					onclick="initShorten(\''.$rq['vrID'].'\' ,\''.$year.'\' ,\''
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateStart']).'\',\''
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateEnd']).'\','
					.'\''.$rq['vrType'].'\',\''.$rq['vrPaydUnpaid'].'\')">shorten</a>';
				}
				
				
				
					
				if ($rq['vrDays']==1 || $rq['vrDays']==0.5) {
					$daysStr=val::mynumber_format($rq['vrDays'],1).' day: ';
					if($rq['vrDays']==0.5)  $daysStr=' HALF DAY:';
					echo "<span style=\"width:570px;display:block;margin-bottom:5px;\">
					&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"display:inline-block\">"
					.$rq['vrType'].', '.$rq['vrPaydUnpaid'].', '.$daysStr.' '
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateStart'])
					."</span><span style=\"float:right\">{$add}</span></span>";
				}
				else {
					$daysStr=val::mynumber_format($rq['vrDays'],1).' days: ';
					echo "<span style=\"width:570px;display:block;margin-bottom:5px;\">
					&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"display:inline-block\">"
					.$rq['vrType'].', '.$rq['vrPaydUnpaid'].', '.$daysStr.' '
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateStart']).' - '
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateEnd'])
					."</span><span style=\"float:right\">{$add}</span></span>";
				}
				
				$nmb++;
				if ($nmb == 3 && $totalReq>3) echo '&nbsp;&nbsp;&nbsp;&nbsp;<a class="blue_link" href="javascript:void(0)" 
				onclick="vievhideRequests(this,\'#approved'.$year.'\')">
				Show more >></a><div style="display:none" id="approved'.$year.'">';
			}
			if ($nmb >= 3 && $totalReq>3) echo "</div>";
		}
		echo '<hr>';
		
		echo '<h4>Pending requests:</h4>';
		$user=$_r['afID'];
		
		$q="SELECT * FROM vacation_requests WHERE vrAFID={$user} AND vrYear={$year} AND vrState='pending'
			ORDER BY	vrDateStart DESC";
			
		$reqs=db::PrepareLoad($q);
		
		if ($reqs==false) echo '&nbsp;&nbsp;&nbsp;&nbsp;None<br>';
		else {
			$nmb=0;
			$totalReq=count($reqs);
			foreach ($reqs as $rq){
				$color="#999";
				$add='';
				$today = val::currentDate('Y-m-d');
				
				$pipe=false;
				
				if ($rq['vrDateStart'] > $today) {
					$color='#000';
					$add.='<a class="blue_link" style="float:left;margin-right:5px;margin-left:10px"  href="?cmd=cancel_request&req_id='.$rq['vrID'].'"
					onclick="return confirm(\'Are you sure to cancel this request?\')">cancel days off</a>
					';
					$pipe=true;
				}
	
				if ($rq['vrDateEnd'] > $today ){
				if ($pipe) $add.='<span style="float:left;display:inline-block">|</span>';
				
				
				if ($rq['vrDays']>1)
					$add.='<a class="blue_link" style="float:left;margin-left:5px;"  href="javascript:void(0)"
					onclick="initShorten(\''.$rq['vrID'].'\' ,\''.$year.'\' ,\''
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateStart']).'\',\''
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateEnd']).'\','
					.'\''.$rq['vrType'].'\',\''.$rq['vrPaydUnpaid'].'\')">shorten'.$rq['vrDays'].'</a>';
				}
				
			
				
				if ($rq['vrDays']==1 || $rq['vrDays']==0.5) {
					$daysStr=val::mynumber_format($rq['vrDays'],1).' day: ';
					if($rq['vrDays']==0.5)  $daysStr=' HALF DAY:';
					
					echo "<span style=\"width:570px;display:block;margin-bottom:5px;\">
					&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"display:inline-block\">"
					.$rq['vrType'].', '.$rq['vrPaydUnpaid'].', '.$daysStr.' '
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateStart'])
					."</span><span style=\"float:right\">{$add}</span></span>";
				}
				else {
					$daysStr=$rq['vrDays'].' days: ';
					echo "<span style=\"width:570px;display:block;margin-bottom:5px;\">
					&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"display:inline-block\">"
					.$rq['vrType'].', '.$rq['vrPaydUnpaid'].', '.$daysStr.' '
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateStart']).' - '
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateEnd'])
					."</span><span style=\"float:right\">{$add}</span></span>";
				}
				
				
				
				$nmb++;
				if ($nmb == 3 && $totalReq>3) echo '&nbsp;&nbsp;&nbsp;&nbsp;<a class="blue_link" href="javascript:void(0)" 
				onclick="vievhideRequests(this,\'#pending'.$year.'\')">
				Show more >></a><div style="display:none" id="pending'.$year.'">';
			}
			if ($nmb >= 3 && $totalReq>3)echo "</div>";
		}
		echo '<hr>';
		echo '<h4>Declined requests:</h4>';
		$q="SELECT * FROM vacation_requests WHERE vrAFID={$user} AND vrYear={$year} AND vrState='declined'
			ORDER BY	vrDateStart DESC";
		$reqs=db::PrepareLoad($q);
		if ($reqs==false) echo '&nbsp;&nbsp;&nbsp;&nbsp;None<br>';
		else {
			$nmb=0;
			$totalReq=count($reqs);
			foreach ($reqs as $rq){
				if ($rq['vrDateStart'] <= val::currentDate('Y-m-d')) $color='#999';
				else $color="#000";
				
				if ($rq['vrDays']==1 || $rq['vrDays']==0.5) {
					$daysStr=val::mynumber_format($rq['vrDays'],1).' day: ';
					if($rq['vrDays']==0.5)  $daysStr=' HALF DAY:'; 
					echo "
					<span style=\"display:block;margin-bottom:5px;\">"
					.$rq['vrType'].', '.$rq['vrPaydUnpaid'].', '.$daysStr.' '
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateStart'])
					.' &nbsp;&nbsp;Reason: '.$rq['vrDeclineReason'].'</span>
					 </span>';
				}
				else {
					$daysStr=$rq['vrDays'].' days:';
					echo "
					<span style=\"display:block;margin-bottom:5px;\">"
					.$rq['vrType'].', '.$rq['vrPaydUnpaid'].', '.$daysStr.' '
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateStart']).' - '
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateEnd'])
					.' &nbsp;&nbsp;Reason: '.$rq['vrDeclineReason'].'</span>
					 </span>';
				}
				 $nmb++;
				if ($nmb == 3 && $totalReq>3) echo '&nbsp;&nbsp;&nbsp;&nbsp;<a class="blue_link" href="javascript:void(0)" 
				onclick="vievhideRequests(this,\'#declined'.$year.'\')">
				Show more >></a><div style="display:none" id="declined'.$year.'">';
			}
			if ($nmb >= 3 && $totalReq>3)echo "</div>";
		}
		echo '<hr>';
		
		
		
		echo '</div>
		</div>';
		}//  end years divs
		
		
		
		echo '
		<script>
		function vievhideRequests(that,id){
			if (jQuery(id).is(":visible")) {
				jQuery(id).slideUp();
				jQuery(that).text("Show more >>");
			}
			else {
				jQuery(id).slideDown();
				jQuery(that).text("Hide more >>");
			}
		}
		
		function checkSubmit(year){ 
			if (jQuery("#working_days_msg"+year).html()=="")
			myalert("Alert","Please select dates.");
			else {
				if ( jQuery("#form"+year+" #startDate").val() != jQuery("#form"+year+" #endDate").val()
					&& jQuery("#form"+year+" #half_day").is(":checked"))
				{
				if(confirm("Half day request will be set for "+jQuery("#form"+year+" #startDate").val()+". Continue?"))
					jQuery("#form"+year).submit();
				}
				else 
				jQuery("#form"+year).submit();
			}
		}
		
		jQuery( document ).ready(function() {
		';
		if (isset($go_to_year)) echo 'jQuery("#year_select").val("'.$go_to_year.'");';
		
		if($year=date('Y')){
		echo 'yearChange();
			
			var startStartDate'.date("Y").'="'.date(USER_DATE).'";
			var startEndDate'.date("Y").' = "12/31/'.date("Y").'";
			
			jQuery("#form'.date("Y").' #startDate").datepicker({ 
				format: "m/d/yyyy", 
				endDate:"12/31/'.date("Y").'",	  
				startDate:"'.date(USER_DATE).'",
				//clearBtn:true,
				todayHighlight:true,
				title:"Please select a start date",
				autoclose:true,
				beforeShowDay: function (date) {
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									if(jQuery.inArray(theday, blockOutDatesArray'.$year.')>=0){ 
									var ret={
										enabled:true,
										classes:"blockOutDate",
										tooltip:"Block out date"
										};
									};
									if(jQuery.inArray(theday, daysOffDatesArray'.$year.')>=0){ 
									var ret={
										enabled:true,
										classes:"dayOFFDate",
										tooltip:"Non-working day"
										};
									}
									
									if(jQuery.inArray(theday, fedHolidaysArray'.$year.')>=0){ 
									var ret={
										enabled:true,
										classes:"fedHoly",
										tooltip:"US holiday"
										};
										
									}
									
									if(jQuery.inArray(theday, vacDatesArray'.$year.')>=0){ 
									var ret={
										enabled:false,
										classes:"vacDate",
										tooltip:"Approved day off"
										};
									}
									return ret;
						}
				
			},"clearDates")
			.on("changeDate", function(selected){
				var typeOfReq=jQuery("#vacType'.$year.'").val();
				var date = selected.format(0,"m/d/yyyy");
			
				if (typeOfReq !="Jury duty" && typeOfReq !="Sick leave" 
				&&  jQuery.inArray(date, blockOutDatesArray'.$year.')>=0){
					myalert("Alert","You can not select block-out dates except if request is for Jury duty or Sick leave.");
					jQuery("#form'.date("Y").' #startDate").val("");
					e.preventDefault();
					
				}
								

				startDate = new Date(selected.date.valueOf());
				startDate.setDate(startDate.getDate(new Date(selected.date.valueOf())));
				jQuery("#form'.date("Y").' #endDate").datepicker("setStartDate", startDate);
			})
			.on("clearDate", function(selected){
				jQuery("#form'.date("Y").' #startDate").datepicker("setStartDate", "'.date(USER_DATE).'");
			});
			
			var endStartDate'.date("Y").'="'.date(USER_DATE).'";
			var endEndDate'.date("Y").' = "12/31/'.date("Y").'";
			
			jQuery("#form'.date("Y").' #endDate").datepicker({ 
			  format: "m/d/yyyy", 
			  startDate:"'.date(USER_DATE).'",
			  endDate:"12/31/'.date("Y").'",
			  //clearBtn:true,			  
			  todayHighlight:true,
			  title:"Please select an end date",
			  autoclose:true,
			  beforeShowDay: function (date) {
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									
									if(jQuery.inArray(theday, blockOutDatesArray'.$year.')>=0){ 
									var ret={
										enabled:true,
										classes:"blockOutDate",
										tooltip:"Block out date"
										};
									};									
									if(jQuery.inArray(theday, daysOffDatesArray'.$year.')>=0){ 
									var ret={
										enabled:true,
										classes:"dayOFFDate",
										tooltip:"Non-working day"
										};
									};
									
									if(jQuery.inArray(theday, fedHolidaysArray'.$year.')>=0){ 
									var ret={
										enabled:true,
										classes:"fedHoly",
										tooltip:"US holiday"
										};
										
									}
									
									if(jQuery.inArray(theday, vacDatesArray'.$year.')>=0){ 
									var ret={
										enabled:false,
										classes:"vacDate",
										tooltip:"Approved day off"
										};
									}
									return ret;

						}
			},"clearDates")
			
			.on("changeDate", function(selected){
				var typeOfReq=jQuery("#vacType"+'.$year.').val();
				var date = selected.format(0,"m/d/yyyy");
				
				if (typeOfReq !="Jury duty" && typeOfReq !="Sick leave" 
				&&  jQuery.inArray(date, blockOutDatesArray'.$year.')>=0){
					myalert("Alert","You can not select block-out dates except if request is for Jury duty or Sick leave.");
					jQuery("#form'.date("Y").' #endDate").val("");
					e.preventDefault();
				}
				startDate = new Date(selected.date.valueOf());
				startDate.setDate(startDate.getDate(new Date(selected.date.valueOf())));
				jQuery("#form'.date("Y").' #startDate").datepicker("setEndDate", startDate);
				
			})
			.on("clearDate", function(selected){
				jQuery("#form'.date("Y").' #startDate").datepicker("setEndDate", "12/31/'.date("Y").'");
				jQuery("#form'.date("Y").' #endDate").datepicker("setEndDate", "12/31/'.date("Y").'");
			});
				
			/*********************************/
			
			var startStartDate'.(date('Y')+1).'="1/1/'.(date('Y')+1).'";
			var startEndDate'.(date('Y')+1).' = "12/31/'.(date('Y')+1).'";
			
			jQuery("#form'.(date('Y')+1).' #startDate").datepicker({ 				
				format: "m/d/yyyy",    
				startDate:"1/1/'.(date('Y')+1).'",
				endDate:"12/31/'.(date('Y')+1).'",
				defaultViewDate:"1/1/'.(date('Y')+1).'",				
				//clearBtn:true,
				todayHighlight:true,
				title:"Please select a start date",
				autoclose:true,
				beforeShowDay: function (date) {
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									
									if(jQuery.inArray(theday, blockOutDatesArray'.($year+1).')>=0){ 
									var ret={
										enabled:false,
										classes:"blockOutDate",
										tooltip:"Block out date"
										};
									};
									if(jQuery.inArray(theday, daysOffDatesArray'.($year+1).')>=0){ 
									var ret={
										enabled:true,
										classes:"dayOFFDate",
										tooltip:"Mom-working date"
										
										};
									};
									
									if(jQuery.inArray(theday, fedHolidaysArray'.($year+1).')>=0){
									var date=theday.split("/");
									var ret={
										enabled:true,
										classes:"fedHoly",
										tooltip:"US holiday"
										};
										
									}
									if(jQuery.inArray(theday, vacDatesArray'.($year+1).')>=0){ 
									var ret={
										enabled:false,
										classes:"vacDate",
										tooltip:"Approved day off"
										};
									}
									
									return ret;

						}
			},"clearDates")
			.on("changeDate", function(selected){
				var typeOfReq=jQuery("#vacType"+'.$year.').val();
				var date = selected.format(0,"mm/dd/yyyy");
				
				if (typeOfReq !="Jury duty" && typeOfReq !="Sick leave" 
				&&  jQuery.inArray(date, blockOutDatesArray'.$year.')>=0){
					myalert("Alert","You can not select block-out dates except if request is for Jury duty or Sick leave.");
					jQuery("#form'.(date("Y")+1).' #endDate").val("");
					e.preventDefault();
				}
				startDate2 = new Date(selected.date.valueOf());
				startDate2.setDate(startDate2.getDate(new Date(selected.date.valueOf())));
				jQuery("#form'.(date("Y")+1).' #endDate").datepicker("setStartDate", startDate2);
			})
			.on("clearDate", function(selected){
				jQuery("#form'.(date("Y")+1).' #startDate").datepicker("setStartdDate", "1/1/'.(date('Y')+1).'");
			});			
		
		var endStartDate'.(date('Y')+1).'="1/1/'.(date('Y')+1).'";
		var endEndDate'.(date('Y')+1).' = "12/31/'.(date('Y')+1).'";
			
		jQuery("#form'.(date('Y')+1).' #endDate").datepicker({ 
				format: "m/d/yyyy", 
				startDate:"1/1/'.(date('Y')+1).'",
				endDate:"12/31/'.(date('Y')+1).'",
				defaultViewDate:"1/1/'.(date('Y')+1).'",	
				//clearBtn:true,				
				todayHighlight:true,
				title:"Please select an end date",
				autoclose:true,
				beforeShowDay: function (date) {
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									
									if(jQuery.inArray(theday, blockOutDatesArray'.($year+1).')>=0){ 
									var ret={
										enabled:false,
										classes:"blockOutDate",
										tooltip:"Block out date"
										};
									};
									
									if(jQuery.inArray(theday, daysOffDatesArray'.($year+1).')>=0){ 
									var ret={
										enabled:true,
										classes:"dayOFFDate",
										tooltip:"Non-working day"
										};
									};
									
									if(jQuery.inArray(theday, fedHolidaysArray'.($year+1).')>=0){ 
									var ret={
										enabled:true,
										classes:"fedHoly",
										tooltip:"US holiday"
										};
										
									}
									
									if(jQuery.inArray(theday, vacDatesArray'.($year+1).')>=0){ 
									var ret={
										enabled:false,
										classes:"vacDate",
										tooltip:"Approved day off(both paid and unpaid)"
										};
									}
									return ret;
						}
			},"clearDates")
			.on("changeDate", function(selected){
				var typeOfReq=jQuery("#vacType"+'.$year.').val();
				var date = selected.format(0,"mm/dd/yyyy");
				
				if (typeOfReq !="Jury duty" && typeOfReq !="Sick leave" 
				&&  jQuery.inArray(date, blockOutDatesArray'.$year.')>=0){
					myalert("Alert","You can not select block-out dates except if request is for Jury duty or Sick leave.");
					jQuery("#form'.(date("Y")+1).' #endDate").val("");
					e.preventDefault();
				}
				FromEndDate2 = new Date(selected.date.valueOf());
				FromEndDate2.setDate(FromEndDate2.getDate(new Date(selected.date.valueOf())));
				jQuery("#form'.(date("Y")+1).' #startDate").datepicker("setEndDate", FromEndDate2);
			})
			.on("clearDate", function(selected){
				jQuery("#form'.(date("Y")+1).' #startDate").datepicker("setEndDate", "12/31/'.(date("Y")+1).'");
				jQuery("#form'.(date("Y")+1).' #endDate").datepicker("setEndDate", "12/31/'.(date("Y")+1).'");
			});
		});
		
		function DayChanged(that){//alert(222);
			var year=jQuery(that).data("year");
			if (jQuery("#form"+year+" #startDate").val()!="" && jQuery("#form"+year+" #endDate").val()!=""){
				var url="?cmd=get_working_days&year="+year
					+"&date_start="+jQuery("#form"+year+" #startDate").val()
					+"&date_end="+jQuery("#form"+year+" #endDate").val()
					+"&vacType="+jQuery("#vacType"+year).val();
					jQuery("#working_days_msg"+year).html("<span style=\"color:red\">Checking. Please wait....</span>");
				
				jQuery.ajax({
				url: url,
				success: function (result) { //alert(result);
				  resp = jQuery.parseJSON(result); 
				  if (resp.result === "OK")  {
					jQuery("#working_days"+year).val(resp.days);
					jQuery("#working_days_msg"+year).html(resp.days_msg);
					jQuery("#paid_type"+year).val(resp.type);
					
				  }
				  else {
					  jQuery("#working_days_msg"+year).html("");
					  //jQuery("#btn"+year).css("visibility","hidden");
					  myalert("Alert",resp.message);
				  }
				}
				});
			}
			else {
				jQuery("#working_days_msg"+year).html("");
				//jQuery("#btn"+year).css("visibility","hidden");
			}
		}
		
		function clearMyDates(year){ 
			jQuery(\'#form\'+year+\' #startDate\').val(\'\').datepicker("update");
			jQuery(\'#form\'+year+\' #endDate\').val(\'\').datepicker("update");
			jQuery("#working_days_msg"+year).html("");
			//jQuery("#btn"+year).css("visibility","hidden");
			
			jQuery("#form"+year+" #startDate").datepicker("setStartDate", eval(startStartDate+year));
			jQuery("#form"+year+" #startDate").datepicker("setEndDate", eval(startEndDate+year));
			
			jQuery("#form"+year+" #endDate").datepicker("setStartDate", eval(endStartDate+year));
			jQuery("#form"+year+" #endDate").datepicker("setEndDate", eval(endtartDate+year));
		}
		
		function showhide_calendar(that){
		    var id="#"+jQuery(that).data("id");
			if (jQuery(id).is(":visible")) {jQuery(that).text("View by month >>");jQuery(id).slideUp();}
			else {jQuery(that).text("Hide view by month >>"); jQuery(id).slideDown();}
			
		}
	
		function show_link(){
			var req=jQuery("#year_select").find(":selected").data("has-request");
			if(req==1) jQuery("#days-off-link").css("visibility","visible"); 
			else jQuery("#days-off-link").css("visibility","hidden");
		}
		
		function yearChange(){
			var sel = jQuery("#years_select").find(":selected").text();
			jQuery(".years").fadeOut();
			jQuery("#yeardiv"+sel).fadeIn();
			show_link();
		}
		
		</script>
		
		</body></html>';
		}// current year
	}

	public static function ViewDaysOff($data,$go_to_year=null){
		
		if (isset($_SESSION['webapp-admin-token'])) $isAdmin=true; else $isAdmin=false;
		
		if(isset($_GET['go_to_year'])) $go_to_year=$_GET['go_to_year'];
		//AppModel::$usr['afStartDate'])
		msg::get();
		echo '<div class="content bg-white" style="text-align:center"><br>';
		
		echo '<div id="years_select" style="width:100%;margin:0 auto;text-align:center;
		font-size:20px;font-weight: bold;position:relative;top:-10px;">
		Year:
		<select id="year_select" title="Click here to change year"  onchange="yearChange()">';
		foreach($data['years'] as $k=>$year){
			//if ($year==2018) continue;
			if ($year==date('Y')) $sel='selected '; else $sel='';
			if (!($year==date('Y') || $year==date('Y')+1)) $sel.='data-has-request="1"'; 
			else $sel.='data-has-request="0"';
			echo '<option '.$sel.' value="'.$year.'">'.$year.'</option>';
		}
		echo '</select>
		<br>
		<span id="days-off-link" >
		<a class="blue_link" style="display:block;margin:10px;font-size:1em;" href="javascript:void(0)"  
		onclick="jQuery(\'#year_select\').val(\''.date('Y').'\');yearChange(); ">Request days off >></a>
		</span>
		</div>	';
		
		foreach($data['years'] as $k=>$year){
		$total1 = cf::getEmployeePaidDaysOff($year, AppModel::$usr['afID']);
        //cf::getEmployeePastPaidDaysOff($year,AppModel::$usr['afID']);  //DaysOffFrontModel::getDefaultDays(AppModel::$usr['afID'],$year,'Vacation');
		$used1 = DaysOffFrontModel::getUsedDays(AppModel::$usr['afID'],$year,['Vacation','Sick leave'],'paid');
		$remain1 = $total1-$used1;
		if ($remain1<0) $remain1=0;
		
		$total2 = DaysOffFrontModel::getDefaultDays(AppModel::$usr['afID'],$year,'CE');
		$used2 = DaysOffFrontModel::getUsedDays(AppModel::$usr['afID'],$year,['CE'],'paid');
		$remain2 = $total2-$used2;
		if ($remain2<0) $remain2=0;
	
		$used3 = DaysOffFrontModel::getUsedDays(AppModel::$usr['afID'],$year,[],'unpaid');
		
		$usedSL=DaysOffFrontModel::getUsedDays(AppModel::$usr['afID'],$year,['Sick leave'],'unpaid');
		$usedJD=DaysOffFrontModel::getUsedDays(AppModel::$usr['afID'],$year,['Jury duty'],'unpaid');
		$usedCE=DaysOffFrontModel::getUsedDays(AppModel::$usr['afID'],$year,['CE'],'unpaid');
		
		$unpaidStr=DaysOffFrontModel::getUnpaidDatesAsJS(AppModel::$usr['afID'],$year,['Unpaid']);
		$unpaidJDStr=DaysOffFrontModel::getUnpaidDatesAsJS(AppModel::$usr['afID'],$year,['Jury duty']);
		$unpaidCEStr=DaysOffFrontModel::getUnpaidDatesAsJS(AppModel::$usr['afID'],$year,['CE']);
		$unpaidSLStr=DaysOffFrontModel::getUnpaidDatesAsJS(AppModel::$usr['afID'],$year,['Sick leave']);
		
		$boDaysStr=DaysOffFrontModel::getBODatesAsJS(AppModel::$usr['afID'],date('Y'));
		$boDaysNextStr=DaysOffFrontModel::getBODatesAsJS(AppModel::$usr['afID'],(date('Y')+1));
		
		$doDaysStr=DaysOffFrontModel::getDaysOffDatesAsJS(AppModel::$usr['afID'],date('Y'));
		$doDaysNextStr=DaysOffFrontModel::getDaysOffDatesAsJS(AppModel::$usr['afID'],(date('Y')+1));
		
		$vacDaysStr=DaysOffFrontModel::getVacDatesAsJS(AppModel::$usr['afID'],date('Y'));
		$vacDaysNextStr=DaysOffFrontModel::getVacDatesAsJS(AppModel::$usr['afID'],date('Y')+1);
		
		$holidaysStr=cf::getFederalHolidaysAsJS(date('Y'));
		$holidaysNextStr=cf::getFederalHolidaysAsJS(date('Y')+1);
		
		if($year==date('Y')){
		echo '
		<div class="modal fade" id="shModal" role="dialog" style="z-index:9999;">
		<div class="modal-dialog modal-sm" style="width:500px">
			<div class="modal-content" style="width:100%">
				<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title" id="msg_title">DAYS OFF request Shortening</h4>
				</div>
				<form id="shForm" method="POST" action="?cmd=shorten" >
				<div class="modal-body" >
				<input type="hidden" name="year" id="shYear" value="">
				<input type="hidden" name="id" id="shID" value="">
				<input type="hidden" name="type" id="shType" value="">
				<input type="hidden" name="paid_type"  id="shpaid_type">
				<input type="hidden" name="days"  id="shdays">
				<label style="width:200px;display:inline-block;">Requested date(s):</label>
				<div id="daterange" style="position:relative;top:5px;display:inline-block;width:200px">
					<div  class="input-group input-daterange" >
					<input value="" onchange="shDayChanged()"  class="date" name="startDate" id="shStartDate" style="width:95px;background:#FFF;text-align:left;padding:2px;height:1.5em;border:1px solid #CCC" type="text" class="form-control" placeholder="Start date" readonly>
					<div style="height:1.5em;padding:2px;border-top:1px solid #CCC;border-bottom:1px solid #CCC" class="input-group-addon">&nbsp;to&nbsp; </div>
					<input value="" onchange="shDayChanged()" class="date" name="endDate" id="shEndDate" style="width:95px;background:#FFF;text-align:left;padding:2px;height:1.5em;border:1px solid #CCC"  type="text" class="form-control" placeholder="End date" readonly>
					</div>	
				</div>
				<label style="width:200px">&nbsp;</label><span id="shworking_days_msg"></span>
				<div id="shbtn" style="visibility:hidden">
					<input type="submit" class="btn btn-primary" value="Send request">
				</div>
				</div>
				
				</form>
			</div>
		</div>
	 </div>
	 <script>
	 var shdateStart,shdateEnd;
	 var   blockOutDatesArray'.$year.'=['.$boDaysStr.'];
	 var   blockOutDatesArray'.($year+1).'=['.$boDaysNextStr.'];
	 var   daysOffDatesArray'.($year).'=['.$doDaysStr.'];
	 var   daysOffDatesArray'.($year+1).'=['.$doDaysNextStr.'];
	 var   vacDatesArray'.($year).'=['.$vacDaysStr.'];
	 var   vacDatesArray'.($year+1).'=['.$vacDaysNextStr.'];
	 var  fedHolidaysArray'.$year.'=['.$holidaysStr.'];
	 var  fedHolidaysArray'.($year+1).'=['.$holidaysNextStr.'];
	 
	 function initShorten(id,year,dateStart,dateEnd,type,paid_type){
		 jQuery("#shworking_days_msg").html("");
		 shdateStart=dateStart;
		 shdateEnd=dateEnd;
		 jQuery("#shYear").val(year);
		 jQuery("#shID").val(id);
		 jQuery("#shType").val(type);
		 jQuery("#shpaid_type").val(paid_type);
		 jQuery("#shStartDate").val(dateStart);
		 jQuery("#shEndDate").val(dateEnd);
		 shblockOutDatesArray = eval("blockOutDatesArray"+year);
		 shdaysOffDatesArray = eval("daysOffDatesArray"+year);
		 shvacDatesArray = eval("vacDatesArray"+year);
		 shFedHolidaysArray = eval("fedHolidaysArray"+year);
		
		 jQuery("#shStartDate").datepicker("destroy");
		 jQuery("#shEndDate").datepicker("destroy");
		 
		 var today = new Date();
		 var sDate = new Date(dateStart);
		 if (today < sDate ) {
		 jQuery( "#shStartDate").datepicker({ 
				format: "m/d/yyyy", 
				startDate:dateStart,
				endDate:dateEnd,	  
				clearBtn:true,
				todayHighlight:true,
				title:"Please select a new start",
				autoclose:true,
				beforeShowDay: function (date) {				    
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									if(jQuery.inArray(theday, shblockOutDatesArray)>=0){ 
									var ret={
										enabled:true,
										classes:"blockOutDate",
										tooltip:"Block out date"
										};
									}
									
									if(jQuery.inArray(theday, shdaysOffDatesArray)>=0){ 
									var ret={
										enabled:true,
										classes:"dayOFFDate",
										tooltip:"Non-working day"
										};
										
									}
									
									if(jQuery.inArray(theday, shFedHolidaysArray)>=0){ 
									var ret={
										enabled:true,
										classes:"fedHoly",
										tooltip:"US holiday"
										};
										
									}
									
									if(jQuery.inArray(theday, shvacDatesArray)>=0){ 
									var ret={
										enabled:true,
										classes:"vacDate",
										tooltip:"Vacation day"
										};
										
									}
									return ret;
									}
						
				
			},"clearDates")
			.on("changeDate", function(selected){
				startDate = new Date(selected.date.valueOf());
				startDate.setDate(startDate.getDate(new Date(selected.date.valueOf())));
				jQuery("#shEndDate").datepicker("setStartDate", startDate);
			})
			.on("clearDate", function(selected){
				jQuery("#shStartDate").val(dateStart);
				jQuery("#shStartDate").datepicker("setStartDate", dateStart);
				jQuery("#shStartDate").datepicker("setEndDate", dateEnd);
				jQuery("#shEndDate").datepicker("setStartDate", dateStart);
			}); 
		 }
			jQuery("#shEndDate").datepicker({ 
			  format: "m/d/yyyy", 
			  startDate:dateStart,
			  endDate:dateEnd,	  
			  clearBtn:true,			  
			  todayHighlight:true,
			  title:"Please select a new end date",
			  autoclose:true,
			  beforeShowDay: function (date) {
				      
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									
									if(jQuery.inArray(theday, shblockOutDatesArray)>=0){ 
									var ret={
										enabled:true,
										classes:"blockOutDate",
										tooltip:"Block out date"
										};
									}
									
									if(jQuery.inArray(theday, shdaysOffDatesArray)>=0){ 
									var ret={
										enabled:true,
										classes:"dayOFFDate",
										tooltip:"Non-working day"
										};
										
									}
									
									if(jQuery.inArray(theday, shFedHolidaysArray)>=0){ 
									var ret={
										enabled:true,
										classes:"fedHoly",
										tooltip:"US holiday"
										};
										
									}
									if(jQuery.inArray(theday, shvacDatesArray)>=0){ 
									var ret={
										enabled:true,
										classes:"vacDate",
										tooltip:"Vacation day"
										};
										
									}
									return ret;

						}
				
			},"clearDates")
			.on("changeDate", function(selected){
				FromEndDate = new Date(selected.date.valueOf());
				FromEndDate.setDate(FromEndDate.getDate(new Date(selected.date.valueOf())));
				jQuery("#shStartDate").datepicker("setEndDate", FromEndDate);
			})
			.on("clearDate", function(selected){
				//jQuery("#shEndDate").datepicker("setStartDate", dateStart);
				jQuery("#shEndDate").val(dateEnd);
				jQuery("#shEndDate").datepicker("setEndDate", dateEnd);
				jQuery("#shStartDate").datepicker("setEndDate", dateEnd);
			});
		 
		 jQuery("#shModal").modal("show");
	 }
	 
	 function shDayChanged(){
	 			if (jQuery("#shStartDate").val()!="" && jQuery("#shEndDate").val()!=""){
				var url="?type=shorten&cmd=get_working_days&year="+jQuery("#shYear").val()
					+"&date_start="+jQuery("#shStartDate").val()
					+"&date_end="+jQuery("#shEndDate").val()
					+"&oper=shorten&paid_type="+jQuery("#shpaid_type").val() 
					+"&vacType="+jQuery("#shType").val();
					jQuery("#shworking_days_msg").html("<span style=\"color:red\">Checking. Please wait....</span>");
				
				jQuery.ajax({
				url: url,
				success: function (result) { 
				  resp = jQuery.parseJSON(result); 
				  if (resp.result === "OK")  { 
					jQuery("#shdays").val(resp.days);
					jQuery("#shworking_days_msg").html(resp.days_msg);
					jQuery("#shpaid_type").val(resp.type);
					jQuery("#shbtn").css("visibility","visible");
					
				  }
				  else {
					  jQuery("#shworking_days_msg").html("");
					  jQuery("#shbtn").css("visibility","hidden");
					  myalert("Alert",resp.message);
				  }
				  
				if(jQuery("#shStartDate").val()==shdateStart && jQuery("#shEndDate").val()==shdateEnd)
				jQuery("#shbtn").css("visibility","hidden");
				}
				
				});
			}
			else {
				jQuery("#shworking_days_msg").html("");
				jQuery("#shbtn").css("visibility","hidden");
			}
		}
	 </script>';
	}	
		echo '<div class="years" style="display:none;" id="yeardiv'.$year.'">';
			
		if ($year==date('Y') || $year==date('Y')+1) {
			echo '<div style="width:100%;margin:0 auto;padding:10px;padding-top:0;text-align:left;
			border:1px solid #CCC;border-radius:5px">';
		
			echo '<div style="position:relative;top:15px;width:100%;text-align:center;margin:0 auto;padding:0">
			<p style="height:1px;margin:0;padding:0;clear:both;"></p>
			<h4>Request days off</h4>
			<h4>'.$year.'</h4>
			<hr>
			<form id="form'.$year.'" method="POST" action="?cmd=save">
			
			
			<div class="row">
			<div class="col-sm-6">
			
			<div style="width:100%;text-align:left">
			<input type="hidden" name="year" value="'.$year.'">
			<label style="width:150px">Type of days off:</label>
			<select  onchange="DayChanged(this)" data-year="'.$year.'" name="vacType" id="vacType'.$year.'" 
			style="border:1px solid #CCC;width:215px;padding:2px;border-radius:4px" name="type" >
			<option value="Vacation">Vacation</option>
			<option value="Sick leave">Sick leave</option>
			<option value="CE">Continuing Education</option>
			<option value="Jury duty">Jury Duty</option>
			</select>
			<br>
			<label style="width:150px;margin-top:5px">Paid days left:</label>
			<span style="margin-top:5px">Vacation/Sick leave: <b>'.$remain1 .'</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; CE: <b>'.$remain2.'</b></span>
			<br>
			
			
			
			<label style="width:150px">Date(s):</label>
			<div id="daterange'.$year.'" style="position:relative;top:5px;display:inline-block;width:200px">
				
					<div  class="input-group input-daterange">
						<input onchange="DayChanged(this)" data-year="'.$year.'" class="date" name="startDate" 
						id="startDate" style="width:95px;background:#FFF;text-align:left;padding:2px;height:1.5em;border:1px solid #CCC" type="text" class="form-control" placeholder="Start date" readonly>
						<div style="height:1.5em;padding:2px;border-top:1px solid #CCC;border-bottom:1px solid #CCC"
						class="input-group-addon">&nbsp;to&nbsp; </div>
						<input onchange="DayChanged(this)" data-year="'.$year.'" class="date" name="endDate" 
						id="endDate" style="width:95px;background:#FFF;text-align:left;padding:2px;height:1.5em;border:1px solid #CCC"  type="text" class="form-control" placeholder="End date" readonly>
					</div>	
			</div>
			
			<span
					style="display:inline-block;position:relative;top:-3px;padding-top:0;padding-bottom:1px;margin-left:20px" 
					class="btn btn-primary" 
					onclick="clearMyDates(\''.$year.'\')">Clear</span>
			</div>
			<span style="display:inline-block;margin-top:15px" >
			<input style="cursor:pointer;position:relative;top:1px" id="half_day" type="checkbox" name="half_day" value="half_day">
			<label style="cursor:pointer;" for="half_day">Half a day only</label>
			</span>
			
</div >';//sm6

			
			
			
			
			
			echo '<div class="col-sm-6">
			<div style="width:100%;text-align:left">';
			
			echo '<b>US holidays: </b>';
			
			//if ($year == date('Y')) 
			echo '<span >'.
			self::listHolidays(cf::getFederalHolydays($year))
			.'</span>'; 
			//else echo '<span >'
			//.self::listHolidays(cf::getFederalHolydays($year+1))
			//.'</span>';
			
			echo '<br><b>Block-out dates: </b>';
			//if ($year==date('Y')) 
			echo '<span >'.self::listDays(DaysOffFrontModel::getBlockOutDates(AppModel::$usr['afID'],$year),'bod').'</span>'; 
			//else echo '<span >'.self::listDays(DaysOffFrontModel::getBlockOutDates(AppModel::$usr['afID'],$year+1),'bod').'</span>';
			echo '<br><b>Non-working days: </b>';
			//if ($year==date('Y')) 
				echo '<span >'.self::listDays(DaysOffFrontModel::getDaysOffDates(AppModel::$usr['afID'],$year),'nwd').'</span>'; 
			//else 
			//echo '<span >'.self::listDays(DaysOffFrontModel::getDaysOffDates(AppModel::$usr['afID'],$year+1),'nwd').'</span>';
		   echo '<br><b>Approved days off: </b>';
		   if ($year==date('Y')) echo 
			'<span >'.self::listDays(DaysOffFrontModel::getVacDates(AppModel::$usr['afID'],$year),'vr').'</span>'; 
			else 
			echo '<span >'.self::listDays(DaysOffFrontModel::getVacDates(AppModel::$usr['afID'],$year),'vr').'</span>';;
			echo '</div>';
			
			echo '</div>';//sm6
			echo '</div>';//row
			
			
			echo '<div style="text-align:left">
			<input type="hidden" name="paid_type" readonly id="paid_type'.$year.'">
			<label style="width:150px">&nbsp;</label><span id="working_days_msg'.$year.'"></span>
			
			
			<label style="width:150px">&nbsp;</label>
			<input style="border:0px solid #CCC" type="hidden" name="days" id="working_days'.$year.'">
			<br>
			</div>
			
			</form>';
			
			
			echo '<div class="row">
			<div class="col-sm-6" style="text-align:left">
			<div id="btn'.$year.'" >
			<label style="width:150px;">&nbsp;</label>
			<input type="button" class="btn btn-primary" value="Send request" onclick="checkSubmit('.$year.')">
			<br><br>
			</div>
			
            If you need more days than the paid days remaining, please submit 2 requests - one for all paid days left and the rest as unpaid.<br><br>
			</div></div>
			
			
			</div></div>
			';
		}
		
		//if ($year >= date('Y')) $add = ' (as is today)'; else $add = '';
        $add='';
		echo '<div style="margin:0 auto;width:100%;text-align:left">';
		echo '<table class="white_table" style="width:100%">';
		echo '<thead><tr><th style="text-align:center">Year</th><th></th><th style="text-align:center">Allowed</th>
		<th style="text-align:center">Used</th><th style="text-align:center">Remaining</th></tr></thead>';
		echo '<tbody><tr><td style="text-align:center">'.$year.'</td><td>Paid days Vacation / sick leave'.$add.'</td>
		<td style="text-align:right">'.val::mynumber_format($total1,1).'</td>
		<td style="text-align:right">'.val::mynumber_format($used1,1).'</td>
		<td style="text-align:right">'.val::mynumber_format($remain1,1).'</td></tr>';
		echo '<tr><td style="text-align:center">'.$year.'</td><td>Paid days CE</td><td style="text-align:right">'.val::mynumber_format($total2,1).'</td>
		<td style="text-align:right">'.val::mynumber_format($used2,1).'</td><td style="text-align:right">'.val::mynumber_format($remain2,1).'</td></tr>';
		
		echo '<tr><td style="text-align:center">'.$year.'</td>
		<td>Unpaid days used <td>
		</td><td style="text-align:right">'.val::mynumber_format($used3,1).'</td><td></td></tr>';
		/******************
		echo '<script>
		jQuery( document ).ready(function() {
			var datesArray'.$year.'=['.$unpaidStr.'];
			jQuery(function(){
				jQuery("#permonth-unpaid'.$year.'").datepicker({
					 startDate:"1/1/'.$year.'",
					 endDate:"12/31/'.$year.'",
					 inline: true,
					 toggleActive:true,
					 title:"Unpaid days per '.$year.'",
					 beforeShowDay: function (date) {
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									
									if(jQuery.inArray(theday, datesArray'.$year.')>=0)
									return  "unpaidDate";
								   else
									return "";
						}
				});
			});
		});
		</script>';
		
		echo '<tr><td style="text-align:center">'.$year.'</td><td>Sick leave unpaid days used <a style="float:right" href="javascript:void(0)" 
		onclick="showhide_calendar(this)" data-id="permonthSL'.$year.'">View by month >>></a><br>
		<div style="width:280px;float:right;display:none;background-color:#FFF" id="permonthSL'.$year.'" class="permonth" data-date="1/1/'.$year.'"></div>
		<td>
		</td><td style="text-align:right">'.$usedSL.'</td><td></td></tr>';
		echo '<script>
		jQuery( document ).ready(function() {
			var datesArraySL'.$year.'=['.$unpaidSLStr.'];
			jQuery(function(){
				jQuery("#permonthSL'.$year.'").datepicker({
					 startDate:"1/1/'.$year.'",
					 endDate:"12/31/'.$year.'",
					 inline: true,
					 toggleActive:true,
					 title:"Sick leave unpaid '.$year.'",
					 beforeShowDay: function (date) {
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									if(jQuery.inArray(theday, datesArraySL'.$year.')>=0)
									return  "unpaidDate";
								   else
									return "";
						}
				});
			});
		});
		</script>';
		
		echo '<tr><td style="text-align:center">'.$year.'</td>
		<td>CE unpaid days used <a style="float:right" href="javascript:void(0)" 
		onclick="showhide_calendar(this)" data-id="permonthCE'.$year.'">View by month >>></a><br>
		<div style="width:280px;float:right;display:none;background-color:#FFF" id="permonthCE'.$year.'" class="permonth" data-date="1/1/'.$year.'"></div>
		<td>
		</td><td style="text-align:right">'.$usedCE.'</td><td></td></tr>';
		echo '<script>
		jQuery( document ).ready(function() {
			var datesArrayCE'.$year.'=['.$unpaidCEStr.'];
			jQuery(function(){
				jQuery("#permonthCE'.$year.'").datepicker({
					 startDate:"1/1/'.$year.'",
					 endDate:"12/31/'.$year.'",
					 inline: true,
					 toggleActive:true,
					 title:"CE unpaid '.$year.'",
					 beforeShowDay: function (date) {
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									if(jQuery.inArray(theday, datesArrayCE'.$year.')>=0)
									return  "unpaidDate";
								   else
									return "";
						}
				});
			});
		});
		</script>';
		
		echo '<tr><td style="text-align:center">'.$year.'</td><td>Jury duty  unpaid days used <a style="float:right" href="javascript:void(0)" 
		onclick="showhide_calendar(this)" data-id="permonthJD'.$year.'">View by month >>></a><br>
		<div style="width:280px;float:right;display:none;background-color:#FFF" id="permonthJD'.$year.'" class="permonth" data-date="1/1/'.$year.'"></div>
		<td>
		</td><td style="text-align:right">'.$usedJD.'</td><td></td></tr>';
		echo '<script>
		jQuery( document ).ready(function() {
			var datesArrayJD'.$year.'=['.$unpaidJDStr.'];
			jQuery(function(){
				jQuery("#permonthJD'.$year.'").datepicker({
					 startDate:"1/1/'.$year.'",
					 endDate:"12/31/'.$year.'",
					 inline: true,
					 toggleActive:true,
					 title:"Jury duty unpaid '.$year.'",
					 beforeShowDay: function (date) {
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									if(jQuery.inArray(theday, datesArrayJD'.$year.')>=0)
									return  "unpaidDate";
								   else
									return "";
						}
				});
			});
		});
		</script>';
		*****************/
	
		echo '</tbody></table><br><a  class="blue_link" href="javascript:void(0)" 
		onclick="showhide_calendar(this)" data-id="permonth-unpaid'.$year.'">View by month >></a><br>
		<div style="text-align:initial;width:100%;padding:10px;display:none;background-color:#FFF" id="permonth-unpaid'.$year.'" class="permonth" data-date="1/1/'.$year.'">
		<h4>Year333: '.$year.'</h4>';
		DaysOffFrontView::showSummary($year);
		echo '</div>';
		echo '</div>
		<br><br>
		<div style="margin:0 auto;width:100%;text-align:left">';
		$user=AppModel::$usr['afID'];
		echo '<h4>Approved days:</h4>';
		$q="SELECT * FROM vacation_requests WHERE vrAFID={$user} AND vrYear={$year} AND vrState='approved'
			ORDER BY	vrDateStart DESC";
		$reqs=db::PrepareLoad($q);
		
		if ($reqs==false) echo '&nbsp;&nbsp;&nbsp;&nbsp;None<br>';
		else {
			$nmb=0;
			$totalReq=count($reqs);
			foreach ($reqs as $rq){
				$color="#999";
				$add='';
				$today = val::currentDate('Y-m-d');
				
				$pipe=false;
				
				if ( ( (! $isAdmin) && ($rq['vrDateStart'] > $today)) || $isAdmin) { 
					$color='#000';
					$add.='<a class="blue_link" style="float:left;margin-right:5px;margin-left:10px"  href="?cmd=cancel_request&req_id='.$rq['vrID'].'"
					onclick="return confirm(\'Are you sure to cancel this request?\')">cancel days off</a>
					';
					$pipe=true;
				}
	
				if (( (! $isAdmin) && ($rq['vrDateEnd'] > $today )) || $isAdmin ) { 
					if ($rq['vrDays']>1){
						if ($pipe) $add.='<span style="float:left;display:inline-block">|</span>';
						$add.='<a class="blue_link" style="float:left;margin-left:5px;"  href="javascript:void(0)"
						onclick="initShorten(\''.$rq['vrID'].'\' ,\''.$year.'\' ,\''
						.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateStart']).'\',\''
						.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateEnd']).'\','
						.'\''.$rq['vrType'].'\',\''.$rq['vrPaydUnpaid'].'\')">shorten</a>';
				    }
				}
				
				
				if ( ( (! $isAdmin) && ($rq['vrDateStart'] > $today)) || $isAdmin ) {	
					if ($rq['vrDays']==1 || $rq['vrDays']==0.5) {
						$daysStr=val::mynumber_format($rq['vrDays'],1).' day: ';
						if($rq['vrDays']==0.5)  $daysStr=' HALF DAY:';
						echo "<span style=\"width:570px;display:block;margin-bottom:5px;\">
						&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"display:inline-block\">"
						.$rq['vrType'].', '.$rq['vrPaydUnpaid'].', '.$daysStr.' '
						.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateStart'])
						."</span><span style=\"float:right\">{$add}</span></span>";
					}
					else {
						$daysStr=val::mynumber_format($rq['vrDays'],1).' days: ';
						echo "<span style=\"width:570px;display:block;margin-bottom:5px;\">
						&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"display:inline-block\">"
						.$rq['vrType'].', '.$rq['vrPaydUnpaid'].', '.$daysStr.' '
						.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateStart']).' - '
						.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateEnd'])
						."</span><span style=\"float:right\">{$add}</span></span>";
					}
				}
				
				if ((! $isAdmin) && ($rq['vrDateEnd'] <= $today)) {
					$daysStr=val::mynumber_format($rq['vrDays'],1).' day: ';
					echo "<span style=\"width:570px;display:block;margin-bottom:5px;\">
						&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"display:inline-block\">"
						.$rq['vrType'].', '.$rq['vrPaydUnpaid'].', '.$daysStr.' '
						.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateStart'])
						."</span></span>";
				}
				
				
				$nmb++;
				if ($nmb == 3 && $totalReq>3) echo '&nbsp;&nbsp;&nbsp;&nbsp;<a class="blue_link" href="javascript:void(0)" 
				onclick="vievhideRequests(this,\'#approved'.$year.'\')">
				Show more >></a><div style="display:none" id="approved'.$year.'">';
			}
			if ($nmb >= 3 && $totalReq>3)echo "</div>";
		}
		echo '<hr>';
		
		echo '<h4>Pending requests:</h4>';
		$user=AppModel::$usr['afID'];
		
		$q="SELECT * FROM vacation_requests WHERE vrAFID={$user} AND vrYear={$year} AND vrState='pending'
			ORDER BY	vrDateStart DESC";
			
		$reqs=db::PrepareLoad($q);
		
		if ($reqs==false) echo '&nbsp;&nbsp;&nbsp;&nbsp;None<br>';
		else {
			$nmb=0;
			$totalReq=count($reqs);
			foreach ($reqs as $rq){
				$color="#999";
				$add='';
				$today = val::currentDate('Y-m-d');
				
				$pipe=false;
				
				if ($rq['vrDateStart'] > $today) {
					$color='#000';
					$add.='<a class="blue_link" style="float:left;margin-right:5px;margin-left:10px"  href="?cmd=cancel_request&req_id='.$rq['vrID'].'"
					onclick="return confirm(\'Are you sure to cancel this request?\')">cancel days off</a>
					';
					$pipe=true;
				}
	
				if ($rq['vrDateEnd'] > $today ){
				if ($pipe && $rq['vrDays']>1) $add.='<span style="float:left;display:inline-block">|</span>';
				
				if ($rq['vrDays']>1)
				$add.='<a class="blue_link" style="float:left;margin-left:5px;"  href="javascript:void(0)"
				onclick="initShorten(\''.$rq['vrID'].'\' ,\''.$year.'\' ,\''
				.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateStart']).'\',\''
				.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateEnd']).'\','
				.'\''.$rq['vrType'].'\',\''.$rq['vrPaydUnpaid'].'\')">shorten</a>';
				}
			
				
				if ($rq['vrDays']==1 || $rq['vrDays']==0.5) {
					$daysStr=val::mynumber_format($rq['vrDays'],1).' day: ';
					if($rq['vrDays']==0.5)  $daysStr=' HALF DAY:';
					echo "<span style=\"width:570px;display:block;margin-bottom:5px;\">
					&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"display:inline-block\">"
					.$rq['vrType'].', '.$rq['vrPaydUnpaid'].', '.$daysStr.' '
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateStart'])
					."</span><span style=\"float:right\">{$add}</span></span>";
				}
				else {
					$daysStr=val::mynumber_format($rq['vrDays'],1).' days: ';
					echo "<span style=\"width:570px;display:block;margin-bottom:5px;\">
					&nbsp;&nbsp;&nbsp;&nbsp;<span style=\"display:inline-block\">"
					.$rq['vrType'].', '.$rq['vrPaydUnpaid'].', '.$daysStr.' '
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateStart']).' - '
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateEnd'])
					."</span><span style=\"float:right\">{$add}</span></span>";
				}
				
				
				
				$nmb++;
				if ($nmb == 3 && $totalReq>3) echo '&nbsp;&nbsp;&nbsp;&nbsp;<a class="blue_link" href="javascript:void(0)" 
				onclick="vievhideRequests(this,\'#pending'.$year.'\')">
				Show more >></a><div style="display:none" id="pending'.$year.'">';
			}
			if ($nmb >= 3 && $totalReq>3)echo "</div>";
		}
		echo '<hr>';
		echo '<h4>Declined requests:</h4>';
		$q="SELECT * FROM vacation_requests WHERE vrAFID={$user} AND vrYear={$year} AND vrState='declined'
			ORDER BY	vrDateStart DESC";
		$reqs=db::PrepareLoad($q);
		if ($reqs==false) echo '&nbsp;&nbsp;&nbsp;&nbsp;None<br>';
		else {
			$nmb=0;
			$totalReq=count($reqs);
			foreach ($reqs as $rq){
				if ($rq['vrDateStart'] <= val::currentDate('Y-m-d')) $color='#999';
				else $color="#000";
				
				if ($rq['vrDays']==1 || $rq['vrDays']==0.5) {
					$daysStr=val::mynumber_format($rq['vrDays'],1).' day: '; 
					if($rq['vrDays']==0.5)  $daysStr=' HALF DAY:';
					echo "
					<span style=\"display:block;margin-bottom:5px;\">"
					.$rq['vrType'].', '.$rq['vrPaydUnpaid'].', '.$daysStr.' '
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateStart'])
					.' &nbsp;&nbsp;Reason: '.$rq['vrDeclineReason'].'</span>
					 </span>';
				}
				else {
					$daysStr = val::mynumber_format($rq['vrDays'],1).' days:';
					echo "
					<span style=\"display:block;margin-bottom:5px;\">"
					.$rq['vrType'].', '.$rq['vrPaydUnpaid'].', '.$daysStr.' '
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateStart']).' - '
					.val::convDate(MYSQL_DATE,USER_DATE,$rq['vrDateEnd'])
					.' &nbsp;&nbsp;Reason: '.$rq['vrDeclineReason'].'</span>
					 </span>';
				}
				 $nmb++;
				if ($nmb == 3 && $totalReq>3) echo '&nbsp;&nbsp;&nbsp;&nbsp;<a class="blue_link" href="javascript:void(0)" 
				onclick="vievhideRequests(this,\'#declined'.$year.'\')">
				Show more >></a><div style="display:none" id="declined'.$year.'">';
			}
			if ($nmb >= 3 && $totalReq>3)echo "</div>";
		}
		echo '<hr>';
		
		
		
		echo '</div>
		</div>';
		}//  end years divs
		
		$endDate = date('Y-m-d', strtotime('+6 months'));
		
		echo '
		<script>
		function vievhideRequests(that,id){
			if (jQuery(id).is(":visible")) {
				jQuery(id).slideUp();
				jQuery(that).text("Show more >>");
			}
			else {
				jQuery(id).slideDown();
				jQuery(that).text("Hide more >>");
			}
		}
		
		function checkSubmit(year){ 
			if (jQuery("#working_days_msg"+year).html()=="")
			myalert("Alert","Please select dates.");
			else {
				if ( jQuery("#form"+year+" #startDate").val() != jQuery("#form"+year+" #endDate").val()
					&& jQuery("#form"+year+" #half_day").is(":checked"))
				{
				alert("You\'ve selected HALF-DAY - please select only 1 date.");
				return false;
				}
				else 
				jQuery("#form"+year).submit();
			}
		}
		
		jQuery( document ).ready(function() {
		';
		if (isset($go_to_year)) echo 'jQuery("#year_select").val("'.$go_to_year.'");';
		
		
		
		if($year=date('Y')){
		echo 'yearChange();
			var _endDate = new Date("'.$endDate.'T24:00");
			
			var startStartDate'.date("Y").'="'.date(USER_DATE).'";
			var startEndDate'.date("Y").' = "12/31/'.date("Y").'";
			
			jQuery("#form'.date("Y").' #startDate").datepicker({ 
				format: "m/d/yyyy", 
				endDate:"12/31/'.date("Y").'",	  
				startDate:"'.date(USER_DATE).'",
				//clearBtn:true,
				todayHighlight:true,
				title:"Please select a start date",
				autoclose:true,
				beforeShowDay: function (date) {
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									var ret={enabled:true};
									
									if(jQuery.inArray(theday, blockOutDatesArray'.($year).')>=0){ 
										ret={
										enabled:false,
										classes:"blockOutDate",
										tooltip:"Block out date"
										};
									};
									if(jQuery.inArray(theday, daysOffDatesArray'.($year).')>=0){ 
										ret={
										enabled:true,
										classes:"dayOFFDate",
										tooltip:"Mom-working date"
										
										};
									};
									
									if(jQuery.inArray(theday, fedHolidaysArray'.($year).')>=0){
									//var date=theday.split("/");
										ret={
										enabled:true,
										classes:"fedHoly",
										tooltip:"US holiday"
										};
										
									}
									if(jQuery.inArray(theday, vacDatesArray'.($year).')>=0){ 
									ret={
										enabled:false,
										classes:"vacDate",
										tooltip:"Approved day off"
										};
									}
									if (date.valueOf() < _endDate.valueOf()) {_ret = true;}
									else _ret=false;
									
									ret["enabled"] = _ret;
									return ret;

						}
			},"clearDates")
			.on("changeDate", function(selected){
				var typeOfReq=jQuery("#vacType'.$year.'").val();
				var date = selected.format(0,"m/d/yyyy");
			
				if (typeOfReq !="Jury duty" && typeOfReq !="Sick leave" 
				&&  jQuery.inArray(date, blockOutDatesArray'.$year.')>=0){
					myalert("Alert","You can not select block-out dates except if request is for Jury duty or Sick leave.");
					jQuery("#form'.date("Y").' #startDate").val("");
					e.preventDefault();
					
				}
								

				startDate = new Date(selected.date.valueOf());
				startDate.setDate(startDate.getDate(new Date(selected.date.valueOf())));
				jQuery("#form'.date("Y").' #endDate").datepicker("setStartDate", startDate);
			})
			.on("clearDate", function(selected){
				jQuery("#form'.date("Y").' #startDate").datepicker("setStartDate", "'.date(USER_DATE).'");
			});
			
			var endStartDate'.date("Y").'="'.date(USER_DATE).'";
			var endEndDate'.date("Y").' = "12/31/'.date("Y").'";
			
			jQuery("#form'.date("Y").' #endDate").datepicker({ 
			  format: "m/d/yyyy", 
			  startDate:"'.date(USER_DATE).'",
			  endDate:"12/31/'.date("Y").'",
			  //clearBtn:true,			  
			  todayHighlight:true,
			  title:"Please select an end date",
			  autoclose:true,
			  beforeShowDay: function (date) {
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									var ret={enabled:true};
									
									if(jQuery.inArray(theday, blockOutDatesArray'.($year).')>=0){ 
										ret={
										enabled:false,
										classes:"blockOutDate",
										tooltip:"Block out date"
										};
									};
									if(jQuery.inArray(theday, daysOffDatesArray'.($year).')>=0){ 
										ret={
										enabled:true,
										classes:"dayOFFDate",
										tooltip:"Mom-working date"
										
										};
									};
									
									if(jQuery.inArray(theday, fedHolidaysArray'.($year).')>=0){
									//var date=theday.split("/");
										ret={
										enabled:true,
										classes:"fedHoly",
										tooltip:"US holiday"
										};
										
									}
									if(jQuery.inArray(theday, vacDatesArray'.($year).')>=0){ 
									ret={
										enabled:false,
										classes:"vacDate",
										tooltip:"Approved day off"
										};
									}
									if (date.valueOf() < _endDate.valueOf()) {_ret = true;}
									else _ret=false;
									
									ret["enabled"] = _ret;
									return ret;

						}
			},"clearDates")
			
			.on("changeDate", function(selected){
				var typeOfReq=jQuery("#vacType"+'.$year.').val();
				var date = selected.format(0,"m/d/yyyy");
				
				if (typeOfReq !="Jury duty" && typeOfReq !="Sick leave" 
				&&  jQuery.inArray(date, blockOutDatesArray'.$year.')>=0){
					myalert("Alert","You can not select block-out dates except if request is for Jury duty or Sick leave.");
					jQuery("#form'.date("Y").' #endDate").val("");
					e.preventDefault();
				}
				startDate = new Date(selected.date.valueOf());
				startDate.setDate(startDate.getDate(new Date(selected.date.valueOf())));
				jQuery("#form'.date("Y").' #startDate").datepicker("setEndDate", startDate);
				
			})
			.on("clearDate", function(selected){
				jQuery("#form'.date("Y").' #startDate").datepicker("setEndDate", "12/31/'.date("Y").'");
				jQuery("#form'.date("Y").' #endDate").datepicker("setEndDate", "12/31/'.date("Y").'");
			});
				
			/*********************************/
			
			var startStartDate'.(date('Y')+1).'="1/1/'.(date('Y')+1).'";
			var startEndDate'.(date('Y')+1).' = "12/31/'.(date('Y')+1).'";
			
			
			jQuery("#form'.(date('Y')+1).' #startDate").datepicker({ 				
				format: "m/d/yyyy",    
				startDate:"1/1/'.(date('Y')+1).'",
				endDate:"12/31/'.(date('Y')+1).'",
				defaultViewDate:"1/1/'.(date('Y')+1).'",				
				//clearBtn:true,
				todayHighlight:true,
				title:"Please select a start date",
				autoclose:true,
				beforeShowDay: function (date) {
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									var ret={enabled:true};
									
									if(jQuery.inArray(theday, blockOutDatesArray'.($year+1).')>=0){ 
										ret={
										enabled:false,
										classes:"blockOutDate",
										tooltip:"Block out date"
										};
									};
									if(jQuery.inArray(theday, daysOffDatesArray'.($year+1).')>=0){ 
										ret={
										enabled:true,
										classes:"dayOFFDate",
										tooltip:"Mom-working date"
										
										};
									};
									
									if(jQuery.inArray(theday, fedHolidaysArray'.($year+1).')>=0){
									//var date=theday.split("/");
										ret={
										enabled:true,
										classes:"fedHoly",
										tooltip:"US holiday"
										};
										
									}
									if(jQuery.inArray(theday, vacDatesArray'.($year+1).')>=0){ 
									ret={
										enabled:false,
										classes:"vacDate",
										tooltip:"Approved day off"
										};
									}
									if (date.valueOf() < _endDate.valueOf()) {_ret = true;}
									else _ret=false;
									
									ret["enabled"] = _ret;
									return ret;

						}
			},"clearDates")
			.on("changeDate", function(selected){
				var typeOfReq=jQuery("#vacType"+'.$year.').val();
				var date = selected.format(0,"mm/dd/yyyy");
				
				if (typeOfReq !="Jury duty" && typeOfReq !="Sick leave" 
				&&  jQuery.inArray(date, blockOutDatesArray'.$year.')>=0){
					myalert("Alert","You can not select block-out dates except if request is for Jury duty or Sick leave.");
					jQuery("#form'.(date("Y")+1).' #endDate").val("");
					e.preventDefault();
				}
				startDate2 = new Date(selected.date.valueOf());
				startDate2.setDate(startDate2.getDate(new Date(selected.date.valueOf())));
				jQuery("#form'.(date("Y")+1).' #endDate").datepicker("setStartDate", startDate2);
			})
			.on("clearDate", function(selected){
				jQuery("#form'.(date("Y")+1).' #startDate").datepicker("setStartdDate", "1/1/'.(date('Y')+1).'");
			});			
		
		var endStartDate'.(date('Y')+1).'="1/1/'.(date('Y')+1).'";
		var endEndDate'.(date('Y')+1).' = "12/31/'.(date('Y')+1).'";
			
		jQuery("#form'.(date('Y')+1).' #endDate").datepicker({ 
				format: "m/d/yyyy", 
				startDate:"1/1/'.(date('Y')+1).'",
				endDate:"12/31/'.(date('Y')+1).'",
				defaultViewDate:"1/1/'.(date('Y')+1).'",	
				//clearBtn:true,				
				todayHighlight:true,
				title:"Please select an end date",
				autoclose:true,
				beforeShowDay: function (date) {
						var theday = (date.getMonth()+1) +"/"+ 
									date.getDate()+ "/" + 
									date.getFullYear();
									var ret={enabled:true};
									
									if(jQuery.inArray(theday, blockOutDatesArray'.($year+1).')>=0){ 
										ret={
										enabled:false,
										classes:"blockOutDate",
										tooltip:"Block out date"
										};
									};
									if(jQuery.inArray(theday, daysOffDatesArray'.($year+1).')>=0){ 
										ret={
										enabled:true,
										classes:"dayOFFDate",
										tooltip:"Mom-working date"
										
										};
									};
									
									if(jQuery.inArray(theday, fedHolidaysArray'.($year+1).')>=0){
									//var date=theday.split("/");
										ret={
										enabled:true,
										classes:"fedHoly",
										tooltip:"US holiday"
										};
										
									}
									if(jQuery.inArray(theday, vacDatesArray'.($year+1).')>=0){ 
									ret={
										enabled:false,
										classes:"vacDate",
										tooltip:"Approved day off"
										};
									}
									if (date.valueOf() < _endDate.valueOf()) {_ret = true;}
									else _ret=false;
									
									ret["enabled"] = _ret;
									return ret;

						}
			},"clearDates")
			.on("changeDate", function(selected){
				var typeOfReq=jQuery("#vacType"+'.$year.').val();
				var date = selected.format(0,"mm/dd/yyyy");
				
				if (typeOfReq !="Jury duty" && typeOfReq !="Sick leave" 
				&&  jQuery.inArray(date, blockOutDatesArray'.$year.')>=0){
					myalert("Alert","You can not select block-out dates except if request is for Jury duty or Sick leave.");
					jQuery("#form'.(date("Y")+1).' #endDate").val("");
					e.preventDefault();
				}
				FromEndDate2 = new Date(selected.date.valueOf());
				FromEndDate2.setDate(FromEndDate2.getDate(new Date(selected.date.valueOf())));
				jQuery("#form'.(date("Y")+1).' #startDate").datepicker("setEndDate", FromEndDate2);
			})
			.on("clearDate", function(selected){
				jQuery("#form'.(date("Y")+1).' #startDate").datepicker("setEndDate", "12/31/'.(date("Y")+1).'");
				jQuery("#form'.(date("Y")+1).' #endDate").datepicker("setEndDate", "12/31/'.(date("Y")+1).'");
			});
		});
		
		function DayChanged(that){//alert(222);
			var year=jQuery(that).data("year");
			if (jQuery("#form"+year+" #startDate").val()!="" && jQuery("#form"+year+" #endDate").val()!=""){
				var url="?cmd=get_working_days&year="+year
					+"&date_start="+jQuery("#form"+year+" #startDate").val()
					+"&date_end="+jQuery("#form"+year+" #endDate").val()
					+"&vacType="+jQuery("#vacType"+year).val();
					jQuery("#working_days_msg"+year).html("<span style=\"color:red\">Checking. Please wait....</span>");
				
				jQuery.ajax({
				url: url,
				success: function (result) { //alert(result);
				  resp = jQuery.parseJSON(result); 
				  if (resp.result === "OK")  {
					jQuery("#working_days"+year).val(resp.days);
					jQuery("#working_days_msg"+year).html(resp.days_msg);
					jQuery("#paid_type"+year).val(resp.type);
					
				  }
				  else {
					  jQuery("#working_days_msg"+year).html("");
					  //jQuery("#btn"+year).css("visibility","hidden");
					  myalert("Alert",resp.message);
				  }
				}
				});
			}
			else {
				jQuery("#working_days_msg"+year).html("");
				//jQuery("#btn"+year).css("visibility","hidden");
			}
		}
		
		function clearMyDates(year){ 
			jQuery(\'#form\'+year+\' #startDate\').val(\'\').datepicker("update");
			jQuery(\'#form\'+year+\' #endDate\').val(\'\').datepicker("update");
			jQuery("#working_days_msg"+year).html("");
			//jQuery("#btn"+year).css("visibility","hidden");
			
			jQuery("#form"+year+" #startDate").datepicker("setStartDate", eval(startStartDate+year));
			jQuery("#form"+year+" #startDate").datepicker("setEndDate", eval(startEndDate+year));
			
			jQuery("#form"+year+" #endDate").datepicker("setStartDate", eval(endStartDate+year));
			jQuery("#form"+year+" #endDate").datepicker("setEndDate", eval(endtartDate+year));
		}
		
		function showhide_calendar(that){
		    var id="#"+jQuery(that).data("id");
			if (jQuery(id).is(":visible")) {jQuery(that).text("View by month >>");jQuery(id).slideUp();}
			else {jQuery(that).text("Hide view by month >>"); jQuery(id).slideDown();}
			
		}
	
		function show_link(){
			var req=jQuery("#year_select").find(":selected").data("has-request");
			if(req==1) jQuery("#days-off-link").css("visibility","visible"); 
			else jQuery("#days-off-link").css("visibility","hidden");
		}
		
		function yearChange(){
			var sel = jQuery("#years_select").find(":selected").text();
			jQuery(".years").fadeOut();
			jQuery("#yeardiv"+sel).fadeIn();
			show_link();
		}
		
		</script>';
		}// current year
	}
/*******
,
										content:"<img class=\'usflag\' src=\'/add/images/US_flag.svg\'><div class=\'holidayspan\'>"
										+date[1]+"</div>"
*********/
	public static function showSummary($year){
		$userID = AppModel::$usr['afID'];
		for ($m = 1; $m <= 12; $m++){
			
			echo '<span style="display:inline-block;width:120px">'.date( 'F',strtotime($year.'-'.$m.'-1')).'</span>';
			/*
			$vSick= DaysOffFrontModel::getUsedDaysPerMonth($userID,$year,$m,['Vacation','Sick leave'],$paidUnpaid='paid');
			$ce= DaysOffFrontModel::getUsedDaysPerMonth($userID,$year,$m,['CE'],$paidUnpaid='paid');
			$jd=DaysOffFrontModel::getUsedDaysPerMonth($userID,$year,$m,['Jury duty'],$paidUnpaid='paid');
			
			if ($vSick>0) $vSick='<span style="font-weight:bold">'.$vSick.'</span>';
			if ($ce>0) $ce='<span style="font-weight:bold">'.$ce.'</span>';
			if ($jd>0) $fd='<span style="font-weight:bold">'.$jd.'</span>';
			
			echo "  Vacation / Sick leave: <span style=\"display:inline-block;width:50px;\">{$vSick}</span>
			CE: <span style=\"display:inline-block;width:50px;\">{$ce}</span>
			Jury Duty: <span style=\"display:inline-block;width:50px;\">{$jd}</span><br>";
			*/
			$vPaid= DaysOffFrontModel::getPaidDaysPerMonth($userID,$year,$m);
			$vUnpaid= DaysOffFrontModel::getUnpaidDaysPerMonth($userID,$year,$m);
			
			if ($vPaid>0) $vPaid='<span style="font-weight:bold">'.$vPaid.'</span>';
			if ($vUnpaid>0) $vUnpaid='<span style="font-weight:bold">'.$vUnpaid.'</span>';
			echo "  Paid: <span style=\"display:inline-block;width:50px;\">{$vPaid}</span>
			Unpaid: <span style=\"display:inline-block;width:50px;\">{$vUnpaid}</span><br>";
			
		}
	}
	
	public static function listDays($days,$prefix){
		//cf::pr($days);
		
		if ($days==false) return ' None';
		
		$s=array();
		foreach($days as $k=>$r){
			$d1=val::convDate(MYSQL_DATE,'m/d',$r[$prefix.'DateStart']);
			$d2=val::convDate(MYSQL_DATE,'m/d',$r[$prefix.'DateEnd']);
			//echo '****'.$d1.'-',$d2.'<br>';
			if ($d1 == $d2){
				if (isset($r['vrDays']) && $r['vrDays']==0.5)
				$s[]=$d1.'(HALF DAY)';
				else
				$s[]=$d1;
			}
			else 
			$s[]=$d1.'-'.$d2;
		}
		
		return implode(', ',$s);
	}
	
	public static function listHolidays($hdarray){
		foreach ($hdarray as $k=>$d){
			$hdarray[$k]=val::convDate(MYSQL_DATE,'m/d',$d);
		}
		
		return implode(', ',$hdarray);
		
	}
	
}


