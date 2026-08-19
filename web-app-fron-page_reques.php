<?php

class myFilesPage extends PageFront {
    
    const CONTROLLER = "EmployeeFrontController";

    public function __construct($C = self::CONTROLLER) {
		 
        parent::__construct($C);
		
    }

    protected function inHead($C = self::CONTROLLER) {
        msg::prepare();
		 table::prepare();
    }
    
    protected function setPgRq($C = self::CONTROLLER){
        $M = App_AdminModel::GetModel($C);
        
        $pg["jobs"]         =   array("url" => "employees");
        $pg["tbl"]          =   $M::$dt["tbl"];
        $pg["key"]          =   $M::$dt["key"];
        $pg["pfx"]          =   $M::$dt["pfx"];
        self::$pg = $pg;
        
        $request = strlen($url = filter_input(INPUT_GET, "rq"))>0 ? $url : "jobs";
        switch($request){
            case "employees":
                $rq = "employees";
                break;
            default: $rq = "employees";
        }
        self::$rq = $rq;
        
        
        self::$pageToControllerMethods = array("downloadFrontFile",
		  "EmployeesFrontController", "employeesFiles");//, "MustSigned");
        $this->setControlerProperties(self::$controller = $C, array("rq", "pg"));
    }

}

$page = new myFilesPage();

$page->HTMLHeader();

msg::get();

if($page::$rq === "employees") {

    if(AccessModel::$usr === null) {
        $page->MustSigned();
    }  else {
        if(filter_has_var(INPUT_GET, "ID")) {
            $page->employeesFiles();
        } else {
            $page->employeesFiles();
        }
    }
} else { cf::vdd($page::$rq); }

$page->HTMLFooter();
