<?php
/** The Orders (admin) class */
class Positions extends PageAdmin
{
    const CONTROLLER = "PositionController";

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
        $pg["assignments"]  =   array("url" => strtolower($pg["item"])."-assignments", "title" => "Credentialing, Onboarding & Off-boarding");
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

$adminSection = new Positions((int)filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT));

if(intval(AppModel::$admin["waaAFID"]) > 0 && !RoleHelper::isHRManager()) {
    cf::Redirect(ADMIN);
}

$adminSection->Header();
if(in_array($adminSection::$rq, array("list", "inactive-list"))) {
    $adminSection->All();
} else if($adminSection::$rq === "summary") {
    $adminSection->Summary();
}  else if($adminSection::$rq === "details") {
    if(filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING)=== "trash") {
        $adminSection->Delete();
    } else if(filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING)=== "restore") {
        $adminSection->Reactivate();
    } else if(filter_input(INPUT_SERVER, "REQUEST_METHOD") === "POST" || $_SERVER['REQUEST_METHOD'] === 'POST') {
        $adminSection->DetailsSave();
    } else {
        $adminSection->Details();
    }
} else if($adminSection::$rq === "assignments") {
    if(filter_input(INPUT_SERVER, "REQUEST_METHOD") === "POST" || $_SERVER['REQUEST_METHOD'] === 'POST') {
        if(filter_has_var(INPUT_POST, "copy-btn")) {
            $adminSection->AssignmentsCopy();
        } else {
            $adminSection->AssignmentsSave();
        }
    } else {
        $adminSection->Assignments();
    }
}

$adminSection->Footer();