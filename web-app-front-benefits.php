<?php

class BenefitsPage extends PageFront {
    
    const CONTROLLER = "BenefitsFrontController";

    public function __construct($C = self::CONTROLLER) {
        parent::__construct($C);
    }

    protected function inHead($C = self::CONTROLLER) {
        msg::prepare();
        table::prepare();
    }
    
    protected function setPgRq($C = self::CONTROLLER){
		$M = App_AdminModel::GetModel($C);
		$pg["benefits"]  =   array("url" => "benefits");
		$pg["breadcrumb"] = array('Benefits');
        self::$pg = $pg;
		
		$request = "bеnеfits";
		switch($request){
			case "benefits":
				$rq = "benefits";
				break;
			default: $rq = "benefits";
		}
		self::$rq = $rq;
		
		
		self::$pageToControllerMethods = array("InitBenefits","HeaderBenefits");
		$this->setControlerProperties(self::$controller = $C, array("rq", "pg"));
		
	}
}

$page = new BenefitsPage();

/********
if (isset($_GET['cmd']) && $_GET['cmd']=='another-employee') {$page->ShowAnotherEmployee();exit;}
if (isset($_GET['cmd']) && $_GET['cmd']=='get_working_days') $page->GetDaysOff();
if (isset($_GET['cmd']) && $_GET['cmd']=='cancel_request') $page->CancelRequest();
*******/
//$page->HTMLHeaderOnly();
msg::get();
if (isset($_GET['cmd']) && $_GET['cmd']=='head')  $page->HeaderBenefits();
else $page->InitBenefits();

;
