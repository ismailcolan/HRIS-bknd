<?php

class CalendarPage extends PageFront {
    
    const CONTROLLER = "CalendarController";

    public function __construct($C = self::CONTROLLER) {
        parent::__construct($C);
    }

    protected function inHead($C = self::CONTROLLER) {
        msg::prepare();
    }
    
    protected function setPgRq($C = self::CONTROLLER){
        $M = App_AdminModel::GetModel($C);
        
        $pg["jobs"]         =   array("url" => "calendar");
        $pg["tbl"]          =   $M::$dt["tbl"];
        $pg["key"]          =   $M::$dt["key"];
        $pg["pfx"]          =   $M::$dt["pfx"];
        self::$pg = $pg;
        
        $request = strlen($url = filter_input(INPUT_GET, "rq"))>0 ? $url : "calendar";
        switch($request){
            case "calendar":
                $rq = "calendar";
                break;
            default: $rq = "calendar";
        }
        self::$rq = $rq;
        
        
        self::$pageToControllerMethods = array("Calendar");
        $this->setControlerProperties(self::$controller = $C, array("rq", "pg"));
    }

}

$page = new CalendarPage();

$page->HTMLHeader();

msg::get();

if($page::$rq === "calendar") {
    $page->Calendar();
} else { cf::vdd($page::$rq); }

$page->HTMLFooter();
