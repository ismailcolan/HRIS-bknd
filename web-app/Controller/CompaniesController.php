<?php
/** The CompanyOffice controller */
class CompaniesController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

    final static function All(){
        msg::get();
        CompaniesModel::$dataAddons+=array(self::$pg["key"]."edit");
        $cond = "`".self::$pg["pfx"]."Active`=\"" .(self::$rq==="list" ? "Yes": "No")."\"";
        CompaniesView::All(self::$pg, CompaniesModel::query($cond), CompaniesModel::$fldLbl, self::$rq);
    }

    static function Summary(){
        msg::get();
        CompaniesView::Summary(self::$data, self::$pg, CompaniesModel::$fldLbl);
    }

    public static function getBackUrl()
    {
        $url = (self::$data[CompaniesModel::$dt["pfx"]."Active"] === "Yes" ? self::$pg["list"]["url"] : self::$pg["inactive-list"]["url"]);
        $params = Array("name"=>"Back to ".strtolower(self::$pg["items"]), "url"=>$url, "icon"=>"fa fa-chevron-circle-left", "args"=>array("class"=>"btn btn-info"));
        return $params;
    }

    final static function Details($lists = array()){ 
        CompaniesModel::Detailsvalidate("client", $lists);
        CompaniesView::Details(self::$data, self::$pg, CompaniesModel::$fldLbl, $lists);
    }
    final static function DetailsSave($lists = array()){
		
		
        try{ CompaniesModel::DetailsValidate("server", $lists); } catch (Exception $e) {return self::Details();}
		
        db::Save(CompaniesModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? msg::set(TXT_SAVE_OK,"success") : msg::set(TXT_SAVE_ERROR,"error");       
		
		//echo '>>>'.db::$id; cf::pr($_POST);cf::pr($_FILES);exit;
		if (isset(db::$id) && db::$id > 0 ) $id = self::$id;
		if (isset($_GET['id'])) $id = $_GET['id'];
		$file = FS_ROOT ."add/images/companyLogo{$_SESSION['customerID']}_{$id}.";
	        
			if (isset($_POST['photoCmd']) && $_POST['photoCmd'] == 'remove') {
				unlink($file.'png');
				msg::set("Current logo was deleted.",'success');
			}
			
			
			if ($_FILES['cLogo']['error'] == 0){
				$exploded = explode('.',$_FILES["cLogo"]["name"]);
				$ext = $exploded[count($exploded) - 1]; 
				
				if (!(move_uploaded_file($_FILES["cLogo"]["tmp_name"], $file.$ext)
				 && cf::convertImagePNG($file.$ext, $file.'png', 5)) ) {
					msg::set("Error uploading logo file.",'error');
					
				 }
				 else msg::set("New company logo was saved succesfully.",'success');
				
			} else if ($_FILES['cLogo']['error'] == 4) msg::set("No new logo file.Logo file remains as is.",'success');
			
        cf::Redirect(ADMIN.self::$pg["summary"]["url"]."?id=".self::$id);
    }
    
}