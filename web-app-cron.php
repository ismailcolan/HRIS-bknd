<?php 
/** The Front Page 
  * @see \web-app-router.php 
  * @see Router::$classFilename("front-page"); */

/** The Cron request class
 * @example http://www.example.com/... */
class CronRequest extends PageFront {
    
    const CONTROLLER = "CronController";
    
    public function __construct() {
        parent::__construct(false);
    }
    
    public function CronExecute($C = self::CONTROLLER){
        $M = App_AdminModel::GetModel($C);

        $request = filter_input(INPUT_GET,"request");
        $C::Run($request);
    }
}


    // define('LOCAL', false);
    // require_once 'web-app/helpers/cf/cfCore.php';
    // require_once 'web-app/helpers/cf/cf.php'; 

    // cf::sendMail("mypartners.bg@gmail.com", "This is a mail for testing ".filter_input(INPUT_GET,"request")." cron job", "Hello, everything works perfect!", "HRIS");
    // die();

//echo 6666666;
$page = new CronRequest();

$page->CronExecute();