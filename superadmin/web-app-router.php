<?php 
/** The /admin (initial) file - Its execution is triggered via dir's .htaccess rule */
require_once(dirname(__DIR__) . "/web-app/boot/app-boot.php");

/** The /admin/ url router class - triggers dedicated (for the given URL) admin class.
 * @example called on "http://www.example.com/admin/login" request.
 * @example called on "http://www.example.com/admin/home" request.  
 * @category app-router */
class _adminIndex extends Router {
    
    public function __construct() {
        $rootInAdmin = (substr(trim(dirname(__FILE__),"\\/"),-10) === "superadmin");
        parent::__construct($rootInAdmin);
		
    }
    
    /** Loads the URL's dedicated class. 
     * @uses WebAppAdmin->web404(); if the class not exists */
    public function triggerAdminAppClass(){
        if(in_array(cf::$rq["class"], ["manager-for-interview-applications", "manager-for-decision-applications", "manager-manually-rejected-applications"])) {
            cf::$rq["class"] = "manager-applications";
        }

        try { 
            if(file_exists(cf::$rq["class"].'.php')){
				
                require_once(cf::$rq["class"].'.php');
				
            } else {
				
                throw new Exception (cf::$rq["class"].'.php does not exist');
            }
        } catch (Exception $e) {
            $page = new Page;
            $page->web404();
        }
		
		
    }
}

$page = new _adminIndex();

$page->triggerAdminAppClass();