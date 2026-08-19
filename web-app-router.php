<?php 
/** The main (initial) file - Its execution is triggered via root .htaccess rule */
require_once(__DIR__ . "/web-app/boot/app-boot.php");

/** The app initial class used to trigger dedicated (for the given URL) PHP class.
 * @example will be used on "http://www.example.com/home" request. 
 * @category app-router */
class __frontIndex extends Router {
    
    public function __construct() {
		
        parent::__construct(false);
    }
    
    /** Loads the URL's dedicated class. 
     * @uses WebAppFront->web404(); if the class not exists
     * In case of URL slug (http://www.example.com/this-is-a-slug-url) the 
     * catched exceptions has also to be served from the main (index) script */
    public function triggerFrontAppClass(){
		//cf::pr(cf::$rq);exit;
        try {
			if(file_exists(cf::$rq["class"].'.php')){
				require_once(cf::$rq["class"].'.php');
            } else {
                throw new Exception (cf::$rq["class"].'.php does not exist');
            }
        } catch (Exception $e) {
            $page = new PageFront();
            $page->web404();
			
        }
    }
}

$server=strtoupper($_SERVER['REQUEST_URI']);

if(strpos($server,'/C/') !== false ) {
	$parts=explode('/',$server);
	$_SESSION['company']=str_replace('%20',' ',$parts[2]);
	db:: SwitchToRealUsingName($_SESSION['company']);
	cf::Redirect(PROTOCOL.$host.'/');
}
//echo '>>>>>>>>';print_r($_SESSION);
$page = new __frontIndex();

$page->triggerFrontAppClass();

?>