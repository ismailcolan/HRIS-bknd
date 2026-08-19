<?php
/** The Offices (admin) class */
class Resources extends PageAdmin
{
    const CONTROLLER = "ResourcesController";

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

    protected function setPgRq($C = self::CONTROLLER, $pg=array("item"=>"Resource", "items"=>"Resources")){
        $M = App_AdminModel::GetModel($C);
        
        $pg["list"]         =   array("url" => strtolower($pg["items"]), "title" => $pg["items"]);
        $pg["inactive-list"]=   array("url" => "inactive-".strtolower($pg["items"]), "title" => "Inactive ".strtolower($pg["items"]));
        $pg["details"]      =   array("url" => strtolower($pg["item"])."-details", "title" => $pg["item"]);
        $pg["summary"]      =   array("url" => strtolower($pg["item"]), "title" => $pg["item"]);
        $pg["resources-categories"] = array("url" => "resources-categories", "title" => "Categories");
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

$adminSection = new Resources((int)filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT));

if(intval(AppModel::$admin["waaAFID"]) > 0 && !RoleHelper::isHRManager()) {
    cf::Redirect(ADMIN);
}


if(filter_has_var(INPUT_GET, "cmd")) {
    $cmd = filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING);
    switch($cmd) {
        case 'addfile':  $adminSection->uploadResourceFile();  break;
        case 'removeFile':  $adminSection->removeResourceFile();  break;
        case 'addlink':  $adminSection->addResourceLink();  break;
        case 'removeLink':  $adminSection->removeResourceLink();  break;
    }
}


$adminSection->Header();

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
        msg::get();
        $adminSection->Details();
    }
} else {
    if($adminSection::$rq === "resources-categories") {
        print "Hello!";
    }
}

$adminSection->Footer();