<?php

class UnpaidDaysReportView extends AppView{
	
	public static function UnpaidRequestsList($res){
		$q="SELECT min(vrYear) FROM vacation_requests
	        LEFT JOIN apply_forms ON afID=vrAFID
			 WHERE afSalaryType='Annual' AND vrPaydUnpaid = 'unpaid' AND vrState='approved'";
		$r = db::prepareLoadSingleRow($q);
		
		$_yearStart=$r['min(vrYear)'];
		
		if (! is_null($_yearStart)) $yearStart=$_yearStart; else $yearStart=date('Y');
		
		if (isset($_GET['year']) && isset($_GET['month']))
		{$year=$_GET['year']; $month=$_GET['month'];}
	    else
		{$year=date('Y'); $month=date('m');}
	
	   echo '<div style="text-align:center;padding:10px">';
	   echo '<span style="font-size:1.3em">Select year and month: 
	   <select style="border:0;cursor:pointer" id="date_select" onchange="changeDateSelect()">';
	   
	   for ($y=$yearStart;$y<=date('Y');$y++){
		   echo '<optgroup label="'.$y.'">';
		   for ($i=1;$i<=12;$i++){
			   if ($year==$y && $month==$i) $sel='selected'; else $sel='';
			   echo '<option '.$sel.' value="'.$y.'-'.$i.'">'.val::convDate(MYSQL_DATE,'F Y',$y.'-'.$i.'-1').'</option>';
		   }
		   echo '</optgroup>';
	   }
	   echo '</select></span>';
	   echo '<h4><b>Unpaid days report for '.val::convDate(MYSQL_DATE,'F Y',$year.'-'.$month.'-1')
	   .'</b></h4>';
	  
	   echo '<table style="width:90%;margin:0 auto;">
	   <thead>
	   <tr><th>Employee name</th><th>Request dates from - to</th><th>Type of vacation</th>
	   <th>Work days used this month</th></tr>
	   </thead>';
	   
	   if (empty($res)) {echo '<tr><td colspan="4" style="text-align:center;">No unpaid days off this month</td></tr>';}
	   else {
			echo '<tbody>';
		   foreach ($res as $k=>$r){
			   $from=val::convDate(MYSQL_DATE,USER_DATE,$r['vrDateStart']);
			   $to= val::convDate(MYSQL_DATE,USER_DATE,$r['vrDateEnd']);
			   $c=0;
			   $days=explode(',',$r['vrRealDays']);
			   $date=val::convDate(MYSQL_DATE,'Y-m',$year.'-'.$month.'-01');
			   
			   foreach($days as $l=>$d){
				   if(strpos($date,$d) >=0) $c++;
			   }
			   echo "<tr><td>{$r['afFirstName']} {$r['afLastName']}</td>
			   <td>{$from} - {$to}  </td><td>{$r['vrType']}</td><td>{$c}</td></tr>";
		   }
		   echo '</tbody>';
	   }
	   echo '</table></div>';
		echo "<script>
		function changeDateSelect(){
			var d=jQuery('#date_select').val();
			var r=d.split('-');
			location.href='?year='+r[0]+'&month='+r[1];
		}
		</script>";
	}
}

