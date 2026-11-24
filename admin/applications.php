<?php
/** The Offices (admin) class */
class Applications extends PageAdmin
{
    const CONTROLLER = "ApplicationController";

    public function __construct($id=0, $C = self::CONTROLLER) {
        parent::__construct();
        
        $M = App_AdminModel::GetModel($C);
        

        $C::$id = !in_array($C::$rq, array("all", "pending", "manually-rejected", "auto-rejected", "for-interview", "for-decision")) && $id>0 ? $id : null;
        $C::$data = isset($C::$id) ? $M::dataById($id) : null;
        
        AccessModel::GrantAccessAndActions($C);
    }

    protected function inHead($C=self::CONTROLLER) {
        msg::prepare();
        if(in_array($C::$rq, array("all", "pending", "manually-rejected", "auto-rejected", "for-interview", "for-decision", "summary"))) {
            table::prepare();
        }
    }

    protected function setPgRq($C = self::CONTROLLER, $pg=array("item"=>"Application", "items"=>"Applications")){
        $M = App_AdminModel::GetModel($C);

        $pg["pending"] =   array("url" => strtolower($pg["items"]), "title" => "Pending applications");
        $pg["all"] =   array("url" => "all-".strtolower($pg["items"]), "title" => "All applications");
        $pg["manually-rejected"]=   array("url" => "manually-rejected-".strtolower($pg["items"]), "title" => "Manually rejected applications ");
        $pg["auto-rejected"]=   array("url" => "auto-rejected-".strtolower($pg["items"]), "title" => "Automatically rejected applications ");
        $pg["for-interview"]    =   array("url" => "for-interview-".strtolower($pg["items"]), "title" => "For interview applications ");
        $pg["for-decision"]    =   array("url" => "for-decision-".strtolower($pg["items"]), "title" => "For decision applications ");

        $pg["details"]      =   array("url" => strtolower($pg["item"])."-details", "title" => "Job opening");
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

$adminSection = new Applications((int)filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT));



$adminSection->ImgsSetup();
$adminSection->Header();


if(in_array($adminSection::$rq, array("all", "pending", "manually-rejected", "auto-rejected", "for-interview", "for-decision"))) {
    $adminSection->All();
} else if($adminSection::$rq === "summary") {
    $adminSection->Summary();
} else if($adminSection::$rq === "details") {
    if(cf::requestStr(INPUT_GET, "cmd") === "trash") {
        $adminSection->Delete();
    } else if(cf::requestStr(INPUT_GET, "cmd") === "restore") {
        $adminSection->Reactivate();
    } else if (
        filter_input(INPUT_SERVER, "REQUEST_METHOD") === "POST"
        || (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST')
    ) {
        $detailCmd = cf::requestStr(INPUT_GET, "cmd");
        $postToDetails = in_array($detailCmd, array('save-note', 'update-note', 'assign-interviewers'), true);
        if (RoleHelper::isInterviewer()) {
            // Interview notes use dedicated cmd actions handled in ApplicationController::Details()
            $adminSection->Details();
        } elseif ($postToDetails) {
            $adminSection->Details();
        } else {
            $adminSection->DetailsSave();
        }
    } else if (cf::requestStr(INPUT_GET, "cmd") === "delete-note") {
        $adminSection->Details();
    } else {
        $adminSection->Details();
    }
}

$adminSection->Footer();