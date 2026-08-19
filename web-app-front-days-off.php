<?php

class DaysOffPage extends PageFront {
    
    const CONTROLLER = "DaysOffFrontController";

    public function __construct($C = self::CONTROLLER) {
        parent::__construct($C);
    }

    protected function inHead($C = self::CONTROLLER) {
        msg::prepare();
        table::prepare();
    }
    
    protected function setPgRq($C = self::CONTROLLER){
		$M = App_AdminModel::GetModel($C);
		$pg["days-off"]  =   array("url" => "days-off");
		$pg["breadcrumb"] = array('Days Off');
        self::$pg = $pg;
		
		$request = "days-off";
		switch($request){
			case "days-off":
				$rq = "days-off";
				break;
			default: $rq = "days-off";
		}
		self::$rq = $rq;
		
		
		self::$pageToControllerMethods = array("ViewDaysOff","GetDaysOff","CancelRequest","ShowAnotherEmployee");
		$this->setControlerProperties(self::$controller = $C, array("rq", "pg"));
		
	}
}

$page = new DaysOffPage();


if (isset($_GET['cmd']) && $_GET['cmd']=='another-employee') {$page->ShowAnotherEmployee();exit;}
if (isset($_GET['cmd']) && $_GET['cmd']=='get_working_days') $page->GetDaysOff();
if (isset($_GET['cmd']) && $_GET['cmd']=='cancel_request') $page->CancelRequest();
$page->HTMLHeader();
msg::get();

if($page::$rq === "days-off") {
    $page->ViewDaysOff();
} else { cf::vdd($page::$rq); }

$page->HTMLFooter();
