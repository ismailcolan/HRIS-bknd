<?php
/** The main app config file */

/*
$host = filter_input(INPUT_SERVER,"HTTP_HOST");
if (!isset($host))
{
    $host = "develop.metavisia.com";
}
*/
//echo __DIR__;
//include_once ("/../boot/app-paths.php");
session_start();

//phpinfo();exit;

//Muz
define('HRIS_V2', false);

$DevelopServer = "/home/developmv";
$RealServer = "/home/humanroadferndaleroadcom";

if (substr($_SERVER["SCRIPT_FILENAME"], 0, strlen($DevelopServer)) == $DevelopServer)
{
     $host = "develop.metavisia.com";
	 define("FS_ROOT","/home/developmv/public_html/");
	 define("SELERIX_ROOT","/");
}
else
{
		
    if (substr($_SERVER["SCRIPT_FILENAME"], 0, strlen($RealServer)) == $RealServer)
    {
        $host = "humanroad.ferndaleroad.com";
		define("FS_ROOT","/home/humanroadferndaleroadcom/public_html/");
		define("SELERIX_ROOT","/");
    }
    else
    {
        $host = "hris.me";
		define("FS_ROOT","D:/xampp/htdocs/hris/");
		define("SELERIX_ROOT","/");
    }
}

    // DEBUG - check whether $host set correctly
// file_put_contents ("server.txt", $host);

/*
    // DEBUG - write server variables into text
$file = '';
foreach ($_SERVER as $key=>$value) {$file.= $key . ":" . $value ."\r\n";}
file_put_contents ("server.txt", $file);
*/

// $date = DateTime::createFromFormat('n/j/Y h:i a', '12/12/2022 08:11 pm');echo $date->format('Y-m-d H:i:s');
/*********************
DEFINE("TCP_server", "66.109.18.214");
DEFINE("TCP_database", "TimeClockPlus70");
DEFINE("TCP_username", "gzlatev");
DEFINE("TCP_password", "ojzx62Ws&3!qq");

DEFINE("FTP_server","xmit.boonchapman.com");//"hris.maidenlanemedical.com");
DEFINE("FTP_username","maidenlane_selerix");//"hrismaidenlane");
DEFINE("FTP_password", "bumpyB!ke16");//"qze00d.drz");
DEFINE("FTP_port",22);//1022);
DEFINE("FTP_upload", SELERIX_ROOT."Upload/");//"home/hrismaidenlane/Upload/")
DEFINE("FTP_download", SELERIX_ROOT."Download/");//"home/hrismaidenlane/Download/")
DEFINE("FTP_protocol", "SFTP");
define("FTP_file_download","_Selerix_DeductionExportTemplate_.xlsx");
if (!isset($_SESSION['customerID'])) $_SESSION['customerID']='';
define("REMOUTE_UPLOAD_DIR","/Upload/");
define("LOCAL_UPLOAD_DIR",  FS_ROOT."add/customer".$_SESSION['customerID']."/uploads/for_upload/benefits_uploaded/");//'/home/developmv/public_html/add/uploads/for_upload/benefits_uploaded/';
define("LOCAL_DOWNLOAD_DIR",FS_ROOT."add/customer".$_SESSION['customerID']."/uploads/downloads/benefits_downloaded/");//"/home/developmv/public_html/add/uploads/downloads/benefits_downloaded/");

define("EMAIL_MAILER_NAME", "Human Resources, Human Road LLC");
define("EMAIL_MAILER_ADDRESS", "mailer@humanroad.ferndaleroad.com");//"mailer@hris.maidenlanemedical.com");



************************/

