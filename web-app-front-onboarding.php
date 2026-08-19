<?php

class OnboardingPage extends PageFront {
    
    const CONTROLLER = "OnboardingFrontController";

    public function __construct($C = self::CONTROLLER) {
        parent::__construct($C);
    }

    protected function inHead($C = self::CONTROLLER) {
        msg::prepare();
        table::prepare();
    }
    
    protected function setPgRq($C = self::CONTROLLER){
        $M = App_AdminModel::GetModel($C);
        
        $pg["pending"]         =   array("url" => "Onboarding Tasks");
        $pg["tbl"]          =   $M::$dt["tbl"];
        $pg["key"]          =   $M::$dt["key"];
        $pg["pfx"]          =   $M::$dt["pfx"];
        self::$pg = $pg;
        
        $request = strlen($url = filter_input(INPUT_GET, "rq"))>0 ? $url : "pending";
        switch($request){
            case "pending":
                $rq = "pending";
                break;
            default: $rq = "pending";
        }
        self::$rq = $rq;
        
        
        self::$pageToControllerMethods = array("signDoc","SignOnboardingTasks","OnboardingTasks", "Completed", "Responsible", "MustSigned");
        $this->setControlerProperties(self::$controller = $C, array("rq", "pg"));
    }

}

$page = new OnboardingPage();
if (isset($_GET['action']) &&  $_GET['action']=='sign_doc')  {$page->signDoc();}


$page->HTMLHeader();

msg::get();

if($page::$rq === "pending") {

    if(AccessModel::$usr === null) {
        $page->MustSigned();
    }  else {
		 if (isset($_GET['cmd']) && $_GET['cmd']=='sign')
			$page->SignOnboardingTasks();
		 else if (isset($_GET['cmd']) && $_GET['cmd']=='sign-doc')
			$page->signDoc();
		 else
			$page->OnboardingTasks();
    }
} else { cf::vdd($page::$rq); }

$page->HTMLFooter();
