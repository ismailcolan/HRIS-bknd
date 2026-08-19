<?php
/** The (Topic) Article View Class */
class PageView extends AppView {
    
    final static function Content($rq){
        require_once(APP_HTML . "page-".$rq.".html");
    }
    
    /** Generates front pages nav <li> tags
     * @param type $rq <- controller::$rq */
    static function NavLisHtml($navArr, $active, $pendingOrderCnt){
		
        $liList = array();
        foreach($navArr as $url=>$innerHtml){
		
            if($innerHtml === "--empty--") {
                $liList[] = form::tagOut("<br/>", "li", ["style"=>"height:10px;"]);
            } else {
                $liCssClassArr = array();
                if($url===$active){
                    $liCssClassArr[] = "active";
                }
                $args = count($liCssClassArr)>0 ? array("class"=>implode(" ", $liCssClassArr)) : null;
                if($innerHtml === '--comingsoon--') {
                    $liList[] = form::tagOut('<a href="javascript:alert(\'Coming soon...\'); ">'.$url.'</a>', "li", $args);
                }elseif (substr( $innerHtml, 0, 5 )=='link:')  { 
				    $lnk=str_replace('link:','',$innerHtml);
					$liList[]=form::tagOut($lnk.$url."</a>", "li", $args);
				}
				else {
                    $liList[] = form::tagOut("<a href=\"".WP.($url!=="home"?$url:null)."\">$innerHtml</a>", "li", $args);
                }
            }
        }
        return implode("\n", $liList);
    }
    static function BreadcrumbsLisHtml($breadcrumbs){
        $bList = array();
        foreach($breadcrumbs as $url=>$title){
            $bList[] =  empty($url) ? 
                        form::tagOut($title, "li") :
                        form::tagOut("<a href=\"".$url."\">$title</a>", "li");
        }
        return implode("", $bList);
    }
    
    /** Generates <li> items from array (to be placed inside <ul>|<ol> tags)
     * @param array $navArray
     * @param boolean $returnCode (true) Return html code OR to execute inline
     * @return string */
    static function NavigationArrayToLiList($navArray, $currentUrl=null, $returnCode=true, $pathPre = WP){
        if((bool)$returnCode){ ob_start();}
        foreach($navArray as $url=>$name){
            $liArgs = ($url===$currentUrl) ? array("class"=>"active"): null;
            form::html("\n");
            form::openLi($liArgs);
                form::tag($name, "a", array("href"=>$pathPre . ($url!=="home"?$url:null)));
            form::closeLi();
        }

        if((bool)$returnCode){ return ob_get_clean(); }
    }
    
    static function ContactForm($list, $fldName2Lbl){
        form::openTag("section");
            form::openDiv(array("class"=>"container"));
        $data = null;
        form::codeStyle(true, 1, false);
        $fldArgsBase = array("fld-class"=>"form-group", "lbl-class"=>"col-sm-3 control-label", "wrap-class"=>"col-sm-7 col-md-5", "class"=>"form-control");
            form::tag("You can also email me below:", "h2", array("id"=>"form-title"));
            form::$ofsetIndentsCount = 2;
            form::openTag("form",array("method"=>"post","validate"=>true, "class"=>"form-horizontal form-contact", "action"=>"#form-title"));
                form::$ofsetIndentsCount = 3;
                form::inpField(
                    $fldName2Lbl["name"].":",
                    "name",
                    $data["name"],
                    (array("id"=>"lc-name")+$fldArgsBase)
                );
                form::inpSelect(
                    $fldName2Lbl["gender"].":",
                    "gender",
                    $list["gender"],
                    $data["gender"],
                    (array("id"=>"lcRootCID")+$fldArgsBase)
                );
                form::inpField(
                    $fldName2Lbl["email"].":",
                    "email",
                    $data["email"],
                    (array("id"=>"email", "placeholder"=>"Email")+$fldArgsBase)
                );
                form::inpSelect(
                    $fldName2Lbl["age-category"].":",
                    "age-category",
                    $list["age-category"],
                    $data["age-category"],
                    (array("id"=>"age-category")+$fldArgsBase)
                );
                form::inpSelect(
                    $fldName2Lbl["nature-enquiry"].":",
                    "nature-enquiry",
                    $list["nature-enquiry"],
                    $data["nature-enquiry"],
                    (array("id"=>"nature-enquiry")+$fldArgsBase)
                );
                form::inpField(
                    $fldName2Lbl["comments"].":",
                    "comments",
                    $data["comments"],
                    (array("id"=>"comments", "type"=>"textarea", "rows"=>"5", "wrap-class"=>"col-sm-9 col-md-7")+$fldArgsBase)
                );
                form::inpField(
                    $fldName2Lbl["captcha"].":",
                    "captcha",
                    $data["captcha"],
                    (array("id"=>"captcha")+$fldArgsBase)
                );
                form::openDiv(array("class"=>$fldArgsBase["fld-class"], "style"=>"margin-top:-10px;"));
                    form::tag("","label",array("class"=>$fldArgsBase["lbl-class"]));
                    form::openDiv(array("class"=>$fldArgsBase["wrap-class"]));
                        form::tag(null,"img", array("src"=>ADD."captcha.jpg", "alt"=>"","class"=>$fldArgsBase["class"]."-static"));
                    form::closeDiv();
                form::closeDiv();
                form::$ofsetIndentsCount = 2;
                form::inpSaveCancel(null,array("fld-class"=>"form-group", "wrap-class"=>"col-sm-offset-3 col-sm-7 col-md-5", "class"=>"btn btn-primary", "class-cancel"=>"btn btn-default"), "Send");
									
            form::closeTag("form");
            form::codeStyle(true, 1, true);
            form::tag("For counsellors in other locations contact Thinkright (www.thinkright.com.au)","p");
            form::closeDiv();
        form::closeTag("section");
    }
    
}
