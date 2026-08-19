<?php
/** The Orders (admin) class */
class offices extends PageAdmin
{
    const CONTROLLER = "PositionOfficeController";

    public function __construct($id=0, $C = self::CONTROLLER) {
        parent::__construct();

        $M = App_AdminModel::GetModel($C);

        $C::$id = !in_array($C::$rq, array("list", "inactive-list")) && $id>0 ? $id : null;
        $C::$data = isset($C::$id) ? $M::dataById($id) : false;

        AccessModel::GrantAccessAndActions($C);
    }

    protected function inHead($C=self::CONTROLLER) {
        msg::prepare();
        if(in_array($C::$rq, array("list", "inactive-list"))) {
            table::prepare();
        }
    }

    protected function setPgRq($C = self::CONTROLLER, $pg=array("item"=>"Position", "items"=>"Positions")){
        $M = App_AdminModel::GetModel($C);

        $pg["list"]         =   array("url" => strtolower($pg["items"]), "title" => $pg["items"]);
        $pg["inactive-list"]=   array("url" => "inactive-".strtolower($pg["items"]), "title" => "Inactive ".strtolower($pg["items"]));
        $pg["summary"]      =   array("url" => strtolower($pg["item"]), "title" => $pg["item"]);
        $pg["details"]      =   array("url" => strtolower($pg["item"])."-details", "title" => $pg["item"]);
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

$adminSection = new offices((int)filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT));

$adminSection->Header();

$adminSection->All();

$adminSection->Footer();