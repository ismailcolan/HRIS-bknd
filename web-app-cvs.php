<?php 
/** The Front Page 
  * @see \web-app-router.php 
  * @see Router::$classFilename("front-page"); */

/** The Cron request class
 * @example http://www.example.com/... */
class CVSRequest extends PageFront {
    
    const CONTROLLER = "CronController";
    
    public function __construct() {
        parent::__construct(false);
    }
    
    protected static function show404() {
        $page = new PageFront();
        $page->web404();
    }

    public function CVSExecute(){
        $id = intval(filter_input(INPUT_GET, 'cvid', FILTER_SANITIZE_NUMBER_INT));
        $name = $_GET['cvname'];//$name = filter_input(INPUT_GET, 'cvname', FILTER_SANITIZE_STRING);

        $rec = CVModel::dataById($id);
		//echo '>>>';cf::pr($rec);exit;
/*******
 if($name != $rec['cvFileName']) {
                var_dump($name, $rec['cvFileName']);
                self::show404();
            } else
***********/
        if(empty($rec)) {
            self::show404();
        } else {
           if(file_exists($rec['filePath'])) {
                header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
                header("Cache-Control: public");
                header("Content-Type: application/binary");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length:".filesize($rec['filePath']));
                header("Content-Disposition: attachment; filename=".$rec['cvFileName']);
                readfile($rec['filePath']);
                die();
            } else {
                self::show404();
            }
        }
    }
}

$page = new CVSRequest();

$page->CVSExecute();