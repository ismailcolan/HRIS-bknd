<?php
/** The CompanyOffice controller */
class UnpaidDaysOffReportController extends AppController {

    const __DUMP__ = false;
	public static $id, $data, $pg;

    final static function MainUnpaidDaysOffReport(){
	
		 $now = date('F Y');
		 if(isset($_GET['now'])) $now = $_GET['now'];
		 $myDate = date_parse_from_format("d F Y", '1 '.$now);
		 
		 if( !isset($_GET['call']) || $_GET['call']!='ajax' ) {
		 echo '<style>.datepicker table tr td {background-color:#FFF ! important}</style>';
			

				echo '<div class="pagination-top" style="text-align:center;font-weight:bold;font-size:18px">
				Select month and year: <input type="text" title="Click here to change year and month." id="datepicker" value="'.$now.'"></div>';
				
				echo '<script>
				jQuery(document).ready(function() {
					jQuery("#datepicker").datepicker( {
					format: "MM yyyy",
					startView: "months", 
					minViewMode: "months",
					autoclose: true
				}).on("changeDate", function(ev){
					var now=jQuery("#datepicker").val();
					
					window.location.href="?now="+now;
					
					
				});
				
				});
				
				</script>
				<style> th{text-align:center ! important;}   </style>';
		 }
		table::create('none', 'vrDateStart', "ASC",ADMIN_TABLES_ROWS_PER_PAGE);
		
		table::$cols[] = Array('First Name','afFirstName');
		table::$cols[] = Array('Last Name','afLastName');
		//table::$cols[] = Array('Employee Nmber','afEmplNumber');
		table::$cols[] = Array('From','DateStart',array('filtered'=>false));
		table::$cols[] = Array('To','DateEnd',array('filtered'=>false));
		table::$cols[] = Array('Type','vrType',array('filtered'=>false));
		//table::$cols[] = Array('Days this month','Days',array('sort'=>false,'filtered'=>false));
		table::$cols[] = Array('Days total','vrDays',array('sort'=>false,'filtered'=>false));
		table::$cols[] = Array('Days in selected month','DaysCount',array('sort'=>false,'filtered'=>false));
		
	
	    $month = sprintf("%02d",$myDate['month']);
		$query=
		"SELECT  vrDateStart,date_format(vrDateStart,'%m/%d/%Y') as DateStart,
		date_format(vrDateEnd,'%m/%d/%Y') as DateEnd,vrType,vrRealDays,afFirstName,afLastName,vrDays,
		
		(CASE
            WHEN vrDays=0.5  THEN 0.5
	        ELSE  ROUND ( (LENGTH(vrRealDays) - LENGTH( REPLACE (vrRealDays, '-{$month}-', '') )  ) / LENGTH('-{$month}-') ) 
        END) AS DaysCount
		
		FROM vacation_requests
		LEFT JOIN  apply_forms ON afID = vrAFID
		WHERE  afSalaryType ='Annual' AND	vrState='approved'
		AND  vrYear='{$myDate['year']}' AND vrRealDays LIKE '%-{$month}-%' AND vrPaydUnpaid='unpaid'";
//echo $query;	//
		table::execute($query);
		foreach (table::$data as $r => &$cells) {
			//if ($cells['DaysCount'] > 0.5)  $cells['DaysCount']=intval($cells['DaysCount']);
			$cells['DaysCount']=val::mynumber_format($cells['DaysCount'],1);
			//if ($cells['vrDays']>0.5)  $cells['vrDays']=intval($cells['vrDays'])';
			$cells['vrDays']=val::mynumber_format($cells['vrDays'],1);
        }
	
        table::load();
		
	}
}