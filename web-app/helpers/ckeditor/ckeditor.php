<?php
/** Description prepare CKEditor usage in forms::inpCKEditor(); */
class ckeditor {

    /** Just confirms the CKEditor CDN script is loaded */
    static function set(){
        self::prepare(true);
    }
    
    static function prepare($setOrCheck = false){
		
        if( helper::prepare($setOrCheck, (__METHOD__)) !== true ){
            return; //Stops the further execution in case the helper call doesn't return true.
        }
		
         cf::appendToHeadTag("//cdn.ckeditor.com/4.9.2/full/ckeditor.js","script", array("defer"=>true));
		  cf::appendToHeadTag(ADD."helpers/ckeditor/add/ckeditor.js","script", array("defer"=>true));
	
    }
}