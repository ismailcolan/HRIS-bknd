<?php
/** The Validation Class.
 *  @example val::required($fieldName);
 *  @category app helper */
class val extends valCore {
    
    /** Check for a vlue to be unique in a table
     * @param string $name - The POST(GET) field name
     * @param string $tbl - The table where field is added
     * @param string $key - The key name of the above table
     * @param mixed $id - The current record id (key)
     * @param string $msg - The validation message
     * @param string $dbField ($name) - The current field name in db - if different than the post var name
     * @return boolean */
    public static function unique($name, $tbl, $key, $id=null, $msg = "Please use unique value.", $dbField=null){
        if (self::$runat == "server") {
            $dbField = is_null($dbField) ? $name : $dbField;
            $request = filter_input(INPUT_SERVER, "REQUEST_METHOD") === "POST" || $_SERVER['REQUEST_METHOD'] === 'POST' ? INPUT_POST : INPUT_GET;
            $q = "SELECT * FROM `{$tbl}` WHERE `{$dbField}`=".db::EscapeStr(filter_input($request, $name), true);
            $q .= is_null($id) ? "" : " AND `{$key}`!=".db::EscapeStr($id, true);
            if(db::NumRows(db::SQL($q))===0){
                return true;
            } else {
                self::setError($name, $msg);
                return false;
            }
        }
    }

    /**
     * Check if a string is a valid date(time)
     *
     * DateTime::createFromFormat requires PHP >= 5.3
     *
     * @param string $name
     * @param string $format
     * @param string $msg
     * @return bool
     */
    public static function date($name, $format, $msg){
        $request = filter_input(INPUT_SERVER, "REQUEST_METHOD") === "POST" || $_SERVER['REQUEST_METHOD'] === 'POST' ? INPUT_POST : INPUT_GET;
        $str_dt = filter_input($request, $name);
        $date = DateTime::createFromFormat($format, $str_dt);
        $isValid = $date && DateTime::getLastErrors()["warning_count"] == 0 && DateTime::getLastErrors()["error_count"] == 0;
        if($isValid) {
            return true;
        } else {
            self::setError($name, $msg);
            return false;
        }
    }

    public static function requireImg($name){
        //On save we write $content value on the controler class to can access it here
        $file  = empty(piece::$content) ? filter_input(INPUT_POST, $name."-filename") : piece::$content;
        return empty($file) ? false : true;
    }
    
    public static function throwServerRunError(){
		
        if(self::$runat==="server" && !empty(form::$error)){ 

            throw new Exception;
        }
    }
	
	public static function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
	}
	
	public static function convDate($FROM_format,$TO_format,$d){
       if ($d=='0000-00-00' || $d=='0000-00-00 00-00-00') return '';
	
		$date = DateTime::createFromFormat($FROM_format,$d);
		
		if (!$date) return '';

		return $date->format($TO_format);
	}
	
	public static function currentDate($format){
		$date = new DateTime();
		return $date->format($format);
	}
		
	
	
	
	
	public static function positionRequirePhysicianField($posID){
		$posID = intval($posID);
		$res = db::PrepareLoad('select * from positions WHERE pID ='.$posID);
		
		
		if ($res != false) {
			if ($res[0]['pPhisician'] == 'no') return false;
			else return true;
		}
		else return false;
		
		
		
	}
}
