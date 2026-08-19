<?php 
require_once(__DIR__ . "/web-app/boot/app-boot.php");
$arrContextOptions=array(
    "ssl"=>array(
        "cafile" => "/path/to/bundle/cacert.pem",
        "verify_peer"=>false,
        "verify_peer_name"=>false
    ),
);

$result = file_get_contents(WP."cron-weekly",
false, 
stream_context_create($arrContextOptions));

echo $result;
