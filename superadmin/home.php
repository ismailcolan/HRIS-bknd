<?php
/** The admin homepage
  * @see \admin\web-app-router.php 
  * @see Router::$classFilename("home"); */

/** The adminHome class - tigerred after the admin login
 * @example http://www.example.com/admin/home */
class SuperAdminHome extends PageSuperAdmin {
    
    const CONTROLLER = "WebappSuperAdminController";
    
    public function __construct($C = self::CONTROLLER) {
        parent::__construct();     
    }

    private function adminHomeViewWelcome(&$welcomeTitle, &$welcomeText){
        if(in_array(AppModel::$admin["waaRole"],array("Admin","Editor"))){
            $welcomeText .= cf::getOutput("form", "tag", array("<span class=\"fa fa-pencil-square-o\"></span> Edit welcome section", "button", array("onclick"=>"location.assign('".self::$pg["view"]["url"]."?cmd=edit')", "class"=>"edit")));
        }
    }
    
    private function adminHomeEditWelcome(&$welcomeTitle, &$welcomeText){
        $C = self::CONTROLLER;
        ob_start();
		
        self::adminHomeValidateWelcome("client");
        form::codeStyle(true, 1, false);
        form::openTag("div",array("class"=>"form-panel"));
            form::$ofsetIndentsCount = 2;
            form::openTag("form",array("method"=>"post","validate"=>true));
                form::$ofsetIndentsCount = 3;
                form::inpField(
                    "Welcome title:",
                    "welcomeTitle",
                    $welcomeTitle,
                    array("autofocus"=>true,"fld-class"=>"width-100pc")
                );
                $ckSettings = cf::defaultCkSettings();
                $ckSettings["fld-class"]="width-100pc";
                form::inpCkEditor(
                    "Message:",
                    "welcomeText",
                    $welcomeText,
                    $ckSettings
                );
            form::$ofsetIndentsCount = 2;
            form::inpSaveCancel(ADMIN.$C::$pg["view"]["url"],array("fld-class"=>"width-50pc"));
            form::closeTag("form");
            form::codeStyle(true, 1, true);
        form::closeTag("div",array("class"=>"form-panel"));

        $welcomeTitle = null;
        $welcomeText = ob_get_clean();
    }
    
    private function adminHomeSaveWelcome(&$welcomeTitle, &$welcomeText){
        $C = self::CONTROLLER;
        self::adminHomeValidateWelcome("server");
        if(!empty(form::$error)) {	
    		self::adminHomeEditWelcome();
			return;
		}
        $title = filter_input(INPUT_POST, "welcomeTitle", FILTER_SANITIZE_STRING);
        $text = filter_input(INPUT_POST,"welcomeText");
        $data = $title."\r\n---End-Title-Start-Content---\r\n". $text;
        $f = fopen(APP_HTML . "admin/home-welcome-msg.md", 'w') or die("Can't open welcome data file.");
        fwrite($f, $data);
        fclose($f);
        msg::set(TXT_SAVE_OK,"success");
        cf::Redirect($C::$pg["view"]["url"]);
    }
    
    private function adminHomeValidateWelcome($type){
        val::init($type);
    }
    
    /*protected function adminChangeSettingsVisibility(){
        if(self::$adminSettingsTabVisibility){
            unset($_SESSION["admin-settings-visibility"]);
        } else {
            $_SESSION["admin-settings-visibility"] = true;
        }
        cf::Redirect($_SERVER['HTTP_REFERER']);
    }*/

    protected function sectionTitle(){
        if(in_array(cf::$rq["url"], array("login","signup"))){ return; }
        /*form::openTag("div",array("id"=>"section-header"));
            form::tag("Admin ".cf::$pg["title"],"h3");
        form::closeTag("div");*/
    }
    
    protected function inHead($C = self::CONTROLLER) {
        msg::prepare();
        //cssmodal::prepare(cf::$page["ext"]);
        cf::appendToHeadTag(ADD. "admin/" . $C::$pg["view"]["url"] . ".css", "css");
        ckeditor::prepare();
        table::prepare();
    }
    
    protected function setPgRq($C = self::CONTROLLER, $pg = array()){
        $pg["view"]         =   array("url" => "home", "title" => "Dashboard");
        $pg["edit"]         =   array("url" => "home", "title" => "Edit dashboard");
        /*$pg["items"]        =  "Pages";
        $pg["item"]         =  "Page";
        $pg["tbl"]          =  "pages";
        $pg["key"]          =  "pID";*/
        
        $C::$pg = $pg;
        
        $C::$rq = "view";
        if(in_array(AppModel::$admin["waaRole"],array("Admin","Editor")) && filter_has_var(INPUT_GET, "cmd")){
            $C::$rq  = filter_has_var(INPUT_POST, "welcomeTitle") ? "save" : "edit";
        }
    }
}


$adminSection = new SuperAdminHome();

$adminSection->Header();
$adminSection->SuperAdminHomePage();
$adminSection->Footer();





