<?php
ini_set('memory_limit', '-1');
date_default_timezone_set('America/New_York');//initiate timezone//
//include("/home/developmv/public_html/web-app/config/app-config.php");
include("web-app/config/app-config.php");


$con1 = mysqli_connect(SERVER_NAME, USER, PASS, DB_PREFIX.'main');
$q="SELECT * FROM customers WHERE cActive='yes'";
$__result = mysqli_query($con1,$q);
mysqli_close($con1);
//===========================================
while ( $r__ = mysqli_fetch_assoc($__result)) {

echo '<hr>DB:'.DB_PREFIX.$r__['cDBASE'].'<br>';
$mysqlcon = mysqli_connect(SERVER_NAME.":3306",USER,PASS,DB_PREFIX.$r__['cDBASE']);

$q = "SELECT * FROM webapp_admin_settings";
$result = mysqli_query($mysqlcon,$q);
while($__r=mysqli_fetch_assoc($result)){
	$GLOBALS[$__r['asKey']]= $__r['asValue'];
}



//code to get date time of last update start here//
$currentdate=date('Y-m-d H:i:s');
echo '<br>DATE NOW:'.$currentdate;
$mysqllu="SELECT crLastRun FROM cron Where `crName`='timesclock_whoisin'";
$resultlu = mysqli_query($mysqlcon, $mysqllu);
$rowlu = mysqli_fetch_array($resultlu);
$lastupdate=$rowlu["crLastRun"];
$datetime1 = new DateTime($rowlu["crLastRun"]);
$datetime2 = new DateTime($currentdate);
$interval = $datetime1->diff($datetime2);
//echo "<br>";
$diftime=$interval->format('%i');
//code to get date time of last update end here//   

//if($diftime>=4) //<<<<<<
if (true)
{

    $updatelu="UPDATE `cron` SET `crLastRun`='".date('Y-m-d H:i:s')."' WHERE `crName`='timesclock_whoisin'";
    mysqli_query($mysqlcon, $updatelu);
    
echo '<BR>GO TO EXECUTE'    ;
    

$TwentyHourEarlierTime=$date = date("Y-m-d H:i:s", strtotime('-20 hours', time()));
$OneHourLater=$date = date("Y-m-d H:i:s", strtotime('+1 hours', time()));
//echo '<pre>';print_r($GLOBALS);exit;
$stingofvaliemployenumbers="";
$sql = "SELECT TimeIn, TimeOut, tcp_employee.employee.EmployeeId as EmployeNumber FROM tcp_employeework.worksegment, tcp_employee.employee Where (tcp_employee.employee.RecordId = tcp_employeework.worksegment.EmployeeRecordId) AND  ((TimeIn >= '".$TwentyHourEarlierTime."')) and  ((TimeIn <= '".$OneHourLater."')) and (TimeOut IS NULL) Order BY tcp_employee.employee.RecordId";
$conn = mssql_connect( $GLOBALS['TCP_server'], $GLOBALS['TCP_username'], $GLOBALS['TCP_password']);
$selected = mssql_select_db($GLOBALS['TCP_database'], $conn);

$stmt = mssql_query($sql, $conn);

while( $row = mssql_fetch_array( $stmt) )

        {
          //$timein=date_create($row['TimeIn']);
          //$finaltimein=date_format($timein,"Y-m-d H:i:s");

          //$timeout=date_create($row['TimeOut']);
          //if($row['TimeOut']!=""){ $finaltimeout=date_format($timeout,"Y-m-d H:i:s"); }else { $finaltimeout=""; }
          $stingofvaliemployenumbers.=$row["EmployeNumber"];
          $stingofvaliemployenumbers.=", ";

        }

        $EmployesPresentIn=substr($stingofvaliemployenumbers, 0, -2);

        $UpdateQuery="UPDATE `apply_forms` SET `afIn`= 0";
        mysqli_query($mysqlcon, $UpdateQuery);

        $UpdateEmployeeInQuery="UPDATE `apply_forms` SET `afIn`= 1 Where afEmplNumber IN ($EmployesPresentIn)";
        mysqli_query($mysqlcon, $UpdateEmployeeInQuery);
    
}//lastupdate condition end here// 
else echo '<BR>TOO EARLY TO EXECUTE' ;  
}
echo '<br>DONE<hr>';
 ?>
