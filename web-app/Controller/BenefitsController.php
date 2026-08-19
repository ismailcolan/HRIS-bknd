<?php
/** The CompanyOffice controller */
class BenefitsController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

	final static function SaveAdminBenefits(){	
		unset($_POST['ADMIN_BENEFITS_SAVE']);
		$val=json_encode($_POST);
		$q="UPDATE webapp_admin_settings SET  asValue ='{$val}' WHERE asKey='BENEFITS_SETTINGS' ";
		$r=db::SQL($q);
		if($r) msg::set("Changes were saved","success");	
		else
		msg::set("Error saving changes","error");
		//self::EditAdminBenefits();
	}
    
	
	final static function EditAdminBenefits(){
		BenefitsView::Edit();
	}
    
    
    
}