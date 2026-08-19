<?php
/** The Front Page 
  * @see \web-app-router.php 
  * @see Router::$classFilename("front-page"); */

/** The front page class
 * @example http://www.example.com/... */
class StaticPage extends PageFront {
    
    const CONTROLLER = "PageController";
    
    
    private static $errors = null;


    public function __construct() {
        parent::__construct();
        PageController::TitleAndMetaTags();
    }
    
    final function Page(){
        PageController::Navigation();
        PageController::Content();
    }
    
    
    private static function contactSendEmail(){
        $options =  array(
                        "Name"=>FILTER_SANITIZE_ENCODED,
                        "Company"=>FILTER_SANITIZE_STRING,
                        "Phone"=>FILTER_SANITIZE_STRING,
                        "Email"=>FILTER_VALIDATE_EMAIL,
                        "Comments"=>FILTER_SANITIZE_STRING,
                    );
		$fd = filter_var_array($_POST["fd"], $options);
        
		//$fd = array_map("htmlentities", $fd, array(ENT_QUOTES));
		//$fd = array_map('stripslashes', $fd);
		$subject = 'Attorneys.org - Contact message';

        $errors = array();
        
		if(empty($fd["Name"])){
            $errors["Name"]="Please Enter a Name";
		}
		if(empty($fd['Email'])){
			$errors["Email"] = "Please Enter Email";
		} else if(!preg_match('/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i', $fd['Email'])){
			$errors["Email"] = "Please Enter Valid Email";
		}
		$msg = '';
		foreach($fd as $key => $value){
			if($key!='BotBootInput'){
				$msg .= '<b>' . $key . ':</b> ' . ($key==="Comments"?("<br />".nl2br($value)):$value) . '<br /><br />';
			}
		}

        if(empty($errors)){
            if(cf::sendMail(EMAIL_CONTACTUS, $subject, $msg, $fd['Name'], $fd['Email'])){
                $_SESSION["success"] = true;
                cf::Redirect(WP."Contact-Us?success");
                //header('Location: '.WP."Contact-Us");
                die();
            } else {
                $_SESSION["success"] = false;
            }
        } else {
            self::$errors = $errors;
        }
        ini_set('display_errors', '1');
//cf::vdd($_POST, LOCAL, $_SESSION,$errors);
		return array($status, $errors);
	}
    
    protected function inHead() {
        if(self::$rq==="contact"){
            msg::prepare();
            //cf::appendToHeadTag(ADD."contact-us.js", "script", array("defer"=>true));
            cf::appendToHeadTag(ADD."helpers/form/add/form.css", "css");
        }
    }
    
    protected function setPgRq($C = self::CONTROLLER){
        $pageUrl = filter_has_var(INPUT_GET, "page-url") ? filter_input(INPUT_GET, "page-url", FILTER_SANITIZE_URL) : null;
        if($pageUrl === "captcha"){
            captcha::generate();
        }
        
        $urls = array_merge(array_keys(PageModel::$navL), array_keys(PageModel::$navR), array_keys(PageModel::$navFooter2Add), PageModel::$noNavPages);
        
        if(in_array(strtolower($pageUrl), $urls)){ 
            self::$rq = $pageUrl;
            $this->setControlerProperties(self::$controller = $C, array("rq", "pg"));
        } else {
            $this->web404(); 
        }

    }

}


$page = new StaticPage();


$page->HTMLHeader();

$page->Page();

$page->HTMLFooter();
