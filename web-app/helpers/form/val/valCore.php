<?php
/** The Validation Core Methods Class */
class valCore {

    public static $runat;

    public static function init($runat) {
        if(!in_array($runat,array("client", "server", false))){
            die("Incorrect validation call request.");
        } 
        self::$runat = $runat;
        form::$jsval["val-".$runat] = true;
        if (empty(form::$error)) {
            form::$error = array();
        }
    }
	
	public static function isInt($name, $msg = "Please provide an integer value.") {
		$val = trim(filter_input(INPUT_POST, $name));
		if (self::$runat == "server"){
			if ((string)intval($val) != $val) {
				self::setError($name, $msg);
				
				return false;
			} else {
				return true;
			}
		} 
    }

    /** Makes a field mandatry
     *  @static
     *  @param string $name
     *  @param string $msg
     *  @param string $inpType=text -> from array("text","select","image","cal") 
     *  @return boolean */
    public static function required($name, $msg, $inpType = "text") {
        if(!in_array($inpType, array("text","select","image","file","cal","date-selects","check-list"))){ die("Error: incorrect \$inpType value in the ".__METHOD__." call."); }

        if (self::$runat == "client") {
            if($inpType == "file"){
                return self::callback($name, "requireFile", $msg);
            } else if($inpType == "image"){
                return self::callback($name, "requireImg", $msg);
            } else if($inpType == "cal"){
                return self::callback($name, "requireCal", $msg);
            }
            self::setJsVal($name, array("required" => true), $msg);
            if ($inpType == "select") {
                self::setJsVal($name, array("data-exclude" => "{!--Empty--!}"), $msg);
            } else if($inpType == "date-selects"){
                self::setJsVal($name."-date", array("required" => true), $msg);
                self::setJsVal($name."-date", array("data-exclude" => "{!--Empty--!}"), $msg);
                self::setJsVal($name."-month", array("required" => true), str_replace("date","month",$msg));
                self::setJsVal($name."-month", array("data-exclude" => "{!--Empty--!}"), str_replace("date","month",$msg));
                self::setJsVal($name."-year", array("required" => true), str_replace("date","year",$msg));
                self::setJsVal($name."-year", array("data-exclude" => "{!--Empty--!}"), str_replace("date","year",$msg));
            } else if($inpType == "check-list"){
                self::setJsVal($name, array("data-min" => 1, "data-max"=>0), $msg);
            }
        } else if (self::$runat == "server") {
            if($inpType == "image"){
                return self::callback($name, "requireImg", $msg);
            } else if (strlen(trim(filter_input(INPUT_POST, $name))) == 0) {
				if ($name=='afSalary') echo '>>>>'.$_POST[$name];
                self::setError($name, $msg);
                return false;
            } else if ($inpType == "select" && filter_input(INPUT_POST, $name) == "{!--Empty--!}") {
                self::setError($name, $msg);
                return false;
            } else if ($inpType == "date-selects") {
//cf::vdd(__METHOD__," - - ");
                    self::setError($name, $msg);
                    return false;
            } else {
                if($inpType == "date-selects"){
                }
                return true;
            }
        }
    }

    /** Limit the maximum symbols count
     *  @static
     *  @param string $name
     *  @param integer $maxLength
     *  @param string $msg
     *  @return boolean */
    public static function maxLength($name, $maxLength, $msg = "Too many characters (max. %d)") {
        $maxLength = abs($maxLength);
        $msg = sprintf($msg, $maxLength);
        if (self::$runat == "client") {
            self::setJsVal($name, array("maxlength" => $maxLength), $msg);
        } else if (self::$runat == "server") {
            if (strlen(trim(filter_input(INPUT_POST, $name))) > $maxLength) {
                self::setError($name, $msg);
                return false;
            } else {
                return true;
            }
        }
    }

