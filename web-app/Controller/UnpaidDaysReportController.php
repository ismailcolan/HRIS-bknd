<?php
/** The UnpaidDaysReport controller */
class UnpaidDaysReportController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

    public static function ListUnpaidRequests(){
		if (isset($_GET['year']) && isset($_GET['month']))
		{$year=$_GET['year']; $month=$_GET['month'];}
	    else
		{$year=date('Y'); $month=date('m');}
	
	    $dd=date('Y-m',strtotime($year.'-'.$month.'-01'));
	
       $q="SELECT * FROM vacation_requests
	        LEFT JOIN apply_forms ON afID=vrAFID
			 WHERE afSalaryType='Annual' AND vrPaydUnpaid = 'unpaid' AND vrState='approved' 
			 AND vrRealDays LIKE '{$dd}-%' ORDER BY afFirstName, afLastName,vrDateStart ASC";
		$res=db::prepareLoad($q);
		
		if ($res==false) $res=array();
		UnpaidDaysReportView::UnpaidRequestsList($res);
		
    }

    
}