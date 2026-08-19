<?php

class MyEmployeesPage extends PageFront {
    
    const CONTROLLER = "MyEmployeesController";

    public function __construct($C = self::CONTROLLER) {
        parent::__construct($C);
    }

    protected function inHead($C = self::CONTROLLER) {
        msg::prepare();
    }
    
    protected function setPgRq($C = self::CONTROLLER){
        $M = App_AdminModel::GetModel($C);
        
        $pg["my-employees"]   =   array("url" => "my-employees");
        //$pg["tbl"]          =   $M::$dt["tbl"];
        //$pg["key"]          =   $M::$dt["key"];
        //$pg["pfx"]          =   $M::$dt["pfx"];
        self::$pg = $pg;
        
        $request = "my-employess";
        switch($request){
            case "my-employess":
                $rq = "my-employess";
                break;
            default: $rq = "my-employess";
        }
        self::$rq = $rq;
        
        
        self::$pageToControllerMethods = array("MyEmployessInit","Test","DaysOffRequest",
				"ApproveRequest","DeclineRequest","MyEmployeesList","UpcommingDaysOff");
        $this->setControlerProperties(self::$controller = $C, array("rq", "pg"));
    }

}

$page = new MyEmployeesPage();

if (isset($_GET['cmd']) && $_GET['cmd']=='test') {$page->Test();exit;}
if (isset($_GET['cmd']) && $_GET['cmd']=='my-employees-list') {$page->MyEmployeesList();exit;}
if (isset($_GET['cmd']) && $_GET['cmd']=='upcomming-days-off') {$page->UpcommingDaysOff();exit;}
if (isset($_GET['cmd']) && $_GET['cmd']=='days-off-request') {$page->DaysOffRequest();exit;}
if (isset($_GET['cmd']) && $_GET['cmd']=='approve') {$page->ApproveRequest();}
if (isset($_GET['cmd']) && $_GET['cmd']=='decline') {$page->DeclineRequest();exit;}
$page->HTMLHeader();

if($page::$rq === "my-employess") {
    $page->MyEmployessInit();
} else { cf::vdd($page::$rq); }

$page->HTMLFooter();
