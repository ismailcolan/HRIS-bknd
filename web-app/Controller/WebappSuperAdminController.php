
<?php
/** The WebappAdmin controller */
class WebappSuperAdminController extends AppController {
    
    const __DUMP__ = false;
    public static $id, $data, $pg;
	
	final static function SuperAdminHomePage(){
     echo 'THIS IS THE HOME PAGE';
    }
    
    final static function All($lists = array()){
        msg::get();
        $lists["roles"]=WebappSuperAdminModel::getEnumFieldValues(WebappSuperAdminModel::$dt["tbl"],"waaRole");
        unset($lists["roles"]["Admin"]); array_unshift($lists["roles"],"Admin"); //Move the Admin in the top position
        WebappSuperAdminModel::$dataAddons = array("waaRegDateFormatted");
        WebappAdminView::All(self::$pg, WebappSuperAdminModel::query(), $lists);
    }
    
    final static function Details($lists = array()){
        WebappSuperAdminModel::ValidateDetails("client", $lists);
        WebappAdminView::Details(self::$data, self::$pg, $lists);
    }
    final static function DetailsSave($lists = array()){
        try{ WebappSuperAdminModel::ValidateDetails("server", $lists); } catch (Exception $e) {return self::Details();}

        db::Save(WebappSuperAdminModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? msg::set(TXT_SAVE_OK,"success") : msg::set(TXT_SAVE_ERROR,"error");
     
        cf::Redirect(self::$pg["list"]["url"]);
    }
    
    static function ChangePassword($oldPass=false){
        msg::get();
        WebappSuperAdminModel::ValidateChangePassword("client", $oldPass);
        WebappAdminView::ChangePassword(self::$pg, $oldPass);
    }
    static function ChangePasswordProcess($oldPass=false){
        try{ WebappSuperAdminModel::ValidateChangePassword("server", $oldPass); } catch (Exception $e) {return self::ChangePassword();}
        
        $q = "UPDATE webapp_admins_tokens SET waatToken=AES_ENCRYPT(\"" . self::$data["token"] . "\", \"".WEBAPP_SQL_SECRET_KEY."\") WHERE waatID = ".self::$id;
        db::Save($q, self::$id, null, self::__DUMP__) ? msg::set(TXT_SAVE_OK,"success") : msg::set(TXT_SAVE_ERROR,"error");
        
        $url = array_key_exists("list", self::$pg)? self::$pg["list"]["url"] : self::$pg["password"]["url"];
        cf::Redirect($url);
    }
    
    static function Login($goto=null){ 
       
        WebappSuperAdminModel::ValidateLogin("client");
        if(is_null($goto)){
            $goto = filter_has_var(INPUT_GET, "goto") ? filter_input(INPUT_GET, "goto", FILTER_SANITIZE_URL) : null;
        }
        WebappSuperAdminView::Login($goto);
    }
    static function LoginProcess(){ 
	  //  print_r($_POST);exit;
		db::SwitchToMain();
		$user = filter_input(INPUT_POST, "user", FILTER_SANITIZE_STRING);
		$pass = filter_input(INPUT_POST, "pass", FILTER_SANITIZE_STRING);
	
		if($user == '' && $pass == ''){
			msg::set('Please enter both username and password.','error');
			self::Login();
		}
		$goto = filter_has_var(INPUT_POST, "goto") ?  filter_input(INPUT_POST, "goto", FILTER_SANITIZE_URL) : null;
		$q = "SELECT * FROM superadmin WHERE saUserName='{$user}'  AND saPass='{$pass}'";
		
		$r = db::PrepareLoadSingleRow($q);
		cf::pr($r);
		if( $r == false){ 
			msg::set('Please enter valid username and password.','error');
			//self::Login();
		}
		
		if($r['saActive'] == 'no'){
			msg::set('This user is not active.','error');
			//self::Login();
		}
		
		
        $_SESSION['superadmin'] = $r['saUserName'];
		//echo 'LOGED'; echo SUPERADMIN.$url;exit;
        cf::Redirect(SUPERADMIN.'home');

    }

    final static function Logout(){
        unset($_SESSION['superadmin']);
        cf::Redirect(SUPERADMIN."login");
    }

    final static function Delete(){
        WebappSuperAdminModel::delete(self::$data);
        cf::Redirect(self::$pg["list"]["url"]);
    }
	
	 final static function Header(){
		
	 }
	
	
}