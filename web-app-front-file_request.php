<?php 
/** The Front Page 
  * @see \web-app-router.php 
  * @see Router::$classFilename("front-page"); */

/** The Cron request class
 * @example http://www.example.com/... */
class frontFileRequest extends PageFront {
    
    const CONTROLLER = "EmployeeFrontController";
    
    public function __construct() {
        parent::__construct(false);
    }
    
    protected static function show404() {
        $page = new PageFront();
        $page->web404();
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

$page = new frontFileRequest();

$page->downloadFrontFile();