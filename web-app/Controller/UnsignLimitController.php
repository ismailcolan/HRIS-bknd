<?php
/** The CompanyOffice controller */
class UnsignLimitController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

	final static function SaveAdminUNSIGN_SETTINGS(){
		$d=$_POST['days'];
		$h=$_POST['hours'];
		$def_period=$_POST['def_period'];
		
		$q1 ="UPDATE webapp_admin_settings SET asValue='{$d}' WHERE asKey='UNSIGN_REPORT_DAYS'";
		$q2 ="UPDATE webapp_admin_settings SET asValue='{$h}' WHERE asKey='UNSIGN_REPORT_HOURS'";
		$q3 ="UPDATE webapp_admin_settings SET asValue='{$def_period}' WHERE asKey='PAYROLL_REPORT_DEF_DAYS'";
		
		$r1=db::SQL($q1); $r2=db::SQL($q2); $r3=db::SQL($q3);
		if ($r1 && $r2 && $r3) msg::set("Changes saved.","success");
		else msg::set("Error saving changes","error");
		
		
		
	}
    
	
	final static function EditAdmin_UNSIGN_SETTINGS(){
		UnsignLimitView::Edit();
	}
    
    
    
}