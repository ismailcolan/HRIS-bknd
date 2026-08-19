<?php

/** The Offices (admin) class */
class EmpIHave extends PageFront
{
    const CONTROLLER = "ProfileController";
	
    public function __construct( $C = self::CONTROLLER)
    { 
	    
        parent::__construct();

        //$M = ApplicationModel::GetModel($C);
		/*
        $C::$id = !in_array($C::$rq, array("hired", "inactive-hired", "manager-hired")) && $id > 0 ? $id : null;

        $C::$data = isset($C::$id) ? $M::dataById($id) : null;

        AccessModel::GrantAccessAndActions($C);
	   */
    }

    protected function inHead($C = self::CONTROLLER)
    {
        msg::prepare();
        table::prepare();
    }

    protected function setPgRq($C = self::CONTROLLER, $pg = array("item" => "Employee", "items" => "Employees"))
    {
        $M = App_AdminModel::GetModel($C);

        $pg["hired"] = array("url" => strtolower($pg["items"]), "title" => $pg["items"]);
        $pg["inactive-hired"] = array("url" => "inactive-" . strtolower($pg["items"]), "title" => "Inactive " . strtolower($pg["items"]));
        $pg["manager-hired"] = array("url" => "manager-" . strtolower($pg["items"]), "title" => "Managers");
        $pg["details"] = array("url" => strtolower($pg["item"]) . "-details", "title" => $pg["item"]);
        $pg["summary"] = array("url" => strtolower($pg["item"]), "title" => $pg["item"]);
        $pg["password"] = array("url" => strtolower($pg["item"] . "-password"), "title" => "Set " . strtolower($pg["item"]) . " password");
        $pg["tbl"] = $M::$dt["tbl"];
        $pg["key"] = $M::$dt["key"];
        $pg["pfx"] = $M::$dt["pfx"];

        foreach ($C::$pg = $pg as $k => $v) {
            if (is_array($v) && isset($v["url"]) && $v["url"] === cf::$rq["url"]) {
                return $C::$rq = self::$rq = $k;
            }
        }
		
		self::$pageToControllerMethods = array("showProfileIAmManagerForJSON");
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


$Section = new EmpIHave();

$Section->ImgsSetup();

//$Section->Header();
$Section->showProfileIAmManagerForJSON();
/****
if (in_array($Section::$rq, array("hired", "inactive-hired", "manager-hired"))) {
    $Section->All();
} else if ($Section::$rq === "summary") {
    $Section->Summary();
} else if ($Section::$rq === "password") {
    $Section->SetPassword();
} else if ($Section::$rq === "details") {
    if (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "trash") {
        $Section->Delete();
    } else if (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "restore") {
        $Section->Reactivate();
    } else if (filter_input(INPUT_SERVER, "REQUEST_METHOD") === "POST" || $_SERVER['REQUEST_METHOD'] === 'POST') {
        $Section->DetailsSave();
    } else {

        $Section->CheckForUndoOnboarding();

        $Section->Details();
    }
}
*****/
$Section->Footer();