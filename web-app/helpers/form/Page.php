<?php
/** The Page class */
class Page {

    /** @var array - holds the current page data URLs & titles by request, page dedicated db_table and its key column */
    protected static $pg = null;
    /** @var string - holds the current page request list|details etc */
    public static $rq = null;
    /** @var array - holds the current user details */
    //public static  $usr = null;
    /** @var integer - id related to the current page */
    protected static $id = null;
    /** @var array - data container related to the current page */
    protected static $data = null;
    /** @var string - The main controller class related to the current page */
    protected static $controller = null;
    /** @var string - holds related for the current URL class */
    public static $error404;
    /** @var boolean - Indicates do the URL belongs to the admin control panel */
    public static $adminSection = false;
    /** @var string - Default language */
    public static $lang = "en";

    public function __construct() {
        //isset check is needed for the image upload
        if(isset(cf::$rq) && array_key_exists("admin-section", cf::$rq) && cf::$rq["admin-section"]===true){
            AppModel::$admin = is_null(AppModel::$admin) ? WebappAdminModel::checkLoginTokens() : AppModel::$admin;
        } else {
            AppModel::$usr = ProfileModel::DataIfLoggedIn();
        }
        cf::$pg = &self::$pg;
        $this->setPgRq();
        $this->inHead();
        require_once(str_replace("[:lang:]", self::$lang, APP_LANG_FILENAME_TEMPLATE));
    }

    private final function setHeadVars(&$v){
        //lang::prepare();
        form::codeStyle(true, 1);
            
            $this->inHead(); //@see $this->inHead() below;
            
            /* head title tag */
            $v["title"] = isset(cf::$pg["title"])? cf::$pg["title"] : null;
            $v["keywords"] = isset(cf::$pg["keywords"])? cf::$pg["keywords"] : null;
            $v["description"] = isset(cf::$pg["description"])? cf::$pg["description"] : null;
            /* head meta tags */
            /*//$metaTagsList = !isset($v["meta"]) ? array(array("http-equiv"=>"Content-Type", "content"=>"text/html; charset=utf-8")) : $v["meta"];
            $metaTagsList = array();
            $v["meta"] = "";
            if(isset(self::$pg["keywords"])){$metaTagsList[] = array("name"=>"keywords", "content"=>self::$pg["keywords"]);}
            if(isset(self::$pg["description"])){$metaTagsList[] = array("name"=>"description", "content"=>self::$pg["description"]);}
            foreach($metaTagsList as $metaArgs){
                $v["meta"].= form::tagOut(null,"meta",$metaArgs);
            }*/
            
            /* head other tags - generated from the $this->inHead() page dedicated class method */
            $v["other"] = !empty(cf::$pageHeadTagContent) ? implode("" ,cf::$pageHeadTagContent). "\n" : "";
        form::codeStyle(false, 0);
    }
    
    /**Loads header html template, seting values for them
     * @param str|array $templates - name/s. If submitted $template="sample" will load "sample-header"
     * @param null|array $v - templates values list 
     * @use $this->setHeaderVars($v) to expand $v with $v["title"], $v["meta"] and $v["other"] (self::inHead() added tags) 
     * @see self::htmlTemplate("sample-header"); */
    public function Header($templates="simple", $v=null) {
        $this->setHeadVars($v);
        $htmlTemplates = (is_string($templates) && $templates==="simple" ? "simple-header" : $templates);
        self::htmlTemplate($htmlTemplates, $v);
    }

    /**Loads footer html template
     * @param str|array $templates - name/s. If submitted $template="sample" will load "sample-footer"
     * @param array $v - templates values list 
     * @see self::htmlTemplate();*/
    public function Footer($templates="simple", $v=null) {
        $htmlTemplates = (is_string($templates) && $templates==="simple" ? "simple-footer" : $templates);
        self::htmlTemplate($htmlTemplates, $v);
    }

    /**Loads 404 page */
    public function web404($fallBackUrl = null) {
        self::checkForFallback($fallBackUrl);
        self::$error404 = true;
        cf::$pg["title"] = "Error 404";
        cf::$pg["description"] = null;
        cf::$pg["keywords"] = null;
        static::HTMLHeader();
        $v["nav-li-footer"] = PageView::NavigationArrayToLiList(array_merge(PageModel::$navL, PageModel::$navR, PageModel::$navFooter2Add));
        //static::panel("gray-line-directory-search");
        require_once(APP_HTML . "web-404.html");
        /*form::openTag("div", array("id" => "err-404"));
        form::tag("<em>404</em>Ooops! That's an error.", "h1");
        form::tag("Page not found.", "h2");
        form::p("The " . SITE_NAME . " webserver was unable to find the requested resourse<br/><strong>" . cf::$rq["url-full"] . "</strong>");
        form::p("It's looking like the url you are using have some errors... it happens.<br />We offer you to use the site navigation top of this message for proper navigation.");
        form::closeTag("div"); //array("id"=>"err-404")
         */
        static::HTMLFooter();

        die();
    }

