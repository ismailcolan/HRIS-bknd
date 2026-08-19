<?php

/** The General model */
class CompanyLogoModel extends AppModel
{

    static $dataAddons = array(), 
	 $dt = array("tbl" => "settings", "key" => "cID", "pfx" => "t", "controller" => "CompanyLogoController");

    static function adminPageTitleWithLinks($links = null)
    {
        return array("Edit customer logo", $links);
    }

   
}
