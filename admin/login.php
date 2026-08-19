<?php 
/** Description: Serves all login pages and tasks */

class adminLogin extends PageAdmin {

    const CONTROLLER = "WebappAdminController";
    private static $admin = array();
	
    public function __construct($C = self::CONTROLLER) {
        parent::__construct();
		
        self::$admin["user"] = filter_has_var(INPUT_POST, "user") ? filter_input(INPUT_POST, "user", FILTER_SANITIZE_SPECIAL_CHARS) : null;
        self::$admin["pass"] = filter_has_var(INPUT_POST, "pass") ? filter_input(INPUT_POST, "pass", FILTER_SANITIZE_SPECIAL_CHARS) : null;
		cf::$rq['recover'] = false;
		cf::$rq['force_login'] = false;
		if(isset($_GET['cmd']) && $_GET['cmd'] == 'login-recover') {
			cf::$rq['recover'] = true;
		}
		elseif (isset($_GET['cmd']) && $_GET['cmd'] == 'force_login') {
			cf::$rq['force_login'] = true;
		}
        elseif (self::$rq == "login" ){
            WebappAdminModel::SetOrValidateRequestSrc();
        }
    }

    protected function inHead() {
        cf::$pg["title"] = "Admin";
        cf::appendToHeadTag(WP."add/admin/login.css","css");
        msg::prepare();
    }

    protected function setPgRq($C = self::CONTROLLER){
        self::$pageToControllerMethods += array(count(self::$pageToControllerMethods)=>"Login","LoginProcess","Logout");
        
        $M = App_AdminModel::GetModel($C);
        $pg["login"]        =   array("url" => "login", "title" => "Login");
        $pg["login-recover"]=   array("url" => "login-recover", "title" => "Login Recovery");
        $pg["login-reset"]=   array("url" => "login-reset", "title" => "Login Reset");
        $pg["logout"]       =   array("url" => "logout", "title" => "Logout");
        $pg["title"] = "Login";

        //$this->setControlerProperties(self::$controller = "WebappAdminController", array("pg", "id", "data", "rq"));
        
        foreach(($C::$pg = $pg) as $k=>$v){
            if(is_array($v) && isset($v["url"]) && $v["url"]===cf::$rq["url"]){
                self::$rq = $C::$rq = WebappAdminController::$rq = $k;
                break;
            }
        }

        //self::$pageToControllerMethods = array(/*"LoginRecoverComplete","LoginRecoverChangePass","LoginRecoverProcess","LoginRecoverForm",*/"Login","LoginProcess","Logout");
    }

}
$adminSection = new adminLogin();

$adminSection->Header("admin/webpage--start(header)");
if(isset($_POST['save-btn']) && $_POST['save-btn'] == 'Send me link'){
		echo '<center><div style="width:80%">';
	    $adminSection->SendLinkForgotPassword();
		echo '</center></div>';
}
elseif(cf::$rq['force_login']){
	$adminSection->ForceLogin();
}
elseif (cf::$rq['recover']) {
		$adminSection->ForgotPasswordForm();
}
elseif (cf::$rq["url"] == "login"){
	
    if(filter_has_var(INPUT_POST, 'user')) {
        $adminSection->LoginProcess();
    } else {
		echo '<center><div style="width:80%">';
        $adminSection->Login();
		echo '</center></div>';
    }
} else if(cf::$rq["url"] == "logout"){
    $adminSection->Logout();
}

$adminSection->Footer("admin/webpage--stop(footer)");