<?php

class AnnouncementsPage extends PageFront {
    
    const CONTROLLER = "AnnouncementsController";

    public function __construct($C = self::CONTROLLER) {
        parent::__construct($C);
    }

    protected function inHead($C = self::CONTROLLER) {
        msg::prepare();
    }
    
    protected function setPgRq($C = self::CONTROLLER){
        $M = App_AdminModel::GetModel($C);
        
        $pg["jobs"]         =   array("url" => "announcements");
        $pg["tbl"]          =   $M::$dt["tbl"];
        $pg["key"]          =   $M::$dt["key"];
        $pg["pfx"]          =   $M::$dt["pfx"];
        self::$pg = $pg;
        
        $request = strlen($url = filter_input(INPUT_GET, "rq"))>0 ? $url : "announcements";
        switch($request){
            case "announcements":
                $rq = "announcements";
                break;
            default: $rq = "announcements";
        }
        self::$rq = $rq;
        
        
        self::$pageToControllerMethods = array("Announcements");
        $this->setControlerProperties(self::$controller = $C, array("rq", "pg"));
    }

}

$page = new AnnouncementsPage();

$page->HTMLHeader();

msg::get();

if($page::$rq === "announcements") {
    $page->Announcements();
} else { cf::vdd($page::$rq); }

$page->HTMLFooter();
