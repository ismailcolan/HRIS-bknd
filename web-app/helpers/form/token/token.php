<?php
/** Token functions - secure & manipulate session or cookies */
class token {
    
    /** Creates Session or Cookie var where the submitted ID is added as token
     * (making possible to restore its value validating its acuracy)
     * @param string $tokenName -> the variable name
     * @param string $tokenType -> must be in_array("session", "cookie")
     * @param type $id -> The id that will be put in the var
     * @param string $tokenDate -> Needed for the validaton
     * @param int $cookieExpire = 0 -> seconds to be added to the current time() -> 1 day = 24*60*60 = 8640 (0 mean the cookie is expired) 
     * @return string $TheToken (only if $tokenType=="cookie" - to can be recorded in database) */
    final static public function SetIdAndDate($tokenName, $tokenType, $id, $tokenDate, $cookieExpire = 0){
        $tokenValue = self::stringFromIdAndDate($id, $tokenDate);
        if($tokenType == "session"){
            $_SESSION[$tokenName] = $tokenValue;
        } else if($tokenType == "cookie"){
            setcookie($tokenName, $tokenValue, time()+$cookieExpire);
            $tokenPieces = explode("||", $tokenValue);
            return $tokenPieces[1];
        } else {
            die("Error: Incorrect token type in ".__METHOD__." call.");
        }
    }
    
    
    /** Extracts validated ID from a storred in session or cookie variable
     * @param string $tokenName -> the variable name
     * @param string $tokenType -> must be in_array("session", "cookie")
     * @param type $refreshIntervalOrExpire -> if $tokenType = session - expects interval ("PT5M") after which to reset the token
     * if $tokenType = cookie - seconds to be added to the current time() -> 1 day = 24*60*60 = 8640 (0 mean the cookie is expired)
     * @return boolean false | $id for session | array("id", "current-date", "current-date", "token") for cookie */
    final static public function GetIdAndReset($tokenName, $tokenType, $refreshIntervalOrExpire = null){
        //echo $tokenName;echo '';print_r($_SESSION);
		 if($tokenType == "session"){
            $tokenToCheck = isset($_SESSION[$tokenName]) ? $_SESSION[$tokenName] : false;
        } else if($tokenType == "cookie"){
            $tokenToCheck = filter_has_var(INPUT_COOKIE, $tokenName) ? filter_input(INPUT_COOKIE, $tokenName) : false;
        } else {
            die("Error: Incorrect token type in ".__METHOD__." call.");
        }
        if($tokenToCheck != false) {
            //We'll get the ID from the token checking accuracy of the submitted token - the format must be ID|Date:token:pieces...
            $tokenData = self::getIdAndDate($tokenToCheck);
            if($tokenData !== false) {
                $tokenId = abs($tokenData["id"]);
                $tokenOldDate = $tokenData["date"];
                if($tokenType == "session"){
                    $resetInterval = trim($refreshIntervalOrExpire);
                    $datetime_current = new DateTime();
                    $datetime_session = new DateTime($tokenOldDate);
                    $datetime_session->add(new DateInterval($resetInterval));
                    if($datetime_current > $datetime_session) {
                        $_SESSION[$tokenName] = self::stringFromIdAndDate($tokenId, $datetime_current->format("Y-m-d H:i:s"));
                    }
                    $result = $tokenId;
                } else if($tokenType == "cookie"){ 
                    $result = array();
                    $result["id"] = $tokenId;
                    $result["current-date"] = $tokenOldDate;
					   $date = new DateTime();
                    $result["new-date"] = $date->format('Y-m-d H:i:s');
                    $result["token"] = self::SetIdAndDate($tokenName, $tokenType, $tokenId, $result["date"], abs($refreshIntervalOrExpire));
                }
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }
        return $result;
    }
    
    /** Validates a token and returns tokenDate and tokenId
     * @param str $tokenToCheck
     * @return boolean false | array ("date"=>dateFromToken, "id"=>idFromToken) */
    protected static function getIdAndDate($tokenToCheck){
		
        $tokenPieces = explode('||', $tokenToCheck);
        $tokenIdAndDateStr = array_shift($tokenPieces);
		
        $tokenData = explode("|",$tokenIdAndDateStr);
        $validate_res = false;
        $tokenId = $tokenData[0];
        $tokenDate = $tokenData[1];
        //The date must be in interval (-30 days) < Date <= Now();
        if(strtotime("- 30 day") < strtotime($tokenDate) && strtotime($tokenDate) <= strtotime("now")) {
            $retArray = array ("date"=>$tokenDate);
            $strToValidate = $tokenId . "|" . $tokenDate;
            if (version_compare(phpversion(), '5.5.0', '>=')) {
                $validate_res = password_verify( $strToValidate , $tokenPieces[0] );
            } else {
                $token = $tokenPieces[0];
                $mac = $tokenPieces[1];
                $checkMac = hash_hmac('sha256', $strToValidate. '||' . $token, WEBAPP_PHP_SECRET_KEY);
                $validate_res = cf::timingSafeCompare($checkMac, $mac);
            }
            if($validate_res === true){
                $retArray["id"] = $tokenId;
            }
        } else {
            $validate_res = false;
        }
        return $validate_res === true ? $retArray : false;
    }
    
    /** Validates a token and returns tokenDate and tokenId
     * @param str $tokenToCheck
     * @return boolean false | array ("date"=>dateFromToken, "id"=>idFromToken) */
    protected static function stringFromIdAndDate($tokenId, $tokenDate){
        $tokenStr = $tokenId . "|" . $tokenDate;
        if (version_compare(phpversion(), '5.5.0', '>=')) {
            $tokenRes = ($tokenStr."||".password_hash($tokenStr, PASSWORD_DEFAULT));
        } else {
            $ivSize = mcrypt_get_iv_size(MCRYPT_CAST_256, MCRYPT_MODE_CFB);
            $tokenVector = mcrypt_create_iv($ivSize, MCRYPT_DEV_RANDOM);
            $tokenCode = hash("sha512",$tokenVector);
            $tokenStr .= '||' . $tokenCode;
            $mac = hash_hmac('sha256', $tokenStr, WEBAPP_PHP_SECRET_KEY);
            $tokenRes = ($tokenStr . '||' . $mac);
        }
        return $tokenRes;
    }
}
