<?php
/** The Orders model */
class MyEmployeesModel extends ApplyFormModel {

    static $dataAddons = array(), 
	 $dt = array("tbl"=>"", "key"=>"", "pfx"=>"",
			"controller"=>"MyEmployeesController");

    static function adminPageTitleWithLinks($links = null){
		
        $C = self::$dt["controller"];
	
		 if(in_array($C::$rq, array("view-docs","delete-employee","hired",'employees-terminated', "inactive-hired", "manager-hired"))){
            $title = $C::$pg[$C::$rq]["title"];
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else if($C::$rq==="summary"){
            $title = (filter_has_var(INPUT_GET, "id") ? $C::$data[self::$dt["pfx"]."FirstName"]. " " . $C::$data[self::$dt["pfx"]."LastName"] : cf::end(__METHOD__));
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else if($C::$rq==="password"){
            $title = (filter_has_var(INPUT_GET, "id") ? "Set password for " . $C::$data[self::$dt["pfx"]."FirstName"]. " " . $C::$data[self::$dt["pfx"]."LastName"] : cf::end(__METHOD__));
            $links = AccessModel::AdminPageHeaderLnk($C);
			  }
		  else if(isset($_GET['list']) && $_GET['list']='emp' ){
            $title = (filter_has_var(INPUT_GET, "id") ? "List Employess of Manager: " . $C::$data[self::$dt["pfx"]."FirstName"]. " " . $C::$data[self::$dt["pfx"]."LastName"] : "Add ".$C::$pg[$C::$rq]["title"]);
			  $_SESSION['manager-of-employees']= $C::$data[self::$dt["pfx"]."FirstName"]. " " . $C::$data[self::$dt["pfx"]."LastName"];
        } else {
            $title = (filter_has_var(INPUT_GET, "id") ? "Editing: " . $C::$data[self::$dt["pfx"]."FirstName"]. " " . $C::$data[self::$dt["pfx"]."LastName"] : "Add ".$C::$pg[$C::$rq]["title"]);
        }
		 
        return array($title, $links);
    }
	
	
	
}
