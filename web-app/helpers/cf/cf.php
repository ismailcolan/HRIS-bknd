<?php
/** The common helper class */
class cf extends cfCore {

    static $dPoint = ".";
    static $currency = "$";
    static $legacy_symbol = array(  "L" =>"<span class=\"legacy text-text-muted\" title=\"Legacy\"> (L)</span>",
                                    "L2" =>"<span class=\"legacy text-text-muted\" title=\"Legacy\"> (L2)</span>",
                                    "A" =>"<span class=\"legacy text-text-muted\" title=\"Legacy\"> (A)</span>",
                                    "C" =>"<span class=\"legacy text-text-muted\" title=\"Legacy\"> (C)</span>",
                                    "N" =>"<span class=\"legacy text-text-muted\" title=\"Legacy\"> (N)</span>"
                                );

    /**Legacy tag - Applicable for `llist_lawyers` & llist_firms
     * Also applicable for `llist_firms_offices`, but not requested by the client
     * @param string $LegacyTagDbValue
     * @param (null)|string $Claimed_lUserId 
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * L  - keep this for legacy
     * L2 - latest db of new lawyers (all doc ones)
     * C  - for claimed, tells us legacy attorney has been claimed
     * A  - ones that get added by us in ADMIN
     * N  - for new lawyers who add themselves, which also means claimed.*/
    static function LT($LegacyTagDbValue, $Claimed_lUserId=null){
        if($LegacyTagDbValue==="N" || is_null($Claimed_lUserId)){
            $resultLT = $LegacyTagDbValue;
        } else {
            $resultLT = "C";
            /* Only for `llist_lawyers` LegacyTag diferent than "N" is has to be
             * switthed to "C" (claimed) if defined `llist_lawyers`.`lUserId`*/
        }
        return self::$legacy_symbol[$resultLT];
    }

	 static function InAdmin(){
		$actual_link = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	   if (strpos($actual_link,'admin') !== false) return true;
	   else return false;
	 }

    /**Sets the image crop settings 
     * can be called in the page constuctor - when self::$rq === "details"
     * or in the validation method as the img errors are returned as inpImg validation messages */
    static function AttorneyImgsSetup(){
        img::$setup = array(); //Its better to reset the values
        img::$setup["path"] = AttorneyProfileModel::ATTORNEY_IMG_PATH;
        img::$setup["crop"] = "inline";
        img::$setup["width"] = "320";
        img::$setup["height"] = "320";
        img::$setup["disable-delete"] = true;
        //img::$setup["path-thumb-copy"] = img::$setup["path"]."lal/";
        //img::$setup["width-thumb-copy"] = "100";
        img::$setup["allowed-extensions"] = array("jpg","png");
    }
    
    /** Push the PHP to show all errors */
    static function showErrors(){
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }
    
    static function rename($name, $first = false){
		$count = explode(" ", $name);
		$regex = true;
		if(count($count[0]) == 2 && count($count[1]) == 2){
			$regex = false;
		}
		if($regex == true){
			if($first === true){
				$pattern = '/\A[a-zA-Z]{1}[.]{1}\s/';
				$replace = '';
			}else{
				$pattern = '/\s?[a-zA-Z]{1}[.]{1}\s/';
				$replace = ' ';
			}
			$name = preg_replace($pattern, $replace, $name);
			if($first === false){
				$name = explode(" ", $name);
				if(count($name) > 2 && strlen($name[2]) > 3){
					unset($name[1]);
				}
				$name = implode(" ", $name);
			}
		}
		return $name;
	}
    
