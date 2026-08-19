<?php
/** Application autoload function */
spl_autoload_register(function ($class) {

    // Autoload register map - Mainly for the regitered helpers
    
    $alrMap = array();
    $alrMap["captcha"]              =   array("fn" => "captcha/captcha");
    $alrMap["cf"]                   =   array("fn" => array("cf/cfCore","cf/cf"));
    $alrMap["ckeditor"]             =   array("fn" => "ckeditor/ckeditor");
    $alrMap["cssmodal"]             =   array("fn" => "cssmodal/cssmodal");
    $alrMap["db"]                   =   array("fn" => array("db/dbCore","db/db"));
    $alrMap["form"]                 =   array("fn" => array("form/formCore","form/form"));
    $alrMap["img"]                  =   array("fn" => "img/img"); //Amended version of the previous
    $alrMap["log"]                  =   array("fn" => "log/log");
    $alrMap["lang"]                 =   array("fn" => "lang/lang");
    $alrMap["msg"]                  =   array("fn" => "msg/msg");
    $alrMap["paging"]               =   array("fn" => "paging/paging");
    $alrMap["signup"]               =   array("fn" => "signup/signup");
    $alrMap["table"]                =   array("fn" => array("table/tableCore","table/table"));
    $alrMap["val"]                  =   array("fn" => array("val/valCore","val/val"));
    $alrMap["token"]                =   array("fn" => "token/token");

    if(isset($alrMap[$class])){
        (empty($alrMap[$class]["fpath"])) ? loadClassFiles($alrMap[$class]["fn"]) : loadClassFiles($alrMap[$class]["fn"], $alrMap[$class]["fpath"]);
    } else if (in_array($class,array("Page","Router","helper"))) {  //Main classes
        loadClassFiles($class);
    } else if (strpos($class,"Controller")) {                       //Controllers classes
        loadClassFiles($class,APP_CONTROLLER);
    } else if (strpos($class,"Model")) {                            //Models classes
        loadClassFiles($class,APP_MODEL);
    } else if (strpos($class,"View")) {                            //Views classes
        loadClassFiles($class,APP_VIEW);
    } else {
        if(substr($class,0,7)!="Google_"){                          //External libs will use their own autoload
            loadClassFiles($class,APP);
        }
    }
});

function loadClassFiles($classFlineNames = null, $path = APP_HELPERS, $fileExtension = ".php"){
    $cNamesArr = (is_array($classFlineNames)) ? $classFlineNames : array($classFlineNames);
    foreach ($cNamesArr AS $cN ){		
        $load_path = ($path.$cN . (!empty($fileExtension) ? $fileExtension : "") );
        @require_once $load_path;
    }
}