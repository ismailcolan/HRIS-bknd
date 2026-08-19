<?php
/** The Offices (admin) class */
class UnsignLimit extends PageAdmin
{
    const CONTROLLER = "UnsignLimitController";

    public function __construct($id=0, $C = self::CONTROLLER) {
        parent::__construct();

        $M = App_AdminModel::GetModel($C);

        $C::$id = !in_array($C::$rq, array("list", "inactive-list")) && $id>0 ? $id : null;
        $C::$data = isset($C::$id) ? $M::dataById($id) : null;
        
        AccessModel::GrantAccessAndActions($C);
    }

    protected function inHead($C=self::CONTROLLER) {
        msg::prepare();

        if(in_array($C::$rq, array("list", "inactive-list", "summary"))) {
            table::prepare();
        }
    }

    protected function setPgRq($C = self::CONTROLLER, $pg=array("item"=>"General", "items"=>"General")){
        $M = App_AdminModel::GetModel($C);
        
        $pg["list"]         =   array("url" => strtolower($pg["item"]), "title" => $pg["items"]);
        $pg["inactive-list"]=   array("url" => "inactive-".strtolower($pg["item"]), "title" => "Inactive ".strtolower($pg["items"]));
        $pg["details"]      =   array("url" => strtolower($pg["item"])."-details", "title" => $pg["item"]);
        $pg["summary"]      =   array("url" => strtolower($pg["item"]), "title" => $pg["item"]);
        $pg["tbl"]          =   $M::$dt["tbl"];
        $pg["key"]          =   $M::$dt["key"];
        $pg["pfx"]          =   $M::$dt["pfx"];
        
        foreach($C::$pg = $pg as $k=>$v){
            if(is_array($v) && isset($v["url"]) && $v["url"]===cf::$rq["url"]){
                return $C::$rq = self::$rq = $k;
            }
        }
    }
}

$adminSection = new UnsignLimit();
if(intval(AppModel::$admin["waaAFID"]) > 0) {
    cf::Redirect(ADMIN);
}

$adminSection->Header();

if (isset($_POST['ADMIN_UNSIGN_SETTINGS'])) {$adminSection->SaveAdminUNSIGN_SETTINGS();}
msg::get();
$adminSection->EditAdmin_UNSIGN_SETTINGS();
$adminSection->Footer();