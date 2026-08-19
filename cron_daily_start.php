<?php
include("web-app/config/app-config.php");
ini_set('error_reporting', E_ALL);// & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
ini_set("error_log", "crons.error.log");
//file_put_contents("/home/developmv/public_html/cron-start.log", "START");
set_time_limit(0);
//$host='https://develop.metavisia.com/';
$arrContextOptions=array(
    "ssl"=>array(
        "cafile" => "/path/to/bundle/cacert.pem",
        "verify_peer"=>false,
        "verify_peer_name"=>false
    ),
);
$result1 = file_get_contents('https://'.$host."/cron_timeslock_p.php",
false, 
stream_context_create($arrContextOptions));
echo $result1;
//file_put_contents("/home/developmv/public_html/mycron.log", $result1);
//file_put_contents("/home/developmv/public_html/cron-start.log", "END:".date('Y-m-d H:i:s'));