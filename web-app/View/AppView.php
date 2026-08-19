<?php
/** The App view */
class AppView {
    
    protected static    $fldArgsBase = 
	array("fld-class"=>"form-group", "lbl-class"=>"control-label col-sm-4 col-md-3", "wrap-class"=>"col-sm-8 col-md-7 col-lg-5", "class"=>"form-control"),
                        $saveCancelArgsBase = array("fld-class"=>"form-group", "wrap-class"=>"col-sm-8 col-md-7 col-lg-5 col-sm-offset-3 col-md-offset-2", "class"=>"btn btn-primary", "class-cancel"=>"btn btn-default");
    
    const ADMIN_LISTS_ROWS_LIMIT = ADMIN_TABLES_ROWS_PER_PAGE;
    
    protected static function AllLnksCode($pg, $which = array("view","edit","deactivate","delete","pass")){
        $lnks = array();
        
        if(($k = array_search("view", $which))!==false){
            $lnks[$k] = form::linkOut($pg["summary"]["url"]."?id=%d" ,"<span class=\"fa fa-folder-o\"></span>", array( "class"=>"tbl-ico", "title"=>"View ".strtolower($pg["item"])));
        }
        if(($k = array_search("edit", $which))!==false){
            $lnks[$k] = form::linkOut($pg["details"]["url"]."?id=%d" ,"<span class=\"fa fa-pencil-square-o\"></span>", array("class"=>"tbl-ico", "title"=>"Edit ".strtolower($pg["item"])));
        }
        if(($k = array_search("order", $which))!==false){
            $lnks[$k] = form::linkOut($pg["details"]["url"]."?id=%d&order=true" ,"<span class=\"fa fa-cart-plus\"></span>", array("target"=>"_blank", "class"=>"tbl-ico", "title"=>"Submit order (for ".strtolower($pg["item"]).")"));
        }
        if(($k = array_search("deactivate", $which))!==false){
            $lnks[$k]  = form::linkOut($pg["details"]["url"]."?id=%d&cmd=trash" ,"<span class=\"fa fa-ban\"></span>",   array("onclick"=>"return confirm('".str_replace("row", strtolower($pg["item"]), TXT_DEACTIVATE_CONFIRM)."');", "class"=>"tbl-ico", "title"=>"Deactivate ".(strtolower($pg["item"]) === "calendar" ? "event" : strtolower($pg["item"]))));
        }
        if(($k = array_search("reactivate", $which))!==false){
            $lnks[$k]  = form::linkOut($pg["details"]["url"]."?id=%d&cmd=restore" ,"<span class=\"fa fa-undo\"></span>",   array(/*"onclick"=>"return confirm('".str_replace("row", strtolower($pg["item"]), TXT_DEACTIVATE_CONFIRM)."');", */"class"=>"tbl-ico", "title"=>"Reactivate ".(strtolower($pg["item"]) === "calendar" ? "event" : strtolower($pg["item"]))));
        }
        if(($k = array_search("delete", $which))!==false){
            if(get_called_class()==="OrderView"){ //Oredrs delete action has "Enter comments" screen and confirmation is skipped
                $lnks[$k]  = form::linkOut($pg["details"]["url"]."?id=%d&cmd=trash&action-src=list" ,"<span class=\"fa fa-trash-o\"></span>", array(/*"onclick"=>"return confirm('".str_replace("row", strtolower($pg["item"]), TXT_DELETE_CONFIRM)."');",*/ "class"=>"tbl-ico", "title"=>"Delete ".strtolower($pg["item"])));
            } else {
                $lnks[$k]  = form::linkOut($pg["details"]["url"]."?id=%d&cmd=trash" ,"<span class=\"fa fa-trash-o\"></span>", array("onclick"=>"return confirm('".str_replace("row", strtolower($pg["item"]), TXT_DELETE_CONFIRM)."');", "class"=>"tbl-ico", "title"=>"Delete ".strtolower($pg["item"])));
            }
        }
        if(($k = array_search("pass", $which))!==false){
            $lnks[$k] = form::linkOut($pg["pass"]["url"]."?id=%d" ,"<span class=\"fa fa-key\"></span>", array( "class"=>"tbl-ico", "title"=>"Change ".strtolower($pg["item"])." password"));
        }

        return (count($lnks)===count($which) ? $lnks : cf::end("You have requested a link type not covered by ".__METHOD__));
    }
    
    protected static function DataPanel($panelData, $title=null, $class=null, $imgUrl = null,$lbl_class='col-sm-3 lbl'){
		
        if(cf::$rq["ext"]==="json") {return;}
		
        form::openDiv(array("class"=>"summary panel ".(isset($class)? $class." ": null)."panel-default"));
		
            if(isset($title)){
                form::div($title,array("class"=>"panel-heading"));
            }
			
            form::openDiv(array("class"=>"panel-body"));
                isset($imgUrl) ? self::PanelImg($imgUrl) : null;
                foreach($panelData as $lbl=>$val){
                    form::openDiv(array("class"=>"row no-gutter"));
                        form::div($lbl.":",array("class"=>$lbl_class));
                        $p = form::tagOut((empty($val) ? "&nbsp;" : $val), "p", array("class"=>"form-control-static"));
                        form::div($p, array("class"=>"col-sm-9"));
                    form::closeDiv();
                }
            form::closeDiv();
        form::closeDiv();
    }
    private static function PanelImg($imgUrl){
        //form::openDiv(array("class"=>"col-sm-4 pull-right text-right"));
            form::openDiv(array("class"=>"thumbnail"));
                form::openA(array("href"=>$imgUrl, "target"=>"_blank", "class"=>"img-container"));
                    form::tag(null, "img", array("src"=>$imgUrl));
                form::closeA();
            form::closeDiv();
        //form::closeDiv();
    }
    
    protected static function DetailsCancelUrl($data, $pg){
        if(array_key_exists("key", $pg)){
            return (is_array($data) && array_key_exists($pg["key"], $data) ? $pg["summary"]["url"]."?id=".(int)$data[$pg["key"]] : $pg["list"]["url"]);
        } else {
            cf::end(__METHOD__." error: The \$pg array has to have key Model::\$dt[key] definition.");
        }
    }

}