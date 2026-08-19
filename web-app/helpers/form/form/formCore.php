<?php
/** The form helper core (html, tags & custom call methods) */
class formCore {
    
    static $jsval = array("validations"=>array() ,"msgs"=>array(), "form-counter"=>1,"val-script-included"=>false, "val-client"=>false, "val-server"=>false);
    private static $autoCloseTagList = array("br", "hr", "meta", "img", "input", "link");
    private static $allowedClassCustomCallsTags = array("a","p", "h1", "div","ul","ol","li", "label", "span", "br", "hr", "code", "button", "input", "clear");
    /* Code style propertiess */
    static $ofsetIndentsCount = false, $ofsetIndentsCountInpSKIP = false;
    static $codeLineBreakBefore = 0, $codeLineBreakBeforeInpSKIP = false;
    static $codeLineBreakAfter = false, $codeLineBreakAfterInpSKIP = false;
    
    static $output = false;
    const GET_OUTPUT_FUNC_SUFFIX = "Out";
    
    /** Description: Validate and prepare inputArgs (tags Attributes)
     *  @param arr   $args
     *  Return str   (space separated attribute="value" list) */
    private static function processArgs(&$args) {
        if (empty($args) || !is_array($args)) {
            return;
        }
        $arrayEvents = array("onblur", "onchange", "onclick", "ondblclick", "onfocus", "oninput", "onkeydown", "onkeypress", "onkeyup", "onmousedown", "onmousemove", "onmouseout", "onmouseover", "onmouseup", "onload", "onselect");
        $arrayAttributes = array("data", "action", "align", "alt", "alt", "aria-hidden", "autofocus", "checked", "content", "class", "colspan", "defer", "disabled", "enctype", "for", "frameborder", "height", "href", "http-equiv", "id", "itemscope", "itemtype", "itemprop", "maxlength", "method", "min", "max", "multiple", "name", "novalidate", "onsubmit", "pattern", "placeholder", "readonly", "regexp", "required", "rel", "role", "selected", "scrolling", "size", "src", "style", "tabindex", "target", "type", "title", "valign", "value", "width", "rows");
        $arr = array_merge($arrayEvents, $arrayAttributes);
        $res = array();
        foreach ($args as $key => $value) {
			if ($key == 'src') {
				$path_parts = pathinfo($value);
				if (isset($path_parts['extension']) && strtolower($path_parts['extension']) == 'js') $value.='?_v='.VER;
			}

			if ($key == 'href') {
				$path_parts = pathinfo($value);
				if (isset($path_parts['extension']) && strtolower($path_parts['extension']) == 'css') $value.='?_v='.VER;
			}
			
            if (in_array(strtolower($key), $arr) || substr($key, 0, 5) == "data-") {
                $res[] = $value===true ? $key : $key."=\"".$value."\"";
                unset($args[$key]);
            }
        }
		
		// echo implode(" ", $res).'<br>';print_r($args);echo '<br>';
        return implode(" ", $res);
    }
    
    /** Description: Adds code style in tag oppening */
    private static function processBefore() {
        if (self::$codeLineBreakBefore && self::$codeLineBreakBeforeInpSKIP != true) {
            self::html("\n");
        }
        if (self::$ofsetIndentsCount > 0 && self::$ofsetIndentsCountInpSKIP != true) {
            self::html(self::codeOfset(self::$ofsetIndentsCount));
        }
    }

    /** Description: Adds code style in tag closure */
    static function processAfter() {
        if (self::$codeLineBreakAfter && self::$codeLineBreakAfterInpSKIP != true) {
            self::html("\n");
        }
    }

