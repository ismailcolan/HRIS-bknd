<?php
/** Description of file - Class to handle file upload */
class file {
    
    /** @var array Allowed for the upload file types (extensions) */
    private static $allowedExtensions = array("pdf","doc","docx","xls","xlsx");
    
    /** @var array Expected file::$setup keys list<br />
     * <b>Example: <samp>self::$setup["path"] = ...</samp></b><br />
     * ["path"] path where <b>file</b> will be storred i.e. constant('FOO_FILES') //-> files/foo/<br />
     * ["allowed-extensions"]=array("jpg","png","gif") or not all of those sent as array<br />
     * ["disable-delete"] => "false" ->  */
    public static $setup = array();
    
    /**Validates and sets file::$setup array
     * @param string $fileFieldName = false - on false (in process.php) gets settings from _GET["settings"]*/
    final static function validSetupData($fileFieldName = null){
        if(!is_null($fileFieldName)){
            $s = self::$setup;
            $name = $fileFieldName;
        } else  if(filter_has_var(INPUT_GET, "settings")){
            $s = (array) json_decode(stripcslashes(filter_input(INPUT_GET, "settings",FILTER_SANITIZE_URL)));
        }
            
        if(isset($s)) {
            $res = array();
            $error = null;
            
            //WP and FP are required
            if(!defined("WP") || !defined("FP")){
                $error = isset($name) ? "Error: one or both \"WP\" / \"FP\" constants are missing before the :callMethod call" : "paths";
            } else if(!defined("TMP")){
                $error = isset($name) ? "Error: \"TMP\" constant is missing before the :callMethod call" : "tmp";
            }
            
            //["path"] is required
            if(is_null($error)){
                if(!isset($s["path"])) {
                    $error = isset($name) ? "Error: file::\$setup[\"path\"] is not set before the :callMethod call" : "path";
                } else {
                    $res["path"] = $s["path"];
                    if(!file_exists(FP . $res["path"])){
                        $error = isset($name) ? "Error: file::\$setup[\"path\"] is set to inaccessible \"".$res["path"]."\" on the :callMethod call" : "path";    
                    }
                }
            }
            
            //["allowed-extensions"]
            if(is_null($error)){
                if(isset($s["allowed-extensions"])){
                    $s["allowed-extensions"] = array_map('strtolower', $s["allowed-extensions"]);
                    foreach($s["allowed-extensions"] as $ext){
                        if(!in_array($ext, self::$allowedExtensions)){
                            $error = isset($name) ? "Error: file::\$setup[\"allowed-extensions\"] is set to incorrect file type: \"".$ext."\" on the :callMethod call" : die("ext");
                            break;
                        }
                    }
                    $allowedImgTypes = $s["allowed-extensions"];
                } else {
                    $allowedImgTypes = self::$allowedExtensions;
                }
                $res["allowed-extensions"] = $allowedImgTypes;
                
            }
            
            //["disable-delete"]
            if(is_null($error)){
                if(isset($s["disable-delete"])){
                    $res["disable-delete"] = (bool)$s["disable-delete"];
                }
            }
        } 
        
        if(is_null($error)){
            file::$setup = $res;
        } else {
            if(isset($name)){
                $bt = debug_backtrace();
                $method = $bt[1]["class"].$bt[1]["type"].$bt[1]["function"]."()";
                $errorMsg = str_replace(":callMethod",$method,$error) . " in ".basename($bt[1]["file"])." (".$bt[1]["line"].").";
                val::setError($name, $errorMsg);
            } else {
               die("fileEror: ".$error);
            }
        }
    }
}
