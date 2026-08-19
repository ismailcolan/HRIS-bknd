<?php 
/** The Front Page 
  * @see \web-app-router.php 
  * @see Router::$classFilename("front-page"); */

/** The Cron request class
 * @example http://www.example.com/... */
class ResourceRequest extends PageFront {
    
    public function __construct() {
        parent::__construct(false);
    }
    
    protected static function show404() {
        $page = new PageFront();
        $page->web404();
    }

    public function Execute(){
        $id = intval(filter_input(INPUT_GET, 'resid', FILTER_SANITIZE_NUMBER_INT));
        $name = filter_input(INPUT_GET, 'resname', FILTER_SANITIZE_STRING);

        $rec = db::PrepareLoadSingleRow("select * from resource_items where riID={$id} and riType='File' and riActive='Yes'");
        if($rec === false) {
            $rec = [];
        } 
        if(empty($rec)) {
            self::show404();
        } else {
            $filePath = "add/uploads/resources/file_{$id}";
            if($name != $rec['riName']) {
                self::show404();
            } elseif(file_exists($filePath)) { 
                header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
                header("Cache-Control: public");
                header("Content-Type: application/binary");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length:".filesize($filePath));
                header('Content-Disposition: attachment; filename="'.$name.'"');
                readfile($filePath);
                die();
            } else {            
                self::show404();
            }
        }
    }
}

$page = new ResourceRequest();

$page->Execute();