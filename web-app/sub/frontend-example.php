<?php

// KEEP THIS CODE HERE. DO NOT CHANGE
include_once("./../web-app/boot/app-boot.php");//  this is all you need

if (!isset($_SESSION['webapp-login-token'])) header('Location:/sign-out');

$con=mysqli_connect(SERVER_NAME,USER,PASS,DBNAME);
$con->set_charset("utf8");

if (mysqli_connect_errno()){  echo "Failed to connect to MySQL: " . mysqli_connect_error();}

echo $_SESSION['front_header'];

// YOUR CODE STARTS HERE
echo '
<div style="text-align:center;">HELLO FROM sub folder FRONT-PART <br>';
$sql="SELECT * FROM  apply_forms WHERE afID>1";

$result = $con->query($sql);

echo '<table class="upl_files_table" style="width:50%;margin: 0 auto;">
<tr><th>ID</th><th>First Name</th><th>Last Name}</th><th>Active</th></tr>';
while ($a=$result->fetch_array()) {
	echo "<tr><td>{$a['afID']}</td><td>{$a['afFirstName']}</td><td>{$a['afLastName']}</td><td>{$a['afActive']}</td></tr>";
}
echo '</table></div>';


// YOUR CODE ENDS HERE. KEEP THE LINE BELOW, DO NOT CHANGE
include_once('../web-app/webapp-html/stop(footer).html');