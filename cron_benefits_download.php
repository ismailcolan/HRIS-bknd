<?php
include("web-app/config/app-config.php");
ini_set("log_errors", 1);
set_time_limit(0);
$host=PROTOCOL.$host;

$arrContextOptions=array(
    "ssl"=>array(
        "cafile" => "/path/to/bundle/cacert.pem",
        "verify_peer"=>false,
        "verify_peer_name"=>false
    ),
);
$result = file_get_contents($host."/cron-benefits-download",
false, 
stream_context_create($arrContextOptions));

echo $result;