    /** Description: Custom calls for the allowed list of tags
     * Examples:
     *    form::divOut($innerHtml,$args); -> works as get Out(put) from form::div($innerHtml,$args); - ommit ob_.. usage
     *    form::div($innerHtml,$args); -> Writes <div {$args}>$innerHtml</div>
     *    form::openDiv($args); -> Writes <div {$args}>
     *    form::closeDiv($repeat); -> Writes </div>  tag repaeated {$repeat - Default:1} times
     * @param str $name
     * @param arr $arguments */
    static function __callStatic($name, $arguments){ 
	
        if(substr($name,-3)==self::GET_OUTPUT_FUNC_SUFFIX){
			//echo '>>>'.substr($name,0,-3);
            self::$output = "";
            forward_static_call_array(array("static",substr($name,0,-3)), $arguments);
            $out = self::$output;
            static::$output = false;
            return $out;
        }
        
        $action = false;
        foreach (array("open","close") as $a){
            if(strpos($name, $a) === 0){
                $name = strtolower(str_replace($a,"",$name));
                $action = $a;
                break;
            }
        }
        if(!in_array($name, self::$allowedClassCustomCallsTags)){
            die("Invalid form class custom call. The tag {$name} in not within the allowed tags.");
        }
        
        $tagsWithoutArgs = array("br","hr");
        if(in_array($name, $tagsWithoutArgs)){
            //Extracting the tag repeater
            $repeat = (isset($arguments[0]) && abs($arguments[0]) > 1) ? abs($arguments[0]) : false;
            $arguments = array("");
        } else if ($name=="clear"){
            $arguments = array(" ",array("class"=>"clearfix"));
            $name="div";
        } else if ($action==="close"){
            //Extracting the Close repeater
            $arguments = (isset($arguments[0]) && abs($arguments[0])) > 0 ? array($arguments[0]) : array();
        }
        
        if ($action !== false){
            //Prepend the tag name to the call $args
            array_unshift($arguments, $name);
            forward_static_call_array(array(__CLASS__,$action."Tag"), $arguments);
        } else {
            //Adds the tag name aftr the fisrt arg ($innerHTML)
            array_splice($arguments, 1, 0, $name);
            if(!isset($repeat)){
                forward_static_call_array(array(__CLASS__,"tag"), $arguments);
            } else {
                for ($i =0; $i < $repeat; $i++){
                    forward_static_call_array(array(__CLASS__,"tag"), $arguments);
                }
            }
        }
    }
    
    /*static function loopCall($method, $args){
        if(count($args)>0){
            foreach($args as $arg){
                call_user_func_array(array("form", $method), $arg);
            }
        }
    }*/
    
    /** Description: Writes <a href="$hrefAttribute" {$args}>$innerHtml</a>
     *  @param str   $hrefAttribute
     *  @param str   $innerHtml
     *  @param arr   $args - tag attributes */
    static function link($hrefAttribute = "", $innerHtml = "", $args = array()) {
		
        $hrefAttribute = empty($hrefAttribute) ? "javascript:void(0);" : $hrefAttribute;
        if (self::$ofsetIndentsCount > 0) {
            $innerHtml = str_replace("\n", self::codeOfset(self::$ofsetIndentsCount), $innerHtml);
        }
        self::openTag("a", array_merge(array("href" => $hrefAttribute), $args));
        self::codeStyleSkip(true);
        self::html($innerHtml);
        self::closeTag("a");
        self::codeStyleSkip(false);
    }

    /** Description: Writes <tag {$args}>
     *  @param str   $innerHtml
     *  @param str $tag
     *  @param arr $args - tag attributes */
    static function tag($innerHtml = "", $tag = "", $args = array()) {
	;
        if (self::$ofsetIndentsCount > 0) {
            $innerHtml = str_replace("\n", "\n".self::codeOfset(self::$ofsetIndentsCount), $innerHtml);
        }
        self::openTag($tag, $args);
        if (!in_array($tag, self::$autoCloseTagList)) {
            self::html($innerHtml);
            self::closeTag($tag);
        }
    }

    /** Description: Writes <tag {$args}>
     *  @param str $tag
     *  @param arr $args - tag attributes */
    static function openTag($tag = "", $args = array()) {
		
        if ($tag == "a") {
            $hrefAttribute = empty($hrefAttribute) ? "javascript:void(0);" : $hrefAttribute;
        } elseif ($tag == "form") {
            //self::$error = isset($args["onsubmit"]) ? array() : false;
            if(!isset($args["method"])){
                $args["method"] = "post";
            }
            if (isset($args["validate"]) && $args["validate"]===true){
                if(self::$jsval["val-client"]===true){
                    $args["onsubmit"] = "return validateCompleteForm(this, 'error');";
                }
                $args["data-no"] = self::$jsval["form-counter"];
            }
        }
        $tagAttributes = self::processArgs($args);
		
        self::processBefore();
        $html = "<" . $tag . (!empty($tagAttributes) ? " " : "") . $tagAttributes . ((in_array($tag, self::$autoCloseTagList)) ? " /" : "") . ">";
        self::html($html);
    }

