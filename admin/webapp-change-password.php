<?php 
/** Description: Serves all login pages and tasks */
class adminPassword extends PageAdmin {
    /*protected static $id, $data, $controller;*/
    const CONTROLLER = "WebappAdminController";
    
    public function __construct($C = self::CONTROLLER) {
        parent::__construct();

        if(self::$rq == "login"){
            AdminModel::LoginSetOrValidateRequestSrc();
        }
        
        $C::$id = (int)AppModel::$admin[WebappAdminModel::$dt["key"]];
        $C::$data = AppModel::$admin;
    }
    
    public function adminLogout(){
        WebAppAdminModel::unsetLoginToken("Admin");
        cf::Redirect(ADMIN."login");
    }
    
    protected function inHead() {
        //cf::appendToHeadTag(ADD."admin/admin.css","css");
        msg::prepare();
    }
    
    protected function setPgRq($C = self::CONTROLLER){
        self::$pageToControllerMethods = array("ChangePassword", "ChangePasswordProcess");
        
        $pg["password"]        =   array("url" => "password", "title" => "Change admin password");
        $pg["title"] = $pg["password"]["title"];
        $pg["breadcrumbs-ending"] = array($pg["password"]["title"]);

        //$this->setControlerProperties(self::$controller = "WebappAdminController", array("pg", "id", "data", "rq"));
        
        foreach($C::$pg = $pg as $k=>$v){
            if(is_array($v) && isset($v["url"]) && $v["url"]===cf::$rq["url"]){
                $C::$rq = self::$rq = $k;
                break;
            }
        }

        //self::$pageToControllerMethods = array("ChangePassword","ChangePasswordProcess");
    }

}
$adminSection = new adminPassword();

$adminSection->Header();

if ($adminSection::$rq == "password"){
    if(filter_has_var(INPUT_POST, 'waaPass')) {
        $adminSection->ChangePasswordProcess();
    } else {
        $adminSection->ChangePassword();
    }
} else if($adminSection::$rq == "logout"){
    $adminSection->Logout();
}

$adminSection->Footer();