if ($host=="hris.me")  {
    define("LOCAL", true);
	
	/*****/
	error_reporting(E_ALL);
    ini_set('display_errors', E_ALL);
    ini_set("error_log", "php.error.log");

	/****
	error_reporting(0);
    ini_set('display_errors',0);
    ***/
	
	define("DB_PREFIX", "");
	define("SERVER_NAME", "localhost");
	define("DBNAME", "real");//"developm_hrisdev");// real_hrisdevelopm_hrisdev//"developm_hris7");
	define("USER", "humanroadferndaleroadcom_userb1");//"hrismaid_userb1");
	define("PASS", "");//"glreUe30&nkr");
	define("PROTOCOL", "http://");
	//define("FP","D:/xampp/htdocs/hris/");
} 

if ($host=="develop.metavisia.com") {
    define("LOCAL", false);
    // error_reporting(0);
    //ini_set('display_errors',0);
	
	 error_reporting(E_ALL|E_STRICT);
    ini_set('display_errors', E_ERROR | E_WARNING | E_PARSE |  E_STRICT | E_DEPRECATED | E_USER_DEPRECATED);
    ini_set("error_log", "php.error.log");
    
	define("DB_PREFIX", "developmv_");
	define("SERVER_NAME", "localhost");
	define("DBNAME", "developmv_hris");
	define("USER", "developmv");
	define("PASS", "qze00d.drz");
	define("PROTOCOL", "https://");
	//define("FP","/home/developvm/public_html/");
}

