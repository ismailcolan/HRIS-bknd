<?php
/** The CompanyOffice controller */
class PayrollReportController extends AppController {

    const __DUMP__ = false;
 
    public static $id, $data, $pg;

	  final static function  CheckReport(){
		$ret=array();
		$startDate=val::convDate(USER_DATE,MYSQL_DATE,$_GET['sd']);
		$endDate=val::convDate(USER_DATE,MYSQL_DATE,$_GET['ed']);
		
		$dayofweek = date('w', strtotime($startDate));
		if ($dayofweek == 1) $realStartDate=$startDate;
		else {
			$rd = new DateTime($startDate.' last Monday');
			$realStartDate = $rd->format('Y-m-d');
	    }
		if($_GET['comp']==0) $add=''; else $add='AND afCompany ='.$_GET['comp'];
		$q ="SELECT * FROM apply_forms 
		LEFT JOIN p_timeclock_worksegment ON EmployeeNumber = afEmplNumber
		LEFT JOIN p_listofweeks ON EMPLOYEE_NUMBER = afEmplNumber
		WHERE afEmplNumber !='' AND afID > 1 AND afSalaryType = 'Hourly' ".$add."
		AND SIGNEDBY_MANAGERID IS NULL
		AND STARTDATE ='{$realStartDate}' AND ENDDATE='{$endDate}'
		AND TimeIn >= '{$realStartDate} 00:00:00' AND TimeOut <= '{$endDate} 23:59:59'
		AND afSalaryType='Hourly'
		AND ((`afActive`='Hired') OR (`afActive`='Terminated' AND `afDateLeaving`>='{$startDate}')) AND `afNoPayrollJA`='no'";
		
		$r=db::PrepareLoad($q);
		
		if (count($r)) $ret['result']=false; else $ret['result']=true;
		
		echo json_encode($ret);
	  }
	  
	
	  
