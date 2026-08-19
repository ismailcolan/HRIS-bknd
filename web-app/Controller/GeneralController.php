<?php
/** The CompanyOffice controller */
class GeneralController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

	final static function SaveAdminMails(){
		if (GeneralModel::SaveData()) msg::set ('Changes were saved.','success');
		else msg::set ('There was a problem to save all changes.','error');
		cf::Redirect(ADMIN.'/general');
	}
    

	final static function EditAdminMails(){
		$lbls = array(
		'MAIN_ADMIN_EMAIL'=>'Administrator mail',
		'ADMIN_MAILS'=>"Notifications emails",
		'SYSTEM_MAILS_SIGNATURE'=>"Signature of system mails",
		'EMAIL_MAILER_NAME'=>'Mailer Name',	
		'EMAIL_MAILER_ADDRESS'=>'Mailer Address',
		'FTP_server'=>'FTP Server',
		'FTP_protocol'=>'FTP Protocol',
		'FTP_port'=>'FTP Port',
		'FTP_username'=>'FTP User',
		'FTP_password'=>'FTP Password',
		'FTP_upload'=>'FTP Upload folder',
		'FTP_download'=>'FTP Download folder',
		'FTP_file_download'=>'FTP Download file',
		'FTP_upload' =>'Remoute folder to upload to',
		
		'TCP_server'=>'TimeClock Server',
		'TCP_database'=>'TimeClock Database',
		'TCP_username'=>'TimeClock Username',
		'TCP_password'=>'TimesClock Password',	
		);
		
		$data = GeneralModel::getData();
		foreach	($lbls as $k=>$v){
			if(!isset($data[$k])) $data[$k]='';
		}	
		
		$pg = array('pfx'=>'');
		
		$args=null;
		GeneralView::Edit($data, $pg, $lbls,$args);
	}
    
    
    
}