if ($host=="humanroad.ferndaleroad.com") {
    define("LOCAL", false);

	ini_set('error_reporting', E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', $_SERVER["DOCUMENT_ROOT"] . '/errors.log');

/*******
 error_reporting(E_ALL|E_STRICT);
    ini_set('display_errors', E_ALL);
    ini_set("error_log", "php.error.log");
********/
  
    define("DB_PREFIX", "humanroadferndaleroadcom_");//hrismaid_
    define("SERVER_NAME", "localhost");
	define("DBNAME", "humanroadferndaleroadcom_dbhris");
	define("USER", "humanroadferndaleroadcom_userb1");
	define("PASS", "Hris-1-userr");
	define("PROTOCOL", "https://");
	//define("FP","/home/hrismaidenlane/public_html/");
	
}
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
//phpinfo();exit;

//echo SERVER_NAME.'======='.USER.'========='. "rt".PASS."0e".'==========='.DBNAME;
$con = mysqli_connect(SERVER_NAME, USER, PASS, DBNAME);
$sql='SELECT * FROM webapp_admin_settings WHERE asKey="ADMIN_MAILS"';
$result=mysqli_query($con,$sql);
$row=mysqli_fetch_row($result);
$m=$row[1];
$mails = explode("\n",$m);
$admin_mails='';
$_mails=array();
foreach ($mails as $mail){
	$mail = trim($mail);
	if ($mail != '') $_mails[]=$mail;
}
mysqli_close($con);

define("EMAIL_ADMIN",implode(',',$_mails));

if (!defined('LOCAL')) {echo 'Not defined host';exit;}


define("VER","2019-11-22");
define("WP", PROTOCOL.$host."/");
//define("FP", PROTOCOL.$host."/");
define("ADMIN_TABLES_ROWS_PER_PAGE", 20);
define("MANAGER_ID", 0);
define("MANAGER_NAME", "HR Manager");
define("EMPLOYEE_THEMSELF_ID", 1);
define("EMPLOYEE_THEMSELF_NAME", "Employee");// Himself / Herself
define("EMPLOYEE_THEMSELF_NAME_FRONT",'You');
define("EMPLOYEE_THEMSELF_NAME_FRONT_SHORT",'You');// must contain same word as EMPLOYEE_THEMSELF_NAME_FRONT

define('MY_DIRECTORY_SEPARATOR','/');



define("ADD", WP . "add".MY_DIRECTORY_SEPARATOR);
define("ADD_IMG", WP . "add".MY_DIRECTORY_SEPARATOR."images".MY_DIRECTORY_SEPARATOR);
define("ADD_CSS", WP . "add".MY_DIRECTORY_SEPARATOR."cssjs".MY_DIRECTORY_SEPARATOR);
define("ADD_JS", WP . "add".MY_DIRECTORY_SEPARATOR."cssjs".MY_DIRECTORY_SEPARATOR);
define("TMP","web-app".MY_DIRECTORY_SEPARATOR."webapp-tmp".MY_DIRECTORY_SEPARATOR);
define("LOGS","web-app".MY_DIRECTORY_SEPARATOR."webapp-logs".MY_DIRECTORY_SEPARATOR);
define('DEFAULT_IMAGE_UPLOAD_SCRIPT',  ADD . "helpers/image/ImageUploadHandler.php");
//define('SUBTEMPLATES_DIR',FP.'sub/templates/');
define("ADMIN_ADVANCED_FUNCS_ALLOWED", (LOCAL ? true : false));

if (!isset($_SESSION['customerID'])) $_SESSION['customerID']=0;
$cust =$_SESSION['customerID'];
define("_IMG_PATH","add/customer{$cust}/form-photo/");
define("_BIO_PATH","add/customer{$cust}/uploads/");


define('WP_IMAGES', WP . 'images'.MY_DIRECTORY_SEPARATOR);
define('FP_IMAGES', FP . 'images'.MY_DIRECTORY_SEPARATOR);

define('IMAGES_DIR', FP . 'add/customer'.$_SESSION['customerID'].MY_DIRECTORY_SEPARATOR);
define('UPLOADS_DIR', FP . 'add/customer'.$_SESSION['customerID'].'/uploads'.MY_DIRECTORY_SEPARATOR);
define('UPLOADS_URL', WP . 'add/customer'.$_SESSION['customerID'].'/uploads'.MY_DIRECTORY_SEPARATOR);

define('CREDENTIALING_FIELD_NAME', 'credentialingFile');
define('CREDENTIALING_UPLOAD_DIR', FP . 'add'.MY_DIRECTORY_SEPARATOR.'customer'.$_SESSION['customerID'].'/uploads'.MY_DIRECTORY_SEPARATOR.'credentialings'.MY_DIRECTORY_SEPARATOR);
define('CREDENTIALING_MAX_FILE_SIZE', 50485760);
define('CREDENTIALING_EXT_ALLOWED', 'pdf,doc,docx,xls,xlsx,jpg,png');
define('CREDENTIALING_FILE_NAME', 'credentialing_');
define("MIN_EMP_PER_POSITION", 0);

define("DEBUG", LOCAL);



define("SERVER_TIMEZONE", "Europe/Sofia");
define("USER_TIMEZONE", 'America/New_York');



date_default_timezone_set(USER_TIMEZONE);


if(!function_exists("array_column")) {
    function array_column($arr, $colName) {
        $result = [];
        foreach ($arr as $item) {
            $result[] = $item[$colName];
        }
        return $result;
    }
}


$_US_STATES =
array(
  'AL' => 'Alabama',
  'AK' => 'Alaska',
  'AZ' => 'Arizona',
  'AR' => 'Arkansas',
  'CA' => 'California',
  'CO' => 'Colorado',
  'CT' => 'Connecticut',
  'DE' => 'Delaware',
  'DC' => 'District Of Columbia',
  'FL' => 'Florida',
  'GA' => 'Georgia',
  'HI' => 'Hawaii',
  'ID' => 'Idaho',
  'IL' => 'Illinois',
  'IN' => 'Indiana',
  'IA' => 'Iowa',
  'KS' => 'Kansas',
  'KY' => 'Kentucky',
  'LA' => 'Louisiana',
  'ME' => 'Maine',
  'MD' => 'Maryland',
  'MA' => 'Massachusetts',
  'MI' => 'Michigan',
  'MN' => 'Minnesota',
  'MS' => 'Mississippi',
  'MO' => 'Missouri',
  'MT' => 'Montana',
  'NE' => 'Nebraska',
  'NV' => 'Nevada',
  'NH' => 'New Hampshire',
  'NJ' => 'New Jersey',
  'NM' => 'New Mexico',
  'NY' => 'New York',
  'NC' => 'North Carolina',
  'ND' => 'North Dakota',
  'OH' => 'Ohio',
  'OK' => 'Oklahoma',
  'OR' => 'Oregon',
  'PA' => 'Pennsylvania',
  'RI' => 'Rhode Island',
  'SC' => 'South Carolina',
  'SD' => 'South Dakota',
  'TN' => 'Tennessee',
  'TX' => 'Texas',
  'UT' => 'Utah',
  'VT' => 'Vermont',
  'VA' => 'Virginia',
  'WA' => 'Washington',
  'WV' => 'West Virginia',
  'WI' => 'Wisconsin',
  'WY' => 'Wyoming',
);  

$_RELS=array("spouse"=>'spouse', "parent"=>'parent',"child"=>'child',"friend"=>'friend','other'=>'other');
$_GENDER=array('male'=>'Male','female'=>'Female');
$_M_STATUS = array("Single"=>'Single', "Married"=>'Married',"Divorced"=>'Divorced',"Widowed"=>'Widowed');
$_TEMPL_ALLOWED=array('[FirstName]','[LastName]','[StartDate]',
				'[Position]','[ManagerName]','[CompanyName]','[Office]');
$_TEMPL_ALLOWED_ADDON = array('[CreatePaswordLink]');

define("ADMIN",WP."admin/");
define("SUPERADMIN",WP."superadmin/");
define("ADMIN_URL","humanroad.ferndaleroad.com");

if (!isset( $GLOBALS['SYSTEM_MAILS_SIGNATURE']))  $GLOBALS['SYSTEM_MAILS_SIGNATURE']="Regards,\nHuman Road Team";

define("MAIL_FOOTER", $GLOBALS['SYSTEM_MAILS_SIGNATURE']);
define("MAIL_FOOTER_HTML", nl2br($GLOBALS['SYSTEM_MAILS_SIGNATURE']));

define("MAIL_PASSWORD_SUBJ","Password recovery");
define("MAIL_PASSWORD_MSG", "You've requested a password recovery. Please follow the link below to set a new password:\n\n{--Recovery-lnk--}".MAIL_FOOTER);

define("MAIL_PASSWORD_CREATE_SUBJ", "Please create a password");
define("MAIL_PASSWORD_CREATE_MSG", "Dear %s %s,\n\nWe're happy to inform you that you've been hired in our company!\nPlease follow this link to create a password to login to your account:\n\n\n%s\n\n\nIf you have any problems accessing the link or logging in to your account, please contact us.\n\nWelcome to our team!".MAIL_FOOTER);

define("MAIL_NEW_APPLICATION_SUBJ", "New application");
define("MAIL_NEW_APPLICATION_MSG", "New Application received in Human RoadRIS.\n\nPosition: %s\nFirst Name: %s\nLast Name: %s\n\n<a href='".ADMIN."applications'>Login to HRIS to view >></a>");

define("MAIL_REJECTED_APPLICATION_SUBJ", "Application rejected");
define("MAIL_REJECTED_APPLICATION_MSG", "Dear %s %s,\n\nUnfortunately your application has been rejected by our team. Thank you for your application.".MAIL_FOOTER);

 
    // ------------------------------------------------------------------------------------------------------------------------------------------------------
    // This function removes duplicate not-uploaded credentialings
    // that is not uploaded credentialings which have due date in the last or within the next 90 days
    // then the logic below creates a string with duplicates to remove - those are all duplicates except the newest one
    // e.g. user has to upload 2 sexual harassment credentialing - earlier this year and now; in this case
    // the system will generate a string to remove the first one (the one  with earlier due date)

function SkipNotUploadedDuplicateCredentials($CredentialsList)
{
    
    $NotUploadedDuplicatesToSkip = "";
    
        // setup variable $d to be = today +90 days
    $d = new DateTime('now');
    $d->modify('+90 day');
    $d = $d->format('Y-m-d 00:00:00');
    
    
    $arr = []; //  array of strings with ecAFID, ecCID, ecDueDate and ecID coded
        
        // generate string used to determine duplicates
    foreach ($CredentialsList as $SingleRow)
    {
        //print_r($SingleRow);
        
            
        
            // generate string array in format: ecAFID&ecCID&ecDueDate&ecID with leading zeroes i.e. fixed length
        if ($SingleRow["ecDueDate"] < $d)
        {
            // print_r($SingleRow);
            $s = sprintf("%'010d", $SingleRow["ecAFID"]) . "&";
            $s .= sprintf("%'010d", $SingleRow["ecCID"]) . "&";
            $s .= $SingleRow["ecDueDate"] . "&";
            $s .= sprintf("%'010d", $SingleRow["ecID"]);
            array_push($arr, $s);
        }
        
            // sort the array
            // this way credentialings for the same ecAFID and ecCID (i.e. same user and same credentialing)
            // will go as subsequent items in the array
        sort ($arr, SORT_STRING);
        
        //print_r($arr);
        //echo "<br>";

    }
    
    
        // check each array item with the next one;
        // if first 21 characters the same (i.e. same user and credentialing)
        // them add that item to the list to remove
    if (count($arr) > 1)
    {
        for ($i = 0; $i < count($arr) - 1; $i++)
        {
            if (substr($arr[$i], 0, 21) == substr($arr[$i + 1], 0, 21))
            {
                $NewItemToRemove = (int)(substr($arr[$i], strlen($arr[$i]) - 10, 10));  // remove leading zeroes by converting to integer
                $NotUploadedDuplicatesToSkip .=  "-" . $NewItemToRemove . "-";
                
            }
        }
    }
        
    //echo "To skip:" . $NotUploadedDuplicatesToSkip;
    return $NotUploadedDuplicatesToSkip;
    
}   // END SkipNotUploadedDuplicateCredentials

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

    // EXPLAINING FUNCTION SkipUloadedCredentials
    // When creating a new emplooyee, all expected credentialings are created as records in the employee_credentialing table but with NULL ecFileName
    // The existence of that record tells the system that a credentialing is to be uploaded
    // When the employee uploads a credentialing, creates a new record in that table BUT - the system keeps the old NULL record too
    // Since the original NULL record remains, the still system thinks there's no file uploaded although there is
    // That's why I've created a function to determine for which credentialings there are both NULL and not-NULL (i.e. aready uploaded file)
    // and if such records are found, the NULL record is ignored (removed from the array used to display credentialins) thus fixing the problem
    // But this logic must be applied only to ONE-TIME credentialings.


$CredentialingsToBeSkippedBecauseUploaded = array();

function SkipUloadedCredentials ($UnprocessedRecords)
{
    
        // generate string used to determine duplicates
    foreach ($UnprocessedRecords as $SingleRow)
    {
        //print_r($SingleRow);
        
            // this IF below is NOT part of the logic to remove duplicate non-uploaded
            
        if ($SingleRow["cType"] == "one-time") // the logic must be applied only to ONE-TIME credentialings
        {
            array_push($ToBeSorted, sprintf('%0' . $PREF_LEN . 'd', $SingleRow["ecCID"]) . ":" . sprintf('%0' . $PREF_LEN . 'd', $SingleRow["ecID"]) . ":" . $SingleRow["ecFileName"]);
        }
    }
    
    $ToBeSorted = array ();
    $PREF_LEN = 10; // Prefix fixed length

    foreach ($UnprocessedRecords as $SingleRow)
    {
        // print_r($SingleRow);
            
        if ($SingleRow["cType"] == "one-time") // the logic must be applied only to ONE-TIME credentialings
        {
            array_push($ToBeSorted, sprintf('%0' . $PREF_LEN . 'd', $SingleRow["ecCID"]) . ":" . sprintf('%0' . $PREF_LEN . 'd', $SingleRow["ecID"]) . ":" . $SingleRow["ecFileName"]);
        }
    }
    asort($ToBeSorted);
    //print_r ($ToBeSorted);
    
        // PreviousPrefix and CurrentPrefix are previous and current record's ecCID and ecID
    $RecordsToRemove = "";
    $PreviousPrefix = "";
    $CurrentPrefix = "";
    
    foreach ($ToBeSorted as $SingleRow)
    {
        //print ($SingleRow . "<br>");
         

            // If same as previous ecID, i.e. it's the same credentialing
            // and the string length = Prefix length i.e. no FileName (NULL)
            // then add the ecCID into the variable $RecordsToRemove
        
        $CurrentPrefix = substr($SingleRow, 0, $PREF_LEN + 1);
        //print ("CURRENT PREFIX:" . $CurrentPrefix . "<br>");
            
            // if same as previous prefix AND length = length of prefixes, i.e. no filename after that i.e. filename NULL
         
        // print ("CURR:" . $CurrentPrefix . ", PREV: " . $PreviousPrefix . ", LENGTH: " .  strlen($SingleRow) * 2 . "<br>");   
        if (($CurrentPrefix == $PreviousPrefix) && (strlen($SingleRow) == strlen($CurrentPrefix) * 2))
        {
            $ToRemove = substr($SingleRow, $PREF_LEN + 1, $PREF_LEN) . ","; // only SECOND half of the prefix is needed - ecID
            $ToRemoveAsNumber = 0 + $ToRemove;
            $RecordsToRemove .= "-" . $ToRemoveAsNumber . "-";
            // print ("TO REMOVE: " . $RecordsToRemove . "<br>");
        }
    
        $PreviousPrefix = $CurrentPrefix;
    }
    
    global $CREDENTIALINGS_TO_SKIP_BECAUSE_UPLOADED;
    $CREDENTIALINGS_TO_SKIP_BECAUSE_UPLOADED = $RecordsToRemove;
    //print ("TO BE SKIPPED: " . $CREDENTIALINGS_TO_SKIP_BECAUSE_UPLOADED);
    
    //print ("TO REMOVE: " . $RecordsToRemove);
    
        // Don't show credentialings expiring in more than 90 days
    $Expiration = new DateTime('now');
    $Expiration->modify('+90 day');
    $Expiration = $Expiration->format('Y-m-d h:i:s');
    
    $NotUploadedDuplicatesToSkip = SkipNotUploadedDuplicateCredentials($UnprocessedRecords);
    //$NotUploadedDuplicatesToSkip = "";
    
    $ProcessedArray = array();
    foreach ($UnprocessedRecords as $SingleRow)
    {
        if
            (
            (strpos ($RecordsToRemove, "-" . $SingleRow ["ecID"] . "-") !== false)
            || 
            ($SingleRow["ecExpiredDate"] > $Expiration)
            ||
            (strpos ($NotUploadedDuplicatesToSkip, "-" . $SingleRow ["ecID"] . "-") !== false)
            )
        // ignore those in $RecordsToRemove plus all expiring in more than 90 days
        
        {
            //print ("<br>MUST REMOVE " . $SingleRow ["ecID"]);
        }
        else
        {
            
                
            $SingleRow["cName"] = html_entity_decode($SingleRow["cName"]);
            array_push($ProcessedArray, $SingleRow);
        }
    }
    
    //foreach ($UnprocessedRecords as $SingleRow) {print($SingleRow["ecID"] . "<br>");}
    //print("<br>DONE:");
    //foreach ($ProcessedArray as $SingleRow) {print($SingleRow["ecID"] . "<br>");}
    
    return ($ProcessedArray);
    
    
} // END function SkipUloadedCredentials()
// --------------------------------------------------------------------------------------------------------------------
