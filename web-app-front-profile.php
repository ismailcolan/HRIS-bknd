<?php
/** front index page
 * @see \web-app-router.php
 * @see Router::$classFilename("front-page");
 */

/** The front page class
 * @example http://www.example.com/...
 */
class ProfilePage extends PageFront
{

    const CONTROLLER = "ProfileController";

    public function __construct($C = self::CONTROLLER)
    {
        parent::__construct($C);
    }

    protected function inHead($C = self::CONTROLLER)
    {
        msg::prepare();
		 table::prepare();
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
//echo '>>>>'.filter_input(INPUT_GET, "rq").' ';echo $request;exit;
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


$page = new ProfilePage();

$page->ImgsSetup();
$page->HTMLHeader();

//msg::get();
//echo $page::$rq;exit;
if ($page::$rq === "profile") {
    $page->Profile();
} elseif ($page::$rq === "sign-out") {
    $page->signOut();
} elseif ($page::$rq === "view-emp-iammanager") {
    ProfileController::showProfileIAmManagerFor();
} elseif ($page::$rq === "change-password") {
    $page->changePassword();
} elseif ($page::$rq === "login-recover") {
	
    $page->recoverPassword();
} elseif ($page::$rq === "login-recover-set-pass") {
    $page->setPassword();
} else {
    cf::vdd($page::$rq);
}

$page->HTMLFooter();
