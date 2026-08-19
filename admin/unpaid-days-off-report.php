<?php
/** The Offices (admin) class */
class UnpaidDaysOffReport extends PageAdmin
{
    const CONTROLLER = "UnpaidDaysOffReportController";

    public function __construct($id=0, $C = self::CONTROLLER) {
        parent::__construct();
        
        $M = App_AdminModel::GetModel($C);
        
        if($C::$rq == "inactive-list") { $C::$rq = "list"; }

        $C::$id = !in_array($C::$rq, array("list", "inactive-list")) && $id>0 ? $id : null;
        $C::$data = isset($C::$id) ? $M::dataById($id) : null;
        
        AccessModel::GrantAccessAndActions($C);
    }

    protected function inHead($C=self::CONTROLLER) {
        msg::prepare();
        if(in_array($C::$rq, array("list", "inactive-list", "summary"))) {
            table::prepare();
        }
        cf::appendToHeadTag(ADD. "admin/" . $C::$pg["list"]["url"] . ".css", "css");
        if($C::$rq==="summary"){
            cf::appendToHeadTag(ADD. "admin/spendings.css", "css");
        }
    }

    protected function setPgRq($C = self::CONTROLLER, $pg=array("item"=>"Office", "items"=>"Offices")){
        $M = App_AdminModel::GetModel($C);

        $pg["list"]         =   array("url" => strtolower($pg["items"]), "title" => $pg["items"]);
        $pg["inactive-list"]=   array("url" => "inactive-".strtolower($pg["items"]), "title" => "Inactive ".strtolower($pg["items"]));
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

$adminSection = new UnpaidDaysOffReport();//(int)filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT)

if(intval(AppModel::$admin["waaAFID"]) > 0) {
    cf::Redirect(ADMIN);
}

msg::prepare();
table::prepare();

$adminSection->Header();



$adminSection->MainUnpaidDaysOffReport();
$adminSection->Footer();