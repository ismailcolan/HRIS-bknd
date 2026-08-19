<?php
/** CssModal */
class cssmodal {
    
    public static $modalHeader=null, $modalFooter="close";
    public static $modalSettings = array();
    
    /** Opens modal html tags
     * @param type $mId - modal ID */
    public static function openModal($mId){
        $modalArgs = self::modalGetArgs($mId);
        form::html("<!-- Modal \"$mId\" Start -->");
        form::openTag("section",$modalArgs);
            form::openDiv(array("class"=>"modal-inner"));
                self::addHeader();
                form::openDiv(array("style"=>"margin:1.2em"));
    }
    
    /** Closes modal html tags
     * @param type $mId - modal ID */
    public static function closeModal($mId){
                form::closeDiv();
                self::addFooter();
            form::closeDiv();//array("class"=>"modal-inner")
            self::addTopCloseLnk();
        form::closeTag("section");
        form::html("<!-- Modal \"$mId\" End -->");
    }
    
    /** Create modal with content (html code)
     * @param type $mId - modal id (#$imId to be called)
     * @param type $content */
    public static function createModal($mId, $content = "<p>That's a test modal</p>"){
        self::openModal($mId);
            form::html($content);
        self::closeModal($mId);
    }
    
    
    private static function addHeader(){
        if(!is_null(self::$modalHeader)){
            form::openTag("header");
                form::tag(self::$modalHeader, "h2",array("id"=>"modal-label"));
            form::closeTag("header");
        }
    }
    
    private static function addFooter(){
        if(!is_null(self::$modalFooter)){
            form::openTag("footer",array("style"=>"padding-bottom:1px;"));
                if(self::$modalFooter === "close" || self::$modalFooter === "cancel"){
                    //form::tag("<a href=\"#!\" class=\"close-action\" title=\"Close this modal\" data-dismiss=\"modal\" class=\"button\">Close</a>", "p", array("style"=>"text-align:center"));
                    form::tag("<button onclick=\"location.assign('#!')\" type=\"button\" data-dismiss=\"modal\" style=\"min-width:60%\">".ucfirst(self::$modalFooter)."</button>", "p", array("style"=>"text-align:center"));
                } else {
                    form::tag(self::$modalFooter, "p");
                }
            form::closeTag("footer");
        }
    }
    
    private static function addTopCloseLnk(){
        $cArgs = array();
        $cArgs["href"] = "#!";
        $cArgs["class"] = "modal-close";
        //$cArgs["title"] = "Close this modal";
        $cArgs["data-close"] ="Close";
        $cArgs["data-dismiss"] ="modal";
        form::openTag("a",$cArgs);
            form::html("&times;");
        form::closeTag("a");
    }
    
    private static function modalGetArgs($mId){
        self::prepare(true);
        if(is_null($mId)){ die("ERROR (in a ".__CLASS__."::openModal call): Please supply the modal id."); }
        $modalArgs = array();
        $modalArgs["id"]=$mId;
        $modalArgs["class"]="modal--show";
        $modalArgs["tabindex"]="-1";
        $modalArgs["role"]="dialog";
        $modalArgs["aria-labelledby"]="modal-label";
        $modalArgs["aria-hidden"]="true";
        if(!empty(self::$modalSettings)){
            foreach(self::$modalSettings as $k=>$v){
                $attrName = $k!=="class" ? "data-cssmodal-".$k : $k;
                $modalArgs[$attrName]=$v;        
            }
            self::$modalSettings = array();
        }
        return $modalArgs;
    }
    
    public static function prepare($setOrCheck = false) {
        if( helper::prepare($setOrCheck, (__METHOD__)) !==true) {
            return;
        }
        cf::appendToHeadTag(ADD."helpers/cssmodal/add/modal.css", "css");
        cf::appendToHeadTag(ADD."helpers/cssmodal/add/modal.js");
        cf::appendToHeadTag(ADD."helpers/cssmodal/add/plugins/modal-html5video.js");
        cf::appendToHeadTag(ADD."helpers/cssmodal/add/plugins/modal-maxwidth.js");
        cf::appendToHeadTag(ADD."helpers/cssmodal/add/plugins/modal-resize.js");
        cf::appendToHeadTag(ADD."helpers/cssmodal/add/plugins/modal-gallery.js");
    }
}
