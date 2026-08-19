<?php

class PayrollPage extends PageFront {
    
    const CONTROLLER = "PayrollController";

    public function __construct($C = self::CONTROLLER) {
        parent::__construct($C);
    }

    protected function inHead($C = self::CONTROLLER) {
        msg::prepare();
    }
    
    protected function setPgRq($C = self::CONTROLLER){
        $M = App_AdminModel::GetModel($C);
        
        $pg["payroll"]         =   array("url" => "payroll");
        //$pg["tbl"]          =   $M::$dt["tbl"];
        //$pg["key"]          =   $M::$dt["key"];
        //$pg["pfx"]          =   $M::$dt["pfx"];
        self::$pg = $pg;
        
        $request = "payroll";
        switch($request){
            case "payroll":
                $rq = "payroll";
                break;
            default: $rq = "payroll";
        }
        self::$rq = $rq;
        
        
        self::$pageToControllerMethods = array("Payroll");
        $this->setControlerProperties(self::$controller = $C, array("rq", "pg"));
    }

}

$page = new PayrollPage();

$page->HTMLHeader();
//msg::get();

if($page::$rq === "payroll") {
    $page->Payroll();
} else { cf::vdd($page::$rq); }

$page->HTMLFooter();
