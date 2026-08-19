<?php
/** The CompanyOffice model */
class ApplicationModel extends ApplyFormModel {
    static $dataAddons = array(), $dt = array("tbl"=>"apply_forms", "key"=>"afID", "pfx"=>"af", "controller"=>"ApplicationController");

    static function adminPageTitleWithLinks($links = null){
        $C = self::$dt["controller"];

        if(in_array($C::$rq, array("all", "pending", "manually-rejected", "auto-rejected", "for-interview", "for-decision"))){
            $title = $C::$pg[$C::$rq]["title"];
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else if($C::$rq==="summary"){
            $title = (filter_has_var(INPUT_GET, "id") ? $C::$data[self::$dt["pfx"]."FirstName"]. " " . $C::$data[self::$dt["pfx"]."LastName"] : cf::end(__METHOD__));
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else if($C::$rq==="details" && filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING)=== "trash"){
            $title = "<span class=\"text-danger\">Delete</span> application form #".$C::$id;
            $links = null;
        } else {
            if($C::$data) {
                $title = "Edit ".$C::$data["afFirstName"]." ".$C::$data["afLastName"]." application form";
            } else {
                $title = "Add new form";
            }
        }
        return array($title, $links);
    }

	public static function checkIfSuchExist($firstName,$lastName,$birtday){
		
		$bday = val::convDate(MYSQL_DATE_TIME,MYSQL_DATE,$birtday);
		
		$q="SELECT * FROM apply_forms WHERE afActive='Terminated' 
		AND afFirstName='{$firstName}' AND afLastName='{$lastName}' AND
		afBirthday LIKE '{$bday}%' ORDER BY afID DESC";
		
		$res=db::PrepareLoadSingleRow($q);
		
		return $res;
	}
			

}
