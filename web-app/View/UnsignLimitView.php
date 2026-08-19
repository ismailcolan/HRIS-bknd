<?php
/** The CompanyOffice view */
class UnsignLimitView extends AppView {

    /** Shows a form to edit admin mails
     * @param type $data - controller::$data
     * @param type $lbls - model::$fldLbl 
     * @param type $lists - lists generated in the model */
    final static function Edit(){
		 
		  $args = self::$fldArgsBase;
		  if ( isset($_SESSION['general_save_success'])){
			  $continue=$_SESSION['general_save_success'];
			  unset($_SESSION['general_save_success']);
		  }
		  
		$q = 'SELECT * FROM webapp_admin_settings WHERE asKey="UNSIGN_REPORT_DAYS"';
		$r = db::PrepareLoadSingleRow($q);
		$days = $r['asValue'];
		
		$q = 'SELECT * FROM webapp_admin_settings WHERE asKey="UNSIGN_REPORT_HOURS"';
		$r = db::PrepareLoadSingleRow($q);
		$hours = $r['asValue'];
		
		$q = 'SELECT * FROM webapp_admin_settings WHERE asKey="PAYROLL_REPORT_DEF_DAYS"';
		$r = db::PrepareLoadSingleRow($q);
		$def_period = $r['asValue'];;
		
		msg::get();
        form::codeStyle(true, 1, false);
        form::openTag("div",array("class"=>"form-panel","style"=>"text-align:center"));
            form::$ofsetIndentsCount = 2;
            form::openTag("form",array("onsubmit"=>"return validateForm();","method"=>"post","validate"=>true, "class"=>"form-horizontal"));
            
			echo 'Un-sign reports allowed until&nbsp;&nbsp;<input style="width:60px" type="text" name="days" id="days" value="'.$days.'"> days&nbsp
and&nbsp;&nbsp;<input type="text" style="width:60px" name="hours" id="hours" value="'.$hours.'"> hours after end of week.<br>';
			
			echo '<span><b>Note:</b> End of week is Monday 12:00 AM. If you set 1 day 0 hours that means Tuesday 12:00 AM.</span>';
			
			echo '<input type="hidden" name="ADMIN_UNSIGN_SETTINGS" value="yes">';
			
			echo '<br><br>Payroll Report default period: <input type="text" style="width:60px" name="def_period" id="def_period" value="'.$def_period.'"> weeks.';
           
		    echo '<br><br><input type="submit" onclick="" class="btn btn-primary" value="Save"> ';
            form::closeTag("form");
            form::codeStyle(true, 1, true);
		form::closeTag("div",array("class"=>"form-panel"));
	
		echo '<script>
		function validateForm(){
			var d=jQuery("#days").val();
			var days=parseInt(d);
			var h=jQuery("#hours").val();
			var hours=parseInt(h);
			
			var _def_period=jQuery("#def_period").val();
			var def_period=parseInt(_def_period);
			
			if (d != days || h != hours || days<0 || hours<0) {alert("Both days and hours must be positive integers.");return false;}
			if ( hours >= 24 ) {alert("Hours can not be greater than or equal to 24.");return false;}
			if ( days > 1000000 ) {alert("Days can not be greater then 1 000 000.");return false;}
			
			if (def_period != _def_period ||  def_period <=0 || def_period > 1000) {
				alert("Payroll Report default period must be positive integer between 1 and 1000.");return false;}
			
			return true;
		}
		
		</script>';
    }


}
