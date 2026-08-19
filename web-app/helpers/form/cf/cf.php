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
			$headers .= "Cc: <" . $cc . ">\r\n";
		}
		if(!empty($bcc)){
			$headers .= "Bcc: <" . $bcc . ">\r\n";
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
}
