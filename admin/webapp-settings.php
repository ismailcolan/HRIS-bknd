<?php 
/** Description: Serves all login pages and tasks */
class Settings extends PageAdmin {
    /*protected static $id, $data, $controller;*/
    const CONTROLLER = "WebappAdminSettingsController";
    
    public function __construct($C = self::CONTROLLER) {
        parent::__construct();
        $M = App_AdminModel::GetModel($C);
        
        $C::$data = $M::LoadKeyValueArrFromTable($M::$dt["tbl"], $M::$dt["key"], "asValue");
        //$C::$data = WebappAdminSettingsModel::data();
    }
    
    protected function inHead() {
        //cf::appendToHeadTag(ADD."admin/admin.css","css");
        msg::prepare();
    }
    
    protected function setPgRq($C = self::CONTROLLER){
        self::$pageToControllerMethods = array("Settings", "SettingsSave");
        
        $pg["settings"]        =   array("url" => "settings", "title" => "Settings");
        $pg["title"] = $pg["settings"]["title"];
        $pg["breadcrumbs-ending"] = array($pg["settings"]["title"]);

        //$this->setControlerProperties(self::$controller = "WebappAdminController", array("pg", "id", "data", "rq"));
        
        foreach($C::$pg = $pg as $k=>$v){
            if(is_array($v) && isset($v["url"]) && $v["url"]===cf::$rq["url"]){
                $C::$rq = self::$rq = $k;
                break;
            }
        }
    }

}
$adminSection = new Settings();

if(intval(AppModel::$admin["waaAFID"]) > 0) {
    cf::Redirect(ADMIN);
}

$adminSection->Header();

if ($adminSection::$rq == "settings"){
    if(filter_has_var(INPUT_POST, 'settings-form')) {
        $adminSection->SettingsSave();
    } else {
        $adminSection->Settings();
    }
}

$adminSection->Footer();