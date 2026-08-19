<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("RoleHelper.php");
// if (isset($_GET['phptest'])) {
//     echo "PHP OK";
//     echo "<br>RoleHelper exists: " . (class_exists('RoleHelper') ? 'YES' : 'NO');
//     exit;
// }
require_once( dirname(__FILE__) . "/app-paths.php");



require_once( APP_CONF . "app-config.php");



require_once( APP_CONF . "app-protected.php");



require_once( APP_CONF . "app-resources.php");



require_once(APP_BOOT . "autoload.php");



require_once(APP_CONF . "variables.php");



require_once(APP_CONF . "variables_p.php");




// function getUserRole() {
//     if (isset($_SESSION['role'])) {
//         return $_SESSION['role'];
//     } else {
//         return 'admin';
//     }
// }

// if (isset($_GET['setpass'])) {

//     $user_id = 14; 
//     $password = "issu123"; // your desired password

//     // ✅ MUST MATCH USERNAME
//     $salt = "ismail"; 

//     $token = cf::hashTokenByPassAndSalt($password, $salt);

//     $q = "UPDATE webapp_admins_tokens 
//     SET 
//         waatSalt = '" . db::EscapeStr($salt, true) . "',
//         waatToken = AES_ENCRYPT('" . $token . "', '" . WEBAPP_SQL_SECRET_KEY . "')
//     WHERE waatID = $user_id";

//     db::SQL($q);

//     echo "Password updated for ismail";
//     exit;
// }

// For ismail (waatID=14), password: issuu20
if (isset($_GET['setpass'])) {

    // ---- ISMAIL (waatID=14) ----
    // $user_id = 14;
    // $password = "issuu21";
    // $email = "ismail@gmail.com";
    // $salt = cf::saltAnWord($email);
    // $token = cf::hashTokenByPassAndSalt($password, $salt);

    // $q = "UPDATE webapp_admins_tokens 
    // SET 
    //     waatSalt = '" . db::EscapeStr($salt) . "',
    //     waatToken = AES_ENCRYPT('" . $token . "', '" . WEBAPP_SQL_SECRET_KEY . "')
    // WHERE waatID = $user_id";
    // db::SQL($q);
    // echo "ismail done<br>";

    // ---- MUZAFFAR (waatID=15) ----
    // $user_id = 15;
    // $password = "muzaa20";
    // $email = "muzaffar@gmail.com";
    // $salt = cf::saltAnWord($email);
    // $token = cf::hashTokenByPassAndSalt($password, $salt);

    // $q = "UPDATE webapp_admins_tokens 
    // SET 
    //     waatSalt = '" . db::EscapeStr($salt) . "',
    //     waatToken = AES_ENCRYPT('" . $token . "', '" . WEBAPP_SQL_SECRET_KEY . "')
    // WHERE waatID = $user_id";
    // db::SQL($q);
    // echo "muzaffar done<br>";

    // $user_id = 16;
    // $password = "farru@22";
    // $email = "farzan@gmail.com";
    // $salt = cf::saltAnWord($email);
    // $token = cf::hashTokenByPassAndSalt($password, $salt);

    // $q = "UPDATE webapp_admins_tokens 
    // SET 
    //     waatSalt = '" . db::EscapeStr($salt) . "',
    //     waatToken = AES_ENCRYPT('" . $token . "', '" . WEBAPP_SQL_SECRET_KEY . "')
    // WHERE waatID = $user_id";
    // db::SQL($q);
    // echo "farru done<br>";

    // $user_id = 18;
    // $password = "hrfive05";
    // $email = "hrfive@gmail.com";
    // $salt = cf::saltAnWord($email);
    // $token = cf::hashTokenByPassAndSalt($password, $salt);

    // $q = "UPDATE webapp_admins_tokens 
    // SET 
    //     waatSalt = '" . db::EscapeStr($salt) . "',
    //     waatToken = AES_ENCRYPT('" . $token . "', '" . WEBAPP_SQL_SECRET_KEY . "')
    // WHERE waatID = $user_id";
    // db::SQL($q);
    // echo "hrfive done<br>";

    $user_id = 19;
    $password = "hrsix@123";
    $email = "hrsix@gmail.com";
    $salt = cf::saltAnWord($email);
    $token = cf::hashTokenByPassAndSalt($password, $salt);

    $q = "UPDATE webapp_admins_tokens 
    SET 
        waatSalt = '" . db::EscapeStr($salt) . "',
        waatToken = AES_ENCRYPT('" . $token . "', '" . WEBAPP_SQL_SECRET_KEY . "')
    WHERE waatID = $user_id";
    db::SQL($q);
    echo "hrsix done<br>";

    $user_id = 20;
    $password = "hrseven@123";
    $email = "hrseven@gmail.com";
    $salt = cf::saltAnWord($email);
    $token = cf::hashTokenByPassAndSalt($password, $salt);

    $q = "UPDATE webapp_admins_tokens 
    SET 
        waatSalt = '" . db::EscapeStr($salt) . "',
        waatToken = AES_ENCRYPT('" . $token . "', '" . WEBAPP_SQL_SECRET_KEY . "')
    WHERE waatID = $user_id";
    db::SQL($q);
    echo "hrseven done<br>";

    exit;
}

