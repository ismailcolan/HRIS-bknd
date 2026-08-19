<?php

class ResourcesPage extends PageFront {
    
    const CONTROLLER = "ResourcesController";

    public function __construct($C = self::CONTROLLER) {
        parent::__construct($C);
    }

    protected function inHead($C = self::CONTROLLER) {
        msg::prepare();
    }
    
    protected function setPgRq($C = self::CONTROLLER){
        $M = App_AdminModel::GetModel($C);
        
        $pg["jobs"]         =   array("url" => "resources");
        $pg["tbl"]          =   $M::$dt["tbl"];
        $pg["key"]          =   $M::$dt["key"];
        $pg["pfx"]          =   $M::$dt["pfx"];
        self::$pg = $pg;
        
        $request = strlen($url = filter_input(INPUT_GET, "rq"))>0 ? $url : "resources";
        switch($request){
            case "resources":
                $rq = "resources";
                break;
            default: $rq = "resources";
        }
        self::$rq = $rq;
        
        
        self::$pageToControllerMethods = array("Categories", "Resources");
        $this->setControlerProperties(self::$controller = $C, array("rq", "pg"));
    }

}

$page = new ResourcesPage(); 

$page->HTMLHeader();

msg::get();
if($page::$rq === "resources") {
    if(filter_has_var(INPUT_GET, "cat")) {
        
        $page->Resources();
    } else {
        $page->Categories();
    }
} else { cf::vdd($page::$rq); }

$page->HTMLFooter();
