<?php 
/** The Front Page 
  * @see \web-app-router.php 
  * @see Router::$classFilename("front-page"); */

/** The Cron request class
 * @example http://www.example.com/... */
class FileRequest extends PageFront {
    
    public function __construct() {
        parent::__construct(false);
    }
    
    protected static function show404() {
        $page = new PageFront();
        $page->web404();
    }

    public function Execute(){
        $id = intval(filter_input(INPUT_GET, 'fid', FILTER_SANITIZE_NUMBER_INT));
        
        $rec = db::PrepareLoadSingleRow("select * from  fcfilesbyuser where 	fcfbuID={$id}");
        if($rec === false) {
            $rec = [];
        } 
        if(empty($rec)) {
            self::show404();
        } else {
           
			$filePath = "../add/customer".$_SESSION['customerID']."/uploads/files/".$rec['fcfbuFileSavedAs'];
			//echo $filePath.'<hr>';cf::pr($rec);exit;
            if(file_exists($filePath)) { 
			    $name=$rec['fcfbuFileName'].'.'.$rec['fcfbuFileЕxt'];
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

$page = new FileRequest();
if (isset($_GET['action']) && $_GET['action']=='set_file'){
  $res=	$page->SetFile($_GET['id'],$_GET['state']);
  echo $res;exit;
}
else
$page->Execute();