// if (isset($_GET['roletest'])) {
//     echo "<pre>";
    
//     if (isset($_SESSION['role'])) {
//         echo "role: " . $_SESSION['role'] . "\n";
//     } else {
//         echo "role: NOT SET\n";
//     }

//     if (isset($_SESSION['role_id'])) {
//         echo "role_id: " . $_SESSION['role_id'] . "\n";
//     } else {
//         echo "role_id: NOT SET\n";
//     }

//     if (isset($_SESSION['role_name'])) {
//         echo "role_name: " . $_SESSION['role_name'] . "\n";
//     } else {
//         echo "role_name: NOT SET\n";
//     }

//     if (isset($_SESSION['admin_id'])) {
//         echo "admin_id: " . $_SESSION['admin_id'] . "\n";
//     } else {
//         echo "admin_id: NOT SET\n";
//     }

//     if (isset($_SESSION['admin_username'])) {
//         echo "username: " . $_SESSION['admin_username'] . "\n";
//     } else {
//         echo "username: NOT SET\n";
//     }

//     echo "</pre>";
//     exit;
// }
//die("boot loaded");

// if (isset($_GET['fixhrtoken'])) {
//     $password = "hrone@123"; // ← put the actual password here
//     $waaID = 17; // Hr One's admin ID

//     $salt = db::PrepareLoadSingleCellValue(
//         "SELECT waatSalt FROM webapp_admins_tokens WHERE waatID = $waaID"
//     );

//     $token = cf::hashTokenByPassAndSalt($password, $salt);

//     db::SQL(
//         "UPDATE webapp_admins_tokens 
//          SET waatToken = AES_ENCRYPT('$token', '" . WEBAPP_SQL_SECRET_KEY . "')
//          WHERE waatID = $waaID"
//     );

//     echo "Done. Token updated for waaID=$waaID";
//     exit;
// }

// After admin is confirmed logged in, check role-based page access
if (isset(AppModel::$admin) && !empty(AppModel::$admin)) {

    // ✅ Get clean URL
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $currentUrl = trim(str_replace('/admin/', '', $requestUri), '/');

    if ($currentUrl === '' || $currentUrl === 'admin') {
        $currentUrl = 'home';
    }

    $currentUrl = strtolower($currentUrl);

    // ================= INTERVIEWER =================
    if (RoleHelper::isInterviewer()) {

        $allowedUrls = [
            'for-interview-applications',
            'application-details',
            'logout',
            'password',
            'employee-password',
        ];

        $allowed = false;
        foreach ($allowedUrls as $url) {
            if (strpos($currentUrl, $url) !== false) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            cf::Redirect(ADMIN . 'for-interview-applications');
            exit;
        }
    }
    

    // ================= HR MANAGER =================
// $controller = strtolower(str_ireplace('controller', '', cf::$rq['class']));

// if (!RoleHelper::canAccessModule($controller)) {
//     cf::Redirect(ADMIN . 'applications');
//     exit;
// }
}