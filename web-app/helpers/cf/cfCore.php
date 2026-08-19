<?php
/** The common functions core */
class cfCore {
    
    /** @var type array, after cf::RequestArray() execution it will look like:  array(
     *    "complete"=>string
     *    "url-full"=>string -> above without the query string
     *    "filename"=>(null|string) -> "contact.html" file name,
     *    "ext"=>string(null|string) -> "html"
     *    "url"=>(null|string) -> "contact"
     *    "dir-list"=>(null|array())     ) */
    public static $rq = null;
    static $pg, $lang, $pageHeadTagContent=array();
    protected static $preparedMethods = array();
    
    /** Reads the servers reuqest vars $_SERVER[HTTP_HOST, SERVER_PORT, REQUEST_URI] setting self::$rq propery
     * @see cf::$rq
     * @return array */
    final static public function RequestArray() {
        //@see {@link [http://php.net/manual/es/function.filter-input.php#77307] [FastCGI filter_input(INPUT_SERVER) problem]}
        if(!is_null($host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_URL))){
            $protocol = filter_input(INPUT_SERVER,'SERVER_PORT', FILTER_SANITIZE_NUMBER_INT) === 443 ? "https://" : "http://";
            $complete = $protocol . $host . ($requestUri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL));
        } else {
            $protocol = (bool)(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === "on") ? "https://" : "http://";
            $requestUri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);            
            $complete = ($protocol . ($host=filter_var($_SERVER["HTTP_HOST"],FILTER_SANITIZE_URL)).$requestUri );
        }
        $request=array(); //set ordering
        $request['complete'] = str_replace("\\","/",$complete);
        $request['url-full'] = strtok($request['complete'],"?");
        $url = explode("/", substr($request['url-full'], strlen($protocol.$host."/")));
        $request["filename"]=array_pop($url);
        $request["ext"] = count($v = explode(".",$request["filename"]))>1 ? array_pop($v) : null;
        $request["url"] = !empty($v) ? implode(".",$v) : $request["filename"];
        $request["dir-list"]= !empty($url) ? $url : null;
        return self::$rq = $request;
    }
    
    /** Description: Appends content into the $pageHeadTagContent
     *  can be called as: 
     *      cf::appendToHeadTag(
     *                          array(
     *                                 "//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.3/jquery.mobile.min.css"=>"css",
     *                                 "//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.3/jquery.mobile.min.js"=>"script"
     *                                )
     *      );
     *  OR
     *      cf::appendToHeadTag("//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js","script");
     *      cf::appendToHeadTag("//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js");
     *  default content type = "script"; */
    final static public function appendToHeadTag($data = array(),$arg_type="script", $args = array()){
        $content = array();
        if(is_array($data)){
            foreach($data as $tag_content => $tag_type){
                $content[] = array($tag_type,$tag_content, array());
            }
        } else {
            $content[] = array($arg_type, $data, $args);
        }
        //cf::vdd($content);
        $headTagAddons = array();
        foreach($content as $v){
			
            switch($v[0]){
                case "css":             
				  $headTagAddons[] = form::tagOut("","link",array_merge($v[2],array("rel"=>"stylesheet","href"=>$v[1])));     break;
                //For the meta tag we need one more var => name => content
                //case "meta":    $headTagAddons[] = self::getOutput("form","tag",array("","meta",array("name"=>"stylesheet","href"=>$v[1])));     break;
                case "inline-script":   $headTagAddons[] = form::tagOut($v[1], "script", $v[2]);     break;
                default :               
				$headTagAddons[] = form::tagOut("","script", array_merge($v[2],array("src"=>$v[1])));    
				//print_r($headTagAddons);
				break;
            }
        }
        self::$pageHeadTagContent = empty(self::$pageHeadTagContent) ? $headTagAddons : array_merge(self::$pageHeadTagContent, $headTagAddons);
		//print_r(self::$pageHeadTagContent);
    }
    
    /** Description: Redirects the visitor
     *  final function Redirect();
     *  forward to a new url */
    final static public function Redirect($redirectUrl) {
        if ($redirectUrl) {
            if (headers_sent()) {   die("<script>document.location=\"" . $redirectUrl . "\";</script>");
            } else {                die(header('Location: ' . $redirectUrl, true, 302)); }
        }
    }
    
    /** Description: final function cf::vdd() - var_dump shortage with die(); - writes vars formated dump for arguments. */
    final static public function vdd(){
        $args = func_get_args();
        echo "<pre>";
        foreach($args as $value){
            var_dump($value);
        }
        $callers=debug_backtrace();
        $from="Dump triggered from: "  .  $callers[1]["class"] . "::" . $callers[1]["function"] . " | " . $callers[0]["file"] . " (line: " . $callers[0]["line"] . ")";
        die("</pre><p style=\"font-family:Tahome,sans-serif; padding-top:5px; font-size:12px; color:#ccc; border-top:1px dotted #ccc\">".$from."</p>");
    }
    
    /** Description: final function cf::vd() - var_dump shortage - writes vars formated dump for submitted arguments.*/
    final static public function vd(){
        $args = func_get_args();
        echo "<pre>";
        foreach($args as $value){
            var_dump($value);
        }
        $callers=debug_backtrace();
        $from="Dump triggered from: "  .  (isset($callers[1]["class"]) ? $callers[1]["class"] . "::" . $callers[1]["function"] : "<i>Out-of-a-class-call...</i>") . " | " . $callers[0]["file"] . " (line: " . $callers[0]["line"] . ")";
        echo "<p style=\"font-family:Tahome,sans-serif; padding-top:5px; font-size:12px; color:#888; border-top:1px dashed #888; margin:0px;\">".$from."</p></pre>";
    }
    
    /** Dies, showing an end message
     *  @param string $msg
     *  @param integer $callIndex  - the index from the debug_backtrace() to be shown below the message */
    final static function end($msg, $callIndex = 1){
        $callers=debug_backtrace();
        $from="The end triggered from: "  .  (isset($callers[$callIndex]["class"]) ? $callers[$callIndex]["class"] . "::" . $callers[$callIndex]["function"] . " | " : "") . $callers[$callIndex]["file"] . " (line: " . $callers[$callIndex]["line"] . ")";
        die("<pre style=\"font-family:Tahoma,sans-serif; color:#888;\">".  $msg."</pre><p style=\"font-family:Tahoma,sans-serif; padding-top:5px; font-size:12px; color:#ccc; border-top:1px dotted #ccc\">".$from."</p>");        
    }
    
    /** Description: gets generated output of a class method execution */
    final static public function getOutput($class="", $method="", $vars=array()){
		
        if(empty($class) || empty($class)){die('$class and $method call cannot be empty.');}
		 
        ob_start();
        call_user_func_array(array(__NAMESPACE__ ."\\".$class, $method), $vars);
        return ob_get_clean();
		
    }

    /** Control Output Buffering: Start or Get current buffer contents and delete current output buffer
     * @param string $getBufferContent - if skipped or passed with "Nooo" starts buferring els return buffer in the passed var */
    static function buffer(&$getBufferContent="Nooo"){
        if($getBufferContent==="Nooo"){ ob_start();
        } else {                        $getBufferContent = ob_get_clean(); }
    }
    
    /** Description: array nested keys lookup
     *  @param type $key
     *  @param type $arr
     *  @return type */
    final static function multyArrayKeyExists($key, $arr){
        $result = array_key_exists($key, $arr);
        if ($result)
            return $result;
        foreach ($arr as $v) {
            if (is_array($v)) {
                $result = self::multy_array_key_exists($key, $v);
            }
            if ($result)
                return $result;
        }
        return $result;
    }
    

    /** Creates salt string (hexadecimal number) from a word
     * @param string $word
     * @param int $returnStrLength = 32
     * @return string */
    final static function saltAnWord($word = "", $returnStrLength = 32) {
        $stringSalt = false;
        if(!empty($word)) { 
            $securePhrase = ";&=+<>3+LGT25H);x&*'X<6;T%15$'&J";
            $string = $securePhrase . $word;
            $stringMd5 = md5($string);
            $stringSalt = ($returnStrLength <= 32) ? substr($stringMd5,0,$returnStrLength) : str_pad($stringMd5,$returnStrLength,"-=");
        }
        return $stringSalt; 
    }
    
    final static function hashTokenByPassAndSalt($pass, $user){
        $salt = hash("sha256",WEBAPP_PHP_SECRET_KEY.$user."123455");
        //Adding prefix of "$5$" and limiting salt string to 16chars after pushes CRYPT_SHA256 usage
        $salt = "$5$".substr($salt,0,16);
        //The crypt will always return a 63 chars string return crypt($pass, $salt);
        //Well remove first 13 of them (maybe they will show algo), then remove some special chars
        $token =  substr(preg_replace("/[^a-zA-Z0-9]+/","",crypt($pass, $salt)),13,60);
        return $token;
    }
    
    /**Description: A timing safe equals comparison
    * To prevent leaking length information, it is important that user input is always used as the second parameter.
    * @param string $safe The internal (safe) value to be checked
    * @param string $user The user submitted (unsafe) value
    * @return boolean True if the two strings are identical.     */
    static function timingSafeCompare($safe, $user) {
       // Prevent issues if string length is 0
       $safe .= chr(0);
       $user .= chr(0);

       $safeLen = strlen($safe);
       $userLen = strlen($user);

       // Set the result to the difference between the lengths
       $result = $safeLen - $userLen;

       // Note that we ALWAYS iterate over the user-supplied length
       // This is to prevent leaking length information
       for ($i = 0; $i < $userLen; $i++) {
           // Using % here is a trick to prevent notices
           // It's safe, since if the lengths are different
           // $result is already non-0
           $result |= (ord($safe[$i % $safeLen]) ^ ord($user[$i]));
       }

       // They are only identical strings if $result is exactly 0...
       return $result === 0;
    }

    static function visitorBrowserData($url) { 
        $user_data=Array(); 
        $user_data["IP"] = getenv('REMOTE_ADDR');
        if(isset($url)) {$user_data["SignInURL"] = $url;}
        if(filter_has_var(INPUT_POST, 'visitor-sr')) {$user_data["SR"] = str_replace(' ','x',  filter_input(INPUT_POST, 'visitor-sr'));}
        
        $browser = @get_browser(null, true);
        if(isset($browser)) {
            $browserData = array("platform"=>"OS", "parent"=>"Browser", "cssversion"=>"CssVersion", "javascript"=>"JsEnabled");
            foreach($browserData as $dF => $dT){
                if(!empty($browser[$dF])){
                    $user_data[$dT] = $browser[$dF];
                }
            }
        }
        return json_encode($user_data);
    }
    
    final static function isAdmin($usrData){
        return (abs($usrData["waaID"])>0 ? true : false);
    }

	
    /** output tag multyselect
     * @param array $list
     * @param array|string $active array to check value exists is | the list field name where to look for status
     * @param string $listFieldId - the list field name for the item id
     * @param string $listFieldName - the list field name for the text 
     * @param string $selectId The container div id */
    static function tagMultySelect($list, $active, $listFieldId, $listFieldName, $selectId = "multy-select", $allowOrder = false){
        if(!is_array($list)){ die(__METHOD__.": Incorrect parameters request call."); }
        form::openDiv(array("id"=>$selectId,"class"=>"multy-select"));
            foreach($list as $el){
                if(!$allowOrder) {
                    $st = in_array(strtolower($el[$active]),array("yes","no")) ? strtolower($el[$active]) : die(__METHOD__.": Incorrect status value \"".$el[$active]."\" request call.");
                } else {
                    $st = abs($el[$active]) > 0 ? "yes" : "no";
                }
                form::openSpan(array("class"=>$st, "data-init"=>$st, "data-value"=>$el[$listFieldId], "onclick"=>"multySelect.setNode(this,'change')"));
                    if($allowOrder){
                        form::span("",array("class"=>"fa fa-bars right"));    
                    }
                    form::span("",array("class"=>"fa fa-square-o fa-lg"));
                    form::span("",array("class"=>"fa fa-check-square-o fa-lg"));
                    form::html($el[$listFieldName]);
                form::closeSpan();
            }
        form::closeDiv();
        form::tag("","script",array("src"=>ADD."helpers/cf/add/tagmultyselect.js"));
        if($allowOrder){
            form::tag("","script",array("src"=>ADD."helpers/cf/add/sortable.min.js"));
            form::tag('
                Sortable.create(document.getElementById("'.$selectId.'"), {
                    handle: ".fa-bars",
                    filter: ".no"
                });
                ',"script");
        }
    }
}
