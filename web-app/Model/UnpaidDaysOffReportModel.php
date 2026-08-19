<?php
/** The Orders model */
class UnpaidDaysOffReportModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"answers", "key"=>"aID", "pfx"=>"a", "controller"=>"QuestionController");
    static $mainTblFields = array("aName");
    static $fldLbl = array( "aID"=>"ID", "aQID"=>"Question", "aName"=>"Answer");
    static $addonFieldsByTables;
    static $postOverrided = array();

	static function adminPageTitleWithLinks($links = null)
    {
        $C = self::$dt["controller"];
		/*************
        if (in_array($C::$rq, array("list", "inactive-list"))) {
            $title = $C::$pg[$C::$rq]["title"];
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else if ($C::$rq === "summary") {
            $title = (filter_has_var(INPUT_GET, "id") ? $C::$data[self::$dt["pfx"] . "Name"] : cf::end(__METHOD__));
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else {
            $title = (filter_has_var(INPUT_GET, "id") ? "Editing: " . $C::$data[self::$dt["pfx"] . "Name"] : "Add " . $C::$pg[$C::$rq]["title"]);
        }
		**********/
		$title ='Unpaid Days Off report';
        return array($title, $links);
    }
}
