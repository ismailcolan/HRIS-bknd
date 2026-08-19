<?php
include("web-app/config/app-config.php");
set_time_limit(0);
$host='https://'.$host;
$arrContextOptions=array(
    "ssl"=>array(
        "cafile" => "/path/to/bundle/cacert.pem",
        "verify_peer"=>false,
        "verify_peer_name"=>false
    ),
);
$result = file_get_contents($host."/cron-credentialing",
false, 
stream_context_create($arrContextOptions));

echo $result;
?>