    /** Requires minimal symbols count
     *  @static
     *  @param string $name
     *  @param integer $minLength
     *  @param string $msg
     *  @return boolean */
    public static function minLength($name, $minLength, $msg = "Please add at least %d characters.") {
        $minLength = abs($minLength);
        $msg = sprintf($msg, $minLength);
        if (self::$runat == "client") {
            self::setJsVal($name, array("data-minlength" => $minLength), $msg);
        } else if (self::$runat == "server") {
            if (strlen(trim(filter_input(INPUT_POST, $name))) < $minLength) {
                self::setError($name, $msg);
                return false;
            } else {
                return true;
            }
        }
    }

    /** Limit the maximum for a number field
     *  @static
     *  @param string $name
     *  @param integer $max
     *  @param string $msg
     *  @return boolean */
    public static function numMax($name, $max, $msg = "Please enter a number lower or equalt to: %d.") {
        $max = abs($max);
        $msg = sprintf($msg, $max);
        if (self::$runat == "client") {
            self::setJsVal($name, array("data-max" => $max), $msg);
        } else if (self::$runat == "server") {
            if (filter_input(INPUT_POST, $name, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) > $max 
			   || filter_input(INPUT_POST, $name, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) < 0) {
                self::setError($name, $msg);
                return false;
            } else {
                return true;
            }
        }
    }

    /** Limit the minimum for a number field
     *  @static
     *  @param string $name
     *  @param string $msg
     *  @return boolean */
    public static function numMin($name, $min, $msg = "Please enter a number greater or equal to than: %d.") {
        $min = abs($min);
        $msg = sprintf($msg, $min);
        if (self::$runat == "client") {
            self::setJsVal($name, array("data-min" => $min), $msg);
        } else if (self::$runat == "server") {
            if (filter_input(INPUT_POST, $name, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) < $min 
			 || filter_input(INPUT_POST, $name, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) < 0) {
                self::setError($name, $msg);
                return false;
            } else {
                return true;
            }
        }
    }

    public static function validDate($name, $msg = "' %s ' is not a valid date value.<br>Valid example: 12/30/1990") {
         
		  
		  if (self::$runat == "server") {
            $test_date = filter_input(INPUT_POST, $name);
			
            if(empty($test_date)) {
                return true;
            }
			
            $msg = sprintf($msg, $test_date, USER_DATE);
            $date = DateTime::createFromFormat(USER_DATE, $test_date);
            $date_errors = DateTime::getLastErrors();
            if ($date_errors['warning_count'] + $date_errors['error_count'] > 0) {

                $date = DateTime::createFromFormat(GLOBAL_PHP_DATE_ALT, $test_date);
                $date_errors = DateTime::getLastErrors();
                if ($date_errors['warning_count'] + $date_errors['error_count'] > 0) {
                    self::setError($name, $msg);
					
                    return false;
                } else {
                    return true;
                }
            } else {
                return true;
            }
        }
    }

    /** Apply regular expression to a field
     *  @static
     *  @param string $name
     *  @param integer $re
     *  @param string $msg
     *  @return boolean */
    public static function regExp($name, $re, $msg = "Please match the requested format.") {
        if (self::$runat == "client") {
            self::setJsVal($name, array("data-re" => $re), $msg);
        } else if (self::$runat == "server") {
            switch($re){
				   case "JSVAL_RX_SSN":
						$re = "/^[0-9]{3}-[0-9]{2}-[0-9]{4}$/";
						break;
                case "JSVAL_RX_EMAIL":
                    $re = "/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,5})+$/";
                    break;
                case "JSVAL_RX_PASSWORD":
                    $re = "/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/";
                    break;
                case "JSVAL_RX_TEL":
                    $re = "/^1?[\-]?\(?\d{3}\)?[\-]?\d{3}[\-]?\d{4}$/";
                    break;
                case "JSVAL_RX_PC":
                    $re = "/^[a-z]\d[a-z]?\d[a-z]\d$/i";
                    break;
                case "JSVAL_RX_ZIP":
                    $re = "/^\d{5}$/";
                    break;
                case "JSVAL_RX_MONEY":
                    $re = "/^\d+([\.]\d\d)?$/";
                    break;
                case "JSVAL_RX_MONEY_COMMAS":
                    $re = "/^\s*[$]?\s*((\d+)|(\d{1,3}(\,\d{3})+))(\.\d{1})?(\d{1})?\s*$/";
                    break;
                case "JSVAL_NAME":
                    $re = "/^([a-zA-Z]+)([a-zA-Z\\'\\-\\ &]+)\$/";
                    break;
                case "JSVAL_RX_CREDITCARD":
                    $re = "/^[1-9][0-9]{0,15}$/";
                    break;
            }
            if (preg_match($re, $_REQUEST[$name]) == 0) {
                self::setError($name, $msg);
                return false;
            } else {
                return true;
            }
        }
    }

