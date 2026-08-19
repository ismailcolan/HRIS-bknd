<?php
/** The admin homepage
  * @see \admin\web-app-router.php 
  * @see Router::$classFilename("home"); */

/** The adminHome class - tigerred after the admin login
 * @example http://www.example.com/admin/home */
class SuperAdminCustomers extends PageSuperAdmin {
    
    const CONTROLLER = "SuperAdminCustomersController";
    
    public function __construct($C = self::CONTROLLER) {
        parent::__construct();  
    }
    
    protected function inHead($C = self::CONTROLLER) {
        msg::prepare();
        table::prepare();
    }
    
    protected function setPgRq($C = self::CONTROLLER, $pg = array()){
        $pg["view"]         =   array("url" => "home", "title" => "Dashboard");
        $pg["edit"]         =   array("url" => "home", "title" => "Edit dashboard");
    }
	
	public static function ImgsSetup()
    {
        img::$setup = array(); //Its better to reset the values
        img::$setup["path"] = ApplyFormModel::IMG_PATH;
        img::$setup["crop"] = "inline";
        img::$setup["width"] = "320";
        img::$setup["height"] = "320";
        img::$setup["disable-delete"] = true;
        img::$setup["allowed-extensions"] = array("jpg", "png");
        img::prepare();
    }
}


$adminSection = new SuperAdminCustomers();


if(isset($_GET['call']) && $_GET['call'] == 'ajax') $adminSection->SuperAdminCustomersAjax();
if (isset($_GET['cmd'])){
	if ($_GET['cmd'] == 'activate') 	$adminSection->SuperAdminCustomerActivate();
	if ($_GET['cmd'] == 'deactivate') 	$adminSection->SuperAdminCustomerDeactivate();
	
}
$adminSection->ImgsSetup();
img::prepare();
$adminSection->Header();
$host=$_SERVER['SERVER_NAME'].'/';

echo '
		<script src="'.PROTOCOL.$host.'add/helpers/table/add/table.js"></script>
		<link rel="stylesheet" href="'.PROTOCOL.$host.'add/helpers/table/add/table.css" />';
		
	if (isset($_GET['cmd'])) {	
		if ($_GET['cmd'] == 'edit') {
			if(isset($_POST['save-btn'])) {
				if(isset($_POST['action']) && $_POST['action'] == 'edit') {
					$adminSection->SuperAdminCustomersSaveChanged();
				}
			}
			else $adminSection->SuperAdminCustomerEdit();		
		}
		if ( $_GET['cmd'] == 'add') {
			if(isset($_POST['save-btn'])) {
				if(isset($_POST['action']) && $_POST['action'] == 'add') {
					$adminSection->SuperAdminCustomerSaveAdd();
				}
			}
			else $adminSection->SuperAdminCustomerAdd();
		}
	}
	else $adminSection->SuperAdminCustomers();
$adminSection->Footer();





