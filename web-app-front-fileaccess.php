<?php
//session is autostarted

include_once('./web-app/config/app-config.php'); 
$MYHOST=WP;

if(!strip_tags($_GET['file']) || !isset($_GET['file']) || $_GET['file'] == "" ){
	// If they tried to access this file directly, send them elsewhere
	header("Location:".$MYHOST);
}


$file=strip_tags($_GET['file']);
$mode='download';
if (strpos($file,'(view)')) {$file=str_replace('(view)','',$file); $mode='view';}


$authed = FALSE;
if(isset($_SESSION['webapp-admin-token']) ||  isset($_SESSION['webapp-login-token'])){
    $authed = TRUE;
} 

if(! $authed ){ header("Location:".$MYHOST);}
else  {
	$filePath='add/customer'.$_SESSION['customerID'].'/uploads/'.$file;
	//echo '>>>>'.$filePath.'<br>'.$mode;exit;
	$filename = basename($filePath); 
	if (file_exists($filePath)){
		if ($mode=='view') { 
			//echo 'View '.$mode;;exit;
			header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
			header("Cache-Control: public");
			header("Content-Type: application/pdf");
			header('Content-Disposition: inline; filename="'.$filename.'"');
			readfile($filePath);
		}
		else{
			//echo 'Download '.$mode;exit;
			header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
			header("Cache-Control: public");
			header("Content-Type: application/binary");
			header("Content-Transfer-Encoding: Binary");
			header("Content-Length:".filesize($filePath));
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			readfile($filePath);
		}
	}
	else
	 header("Location:".$MYHOST);
}