    static function processPageUrl(&$pageId, &$pagesData, $pgUrl){
            $allPages = PageModel::dataPages();
            if($allPages) {
                $pagesUrls = array();
                $pagesData = array();
                foreach($allPages as $v){
                    $id = $v["fpID"];
                    unset($v["fpID"]);
                    $pagesData[$id] = $v;
                    $pagesUrls[$id] = $v["fpUrl"];
                }
                $pageId = in_array($pgUrl, $pagesUrls) ? array_search($pgUrl, $pagesUrls) : false;
                if($pageId) {
                    $request = "//".filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_URL) . filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
                    $requestUrl = strtok(str_replace(WP,"",$request),"?");
                    if(!empty($requestUrl) && $requestUrl != $pagesUrls[$pageId]){
                        cf::Redirect(WP.$pagesUrls[$pageId]);
                        die;
                    } else if($pageId == 1 && !empty($requestUrl)){
                        cf::Redirect(WP);
                        die;
                    }
                }
            }
    }
    
    /**Sets the image settings 
     * can be called in the page constuctor - when self::$rq === "details"
     * or in the validation method as the img errors are returned as inpImg validation messages */
    static function authorImgSetup(){
        img::$setup = array(); //Its better to reset the values
        img::$setup["path"] = constant('AUTHORS_IMAGES');
        //img::$setup["crop"] = "false";
        //img::$setup["width"] = "400";
        //img::$setup["height"] = "400";
        //img::$setup["disable-delete"] = true;
        //img::$setup["path-thumb-copy"] = null;
        //img::$setup["width-thumb-copy"] = "100";
    }
    
    static function defaultCkSettings(){
        $ckSettings = array();
        $ckSettings["removePlugins"] = "tabletools,table,image";
		 //$ckSettings["extraPlugins"] = "link";
        $ckSettings["removeButtons"] = "HorizontalRule,SpecialChar,Anchor,Save,NewPage,Preview,Print,Templates"
		 .",About,Source"
	     .",Find,Replace,SelectAll,Scayt"
	     .",Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField"
.",Strike,CopyFormatting,"
.",,Outdent,Indent,Blockquote,CreateDiv"
.",BidiLtr,BidiRtl,Language,Anchor"
.",Image,Flash,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe"
.",Styles,Format,BGColor,Maximize,ShowBlocks";
//,Cut,Copy,Paste,PasteText,PasteFromWord,,Undo,Redo,RemoveFormat,
//Bold,Italic,Underline,Subscript,Superscript
//NumberedList,BulletedList,JustifyLeft,JustifyCenter,JustifyRight,JustifyBlock,
//Font,FontSize,TextColor,,Link,Unlink,


		 $ckSettings["links"] = "links";
        //$ckSettings["disallowedContent"] = "img script div input [class] [on*] [style]";
        //$ckSettings["allowedContent"] = "img div input [class] [on*] [style]";
        $ckSettings["height"] = "150";
        $ckSettings["toolbar"] = "basic";
        //$ckSettings["toolbarLocation"] = "bottom";
        return $ckSettings;
    }
    
    /**
	 * Compose and send e-mail.
	 *
	 * @param	(string)	$to	 		Receiver, or receivers e-mail(s).
	 * @param	(string)	$subject	Subject of the email to be sent.
	 * @param	(string)	$message	Message content to be sent.
	 * @param	(string)	$from_name	"From" header name.
	 * @param	(string)	$from	 	"From" header e-mail address.
	 * @param	(string)	$return	 	"Return" header e-mail address.
	 * @param	(string)	$cc	 		"CC" header e-mail address.
	 * @param	(string)	$bcc	 	"BCC" header e-mail address.
	 * @return	(boolean)				mail() function return value
	 */
	public static function sendMail($to = '', $subject = '', $message = '', $from_name = '', $from = '', $return = '', $cc = '', $bcc = ''){
		if(empty($to)){
			return false;
		}
        if(LOCAL){
            $subjectOriginal = $subject;
        }
		$subject = '=?utf-8?B?' . base64_encode($subject) . '?=';
		if(!empty($from_name)){
			$from_name = '=?utf-8?B?' . base64_encode($from_name) . '?=';
		}
		$headers = "MIME-Version: 1.0\r\n" .
				   "Content-type: text/html; charset=utf-8\r\n" .
				   "Content-Transfer-Encoding: 8bit\r\n";
		if(!empty($return)){
			$headers .= "Return-Path: <" . $return . ">\r\n";
		}
		if(!empty($from_name) && !empty($from)){
			$headers .= 'From: "' . $from_name . '" <' . $from . ">\r\n";
		} elseif(empty($from_name) && !empty($from)){
			$headers .= "From: <" . $from . ">\r\n";
		}
		if(!empty($cc)){
			$headers .= "Cc: " . $cc . "\r\n";
		}
		if(!empty($bcc)){
			$headers .= "Bcc: " . $bcc . "\r\n";
		}
		//die("GO:".implode('<br />----<br />',$to, $subject, $message, $headers)));
        //cf::vdd(LOCAL,$to, $subject, $message, $headers);
		
		 /*************
        if(LOCAL){
            $filename = 'email' . Date('-Y-m-d-H-i-s') . '-' . rand(1,100000) . '.html';
            $mailfile = APP_LOGS."emails-local-log/".$filename;						;			
			$fh = fopen($mailfile, 'w') or die("can't open file");			
			$stringData = "<!doctype html>\n<html>\n<head><meta charset=\"utf-8\">\n<body>\n";
            $stringData .= "<h4>Subject:</h4><h2>$subjectOriginal</h2>";
            $stringData .= "<p>To: ".htmlentities($to)."</p>";
            $stringData .= "<pre>".htmlentities($headers)."</pre>";
            $stringData .= "<h4>Message:</h4><p>".$message."</p>";
			$stringData .= "\n<body>\n<html>\n";
			fwrite($fh, $stringData);
			fclose($fh);
			?><script language="javascript">
				window.open ("<?= WP. "add/email/" . $filename ?>", "mywindow");
			</script><?php
            return true;
        } else {
			***********/
			//echo 'TO:'.$to.'<br>SUBJ:'.$subject.'<br>MSG:'.$message.'<br>Headers:'.$headers;
		 
		 /*
		 print(
		 "to:" . $to . "\r\n" .
		 "subject:" . $subject . "\r\n" .
		 "message:" . $message . "\r\n" .
		 "headers:" . $headers . "\r\n"
		     
		 );
		 */
		 
		
          $success = mail($to, $subject, $message, $headers);
			if (!$success) {
				//print_r(error_get_last());
			}
		
		   return $success;
        //}
	}

    /**Format a number value for display
     * @param float $val
     * @param int $decimal
     * @param string $dPoint
     * @param string $currency
     * @param string $dTousandsSeprator
     * @return string */
    public static function number($val, $decimal=2, $dPoint=".", $currency = null, $dTousandsSeprator = ","){
        $val = number_format(floatval($val), (int)$decimal, $dPoint, " "); //number format not like &nbsp;
		return ((!is_null($currency) ? $currency : "").str_replace(" ",$dTousandsSeprator,$val));
    }
    
    /**Format a number value as a price
     * uses self::number() default formatting for d point and thousands separator
     * adding self::$currency to the formatted number
     * @param type $val
     * @return string */
    public static function price($val){
        return self::number($val, 2, ".", self::$currency);
    }

    static function numberDecimalCommaToDot($val){
        return (float)(str_replace(",", ".", $val));
    }

    public static function ifNull($val, $defaultVal){
		return is_null($val) ? $defaultVal : $val;
	}
    
    static function visitorBrowserData($url) { 
        $user_data=Array(); 
        $user_data["IP"] = getenv('REMOTE_ADDR');
        if(filter_has_var(INPUT_POST, 'resolution')) {$user_data["SR(px)"] = str_replace(' ','x',  filter_input(INPUT_POST, 'resolution'));}
        $user_data["USER_AGENT"] = filter_input(INPUT_SERVER, "HTTP_USER_AGENT");
        if(isset($url)) {$user_data["SignInURL"] = $url;}
        
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



    static function encrypt($plaintext, $password) {
        $method = "AES-256-CBC";
        $key = hash('sha256', $password, true);
        $iv = openssl_random_pseudo_bytes(16);

        $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
        $hash = hash_hmac('sha256', $ciphertext, $key, true);

        return $iv . $hash . $ciphertext;
    }

    /*
     * return null if $password is wrong
     */
    static function decrypt($ivHashCiphertext, $password) {
        $method = "AES-256-CBC";
        $iv = substr($ivHashCiphertext, 0, 16);
        $hash = substr($ivHashCiphertext, 16, 32);
        $ciphertext = substr($ivHashCiphertext, 48);
        $key = hash('sha256', $password, true);

        if (hash_hmac('sha256', $ciphertext, $key, true) !== $hash) return null;

        $result = openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
        return $result;
    }

    static function getRandomString($length=24) {
        $pull = [];
        while (count($pull) < $length) {
            $pull = array_merge($pull, range(0, 9), range('a', 'z'), range('A', 'Z'));
        }
        shuffle($pull);
        return substr(implode($pull), 0, $length);
    }

    static function outputFile($filesource, $fileName) {
        if (file_exists($filesource)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.$fileName.'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filesource));
            readfile($filesource);
            die();
        }
    }
    
	static function pr($arr){
		echo "<pre>";
		print_r($arr);
		echo "</pre>";
	}
	
	public static function get_ip_address() {
    // check for shared internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && self::validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }

    // check for IPs passing through proxies
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // check if multiple ips exist in var
        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
            $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($iplist as $ip) {
                if (self::validate_ip($ip))
                    return $ip;
            }
        } else {
            if (self::validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED']) && self::validate_ip($_SERVER['HTTP_X_FORWARDED']))
        return $_SERVER['HTTP_X_FORWARDED'];
    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && self::validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && self::validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
        return $_SERVER['HTTP_FORWARDED_FOR'];
    if (!empty($_SERVER['HTTP_FORWARDED']) && self::validate_ip($_SERVER['HTTP_FORWARDED']))
        return $_SERVER['HTTP_FORWARDED'];

    // return unreliable ip since all else failed
    return $_SERVER['REMOTE_ADDR'];
	}

/**
 * Ensures an ip address is both a valid IP and does not fall within
 * a private network range.
 */
	public static function validate_ip($ip) {
		if (strtolower($ip) === 'unknown')
			return false;
		else return true;
		// generate ipv4 network address
		$ip = ip2long($ip);

		// if the ip is set and not equivalent to 255.255.255.255
		if ($ip !== false && $ip !== -1) {
			// make sure to get unsigned long representation of ip
			// due to discrepancies between 32 and 64 bit OSes and
			// signed numbers (ints default to signed in PHP)
			$ip = sprintf('%u', $ip);
			// do private network range checking
			if ($ip >= 0 && $ip <= 50331647) return false;
			if ($ip >= 167772160 && $ip <= 184549375) return false;
			if ($ip >= 2130706432 && $ip <= 2147483647) return false;
			if ($ip >= 2851995648 && $ip <= 2852061183) return false;
			if ($ip >= 2886729728 && $ip <= 2887778303) return false;
			if ($ip >= 3221225984 && $ip <= 3221226239) return false;
			if ($ip >= 3232235520 && $ip <= 3232301055) return false;
			if ($ip >= 4294967040) return false;
		}
		return true;
	}

/**
 * Count the number of working days in year of date
 * and returns working days till date DIVIDED BY  all working 
 * days in this year
 *
 * @param   string  $date    Start date ('YYYY-MM-DD' format)
 * @return  float            Number of working days ('zero' on error)
 *
 */
 
public static function getDaysToAsPercent($date){
	$d=DateTime::createFromFormat('Y-m-d',$date);
	$_d=$d->format('Y');
	$d1=DateTime::createFromFormat('Y-m-d',$_d.'-12-31');
	$total = date("z", $d1->getTimestamp())+1;
	$rest=$total - date('z', strtotime($date))+1;
	$cf=$rest/$total;
	return $cf;
}

/**
 * Count the number of working days between two dates.
 *
 * This function calculate the number of working days between two given dates,
 * taking account of the Public festivities, Easter and Easter Morning days,
 * the day of the Patron Saint (if any) and the working Saturday.
 *
 * @param   string  $date1    Start date ('YYYY-MM-DD' format)
 * @param   string  $date2    Ending date ('YYYY-MM-DD' format)
 * @param   boolean $workSat  TRUE if Saturday is a working day
 * @param   string  $patron   Day of the Patron Saint ('MM-DD' format)
 * @return  integer           Number of working days ('zero' on error)
 *
 */
 
public static function getFederalHolydays($year) {
	$holidays = array(
  $year.'-01-01',
  date("Y-m-d", strtotime("Last Monday of May ". $year)),
  $year.'-07-04',
  date("Y-m-d", strtotime("First Monday of September ".$year)),
  date("Y-m-d", strtotime("fourth Thursday of November ". $year)),
  $year.'-12-25');
  
  return $holidays;
}

public static function getFederalHolidaysAsJS($year) {
	$holidays = self::getFederalHolydays($year);
	foreach ($holidays as $k=>$h){
		$holidays[$k]='"'.val::convDate(MYSQL_DATE,USER_DATE,$h).'"';
	}
  return implode(',',$holidays);
}

public static function getWorkdaysBetween($date1, $date2, $workSat = FALSE, $patron = NULL) {
  if (!defined('SATURDAY')) define('SATURDAY', 6);
  if (!defined('SUNDAY')) define('SUNDAY', 0);

  // Array of all public holydays
  $publicHolidays = array(
  '01-01',
  date("m-d", strtotime("Last Monday of May ". date("Y",strtotime($date1)))),
  '07-04',
  date("m-d", strtotime("First Monday of September ". date("Y",strtotime($date1)))),
  date("m-d", strtotime("fourth Thursday of November  ". date("Y",strtotime($date1)))),
  '12-25');
  

  // The Patron day (if any) is added to public festivities
  if (isset($patron)){
	 foreach($patron as $p){
		$publicHolidays[] = $p;
	 }
  }

 
  /*
   * Array of all Easter Mondays in the given interval
   */
  $yearStart = date('Y', strtotime($date1));
  $yearEnd   = date('Y', strtotime($date2));
/*
  for ($i = $yearStart; $i <= $yearEnd; $i++) {
    $easter = date('Y-m-d', easter_date($i));
    list($y, $m, $g) = explode("-", $easter);
    $monday = mktime(0,0,0, date($m), date($g)+1, date($y));
    $easterMondays[] = $monday;
  }
*/
  $start = strtotime($date1);
  $end   = strtotime($date2);
  $workdays = 0;
  $workdays_arr=array();
  
  
  for ($i = $start; $i <= $end; $i = strtotime("+1 day", $i)) {
    $day = date("w", $i);  // 0=sun, 1=mon, ..., 6=sat
    $mmgg = date('m-d', $i);
    if ($day != SUNDAY &&
      !in_array($mmgg, $publicHolidays) &&
      // !in_array($i, $easterMondays) &&
      !($day == SATURDAY && $workSat == FALSE)) {
        $workdays++;
		
		 $workdays_arr[]=$mmgg;
    }
  }
  return $workdays_arr;//intval($workdays);
}

	public static function  convertImagePNG($originalImage, $outputImage, $quality=5){
		// jpg, png, gif or bmp?
		$exploded = explode('.',$originalImage);
		$ext = $exploded[count($exploded) - 1]; 
		$ext = strtolower($ext);
		if (preg_match('/jpg|jpeg/i',$ext))
			$imageTmp=imagecreatefromjpeg($originalImage);
		else if (preg_match('/png/i',$ext)) {
			//$imageTmp=imagecreatefrompng($originalImage);
			copy($originalImage , $outputImage);
			return 1;
		}
		else if (preg_match('/gif/i',$ext))
			$imageTmp=imagecreatefromgif($originalImage);
		else if (preg_match('/bmp/i',$ext))
			$imageTmp=imagecreatefrombmp($originalImage);
		else
			return 0;

		// quality is a value from 0 (worst) to 100 (best)
		unlink($originalImage);
		imagepng($imageTmp, $outputImage, $quality);
		imagedestroy($imageTmp);

		return 1;
	}
	
	public static function  convertImageJPEG($originalImage, $outputImage, $quality=85){
		// jpg, png, gif or bmp?
		$exploded = explode('.',$originalImage);
		$ext = $exploded[count($exploded) - 1]; 

		if (preg_match('/jpg|jpeg/i',$ext))
			$imageTmp=imagecreatefromjpeg($originalImage);
		else if (preg_match('/png/i',$ext))
			$imageTmp=imagecreatefrompng($originalImage);
		else if (preg_match('/gif/i',$ext))
			$imageTmp=imagecreatefromgif($originalImage);
		else if (preg_match('/bmp/i',$ext))
			$imageTmp=imagecreatefrombmp($originalImage);
		else
			return 0;

		// quality is a value from 0 (worst) to 100 (best)
		unlink($originalImage);
		imagejpeg($imageTmp, $outputImage, $quality);
		imagedestroy($imageTmp);

		return 1;
	}
    
    public static function getPolicy($afID){
            $q="SELECT * FROM `apply_forms` WHERE afID  =".$afID."  AND `afActive` = 'Hired'";
            $res=db::PrepareLoadSingleRow($q);
            
            $dateStart = val::convDate(MYSQL_DATE,'Y-m-d',$res['afStartDate']);
            $dateNow = date('Y-m-d');
            $diff = self::dateDifference($dateStart,$dateNow);
            
            if ($dateNow >= '2021-01-01'){
               if ($diff <  1) return 5;
               if ($diff >= 1 && $diff<3) return  10;
               if ($diff >= 3) return  20 ;

            }
            else return 20;
    }
   

   
    public static function getEmployeePaidDaysOff($year,$afID){
        
        $q="SELECT * FROM `apply_forms` WHERE afID  =".$afID."  AND `afActive` = 'Hired'";
		$res=db::PrepareLoadSingleRow($q);
        //echo '>>>>>>>>><pre>';print_r($res);exit;
        if(isset($res['afVacationDays']) && $res['afVacationDays'] != 0 ) return $res['afVacationDays'];
       // if(!isset($res['afVacationDaysLastChanged'])) {
        if (
            ! isset($res['afVacationDaysLastChanged'])  
            || $res['afVacationDaysLastChanged'] == '0000-00-00' 
            ) { 
                //self::getEmployeePastPaidDaysOff($year,$afID);
            }
            else {
                return $res['afVacationDays'];
            }
       // }
        $lastChanged = $res['afVacationDaysLastChanged'];
        
        if($lastChanged == '0000-00-00' || empty($lastChanged) || (!empty($lastChanged) && 2021 <= val::convDate(MYSQL_DATE,'Y',$lastChanged)) )
		if ($res !== false){
            $dateStart = val::convDate(MYSQL_DATE,'Y-m-d',$res['afStartDate']);
            $dateNow = $year.'-'.date('m-d');
            $diff = self::dateDifference($dateStart,$dateNow);
            //echo '>>>>'.$year.' '.$dateStart.' NOW:'.$dateNow.' '.$diff.'<br>';
            if ($year >= '2021'){ 
               if ($diff <  1) return 5;
               if ($diff >= 1 && $diff< 3) return  10;
               if ($diff >= 3) return  20 ;

            }
            else return 20;
        }
        else return $res['afVacationDays'];
    }
    
    public static function  getDaysBetween2Dates($date_1, $date_2, $absolute = true)
    {
        $date1 = date_create($date_1);
        $date2 = date_create($date_2);
        $interval = $date2->diff($date1);
        // if we have to take in account the relative position (!$absolute) and the relative position is negative,
        // we return negatif value otherwise, we return the absolute value
        return (!$absolute and $interval->invert) ? - $interval->days : $interval->days;
    }

   public static function dateDifference($date_1 , $date_2 , $differenceFormat = '%y %d' )
    { 
        $y = cf::getDaysBetween2Dates($date_1 , $date_2);
        return $y/365;
        /*********
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);
        $interval = date_diff($datetime1, $datetime2);
        var_dump($interval);      
        return $interval->format($differenceFormat)/365;
        ******/
    }
    
   public static function getEmployeePastPaidDaysOff($year,$afID){
        $q="SELECT pdpyDays,pdpyYear,pdpyDateChanged FROM `paiddayspastyears` 
        WHERE pdpyAFID =".$afID."  AND `pdpyYear` = '{$year}'  ORDER BY pdpyDateChanged DESC";
		$res=db::PrepareLoadSingleRow($q);

        $q="SELECT pdpyDays,pdpyYear,pdpyDateChanged FROM `paiddayspastyears` 
        WHERE pdpyAFID =".$afID."  ORDER BY pdpyDateChanged DESC";
		$res1=db::PrepareLoadSingleRow($q);
        
        if(is_array($res)) {return $res['pdpyDays'];}
        if(is_array($res1)){return $res1['pdpyDays'];}
        if(!is_array($res) && !is_array($res1)) {
            if ($year < 2021) 
                return 20;
            else {
                 return self::getEmployeePaidDaysOff($year,$afID);                
            }
        }
   }
}
