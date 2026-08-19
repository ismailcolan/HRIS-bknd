<?php

class JobsPage extends PageFront {
    
    const CONTROLLER = "JobFrontController";

    public function __construct($C = self::CONTROLLER) {
        parent::__construct($C);
    }

    protected function inHead($C = self::CONTROLLER) {
        msg::prepare();
    }
    
    protected function setPgRq($C = self::CONTROLLER){
        $M = App_AdminModel::GetModel($C);
        
        $pg["jobs"]         =   array("url" => "jobs");
        $pg["tbl"]          =   $M::$dt["tbl"];
        $pg["key"]          =   $M::$dt["key"];
        $pg["pfx"]          =   $M::$dt["pfx"];
        self::$pg = $pg;
        
        $request = strlen($url = filter_input(INPUT_GET, "rq"))>0 ? $url : "jobs";
        switch($request){
            case "jobs":
                $rq = "jobs";
                break;
            default: $rq = "jobs";
        }
        self::$rq = $rq;
        
        
        self::$pageToControllerMethods = array("Jobs");
        $this->setControlerProperties(self::$controller = $C, array("rq", "pg"));
    }

}

$page = new JobsPage();

$page->HTMLHeader();
msg::get();

if($page::$rq === "jobs") {
    $page->Jobs();
} else { cf::vdd($page::$rq); }

$page->HTMLFooter();
