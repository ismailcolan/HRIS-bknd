<?php

/** The General model */
class UnsignLimitModel extends AppModel
{

    static $dataAddons = array(), 
	 $dt = array("tbl" => "settings", "key" => "cID", "pfx" => "t", "controller" => "GeneralController");

    static function adminPageTitleWithLinks($links = null)
    {
        return array("General", $links);
    }

   
}
