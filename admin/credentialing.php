<?php
/** The Offices (admin) class */
class Credentialing extends PageAdmin
{
    const CONTROLLER = "CredentialingController";

    public function __construct($id=0, $C = self::CONTROLLER) {
        parent::__construct();

        $M = App_AdminModel::GetModel($C);

        $C::$id = !in_array($C::$rq, array("list", "inactive-list")) && $id>0 ? $id : null;
        $C::$data = isset($C::$id) ? $M::dataById($id) : null;
        
        AccessModel::GrantAccessAndActions($C);
    }

    protected function inHead($C=self::CONTROLLER) {
        msg::prepare();

        if(in_array($C::$rq, array("list", "inactive-list", "summary","report"))) {
            table::prepare();
        }
    }

    protected function setPgRq($C = self::CONTROLLER, $pg=array("item"=>"Credentialing", "items"=>"Credentialings")){
        $M = App_AdminModel::GetModel($C);
        
        $pg["list"]         =   array("url" => strtolower($pg["item"]), "title" => $pg["items"]);
        $pg["inactive-list"]=   array("url" => "inactive-".strtolower($pg["item"]), "title" => "Inactive ".strtolower($pg["items"]));
        $pg["details"]      =   array("url" => strtolower($pg["item"])."-details", "title" => $pg["item"]);
        $pg["summary"]      =   array("url" => strtolower($pg["item"]), "title" => $pg["item"]);
		$pg["report"]       =   array("url" => strtolower($pg["item"])."-report", "title" => $pg["item"]);
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

// $adminSection = new Credentialing((int)filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT));

$id = (int)filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$copyId = (int)filter_input(INPUT_GET, "copy", FILTER_VALIDATE_INT);

$adminSection = new Credentialing($copyId ?: $id);

if ($copyId > 0) {

    CredentialingController::$id = null;

    CredentialingController::$data["cName"] = "";
}

if(intval(AppModel::$admin["waaAFID"]) > 0) {
    cf::Redirect(ADMIN);
}

$adminSection->Header();

if($adminSection::$rq === "report") {
    $adminSection->Report();
}

if(in_array($adminSection::$rq, array("list", "inactive-list"))) {
    $adminSection->All();
} else if($adminSection::$rq === "summary") {
    $adminSection->Summary();
} else if($adminSection::$rq === "details") {
    if(filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING)=== "trash") {
        $adminSection->Delete();
    } else if(filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING)=== "restore") {
        $adminSection->Reactivate();
    } else if(filter_input(INPUT_SERVER, "REQUEST_METHOD") === "POST" || $_SERVER['REQUEST_METHOD'] === 'POST') {
        $adminSection->DetailsSave();
    } else {
        $adminSection->Details();
    }
}

$adminSection->Footer();