    /**Loads ../webapp-html/".$htmlTemplate.".html,
     * Omit loading if cf::$rq["ext"] === "json"
     * @param str|array $htmlTemplates - an template, list, or "sample-(header|footer)"
     * @param null|array $v - templates values list
     * @param string $extension - (".html") */
    static final function htmlTemplate($htmlTemplates, $v=null, $extension=".html"){
        if(!isset(cf::$rq["ext"]) || !in_array(cf::$rq["ext"],array("json"))){
            if(is_array($htmlTemplates)){
                foreach($htmlTemplates as $teplate){
                    require_once(APP_HTML . $teplate . $extension);    
                }
            } else if($htmlTemplates === "simple-header"){
                form::html("<!DOCTYPE html>");
                form::openTag("html");
                form::openTag("head");
                form::codeStyle(true, 1);
                    if(!empty($v["title"])){    form::tag($v["title"],"title");}
                    if(!empty($v["meta"])){     form::html($v["meta"]);}
                    if(!empty($v["other"])){    form::html($v["other"]);}
                form::codeStyle(false, 0);
                form::closeTag("head");
                form::openTag("body");
            } else if($htmlTemplates === "simple-footer"){
                form::closeTag("body");
                form::closeTag("html");
            } else if(is_string($htmlTemplates)){
                require_once(APP_HTML . $htmlTemplates . $extension);
            }
        }
    }
    
    /** sets self::$pg array if defined and the current request (self::$rq) */
    protected function setPgRq(){}

    /**Loads tags in the page's header. To be overrided in the request dedicated class file.
     * @use cf::appendToHeadTag(ADD."incl/home.css","css"); 
     * @use masg::prepare() - to prepare any needed on the page helper calls */
    protected function inHead(){}





    protected function prepareHeadTagVars(&$headTitleTag = '', &$headCustomAddedTags = ''){
        $this->inHead(); //adds page prepared for the title tags in cf::$pageHeadTagContent

        $headTitleTag = (self::$adminSection === true ? " Admin: " : "");
        $headTitleTag.= !empty(cf::$pg["title"]) ? (cf::$pg["title"]) :"";
        
        $headCustomAddedTags = !empty(cf::$pageHeadTagContent) ? "\n".form::codeOfset(1) . implode("\n".form::codeOfset(1) ,cf::$pageHeadTagContent). "\n" : "";
    }
    /**Loads html header */
    public function HTMLHeader() {
        $MainNavLinkDirectoryHoverPanelCode = $MainNavLinkTopicHoverPanelCode = null;
        $frontHeadTitleTag = $frontHeadTagCode = $frontMainNavCode = $frontSubNavCode = false;
        $this->prepareHeadTagVars($frontPageTitle, $frontHeadTagCode);
        $frontPageDescription = !is_null(cf::$pg["description"]) ? cf::$pg["description"] : null;
        $frontPageKeywords = !is_null(cf::$pg["keywords"]) ? cf::$pg["keywords"] : null;
        $mainNavHoverDirCode = $mainNavHoverTpicCode = null;
        $v=null;
        require_once(APP_HTML . "start(header).html");
    }
    /**Loads html footer */
    public function HTMLFooter() {
        require_once(APP_HTML . "stop(footer).html");
    }

    



    static function panel($panelName, $v=null){
        require_once (APP_HTML ."app-panel-". $panelName . ".html");
    }

    private static function checkForFallback($fallBackUrl){
        if(!is_null($fallBackUrl) && class_exists("FallbackModel")){
            $fallbackData = FallbackModel::dataByUrl($fallBackUrl);
            if(!is_null($fallbackData)){
                if(is_null($fallbackData["ufRedirect"])){
                    http_response_code($fallbackData["ufHttpCode"]);
                } else {
                    header("HTTP/1.1 301 Moved Permanently"); 
                    //The next one must be used but the local server not support it (PHP<5.4)
                    //http_response_code($fallbackData["ufHttpCode"]);
                    header("Location: ".WP.$fallbackData["ufRedirect"]);
                }
                exit;
            } else {
                header("HTTP/1.0 404 Not Found");
            }
        }
    }
	
    final static function gzipStartStop($action="stop"){
        if(static::USE_GZIP===true){
            if($action=="start"){
                ob_start("ob_gzhandler");
            } else if($action=="stop"){
               ob_end_flush();
            }
        }
    }
    
    /** Links a controler properties values, conecting them to the current class properties values
     * @param string $ctr - The controller name
     * @param array $propList - The list of properties to be linked
     * @example  if ctr = "pge" and "id" is added in the $propList it will execute pge::$id = &self::$id 
     * and wherever self::$id changes the pge::$id will be reflected */
    protected static function setControlerProperties($ctr, $propList = array()){
        if($ctr=="page"){
            loadClassFiles($ctr, APP_CONTROLLER);
        } 
        foreach($propList as $v){
            $ctr::$$v = &static::$$v;
        }
    }
    
    /** Custom call for a pre-set list of functions
     * Every function from the list will be translated as a page controller method call:
     * $this->pageDetails       => $controller::Details();
     * $this->pageDetailsSave   => $controller::DetailsSave();
     * $this->pageDelete        => $controller::Delete();
     * Dies if the page::CONTROLLER is not set or the call lead to a function outside the expected controller functions
     * @param type $method
     * @param type $args */
    public function __call($method, $args=array()){
        static::CONTROLLER!=="AppController" ? static::CONTROLLER : (cf::end(__METHOD__." Invalid page custom Call. The ".__METHOD__." has no CONTROLLER constant definition."));

        $func = in_array($method, static::$pageToControllerMethods) ? $method : (cf::end(__METHOD__." error - calls an undefined in <strong>self::\$pageToControllerMethods</strong> func: <i>".static::CONTROLLER."::</i><strong>".$method."</strong>()"));
        
        forward_static_call_array(array(static::CONTROLLER, $func), $args);
    }
}