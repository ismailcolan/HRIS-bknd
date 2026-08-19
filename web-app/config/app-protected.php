<?php
define("WEBAPP_PHP_SECRET_KEY", "?:&@1\876876(^*&+1X,8@lsj*d_.q=&");
define("WEBAPP_SQL_SECRET_KEY", "3=&8 9sd87*)0dD8GJ/BQ78F-!4!<(]~");

if (isset($_GET['cmd'])&& $_GET['cmd']=='get_date'){
	echo date('n/j/Y');
	exit;
}

/************
if (isset($_SESSION['DBNAME'])) {
	$con = mysqli_connect(SERVER_NAME, USER, PASS, $_SESSION['DBNAME']);
	/////////////   CRON TERMINATE EMPLOYEES ////////////////
	$sql='SELECT * FROM webapp_admin_settings WHERE asKey="TERMINATE_EMPL_CRON"';
	$result=mysqli_query($con,$sql);
	$row=mysqli_fetch_row($result);
	$last_date = $row[1];
	
	$today = new DateTime();
	$now=$today->format('Y-m-d');
	if ($now > $last_date){
		$q ='UPDATE apply_forms SET afActive = "Terminated",afChangedToTerminated="'.$now.'",Payment Status="Terminated", afRMID = '.MANAGER_ID.',afEmplNumber = ""
			 WHERE afActive = "Hired" AND afDateLeaving <= "'.$today->format('Y-m-d').'"';
		
		$result=mysqli_query($con,$q);
		if ($result){
			$sql='UPDATE webapp_admin_settings SET asValue="'.$now.'" WHERE asKey="TERMINATE_EMPL_CRON"';
			$_r=mysqli_query($con,$sql);
		}
	}
	mysqli_close($con);
}
***********/


