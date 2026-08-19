<?php 
/** Description: Serves all login pages and tasks */
class SuperAdminLogin extends PageSuperAdmin {

    const CONTROLLER = "WebappSuperAdminController";
    private static $admin = array();

    public function __construct($C = self::CONTROLLER) { 
        parent::__construct();

        self::$admin["user"] = filter_has_var(INPUT_POST, "user") ? filter_input(INPUT_POST, "user", FILTER_SANITIZE_SPECIAL_CHARS) : null;
        self::$admin["pass"] = filter_has_var(INPUT_POST, "pass") ? filter_input(INPUT_POST, "pass", FILTER_SANITIZE_SPECIAL_CHARS) : null;

        if (self::$rq == "login" ){
            WebappAdminModel::SetOrValidateRequestSrc();
        }
    }

    protected function inHead() {
        cf::$pg["title"] = "Multy Company HRIS SUPER Admin Login";
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

$adminSection = new SuperAdminLogin();

$adminSection->Header();

if (cf::$rq["url"] == "login"){
    if(filter_has_var(INPUT_POST, 'user')) {
		
        $adminSection->LoginProcess();
    } else {
        $adminSection->Login();
    }
} else if(cf::$rq["url"] == "logout"){
	
    $adminSection->Logout();
}

$adminSection->Footer("superadmin/webpage--stop(footer)");