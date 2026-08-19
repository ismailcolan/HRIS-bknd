<?php
class Benefits extends PageAdmin
{
    const CONTROLLER = "BenefitsController";
	

    public function __construct($id=0, $C = self::CONTROLLER) {
	
        parent::__construct();
		
        $M = App_AdminModel::GetModel($C);
		
       // $C::$id = !in_array($C::$rq, array("list", "inactive-list")) && $id>0 ? $id : null;
       // $C::$data = isset($C::$id) ? $M::dataById($id) : null;
       
        AccessModel::GrantAccessAndActions($C);
		
    }

    protected function inHead($C=self::CONTROLLER) {
        msg::prepare();
    }

    protected function setPgRq($C = self::CONTROLLER, $pg=array("item"=>"Benefits", "items"=>"Benefits")){
        $M = App_AdminModel::GetModel($C);
        /*
        $pg["list"]         =   array("url" => strtolower($pg["item"]), "title" => $pg["items"]);
        $pg["inactive-list"]=   array("url" => "inactive-".strtolower($pg["item"]), "title" => "Inactive ".strtolower($pg["items"]));
        $pg["details"]      =   array("url" => strtolower($pg["item"])."-details", "title" => $pg["item"]);
        $pg["summary"]      =   array("url" => strtolower($pg["item"]), "title" => $pg["item"]);
        $pg["tbl"]          =   $M::$dt["tbl"];
        $pg["key"]          =   $M::$dt["key"];
        $pg["pfx"]          =   $M::$dt["pfx"];
       
	 */
    }
}

$adminSection = new Benefits();

if(intval(AppModel::$admin["waaAFID"]) > 0) {
   cf::Redirect(ADMIN);
}

$adminSection->Header();

if (isset($_POST['ADMIN_BENEFITS_SAVE'])) {$adminSection->SaveAdminBenefits();}
msg::get();
$adminSection->EditAdminBenefits();
$adminSection->Footer();