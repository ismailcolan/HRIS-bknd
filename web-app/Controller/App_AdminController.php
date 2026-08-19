<?php
/** The APP_Admin controller class */
class App_AdminController extends AppController {
    
    public static $pg, $rq, $controller;
    
    
    private static function PageNavigation($ctr){ 
        $navCode = $subCode = null;
        if(cf::$rq["class"]!=="login"){ //On the login class urls we not show headers
		   
            list($navArr, $subArr) = App_AdminModel::PageNavigationArrays($ctr, self::$pg);
//            $navCode = WebpageAssetsAdminView::NavigationArrayToListNodes($navArr);

            $navCode = WebpageAssetsAdminView::AdminMainMenuHtmlCode($navArr);
			
            $subCode = WebpageAssetsAdminView::NavigationArrayToListNodes($subArr);
        }
		
        return array($navCode, $subCode);
    }
    
    private static function PageBreadcrumbs($pg){
        $breadcrumbsArray = App_AdminModel::BreadcrumbsArray($pg);
        return is_null($breadcrumbsArray) ? null : WebpageAssetsAdminView::NavigationArrayToListNodes($breadcrumbsArray);
    }
    
    public static function PageTitle($ctr,$add=''){

        $title = $links = null;
        if(method_exists(($model=str_replace("Controller","Model",$ctr)), "adminPageTitleWithLinks")){
			list($title, $links) = $model::adminPageTitleWithLinks();
				
        } else {
			  
            $title = $ctr::$pg[$ctr::$rq]["title"];
			
        }
	
        if(!is_null($title)){
            WebpageAssetsAdminView::pageTitle($title, $ctr, $links,$add);
        }
    }


    final static function Headers($ctr, &$templates=null, &$v=null){ 
		
        //Page Start (Head)
        $templates[] = "admin/webpage--start(header)";
        //self::LoadHtmlTemplate("webpage-start(header)", array("title"=>$frontPageTitle, "head-code"=>$frontHeadTagCode,"description"=>$frontPageDescription, "keywords"=>$frontPageKeywords));
        
        //Page Navigation
        $templates[] = "admin/webpage--navigation";
		
		
        list($v["page-nav"],$v["page-sub"]) = self::PageNavigation($ctr);
       
	    $model=str_replace("Controller","Model",$ctr);
		
        if(method_exists(($model=str_replace("Controller","Model",$ctr)), "BreadcrumbsAndTitle")){ 
			
			$model::BreadcrumbsAndTitle();
        } else { 
		    
            App_AdminModel::BreadcrumbsAndTitle($ctr);
        }
        //Page Breadcrumbs (Optional)
        /*if(!is_null($v["breadcrumbs"] = self::PageBreadcrumbs($ctr::$pg))){
            $templates[] = "admin/webpage--breadcrumbs";
            //self::LoadHtmlTemplate("webpage-breadcrumbs", $pageBreadcrumbsCode);
        }*/
       
        $templates[] = "admin/webpage--container-start";
    }
    
    final static function Footer(&$templates){
        $templates[] = "admin/webpage--container-stop";
        $templates[] = "admin/webpage--stop(footer)";
    }
}
