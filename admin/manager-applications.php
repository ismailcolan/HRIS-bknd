<?php
/** The Offices (admin) class */
class ManagerApplications extends PageAdmin
{
    const CONTROLLER = "ApplicationController";

    public function __construct($id=0, $C = self::CONTROLLER) {
        parent::__construct();
        
        $M = App_AdminModel::GetModel($C);
        $C::$id = $id>0 ? $id : null;
        $C::$data = isset($C::$id) ? $M::dataById($id) : null;

        AccessModel::GrantAccessAndActions($C);
    }

    protected function inHead($C=self::CONTROLLER) {
        msg::prepare();
        table::prepare();
    }

    protected function setPgRq($C = self::CONTROLLER, $pg=array("item"=>"Application", "items"=>"Applications")){
        $M = App_AdminModel::GetModel($C);

        $pg["all"] =   array("url" => cf::$rq["url"], "title" => "All applications");
        $pg["details"]      =   array("url" => cf::$rq["url"], "title" => "Application details");
        $pg["summary"]      =   array("url" => cf::$rq["url"], "title" => "Application details");
        $pg["tbl"]          =   $M::$dt["tbl"];
        $pg["key"]          =   $M::$dt["key"];
        $pg["pfx"]          =   $M::$dt["pfx"];
        
        foreach($C::$pg = $pg as $k=>$v){
            if(is_array($v) && isset($v["url"]) && $v["url"]===cf::$rq["url"]){
                return $C::$rq = self::$rq = $k;
            }
        }
    }

    public static function ImgsSetup()
    {
        img::$setup = array(); //Its better to reset the values
        img::$setup["path"] = ApplyFormModel::IMG_PATH;
        img::$setup["crop"] = "inline";
        img::$setup["width"] = "320";
        img::$setup["height"] = "320";
        img::$setup["disable-delete"] = true;
        img::$setup["allowed-extensions"] = array("jpg", "png");
        img::prepare();
    }

}

$adminSection = new ManagerApplications((int)filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT));
$adminSection->ImgsSetup();
$adminSection->Header();
    if(ApplicationController::$id) {
        if(filter_has_var(INPUT_POST, "save-btn")) {
            $adminSection->DetailsSaveForManager();
        } else {
            $adminSection->DetailsForManager();
        }
    } else {
        $adminSection->AllForManager();
    }
$adminSection->Footer();