    /** Requires the field value to match the value of another field
     *  @static
     *  @param string $name
     *  @param string $nameEqualTo
     *  @param string $msg
     *  @return boolean */
    public static function equals($name, $nameEqualTo, $msg) {
        if (self::$runat == "client") {
            self::setJsVal($name, array("data-equals" => $nameEqualTo), $msg);
        } else if (self::$runat == "server") {
            if(cf::timingSafeCompare(trim($_REQUEST[$name]), trim($_REQUEST[$nameEqualTo]))){
                return true;
            } else {
                self::setError($name, $msg);
                return false;
            }
        }
    }
    
    /** Checks do the selected value/s match the allowed value/s
     *  @static
     *  @param string $name
     *  @param str|arr $validValues
     *  @param string $msg
     *  @return boolean */
    public static function validValue($name, $validValues = array(), $msg = "The field value is not in the allowed list.") {
        if (self::$runat == "client") {
            //self::setJsVal($name, array("data-re" => $re), $msg);
        } else if (self::$runat == "server") {
            if (is_array($validValues)) {
                if (in_array(filter_input(INPUT_POST, $name), $validValues) != true) {
                    self::setError($name, $msg);
                    return false;
                } else {
                    return true;
                }
            } else {
                if (filter_input(INPUT_POST, $name) != $validValues) {
                    self::setError($name, $msg);
                    return false;
                } else {
                    return true;
                }
            }
        }
    }
    
    /** Calls custom callback function
     *  For the client side the function "$jsFuncName" must be defined on the page's JS
     *  while servers validation must be a defined validation method.
     *  @param string $name
     *  @param string $jsFuncAndMethod
     *  @param string $msg
     *  @return boolean */
    public static function callback($name, $jsFuncAndMethod, $msg = "Please fulfill %s requirenmens.") {
        $message = sprintf($msg, $jsFuncAndMethod);
        if (self::$runat == "client") {
            self::setJsVal($name, array("data-callback" => $jsFuncAndMethod), $message);
        } else if (self::$runat == "server") {
            $res = (bool) val::$jsFuncAndMethod($name);
            if($res !== true) {
                self::setError($name, $message);
                return false;
            }
        }
    }

    /** Sets the error messages array in the form class needed
     *  @static
     *  @param string $name
     *  @param arr $attr
     *  @param string $msg */
    public static function setJsVal($name, $attr = array(), $msg = "") {
        foreach ($attr as $k => $v) {
            if (!empty($msg)) {
                form::$jsval["msgs"][$name][$k] = $msg;
            }
            form::$jsval["validations"][$name][$k] = $v;
        }
    }

    /** Triggers validation error (stops the form on validation and shows the error)
     *  @static
     *  @param string $name
     *  @param string $msg 
     *  Examples: 
     *      val::regExp("fieldName", "/^[A-Za-z0-9\-\']{2,30}$/"); 
     *      val::regExp("fieldName", "/^([A-Za-z0-9\s-'])+([A-Za-z0-9\s-'])$/");
     *    */
    public static function setError($name, $msg) {
        if (!in_array(substr($msg, -1), array(" ",".", "!", "?"))) {
            $msg.=".";
        }
        form::$error[$name][] = $msg;
    }
}
