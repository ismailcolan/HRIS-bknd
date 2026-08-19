<?php
/** Base helper class - verify the prepare function (helper optional) is called */
class helper {
    
    public static $pageExtension, $preparePassed = array();
    
    static function prepare($setOrCheck = false, $classMethod){
        self::$pageExtension = cf::$rq["ext"];
        $continuePreparation = $setOrCheck === false ? true : false ;
        if($setOrCheck === true){
            if(!in_array($classMethod, self::$preparePassed) && empty(self::$pageExtension)){
                die("ERROR (in a ".__CLASS__." call): Please call the ".$classMethod."(); method before the page headers sent."); 
            }
        } else if(!empty($setOrCheck)) {
            self::$pageExtension = $setOrCheck;
        } 
        if(!in_array($classMethod, self::$preparePassed)){
            self::$preparePassed[] = $classMethod;
        }
        return $continuePreparation;
    }
    
}
