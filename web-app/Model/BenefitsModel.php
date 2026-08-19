<?php
class BenefitsModel extends AppModel {
    static 	$dataAddons = array(), 
			$dt = array("tbl" => "settings", "key" => "cID", "pfx" => "t", "controller" => "BenefitsController");

    static function adminPageTitleWithLinks($links = null) {
		$C = self::$dt["controller"];
		$links = AccessModel::AdminPageHeaderLnk($C);
        return array("Benefits Settings", $links);
    }
}