    /** Description: Writes </tag> - repeated $closeRepeater times
     *  @param str $tag
     *  @param int $closeRepeater */
    static function closeTag($tag = "", $closeRepeater = 1) {
        $closeRepeater = abs($closeRepeater) > 0 ? $closeRepeater : 1;
        if($tag != "script"){
            self::processBefore();
        }
        $html = str_repeat("</" . $tag . ">", $closeRepeater);
        self::html($html);
        self::processAfter();
        if ($tag=="form"){
            self::afterFormTagJS();
        }
    }

    /** Description: Outputs the $code (echo)
     *  @param str $code */
    static function html($code) {
        if(static::$output!==false) {
            self::$output.=$code;   
        } else {
            echo($code);
        }
    }


    /** Description: skipping code ofset 
     *  @param bool $action */
    static function codeStyleSkip($action = false) {
        if ($action === true || $action === false) {
            self::$codeLineBreakAfterInpSKIP = $action;
            self::$ofsetIndentsCountInpSKIP = $action;
            self::$codeLineBreakBeforeInpSKIP = $action;
        }
    }
    
    /** Description: Set or reset code style. Store settings in dedicated properties of the Form class
     *  @param bol $breakBeforeHtml
     *  @param str $indentOfsetCount
     *  @param bol $breakAfterHtml */
    static function codeStyle($breakBeforeHtml = false, $indentOfsetCount = 0, $breakAfterHtml = false) {
        self::$codeLineBreakBefore = $breakBeforeHtml;
        self::$ofsetIndentsCount = $indentOfsetCount;
        self::$codeLineBreakAfter = $breakAfterHtml;
    }

    /** Description: creates code ofset 
     *  @param int $count
     *  Return (str with needed count of spaces ofset)  */
    static function codeOfset($count = 0) {
        return str_repeat(defined("HTML_INDENT") ? constant("HTML_INDENT") : "    ", $count);
    }

    private static function afterFormTagJS(){
        if (empty(self::$jsval["msgs"])) {
            return;
        }
        //self::codeStyle(false,0,true);
        $fields = array();
        foreach(self::$jsval["msgs"] as $k => $v){
            $m = array();
            foreach($v as $valType => $message){
                $m[] = str_repeat(HTML_INDENT, (self::$ofsetIndentsCount)) . "\"".$valType ."\" : \"". $message ."\"";
            }
            if (!empty($m)){
                $fields[]=str_repeat(HTML_INDENT, abs(self::$ofsetIndentsCount-1)) . "\"".$k."\": {\n" . implode(",\n", $m) . "}";
            }
        }
        if (!empty($fields)){
            $script =   " \nvar msgs" . self::$jsval["form-counter"] . " = {\n" .
                            implode(",\n",$fields) .
                        "\n}\n";
            self::tag($script,"script");
            self::$jsval["form-counter"]++;
            if (self::$jsval["val-script-included"] != true){
                self::tag("","script",array("src"=>ADD."helpers/val/add/val.js"));
                self::$jsval["val-script-included"] = true;
            }
        }
    }

    public static function prepare($setOrCheck = false){
        if(!is_bool($setOrCheck)){
            if(empty($setOrCheck)){ 
                cf::vdd("ERROR (in a ".__CLASS__." call): The call parameter \$setOrCheck cannot be empty. Expected string or array with valid form::\"...\" method/s names."); 
            } else {                
                $methods = is_array($setOrCheck) ? $setOrCheck : array($setOrCheck); 
                $setOrCheck = false; //Needed to pass trough helper::prepare(false) -> which will confirm the form method is executed in headers;
            }
            foreach($methods as $method){
                self::prepareMethod($method);
            }
        } else if($setOrCheck===false) {
            cf::vdd("ERROR (in a ".__CLASS__." call): Please call the prepare(inpCal | array(\"inpCal\",...)); method with sting or array of needed for preparation form::methods() before the page headers sent."); 
        }
        if( helper::prepare($setOrCheck, (__METHOD__)) !== true){
            return; //Stops the further execution in case the helper call doesn't return true.
        }
    }
    
    private static function prepareMethod($setOrCheck){
        switch($setOrCheck){
            case "inpCal":
                cf::appendToHeadTag("//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css","css");
                cf::appendToHeadTag("//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js");
                cf::appendToHeadTag(ADD."helpers/form/add/inpcal.js");
                break;
            case "inpTags":
                cf::appendToHeadTag(ADD."helpers/form/add/inptags.js");
                cf::appendToHeadTag(ADD."helpers/form/add/inptags.css","css");
                break;
            default:
                cf::vdd("ERROR (in a ".__CLASS__." call): The form method \"{$setOrCheck}\" is not defined in the ".__METHOD__)."."; 
        }
    }
}
