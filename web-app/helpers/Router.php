<?php
/** The router class
 *  @see ...\web-app-router.php */ 
class Router {

    /** @var string holds the currently active language */
    public static $lang = null;

    /** Execute cf::RequestArray() filling the cf::$rq array;
     * Loads the language current value. 
     * Sets the cf::$rq["class"] via AppModel::setCommonRequestClass();
     * @param bool $rootInCP */
    public function __construct($rootInCP) { 
        cf::RequestArray();
        cf::$rq["admin-section"] = (bool)$rootInCP;
        //self::$lang = lang::get($rootInCP);

        self::setCommonRequestClass($rootInCP);
		
    }

    /** Sets the cf::$rq["class"] via AppModel::setCommonRequestClass(); - for the given url (cf::$rq["url"]). 
     * @param bool $rootInCP */
    private static function setCommonRequestClass($rootInCP){
        AppModel::setCommonRequestClass($rootInCP);
		
    }
}