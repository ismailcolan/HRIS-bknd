<?php
/** The CompanyOffice view */
class GeneralView extends AppView {

    /** Shows a form to edit admin mails
     * @param type $data - controller::$data
     * @param type $lbls - model::$fldLbl 
     * @param type $lists - lists generated in the model */
    final static function Edit($data, $pg, $lbls,$args=null){
		 
		  $args = self::$fldArgsBase;
		  if ( isset($_SESSION['general_save_success'])){
			  $continue=$_SESSION['general_save_success'];
			  unset($_SESSION['general_save_success']);
		  }
		  if (! isset($continue)){
        form::codeStyle(true, 1, false);
        form::openTag("div",array("class"=>"form-panel"));
            form::$ofsetIndentsCount = 2;
			//"onsubmit"=>"return validateEmails($('#txt_area').val())"
            form::openTag("form",array("method"=>"post","validate"=>true, "class"=>"form-horizontal"));
              // Please enter one email address per line without any separators.
				
				echo '
				<label for="txt_area" class="control-label col-sm-4 col-md-3"></label>
				<h5><b>Mailing Settings</b></h5>';
				 form::inpField(
                    $lbls[($f = $pg["pfx"]."MAIN_ADMIN_EMAIL")].":",
                    $f,
                    $data[$f],   
						$args+array('hint'=>'This will be email used for restore of password when password is forgotten.
						<br><b>It is important to keep it actual</b>.','id'=>'main_admin_mail')
                );
				
                form::inpField(
                    $lbls[($f = $pg["pfx"]."ADMIN_MAILS")].":",
                    $f,
                    $data[$f],   
						array( "rows"=>"5")+$args+array('hint'=>'Place here emails of persons you want to recieve notification of system.
						<br><b>It will be good idea to place main admin email here also</b>.
						<br>Please enter one email address per line without any separators.','type'=>'textarea','id'=>'txt_area')
                );
				/****************/
				form::inpField(
                    $lbls[($f = $pg["pfx"]."EMAIL_MAILER_NAME")].":",
                    $f,
                    $data[$f],   
						$args+array('hint'=>'This will be visible name of sender when systeme mails are sent.','id'=>'EMAIL_MAILER_NAME')
                );
				
				form::inpField(
                    $lbls[($f = $pg["pfx"]."EMAIL_MAILER_ADDRESS")].":",
                    $f,
                    $data[$f],   
						$args+array('hint'=>'This will be addres system mails are comming from.','id'=>'EMAIL_MAILER_ADDRESS')
                );
				/*****************/
				 form::inpField(
                    $lbls[($f = $pg["pfx"]."SYSTEM_MAILS_SIGNATURE")].":",
                    $f,
                    $data[$f],   
						array( "rows"=>"5")+$args+array('hint'=>'Please enter plain text with Enter for new line. It will be converted to HTML when necessary.','type'=>'textarea','id'=>'txt_area')
                );
				
				echo '<hr>
				<label for="txt_area" class="control-label col-sm-4 col-md-3"></label>
				<h5><b>FTP Settings for Benefits upload and download</b></h5>';
		
				 form::inpField(
                    $lbls[($f = $pg["pfx"]."FTP_server")].":",
                    $f,
                    $data[$f],   
						array( "rows"=>"5")+$args+array('id'=>'FTP_server')
                );
				
				 form::inpField(
                    $lbls[($f = $pg["pfx"]."FTP_protocol")].":",
                    $f,
                    $data[$f],   
						array( "rows"=>"5")+$args+array('id'=>'FTP_protocol')
                );
				
				 form::inpField(
                    $lbls[($f = $pg["pfx"]."FTP_port")].":",
                    $f,
                    $data[$f],   
						array( "rows"=>"5")+$args+array('id'=>'FTP_port')
                );
				
				
				 form::inpField(
                    $lbls[($f = $pg["pfx"]."FTP_password")].":",
                    $f,
                    $data[$f],   
						array( "rows"=>"5")+$args+array('id'=>'FTP_password')
                );
				
				 form::inpField(
                    $lbls[($f = $pg["pfx"]."FTP_username")].":",
                    $f,
                    $data[$f],   
						array( "rows"=>"5")+$args+array('id'=>'FTP_username')
                );
				
				 form::inpField(
                    $lbls[($f = $pg["pfx"]."FTP_upload")].":",
                    $f,
                    $data[$f],   
						array( "rows"=>"5")+$args+array('id'=>'FTP_upload')
                );
				
				 form::inpField(
                    $lbls[($f = $pg["pfx"]."FTP_download")].":",
                    $f,
                    $data[$f],   
						array( "rows"=>"5")+$args+array('id'=>'FTP_download')
                );
				
				 form::inpField(
                    $lbls[($f = $pg["pfx"]."FTP_file_download")].":",
                    $f,
                    $data[$f],   
						array( "rows"=>"5")+$args+array('id'=>'FTP_file_download')
                );
				
				
				
				echo '<hr>
				<label for="txt_area" class="control-label col-sm-4 col-md-3"></label>
				<h5><b>FTP Settings for TimeClock</b></h5>';
		
				 form::inpField(
                    $lbls[($f = $pg["pfx"]."TCP_server")].":",
                    $f,
                    $data[$f],   
						array( "rows"=>"5")+$args+array('id'=>'TCP_server')
                );
				
				 form::inpField(
                    $lbls[($f = $pg["pfx"]."TCP_database")].":",
                    $f,
                    $data[$f],   
						array( "rows"=>"5")+$args+array('id'=>'TCP_database')
                );
				
				 form::inpField(
                    $lbls[($f = $pg["pfx"]."TCP_username")].":",
                    $f,
                    $data[$f],   
						array( "rows"=>"5")+$args+array('id'=>'TCP_username')
                );
				
				 form::inpField(
                    $lbls[($f = $pg["pfx"]."TCP_password")].":",
                    $f,
                    $data[$f],   
						array( "rows"=>"5")+$args+array('id'=>'TCP_password')
                );
				
		
		
		
		
				
            form::inpSaveCancel('',self::$saveCancelArgsBase);//ADMIN.self::DetailsCancelUrl($data, $pg) , self::$saveCancelArgsBase);
            form::closeTag("form");
            form::codeStyle(true, 1, true);
			form::closeTag("div",array("class"=>"form-panel"));
			echo '</div>';
		  }
/***************			
		echo '
<script language="javascript" type="text/javascript">
function validateEmail(email) {
  var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(email);
}
function validateEmails(value) {
    var result = value.split("\n");
	 var x=0;
    for(var i = 0;i < result.length;i++){
		var m = result[i].trim();
       if(m.length>0 && !validateEmail(result[i].trim())) {
		   $("#txt_area").setCursorPosition(x).focus();
		   myalert("Alert","<b>"+result[i]+"</b> is not valid email on row "+(i+1));
			return false;
		}
	   x=x+result[i].length+1;
	}
    return true;
}
</script>';
***************/
    }


}
