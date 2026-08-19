<?php 
/** The page (Admin) class */
class PageSuperAdmin extends Page {

    protected static $timeStart = null, 
    $pageToControllerMethods = array("SuperAdminHomePage","SuperAdminCustomers","SuperAdminCustomersAjax",
	"SuperAdminCustomerActivate","SuperAdminCustomerDeactivate","SuperAdminCustomerEdit","SuperAdminCustomerSave",
	"SuperAdminCustomerAdd","SuperAdminCustomersSaveChanged",
	"SuperAdminCustomerSaveAdd","SuperAdminCustomersAdd");
    private static $accessFreeUrls = array("logout", "login");
    
		
    public function __construct() {
		
        parent::__construct();
		
        if(! isset($_SESSION['superadmin'])){
            if(!in_array(cf::$rq["url"], self::$accessFreeUrls)){
                cf::Redirect( SUPERADMIN . "login" . (!empty(cf::$rq["url"]) ? "?go=".urlencode(cf::$rq["url"]) : "") );
            }
        } else { 
            if(in_array(cf::$rq["url"], array("login"))){
                cf::Redirect( SUPERADMIN . ( filter_has_var(INPUT_GET, "go") ? filter_input(INPUT_GET,"go",FILTER_SANITIZE_URL) : "home" ) );
            }
        }
		
    }
	
	public function HeaderOnly($templates=null, $v=null) {
		  $class=cf::$rq["class"];
		  cf::$rq["class"]='';
        if(self::$error404===true){ 
            cf::$rq["class"]=null;
        }
        App_AdminController::$rq = self::$rq;


        if(is_null($templates)){
           App_AdminController::Headers(static::CONTROLLER, $templates, $v);
        }
        parent::Header($templates, $v);
		 cf::$rq["class"]=$class;
	}
	
	public static function isActive($urls){
		foreach($urls as $url){
			if(strpos($_SERVER['REQUEST_URI'],$url)!== false) return 'class="active"';
		}
		return 'class="inactive"';
	}
	
    public function Header($templates=null, $v=null) { 
        if(self::$error404===true){
            cf::$rq["class"]=null;
        }
		$v=array();
		$v["page-nav"]='
		<div id="menu">
		<ul class="top-level-menu" style="padding-left:0">
			<li '.self::isActive(['home']).'><a href="home">Home</a>
			</li>
			<li '.self::isActive(['custumers']).'><a href="customers">Customers</a>
			</li>
		</ul>
		</div>';
		/**********
		<ul class="second-level-menu">
					 <li '.self::isActive(['customers']).'><a href="home">List</a></li>
					 <li '.self::isActive(['New Cutomer']).'><a class="dropdown-item" href="credentialing">Credentialing</a></li>
				</ul>
		**********/
		
		$v["page-sub"]='';

		include '../web-app/webapp-html/superadmin/webpage--start(header).html';
		if (isset($_SESSION['superadmin'])) include '../web-app/webapp-html/superadmin/webpage--navigation.html';
		include '../web-app/webapp-html/superadmin/webpage--container-start.html';
    }
    
    public function Footer($templates=null, $v=null) {
		include '../web-app/webapp-html/superadmin/webpage--container-stop.html';
        include '../web-app/webapp-html/superadmin/webpage--stop(footer).html';
    }
    
}
