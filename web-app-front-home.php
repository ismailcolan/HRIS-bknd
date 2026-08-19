<?php

class IndexPage extends PageFront {
    
    const CONTROLLER = "HomeFrontController";

    private static $errors = null;

    public function __construct($C = self::CONTROLLER) {
        parent::__construct($C);
    }

    protected function inHead($C = self::CONTROLLER) {
        msg::prepare();
        cf::appendToHeadTag(ADD_CSS."home.css", "css");
    }
    
    protected function setPgRq($C = self::CONTROLLER){
        $M = App_AdminModel::GetModel($C);
        
        $pg["home"]         =   array("url" => "home");
        
        $pg["tbl"]          =   $M::$dt["tbl"];
        $pg["key"]          =   $M::$dt["key"];
        $pg["pfx"]          =   $M::$dt["pfx"];
        self::$pg = $pg;
        
        $request = strlen($url = filter_input(INPUT_GET, "rq"))>0 ? $url : "home";
        switch($request){
            case "home": 
                $rq = "home";
                break;
            default: $rq = "home";
        }
        self::$rq = $rq;
        
        
        self::$pageToControllerMethods = array("Home");
        $this->setControlerProperties(self::$controller = $C, array("rq", "pg"));
    }

}

$page = new IndexPage();

if (isset(AppModel::$usr["afID"])) {
    if(!filter_has_var(INPUT_GET, "autologin")) {
        cf::redirect(WP."profile");
    }
}

$page->HTMLHeader();

msg::get();
if($page::$rq === "home") {
    $page->Home();
} else { cf::vdd($page::$rq); }

$page->HTMLFooter();
