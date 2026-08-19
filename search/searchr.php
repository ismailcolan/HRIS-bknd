<?
$TOP_DIR = "/home/humanroadferndaleroadcom/public_html";
$SEARCH = 'gray-circle';
$phpFiles = array();

function recurseDir ($current)
{
    global $phpFiles;
    
    $dirs = glob($current . "/*", GLOB_ONLYDIR);
    $files = glob($current . "/*.php");
    foreach ($files as $singleFile)
    {
        //print ($singleFile . "<br>");
        array_push($phpFiles, $singleFile);
        
    }
    
    foreach ($dirs as $singleDir)
    {
        //print ($singleDir . "<br>");
        recurseDir ($singleDir);
    }
}

recurseDir ($TOP_DIR);

foreach ($phpFiles as $phpFile)
{
    $fileContents = file_get_contents($phpFile);
    if (((stripos ($fileContents, $SEARCH) !== false) && (stripos ($phpFile, "/search/searchr.php") === false))) {print ($phpFile . "<br>");}
    
}

?>