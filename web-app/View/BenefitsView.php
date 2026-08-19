<?php
/** The CompanyOffice view */
class BenefitsView extends AppView {
    
	final static function Edit(){
		$args = self::$fldArgsBase;
		
		$q = "SELECT * FROM webapp_admin_settings  WHERE asKey='BENEFITS_SETTINGS' ";	
		$r = db::PrepareLoadSingleRow($q);

		$data = json_decode($r['asValue'],true);
/****		
		switch (json_last_error()) {
        case JSON_ERROR_NONE:
            echo ' - No errors';
        break;
        case JSON_ERROR_DEPTH:
            echo ' - Maximum stack depth exceeded';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            echo ' - Underflow or the modes mismatch';
        break;
        case JSON_ERROR_CTRL_CHAR:
            echo ' - Unexpected control character found';
        break;
        case JSON_ERROR_SYNTAX:
            echo ' - Syntax error, malformed JSON';
        break;
        case JSON_ERROR_UTF8:
            echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
        default:
            echo ' - Unknown error';
        break;
    }
*******/
		if(!is_array($data)) {
			$data=array();
			$data['PayrollFrequencyAnualEmployees']=12;
			$data['PayrollFrequencyHourlyEmployees']=26;
			$data['DeductionFrequencyAnualEmployees']=12;
			$data['DeductionFrequencyHourlyEmployees']=26;
			$data['excelSheedPrefix']='MaidenLaneMedical';
			$data['EmailDeductionsSheetTo']="rwhite@kalhealth.org, info@metavisia.com";
		}
        form::codeStyle(true, 1, false);
        form::openTag("div",array("class"=>"form-panel"));
            form::$ofsetIndentsCount = 2;
            form::openTag("form",array("onsubmit"=>"return validateForm()","method"=>"post","validate"=>true, "class"=>"form-horizontal"));
			    echo '<div class="fld form-group"><label for="PayrollFrequencyAnualEmplyees" class="control-label col-sm-4 col-md-3">';
				echo '<label style="width:auto"><h5 style="font-weight:bold">Payroll frequency</h5></label>';
				echo '</div>';
				
                form::inpField(
                    'Anual employees:',
                    'PayrollFrequencyAnualEmployees',
                    $data['PayrollFrequencyAnualEmployees'], 
					$args+array('hint'=>'Integer between 1 and 400.','style'=>'width:80px','type'=>'text','id'=>'PayrollFrequencyAnualEmployees')
                );
				
				form::inpField(
                    'Hourly employees:',
                    'PayrollFrequencyHourlyEmployees',
                    $data['PayrollFrequencyHourlyEmployees'], 
					$args+array('hint'=>'Integer between 1 and 400.','style'=>'width:80px','type'=>'text','id'=>'PayrollFrequencyHourlyEmployees')
                );
				
				
				
				echo '<div class="fld form-group"><label for="PayrollFrequencyAnualEmplyees" class="control-label col-sm-4 col-md-3">';
				echo '<label style="width:auto;"><h5 style="font-weight:bold">Deduction frequency</h5></label>';
				echo '</div>';
                form::inpField(
                    'Anual employees:',
                    'DeductionFrequencyAnualEmployees',
                    $data['DeductionFrequencyAnualEmployees'], 
					$args+array('hint'=>'Integer between 1 and 400.','style'=>'width:80px','type'=>'text','id'=>'DeductionFrequencyAnualEmployees')
                );
				
				form::inpField(
                    'Hourly employees:',
                    'DeductionFrequencyHourlyEmployees',
                    $data['DeductionFrequencyHourlyEmployees'], 
					$args+array('hint'=>'Integer between 1 and 400.','style'=>'width:80px','type'=>'text','id'=>'DeductionFrequencyHourlyEmployees')
                );
				
				echo '<hr>';
				form::inpField(
                    'Excel Sheed prefix:',
                    'excelSheedPrefix',
                    $data['excelSheedPrefix'], 
					$args+array('type'=>'text','id'=>'excelSheedPrefix')
                );
				
				form::inpField(
                    'Email Deductions sheet to:',
                    'EmailDeductionsSheetTo',
                    $data['EmailDeductionsSheetTo'],  
						array( "rows"=>"5")+$args+array('hint'=>'Please enter e-mail adresses with "," separator.','type'=>'text','id'=>'EmailDeductionsSheetTo')
                );
				
			echo '<input type="hidden" name="ADMIN_BENEFITS_SAVE" value="true">';	
			
			echo '<br><label  class="control-label col-sm-4 col-md-3"></label>
			<input type="submit" onclick="" class="btn btn-primary" value="Save"> ';
				
            //form::inpSaveCancel('',self::$saveCancelArgsBase);//ADMIN.self::DetailsCancelUrl($data, $pg) , self::$saveCancelArgsBase);
            form::closeTag("form");
            form::codeStyle(true, 1, true);
			  form::closeTag("div",array("class"=>"form-panel"));
		
		echo '<script>
		function validateForm(){
			if(!checkIt("#PayrollFrequencyAnualEmployees","Payroll Frequency Anual emplyees"))  return false;
			if(!checkIt("#PayrollFrequencyHourlyEmployees","Payroll Frequency Hourly emplyees"))  return false;
			if(!checkIt("#DeductionFrequencyAnualEmployees","Deduction Frequency Anual emplyees"))  return false;
			if(!checkIt("#DeductionFrequencyHourlyEmployees","Deduction Frequency Hourly emplyees")) return false;
			
			if(!checkEmpty("#excelSheedPrefix","Excel Sheed prefix"))  return false;
			if(!checkEmpty("#EmailDeductionsSheetTo","Email Deductions sheet to"))  return false;
		}
		
		function checkIt(v,name){
		    var val=jQuery(v).val();
			var int = parseInt(val);
			if(int != val || int<1 || int>400) {myalert("Incorrect entry","Value of "+ name +" must be integer between 1 and 400.");return false;}
			return true;
		}
		
		function checkEmpty(v,name){
			var val=jQuery(v).val();
			var tr = val.trim();
			if(tr=="") {myalert("Incorrect entry","There must be value of  "+ name +".");return false;}
			return true;
		}
		
		</script>';
    }

}
