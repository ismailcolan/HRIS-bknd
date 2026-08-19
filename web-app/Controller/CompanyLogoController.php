<?php
/** The CompanyOffice controller */
class CompanyLogoController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

	
	
	final static function EditLogo(){
		CompanyLogoView::Edit();
	}
	
	final static function SaveLogo(){ //cf::pr($_FILES);cf::pr($_POST);
		
			$file = FS_ROOT ."add/images/customerLogo{$_SESSION['customerID']}.";
	
			if (isset($_POST['photoCmd']) && $_POST['photoCmd'] == 'remove') unlink($file.'png');
			//cf::pr($_FILES);exit;
			
			if ($_FILES['cLogo']['error'] == 0){
				$exploded = explode('.',$_FILES["cLogo"]["name"]);
				$ext = $exploded[count($exploded) - 1]; 
				
				if (!(move_uploaded_file($_FILES["cLogo"]["tmp_name"], $file.$ext)
				 && cf::convertImagePNG($file.$ext, $file.'png', 5)) ) {
					msg::set("Error uploading logo file. Nothing changed.",'error');
					
				 }
				
			}
			
			self::EditLogo();
			
	}
    
    
    
}