	  final static function  ListReport(){ 
		$startDate=val::convDate(USER_DATE,MYSQL_DATE,$_GET['sd']);		
		$endDate=val::convDate(USER_DATE,MYSQL_DATE,$_GET['ed']);
		
		$companies=CompaniesModel::GetActiveCompaniesLong();
		
		if($_GET['comp']==0) $company='AllCompanies'; else $company=$companies[$_GET['comp']];
		$outFilename = 'payroll '.$company.' '.$startDate.' - '.$endDate.' ( '.date('M j Y, h-i A').' )';//s
		$dayofweek = date('w', strtotime($startDate));
		if ($dayofweek == 1) $realStartDate=$startDate;
		else {
			$rd = new DateTime($startDate.' last Monday');
			$realStartDate = $rd->format('Y-m-d');
	    }
		
		$eData=array();
		
		//$realStartDate
		//$realEndDate
															
		//if ($_GET['comp'] == '1')  $cID = '19058145'; else $cID = '19058143';
		
		
		if($_GET['comp']==0) $add=''; else $add='AND afCompany ='.$_GET['comp'];
		$query="SELECT * FROM apply_forms

		LEFT JOIN  companies as com ON (comID = afCompany)
		WHERE afEmplNumber !='' AND afID > 1 ".$add."
		
		AND afSalaryType='Hourly'
		AND ((`afActive`='Hired') OR (`afActive`='Terminated' AND `afDateLeaving`>='{$realStartDate}')) AND `afNoPayrollJA`='no'
	
		ORDER BY  CAST(afEmplNumber AS INTEGER) ASC";
		
		
		
		$res=db::prepareLoad($query);
		if ($res===false) $res=array();
		
		
		//$res=self::multi_unique($res);
		
		$eData=array();
		if (empty($res)) {$eData[]=array('No data');}
		else {
			$eData[] = array('CoCode','BatchID','File #','Reg Hours','O/T Hours',
			"Hours 3 Code", "Hours 3 Amount", "Hours 3 Code", "Hours 3 Amount", "Hours 3 Code", "Hours 3 Amount");
		
			$total = array();
			$current=0;
		}	
			
			foreach ($res as $r){
				/////////// sick leave
				$qry2='SELECT * FROM vacation_requests WHERE vrAFID='.$r['afID'].'
				AND vrType="Sick leave" AND vrState="approved" AND vrPaydUnpaid="paid"
				AND (
				       (vrDateStart BETWEEN "'.$startDate.'" AND "'.$endDate.'")
					OR (vrDateEnd BETWEEN "'.$startDate.'" AND "'.$endDate.'")
					OR (("'.$startDate.'" BETWEEN vrDateStart AND vrDateEnd)  AND ("'.$endDate.'" BETWEEN vrDateStart AND vrDateEnd))
					OR ((vrDateStart BETWEEN "'.$startDate.'" AND "'.$endDate.'" ) AND (vrDateEnd BETWEEN "'.$startDate.'" AND "'.$endDate.'"))
					OR (("'.$startDate.'" <= vrDateStart) AND ("'.$endDate.'" >= vrDateEnd) ) 
				)';
			
				$res2=db::prepareLoad($qry2);
				
				if (!is_array($res2)) $res2=array();
				$paidSic = 0;
				foreach($res2 as $r2){
					if ($r2['vrDays'] == 0.5) $pVac = 0.5;
					else {
						if( $r2['vrDateStart'] < $startDate ) $_startDate=$startDate;
						else $_startDate = $r2['vrDateStart'];
						
						if( $r2['vrDateEnd'] < $endDate ) $_endDate=$r2['vrDateEnd'];
						else $_endDate=$endDate;
							
						$pSic = self::deys_between($r2['vrRealDays'],$_startDate,$_endDate);
						//DaysOffFrontModel::getWorkingDaysBetween($r['afID'],$_startDate,$_endDate);
					}
					$paidSic = $paidSic + (real)$pSic;
				}
				
				
				$paidSic = $paidSic * 8;// in hours
				
				//////////   vacation_requests
				$qry2=
				'SELECT * FROM vacation_requests WHERE vrAFID='.$r['afID'].'
				AND vrType="Vacation" AND vrState="approved" AND vrPaydUnpaid="paid"
				AND (
				       (vrDateStart BETWEEN "'.$startDate.'" AND "'.$endDate.'")
					OR (vrDateEnd BETWEEN "'.$startDate.'" AND "'.$endDate.'")
					OR (("'.$startDate.'" BETWEEN vrDateStart AND vrDateEnd)  AND ("'.$endDate.'" BETWEEN vrDateStart AND vrDateEnd))
					OR ((vrDateStart BETWEEN "'.$startDate.'" AND "'.$endDate.'" ) AND (vrDateEnd BETWEEN "'.$startDate.'" AND "'.$endDate.'"))
					OR (("'.$startDate.'" <= vrDateStart) AND ("'.$endDate.'" >= vrDateEnd) ) 
				)';
				
				
				$res2=db::prepareLoad($qry2);
				if (!is_array($res2)) $res2=array();
				$paidVac=0;
				foreach($res2 as $r2){
					if ($r2['vrDays'] == 0.5) $pSic = 0.5;
					else {
						if( $r2['vrDateStart'] < $startDate ) $_startDate=$startDate;
						else $_startDate = $r2['vrDateStart'];
						
						if( $r2['vrDateEnd'] < $endDate ) $_endDate=$r2['vrDateEnd'];
						else $_endDate=$endDate;
							
						$pVac = self::deys_between($r2['vrRealDays'],$_startDate,$_endDate);
						//DaysOffFrontModel::getWorkingDaysBetween($r['afID'],$_startDate,$_endDate);
					}
					$paidVac = $paidVac + (real)$pVac;
				}
				
				$paidVac = $paidVac * 8;// in hours
				
			
				
				if ($r['afEmplNumber'] >  150000000000000) continue;
				
				if($r['comShortName'] == 'MLM') {
					$coCode='HR';
					if($r['afSalaryType'] == 'Annual') $coCode=$coCode.'2';
					if($r['afSalaryType'] == 'Hourly') $coCode=$coCode.'0';
				}
				if($r['comShortName'] == 'KAL') {
					$coCode='HQ';
					if($r['afSalaryType'] == 'Annual') $coCode=$coCode.'X';
					if($r['afSalaryType'] == 'Hourly') $coCode=$coCode.'E';
				}

				$BatchID ='Batch01';
				
				$File = $r['afEmplNumber'];
				
				
				$qry1 = 'SELECT * FROM p_timeclock_worksegment
				WHERE EmployeeNumber ='.$r['afEmplNumber'].
				' AND TimeIn >= "'.$realStartDate.' 00:00:00" AND  TimeIn <= "'.$endDate.' 23:59:59"
				AND TimeOut <= "'.$endDate.' 23:59:59"
				ORDER BY TimeIn ASC' ;
				
				
				
				$res1=db::prepareLoad($qry1);
				if ($res1===false) $res1=array();
				
				
				$realStartDate= $realStartDate.' 00:00:00';
				$total = array();
				$holiday= array();
				$total_real =array();;
				$holiday_real = array();
				
				$current='';
				$weeks=array();
				foreach ($res1 as $r1){
				
					$date = new DateTime($r1['TimeIn']);
					$week = $date->format("W");
					if ( $week != $current ) {
						$weeks[]=$week;
						$total[$week] = 0;
						$holiday[$week]= 0;
						$total_real[$week] = 0;
						$holiday_real[$week] = 0;
						$current = $week;
					}
					
					if (isset($r1['	TypeAdmin'])) $r1['Type']=$r1['	TypeAdmin'];
					if (isset($r1['HousrWorkingAdmin'])) 
						$r1['WorkingHours'] = self::toRealHours($r1['HousrWorkingAdmin']);
					else 
						$r1['WorkingHours']= self::toRealHours($r1['WorkingHours']);
					
					if ( $r1['Type']=='Work') {
						$total[$week]=$total[$week]+ $r1['WorkingHours'];
						//$total_real[$week]=$total_real[$week]+$r1['WorkingHours'];
						if ($r1['TimeIn'] < $startDate) {
							continue;
						} else {
							$total_real[$week]=$total_real[$week]+$r1['WorkingHours'];
						}
						
					}
				
					if ( $r1['Type']=='Holiday') {
						$r1['WorkingHours']=self::toRealHours($r1['WorkingHours']);
						$holiday[$week]=holiday[$week]+ $r1['WorkingHours'];
						$holiday_real[$week]=$holiday_real[$week]+$r1['WorkingHours'];
						if ($r1['TimeIn'] < $startDate) {
							continue;
						} else {
							$holiday_real[$week]=$holiday_real[$week] + $r1['WorkingHours'];
						}
					
					}
				
					
				}

/***********				
				echo '<pre>';
				
				print_r($total);
				print_r($total_real);
				print_r($holiday);
				
				exit;
***********/
				$regular = 0;
				$overtime = 0;
				$paidHoliday = 0;
				$all=0;
				foreach ($weeks as $w){
					if( $total[$w]>=40 ) {
						//$raz=$total[$w]-$total_real[$w];
						if($total_real[$w]>=40){
						 $regular=$regular+40; 
					     $overtime = $overtime + $total[$w]-40;
						}
						else 
						{$regular=$regular+$total_real[$w];//<<<<
						 $overtime = $overtime + $total[$w]-40; //remove comment to include overall overtime
						}
						
				    }
					else $regular=$regular + $total_real[$w];
					
					$paidHoliday = $paidHoliday + $holiday[$w];
				}
				
				if($overtime>0) $overtime =  number_format($overtime, 2, '.', '');
				else $overtime = '';
				
				$eData[] = array($coCode,$BatchID,$File, 
				number_format($regular, 2, '.', ''),
				$overtime,
				'VAC',
				number_format($paidVac, 2, '.', ''),
				'SIC',
				number_format($paidSic, 2, '.', ''),
				'HOL',
				number_format($paidHoliday, 2, '.', ''));
			
				
		}

		header("Content-Type:application/csv");
		header('Content-Disposition:attachment; filename="'.$outFilename.'.csv"');
		
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		
		$output = fopen("php://output",'w') or die("Can't open php://output");
		foreach($eData as $v){
			fputcsv($output, $v);
		}
		fclose($output) or die("Can't close php://output");
       	
		
	  }
	 
	 
	
