<?php
class SuperAdminCustomersModel extends AppModel {
    static 	$dataAddons = array(), 
			$dt = array("tbl" => "customers", "key" => "cID", "pfx" => "", "controller" => "SuperAdminCustomerController");

    static function adminPageTitleWithLinks($links = null) {
		$C = self::$dt["controller"];
		$links = AccessModel::AdminPageHeaderLnk($C);
        return array("Customers", $links);
    }
	
	public static function ValidateEdit($type){
		val::init($type);
		self::validateName("cCustomerName","Customer Name");
		self::validateName("cCustomerShortName","Customer Short Name");
		//self::validateEmail("cAdminEmail","Admin email");
		//self::validatePass("cPass","Password");
		self::validateDB("cDBASE","DB Name",$type);
		
		val::throwServerRunError();
	}
	
	private static function validateEmail($name="cAdminEmail",$label){
		val::required($name, "Please fill out the " . strtolower($label) . ".");
		val::regExp($name, self::EMAIL_REGEXP, "Please use valid email.");
        val::maxLength($name, 255);
		
		if (isset($_GET['id'])) $id=$_GET['id']; else $id = 0;
        val::unique($name, 'customers', 'cID', $id);
	}
	
	private static function validateDB($name = "cDBASE",$label,$type){
        val::required($name, "Please fill out the " . strtolower($label) . ".");
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::maxLength($name, 50);
		if (isset($_GET['id'])) $id=$_GET['id']; else $id = 0;
        val::unique($name, 'customers', 'cID', $id);
		
		if($type == 'server') {
			$DBNAME=DB_PREFIX.$_POST['cDBASE'];
			$q = "SHOW DATABASES LIKE '{$DBNAME}';";
			$res = db::PrepareLoad($q);
			
			if($res === false) { val::setError('cDBASE',$q . ' - No such DB created. Please create DB and tables first.');db::SwitchToMain(); return;}
			if (! db::SwitchToRealByProvidedName($DBNAME)){
				val::setError('cDBASE','Error connectin to DB '.$DBNAME.'.');
				db::SwitchToMain();
				return;
			}
			else {
				$q = "SHOW TABLES LIKE 'webapp_admins'";
				$res = db::PrepareLoad($q);
				
				if($res === false) { 
					val::setError('cDBASE','There is no table webapp_admins in '.$DBNAME.'.'); 
					db::SwitchToMain();
					return;
				}
			}
		}
		db::SwitchToMain();
    }
	
	
	
	private static function validateName($name = "cCustomerName",$label){
        val::required($name, "Please fill out the " . strtolower($label) . ".");
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::maxLength($name, 50);
		if (isset($_GET['id'])) $id=$_GET['id']; else $id = 0;
        val::unique($name, 'customers', 'cID', $id);
    }
	
	private static function validatePass($name = "cPass",$label){
        val::required($name, "Please fill out the " . strtolower($label) . ".");
        val::minLength($name, 6, "Please add at least 6 characters.");
        val::maxLength($name, 50);
    }
}
