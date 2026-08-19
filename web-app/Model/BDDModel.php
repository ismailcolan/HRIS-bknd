<?php

/** The General model */
class BDDModel extends AppModel {

    static $dataAddons = array(), 
	$dt = array("tbl" => "", "key" => "", "pfx" => "", "controller" => "BDDController");
    static $mainTblFields = array();
    static $fldLbl = array();
    static $postOverrided = array();
	
	static function adminPageTitleWithLinks($links = null) {
		$C = self::$dt["controller"];
		
		$links = AccessModel::AdminPageHeaderLnk($C);
        return array("Latest Benefits Data",$links);
    }
	
	
}