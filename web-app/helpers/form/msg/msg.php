<?php
/** The message PHP helper*/
class msg 
{
    /** @const string MSG_SESSION_KEY Default $_SESSION key to be used */
    const MSG_SESSION_KEY = "web-app-msgs";
//    /** @const boolean Do the site use bootstrap CSS ({@link https://getbootstrap.com}) */
    const USE_BOOTSTRAP_CSS = true;
    
    /**@var array Context "map" to dedicated Bootstrap class attribute*/
    private static $contextMapToBootstrapClass = array(
        "plain"=>"alert-info", "success"=>"alert-success",
        "error"=>"alert-danger", "warning"=>"alert-warning"
    );

    /**Sets the message in the session
     * @param string $msg The message content
     * @param string $context For the background color of message tag; One of
     *      ["plain"(gray/blue),"success"(green),"error"(red), "warning"(yellow)]
     * $param (null)|string $customSessionKey - Adjustment to the session key. 
     *      For the cases where separate messages are has to appear on diferent 
     *      positins.*/
    final static function set($msg="", $context="plain", $customSessionKey=null){
        self::prepare(true);
        $key = (isset($customSessionKey)? $customSessionKey : self::MSG_SESSION_KEY);
        $msgs = &$_SESSION[$key];
        if(!is_array($msgs)){
            $msgs = array();
        }
        if(!in_array($context, array_keys(self::$contextMapToBootstrapClass))){
            die("Incorrect msg type.");
        }
        $msgs[] = array("value"=>$msg, "context"=>$context);
    }
    
    /**Shows storred in the session message(s)
     * $param (null)|string $customSessionKey - Adjustment to the session 
     * variable name. For the cases where separate messages needed (to appear on diferent positins).*/
    final static function get($customSessionKey=null){
        self::prepare(true);
        $key = (isset($customSessionKey)? $customSessionKey : self::MSG_SESSION_KEY);
        $msgs = isset($_SESSION[$key]) ? $_SESSION[$key] : array();
		 $s='';
        foreach ($msgs as $msg){
			  if (strpos($s,$msg['value']) === false) {
				$s.=$msg['value'];
				self::Html($msg);
			  }
        }
        unset($_SESSION[$key]);
    }
	
	final static function returnVal($customSessionKey=null){
        self::prepare(true);
        $key = (isset($customSessionKey)? $customSessionKey : self::MSG_SESSION_KEY);
		
        $msgs = isset($_SESSION[$key]) ? $_SESSION[$key] : array();
		
		 $ret='';
		 $i=count($msgs);
		 $z=1;
        foreach ($msgs as $msg){
            if (strpos($ret, $msg["value"])=== false) $ret.=$msg["value"];
			  if ($z != $i) $ret.='<br>';
			  $z++;
        }
        unset($_SESSION[$key]);
		 if ($ret == '') $ret='No new notifications.';
		 return $ret;
    }
	
	
    
    final static function clear($customSessionKey=null){
        self::prepare(true);
        $key = (isset($customSessionKey)? $customSessionKey : self::MSG_SESSION_KEY);
        unset($_SESSION[$key]);
    }
    
    /** Just shows a message
     * @param array|string $msg  - is is_array($msg) the $type is ignored as type is expected to be set in the array inside
     * @param string $context = "plain" */
    final static function put($msg, $context="plain"){
        self::prepare(true);
        $msgs = is_array($msg) ? $msg : array(array("value"=>$msg, "context"=>$context));
        foreach ($msgs as $msg){
            self::Html($msg);
        }
    }
	
    private static function Html($msg){
        if(!(bool)self::USE_BOOTSTRAP_CSS){
            form::openP(array("class"=>"app-msg ".$msg["context"]));
                form::html($msg["value"]);
                form::link("javascript:void(0);","close",array("class"=>"material-icons","onclick"=>"var _this = this; setTimeout(function(){_this.parentNode.style.display = 'none';},400); this.parentNode.setAttribute('class',(this.parentNode.getAttribute('class')+' msg-fadeout'));"));
            form::closeP();
        } else {
            $bootstrapCSSClass = array_key_exists($msg["context"], self::$contextMapToBootstrapClass) ? self::$contextMapToBootstrapClass[$msg["context"]] : $msg["context"];
            form::openP(array("class"=>"alert alert-dismissible ".$bootstrapCSSClass));
                form::openButton(array("type"=>"button", "class"=>"close", "data-dismiss"=>"alert", "aria-label"=>"Close"));
                    form::span("&times;",array("aria-hidden"=>"true"));
                form::closeButton();
                form::html($msg["value"]);
                form::link("javascript:void(0);","&nbsp;",array("onclick"=>"var _this = this; setTimeout(function(){_this.parentNode.style.display = 'none';},400); this.parentNode.setAttribute('class',(this.parentNode.getAttribute('class')+' msg-fadeout'));"));
            form::closeP();
        }
    }
    
    static function prepare($setOrCheck = false){
        if( helper::prepare($setOrCheck, (__METHOD__)) !== true ){
            return; //Stops the further execution in case the helper call doesn't return true.
        }
        //Starts the session if its not started yet.
        if (@!session_status() || @session_status() == PHP_SESSION_NONE) {
            @session_start();
        }
        cf::appendToHeadTag(ADD."helpers/msg/add/msg.css", "css");
    }
}