<?php 
/** Page Front - Base Web-Application Page class */
class PageFront extends Page {
    
    //protected static $frontPageId = null, $frontPages = null;
    protected static $id=null, $data = null, $timeStart = null, $pageToControllerMethods = array();
    private static $accessFreeUrls = array("logout", "login", "login-recover",
	"cron-weekly", "cron-daily");
    const CONTROLLER = "PageController";
    
	 public function SetFile(){
		 $sql = 'UPDATE fcfilesbyuser SET fcfbuVisible="'.$_GET['state'].'" WHERE 	fcfbuID='.$_GET['id'];
		 $r= db::SQL($sql);
	    $res=array();
		
		 if ($r) $res['result']='OK';
			else {
				$res['result']='FAIL';
				$res['state']=$_GET['state'];
				$res['id']=$_GET['id'];
			}
		 
		 echo json_encode($res);
		 
		 
	  }
	
    public function __construct($C = self::CONTROLLER) { 
		$url = $_SERVER['REQUEST_URI'];
		
		//cf::pr($_SESSION);echo $url;
		if((!isset($_SESSION['webapp-admin-token']) && !isset($_SESSION['webapp-login-token'])) && 
		!($url == '/' || $url == '/jobs' || $url == '/profile' 
		|| strpos($url,'/application-form')!==false ||   strpos($url,'/job-details')!==false
		|| strpos($url,'/login-recover')!==false
		|| strpos($url,'timesclock_admin_p.php')!==false
		|| strpos($url,'/cron-onboarding')!==false || strpos($url,'/cron-benefits')!==false || strpos($url,'/cron-offboarding')!==false || strpos($url,'/cron-credentialing')!==false
		)){
		  cf::Redirect('/');
		}

        parent::__construct();

        /*
                if(isset(AppModel::$usr)!==true) {
                    if(!empty(cf::$rq["url"])){
                        if(!in_array(cf::$rq["url"], self::$accessFreeUrls)){
                            //cf::vdd(cf::$rq["url"], self::$accessFreeUrls, !in_array(cf::$rq["url"], self::$accessFreeUrls), !empty(cf::$rq["url"]));
                            cf::Redirect( WP . ( filter_has_var(INPUT_GET, "go") ? filter_input(INPUT_GET,"go",FILTER_SANITIZE_URL) : "" ) );
                        }
                    }
                }
        */
		
        if(!isset(cf::$rq["ext"]) || empty(cf::$rq["ext"])){
            if(method_exists($C, "TitleAndMetaTags")) {
                $C::TitleAndMetaTags();
            }
        }
		
    }

    public function webGetSectionClass($fName="",$addon = ""){
        $fPath = str_replace(basename($fName), '', $fName);
        $fNameFile = preg_replace('/\.php$/', '', basename($fName));
        $fnameArr = explode("-", $fNameFile);
        $fNameFile = strpos($fNameFile,"-") ? array_shift( $fnameArr ) : $fNameFile;
        return $fPath . $fNameFile . $addon . ".php";
        //return $fName . $addon . ".class.php";
    }
    
    public function HTMLHeader() {
        if(isset(cf::$rq["ext"]) && cf::$rq["ext"]==="json"){return;}
        $v = array();
        $v["title"] = !empty(cf::$pg["title"]) ? cf::$pg["title"] : null;
        $v["description"] = !empty(cf::$pg["description"]) ? cf::$pg["description"] : null;
        $v["keywords"] = !empty(cf::$pg["keywords"]) ? cf::$pg["keywords"] : null;
        $v["other"] = !empty(cf::$pageHeadTagContent) ? implode("" ,cf::$pageHeadTagContent). "\n" : "";

        if(!isset(cf::$pg["ext"]) || !in_array(cf::$pg["ext"], array("json"))){
            require_once(APP_HTML . "start(header).html");
            
            PageController::PagesStart(static::CONTROLLER);
        }
    }
    
    public function HTMLFooter() {
        if(isset(cf::$pg["ext"]) && in_array(cf::$pg["ext"],array("json"))){ return; }

        if(isset(static::$timeStart) && !is_null(static::$timeStart)){
            $execTime = "Exec time: ". (number_format((microtime(true) - static::$timeStart),6) . " s");
        }
        /*if(DEBUG === true){//cf::vd();}*/
        $v = array();
        PageController::PagesStop($v);
        require_once(APP_HTML . "stop(footer).html");
    }
    
    public function HTMLHeaderPopup() {
        $frontPageTitle = $frontHeadTagCode = $mainNavHoverDirCode = $mainNavHoverTpicCode = $frontMainNavMobileLinksArr= false;
        
        $this->prepareHeadTagVars($frontPageTitle, $frontHeadTagCode);
        $frontPageDescription = !is_null(cf::$pg["description"]) ? : null;
        $frontPageKeywords = !is_null(cf::$pg["keywords"]) ? : null;
        
        //@list($mainNavHoverDirCode, $mainNavHoverTpicCode) = $this->mainNavHoverPanelsCode();
        //$frontMainNavMobileLinksArr = self::frontMainNavMobileLinks();
        if(!isset(cf::$pg["ext"]) || !in_array(cf::$pg["ext"],array("json"))){ 
            require_once(APP_HTML . "start-popup(header).html");
        }
    }
    
    public function HTMLFooterPopup() {
        if(!isset(cf::$pg["ext"]) || in_array(cf::$pg["ext"],array("json"))){ return; }
        if(DEBUG === true){
            //cf::vd();
        }
        require_once(APP_HTML . "stop-popup(footer).html");
    }
    
    public function breadcrumbs($breadcrumbs, $getHtml = false) {
        if($getHtml===true){  cf::buffer(); }
        ?>
        <ol class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
            <?php
            $ctr=1;
            foreach($breadcrumbs as $lnk=>$name){
                $lnkTag = empty($lnk) ? "span" : "a";
                ?><li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                    <<?=$lnkTag?> itemprop="item"<?=$lnkTag=="a"? " href=\"".$lnk."\"":""?>>
                        <span itemprop="name"><?=$name?></span>
                    </<?=$lnkTag?>>
                    <meta itemprop="position" content="<?=$ctr++?>"/>
                </li> <?php
            }
            ?>
        </ol>
        <?php
        if($getHtml===true){ cf::buffer($getHtml); return $getHtml; } 
    }
}
