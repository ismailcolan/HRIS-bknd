<?php
// KEEP THIS CODE HERE, DO NOT CHANGE
include_once("./../../web-app/boot/app-boot.php");

if (!isset($_SESSION['webapp-admin-token'])) header('Location:'.WP.'/admin/logout');

$con=mysqli_connect(SERVER_NAME,USER,PASS,DBNAME);
$con->set_charset("utf8");

if (mysqli_connect_errno()){  echo "Failed to connect to MySQL: " . mysqli_connect_error();}

echo $_SESSION['admin_header'];


// YOUR CODE STARTS HERE
echo '
<div style="text-align:center;">HELLO FROM sub folder ADMIN-PART <br>';
$sql="SELECT * FROM  apply_forms WHERE afID>1";

$result = $con->query($sql);

echo '<table class="upl_files_table" style="width:50%;margin: 0 auto;">
<tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Active</th></tr>';
while ($a=$result->fetch_array()) {
	echo "<tr><td>{$a['afID']}</td><td>{$a['afFirstName']}</td><td>{$a['afLastName']}</td><td>{$a['afActive']}</td></tr>";
}
echo '</table></div>
</div>';

// YOUR CODE ENDS HERE. KEEP THE BELOW CODE - DO NOT CHANGE
include_once('../../web-app/webapp-html/admin/webpage--stop(footer).html');