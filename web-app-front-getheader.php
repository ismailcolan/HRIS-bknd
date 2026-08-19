<?php
/** The Offices (admin) class */

class FakeClass extends PageFront{
	
	 const CONTROLLER = "EmployeeFrontController";
     public function __construct($id = 0, $C = self::CONTROLLER)
    {
        parent::__construct();

        $M = App_AdminModel::GetModel($C);

        $C::$id = !in_array($C::$rq, array("hired", "inactive-hired", "manager-hired")) && $id > 0 ? $id : null;

        $C::$data = isset($C::$id) ? $M::dataById($id) : null;

        AccessModel::GrantAccessAndActions($C);
    }
	
	protected function setPgRq($C = self::CONTROLLER)
    { 
        $M = App_AdminModel::GetModel($C);

        $pg["profile"] = array("url" => "profile");
        $pg["recover"] = array("url" => "login-recover");
		 $pg["view-emp-iammanager"] = array("url" => "view-emp-iammanager");

        $pg["tbl"] = $M::$dt["tbl"];
        $pg["key"] = $M::$dt["key"];
        $pg["pfx"] = $M::$dt["pfx"];
        self::$pg = $pg;

        $request = strlen($url = filter_input(INPUT_GET, "rq")) > 0 ? $url : "profile";
//echo filter_input(INPUT_GET, "rq").' ';echo $request;exit;
        switch ($request) {
            case "profile":
                $rq = "profile";
                break;
            case "my-profile":
                $rq = "my-profile";
                break;
            case "login-recover":
                $rq = "login-recover";
                break;
			  case "login-recover-set-pass":
                $rq = "login-recover-set-pass";
                break;
            case "sign-out":
                $rq = "sign-out";
                break;
			  case "payroll":
                $rq = "payroll";
                break;
            case "change-password":
                $rq = "change-password";
                break;
			  case "view-emp-iammanager":
                $rq = "view-emp-iammanager";
                break;
			  case "getheader":
                $rq = "getheader";
                break;
            default:
                $rq = "profile";
        }
		
        self::$rq = $rq;

        self::$pageToControllerMethods = array("Payroll","Profile", "recoverPassword", "signOut", "changePassword");
        $this->setControlerProperties(self::$controller = $C, array("rq", "pg"));
		
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

$aS = new FakeClass();

$cmd=filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING);
ob_start();
$aS->ImgsSetup();
$aS->HTMLHeader();
$out = ob_get_contents();
$_SESSION['front_header']=$out;
ob_end_clean();
header('Location:'.$cmd);
	


