<?php
/** The Offices (admin) class */
class FileCategories extends PageAdmin
{
    const CONTROLLER = "FileCategoriesController";

    public function __construct($id=0, $C = self::CONTROLLER) {
        parent::__construct();
		 
        $M = App_AdminModel::GetModel($C);

        $C::$id = !in_array($C::$rq, array("list", "inactive-list","getfile")) && $id>0 ? $id : null;
        $C::$data = isset($C::$id) ? $M::dataById($id) : null;
        
        AccessModel::GrantAccessAndActions($C);
    }

    protected function inHead($C=self::CONTROLLER) {
        msg::prepare();

        if(in_array($C::$rq, array("list", "inactive-list", "summary"))) {
            table::prepare();
        }
    }

    protected function setPgRq($C = self::CONTROLLER, $pg=array("item"=>"Filecategories", "items"=>"File Categories")){
        $M = App_AdminModel::GetModel($C);

		$pg["list"]         =   array("url" => strtolower($pg["item"]), "title" => $pg["items"]);
		$pg["inactive-list"]=   array("url" => "inactive-".strtolower($pg["item"]), "title" => "Inactive ".strtolower($pg["items"]));
		$pg["details"]      =   array("url" => strtolower($pg["item"])."-details", "title" => $pg["item"]);
		$pg["summary"]      =   array("url" => strtolower($pg["item"]), "title" => $pg["item"]);
		$pg["getfile"]      =   array("url" => strtolower($pg["item"]), "title" => $pg["item"]);
     
		$pg["tbl"]          =   $M::$dt["tbl"];
		$pg["key"]          =    $M::$dt["key"];
		
		$pg["pfx"]          =   $M::$dt["pfx"];
        
        foreach($C::$pg = $pg as $k=>$v){
            if(is_array($v) && isset($v["url"]) && $v["url"]===cf::$rq["url"]){
                return $C::$rq = self::$rq = $k;
            }
        }

    }
}

msg::prepare();

$adminSection = new FileCategories((int)filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT));
if(intval(AppModel::$admin["waaAFID"]) > 0) {
    cf::Redirect(ADMIN);
}
if ($adminSection::$rq === null){
	
	$adminSection->downloadFile();
}
	
$adminSection->Header();

msg::get();
if (in_array($adminSection::$rq, array("list"))) {
    $adminSection->All();
} else if ($adminSection::$rq === "summary") {
    $adminSection->Summary();
} else if ($adminSection::$rq === "details") { 
    if (filter_has_var(INPUT_POST, "delete-btn")) {
        $adminSection->RemoveResources();
    }else if (isset($_POST["fiels-delete-btn"])) {
		$adminSection->deleteFilesFromCategory();
    }
	else if (isset($_POST["files-move-btn"])) {
		$adminSection->moveFilesToCategory();
    }
	else if (in_array(filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING), ["trash", "alert"]) || filter_has_var(INPUT_POST, "move-btn")) {
		$adminSection->Delete();
    } else if (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "restore") {
		$adminSection->Reactivate();
    } else if (filter_input(INPUT_SERVER, "REQUEST_METHOD") === "POST" || $_SERVER['REQUEST_METHOD'] === 'POST') {
		$adminSection->DetailsSave();
    } else {
        msg::prepare();
        $adminSection->Details();
    }
}

$adminSection->Footer();