    final static function MainPayrollReport(){
        msg::get();
		if (isset($_GET['sdate'])) $startdate=$_GET['sdate'];
		else  $startdate=date('Y-m-d');
			
		$today=date('Y-m-d');
		
		$currentEndDate= date('Y-m-d', strtotime('next sunday', strtotime($startdate)));
		if ( $currentEndDate > $today )  $currentEndDate=date('Y-m-d', strtotime('previous sunday', strtotime($startdate)));
		$currentStartDate= date('Y-m-d', strtotime('previous monday', strtotime($currentEndDate)));
		
		$prevWeekStartDate=date('Y-m-d', strtotime('previous monday', strtotime($currentStartDate)));
		$nextWeekEndDate = date('Y-m-d', strtotime('previous sunday', strtotime($currentStartDate)));

		$nextWeekStartDate=date('Y-m-d', strtotime('next monday', strtotime($currentEndDate)));
		$nextWeekEndDate = date('Y-m-d', strtotime('next sunday', strtotime($nextWeekStartDate)));

		if($today < $nextWeekEndDate)
		{
			$nextbutton="no";
		}
		else
		{
			$nextbutton="yes";
		}

        echo '<style>
        
        .topinfo {
            display: none !important;
        }

        .main-section {
            background-color: #fff;
            padding: 20px;
        }

        .top-heading {
            background-color: #fff;
            margin: 0 -15px;
            border-top: 1px solid #e5e5e5;
            border-bottom: 1px solid #d4d8d8;
            padding: 15px 15px 0;
        }

        .top-heading .breadcrumb {
            margin-bottom: 0;
            margin-top: 20px;
            background-color: #fff;
            font-size: 17px;
            line-height: 40px;
            color: #2c3e50;
            padding: 0 0 0 30px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .top-heading .breadcrumb li {
            border-bottom: 4px solid #27ae60;
        }

        .pagination-top {
            overflow: hidden;
            padding: 20px;
        }

        .from-to-date {
            width: calc(100% - 190px);
            float: left;
            text-align: center;
            line-height: 34px;
        }

        .from-to-date span {
            display: inline-block;
            padding-right: 10px;
            color: #327ab7;
            font-size: 18px;
            font-weight: 700;
        }

        .search-date-starttoend {
            padding: 20px 0;
            border: 1px solid #e8e8e8;
            border-bottom-width: 0px;
            background-color: #fff;
            overflow: hidden;
            position: relative;
        }

        .search-date-starttoend form {
            float: right;
            width: 45%;
            margin-bottom: 0;
        }

        .search-date-starttoend label {
            float: left;
            font-size: 16px;
            width: 22%;
            line-height: 34px;
            margin: 0;
            font-weight: 700;
        }

        .search-date-starttoend label ~ select.form-control {
            font-size: 16px;
        }

        .search-date-starttoend label ~ select.form-control,
        .search-date-starttoend label  select.form-control  input.btn {
            width: calc(23% - 20px);
            margin-right: 14px;
            float: left;
        }

        .search-date-starttoend label ~ select.form-control:nth-child(2) {
            width: 25%;
            padding: 6px 7px;
        }

        .search-date-starttoend label ~ select.form-control:nth-child(3) {
            width: 70px;
            padding: 6px 7px;
        }

        .search-date-starttoend label ~ select.form-control:nth-child(4) {
            width: 16%;
            padding: 6px 7px;
        }

        .search-date-starttoend label  select.form-control  input.btn:last-child {
            margin-right: 0;
            width: 9%;
        }


        table {
            background-color: #fff;
        }

        table tbody tr:nth-child(odd) {
            background-color: #fff;
        }

        table tr th,
        table tr td {
            padding: 4px;
            text-align: center;
        }
		.datepiker{
			cursor:pointer;
			width:100px;
		}
		input{type=button]{
		 position:relative;
		 top:-5px;
		}
		
		.datepicker table tr {
			background-color: yellow ! important;
			border:1px solid red;
		}

    </style>
	<script src="/add/cssjs/bootstrap-datepicker.min.js"></script>';
	
	$q='SELECT * FROM companies WHERE comActive="Yes"';
	$res=db::prepareLoad($q);
	


            echo '<div class="pagination-top" style="text-align:center">';

          
			/****
			<input style="cursor:pointer" type="radio" id="mlm" name="company" checked value="MLM"><label style="color:#337ab7;cursor:pointer" for ="mlm">MLM</label> 
            <input style="cursor:pointer" type="radio" id="kal" name="company" value="KAL"><label style="color:#337ab7;cursor:pointer" for ="kal">KAL</label>
			</span><br>';
			****/
			
           // echo '<a  class="btn btn-primary pull-left" href="?sdate='.$prevWeekStartDate.'"><< Prev Week</button></a>';		
            
            
			echo '<span style="color: #000;font-size:16px;font-weight: bold;">';
			
			echo '<button title="Click to select start date (Monday only)" 
			id="selWeek" type="button" class="btn btn-primary">Select Start Date (Monday only)</button>';
			
            echo '<br><br><span >Period: </span>';
            echo '<span id="startDateSpan"></span>
			<input type="hidden" id="startDate">';
            echo '<span style="color: #000;"> - </span>';
            echo '<span id="endDateSpan"></span>
			<input type="hidden" id="endDate">';
			
			$q = 'SELECT * FROM webapp_admin_settings WHERE asKey="PAYROLL_REPORT_DEF_DAYS"';
			$r = db::PrepareLoadSingleRow($q);
			$def_period = $r['asValue'];
			echo '<span style="display:block;margin-top:20px">How many weeks: <input style="width:60px" type="text" name="period" id="period" value="'.$def_period.'" 
			onblur="checkDays()">
			 <input type="button" style="position:relative;top:-2px;display:none;height:28px;line-height:10px" class="btn btn-primary" id="update_btn" 
			 value="Update" onclick="doUpdate()"></span>';
			
			
			echo '<span style="display:block;margin-top:20px;color: #000;font-size:16px;font-weight: bold;">Company:&nbsp;
			<select style="border:1px solid lightgray" name="company" id="company">';
			echo '<option value="0" >All</option>';
			foreach ($res as $r){
				echo '<option value="'.$r['comID'].'" >'.$r['comName'].'</option>';
			}
			
			echo '</select></span>';
			
			
			echo '<br><br><button onclick="showMessage();setTimeout(checkReport,100);" id="customSubmit" type="button" style="display:none" class="btn btn-success">Export CSV file >></button>';
			
			echo '<br><br><span style="display:none" id="message">Payroll report is being generated. This may take a few moments. Please wait...</span>';
           
		   echo '</span>';
           
		   /*************
			if ( $nextbutton=="yes" ){
				echo '<a  class="btn btn-primary pull-right" href="?sdate='.$nextWeekStartDate.'">Next Week >></button></a>';
			}
			else echo '<a style="visibility:hidden" class="btn btn-primary pull-right" href="?sdate='.$nextWeekStartDate.'">Next Week >></button></a>';
			************/
            echo '</div></div>';
			
			echo '<iframe  class="iframe_page" id="content" style="padding:0;margin:0;width:100%;"></iframe>';

			echo '<script>
			var weekdays = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
			jQuery( document ).ready(function() {
				jQuery("#selWeek").datepicker()
					.on("changeDate", function(ev){
						jQuery("#selWeek").datepicker("hide");
						
						var date = ev.date;
						var period=jQuery("#period").val();
						
						var d=date.getDay();
						//if (d==0) d=7;
						//sDate = formatDate(new Date(date.getFullYear(), date.getMonth(), date.getDate() - d+1));
						var sDate = formatDate(date);
						var eDateObj=new Date(date.getFullYear(), date.getMonth(), date.getDate()+ parseInt(period)*7-1 );
						var eDate = formatDate(eDateObj);
						
						var _eDate = formatDateMYSQL(new Date(date.getFullYear(), date.getMonth(), date.getDate() + parseInt(period)*7-1 ));
				
						if(! compareDates(_eDate)) {
							alert("End date in future, will be set to today instead.");
							var now = new Date();
							eDateObj = now;
							eDate = formatDate(now);;
						}
						
						jQuery("#startDate").val(sDate);
					    jQuery("#endDate").val(eDate);
						jQuery("#startDateSpan").text(sDate+" ("+weekdays[date.getDay()]+")");
						jQuery("#endDateSpan").text(eDate+" ("+weekdays[eDateObj.getDay()]+")");
				
						
						jQuery("#customSubmit").fadeIn();
						jQuery("#update_btn").fadeIn();
						
				});
				
			});
			
			
			
			function checkDays(){
				var d = jQuery("#period").val();
				var d1=parseInt(d);
				if (d != d1 || d1 <= 0 || d1>1000){
						alert("How many weeks should be a positive number of less than 1000.");
						var d = jQuery("#period").val("'.$def_period.'");
						return false;
				}
				else {
					
					return true;
				}
				
			}
			
			function doUpdate(){
				var _d = jQuery("#startDate").val();
				var period=jQuery("#period").val()*7;
				
				var date = new Date(_d);
				//if (d==0) d=7;
				//sDate = formatDate(new Date(date.getFullYear(), date.getMonth(), date.getDate() - d+1));
				var sDate = formatDate(date);
				var eDateObj=new Date(date.getFullYear(), date.getMonth(), date.getDate()+ parseInt(period)-1 );
				var eDate = formatDate(eDateObj);
				
				var _eDate = formatDateMYSQL(new Date(date.getFullYear(), date.getMonth(), date.getDate() + parseInt(period)-1 ));
		
				if(! compareDates(_eDate)) {
					alert("WARNING: End date is in the future, will be assumed today.");
					var now = new Date();
					eDateObj = now;
					eDate = formatDate(now);;
				}
				
				jQuery("#startDate").val(sDate);
				jQuery("#endDate").val(eDate);
				jQuery("#startDateSpan").text(sDate+" ("+weekdays[date.getDay()]+")");
				jQuery("#endDateSpan").text(eDate+" ("+weekdays[eDateObj.getDay()]+")");
		
				
				jQuery("#customSubmit").fadeIn();
			}
			
			function compareDates(date){
 				var q = new Date();
				var m = q.getMonth();
				var d = q.getDate();
				var y = q.getFullYear();

				var now = new Date(y,m,d);
				var nowS=formatDateMYSQL(now);
				
				if ( date <= nowS ) return true; else return false;
			}
			
			function formatDate(date) {
			  var monthNames = [
				"January", "February", "March",
				"April", "May", "June", "July",
				"August", "September", "October",
				"November", "December"
			  ];

			  var day = date.getDate();
			  var monthIndex = date.getMonth();
			  var year = date.getFullYear();

			  return monthIndex+1 +"/"+day + "/"+ year;
			}
			
			function zeroPad(num, places) {
			  var zero = places - num.toString().length + 1;
			  
			  return Array(+(zero > 0 && zero)).join("0") + num;
			}
			
			function formatDateMYSQL(date) {
			  var monthNames = [
				"January", "February", "March",
				"April", "May", "June", "July",
				"August", "September", "October",
				"November", "December"
			  ];

			  var day = date.getDate();
			  var monthIndex = date.getMonth();
			  var year = date.getFullYear();
			
			  var ret = zeroPad(year,4)+"-"+zeroPad(monthIndex+1,2)+"-"+zeroPad(day,2) ;
			  
			  return ret;
			  
			}
			
			function checkReport(){
				if (!checkDays()) return false;
				if (! DoCheckReport()){
					if (confirm("The selected period includes unsigned reports which will be excluded. Proceed?")) {setTimeout(hideMessage,5000); sendThisAsIs();}
					else jQuery("#message").hide();
				}
				else {
					setTimeout(hideMessage,5000);
					sendThisAsIs();
				}
				
			}
			
			function DoCheckReport(){
				
				var comp =jQuery("#company").val();
				var start = jQuery("#startDate").val();
				var end = jQuery("#endDate").val();
				var url="?cmd=checkReport&comp="+comp+"&sd="+start+"&ed="+end;
				jQuery.ajax({
					url:url,
                    method: "get",
					async:false,
                    success: function (data) { 
                        resp = jQuery.parseJSON(data); 
                        return resp.result;
                    }
                });

			}
			
			function showMessage(){
				jQuery("#message").show();
			}
			
			function hideMessage(){
				jQuery("#message").hide();
			}
			
			function sendThisAsIs(){ 
			    var comp =jQuery("#company").val();
				var start = jQuery("#startDate").val();
				var end = jQuery("#endDate").val();
				var url="?cmd=listReport&comp="+comp+"&sd="+start+"&ed="+end;
				jQuery("#content").attr("src",url);
			};
			
			function doResizeIframes(){
			   var iFrames = jQuery(".iframe_page");
				function iResize() { 
					for (var i = 0, j = iFrames.length; i < j; i++) { 
					  iFrames[i].style.height = iFrames[i].contentWindow.document.body.offsetHeight + 20 + "px";
					}
				}
				iResize();
		   }
			jQuery("#selWeek").datepicker({ 
				format: "m/d/yyyy", 
				weekStart: 1,
				startDate:"1/1/1970",
				endDate:"'.date(USER_DATE).'",
				
				daysOfWeekDisabled: "0,2,3,4,5,6",
				
				calendarWeeks: true,
				clearBtn:false,
				todayHighlight:true,
				title:"Please select start date",
				autoclose:true,
				}
				
			,"clearDates")
			
			.on("clearDate", function(selected){
				jQuery("#endDate").datepicker("setStartDate", "1/1/1970");
				jQuery("#startDate").datepicker("setEndDate", "'.val::convDate(MYSQL_DATE,USER_DATE,date('Y-m-d')).'");
			});
			
			
			
			
			</script>';
		
    }
	
	public static function toRealHours($in){
			$int = floor($in);     
			$fra = $in - $int;
			$realFra = $fra*100/60;
			return  number_format($int+$realFra, 2, '.', '');
			
	}
	
	public static function deys_between($RealDays,$startDate,$endDate){
		$all_days=explode(',',$RealDays);
		$c=0;
		foreach($all_days as $d){
			if($d >= $startDate AND $d<=$endDate) $c++;	
		}
		return $c;
	}

}